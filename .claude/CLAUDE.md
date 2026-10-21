# CLAUDE.md

Binding rules and instructions for coding agents and engineers.
Project overview, environment, and verification commands: see [AGENTS.md](../AGENTS.md).

---

## 1. Workflow Orchestration

### Plan Mode Default
- Enter plan mode for ANY non-trivial task (3 or more steps, architectural decisions, or schema changes).
- Write down clear, actionable steps in `tasks/todo.md` before touching code.
- If an unexpected error occurs during execution, STOP and re-plan immediately. Do not push blindly.

### Self-Improvement Loop
- After ANY bug fix, merge conflict, or user correction: update `tasks/lessons.md` with the pattern.
- Formulate a clear rule that prevents the exact same mistake from occurring again.
- Review `tasks/lessons.md` at the beginning of each coding session.

### Verification Before "Done"
- Never mark a task complete without proving it works with actual command output (`./scripts/verify.sh`).
- Diff behavior between the base branch (`develop`, or `main` for hotfixes) and your changes.
- Ask: "Would a staff engineer approve this PR?"
- Run tests, check `storage/logs/laravel.log` (or `php artisan pail`), and demonstrate correctness.

### Root-Cause Bug Fixing
- Do not apply surface-level monkey-patches or temporary hacks.
- Trace the defect to its fundamental root cause.
- Keep the blast radius minimal: only touch code directly necessary to fix the problem.
- Preserve all unrelated comments and docstrings.

---

## 2. Database & Schema Governance (Laravel + MySQL)

- **Single Source of Truth**: All structure changes live in `database/migrations/` (`php artisan make:migration`).
  Never run DDL by hand, from tinker, or from runtime code.
- **Zero Drift**: Never edit a migration that already ran on staging/production; add a new migration.
  Every migration must have a working `down()`. Data fixes also go in a migration (see
  `2026_09_03_092500_fix_null_points_for_polling_and_wordcloud_in_site_settings.php`).
- **Portability**: Tests run on SQLite in-memory. Avoid MySQL-only SQL in migrations and queries, or guard it
  with `DB::getDriverName()`.
- **Idempotency Guarantee**: `php artisan db:seed` must be safe to run repeatedly.
  - Call seeders in dependency order in `DatabaseSeeder` (roles before users, categories before courses).
  - Use `updateOrCreate` / `firstOrCreate` / `upsert`, never bare `Model::create()` in a seeder.
  - Key every seeded row on a natural key (role `name`, category `slug`, user `email`), never on an auto-increment ID.
- **Safe Testing**: Use `RefreshDatabase` and factories. Never point tests at the dev or production MySQL database.
- **Honest Metrics**: Never show a reassuring zero (0) for points, scores, progress, or revenue that does not exist
  or has not been computed. Return `null` and render "belum ada data" (or equivalent) in the view.

---

## 3. Code Hygiene & Standards

- **Code Style**: Laravel Pint (default preset). `vendor/bin/pint --test` must pass.
- **Punctuation**: Use plain ASCII punctuation in new code, comments, Blade views, and translations
  (no em-dash, en-dash, curly quotes, or Unicode ellipsis).
- **Timezone Safety**: Store timestamps in UTC (app timezone is `UTC`). Convert to `users.timezone` only for display.
  Handle date-only values (birth date, availability dates) as `Y-m-d` strings or `date` casts, not shifted datetimes.
- **Authorization**: Protect every route with the right middleware/role (Spatie) and check ownership
  (an instructor only edits their own courses, a student only sees their own attempts).
- **Validation**: Validate input with Form Requests or `$request->validate()`; never trust client-side checks alone.
- **Errors**: Never show raw exceptions or SQL errors to users; `APP_DEBUG=false` in production.
- **Documentation**: Record major architectural decisions in `docs/adr/` and user-visible changes in `CHANGELOG.md`.
