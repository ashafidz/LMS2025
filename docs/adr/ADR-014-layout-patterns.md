# ADR-014: Shared layout patterns for every page

- **Status**: Accepted
- **Date**: 2026-11-06
- **Deciders**: Project owner

---

## Context and Problem Statement
After the soft UI (ADR-012) and the illustrations (ADR-013), each dashboard page still had its own page head, uneven card headers,
horizontal forms with tiny labels, random button colours, English headings and, on the learning page, a table of contents that pushed
the lesson below the first phone screen. The owner allowed any layout change **as long as no flow changes**.

## Decision
Shared patterns, all in `public/css/ruang-studi-dashboard.css` and a few Blade components.

- **Page head** (`components/dash/page-head`): breadcrumb, context line, `h1`, subtitle and an `actions` slot. `title`, `subtitle` and
  `context` are named slots (they may hold `{{ }}`); `crumbs` is an array of `[label, url|null]`; `__home` renders the house icon.
  No dark rule under it.
- **Card header** (`.card-header`): a flex row; `.card-header-right` is static and pushed right, so it cannot cover a long title.
- **Stacked form** (`.rs-form`): label above the field at every width; the page sits in a centred 1040 px column (`.rs-page-form` on the page wrapper) and fields fill the card; a row with two label/field pairs shows them side by side from 768 px.
  `.rs-form-section` groups a long form; `.rs-form-actions` holds Cancel (soft) then Save (primary), full width on phones with Save first.
  `.rs-grid-2` puts date pairs side by side from 768 px. Repeating rows (links, poll options) are soft cards.
- **Button hierarchy**: primary for the one main action, `btn-secondary` (soft) for the rest, `btn-outline-danger` for delete (solid
  danger only inside confirm dialogs), `btn-success` only for decisions such as Setujui. Never put `text-white` on a soft button.
- **Learning page** (`student/courses/show`): two columns from 992 px (about 68% / 32%, sticky table of contents); on phones the
  table of contents is a collapsed panel under the page head, so the lesson is in the first screen. The lesson header shows a type chip
  and the title once; the footer is Sebelumnya / Tandai Selesai / Selanjutnya; the discussion is its own card.
  Long names are cut visually only (CSS, never `Str::limit`): module names 3 lines, lesson names 2 lines, shown in full while that
  module is open or that lesson is current; the full name is also in `title`, and `overflow-wrap: anywhere` breaks names without spaces.
  Preview mode: all write actions are disabled in the view and refused on the server; the manager's own progress and points are not shown.
- **Mobile lists**: `.rs-table-cards` (ADR-012), and card rows with a chip for module and lesson lists.
- **Errors**: every lesson and module form prints `$errors` through `instructor/lessons/partials/_form-errors`.
- **Indonesian messages**: `lang/id/{validation,auth,passwords,pagination}.php`, with an `attributes` block of Indonesian field names.
  Existing per-form messages keep precedence.

## Hard rules about flows
Only HTML structure, CSS classes and visible wording may change. Never change routes, route names, HTTP methods, link targets,
redirects, form `name`/`value`/`required`/`accept`, hidden inputs, `@csrf`/`@method`, ids and `data-*` hooks read by scripts (for
example the learning page ids), controllers, models, policies, validation or what each role can see.
Before removing a class or id, grep for it in the view, `public/js`, `resources/js` and `@push('scripts')`.

## Deploy step (locale)
`.env.example` now says `APP_LOCALE=id`. The local `.env` and production are not edited by the code: set `APP_LOCALE=id` in `.env`,
then run `php artisan config:cache`. Without it the Indonesian files are not used. `phpunit.xml` forces `APP_LOCALE=id` for tests.

## Merge note (sync with `main` 1.6.4)
`main` changed `instructor/lessons/create-document.blade.php` and `edit-lessondocument.blade.php` (error output and a `?v=` file
version). They will conflict. Keep this branch's layout, add the 1.6.4 behaviour (the `?v=` link and preview), and drop 1.6.4's own
error block because `_form-errors` already covers it.

## Update 2026-11-06: bigger scale and new home pages

### Scale (dashboard only, CSS only)
The dashboard text and controls grew two steps. Sizes are tokens in `:root` of `ruang-studi-dashboard.css`: `--rs-fs-body` 18px,
`--rs-fs-small` 16px, `--rs-fs-xs` 14px, `--rs-fs-title` 42px, `--rs-fs-card` 26px, `--rs-fs-sec` 23px, `--rs-fs-row` 20px,
`--rs-fs-stat` 34px, buttons 52px (small 42px, large 58px), inputs 52px, card padding 32px, sidebar 300px. Phones (up to 767.98px) get about
one step: body 16px, title 28px, buttons 48px (small 40px), inputs 48px, card padding 20px. New CSS must use the tokens, not pixel sizes.
Laptops (768 to 1439.98px, e.g. 1280x720 and 1366x768) get one step less than the desktop scale: body 16px, small 14.5px, xs 13px, title 32px, card 21px, section 19px, row 17px, stat 28px, buttons 46px (small 38px, large 50px), inputs 46px, card padding 24px, sidebar 264px. 1440px and wider keep the desktop scale.
Tables stay one step smaller than body text (cells `--rs-fs-small`, headers `--rs-fs-xs` uppercase) so a table with many columns still fits
a 1366px laptop; a table of 7 or more columns may scroll inside `.table-responsive`, the page itself never scrolls sideways.
The public site and the sign-in pages keep their own scale. Jodit, SweetAlert (except button height), charts, the certificate editor and the
quiz timer are not scaled.

### Student home (`StudentDashboardController::index`)
All read-only, a fixed number of queries for any number of courses.
- **Next lesson** per course in "Lanjutkan belajar": first lesson (module order, then lesson order) not completed. One query for the lessons
  of those courses, one for completed ids, one for course points. A module locked by points shows "Modul berikutnya masih terkunci"
  instead of the title. The lock rule is `LessonAccess::meetsPointsRequirement()`, also used by `isModuleUnlocked()` and the learning page.
  Type names come from `Lesson::TYPE_LABELS` / `Lesson::typeLabel()`, shared with the learning page and the instructor lesson list.
- **Aktivitas poin**: the 5 latest `point_histories` (hidden when none).
- **Lencana berikutnya**: unowned active badges that have rules, `BadgeService::progressFor`, highest sum of current/target wins, ties to the
  lowest id (hidden when none apply).
- **Kursus untuk kamu**: 3 published courses the student is not enrolled in, categories of the enrolled courses first, then the newest
  (hidden when none).

### Instructor home (`InstructorDashboardController::index`)
Read-only, own courses only. "Perlu ditindaklanjuti" lists submissions with status `submitted` grouped per assignment (max 5, link to
`instructor.assignment.submissions`) and counts of courses waiting for review, rejected and draft. "Kursus saya" replaces the two old tables
(students, completed, not completed per course); "Belum ada siswa" replaces the completion bar when a course has no students. The side cards
"Diskusi terbaru" (5, not the instructor's own, not deleted) and "Ulasan terbaru" (3) are hidden when empty.

### Courses and modules on one page
`instructor/courses/index` is the one place for courses and their modules. One query set loads courses with modules, lesson counts and student
counts. A course's modules open in a panel (`aria-expanded`, `aria-controls`, region labelled by the course title); the Sortable for a panel is
created when it first opens, and the reorder URL sits in `data-reorder-url`. `ModuleController::index` and the redirects after store, update
and destroy go to `courses.index?open={hash}#course-{hash}`, so old links and breadcrumbs still work; `instructor/modules/index.blade.php` is gone.
Search and the status filter work in the browser only. The list adapts to the width of the list itself (container queries), not the screen,
because the 300px sidebar takes much of a 1366px screen.
Not done on purpose: loading modules by AJAX when a panel opens. It is not needed at today's data size. If an instructor ever has hundreds
of courses, that is the way out.

## Consequences
- New pages should start from these patterns instead of the template markup.
- Learning page JS gained small hooks (`is-current`, `data-lesson-type`, progress counts) but keeps every old id and endpoint.
