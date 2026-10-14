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

### [YYYY-MM-DD] [Issue Title]
- **Symptom**: [What went wrong or broke]
- **Root Cause**: [Why it happened at the architectural or code level]
- **Rule**: [Concrete prevention rule to prevent it from ever happening again]
