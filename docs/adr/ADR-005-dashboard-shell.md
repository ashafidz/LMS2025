# ADR-005: Dashboard shell "Rak Buku" for all roles

- **Status**: Accepted
- **Date**: 2026-10-17
- **Deciders**: Project owner, Claude (Opus plan, Sonnet implementation)

---

## Context and Problem Statement
All 109 dashboard views (instructor, admin, superadmin, student) extend `layouts.app-layout`, which carried the
Guru Able frame: pcoded navbar and sidebar, page loader, Roboto font. The public site already moved to the
"Rak Buku" direction (ADR-003, ADR-004). The dashboard frame still looked like the purchased template, and the
content views carry their own Bootstrap 4 / Guru Able markup that cannot be rewritten in one step.

---

## Decision Drivers
- One visual identity across public site and dashboards.
- Menus a layperson (school and ministry staff) can read: plain Indonesian, grouped.
- Rewrite the frame once, rewrite the 109 page contents later in small phases.

---

## Considered Options
1. Keep the Guru Able frame until every page is rewritten.
2. Replace the frame now, keep page content CSS/JS, add a small bridge for shared parts.
3. Rewrite frame and all 109 pages together.

---

## Decision Outcome
Chosen option: **2**, because it gives every dashboard the new identity at once with a small, reviewable change.

- New frame in `layouts/app-layout.blade.php` with `public/css/ruang-studi-dashboard.css` and
  `public/js/ruang-studi-dashboard.js` (prefix `rs-dash`).
- Grouped table-of-contents menu per role in `resources/views/layouts/dashboard/nav-*.blade.php`, items built with
  `<x-dash.link>`.
- A bridge section in the CSS restyles page header, cards and primary buttons that every content view still uses.
- Content CSS/JS (Bootstrap 4, Guru Able `style.css`, Bootstrap 5 JS) stays until phase D.
- `@yield('dash-nav')` is an empty slot for per-course menus (phase D1).

### Positive Consequences
- All roles share one frame, fonts and colors; the old loader and pcoded scripts are gone.

### Negative Consequences / Trade-offs
- Pages look mixed (new frame, old content) until phase D.
- Bootstrap 4 and Bootstrap 5 JS are still both loaded.
- The bridge section must be deleted when the last content view is rewritten.
