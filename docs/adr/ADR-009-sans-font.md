# ADR-009: Sans-serif font (Poppins) replaces Literata

- **Status**: Accepted
- **Date**: 2026-11-01
- **Deciders**: Project owner

---

## Context and Problem Statement
The owner dislikes serif fonts (the "tails" on letters) and wants a clean sans-serif like the Mac system font or
Poppins. Six fonts were compared on real screen snippets in `tasks/font-options-sans.html`: Literata (current),
Onest, Plus Jakarta Sans, Figtree, Instrument Sans, Albert Sans. Poppins was left out of that comparison because it is
very common.

Onest was applied first. After seeing it, the owner chose Poppins.

---

## Decision
Use **Poppins** (weights 400, 500, 700, 800 and italic 400) for the public site and the dashboard, loaded from Google
Fonts in the three layouts. Stack: `"Poppins", system-ui, -apple-system, "Segoe UI", sans-serif`.

The CSS token `--rs-serif` is renamed `--rs-font` because it is no longer a serif font. Sizes, weights and spacing are
unchanged.

---

## Consequences
- Poppins is wide and geometric, so text takes more horizontal space than Literata or Onest at the same size. Buttons,
  table headers and the dashboard sidebar were checked at 1280 and 375 px wide.
- In Poppins the capital I and lowercase l look almost the same; this rarely matters in course text.
- PDFs (certificate, invoice, recap) keep their own fonts, because dompdf and the certificate editor manage them.
- Supersedes the font part of ADR-003 (update 2026-10-29).

## Update 2026-11-01: font pairing
One font for everything was not enough, so the project now uses three roles, all without serifs (compared on real
screen snippets in `tasks/font-pairing-options.html`):
- Headings: **Poppins** (`--rs-font-head`).
- Body text, buttons, tables, forms: **Nunito Sans** (`--rs-font`), more comfortable for long reading, tall lowercase letters.
- Code, order codes, coupon codes: **JetBrains Mono** (`--rs-font-mono`), where 0/O and 1/l/I are easy to tell apart.
The token `--rs-font` stays the default, so any rule that was not moved to the heading font now uses Nunito Sans.

