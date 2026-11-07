# ADR-012: Soft rounded UI for the dashboard and the public site

- **Status**: Accepted
- **Date**: 2026-11-06
- **Deciders**: Project owner

---

## Context and Problem Statement
The owner supplied a reference of a soft, rounded interface and approved the mockup `tasks/redesign-mockup.html` (student dashboard,
instructor course list, create course form, public home, sign in). The "shelf" look of ADR-003 and the flat, square card look
of ADR-005 felt dated and hard-edged. Colours (ADR-008) and fonts (ADR-009) stay as they are.

---

## Decision
Change shape, depth and spacing only, through tokens and a few components.

- **Tokens** (both CSS files): `--rs-lavender`, `--rs-lavender-d`, `--rs-wash`, radii `--rs-radius-sm/md/lg/xl/pill`
  (10, 16, 24, 32 px, 999 px), `--rs-shadow-card`, `--rs-shadow-pop`, `--rs-shadow-primary`, `--rs-focus-ring`.
- **Dashboard** (`ruang-studi-dashboard.css`): white sidebar with icon tiles (`components/dash/link` takes an `icon` prop), a rounded
  main panel, white icon buttons in the top bar, and a Bootstrap bridge (cards, buttons, forms, tables, badges, tabs, pagination,
  progress, SweetAlert2) scoped under `.rs-dash`.
- **New components**: `.rs-ring`, `.rs-course-card` (`--blue`, `--lav`, `--amber`, `--dark`), `.rs-stat`, `.rs-tile`.
- **Opt-in `.rs-table-cards`**: a table becomes rows of cards (stacked cards under 768 px, labels from `data-label`). It is not global,
  because quiz results, recaps and DataTables would break. Only the instructor course list uses it.
- **Public site** (`ruang-studi.css`): floating white header, hero panel with CSS-only art, course cards with the cover inside the card,
  white rounded cards for sections, blue CTA panels, Valhalla footer with rounded top corners. Shelf-only decorations are removed
  (`--rs-shelf` stays as a colour token).
- **Plain layout** (sign in, register, password pages, status and error pages): a split frame with a CSS art panel.
  The art panel is `aria-hidden`; the login and register role choice stays a real radio group, shown as a segmented control.
- **Contrast rules** (B0.1 to B0.3):
  - Small white text sits on `--rs-primary` (#1266e4, hover `--rs-primary-d`), never on `--rs-accent` (4.31:1).
    `--rs-accent` is for non-text uses: active icon tile, focus rings, bars, rings, shadow colour, and large headings.
  - Avatar initials use the gradient `--rs-lavender-d` to `--rs-primary`.
  - `--rs-muted` (3.21:1 on white) is only for decoration, never for text that must be read. Use `--rs-slate` for such text.
  - Lavender and Shiny Trumpet cards carry dark text (`--rs-ink`).

---

## Consequences
- Supersedes the shelf look of ADR-003 and the flat card look of ADR-005. Colours (ADR-008) and fonts (ADR-009) are unchanged.
- All Guru Able content views get the new look through the bridge without editing each view. Pages that rely on vendor classes with
  `!important` may need local overrides later.
- Students and instructors see course status and question type as Indonesian names, not codes (`Course::STATUS_LABELS`, `Question::TYPE_LABELS`).

---

## Contrast pairs checked
Ratios come from `scratchpad/t9/contrast.py`, which reads the token values and badge colours from the CSS. Every pair needs at least 4.5:1.

```
 5.19:1  LULUS  Tombol primer (.btn-primary, rs-dash-btn, swal2-confirm, halaman aktif): putih di --rs-primary (#FFFFFF / #1266e4)
 7.58:1  LULUS  Tombol primer hover: putih di --rs-primary-d (#FFFFFF / #0e4fb1)
 5.19:1  LULUS  Kartu kursus biru: putih di --rs-primary (#FFFFFF / #1266e4)
 5.54:1  LULUS  Kartu kursus lavender: --rs-ink di --rs-lavender (#292940 / #C08CEE)
 7.20:1  LULUS  Kartu kursus kuning: --rs-ink di --rs-shelf (#292940 / #EEAC5C)
14.14:1  LULUS  Kartu kursus gelap: putih di --rs-ink (#FFFFFF / #292940)
14.14:1  LULUS  Kartu samping: putih di --rs-ink (#FFFFFF / #292940)
14.14:1  LULUS  Angka persen di kartu: --rs-ink di putih (#292940 / #FFFFFF)
 4.58:1  LULUS  Tombol lunak: --rs-primary di --rs-paper (#1266e4 / #EEF1F6)
 6.06:1  LULUS  Tombol lunak hover: --rs-primary-d di --rs-line (#0e4fb1 / #E2E6EE)
 5.19:1  LULUS  Menu aktif: --rs-primary di putih (#1266e4 / #FFFFFF)
 6.38:1  LULUS  Menu biasa: --rs-slate di putih (#5c5d78 / #FFFFFF)
 4.58:1  LULUS  Menu hover: --rs-primary di --rs-paper (#1266e4 / #EEF1F6)
 6.38:1  LULUS  Judul grup menu: --rs-slate di putih (#5c5d78 / #FFFFFF)
 6.38:1  LULUS  Header tabel: --rs-slate di putih (#5c5d78 / #FFFFFF)
14.14:1  LULUS  Teks kartu tabel: --rs-ink di putih (#292940 / #FFFFFF)
 5.10:1  LULUS  Avatar inisial (sisi gelap lavender): putih di --rs-lavender-d (#FFFFFF / #8a4fd1)
 5.19:1  LULUS  Avatar inisial (sisi biru): putih di --rs-primary (#FFFFFF / #1266e4)
 5.19:1  LULUS  Peran pengguna di menu: --rs-primary di putih (#1266e4 / #FFFFFF)
 5.63:1  LULUS  Tab nonaktif: --rs-slate di --rs-paper (#5c5d78 / #EEF1F6)
 5.19:1  LULUS  Tab aktif / radio terpilih: --rs-primary di putih (#1266e4 / #FFFFFF)
 5.63:1  LULUS  Radio tak terpilih: --rs-slate di --rs-paper (#5c5d78 / #EEF1F6)
 6.38:1  LULUS  Statistik label: --rs-slate di putih (#5c5d78 / #FFFFFF)
14.14:1  LULUS  Cincin progres: --rs-ink di putih (#292940 / #FFFFFF)
13.77:1  LULUS  Isi kolom: --rs-ink di --rs-wash (#292940 / #FBFCFE)
 7.96:1  LULUS  Alert danger (#8c1d1d / #FDECEC)
 5.71:1  LULUS  Badge success (#1d6b3d / #E3F4EA)
 5.68:1  LULUS  Badge warning (#8a5200 / #FDF0DC)
 5.63:1  LULUS  Badge secondary (#5c5d78 / #EEF1F6)
 6.66:1  LULUS  Badge info (#0e4fb1 / #E8F1FF)
 6.66:1  LULUS  Badge primary (#0e4fb1 / #E8F1FF)
 8.82:1  LULUS  [C] Footer: teks #c9cbe0 di --rs-ink (#c9cbe0 / #292940)
14.14:1  LULUS  [C] Footer: tautan putih di --rs-ink (#FFFFFF / #292940)
 5.19:1  LULUS  [C] Panel seni masuk/daftar: putih di --rs-primary (#FFFFFF / #1266e4)
 5.19:1  LULUS  [C] Pita CTA: putih di --rs-primary (#FFFFFF / #1266e4)
 6.66:1  LULUS  [C] Pil: --rs-info di --rs-info-bg (#0e4fb1 / #E8F1FF)
 5.63:1  LULUS  [C] Fitur hero (ticks): --rs-slate di --rs-paper (#5c5d78 / #EEF1F6)
 6.38:1  LULUS  [C] Kartu mengambang: --rs-slate di putih (#5c5d78 / #FFFFFF)
 4.58:1  LULUS  [C] Tautan .rs-link: --rs-primary di --rs-paper (#1266e4 / #EEF1F6)
 5.19:1  LULUS  [C] Harga kursus: --rs-primary di putih (#1266e4 / #FFFFFF)
 5.19:1  LULUS  [C] Tombol ghost: --rs-primary di putih (#1266e4 / #FFFFFF)
 4.58:1  LULUS  [C] Tombol ghost hover: --rs-primary di --rs-paper (#1266e4 / #EEF1F6)
 5.19:1  LULUS  [C] Tombol putih di pita biru: --rs-primary di putih (#1266e4 / #FFFFFF)
 4.56:1  LULUS  [C] Menu utama aktif: --rs-primary di --rs-info-bg (#1266e4 / #E8F1FF)
 6.38:1  LULUS  [C] Menu utama biasa: --rs-slate di putih (#5c5d78 / #FFFFFF)
 5.19:1  LULUS  [C] Segmen masuk terpilih: --rs-primary di putih (#1266e4 / #FFFFFF)
 5.63:1  LULUS  [C] Segmen masuk tak terpilih: --rs-slate di --rs-paper (#5c5d78 / #EEF1F6)
13.77:1  LULUS  [C] Input: --rs-ink di --rs-wash (#292940 / #FBFCFE)
 5.63:1  LULUS  [C] Salin kaki halaman polos: --rs-slate di --rs-paper (#5c5d78 / #EEF1F6)
 5.19:1  LULUS  [C] Kartu kursus tanpa foto: putih di #1266e4 (rs-cover--c0) (#FFFFFF / #1266e4)
14.14:1  LULUS  [C] Kartu kursus tanpa foto: putih di #292940 (c1) (#FFFFFF / #292940)
 6.20:1  LULUS  [C] Kartu kursus tanpa foto: putih di #73548e (c2) (#FFFFFF / #73548e)
 7.58:1  LULUS  [C] Kartu kursus tanpa foto: putih di #0e4fb1 (c3) (#FFFFFF / #0e4fb1)
10.83:1  LULUS  [C] Kartu kursus tanpa foto: putih di #3a3a5c (c4) (#FFFFFF / #3a3a5c)
 5.97:1  LULUS  [C] WhatsApp: putih di #0e7064 (#FFFFFF / #0e7064)
gagal: 0
judul hero besar (46 px): --rs-accent di putih 4.31:1, butuh 3:1 untuk teks besar
```

Large heading exception: the hero title highlight uses `--rs-accent` on white at 46 px (4.31:1, above the 3:1 needed for large text).
