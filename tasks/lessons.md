# Project Lessons Learned & Prevention Rules

Record all solved bugs, merge conflicts, and user corrections here. Review this file before starting new features.

---

## Example Entry

### [2026-10-09] Migration Timestamp Collision After Merge
- **Symptom**: `php artisan migrate` runs migrations in an unexpected order after merging two feature branches.
- **Root Cause**: Both branches created migrations with the same timestamp prefix (e.g. two `2026_01_06_103138_*` files); Laravel sorts by filename, so order depends on the table name.
- **Rule**: After merging branches that add migrations, list `database/migrations/` sorted, make sure dependent tables come after their parents, and run `php artisan migrate:fresh --seed` on a local database plus `./scripts/verify.sh` before concluding the merge.

---

## Log Entries

### [2026-10-14] Payment Status Bypass via Client-Side Return URLs (F1, F3, F4)
- **Symptom**: Opening `/orders/{order}/success` marked pending orders as paid and granted access to courses without payment verification, and users could access other users orders.
- **Root Cause**: Return URLs relied on client-side navigation without authorization checks or server-to-server gateway status verification.
- **Rule**: Never trust browser redirects for transaction status transitions. Order settlement must strictly be driven by verified server webhook notifications or server-side status inquiry with signature and amount matching. All order return routes must verify order ownership (`$order->user_id === Auth::id()`).

### [2026-10-14] Premature Coupon Quota Consumption (F10)
- **Symptom**: Abandoned, failed, or cancelled checkout sessions permanently consumed coupon usage quotas.
- **Root Cause**: Coupon counters were incremented during initial order generation instead of at settlement time.
- **Rule**: Decrement stock or increment usage quotas only when an order is confirmed paid in `OrderSettlementService`. Re-validate coupon validity right before checkout processing.

### [2026-10-14] Authorization Bypass via Missing Ownership Checks and Session Active Role (F5, F6, F7, F9, F12)
- **Symptom**: Instructors could view or edit other instructors courses, modules, and quiz questions. Students could bypass quiz enrollment checks by switching session active_role, access other students quiz attempts, preview quizzes, or submit assignments and pollings for non-enrolled courses.
- **Root Cause**: Missing policy checks on resource routes, reliance on mutable session('active_role') instead of database roles/enrollments, and missing ownership checks on quiz attempts and assignments.
- **Rule**: Always authorize resource access using Laravel Gates/Policies (e.g. CoursePolicy) or direct database enrollment and ownership checks (`abort_unless`). Never rely on session variables for authorization or access control.

### [2026-10-14] Quiz Answer Oracle via Public Check-Answer AJAX Endpoint (F8)
- **Symptom**: Students or malicious users could query `/student/quiz/check-answer` with question IDs and candidate answers to receive correct/false responses and complete question explanations with debug info.
- **Root Cause**: An unused debugging/oracle endpoint remained exposed in routes without attempt binding or answer reveal policy restrictions.
- **Rule**: Avoid exposing real-time answer verification endpoints during active quiz attempts unless explicitly permitted by quiz settings with an active attempt and verified participant ownership. Delete obsolete debug endpoints.
