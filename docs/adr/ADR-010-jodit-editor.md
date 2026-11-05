# ADR-010: Self-hosted Jodit replaces TinyMCE from Tiny Cloud

- **Status**: Accepted
- **Date**: 2026-11-01
- **Deciders**: Project owner

---

## Context and Problem Statement
The lesson article editor loaded TinyMCE 6 from Tiny Cloud (`cdn.tiny.cloud`) with an API key written in the Blade
views. That makes the editor depend on an outside service: it can stop working if the service has trouble, if the free
usage limit is reached, or if the domain is not registered in the Tiny account. It was used on two pages only: create
article and edit article.

---

## Decision
Use **Jodit 4.18.12** (MIT licence), stored in `public/vendor/jodit` like Select2 (not added to `package.json`).

Options compared:
- Jodit (MIT): all needed features, close to what instructors already know.
- TinyMCE 6 self-hosted (MIT): least change, but version 6 no longer gets security updates.
- TinyMCE 7 self-hosted (GPL): the licence brings legal obligations for a commercial application.
- Quill 2 (BSD): weak table support.
- Tiptap (MIT): needs a hand-built toolbar, the most work.
- CKEditor 5: the free version is GPL. Trix: no tables.

Details:
- One shared partial, `instructor/lessons/partials/_article-editor-scripts.blade.php`, used by both pages.
- No "source code" (HTML) button: lay users should not have to read or type HTML.
- Pasted images stay inside the article as data URIs, limited to jpg, png and gif because `HtmlSanitizer` drops other
  data URI types. Uploading images to server storage is a possible later task.
- The server side is unchanged: `HtmlSanitizer::clean()` still cleans the content on save.
- Jodit's own `imagesExtensions` does not stop WebP from the image dialog, and the sanitizer would drop it silently.
  So the form checks before sending: only jpg/png/gif data URI images, at most 2 MB per image and about 6 MB for the
  whole article (the PHP `post_max_size` is 8 MB). A blocked save shows a plain-language message.
- When Jodit loads old content into the editor it already strips `<script>`, `on*` attributes and `javascript:` links,
  so legacy content from before the sanitizer cannot run code on the edit page.

---

## Consequences
- No more calls to Tiny Cloud and no API key in the code.
- Updating Jodit means copying `jodit.min.js` and `jodit.min.css` again from the npm package.
- Existing articles keep working: the stored HTML is the same format and is still sanitized.
- `HtmlSanitizer` allows the CSS Jodit uses to align images (`display`, `float`, `margin-*`, `vertical-align`;
  `display` needs `CSS.AllowTricky`) and sets `Attr.DefaultImageAlt` to empty, because HTMLPurifier otherwise copies
  the data URI into `alt`. Cache prefix bumped to `v2`. Images centred before this fix lost `margin-right` on save and
  must be re-centred once.
