# ADR-002: Remove the BootstrapMade template from public, shop, account and error pages

- **Status**: Accepted
- **Date**: 2026-10-17
- **Deciders**: Project owner

---

## Context and Problem Statement
The BootstrapMade template license requires a "Designed by BootstrapMade" credit for as long as its files are used.
After ADR-001 the public pages already had their own look ("Rak Buku"), but every page still loaded the template
`main.css`, `validate.js`, Bootstrap CSS/JS and template images, so the credit had to stay. Cart, checkout, error pages
and the sign-in pages still used Bootstrap markup or the Guru Able admin template. The Guru Able template is now only
needed on the dashboards.

---

## Decision Drivers
- Remove the credit legally by not loading any BootstrapMade file.
- One consistent look for everything a visitor or student sees before the dashboard.
- Error and sign-in pages must still render when the database is down.
- No build step and no new packages.

---

## Considered Options
1. Keep Bootstrap CSS and only drop `main.css`.
2. Make `ruang-studi.css` standalone with a small reset, and move the remaining pages to it.
3. Introduce Tailwind or a Vite build.

---

## Decision Outcome
Chosen option: **2**, because it removes the template with the least risk and no tooling change.

- `public/css/ruang-studi.css` is standalone and starts with a small reset that replaces Bootstrap reboot.
- Bootstrap Icons v1.13.1 (MIT) are copied to `public/vendor/bootstrap-icons`.
- Bootstrap modals are replaced by the native `<dialog>` element, driven by `public/js/ruang-studi.js`.
- A new `layouts/plain-layout.blade.php` serves error pages, account status pages and sign-in pages. It runs no
  database query of its own, so 500 and 503 pages still render.
- The favicon is `public/favicon.svg`. `public/home-page/` is deleted (files remain in git history).

### Positive Consequences
- The BootstrapMade credit is gone and no BootstrapMade file is loaded.
- Public pages no longer download Bootstrap CSS/JS or Font Awesome 4.7.

### Negative Consequences / Trade-offs
- Dashboards still use Guru Able until the dashboard phases.
- `public/js/csrf-refresh.js` calls jQuery `$` and throws on pages without jQuery; it is still open and will be fixed separately.
- Fortify status messages (for example "We have emailed your password reset link.") are still English.
