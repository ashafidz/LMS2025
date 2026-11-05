# ADR-008: Blue palette for the public site and dashboard shell

- **Status**: Accepted
- **Date**: 2026-11-01
- **Deciders**: Project owner

---

## Context and Problem Statement
The owner chose a new six-color palette to replace the warm wood and terracotta palette of ADR-006:
`#FBFCFE` Wash Me, `#E2E6EE` Ghostly Tuna, `#EEAC5C` Shiny Trumpet, `#C08CEE` Luminous Lavender,
`#1572FE` Hadfield Blue and `#292940` Valhalla.

---

## Decision
Keep the role-based tokens of ADR-006 and change only their values. Text pairs must keep WCAG contrast 4.5:1.

| Token | New value | Use |
|---|---|---|
| `--rs-primary` | `#1266e4` | buttons, links, bands |
| `--rs-primary-d` | `#0e4fb1` | hover |
| `--rs-accent` | `#1572FE` | focus outline, progress, tabs (non-text) |
| `--rs-ink` | `#292940` | main text, footer |
| `--rs-slate` | `#5c5d78` | secondary text |
| `--rs-muted` | `#8a8fa3` | input borders (non-text) |
| `--rs-paper` | `#EEF1F6` | hero, page header, dashboard background |
| `--rs-line` | `#E2E6EE` | lines and borders |
| `--rs-shelf` | `#EEAC5C` | thick shelf lines |
| `--rs-white` | `#FBFCFE` | public page background |
| `--rs-info` / `-d` / `-bg` | `#0e4fb1` / `#0c4498` / `#E8F1FF` | info alerts and buttons |
| `--rs-select` | `#F3EAFD` | selected item background |

Three deliberate departures from the owner's hex values, all for contrast:
- Text on blue uses `#1266e4` (5.19 on white, 5.06 on Wash Me, 4.58 on `#EEF1F6`) instead of `#1572FE` (4.31 on white). `#1572FE` stays for non-text elements.
- `#EEAC5C` and `#C08CEE` cannot hold white text (1.96 and 2.55), so they are decoration or tinted backgrounds only.
- Section background `#EEF1F6` sits between Wash Me and Ghostly Tuna, because Wash Me is almost white and cards would
  not stand out.

---

## Consequences
- Public site, dashboard shell and the Bootstrap bridge follow the tokens. Status colors, star rating, WhatsApp button,
  PDFs, certificates and emails are unchanged.
- The bookshelf illustrations in `public/images/layanan/` keep their warm wood tones and may look less matched.
- `#1266e4` text on Ghostly Tuna `#E2E6EE` is 4.15:1; avoid small link text on that background.
