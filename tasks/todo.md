# Active Task Plan

## Task: [Short Task Title]

- Status: In Progress
- Started: [Date]
- Branch: [feature/... from develop]
- Target: [Goal description]

---

### Plan Checklist

- [ ] 1. Analysis and root cause diagnosis (review `tasks/lessons.md` first)
- [ ] 2. Implementation: migrations / models (if schema changes)
- [ ] 3. Implementation: controllers, services, Blade views
- [ ] 4. Tests: feature/unit tests for the change
- [ ] 5. Verification: `./scripts/verify.sh`
- [ ] 6. Self-review, `CHANGELOG.md`, and `tasks/lessons.md` update

---

### Verification Proof

- Command: `vendor/bin/pint --test` -> [Output summary]
- Command: `php artisan test` -> [Output summary]
- Command: `npm run build` -> [Output summary]

---

## Backlog: gaps found while adopting the engineering standards (2026-10-14)

- [x] `database/seeders/RoleSeeder.php` uses `Role::create()`: second `db:seed` fails on the unique role name. Switch to `Role::firstOrCreate(['name' => ...])`.
- [x] `database/seeders/CourseCategorySeeder.php` uses `CourseCategory::create()`: duplicates or unique error on re-seed. Switch to `updateOrCreate(['slug' => ...], [...])`.
- [x] `vendor/bin/pint --test` reports 132 style issues in 232 files (2026-10-14), and `.github/workflows/ci.yml` hides this with `|| echo ...`. Run `vendor/bin/pint` in a dedicated style-only PR, then remove the CI fallback.
- [x] Test suite only has the example tests. Add feature tests for the critical flows (enrollment, quiz attempt, payment callback).
- [x] Non-ASCII punctuation in `resources/views/student/quizzes/take.blade.php`, `resources/views/superadmin/point-sync/mass-sync-preview.blade.php`, and one migration. Clean up when those files are next touched.
- [x] `README.md` says Laravel 11.x; `composer.json` requires `laravel/framework ^12`.

---

## Status per 2026-10-14 (akhir sesi)

### Branch dan commit (semua lokal, belum ada yang di-push)
- `feature/engineering-standards` -> `f7b90a7` standar kit (AGENTS.md, .claude, .cursor, tasks, scripts/verify.sh)
- `fix/gap-remediation` -> `6a6df1a` hasil `tasks/plan-gap-remediation.md` (pembayaran, otorisasi, diamond, seeder, tests, Pint seluruh repo)
- `fix/coupon-pricing` (aktif) -> BELUM di-commit: hasil `tasks/plan-bugfix-round2.md` + fix review `chunkById`.
  Sudah direview Opus dan layak commit. Verifikasi terakhir: Pint PASS, 76 tests passed (288 assertions), build PASS.

### Langkah berikutnya
- [ ] Commit `fix/coupon-pricing`
- [ ] Putuskan alur merge ke `develop` (urutan: engineering-standards -> gap-remediation -> coupon-pricing; tiap branch dibuat dari branch sebelumnya)
- [ ] Push dan buka PR (setelah diputuskan)

### Wajib manual sebelum deploy ke production
- [ ] Midtrans dashboard: Payment Notification URL = `https://<domain>/midtrans/notification`
- [ ] Cron cPanel: `* * * * * cd /path && php artisan schedule:run >> /dev/null 2>&1` (untuk `orders:sync-pending`)
- [ ] Uji sandbox: bayar VA setelah menutup popup Snap -> order jadi `paid`; kupon khusus kursus dengan 2 kursus di keranjang -> total keranjang = nominal Snap
- [ ] Uji akses: siswa lain membuka `/orders/<hash>/success` -> 403; instruktur B membuka edit modul instruktur A -> 403

### Backlog (kecil, belum dijadwalkan)
- [ ] Halaman keranjang belum cek ulang kupon yang kedaluwarsa setelah dipasang (checkout sudah menolak dengan pesan jelas).
- [ ] Kupon dihapus admin saat sedang dipakai: pesan di keranjang salah ("kursusnya tidak ada lagi di keranjang").
- [ ] Order pending menahan kuota kupon per pengguna selama 24 jam (sesuai desain; dokumentasikan untuk tim CS).
- [ ] File tugas siswa masih di disk `public`; pindah ke disk privat dengan download terkontrol.
- [ ] Pesan flash campur bahasa Inggris dan Indonesia.
- [ ] `schedule:list` lokal butuh MySQL hidup (cache lock `withoutOverlapping`).
