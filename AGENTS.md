# AGENTS.md

Instructions for AI coding agents (Antigravity, Cursor, Claude Code, Copilot, etc.) and human developers working in this repository.

The binding project rules live in **[.claude/CLAUDE.md](.claude/CLAUDE.md)** and **[.cursor/rules/](.cursor/rules/)**.
Read both before starting any non-trivial task.

---

## The Five Golden Rules

1. **Verify Before Saying It Works ("Run the thing")**:
   Never mark a task complete or state that something is fixed without command output proving it.
   Run `./scripts/verify.sh` (Pint, PHPUnit, Vite build). If a test failed or was skipped, explicitly state so.

2. **Schema Changes Go Through Migrations Only**:
   Never execute manual DDL against the database (no ALTER/CREATE from phpMyAdmin, tinker, or `DB::statement`
   outside a migration). Every table, column, index, and constraint change is a new file in `database/migrations/`.
   Never edit a migration that has already run on a shared environment; add a new one instead.

3. **Database Seeding Must Be 100% Idempotent**:
   Running `php artisan db:seed` twice must not create duplicates or fail on unique/foreign key constraints.
   Use `updateOrCreate` / `firstOrCreate` / `upsert` keyed on natural keys (slug, email, role name).
   Keep reference data (roles, categories, site settings) separate from demo fixtures (factories).

4. **Self-Improvement Loop (`tasks/lessons.md`)**:
   Whenever a bug is fixed, a merge conflict occurs, or a user correction is made, document the symptom,
   root cause, and prevention rule in `tasks/lessons.md`. Review this file before starting new work.

5. **Plain ASCII Punctuation in New Code and UI Strings**:
   Do not use smart curly quotes, em-dash, en-dash, or Unicode ellipsis in code, Blade views, or translations.
   Use standard plain ASCII quotes and hyphens.

---

## Project Overview

- **Product**: ruangstudi.id, a Learning Management System (courses, quizzes, assignments, gamification, payments).
- **Backend**: Laravel 12 (`laravel/framework ^12`), Fortify (auth, 2FA), Spatie Permission (roles:
  superadmin, admin, instructor, student), Hashids, Maatwebsite Excel, DomPDF, Midtrans.
- **Frontend**: Blade + Tailwind CSS 4 + Alpine.js, bundled with Vite. No public JSON API (only `routes/web.php`).
- **Branching**: GitFlow, see [docs/branching-strategy.md](docs/branching-strategy.md). `main` auto-deploys to production.

---

## Local Development Environment

- PHP: 8.4 locally via `mise.toml` (CI runs 8.3; do not use syntax newer than 8.3)
- Node.js: >= 20 (CI uses 20)
- Database: MySQL 8 via `docker-compose.yaml` on `localhost:3306`
- Mail: MailHog via `docker-compose.yaml` (SMTP `1025`, web UI `http://localhost:8025`)
- Tests: SQLite in-memory (configured in `phpunit.xml`), never the dev MySQL database
- App timezone: `UTC` (`config/app.php`); per-user display timezone lives in `users.timezone`
- Environment file: `.env` (keep `.env.example` up to date when adding a new variable)
- Dev server: `composer dev` (serve + queue + vite)

---

## Verification Commands

Run these before opening a pull request or finishing a task:

```bash
# All-in-one (same checks as CI)
./scripts/verify.sh        # or: composer verify

# Individual steps
vendor/bin/pint --test     # code style (run vendor/bin/pint to fix)
php artisan test           # PHPUnit, SQLite in-memory
npm run build              # Vite build

# Plain ASCII punctuation check on the files you changed
git diff --name-only develop... | xargs -r grep -nP '[\x{2014}\x{2013}\x{201C}\x{201D}\x{2018}\x{2019}\x{2026}]'
```
