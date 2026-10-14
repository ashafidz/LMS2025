# Plan: Bugfix Round 2 (coupon, pricing, ID comparison, stuck pending orders)

- Written: 2026-10-14 by Claude Opus after reviewing the result of `tasks/plan-gap-remediation.md`
- Base: the gap-remediation work must already be committed (see "Before you start"). Work on a new branch
  `fix/coupon-pricing` created from that commit.
- Read first: `AGENTS.md`, `.claude/CLAUDE.md`, `tasks/lessons.md`, `tasks/plan-gap-remediation.md` (context only)
- Status legend: `[ ]` todo, `[x]` done, `[!]` deviated (write why under the task)

---

## Before you start (done by the human or the reviewing AI, not by the implementer)

- [ ] Gap-remediation changes committed on their own branch (e.g. `fix/gap-remediation`).
- [ ] New branch `fix/coupon-pricing` created from that commit. `git status` is clean.

If `git status` is not clean when you start, STOP and report. Do not mix this work with older uncommitted changes.

---

## 0. Rules for the implementer (binding)

1. Do NOT commit and do NOT push. Leave all changes in the working tree.
2. Work task by task in order. A task is done only when its acceptance tests exist and pass. Paste the real
   output (short: `Tests: N passed`) under each task.
3. Only touch the files listed per task. If you need another file, touch it and record it under the task with `[!]`.
4. Never delete commented-out code. Never edit existing migrations. Plain ASCII punctuation only.
5. User-facing messages are in Indonesian, short, plain language for non-technical users (school staff, students).
   Use exactly the messages given in this plan.
6. Run `vendor/bin/pint` on files you touch. Finish with `./scripts/verify.sh` (all three steps must pass).
7. If the code does not match what this plan describes (method, variable, or column missing or named differently),
   STOP that task, write what you found with `[!]`, continue with the next independent task. Do not guess.
8. Add one entry per real bug fixed to `tasks/lessons.md` (symptom, root cause, rule).

Baseline before this plan: `./scripts/verify.sh` all pass, `php artisan test` = 47 passed (179 assertions).

---

## Bugs this plan fixes

| # | Bug | Where (current code) |
|---|-----|----------------------|
| B1 | Course-scoped coupon (`coupons.course_id` set) discounts the WHOLE cart subtotal instead of only that course's price. | `Student/CartController::index()` lines ~24-38, `Student/CheckoutController::process()` lines ~31-60 |
| B2 | Cart page and checkout compute prices with two different copies of the formula. The cart does not cap the discount (fixed coupon bigger than subtotal gives a negative price) and still adds VAT and transaction fee when the total is 0, so the cart shows a different total than the checkout charges. | same as B1 |
| B3 | Prices with VAT/fee percentages produce decimals (e.g. 110,450.50). Midtrans charges whole rupiah (`MidtransService::createSnapToken` rounds), the order stores decimals, the webhook compares rounded values. Stored amount and charged amount can differ by up to 1 rupiah. | `CheckoutController`, `MidtransService`, `MidtransCallbackController` |
| B4 | All coupon errors now show one generic message. The specific messages for "per-user limit reached" and "coupon not valid for the courses in your cart" were lost. | `Coupon::isUsableBy()`, `Student/CartController::applyCoupon()`, `CheckoutController::process()` |
| B5 | Coupon quota is checked at checkout but counted only when the order is paid, so several pending orders can push a coupon past `max_uses` / `max_uses_per_user`. | `Coupon::isUsableBy()`, `OrderSettlementService::markPaid()` |
| B6 | A session coupon whose course was removed from the cart still applies. | `CartController::index()`, `CheckoutController::process()` |
| B7 | Ownership checks use strict `===` between an ID attribute and `Auth::id()` / `$user->id`, but the models do not cast those columns to integer. If the production PDO driver ever returns strings, every owner gets 403. | `PaymentController`, `CheckoutController::show()`, `CoursePolicy`, `StudentQuizController`, others found by grep |
| B8 | If the Midtrans webhook is not reachable (URL not set in dashboard, server down), a paid order stays `pending` forever unless the student returns to the success page. No safety net. | none exists |

---

## Task 1 - `CartPricingService` (fixes B1, B2, B3, B6)

### [ ] 1.1 Create the service
- New file: `app/Services/CartPricingService.php`
- Public method:
  ```php
  /**
   * @param  \Illuminate\Support\Collection  $cartItems  Cart models with ->course loaded
   * @return array{subtotal: int, discount: int, vat_percentage: float, vat_amount: int, transaction_fee: int, final_total: int, coupon: ?\App\Models\Coupon}
   */
  public function calculate(Collection $cartItems, ?Coupon $coupon, ?SiteSetting $settings): array
  ```
- Rules (all money values are whole rupiah integers, use `(int) round(...)` at each step):
  1. `subtotal` = sum of `course->price` of all items.
  2. Coupon base:
     - `coupon->course_id === null` -> base = subtotal.
     - `coupon->course_id` set -> base = price of that course if it is in `$cartItems`; if it is not in the cart,
       treat the coupon as not applicable: `discount = 0` and return `coupon => null` (fixes B6).
  3. `discount`: type `fixed` -> `min(coupon->value, base)`; type `percent` -> `round(base * value / 100)`. Never above base.
  4. `after_discount = subtotal - discount` (never below 0).
  5. If `after_discount == 0`: `vat_amount = 0`, `transaction_fee = 0`, `final_total = 0` (free order, matches current checkout behavior).
  6. Else: `vat_amount = round(after_discount * vat_percentage / 100)`;
     `before_fee = after_discount + vat_amount`;
     `transaction_fee = round(transaction_fee_fixed + before_fee * transaction_fee_percentage / 100)`;
     `final_total = before_fee + transaction_fee`.
  7. `vat_percentage` = `settings->vat_percentage ?? 0` (returned so checkout can store `vat_percentage_at_purchase`).
- Keep column names of `SiteSetting` exactly as the current code uses them: `vat_percentage`,
  `transaction_fee_fixed`, `transaction_fee_percentage`.

### [ ] 1.2 Use it in the cart page
- `app/Http/Controllers/Student/CartController.php` `index()`:
  - Reload the session coupon from DB: `$coupon = session('coupon') ? Coupon::find(session('coupon')->id) : null;`
  - Replace the inline formula (from `$subtotal = ...` to `$finalTotal = ...`) with one call to the service.
  - If the service returns `coupon => null` while a session coupon existed, `session()->forget('coupon')` and flash
    `session()->now('error', 'Kupon dilepas karena kursusnya tidak ada lagi di keranjang.')`.
  - Keep passing the same variable names to the view: `subtotal`, `coupon`, `discount`, `vatAmount`, `transactionFee`, `finalTotal`.
  - Leave the public coupon and popular course queries untouched.
- `resources/views/student/cart/index.blade.php` line ~99 uses `session('coupon')->code`. Change to `$coupon->code`
  inside the existing condition, guarding with `@if ($coupon)` if needed. Change nothing else in the view.

### [ ] 1.3 Use it in checkout
- `app/Http/Controllers/Student/CheckoutController.php` `process()`:
  - Keep the coupon reload and validation block (it will change in Task 2).
  - Replace the inline formula (discount cap, VAT, fee, final total) with the service call.
  - If a session coupon existed but the service returns `coupon => null`: forget the session coupon and redirect to
    `student.cart.index` with error `'Kupon dilepas karena kursusnya tidak ada lagi di keranjang.'` (do not create an order).
  - Pass the service values to `createOrderRecord` / `processFreeOrder` (same parameters as today).
- `MidtransService::createSnapToken`: keep `(int) round($order->final_amount)`; it is now a no-op because the amount is already whole.
- Do not change `MidtransCallbackController` amount comparison.

### Acceptance (new file `tests/Unit/CartPricingServiceTest.php` or `tests/Feature/Payment/CartPricingServiceTest.php`)
- [ ] no coupon, VAT 11%, fee 0 -> integers, `final_total = subtotal + round(subtotal*0.11)`
- [ ] global percent coupon 10% on two courses (100000 + 50000) -> discount 15000
- [ ] course-scoped percent coupon 50% for the 50000 course in a cart of 100000 + 50000 -> discount 25000 (not 75000)
- [ ] course-scoped coupon whose course is not in cart -> discount 0, coupon null
- [ ] fixed coupon 200000 on subtotal 150000 -> discount 150000, final_total 0, vat 0, fee 0
- [ ] VAT 11% and fee 2.5% on 99999 -> every returned value is `int`
- [ ] Feature: cart page total equals the `final_amount` of the order created by checkout for the same cart and coupon (mock `MidtransService::createSnapToken`)
- [ ] Feature: checkout with session coupon for a course no longer in cart -> redirect to cart with the error, no order created
- [ ] Existing `tests/Feature/Payment/CheckoutTest.php` still passes (update expected numbers only if they relied on the old whole-cart discount for a course-scoped coupon; record any change with `[!]`)

---

## Task 2 - Specific coupon error messages (fixes B4)

### [ ] 2.1 Reason method on the model
- `app/Models/Coupon.php`: add `public function unusableReasonFor(User $user): ?string` that runs the same checks as
  `isUsableBy()` in the same order and returns the first matching message, or `null` when usable:
  | Check | Message |
  |-------|---------|
  | not active, expired, or not started yet | `'Kode kupon tidak valid atau sudah tidak berlaku.'` |
  | total quota reached (see Task 3 for the counting rule) | `'Kuota kupon ini sudah habis.'` |
  | per-user quota reached | `'Anda telah mencapai batas maksimal penggunaan untuk kupon ini.'` |
  | `course_id` set and that course is not in the user's cart | `'Kupon ini tidak berlaku untuk kursus yang ada di keranjang Anda.'` |
- Rewrite `isUsableBy()` as `return $this->unusableReasonFor($user) === null;` (keep the method; tests use it).

### [ ] 2.2 Use the reason
- `Student/CartController::applyCoupon()`: unknown code -> `'Kode kupon tidak valid atau sudah tidak berlaku.'`;
  otherwise `if ($reason = $coupon->unusableReasonFor($user)) return back()->withErrors(['code' => $reason]);`
- `Student/CheckoutController::process()`: when the reloaded coupon is unusable, redirect to cart with
  `'Kupon tidak bisa dipakai: '.$reason` (or `'Kupon tidak berlaku lagi.'` if the coupon was deleted).

### Acceptance (`tests/Feature/Payment/CouponMessageTest.php`)
- [ ] each of the 4 cases in the table returns its exact message on `applyCoupon` (assert session errors `code`)
- [ ] valid coupon -> success flash `'Kupon berhasil diterapkan.'`
- [ ] checkout with a coupon that became expired after applying -> redirect to cart with the message, no order

---

## Task 3 - Count pending orders in coupon quota (fixes B5)

### [ ] 3.1 Counting rule
- In `Coupon` add:
  ```php
  public const RESERVATION_HOURS = 24; // Midtrans Snap default expiry

  public function reservedCount(?User $user = null): int
  ```
  = number of `orders` with `coupon_id = $this->id`, `status = 'pending'`, `created_at >= now()->subHours(self::RESERVATION_HOURS)`,
  filtered by `user_id` when `$user` is given. Check the relation name between Coupon and Order first
  (`grep -n "function orders\|coupon_id" app/Models/Coupon.php app/Models/Order.php`); add `public function orders()` (hasMany) on Coupon if missing.
- Quota checks in `unusableReasonFor()`:
  - total: `max_uses !== null && uses_count + reservedCount() >= max_uses`
  - per user: `max_uses_per_user !== null && (pivot uses_count or 0) + reservedCount($user) >= max_uses_per_user`
- `OrderSettlementService::markPaid()` stays as is (it increments `uses_count` when paid; the paid order is no longer
  `pending`, so it stops counting as reserved: no double count).
- Edge: in `CheckoutController::process()` the validation runs BEFORE the new order is created, so the order being
  created is not counted against itself. Keep that order of operations.

### Acceptance (`tests/Feature/Payment/CouponQuotaTest.php`)
- [ ] `max_uses = 1`, one pending order (created now) with this coupon by user A -> user B gets `'Kuota kupon ini sudah habis.'`
- [ ] same, but the pending order is 25 hours old -> user B can use it
- [ ] same, but the order is `failed` or `cancelled` -> user B can use it
- [ ] `max_uses_per_user = 1`, user A has one pending order with the coupon -> user A gets the per-user message, user B can use it
- [ ] pending order then `markPaid` -> `uses_count = 1`, reserved = 0, total counted = 1 (no double count)

---

## Task 4 - Integer casts for ownership IDs (fixes B7)

### [ ] 4.1 Find every strict ID comparison
- Run and paste the output under this task (27 matches on 2026-10-14):
  ```bash
  grep -rnE '(===|!==) *(Auth::id\(\)|auth\(\)->id\(\)|\$user->id|\$student->id)|(Auth::id\(\)|auth\(\)->id\(\)) *(===|!==)' app
  ```

### [ ] 4.2 Add casts, do not change the comparisons
- For every model whose `*_id` column appears in that output, add an `'<column>' => 'integer'` entry to the model's
  existing `$casts` array (create `protected $casts = [...]` if missing; if the model uses `casts()` method, add there).
  At minimum: `Order` (`user_id`), `Course` (`instructor_id`), `QuizAttempt` (`student_id`), plus any others from 4.1.
- Do not change the `===` comparisons themselves; the casts make them reliable.

### Acceptance
- [ ] `tests/Unit/OwnershipCastTest.php`: for each model changed, `new Model(['<col>' => '5'])` (or `setRawAttributes`) -> `->getAttribute('<col>') === 5`
- [ ] full `php artisan test` still green

---

## Task 5 - Safety net for stuck pending orders (fixes B8)

### [ ] 5.1 Artisan command
- New file: `app/Console/Commands/SyncPendingOrders.php`, signature `orders:sync-pending {--minutes=15} {--hours=48}`,
  description `'Cek status order pending ke Midtrans dan selesaikan yang sudah dibayar'`.
- Logic: for each `Order` with `status = 'pending'`, `final_amount > 0`, `created_at` between `now()->subHours(hours)` and
  `now()->subMinutes(minutes)`, chunk by 100:
  - `$status = app(MidtransService::class)->fetchStatus($order->order_code)`; empty array -> skip.
  - Same mapping and amount check as `MidtransCallbackController::handle` (settlement, or capture + accept, with matching
    rounded amount -> `markPaid`; deny/cancel/failure/expire -> `markFailed($order, 'failed')`; pending -> nothing).
  - To avoid duplicating the mapping, extract it from `MidtransCallbackController` into
    `OrderSettlementService::applyGatewayStatus(Order $order, array $status): string` returning `'paid'|'failed'|'ignored'|'amount_mismatch'`,
    and use it in the controller, in `PaymentController::success()`, and in the command. The controller still returns 422 on `amount_mismatch`.
  - Print a summary line: `Checked: N, paid: X, failed: Y, unchanged: Z`.
- Register the schedule in `routes/console.php`:
  `Schedule::command('orders:sync-pending')->everyFifteenMinutes()->withoutOverlapping();` (add `use Illuminate\Support\Facades\Schedule;`).
- `AGENTS.md`, section Local Development Environment: add one line: production needs the Laravel scheduler cron
  (`* * * * * cd /path && php artisan schedule:run >> /dev/null 2>&1`) for `orders:sync-pending`; the Midtrans webhook stays the primary path.

### Acceptance (`tests/Feature/Payment/SyncPendingOrdersTest.php`, mock `MidtransService::fetchStatus`)
- [ ] pending order 20 minutes old, gateway `settlement` with matching amount -> becomes `paid`, user enrolled
- [ ] pending order 5 minutes old -> not checked (`fetchStatus` never called)
- [ ] gateway `expire` -> `failed`
- [ ] gateway returns `[]` -> unchanged
- [ ] amount mismatch -> unchanged
- [ ] existing `MidtransWebhookTest` and `PaymentReturnTest` still pass after the extraction

---

## Task 6 - Wrap up

- [ ] `./scripts/verify.sh` -> paste the final summary (Pint, Tests count, Build).
- [ ] `CHANGELOG.md`: one short entry in plain Indonesian: diskon kupon per kursus, total keranjang sama dengan checkout,
      pesan kupon yang jelas, kuota kupon, sinkronisasi otomatis pembayaran yang tertunda.
- [ ] `tasks/lessons.md`: entries for B1/B2 (duplicated pricing formula), B5 (quota counted at a different moment than it is checked), B8 (no fallback when webhook is down).

---

## Out of scope

- UI redesign of the cart or checkout pages (only the `session('coupon')` line in the cart view changes).
- Changing coupon admin forms or coupon types.
- Changing how points, badges, or diamonds work.
- New migrations or schema changes (none are needed: everything uses existing columns).
- Committing, pushing, branching beyond what "Before you start" says.

## Manual checks for the human after implementation

1. Midtrans dashboard: Payment Notification URL = `https://<domain>/midtrans/notification` (still required).
2. cPanel cron for `php artisan schedule:run` every minute.
3. Sandbox: apply a course-scoped coupon with two courses in cart; cart total and Snap amount must match.
