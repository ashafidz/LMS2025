# ADR-016: point history as source of truth, and paid purchases give diamonds

- **Status**: Accepted
- **Date**: 2026-11-24
- **Deciders**: Project owner, client

---

## Context and Problem Statement
Three production reports in one course: a student ranked 172 points while the progress page showed 72, another student had
116 points on the progress page but 0 in the instructor ranking, and the point sync page said "Sistem Poin Stabil".

- The instructor course ranking only loaded the top 100 `course_user` rows and showed 0 for everybody else.
- The progress pages only counted history rows with a lesson, so the 100 "Membeli kursus" points were invisible there.
- A course purchase gave points, also for 100% coupon orders (Rp0). In one course only one student had bought the course, so
  that student led the ranking and unlocked modules with points that were not earned in the course.
- Deleting a lesson or module removes its `point_histories` rows (foreign key cascade) but never lowered
  `course_user.points_earned`. Production had 155 totals above their history (68 students in active courses).
- The point sync compared only one history row per lesson and never compared the total with the history.

## Decision
- **The point history (`point_histories`) is the source of truth.** `course_user.points_earned` is a cache that rankings,
  module locks and badges read; `App\Services\PointLedger::recalculate()` rebuilds it from the history.
- **Deleting a lesson or module removes its points** (client decision): the controllers read the affected students first, delete,
  then recalculate their totals. A module delete removes lessons through the database, so this is done in the controller.
- **`points:reconcile`** (daily 01:45 with `--fix`) rebuilds totals that differ from the history in courses that are not
  soft-deleted, and logs every fix. Diamond balances that differ from `diamond_histories` are only reported, never changed.
- **Point sync** works per student under `StudentWriteLock`, keeps one row per lesson (duplicates removed, manual-points lessons
  excepted because several awards are allowed), and rebuilds the total from the history.
- **A course purchase gives diamonds, not points, and only when the order was paid** (`final_amount > 0`). The amount is the
  new setting `diamonds_for_purchase`. Free orders (coupon to Rp0) give nothing. The bonus is written as
  "Bonus pembelian kursus: ..." because "Membeli kursus: ..." already means diamonds spent on a course.
- **Old purchase points** (data migration, can be rolled back): every "Membeli kursus" point row is removed; paid ones become
  diamonds at the point-to-diamond rate; courses already converted to diamonds are skipped. Diamond balances without history get
  one "Saldo awal (penyesuaian data)" row so balance and history match; balances are not changed.
- **Rankings**: equal points keep sequential numbers in a fixed order: who reached the points first (`course_user.updated_at`,
  for modules the latest history row), then user id. Instructors see every student; students see the top 100 plus their own rank.

## Consequences
- Totals and the progress pages always agree with the history; drift is repaired within a day and logged.
- After deploy, students lose points from deleted lessons and old purchase points. Five students in active courses fall a few
  points below a module requirement (including a UAS module); the client informs the instructors.
- No unique index on `point_histories`: manual points may be given several times for one lesson.
