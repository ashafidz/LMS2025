# Plan: Gap Remediation (security, payment, data integrity, tooling)

- Written: 2026-10-14 (analysis by Claude Opus, for implementation by another AI agent or developer)
- Repo: ruangstudi.id LMS, Laravel 12, PHP 8.3 compatible, MySQL in dev/prod, SQLite in-memory in tests
- Read first: `AGENTS.md`, `.claude/CLAUDE.md`, `tasks/lessons.md`
- Status legend: `[ ]` todo, `[x]` done, `[!]` deviated (write why under the task)

---

## 0. Rules for the implementer (binding)

1. Work task by task, in the order below. One branch per phase, created from `develop`:
   `fix/payment-security` (Phase 1), `fix/authorization` (Phase 2), `fix/data-integrity` (Phase 3),
   `chore/tooling-cleanup` (Phase 4). Phase 0 goes into the Phase 1 branch as its first commit.
2. Do NOT push and do NOT open PRs. Commit locally only if the user asked for commits; otherwise leave changes uncommitted in the working tree.
3. Every task lists "Acceptance". A task is done only when its acceptance tests exist and pass. Paste the real
   output of the verification command under the task (short summary: `Tests: N passed`).
4. Minimal blast radius: change only the files listed for the task. If a task needs another file, you may touch
   it, but write the file name and reason under the task as a deviation (`[!]`).
5. Never delete commented-out code. The team keeps commented code on purpose (some blocks say
   "tidak terpakai tapi tolong jangan dihapus"). Only delete whole dead methods/classes that this plan names explicitly.
6. Never edit an existing migration. New schema changes = new migration file with working `down()`.
7. Plain ASCII punctuation in all new code, comments, and strings. User-facing messages in Indonesian
   (match the existing style, e.g. `'Anda tidak terdaftar di kursus ini.'`).
8. Run `vendor/bin/pint <changed files>` on files you touch (not on the whole repo, except in Task 4.1).
9. If something in the codebase contradicts this plan (a method name differs, a column does not exist,
   behavior is not what is described), STOP that task, write what you found under it with `[!]`, and continue
   with the next independent task. Do not guess.
10. After any fix of a real bug, add an entry to `tasks/lessons.md` (symptom, root cause, rule).

### Verification commands

```bash
php artisan test --filter=<TestClass>   # per task
./scripts/verify.sh                      # end of each phase (Pint step will fail until Task 4.1; report it, do not fix early)
```

Known baseline (2026-10-14): `php artisan test` = 2 passed. `vendor/bin/pint --test` = 132 issues in 232 files.
`npm run build` needs `npm install` first. Migrations run fine on SQLite (`DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan migrate:fresh --force` passes).

---

## Findings summary (why this plan exists)

| # | Severity | Finding | Where |
|---|----------|---------|-------|
| F1 | CRITICAL | `GET /orders/{order}/success` marks any pending order `paid` and enrolls the user with no payment verification and no owner check (owner check is commented out). Anyone can get paid courses for free by opening the success URL. | `app/Http/Controllers/PaymentController.php` `success()` |
| F2 | CRITICAL | No Midtrans notification (webhook) route exists. `MidtransCallbackController` is not routed; its CSRF exception lives in `app/Http/Middleware/VerifyCsrfToken.php`, which Laravel 12 does not load. Payments made after the Snap popup closes (VA, QRIS, minimarket) are never confirmed. | `routes/web.php`, `app/Http/Controllers/MidtransCallbackController.php` |
| F3 | HIGH | Snap `onClose` redirects to `/orders/{order}/cancelled`, which sets status `cancelled` client-side. A user who closes the popup and then pays by VA ends with money taken and a cancelled order. Same for `failed`. | `resources/views/student/checkout/show.blade.php`, `PaymentController` |
| F4 | HIGH | `pending/failed/cancelled` and `CheckoutController::show()` have no owner check: any logged-in user can view or change another user's order. | `PaymentController`, `Student/CheckoutController.php` |
| F5 | HIGH | Instructor IDOR: module and lesson controllers have no ownership check (comment "Otorisasi dihapus"). Any approved instructor can edit/delete any other instructor's modules, lessons, quiz questions, lesson points. | `Instructor/ModuleController.php`, `Instructor/LessonController.php`, `Instructor/QuizQuestionController.php`, `Instructor/LessonPointController.php`, others listed in Phase 2 |
| F6 | HIGH | `/switch-role/{role}` is outside the `auth` group and stores any string in `session('active_role')`. Quiz `start/begin/take` only check enrollment when `active_role === 'student'`, so `/switch-role/x` bypasses enrollment checks. | `routes/web.php:96`, `RoleSwitchController.php`, `Student/StudentQuizController.php` |
| F7 | HIGH | `take()` does not check `attempt->student_id`, so a student can open another student's in-progress attempt. | `StudentQuizController::take()` |
| F8 | HIGH | `POST /student/quiz/check-answer` lets any logged-in user ask whether an answer to ANY question is correct (answer oracle) and returns `debug_info`. Not used by any view. | `StudentQuizController::checkAnswerAjax()`, `routes/web.php` |
| F9 | HIGH | Quiz preview (`is_preview=true` in `begin()` and `submit()`) is available to any logged-in user for any quiz. | `StudentQuizController` |
| F10 | MEDIUM | Coupon usage counters are incremented when the order is created, not when it is paid; failed/cancelled orders still consume coupon quota. The coupon is read from session without re-validation at checkout. | `Student/CheckoutController.php` |
| F11 | MEDIUM | Diamond purchase/conversion check balance or conversion flag outside the transaction without row locks: double-submit can double spend or double convert. Purchase does not check existing enrollment. | `app/Services/DiamondService.php` |
| F12 | MEDIUM | Student actions without enrollment check: assignment submit (also awards points), polling/wordcloud submit (may award points). | `Student/StudentAssignmentController.php`, `Student/CourseController.php` |
| F13 | MEDIUM | Seeders not idempotent: `Role::create`, `CourseCategory::create`. | `database/seeders/RoleSeeder.php`, `CourseCategorySeeder.php` |
| F14 | MEDIUM | Almost no tests (2 example tests), only `UserFactory`. CI hides Pint failures with `|| echo`. Deploy workflow uses unpinned actions (`@main`, `@master`). | `tests/`, `.github/workflows/*.yml` |
| F15 | LOW | Dead code: `QuestionControllerStable` (not routed), `MidtransCallbackController` methods using non-existent `costumer.*` views, `app/Http/Middleware/VerifyCsrfToken.php` (not loaded in Laravel 12). README says Laravel 11. | various |

---

## Phase 0 - Test foundation (prerequisite for every phase)

### [x] 0.1 Factories for the core models
- Files (new): `database/factories/CourseCategoryFactory.php`, `CourseFactory.php`, `ModuleFactory.php`,
  `LessonFactory.php`, `QuizFactory.php`, `QuizAttemptFactory.php`, `OrderFactory.php`, `CouponFactory.php`,
  `InstructorProfileFactory.php`, `StudentProfileFactory.php`.
- Before writing each factory, read the model (`app/Models/*.php`: `$fillable`, casts, relations, `booted()`) and
  every migration that touches its table (`grep -l "<table_name>" database/migrations/*`) to get NOT NULL columns.
- Add `use HasFactory;` to each model that lacks it (only that line plus the import).
- Notes:
  - `Lesson` is polymorphic (`lessonable_type`, `lessonable_id`). Give `LessonFactory` a state
    `forQuiz()` that creates a `Quiz` and sets `lessonable_*`. `Quiz::lesson()` is `morphOne`.
  - `Order::booted()` generates `order_code`; do not set it in the factory.
  - `Course.instructor_id` -> `User`. Instructor routes need an approved instructor profile
    (`CheckInstructorStatus`, `instructorProfile->application_status === 'approved'`).
  - Many models use `HasHashedRouteKey`; always build URLs in tests with `route('name', $model)`, never raw IDs.
- Acceptance: a throwaway test can create a course with module, quiz lesson, attempt, and order without errors.
- Verification:
  ```
  php artisan test --filter=PhaseZeroFactoryTest
  PASS Tests\Feature\PhaseZeroFactoryTest
  ✓ can create all core models using factories (0.22s)
  Tests: 1 passed (10 assertions)
  Duration: 0.25s
  ```

### [x] 0.2 Test helpers
- File: `tests/TestCase.php` (extend only). Add helpers:
  - `seedRoles()`: `$this->seed(\Database\Seeders\RoleSeeder::class)` (requires Task 3.1 idempotency; do 3.1 first if needed).
  - `makeStudent(): User` (role `student`, verified email), `makeInstructor(): User` (role `instructor`, approved `InstructorProfile`),
    `makeAdmin()`, `makeSuperadmin()`.
  - `enroll(User $u, Course $c)`: `$u->enrollments()->syncWithoutDetaching([$c->id => ['enrolled_at' => now()]])`
    (check the pivot columns of `enrollments()` in `app/Models/User.php:156` first).
- All feature tests use `RefreshDatabase`.
- Acceptance: `php artisan test` still green.
- Verification:
  ```
  php artisan test --filter=PhaseZeroTest
  PASS Tests\Feature\PhaseZeroTest
  ✓ helpers can create student instructor admin superadmin and enroll (0.24s)
  Tests: 1 passed (8 assertions)
  Duration: 0.26s

  php artisan test
  Tests: 4 passed (20 assertions)
  Duration: 0.29s
  ```

---

## Phase 1 - Payment and orders (F1, F2, F3, F4, F10) - branch `fix/payment-security`

Target design: order status only changes from data verified on the server (Midtrans webhook with valid
signature, or a server-to-server status call). Browser redirects only display status. Settlement logic lives in one place.

### [x] 1.1 `MidtransService` (gateway wrapper, mockable)
- New file: `app/Services/MidtransService.php`
  - `configure(): void` sets `Midtrans\Config::$serverKey`, `$isProduction`, `$isSanitized = true`, `$is3ds = true` from `config('midtrans.*')`.
  - `createSnapToken(Order $order): string` (move body of `CheckoutController::generateAndSaveSnapToken` params here; caller saves the token).
  - `fetchStatus(string $orderCode): array` returns `['transaction_status' => ..., 'fraud_status' => ..., 'gross_amount' => ..., 'status_code' => ...]`
    using `Midtrans\Transaction::status($orderCode)`; on exception log and return `[]`.
  - `isValidSignature(array $payload): bool`: `hash_equals(hash('sha512', order_id . status_code . gross_amount . server_key), signature_key)`.
- Bind nothing special; resolve via container (`app(MidtransService::class)`) so tests can `$this->mock(MidtransService::class)`.
- Verification:
  ```
  php artisan test --filter=MidtransWebhookTest
  PASS Tests\Feature\Payment\MidtransWebhookTest
  ✓ invalid signature returns 403 and order stays pending (0.24s)
  ```

### [x] 1.2 `OrderSettlementService` (single source of truth for status changes)
- New file: `app/Services/OrderSettlementService.php`
  - `markPaid(Order $order): bool`
    - `DB::transaction`: reload with `Order::whereKey($order->id)->lockForUpdate()->first()`.
    - Allowed transitions to `paid`: from `pending`, `failed`, `cancelled` (late VA payment). If already `paid` return `false` (idempotent).
    - Set `status = 'paid'`, save.
    - For each item: `$user->enrollments()->syncWithoutDetaching([$course_id => ['enrolled_at' => now()]])` and
      `PointService::addPoints($user, $item->course, 'purchase')` only when the course was not already enrolled before this call.
    - Coupon usage (moved from checkout, F10): if `coupon_id`, `increment('uses_count')` on the coupon and on the
      user pivot (same logic as current `CheckoutController::process` lines inside `if ($coupon)`).
    - Delete the user's cart rows for the purchased course IDs.
  - `markFailed(Order $order, string $status): bool` (`$status` in `failed|cancelled|expired`): only from `pending`.
    Check that `expired` is a valid value for `orders.status` (read the migration; if it is an enum without `expired`, use `failed`).
- Acceptance: unit/feature test calls `markPaid` twice -> one enrollment row, one `purchase` point history, coupon `uses_count` +1 once.
- Verification:
  ```
  php artisan test --filter=OrderSettlementServiceTest
  PASS Tests\Feature\Payment\OrderSettlementServiceTest
  ✓ mark paid is idempotent and enrolls once (0.28s)
  ✓ mark failed sets status only from pending (0.01s)
  Tests: 2 passed (13 assertions)
  Duration: 0.33s
  ```

### [x] 1.3 Webhook route and controller
- `bootstrap/app.php`: inside `withMiddleware`, add `$middleware->validateCsrfTokens(except: ['midtrans/notification']);`
- `routes/web.php`: outside every auth group add
  `Route::post('/midtrans/notification', [MidtransCallbackController::class, 'handle'])->middleware('throttle:60,1')->name('midtrans.notification');`
- Rewrite `MidtransCallbackController::handle(Request $request)`:
  1. `$payload = $request->all()`. If `!MidtransService::isValidSignature($payload)` -> `403`.
  2. Find order by `order_code = $payload['order_id']`; not found -> `404` JSON (no exception).
  3. If `(int) round($payload['gross_amount']) !== (int) round($order->final_amount)` -> log warning, `422`, no status change.
  4. Map: `capture` + `fraud_status=accept` or `settlement` -> `markPaid`; `deny|cancel|failure` -> `markFailed(failed)`;
     `expire` -> `markFailed(expired or failed)`; `pending` -> no change.
  5. Return `200 {"message":"OK"}`.
- Delete methods `success`, `fail`, `pending`, `pendingPayment`, `cancel` from `MidtransCallbackController` (dead, reference non-existent `costumer.*` views).
- Delete `app/Http/Middleware/VerifyCsrfToken.php` (not loaded by Laravel 12; grep first that nothing references it).
- Document in `AGENTS.md` (Local Development Environment): Midtrans dashboard notification URL must be `https://<domain>/midtrans/notification`; locally use a tunnel (ngrok) or rely on Task 1.4 status check.
- Acceptance tests (`tests/Feature/Payment/MidtransWebhookTest.php`):
  - invalid signature -> 403, order stays `pending`
  - valid `settlement` -> 200, order `paid`, user enrolled, no CSRF token needed
  - same notification twice -> still one enrollment, one point history
  - `gross_amount` mismatch -> 422, order `pending`
  - unknown `order_id` -> 404
  - `expire` on pending -> not `paid`; then `settlement` -> `paid` (late payment)
  - Build signatures in the test with `config(['midtrans.server_key' => 'test-key'])`.
- Verification:
  ```
  php artisan test --filter=MidtransWebhookTest
  PASS Tests\Feature\Payment\MidtransWebhookTest
  ✓ invalid signature returns 403 and order stays pending (0.24s)
  ✓ valid settlement marks order paid and enrolls user (0.03s)
  ✓ same notification twice keeps single enrollment and point history (0.02s)
  ✓ gross amount mismatch returns 422 and order stays pending (0.02s)
  ✓ unknown order id returns 404 (0.01s)
  ✓ expire marks failed then subsequent settlement marks paid (0.02s)
  Tests: 6 passed (23 assertions)
  Duration: 0.36s
  ```

### [x] 1.4 Return pages become read-only plus server-side status check
- `PaymentController`:
  - Every action (`success`, `pending`, `failed`, `cancelled`): first `abort_unless($order->user_id === Auth::id(), 403);` (uncomment intent; leave the old commented lines in place).
  - `success()`: if status is `pending`, call `MidtransService::fetchStatus($order->order_code)`; if status is
    `settlement` or (`capture` and `accept`) and amount matches, call `OrderSettlementService::markPaid`. Never set `paid` otherwise.
    Free orders (`final_amount == 0`) are already `paid` (see 1.5). Render the view with the refreshed order; the view must
    show "Pembayaran sedang diproses" when status is still `pending` (check `resources/views/student/checkout/success.blade.php` and add that branch).
  - `failed()` and `cancelled()`: do NOT change status. Only display. (Webhook decides.)
  - Remove private `enrollStudent()` and `enrollStudentAndAddPoints()` (logic moved to service).
- `resources/views/student/checkout/show.blade.php`: `onClose` should redirect to `route('payment.pending', $order)` (user can reopen payment later from transaction history), not `payment.cancelled`.
- `Student/CheckoutController::show()`: add the same owner check.
- Acceptance tests (`tests/Feature/Payment/PaymentReturnTest.php`, mock `MidtransService`):
  - other user opens `/orders/{order}/success|pending|failed|cancelled` -> 403; same for `checkout.show`
  - owner opens success, gateway returns `pending` -> order stays `pending`, not enrolled
  - owner opens success, gateway returns `settlement` with matching amount -> `paid`, enrolled once
  - owner opens `cancelled` -> status unchanged `pending`
- Verification:
  ```
  php artisan test --filter=PaymentReturnTest
  PASS Tests\Feature\Payment\PaymentReturnTest
  ✓ other user gets 403 on all return routes and checkout show (0.27s)
  ✓ owner opens success and gateway returns pending keeps order pending (0.02s)
  ✓ owner opens success and gateway returns settlement marks paid and enrolled once (0.03s)
  ✓ owner opens cancelled keeps status pending (0.02s)
  Tests: 4 passed (17 assertions)
  Duration: 0.36s
  ```

### [x] 1.5 Checkout uses the services; coupon re-validation
- `Student/CheckoutController::process()`:
  - Remove the block that increments coupon usage at order creation (moved to `markPaid`).
  - Re-validate the session coupon before computing the discount: reload it from DB (`Coupon::find($coupon->id)`),
    and apply the same validity rules used when the coupon was applied. Find them in `Shared/CouponController.php` or the
    student coupon-apply code (`grep -rn "session()->put('coupon'" app`); extract them into a reusable method
    (e.g. `Coupon::isUsableBy(User $user): bool`) and call it in both places. If invalid: forget the session coupon and
    redirect to cart with `'Kupon tidak berlaku lagi.'`.
  - Cap discount: `$discount = min($discount, $subtotal)`.
  - Snap token via `MidtransService::createSnapToken`; delete private `generateAndSaveSnapToken`.
  - `processFreeOrder`: create order as `pending` with `final_amount = 0`, then call `OrderSettlementService::markPaid` (so enrollment, points, coupon usage, cart cleanup follow the same path; fixes duplicate `attach`).
  - Keep the current cart behavior for paid orders (cart cleared after the Snap token is created); `markPaid` cart cleanup is then a no-op.
- Acceptance tests (`tests/Feature/Payment/CheckoutTest.php`, mock `MidtransService::createSnapToken`):
  - checkout creates a `pending` order, coupon `uses_count` unchanged
  - 100% coupon -> order `paid`, enrolled, coupon `uses_count` +1
  - expired/over-quota coupon in session -> redirect with error, no order
  - free checkout when already enrolled in one course -> no exception, no duplicate enrollment
- Verification:
  ```
  php artisan test --filter=CheckoutTest
  PASS Tests\Feature\Payment\CheckoutTest
  ✓ checkout creates pending order and coupon uses count unchanged (0.26s)
  ✓ 100 percent coupon marks order paid enrolled and coupon uses count incremented (0.02s)
  ✓ expired or over quota coupon in session redirects with error and no order (0.02s)
  ✓ free checkout when already enrolled does not throw and no duplicate enrollment (0.02s)
  Tests: 4 passed (27 assertions)
  Duration: 0.34s
  ```

---

## Phase 2 - Authorization (F5, F6, F7, F8, F9, F12) - branch `fix/authorization`

### [x] 2.1 `CoursePolicy` for instructor ownership
- New file: `app/Policies/CoursePolicy.php` with `manage(User $user, Course $course): bool` =
  `$course->instructor_id === $user->id`. Laravel 12 auto-discovers `App\Policies\CoursePolicy` for `App\Models\Course`.
- Use `Gate::authorize('manage', $course)` (or `$this->authorize` if the base `Controller` has `AuthorizesRequests`; it does not by default in Laravel 12, so use `Gate::authorize`).
- Resolve the course: Module -> `$module->course`; Lesson -> `$lesson->module->course`; Quiz -> `$quiz->lesson->module->course`;
  LessonAssignment -> `$assignment->lesson->module->course`.
- Apply at the top of EVERY public action of:
  - `Instructor/ModuleController.php` (all actions; `index/create/store/reorder` use `$course`)
  - `Instructor/LessonController.php` (all actions incl. `pollingResults`, `wordcloudResults`, `togglePollingStatus`, `toggleWordcloudStatus`; for `reorder` also verify every submitted lesson ID belongs to `$module`)
  - `Instructor/LessonPointController.php` (`index`, `award`; also verify the awarded user is enrolled in the course)
  - `Instructor/QuizQuestionController.php` (all; for `attachQuestions` verify each question belongs to the instructor using the same rule `Instructor/QuestionController.php` uses - read it first)
  - `Instructor/QuizSecurityController.php`, `Instructor/InstructorQuizController.php`, `Instructor/InstructorAssignmentController.php`, `Instructor/InstructorRecapController.php`, `Instructor/CourseController.php`:
    these already have some checks; audit each public action and add the policy call where an action takes a model and has no check. Do not remove existing checks.
  - `Instructor/QuestionTopicController.php`, `Instructor/InstructorLeaderboardController.php`, `Instructor/InstructorDashboardController.php`: verify queries are scoped to `Auth::id()`; add checks where a route model is used without scoping.
- Acceptance tests (`tests/Feature/Authorization/InstructorOwnershipTest.php`): for module `update`, `destroy`; lesson `update`, `destroy`, `togglePollingStatus`; quiz question `attachQuestions`, `detachQuestion`; lesson point `award`:
  instructor B -> 403 and DB unchanged; owner A -> success (302/200).
- Verification:
  ```
  php artisan test tests/Feature/Authorization/InstructorOwnershipTest.php
  PASS Tests\Feature\Authorization\InstructorOwnershipTest
  ✓ instructor b cannot update or delete instructor a module (0.27s)
  ✓ instructor a can update and delete own module (0.03s)
  ✓ instructor b cannot update delete or toggle polling on instructor a lesson (0.03s)
  ✓ instructor b cannot attach or detach questions on instructor a quiz (0.02s)
  ✓ instructor a can attach and detach own questions (0.02s)
  ✓ instructor b cannot award lesson points on instructor a course (0.02s)
  ✓ instructor b cannot access or modify instructor a course (0.03s)
  Tests: 7 passed (20 assertions)
  Duration: 0.46s
  ```

### [x] 2.2 Role switch hardening (F6)
- `routes/web.php`: move `Route::get('/switch-role/{role}', ...)` inside the `Route::middleware(['auth'])->group(...)` block (keep name `role.switch`).
- `RoleSwitchController::switch`: first line after `$user`: `abort_unless(in_array($role, ['superadmin','admin','instructor','student'], true), 403);`
  - Implementation detail: For `$role === 'instructor'` where `$user->hasRole('instructor')` is false, users are allowed to reach the application or application status pages (`instructor.pending`, `instructor.rejected`, `instructor.deactive`, or `student.apply_instructor.create`), but `active_role` is NOT set in session. For all other actual role switches, `$user->hasRole($role)` is strictly checked with `abort_unless($user->hasRole($role), 403)`.
- Acceptance tests: guest -> redirect to login; student switching to `admin` -> 403 and session unchanged; instructor switching to `instructor` -> ok.
- Verification:
  ```
  php artisan test tests/Feature/Authorization/RoleSwitchTest.php
  PASS Tests\Feature\Authorization\RoleSwitchTest
  ✓ guest is redirected to login (0.24s)
  ✓ student cannot switch to admin or superadmin (0.03s)
  ✓ student cannot switch to invalid role (0.02s)
  ✓ instructor can switch to instructor (0.02s)
  ✓ student switching to instructor redirects to application without setting active role (0.02s)
  ✓ student with pending instructor profile redirects to pending without setting active role (0.02s)
  Tests: 6 passed (17 assertions)
  Duration: 0.36s
  ```

### [x] 2.3 Quiz access (F6, F7, F9)
- `StudentQuizController`: replace every `if (session('active_role') === 'student') { enrollment check }` (in `start`, `begin`, `take`, `result`) with an unconditional check through a private helper:
  `canAccessCourse(User $user, Course $course): bool` = enrolled OR `$course->instructor_id === $user->id` OR `$user->hasAnyRole(['admin','superadmin'])`.
- `take()`: after loading the attempt, `abort_unless($attempt->student_id == Auth::id(), 403);`
- Preview (`begin()` with `is_preview === 'true'`, `submit()` with `is_preview === 'true'`, `start()` with `?preview=true`): allowed only if
  `$quiz->lesson->module->course->instructor_id === Auth::id()` or the user has `admin`/`superadmin`; otherwise 403.
- Acceptance tests (`tests/Feature/Quiz/QuizAccessTest.php`): non-enrolled student cannot `begin` even after `/switch-role/foo`; student B cannot `take` student A's attempt; student cannot preview; course instructor can preview; enrolled student can begin and submit.
- Verification:
  ```
  php artisan test tests/Feature/Quiz/QuizAccessTest.php
  PASS Tests\Feature\Quiz\QuizAccessTest
  ✓ non enrolled student cannot begin quiz even with tampered active role (0.27s)
  ✓ student b cannot take student a attempt (0.02s)
  ✓ student cannot preview quiz (0.03s)
  ✓ course instructor can preview quiz (0.03s)
  ✓ enrolled student can begin and submit quiz (0.03s)
  ✓ check answer route returns 404 (0.02s)
  Tests: 6 passed (13 assertions)
  Duration: 0.44s
  ```

### [x] 2.4 Remove the answer oracle (F8)
- `grep -rn "check_answer\|check-answer\|checkAnswerAjax" resources app routes` - on 2026-10-14 no view used it.
- If still unused: delete the route `student.quiz.check_answer` and the method `checkAnswerAjax()` (keep the private `checkAnswer()` used by `submit`). Leave the commented-out old version above it untouched.
- If something uses it: do NOT delete; instead require `attempt_id`, check attempt owner + `in_progress`, check the question belongs to the attempt's quiz, only answer when the quiz setting that reveals answers is enabled (see `reveal_answers` column in `2025_08_02_052921_add_reveal_answers_to_quizzes_table.php`), and remove `debug_info`.
- Acceptance: test that `POST /student/quiz/check-answer` returns 404 (or 403 under the alternative).
- Verification: verified in `QuizAccessTest::test_check_answer_route_returns_404` (returned 404).

### [x] 2.5 Enrollment checks on student actions (F12)
- `Student/StudentAssignmentController::submit`: before validation, `abort_unless(enrolled in $assignment->lesson->module->course, 403)`.
- `Student/CourseController`: `submitPolling`, `submitWordcloud`, `ajaxPollingResults`, `ajaxWordcloudResults`, `getLeaderboard`, `getModuleLeaderboard`, `markAsComplete` (already has one; keep): add the same `canAccessCourse` style check (enrolled, owner instructor, or admin/superadmin). Return JSON 403 for AJAX endpoints (match the existing JSON shape `['success' => false, 'message' => ...]`).
- Acceptance tests: non-enrolled student gets 403 on assignment submit and polling submit, and receives no points (`PointHistory` count unchanged).
- Verification:
  ```
  php artisan test tests/Feature/Authorization/StudentActionEnrollmentTest.php
  PASS Tests\Feature\Authorization\StudentActionEnrollmentTest
  ✓ non enrolled student gets 403 on assignment submit and no points awarded (0.29s)
  ✓ non enrolled student gets 403 on polling submit and no points awarded (0.03s)
  ✓ enrolled student can submit assignment and polling (0.04s)
  Tests: 3 passed (10 assertions)
  Duration: 0.39s
  ```

---

## Phase 3 - Data integrity (F11, F13) - branch `fix/data-integrity`

### [x] 3.1 Idempotent seeders
- `database/seeders/RoleSeeder.php`: `Role::firstOrCreate(['name' => $name, 'guard_name' => 'web'])` for the 4 roles. Keep `app()[PermissionRegistrar::class]->forgetCachedPermissions()` if present.
- `database/seeders/CourseCategorySeeder.php`: `CourseCategory::updateOrCreate(['slug' => Str::slug($category)], ['name' => $category])`.
- Check `SuperadminUserSeeder`, `AdminUserSeeder`: they use `firstOrCreate`; make sure `assignRole` does not fail on re-run (Spatie `assignRole` is safe) - just verify.
- Acceptance test (`tests/Feature/SeederIdempotencyTest.php`): `$this->seed(); $this->seed();` -> no exception; counts of roles (4), categories, users identical after the 2nd run.
- Verification:
  ```
  php artisan test tests/Feature/SeederIdempotencyTest.php
  PASS Tests\Feature\SeederIdempotencyTest
  ✓ database seeder is idempotent (0.27s)
  Tests: 1 passed (6 assertions)
  Duration: 0.30s
  ```

### [x] 3.2 Diamond race conditions (F11)
- `app/Services/DiamondService.php`:
  - `useForPurchase`: move the balance check inside the transaction after `$locked = User::whereKey($user->id)->lockForUpdate()->first();`. Return `false` if balance is insufficient or the user is already enrolled in the course. Use `syncWithoutDetaching` instead of `attach`.
  - `convertFromPoints`: inside the transaction, re-read the `course_user` pivot row with `DB::table('course_user')->where(...)->lockForUpdate()->first()` and re-check `is_converted_to_diamond` and `points_earned` before converting.
- Acceptance tests: insufficient balance -> false, nothing changed; already enrolled -> false, balance unchanged; calling `convertFromPoints` twice -> second returns false, balance increased once.
  (SQLite ignores `lockForUpdate`; the tests cover the logic, the lock covers MySQL concurrency.)
- Verification:
  ```
  php artisan test tests/Feature/Diamond/DiamondServiceTest.php
  PASS Tests\Feature\Diamond\DiamondServiceTest
  ✓ use for purchase returns false when insufficient balance (0.24s)
  ✓ use for purchase returns false when already enrolled (0.01s)
  ✓ use for purchase succeeds when sufficient balance (0.02s)
  ✓ convert from points twice second returns false and balance increased once (0.02s)
  Tests: 4 passed (13 assertions)
  Duration: 0.32s
  ```

---

## Phase 4 - Tooling and cleanup (F14, F15) - branch `chore/tooling-cleanup`

### [x] 4.1 Pint baseline (style-only commit)
- Run `vendor/bin/pint` on the whole repo. (Per prompt instructions, left uncommitted in working tree).
- `php artisan test` passes.
- `.github/workflows/ci.yml`: changed `run: vendor/bin/pint --test || echo "Pint failed, but we continue for now"` to `run: vendor/bin/pint --test`.
- Verification:
  ```
  vendor/bin/pint --test
  PASS 256 files
  ```

### [x] 4.2 Pin GitHub Actions
- `.github/workflows/deploy.yml`: replaced `easingthemes/ssh-deploy@main` with `easingthemes/ssh-deploy@v6.0.1` and `appleboy/ssh-action@master` with `appleboy/ssh-action@v1.2.5`.
- Verification: pinned to latest releases `v6.0.1` and `v1.2.5`.

### [x] 4.3 Remove dead code
- Deleted `app/Http/Controllers/Instructor/QuestionControllerStable.php` after verifying zero references in repo.
- `README.md`: updated "Laravel 11.x" -> "Laravel 12.x" in badge and tech stack line.

### [x] 4.4 Docs
- `CHANGELOG.md`: added `[1.3.0] - 2026-10-14` entry detailing security and hardening improvements.
- `AGENTS.md`: Midtrans webhook CSRF exemption and route note verified.
- `tasks/todo.md` backlog: ticked all resolved items.

---

## Out of scope (do NOT do in this plan)

- Moving assignment submission files from the `public` disk to a private disk with signed downloads (track as backlog).
- Translating mixed English/Indonesian flash messages, UI redesign, i18n.
- Refactoring large controllers (`StudentQuizController`, `LessonController`) beyond the checks above.
- Changing point/badge business rules, quiz scoring, or proctoring logic.
- Database schema redesign, adding enums, or touching existing migrations.
- Deploy pipeline changes other than pinning action versions (no backups, no maintenance mode changes).
- Upgrading Composer/NPM dependencies.

## Manual checks for the human after implementation

1. In the Midtrans sandbox dashboard set Payment Notification URL to `https://<staging-domain>/midtrans/notification`.
2. Sandbox purchase with VA: close the Snap popup, pay with the simulator, confirm the order becomes `paid` and the course appears.
3. Open another user's `/orders/<hash>/success` while logged in as a different student: expect 403.
4. As instructor B, try to open instructor A's module edit URL: expect 403.
