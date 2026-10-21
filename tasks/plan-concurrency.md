# Plan: handle 150 concurrent students on DomaiNesia shared hosting

Status: ready for implementation (Phase 1). Phase 0 is done by the user on the server. Branch: `logic-rework`.

## Context

- Test: about 150 students doing random student activity at once (browse courses, open lessons, quizzes with
  camera/tab proctoring, etc.). Result: "Resource Limit Reached".
- Hosting limits (CloudLinux LVE): Entry Processes (EP) 20, CPU 100% (= 1 core), RAM 3 GB, NPROC 200, IOPS 6000,
  IO 150 MB/s. EP = max PHP requests running at the same time; request 21 gets HTTP 508.
- So the two levers are: (a) make each PHP request shorter and cheaper, (b) send fewer PHP requests per student.
- Production `.env` (2026-10-21): `APP_DEBUG=false`, `LOG_LEVEL=debug`, `LOG_STACK=single`,
  `SESSION_DRIVER=database`, `CACHE_STORE=database`, `QUEUE_CONNECTION=database` (no `ShouldQueue` class exists),
  mail over SMTP (synchronous). `config/app.php` hardcodes `timezone => 'UTC'` (APP_TIMEZONE is ignored; keep it).
- Deploy already runs `config:cache`, `route:cache`, `view:cache`; static files are not served by PHP.

## Findings (measured with a temporary query-log probe on SQLite, 30-question quiz)

| Request | Queries | Of which `Schema::hasTable` | Of which cache table (prod) |
|---|---|---|---|
| `student.courses.show` | 22 | 4 | 5 |
| `student.lessons.content` (quiz lesson) | 13 | 2 | 2 |
| `student.quiz.start` | 17 | 4 | 4 |
| `student.quiz.begin` | 40 | 0 | 0 |
| `student.quiz.take` | 20 | 4 | 4 |
| `student.quiz.submit` | 74 | 0 | 1 |

Plus 2 `sessions` queries per request in production (database session driver).

1. `app/Providers/ViewComposerServiceProvider.php`: `View::composer('*')` runs for EVERY view and partial and does
   `Schema::hasTable('site_settings')` (an `information_schema` query on MySQL) + `Cache::rememberForever` (a DB
   query with the database cache store). It duplicates `View::share('siteSettings')` in `AppServiceProvider`.
   The `layouts.home-layout` composer runs an uncached aggregate join (`footerCategories`) and an uncached
   `SiteSetting::first()` on every public page.
2. Camera proctoring (`resources/views/student/quizzes/take.blade.php`, `logCameraViolation`): each logged violation
   uploads a full-resolution JPEG (quality 0.8) to `StudentQuizController::logCameraViolation`, which stores the file,
   runs about 6 queries (`firstOrCreate` + 2 separate `increment` + `save`) and writes 2 `Log::info` lines. With
   default settings (interval 5 s, duration 3 s) one student who keeps looking down sends one upload every ~5-8 s.
   `logTabViolation` has the same shape (create + firstOrCreate + increment + `Log::info`).
3. `QuizShuffleService::generateShuffledOrder`: one `INSERT` per question (`QuizAttemptQuestionOrder::create` in a
   loop) + `Log::info`. 150 students pressing "Mulai" in the same minute.
4. `StudentQuizController::submit`: `$attempt->quiz->questions` is loaded without `options`, so `checkAnswer`
   lazy-loads options per question (N+1), and `storeStudentAnswer` inserts per answer.
5. `Student/CertificateController::download`: renders the PDF with dompdf on every download (CPU heavy,
   `set_time_limit(120)`), never stored.
6. `LOG_LEVEL=debug` in production: every `Log::info` in hot paths writes to one ever-growing `laravel.log`.

## Phase 0 - server settings (user does this; no code; biggest win, lowest risk)

Do it outside class hours: switching the session driver logs everybody out once.

1. In production `.env`:
   - `LOG_LEVEL=warning`
   - `LOG_STACK=daily` (and optionally `LOG_DAILY_DAYS=14`)
   - `SESSION_DRIVER=file`
   - `CACHE_STORE=file`
   - `BCRYPT_ROUNDS=10` (see "Round 2 investigation")
2. Then on the server: `php artisan config:cache`.
3. cPanel > Select PHP Version > Extensions: make sure `opcache` is ticked (PHP 8.3).
4. Check the size of `storage/logs/laravel.log`; archive/delete it if it is huge.
5. Security (the `.env` was pasted into a chat): change the SMTP password of
   `noreply@edukasi.wahanamediadigital.com`, revoke the old Gmail app password of `tokokuealwa@gmail.com` (commented
   but still valid until revoked), and give the DB user a strong password (it currently equals the username). Update
   `.env` and run `php artisan config:cache` again.
6. Optional cleanup: `SESSION_DOMAIN=edukasi.wahanamediadigital.com` does not match `APP_URL=https://ruangstudi.id`
   (middleware `ConfigureSessionDomain` rewrites it per request). Not in scope here; leave as is.

## Phase 1 - code changes (Sonnet implements)

### Rules

- Follow `.claude/CLAUDE.md` (Pint, ASCII punctuation, root-cause, no unrelated edits).
- Do not change proctoring rules: what counts as a violation, thresholds, intervals and durations stay the same.
  Only make each report cheaper.
- Do not change any route name, URL, request field name or JSON response key used by the front end.
- No migrations needed. If one seems needed, stop and write it in this file.
- No commit, no push.

### 1.1 Remove per-view overhead - `app/Providers/ViewComposerServiceProvider.php`

- Delete the `View::composer('*', ...)` block entirely (`AppServiceProvider::boot` already shares `siteSettings`
  from `Cache::rememberForever('site_settings', ...)` once per request).
- In the `layouts.home-layout` composer:
  - Replace `SiteSetting::first()` with `Cache::rememberForever('site_settings', fn () => SiteSetting::firstOrNew())`
    (same key and same closure result as `AppServiceProvider`, so the existing `Cache::forget('site_settings')` in
    `Superadmin/SiteSettingController.php:76` keeps working).
  - Wrap the `footerCategories` query in `Cache::remember('footer_categories', now()->addMinutes(10), ...)`.
- Remove the now-unused `use` imports (`Schema` if unused).

### 1.2 Cheaper camera and tab violation reports

Front end - `resources/views/student/quizzes/take.blade.php`, function `captureScreenshot`:
- Scale the canvas so the longest side is at most 480 px (keep aspect ratio) and use JPEG quality 0.6 instead of
  0.8. Nothing else in the detection logic changes.
- After a response with `should_block === true`, do not send further camera reports (check whether the existing code
  already stops; if it does, leave it).

Back end - `app/Http/Controllers/Student/StudentQuizController.php`:
- `logCameraViolation`:
  - Load the attempt with `QuizAttempt::with('quiz.securitySetting')->findOrFail(...)`.
  - Remove both `Log::info(...)` calls (keep `Log::error` in the catch blocks).
  - Replace the two `increment` calls + `save()` with one `increment` that updates both columns:
    `$summary->increment($fieldMap[$violationType], 1, ['total_face_violations' => DB::raw('total_face_violations + 1')])`
    then `$summary->refresh()` is NOT needed if the response values are computed from the in-memory model; verify the
    JSON response still returns the correct counts (write a test for it, see 1.6).
- `logTabViolation`: same treatment (remove `Log::info`, eager-load what it reads, no extra `save()` after
  `increment`).

### 1.3 Quiz begin - `app/Services/QuizShuffleService.php`

- Replace the per-question `QuizAttemptQuestionOrder::create` loop with one bulk insert:
  build an array of rows (`attempt_id`, `question_id`, `shuffled_order`, plus `created_at`/`updated_at` = `now()`
  only if the table has timestamp columns - check the migration and `$timestamps` on the model) and call
  `QuizAttemptQuestionOrder::insert($rows)`.
- Remove the success `Log::info` (keep `Log::warning`/`Log::error`).

### 1.4 Quiz submit - `StudentQuizController::submit`

- Before the loop, eager load options: `$attempt->quiz->loadMissing('questions.options')` (do not change the
  preview branch behaviour).
- In `storeStudentAnswer`, keep behaviour but collect rows for the whole attempt and insert once after the loop
  inside the same `DB::transaction` (one `StudentAnswer::insert($rows)`, adding `attempt_id` and timestamps
  explicitly). First check `app/Models/StudentAnswer.php` for model events/observers or casts that `insert()` would
  bypass; if there are any, keep `createMany` and note it here.

### 1.5 Certificate PDF cache - `Student/CertificateController::download`

- Store the generated PDF in `storage/app/certificates/` (local disk, NOT public) with a file name that changes when
  the printed content changes: `{certificate_code}-{md5(user name | course title | settings updated_at)}.pdf`.
- If the file exists, return `Storage::disk('local')->download($path, $fileName)`; otherwise render with dompdf,
  `put` it, then download. Eligibility check stays first and unchanged.
- Keep the existing error handling and messages.

### 1.6 Tests (new file `tests/Feature/Performance/QueryBudgetTest.php`, plus additions)

Use the setup style of `tests/Feature/Quiz/QuizAccessTest.php` (30 questions, 4 options each) and
`DB::enableQueryLog()`:
- No `sqlite_master` (schema) queries on `student.courses.show`, `student.quiz.start`, `student.quiz.take`.
- Query budgets (set each to the measured value after the change, rounded up a little, and write the numbers here):
  `student.quiz.begin` < 40 before -> expect about 15; `student.quiz.submit` < 74 before -> expect about 15.
- Camera violation: posting 2 violations returns `violation_count` 2 and the right breakdown; with
  `UploadedFile::fake()->image(...)` the screenshot is stored on the fake `public` disk.
- Tab violation: count goes up by 1 per call.
- Certificate: second download does not call dompdf again (file exists on fake `local` disk) - if hard to test
  without mocking, test that the file is written after the first download.
- Existing tests must stay green.

### Verification (paste real output)

```
vendor/bin/pint --test
php artisan test tests/Feature/Performance tests/Feature/Quiz
./scripts/verify.sh
```

Also record before/after query counts for the 6 requests in the table above (temporary probe, delete it after).

### Out of scope (Phase 1)

- UI or design changes, `console.log` cleanup in `take.blade.php`.
- Changing proctoring semantics (thresholds, intervals, what counts as a violation).
- Queue worker / queued emails (needs cron on the server; see Phase 2).
- `SESSION_DOMAIN`, timezone, Midtrans, anything in `feature/public-redesign`.

## Phase 2a - code done now (2026-10-21, user asked to do the rest right away)

1. Queue the auth emails that students trigger in bursts (SMTP no longer holds a web entry process):
   - `app/Notifications/QueuedVerifyEmail.php` (extends `VerifyEmail`, `ShouldQueue`) and
     `app/Notifications/QueuedResetPassword.php` (extends `ResetPassword`, `ShouldQueue`).
   - `User::sendEmailVerificationNotification()` / `sendPasswordResetNotification($token)` use them.
   - Admin/instructor mails (`ContactReplyMail`, `InstructorApproved/Rejected`, `AssignmentRevisionRequired`) stay
     synchronous: rare, and their success messages ("Balasan berhasil dikirim") must stay truthful.
2. Run the queue from the scheduler (no daemon on shared hosting):
   `Schedule::command('queue:work --stop-when-empty --tries=3 --max-time=50')->everyMinute()->withoutOverlapping(5)`
   (lock expires after 5 minutes, so a worker killed by the host limits does not block the queue for 24 hours)
   in `routes/console.php`. Cron jobs do not count as web entry processes.
3. Remove the boot-time `View::share('siteSettings')` from `AppServiceProvider` (the memoized `'*'` composer now
   provides it), so boot no longer queries the DB on every request and artisan command.
4. Tests: verification and reset notifications are queued (`Notification::fake`, `assertSentTo` +
   `instanceof ShouldQueue`), schedule contains the queue command, views still get `$siteSettings`.

DEPLOY BLOCKER: the cPanel cron `* * * * * cd <app path> && php artisan schedule:run >> /dev/null 2>&1` must exist
before this is deployed, otherwise verification and reset emails stay in the `jobs` table and are never sent (the
same cron is already needed for `orders:sync-pending`).

## Round 2 investigation (2026-10-21)

Measured the other student endpoints (probe, 5 vs 25 students): home 8, catalog 11-13, public course page 12-18,
student dashboard 2, my-courses 3, student course page 11, lesson content 7, mark complete 8, course leaderboard 3,
module leaderboard 11 -> 31 (grows per student). No polling or timers on the course page (all fetches are clicks).
Hot tables are indexed (MySQL indexes every foreign key; composite PKs on `lesson_user`, `course_enrollments`).
Videos and documents are static files (`Storage::url`), not streamed through PHP. Missing static assets that would
fall through to `index.php` only exist on the admin profile page (4 icons) - not a student hot path.

Fixed in code:
- Leaderboards (`Student/CourseController::getLeaderboard`, `getModuleLeaderboard`): the partials read
  `$rank->user->studentProfile`, one query per row (up to 100). Now `with('user.studentProfile')`. Test
  `test_leaderboards_do_not_run_a_query_per_student` (fails without the fix).
- Public course page related courses (`CatalogController::show`): `with('instructor', 'category')`.
- Deploy: `php artisan event:cache` added after `view:cache` in `.github/workflows/deploy.yml`.

Config finding (add to Phase 0): bcrypt cost. Local benchmark per hash: cost 10 = 62 ms, 11 = 106 ms, 12 = 234 ms
(shared hosting CPU is slower). 150 students logging in at once at cost 12 = roughly 35-50 s of CPU on one core,
which alone fills all 20 entry processes. Set `BCRYPT_ROUNDS=10` (OWASP minimum for bcrypt, Laravel's default
before v11). Existing cost-12 hashes are rehashed to 10 automatically on the next login (`rehash_on_login` is true).

## Phase 2b - after Phase 0 + 1 + 2a are deployed

1. Ask the owner to repeat the same 150-student test, and capture cPanel > Resource Usage for that hour
   (EP faults, CPU faults, memory). Compare with the previous test.
2. If EP faults remain: look at the slowest remaining endpoints from the daily log / Resource Usage.
3. If the CPU limit is the one being hit even after this, the honest answer is a bigger plan (more EP / CPU) or a VPS:
   1 core and 20 entry processes is a hard ceiling no code change removes.

## Phase 1 results (2026-10-21)

Status: implemented, not committed.

### Deviation from 1.1

Removing `View::composer('*')` completely broke `$siteSettings` in views whenever `AppServiceProvider::boot` could
not query the DB at boot (in tests the tables do not exist yet at boot; in production the same happens silently if
MySQL hiccups at boot, because the `View::share` is wrapped in try/catch). Kept a `'*'` composer instead, but it
resolves the settings once per request (memoized on the provider, `Cache::rememberForever('site_settings')`) and no
longer calls `Schema::hasTable`. The home layout composer reuses the same memoized value.

### Query counts (same probe as the Findings table: SQLite, database cache store, 30-question quiz)

| Request | Before | After | Schema before -> after | Cache before -> after |
|---|---|---|---|---|
| `student.courses.show` | 22 | 15 | 4 -> 0 | 5 -> 2 |
| `student.lessons.content` (quiz lesson) | 13 | 9 | 2 -> 0 | 2 -> 0 |
| `student.quiz.start` | 17 | 9 | 4 -> 0 | 4 -> 0 |
| `student.quiz.begin` | 40 | 11 | 0 | 0 |
| `student.quiz.take` | 20 | 12 | 4 -> 0 | 4 -> 0 |
| `student.quiz.submit` | 74 | 16 | 0 | 1 -> 1 |

Budgets in `tests/Feature/Performance/QueryBudgetTest.php`: begin <= 15, submit <= 20, zero schema queries on
courses.show / quiz.start / quiz.take.

### Notes

- Camera reports: screenshot now max 480 px on the longest side, JPEG 0.6. Front end already stopped sending after
  `should_block` (`isCameraBlocked` check in `onResults`), left as is.
- Certificate cache: old files stay on disk when the name/title/settings change (new hash). Small; clean up later if
  needed. Files live in `storage/app/private/certificates` (Laravel 12 `local` disk root is `storage/app/private`;
  its `serve` route requires a signed URL, so the PDFs are not publicly reachable).

### Opus review (2026-10-21)

- Fixed: camera `incrementEach` went through the query builder and no longer touched `updated_at` of the integrity
  summary (old `$model->increment()` did). Now passes `['updated_at' => now()]`; test asserts it.
- Checked, no change needed: bulk insert of question order matches old `create` (model has `UPDATED_AT = null`, so
  `created_at` was already set by Laravel in UTC); `StudentAnswer` has no events/observers (`HasLocalDates` is
  accessors only); multi-answer and drag-and-drop rows are built exactly as before; preview submit path unchanged;
  `should_block` already stops camera reports; zero-size video before ready behaves as before.
- Follow-up (not done, low impact): `AppServiceProvider::boot` still queries `site_settings` at boot for every
  request and artisan command, duplicating the composer; can be removed in a later cleanup. The memoized composer
  would keep stale settings inside a long-running process (future `queue:work`); use `--stop-when-empty`.

## Local load test (2026-10-21)

Files: `scripts/loadtest/run.sh`, `scripts/loadtest/student-mix.js` (k6), `database/seeders/LoadTestSeeder.php`
(not called by `DatabaseSeeder`, refuses to run in production; idempotency test `tests/Feature/LoadTestSeederTest.php`).

- `run.sh [before|after] [students=150] [hold=3m]` wipes and seeds a dedicated database (`lms_loadtest`), caches
  config/routes/views/events like the deploy, and starts `php -S` with 20 workers pinned to one CPU core
  (`taskset`) and opcache on, to mimic 20 entry processes on 1 core. Env vars override `.env`; the cached config is
  cleared on exit.
- `before` = current production config (database session/cache, bcrypt 12, debug log); `after` = Phase 0 config.
- Scenario per student: log in once, then random actions with 3-8 s think time: lesson content 30%, course page 20%,
  mark complete 10%, leaderboard 5%, quiz 15% (start, begin, ~45 s answering with camera/tab reports for a third of
  the students, submit), home/catalog 15%, public course page 5%.
- Output: per request type count, median, p95, max, failed %, saved to `storage/logs/loadtest/`.
- Smoke-tested with 5 students on SQLite and on the dev MySQL 26.7 container (podman `mysql_edugames`, database
  `lms_loadtest` granted to `edugamesuser`): all request types 0% failed. Limits of the simulation: `php -S` queues extra requests instead of answering 508, so look at
  p95/max latency (a request waiting for a free worker) rather than at failures; MariaDB is not pinned to the core.
- `LOADTEST_DEBUG=1` prints every failed response.

### Results, 150 students (2026-10-21, Phase 1 + 2a code in both runs, MySQL 26.7 container)

| Request | before p50 / p95 / max (ms) | after p50 / p95 / max (ms) |
|---|---|---|
| login | 7516 / 17970 / 27092 | 67 / 167 / 255 |
| login_page | 1198 / 12539 / 16306 | 4 / 10 / 23 |
| course_page | 33 / 7283 / 18294 | 14 / 27 / 80 |
| lesson_content | 21 / 3871 / 17871 | 11 / 25 / 69 |
| catalog | 21 / 6679 / 17268 | 11 / 31 / 65 |
| quiz_start | 26 / 5191 / 16554 | 12 / 28 / 54 |
| quiz_begin | 29 / 3608 / 17609 | 20 / 38 / 83 |
| camera_violation | 24 / 61 / 17655 | 18 / 43 / 248 |
| quiz_submit | 25 / 57 / 7386 | 17 / 33 / 64 |
| total | 4897 req, 16.9/s | 5459 req, 18.9/s |

Reading: with the current production config, the login burst during ramp-up (bcrypt 12 + database
session/cache) makes every request queue for up to 17-27 s; on the real host those waits are 508 errors. With the
Phase 0 config every request type stays under 260 ms max. The p50 values are similar in both runs: the code itself is
fast; the queueing comes from config. Caveat: the laptop core is faster than the shared-hosting core, so real
latencies are higher; the next useful number is the breaking point of the `after` config (run with more students).

## Phase 3 - correctness under concurrent requests (2026-10-21)

Problem: several student actions use check-then-write without a lock. Two requests at the same time (double click,
browser retry, auto-submit + manual submit while the server is slow) both pass the check.

| # | Where | Effect |
|---|---|---|
| R1 | `Student/CourseController::markAsComplete`, `submitPolling`, `submitWordcloud` (`alreadyCompleted` check, then points) | points awarded twice |
| R2 | `StudentQuizController::submit` (status check outside any lock) | answers stored twice, quiz points twice |
| R3 | `StudentQuizController::begin` (existing attempt / max_attempts check, then create) | two `in_progress` attempts, `max_attempts` exceeded |
| R4 | `PointService::addPoints` (`CourseUser` find, then plain `create`) | unique violation -> 500 on the first points of a course |
| R5 | `StudentAssignmentController` (first-submission check, then points) | assignment points twice |

Not a problem (checked): `QuizAttemptIntegritySummary::firstOrCreate` - Laravel 12 `firstOrCreate` falls back to
`createOrFirst`, which handles the unique violation.

Decision (user, 2026-10-21): fix in code with row locks; no new unique index on `point_histories` now (a migration
would have to clean existing duplicates in production first).

Approach: one helper that serializes a student's writes - `DB::transaction` + `User::whereKey($id)->lockForUpdate()`
- and every check is repeated inside it. Per-user lock, so different students never wait on each other.
R2 duplicate submits redirect to the result page instead of a 403 page (the browser shows the last response).
R4 uses `createOrFirst` + atomic `increment`.

Proof: `scripts/loadtest/race.sh` (k6, 20 parallel requests from the same student on MySQL) prints DB counts;
run before and after the fix.

### Phase 3 results (2026-10-21)

Implemented: `app/Services/StudentWriteLock.php` (transaction + `lockForUpdate` on the student's `users` row,
3 deadlock retries), used in `markAsComplete`, `submitPolling`, `submitWordcloud`, `StudentAssignmentController::submit`
(file stored before the lock, old file deleted after commit), quiz `begin` and quiz `submit`.
`PointService::addPoints` uses `CourseUser::createOrFirst` + `increment`.

Behaviour changes (intended):
- Submitting an attempt that is no longer `in_progress` redirects to the result page (was a 403 page).
- `begin` resumes an `in_progress` attempt before checking `max_attempts`. Before, with `max_attempts = 1`, the second
  click of a double click showed "batas maksimal" although the attempt had just been created.

Race test, 20 parallel requests from one student session (`scripts/loadtest/race.sh`, MySQL):

| Check | Expected | Before fix | After fix |
|---|---|---|---|
| point rows for the raced lesson | 1 | 1 | 1 |
| attempts on a `max_attempts = 1` quiz | 1 | 6 | 1 |
| stored answers for one 20-question submit | 20 | 60 | 20 |
| HTTP 500 (deadlock on `quiz_attempts`, duplicate key on `lesson_user`) | 0 | 19 | 0 |

Feature tests: `tests/Feature/Student/RepeatedActionsTest.php` (5 tests, sequential repeats).
Not done (decision): unique index on `point_histories`.

Load test after Phase 3 (150 students, `after` config): 5439 requests, 0% failed, every max under 300 ms.
Medians are ~1.3-1.6x the earlier `after` run on all request types, including ones without a lock (home 11 -> 15,
catalog 11 -> 16, login_page 4 -> 5) at the same rate as locked ones (lesson_complete 22 -> 30, quiz_submit 17 -> 28),
so the shift is run-to-run noise on the laptop, not the per-student lock.
