# Active Task Plan

## Task: [Short Task Title]

- Status: In Progress
- Started: [Date]
- Branch: [feature/... from develop]
- Target: [Goal description]

---

### Plan Checklist

- [ ] 1. Analysis and root cause diagnosis (review `tasks/lessons.md` first)
- [ ] 2. Implementation: migrations / models (if schema changes)
- [ ] 3. Implementation: controllers, services, Blade views
- [ ] 4. Tests: feature/unit tests for the change
- [ ] 5. Verification: `./scripts/verify.sh`
- [ ] 6. Self-review, `CHANGELOG.md`, and `tasks/lessons.md` update

---

### Verification Proof

- Command: `vendor/bin/pint --test` -> [Output summary]
- Command: `php artisan test` -> [Output summary]
- Command: `npm run build` -> [Output summary]

---

## Backlog: gaps found while adopting the engineering standards (2026-10-14)

- [x] `database/seeders/RoleSeeder.php` uses `Role::create()`: second `db:seed` fails on the unique role name. Switch to `Role::firstOrCreate(['name' => ...])`.
- [x] `database/seeders/CourseCategorySeeder.php` uses `CourseCategory::create()`: duplicates or unique error on re-seed. Switch to `updateOrCreate(['slug' => ...], [...])`.
- [x] `vendor/bin/pint --test` reports 132 style issues in 232 files (2026-10-14), and `.github/workflows/ci.yml` hides this with `|| echo ...`. Run `vendor/bin/pint` in a dedicated style-only PR, then remove the CI fallback.
- [x] Test suite only has the example tests. Add feature tests for the critical flows (enrollment, quiz attempt, payment callback).
- [x] Non-ASCII punctuation in `resources/views/student/quizzes/take.blade.php`, `resources/views/superadmin/point-sync/mass-sync-preview.blade.php`, and one migration. Clean up when those files are next touched.
- [x] `README.md` says Laravel 11.x; `composer.json` requires `laravel/framework ^12`.
