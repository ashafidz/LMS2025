# ADR-003: Static stylesheet for the public site

- **Status**: Superseded in part by ADR-004 and ADR-006
- **Date**: 2026-10-17
- **Deciders**: Project owner

---

## Context and Problem Statement
The public pages (home, catalog, course detail, about, contact, FAQ, testimonials, service detail, public profile)
are being redesigned (direction "Rak Buku"). They share `layouts/home-layout.blade.php` with the cart, checkout,
error and auth status pages, which still use Bootstrap markup. We need a styling approach for the new design that does
not break those pages.

---

## Decision Drivers
- `home-layout` is shared with pages that are not part of this redesign and still depend on Bootstrap 5.3 and the BootstrapMade `main.css`.
- No layout in the project uses Vite today.
- The change should be view/CSS/JS only, with no build step and no new packages.

---

## Considered Options
1. Option 1: Tailwind through Vite for the public pages. Tailwind preflight would reset styles that the Bootstrap-based pages rely on.
2. Option 2: Keep extending the BootstrapMade `main.css`. Hard to maintain, and it carries template filler styles.
3. Option 3: A static stylesheet `public/css/ruang-studi.css` with `rs-` prefixed classes, loaded after Bootstrap and `main.css`.

---

## Decision Outcome
Chosen option: **Option 3**, because it isolates the new design under the `rs-` prefix, needs no build step, and
re-points the template CSS variables (fonts and colors) so the out-of-scope pages follow the new look automatically.

### Positive Consequences
- Out-of-scope pages keep working and pick up the new header, footer, fonts and colors.
- No new dependencies.

### Negative Consequences / Trade-offs
- Bootstrap and `main.css` stay loaded until the remaining pages are restyled; after that they can be removed.
- The stylesheet is not purged or minified.

## Update 2026-10-29
The owner asked for no sans-serif fonts. Literata replaces Bitter and Atkinson Hyperlegible on the public pages and
the dashboard (one `--rs-serif` token for headings and body). PDFs (certificate, invoice, recap) keep their own fonts,
because dompdf and the certificate editor manage those.
