# ADR-013: Irasutoya illustrations (20 images)

- **Status**: Accepted
- **Date**: 2026-11-06
- **Deciders**: Project owner

---

## Context and Problem Statement
The soft UI (ADR-012) has many empty states, result pages and status pages with only an icon. The owner chose illustrations from
Irasutoya to make them friendlier for non-technical users.

---

## Source and licence
- Source: irasutoya.com by Takashi Mifune. Free for commercial use, no credit needed.
- At most 20 images per site or work for free commercial use. More needs permission or payment.
- No redistribution of the images as material, and not for logos or marks.
- These terms were taken from the site FAQ. Check them again before adding any image.

## Rules
- Decorative only: `alt=""`. The text next to the picture says the same thing.
- Web files are WebP in `public/images/illustrations/`. The PNG originals are kept outside the repo.
- Use them only through `<x-rs.illustration name="..." size="sm|md|lg|art">`. The component holds the width and height of each file
  and renders nothing for an unknown name. `tests/Feature/IllustrationTest.php` fails if the folder does not hold exactly these 20 files.
- Replace an image only under the same file name. If the size changes, update the map in the component.
- Never add a 21st image.

## The 20 files and where they are used
| File | Used in |
|---|---|
| rs-01-hero-belajar | Home hero |
| rs-02-tentang-tim | Home "Tentang Kami" and the About page header |
| rs-03-masuk | Sign in (art panel) |
| rs-04-daftar | Register (art panel) |
| rs-05-lupa-sandi | Forgot and reset password (art panel) |
| rs-06-verifikasi-email | Verify email |
| rs-07-cari-kursus | Student dashboard and "Kursus saya" with no course |
| rs-08-semua-selesai | Student dashboard when all courses are finished |
| rs-09-sertifikat-kosong | Certificates, empty |
| rs-10-transaksi-kosong | Transactions, empty |
| rs-11-keranjang-kosong | Cart, empty |
| rs-12-instruktur-mulai | Instructor courses, empty |
| rs-13-kuis-lulus | Quiz result, passed |
| rs-14-kuis-coba-lagi | Quiz result, not passed |
| rs-15-sertifikat-terbit | Certificates page, top card when there are certificates |
| rs-16-bayar-berhasil | Checkout success |
| rs-17-bayar-menunggu | Checkout pending and processing, instructor application under review |
| rs-18-bayar-gagal | Checkout failed and cancelled |
| rs-19-status-akun | Rejected or deactivated instructor, deactivated student |
| rs-20-tersesat | All error pages (400 to 504) |

## Flagged images
02, 08, 13, 14 and 15 show children or school uniforms, and 14 is a gloomy "try again". The owner chose to keep them for now. They
may be replaced later under the same file name, so no code depends on the picture content.

## Consequences
- The first view of the home page adds about 44 KB (the hero); the rest is lazy.
- Error pages stay light and never touch the database for these images.
