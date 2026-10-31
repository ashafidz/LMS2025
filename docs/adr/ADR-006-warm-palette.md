# ADR-006: Warm color palette for the public site and dashboard shell

- **Status**: Accepted
- **Date**: 2026-10-29
- **Deciders**: Project owner

---

## Context and Problem Statement
The Detail Layanan page now uses new bookshelf illustrations (`public/images/layanan/`) in warm wood, cream and
terracotta tones. Next to them the blue-white-grey palette of the "Rak Buku" direction felt cold and disconnected.
Three palette options were compared side by side in `tasks/color-options.html`.

---

## Decision Drivers
- Visual harmony with the new illustrations.
- Readable text for lay users: every text/background pair meets WCAG contrast 4.5:1.
- One set of tokens shared by the public site and the dashboard shell, so a later change stays a token change.

---

## Considered Options
1. Option A: "Sekarang (biru)", keep the current blue palette.
2. Option B: "Hangat penuh (kayu dan terakota)", a fully warm palette taken from the illustrations.
3. Option C: "Campuran (navy tetap, suasana hangat)", keep navy as primary with warm surfaces.

---

## Decision Outcome
Chosen option: **Option B**, because the owner preferred it and it matches the illustrations.

Tokens were renamed from color names to role names (`--rs-navy` -> `--rs-primary`, `--rs-navy-d` -> `--rs-primary-d`,
`--rs-blue` -> `--rs-accent`, `--rs-steel` -> `--rs-muted`) because the new values are no longer navy or blue.

| Token | Value |
|---|---|
| `--rs-primary` | `#5a2a1e` |
| `--rs-primary-d` | `#3f1d15` |
| `--rs-accent` | `#a8492a` |
| `--rs-ink` | `#2b1d17` |
| `--rs-slate` | `#6b5a4e` |
| `--rs-muted` | `#9c8b7a` |
| `--rs-paper` | `#f7f1e8` |
| `--rs-line` | `#e6d9c6` |
| `--rs-shelf` | `#c9a27a` |
| `--rs-white` | `#ffffff` |
| `--rs-star` | `#b7791f` |
| `--rs-ok` / `--rs-ok-bg` | `#2e6b45` / `#e9f2ea` |
| `--rs-danger` / `--rs-danger-bg` | `#b42318` / `#fdecec` |
| `--rs-info` / `--rs-info-bg` | `#3f6670` / `#e7f0f1` (dashboard also `--rs-info-d` `#2f4e56`) |

### Positive Consequences
- The public site, the dashboard shell and the Bootstrap bridge in `ruang-studi-dashboard.css` (`btn-primary`,
  `btn-outline-primary`, `text-primary`, `bg-primary`, `badge-primary`, `btn-info`, `badge-info`, `alert-info`,
  link hover) all follow the tokens.
- The favicon uses the same colors.

### Negative Consequences / Trade-offs
- PDFs, email templates, Guru Able `style.css` and Bootstrap `success`, `warning`, `danger` and `secondary` colors are
  unchanged. Dashboard page content keeps some Guru Able colors until phase D.
- `--rs-danger` red is close in hue to the terracotta accent. Mitigation: error messages must keep an icon or a word
  such as "Gagal"; never signal an error by color alone.
