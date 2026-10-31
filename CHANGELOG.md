# Changelog

All notable changes to this project will be documented in this file.

## [1.6.2] - 2026-10-31

### Added

- **Pengaturan Tautan Video Beranda**: Tautan tombol "Lihat video" pada bagian awal (hero) beranda kini dapat dikonfigurasi melalui menu Pengaturan Situs yang dapat diakses oleh Admin dan Superadmin. Jika kolom dikosongkan, tombol "Lihat video" otomatis disembunyikan di beranda publik.

## [1.6.1] - 2026-10-31

### Fixed

- **Kontras Tombol Kuning**: Seluruh tombol dan lencana kuning (btn-warning, badge-warning) kini memakai teks gelap (#212529) agar terbaca jelas dan kontras, menggantikan teks putih yang sulit terbaca.
- **Pemberitahuan Kursus Diperbarui pada Halaman Sertifikat**: Bila instruktur menambahkan materi baru ke kursus yang sertifikatnya sudah pernah diterbitkan, kartu sertifikat siswa di halaman Sertifikat Saya menampilkan pemberitahuan bahwa kursus telah diperbarui dan menyediakan tombol Lanjutkan Kursus untuk menyelesaikan materi baru sebelum sertifikat dapat diunduh kembali. Pesan galat unduhan langsung juga diperjelas.

## [1.6.0] - 2026-10-31

### Security

- Memperbarui dependensi `symfony/mime` ke versi 7.4.22 untuk menutup celah keamanan CVE-2026-45070 (injeksi header email).

### Changed

- Dasbor semua peran (instruktur, admin, superadmin, siswa) memakai kerangka baru: menu daftar isi berkelompok, bar atas, serta huruf dan warna Ruang Studi. Isi halaman dasbor belum berubah.
- Judul modal detail pengumpulan tugas instruktur kini menyertakan nomor induk/NRP siswa ("Detail Pengumpulan: [NRP] - [Nama Siswa]").
- Dasbor semua peran kini memakai huruf Literata (serif) dan warna hangat yang sama dengan halaman publik.
- Ikon situs (favicon) memakai warna baru.
- Menu samping dasbor kini juga berisi "Profil saya", "Ke halaman depan", dan "Keluar" (grup Profil), sama seperti menu di nama pengguna.
- Gambar di halaman Detail Layanan diganti dengan ilustrasi baru bertema rak buku (gambar template lama dihapus).
- Beranda (bagian Tentang Kami) dan halaman Tentang kini memakai ilustrasi bertema rak buku, menggantikan kotak ikon sementara.
- Beranda kembali punya dua ajakan: "Kenali Lebih Dekat" (ke halaman Tentang) setelah Layanan Kami, dan "Cari Kursus Lainnya?" (ke katalog) setelah kursus populer.
- Tampilan halaman publik diperbarui (beranda, katalog, detail kursus, tentang, kontak, FAQ, testimoni, detail layanan, profil publik): huruf Literata (serif) untuk judul dan isi, warna hangat (krem, cokelat kayu, terakota), kursus ditampilkan seperti sampul buku.
- Template BootstrapMade dihapus dari halaman publik, keranjang, checkout, halaman galat, halaman status akun, login, dan daftar. Kredit "Designed by BootstrapMade" dihapus, folder `public/home-page` dihapus, dan halaman-halaman tersebut memakai tampilan Rak Buku yang sama (modal diganti dialog bawaan peramban).

### Fixed

- Form ulasan kursus kini ditutup dengan pesan "Ulasan Belum Bisa Diisi" selama admin belum membuat pertanyaan penilaian (Likert), sehingga siswa tidak lagi melihat galat "The likert answers field is required". Setiap pertanyaan penilaian yang aktif wajib dijawab.
- Kartu kursus berbayar diamond tidak lagi tampil sebagai Gratis di katalog dan detail kursus.
- Angka peserta 0 tidak lagi ditampilkan sebagai 0+ Siswa.
- Teks halaman galat 400 dan 404 yang tertukar sudah diperbaiki.
- Pesan galat kupon di keranjang kini terlihat (sebelumnya tidak tampil).
- Label PPN di keranjang kini memakai persentase asli dari pengaturan, bukan selalu 11%.
- Kupon potongan tetap kini tampil sebagai "Potongan Rp..." (sebelumnya selalu "Diskon ...%").
- Ringkasan pesanan di checkout kini menampilkan PPN dan Biaya Layanan sehingga rinciannya sesuai total.
- Snap.js memakai alamat produksi saat `midtrans.is_production` aktif (sebelumnya selalu sandbox).
- Waktu di halaman pembayaran berhasil kini memakai zona waktu pengguna, bukan jam UTC yang diberi label WIB.
- Kolom alamat website di pendaftaran instruktur tidak lagi tampil sebagai opsional padahal wajib.
- Status "tautan atur ulang kata sandi dikirim" kini terlihat di halaman lupa kata sandi (sebelumnya tersembunyi di modal).

## [1.5.2] - 2026-10-26

### Fixed

- **Pratinjau Pengumpulan Tugas**: Pratinjau PDF tugas siswa pada halaman instruktur kini dimuat secara bertahap (lazy load) saat modal dibuka, bukan serentak saat halaman pertama kali dibuka, untuk mencegah lonjakan koneksi dan error 508 (Resource Limit Is Reached) pada hosting server.
- **Optimasi Query Pengumpulan**: Eager loading relasi assignment pada daftar pengumpulan tugas untuk mengeliminasi query N+1 saat mengecek status keterlambatan tugas.

## [1.5.1] - 2026-10-25

### Added

- **Cek Keaslian Sertifikat**: Halaman publik `/verifikasi-sertifikat` (tautan "Cek Keaslian Sertifikat" di bagian bawah situs). Siapa pun, misalnya sekolah atau pemberi kerja, bisa memasukkan kode sertifikat untuk memastikan sertifikat benar diterbitkan Ruang Studi. Bila kode ditemukan, halaman menampilkan nama penerima, kursus, instruktur, dan tanggal terbit (sama seperti yang tercetak di sertifikat); email dan data pribadi lain tidak ditampilkan. Pengecekan dibatasi 30 kali per menit agar kode tidak bisa ditebak beruntun. Kode juga bisa dikirim lewat tautan, misalnya `/verifikasi-sertifikat?kode=LMS-...`.

## [1.5.0] - 2026-10-25

### Security

- **Kursus Diamond dan Draft Tidak Bisa Didapat Gratis**: Kursus yang belum terbit, kursus berbayar diamond, dan kursus yang sudah dihapus tidak lagi bisa dimasukkan ke keranjang atau di-checkout. Item seperti itu otomatis dikeluarkan dari keranjang dengan pesan jelas, dan pembelian dengan diamond hanya berlaku untuk kursus yang sudah terbit.
- **Kunci Modul Tidak Bisa Dilewati**: Menambah `?preview` di alamat halaman tidak lagi membuka modul yang terkunci poin. Tandai selesai, kuis, tugas, polling, word cloud, dan diskusi juga menghormati kunci modul.
- **Akses Kursus Instruktur Lain**: Instruktur hanya bisa melihat dan mempratinjau kursusnya sendiri, tidak lagi semua kursus.
- **Sertifikat dan Diamond**: Lesson kuis dan tugas tidak bisa lagi ditandai selesai manual. Kuis selesai hanya jika lulus, tugas hanya lewat pengumpulan tugas, sehingga sertifikat dan konversi diamond tidak bisa didapat tanpa menyelesaikannya.
- **Ulasan Kursus**: Ulasan kursus dan instruktur hanya bisa dikirim oleh siswa yang terdaftar di kursus tersebut.
- **Screenshot Kamera Kuis**: Screenshot bukti pelanggaran divalidasi (hanya gambar, maksimal 1 MB) dan pengiriman log pelanggaran dibatasi 30 kali per menit.
- **Deploy**: Folder internal (tasks, docs, konfigurasi AI, scripts) tidak lagi dikirim ke server, kode maintenance dipindah ke GitHub secret `MAINTENANCE_SECRET`, CI memakai Node 22 dan menjalankan `composer audit`.

- **Batas Waktu Kuis Ditegakkan Server**: Submit setelah batas waktu (toleransi 120 detik) dinyatakan tidak lulus kecuali kuis mengizinkan jawaban terlambat. Jadwal "tersedia dari/sampai" kini juga dicek saat memulai kuis. Status "dikeluarkan karena pelanggaran" dihitung dari log server, tidak lagi dari isian browser.
- **File Privat**: Dokumen lesson, file tugas siswa, dan screenshot pelanggaran kuis kini disimpan privat dan hanya bisa dibuka lewat aplikasi oleh yang berhak (siswa terdaftar, pemilik kursus, admin). Perintah `php artisan files:move-private` memindahkan file lama. Video lesson tetap publik.
- **Penyaringan HTML**: Isi artikel dan deskripsi kursus dibersihkan dari script dan atribut berbahaya saat disimpan dan saat ditampilkan.
- **Form Kontak**: Perlindungan spam reCAPTCHA kini benar-benar aktif di production (sebelumnya kunci dibaca dengan `env()` yang kosong setelah `config:cache`, sehingga pemeriksaan terlewati). Pengirim tidak bisa lagi menandai pesannya sendiri sebagai sudah dibaca/dibalas, isi pesan dibatasi 5000 karakter, Google yang tidak bisa dihubungi tidak lagi menyebabkan error, dan pengunjung melihat pesan error singkat (bukan seluruh halaman). Tambahkan `RECAPTCHA_SITE_KEY` dan `RECAPTCHA_SECRET_KEY` di `.env` production bila belum ada.
- **Dependency**: Seluruh dependency PHP dan Node diperbarui sehingga `composer audit` dan `npm audit` bersih (Laravel 12.70, Fortify 1.41, dompdf, symfony, vite, dll).

### Added

- **Template Sertifikat**: Superadmin dan admin kini punya menu **Template Sertifikat** untuk membuat desain sertifikat sendiri. Unggah gambar latar (PNG/JPG), lalu seret Nama Siswa, Judul Kursus, Nama Instruktur, Kode Sertifikat, Tanggal Terbit, dan Teks Bebas ke posisi yang diinginkan, atur jenis huruf, ukuran, warna, dan perataan. Tombol **Pratinjau PDF** menampilkan hasil asli sebelum disimpan.
- **Versi Template**: Setiap simpan membuat versi baru (versi lama tidak hilang dan bisa diaktifkan kembali). Satu versi dipilih sebagai **Aktif**; sertifikat yang diunduh siswa selalu memakai versi aktif terbaru, dengan kode dan tanggal terbit yang sama. Tombol **Kembali ke Desain Bawaan** tersedia.
- Lima pilihan huruf: Helvetica, Times, Montserrat, Playfair Display, dan Great Vibes (lisensi SIL OFL, `public/fonts/certificates/OFL.txt`). Nama yang terlalu panjang otomatis dikecilkan agar muat.
- Dokumentasi keputusan: `docs/adr/ADR-001-certificate-templates.md`. Migrasi baru: `certificate_templates`.
- Gambar latar: foto dari HP (JPEG dengan tag rotasi) otomatis ditegakkan; gambar lebih dari 6000 piksel atau 25 megapiksel ditolak dengan pesan yang jelas. Pratinjau PDF bisa dibuat sebelum desain diberi nama.

- **Badge Berbasis Aturan**: Superadmin dan admin kini bisa membuat badge dan mengatur syaratnya lewat form, tanpa kode. Satu badge punya 1 sampai 3 syarat yang semuanya harus terpenuhi, contoh: "Lulus kuis minimal 10 kuis di semua kursus". Ukuran yang tersedia: lulus kuis, nilai kuis sempurna, menyelesaikan pelajaran, mendapat sertifikat, lulus tugas, mengumpulkan poin, menulis ulasan, aktif berdiskusi, dan mengikuti kursus, dengan cakupan semua kursus, kursus tertentu, atau kategori tertentu.
- Tombol **Hitung siswa yang memenuhi** menampilkan berapa siswa yang sudah memenuhi syarat sebelum badge disimpan, tombol **Berikan ke siswa** memberi badge ke siswa yang sudah memenuhi, dan **Duplikat** membuat salinan badge dengan syarat yang bisa diubah. Syarat dikunci setelah ada siswa yang mendapat badge.
- Siswa melihat progres yang benar di halaman Badge Saya (contoh "7 dari 10 kuis"), tanggal badge didapat, dan notifikasi "Selamat! Kamu mendapat badge baru" satu kali. Badge diperiksa otomatis di latar belakang setiap kali progres siswa berubah, dan sekali sehari lewat `php artisan badges:award`.
- Tombol **Hitung siswa yang memenuhi** dan **Berikan ke siswa** memakai satu query untuk semua siswa (bukan satu per satu), sehingga tetap cepat di hosting bersama walau siswanya banyak.
- Dokumentasi keputusan: `docs/adr/ADR-002-rule-based-badges.md`. Migrasi baru: `badge_rules` dan kolom `badge_user.seen_at`. Menu Badge juga tersedia untuk admin.

- Pratinjau sertifikat siswa tetap tampil di browser lama (Safari sebelum versi 15 menampilkan kanvas kosong, Safari 15 memakai ukuran huruf yang salah); ukuran huruf dihitung dari lebar kanvas sebenarnya.
- Pratinjau desain sertifikat dengan gambar latar yang rusak, atau dari versi yang gambar latarnya hilang di server, kini menyebutkan penyebabnya (sebelumnya hanya "Pratinjau gagal dibuat. Coba lagi."). Gambar latar yang gagal disimpan tidak lagi tertinggal di server, dan perintah harian `php artisan certificates:prune-files` membersihkan gambar latar tak terpakai serta sisa file pratinjau.

- **Luluskan Kuis yang Terlambat**: Di halaman detail monitoring kuis, instruktur kini bisa meluluskan percobaan yang gagal hanya karena dikirim terlambat (misalnya internet siswa putus), dengan alasan wajib diisi, bila nilainya mencapai batas lulus. Siswa mendapat tanda selesai dan poin kuis seperti lulus biasa; siapa, kapan, dan alasannya tercatat. Status terlambat dicatat saat siswa mengirim kuis, sehingga mengubah batas waktu kuis sesudahnya tidak mengubah hasil. Migrasi baru: kolom `failed_by_deadline` dan `late_override_*` di `quiz_attempts`.

- Perintah `php artisan lms:audit-quiz-completions`: laporan (hanya baca) lesson kuis yang ditandai selesai tanpa attempt lulus.
- Perintah `php artisan files:move-private [--dry-run]`.

### Changed

- Badge "Lulus 10 Kuis" yang sudah ada dipindahkan otomatis ke sistem aturan baru (pemilik lama tetap memilikinya). Kuis yang sama yang dilulusi berulang kali kini dihitung satu kali.
- Syarat badge "nilai kuis sempurna" menghitung skor revisi instruktur terhadap skor maksimum kuis (skor revisi adalah skor mentah, bukan 0-100).
- Ikon badge menerima PNG, JPG, atau WEBP maksimal 1 MB. SVG tidak lagi diterima karena bisa memuat script.
- Unduhan sertifikat siswa memakai template aktif; tanpa template aktif tampilannya sama seperti sebelumnya. PDF lama sertifikat yang sama dihapus saat desain berganti.

### Fixed

- Lulus kuis kini menandai lesson selesai walaupun poinnya sudah pernah diberikan sebelumnya.

## [1.4.0] - 2026-10-21

### Added

- **Student Management Search**: Halaman Manajemen Student (admin dan superadmin) kini memiliki kotak pencarian berdasarkan nama, email, atau NIM/NIP/NIDN, lengkap dengan tombol Reset dan pesan jelas saat tidak ada hasil.
- **Load Test Tooling**: Skrip uji beban lokal `scripts/loadtest/run.sh` (k6, simulasi 150 siswa acak dengan batas mirip hosting: 1 core CPU, 20 proses PHP) dan `scripts/loadtest/race.sh` (20 request serentak dari satu siswa), beserta `LoadTestSeeder` yang menolak berjalan di production.

### Changed

- **Student Management Pagination**: Daftar student kini memakai pagination bernomor bergaya Bootstrap (10 per halaman) dengan keterangan "Menampilkan X-Y dari N student". Kata kunci pencarian tetap terbawa saat berpindah halaman.
- **Document Preview Modal**: Modal pratinjau pelajaran Dokumen (PDF) di halaman Kelola Pelajaran instruktur kini lebih lebar (90% layar di desktop) dan viewer PDF lebih tinggi (70% tinggi layar), juga pada tampilan dokumen di halaman belajar siswa.

### Performance

- **Concurrent Users**: Mengurangi beban server agar lebih tahan saat banyak siswa aktif bersamaan (batas 20 proses hosting). Query per halaman siswa turun (mulai kuis 40 -> 11, submit kuis 74 -> 16), tidak ada lagi query skema per view, laporan pelanggaran kamera mengirim screenshot lebih kecil dan diproses lebih ringan, PDF sertifikat disimpan dan tidak dibuat ulang setiap unduhan.
- **Leaderboard & Course Page Queries**: Leaderboard kursus dan modul tidak lagi menjalankan satu query per siswa (sebelumnya hingga 100 query), dan daftar kursus terkait di halaman detail kursus memuat instruktur dan kategori sekaligus.
- **Deploy Cache**: Deploy kini juga menjalankan `php artisan event:cache`.
- **Queued Auth Emails**: Email verifikasi dan reset password kini dikirim lewat antrean (dijalankan scheduler tiap menit), sehingga SMTP tidak menahan request. Membutuhkan cron `schedule:run` di server.

### Fixed

- **Double Click Protection**: Klik ganda atau request bersamaan dari siswa yang sama tidak lagi membuat attempt kuis ganda, menyimpan jawaban kuis berkali-kali, memberi poin dua kali (tandai selesai, polling, word cloud, tugas), atau memunculkan error 500. Submit ulang attempt yang sudah selesai kini diarahkan ke halaman hasil, dan klik "Mulai" pada kuis yang sedang dikerjakan selalu melanjutkan attempt yang ada.
- **Student Status Confirmation**: Pesan konfirmasi tombol Deactive/Re-active di Manajemen Student sebelumnya menyebut "Instructor"; kini menyebut student dengan kalimat yang benar.

### Deployment Notes

- Wajib sebelum deploy: cron cPanel `* * * * * cd <path aplikasi> && php artisan schedule:run >> /dev/null 2>&1` (menjalankan antrean email dan `orders:sync-pending`).
- Disarankan di `.env` production (hasil uji beban: antrean 17-27 detik hilang): `SESSION_DRIVER=file`, `CACHE_STORE=file`, `BCRYPT_ROUNDS=10`, `LOG_LEVEL=warning`, `LOG_STACK=daily`, lalu `php artisan config:cache`. Ganti driver session membuat semua pengguna logout sekali; lakukan di luar jam kelas. Pastikan ekstensi `opcache` aktif.

## [1.3.1] - 2026-10-14

### Fixed

- **Pricing & Coupon Calculation**: Diskon kupon per kursus kini hanya memotong harga kursus yang dituju (bukan seluruh subtotal keranjang). Perhitungan total di halaman keranjang belanja diselaraskan dengan alur checkout menggunakan `CartPricingService`, sehingga nominal yang ditampilkan sama persis dengan yang ditagihkan.
- **Specific Coupon Messages**: Menampilkan pesan galat kupon yang jelas dan informatif untuk setiap kondisi (kupon kedaluwarsa/tidak aktif, kuota total habis, batas maksimal per pengguna tercapai, atau kupon tidak berlaku untuk kursus di keranjang).
- **Coupon Quota Reservation**: Kuota kupon kini memperhitungkan pesanan berstatus pending dalam 24 jam terakhir agar kuota tidak terlampaui saat beberapa transaksi berjalan bersamaan.
- **Ownership ID Casts**: Memastikan casting integer pada kolom kepemilikan (`user_id`, `instructor_id`, `student_id`) pada seluruh model terkait guna mencegah penolakan otorisasi palsu akibat perbandingan tipe ketat.

### Added

- **Pending Order Sync**: Penambahan perintah `orders:sync-pending` dan penjadwalan berkala untuk memeriksa dan menyelesaikan transaksi tertunda langsung ke gateway Midtrans secara otomatis.

## [1.3.0] - 2026-10-14

### Security & Hardening

- **Payment & Webhook Security**: Memperketat alur checkout dan callback Midtrans. Menghapus ketergantungan status pembayaran pada URL return peramban. Seluruh status pelunasan transaksi kini diverifikasi melalui webhook resmi dengan validasi SHA512 signature dan pencocokan nominal tagihan (`OrderSettlementService`), serta fallback query langsung ke server Midtrans.
- **Role Switch & Authorization**: Mengamankan rute pergantian peran (`/switch-role/{role}`) dengan middleware auth dan validasi kepemilikan role untuk mencegah eskalasi hak akses.
- **Instructor Ownership Protection**: Menerapkan kebijakan otorisasi `CoursePolicy` pada seluruh aksi controller instruktur (Course, Module, Lesson, Quiz, Question Bank, Leaderboard) sehingga instruktur tidak dapat melihat, mengubah, atau memanipulasi materi milik instruktur lain.
- **Quiz & Assignment Access Control**: Menerapkan validasi enrollment kursus tanpa celah pada pengerjaan kuis, pengumpulan tugas, dan pengisian polling. Memastikan siswa hanya dapat mengakses attempt miliknya sendiri dan membatasi fitur preview kuis hanya untuk instruktur pemilik dan admin.
- **Answer Oracle Removal**: Menghapus endpoint publik pengecekan jawaban real-time (`/student/quiz/check-answer`) yang mengekspos kunci jawaban dan pembahasan soal.
- **Data Integrity & Concurrency**: Menjamin idempoten seluruh seeder database (`RoleSeeder`, `CourseCategorySeeder`, `SuperadminUserSeeder`, `AdminUserSeeder`) dan menambahkan database lock concurrency (`lockForUpdate`) pada transaksi pengurangan diamond dan konversi poin ke diamond (`DiamondService`).

## [1.2.0] - 2026-09-14

### Changed

- **Lesson Editor UI**: Menambahkan informasi nama Kursus dan Modul secara dinamis pada _header_ seluruh halaman pembuatan dan pengeditan pelajaran (Video, Artikel, Kuis, Polling, Word Cloud, dll) agar instruktur memiliki konteks navigasi yang jelas.
- **Module List UI**: Mengeluarkan tombol "Papan Peringkat" dari menu _dropdown_ dan meletakkannya di samping tombol "Kelola Pelajaran" (tampil adaptif berdasarkan ukuran layar _device_).
- **Lesson List UI**: Merapikan tombol aksi pelajaran. Tombol "Edit" kini disembunyikan secara permanen ke dalam menu _dropdown_ (gear), sementara tombol-tombol aksi sekunder (seperti Pratinjau, Kelola Soal, Hasil Polling) dikeluarkan agar lebih cepat diakses dari layar desktop.
- **Student List UI**: Menghapus kolom "Email" dari tabel daftar siswa yang terdaftar di dalam menu Daftar Kursus untuk merampingkan antarmuka.
- **Student Progress UI**: Memperbaiki tata letak (layout) _header_ profil siswa sehingga Email dan NIM kini tersusun rapi di bawah Nama Siswa. Selain itu, ditambahkan ruang (margin) yang ideal antara ikon status kelulusan (centang hijau) dengan judul pelajaran.

## [1.1.1] - 2026-09-10

### Changed

- **Licensing & Copyright**: Mengubah lisensi repositori dari status terbuka (MIT) menjadi tertutup secara hukum (_Proprietary / All Rights Reserved_) melalui penambahan berkas `LICENSE` khusus untuk `ruangstudi.id`. Hal ini mengamankan hak kekayaan intelektual (HAKI) perusahaan dari penyalinan yang tidak sah.
- **Documentation Cleanup**: Membersihkan duplikasi konten (teks dan _badge_) yang sempat berantakan di `README.md` dan memperbarui klausa lisensi di bagian paling bawah dokumen agar sejalan dengan status _Proprietary_.

## [1.1.0] - 2026-09-10

### Added

- **Polling & Word Cloud Voter Modals**: Mengubah tampilan daftar nama pemilih di menu hasil Polling (baik halaman instruktur maupun modal ajax siswa) dan halaman hasil Word Cloud menjadi tombol interaktif "Lihat Detail/Responden". Ketika ditekan, muncul _modal window_ tabel terperinci berisi Nomor, NRP/NIM (`unique_id_number`), Nama Lengkap, dan Email responden (melalui _eager loading_ `user.studentProfile`).
- **NRP/NIM Integration**: Memunculkan NRP/NIM dan Identitas lengkap pada _modal_ hasil untuk memperjelas identifikasi responden oleh instruktur.

### Fixed

- **Word Cloud Missing Dominant Word Bug**: Memperbaiki bug kritis di mana kata yang paling dominan/sering muncul malah hilang (tidak ter-_render_) di dalam kanvas Word Cloud. Mengganti perhitungan `weightFactor` di `wordcloud2.js` menjadi normalisasi linear (ukuran _font_ maksimal dibatasi `120px`), dan mengaktifkan pengaturan `shrinkToFit: true` sehingga teks seberapa pun panjangnya atau sebanyak apa pun frekuensinya akan otomatis disesuaikan agar selalu muat di dalam batas kanvas tanpa di-drop.
- **Polling & Word Cloud Modal Trapping**: Memperbaiki isu tampilan di mana _modal_ hasil pemilih yang baru ditambahkan "terjebak" (trapped/clipped) di balik layout _card_ atau daftar (_list-group_) karena konflik `overflow: hidden`. Memindahkan _rendering_ HTML modal ke tingkat _root_ di bagian bawah berkas, serta menyuntikkannya ke `<body>` via JavaScript (khusus untuk tampilan pratinjau Ajax siswa) dipadukan dengan `z-index: 1050`.

## [1.0.0] - 2026-09-07

### Added

- **Select All & Batch Assignment Revision (Instructor)**: Menambahkan fitur seleksi masal (_checkbox_ per baris dan _Select All_ di header tabel) pada halaman pengumpulan tugas instruktur. Instruktur dapat memilih beberapa atau seluruh siswa sekaligus untuk meminta revisi tugas (_batch revision_) dalam satu kali klik melalui tombol dinamis **"Minta Revisi Terpilih (N)"**.
- **Batch Revision Confirmation Modal**: Menyediakan modal konfirmasi sebelum aksi revisi masal dieksekusi, lengkap dengan tinjauan jumlah siswa terpilih dan _textarea_ catatan/umpan balik (_feedback_) revisi yang dapat disesuaikan (nilai bawaan: _"Tugas belum sesuai kriteria. Silakan perbaiki dan kumpulkan kembali."_).
- **Batch Revision Backend & Transaction**: Menambahkan _endpoint_ `POST /instructor/assignments/{assignment}/submissions/bulk-revision` dan _method_ `bulkRevise()` pada `InstructorAssignmentController`. Sistem memperbarui status tugas menjadi `revision_required`, menyetel nilai ke `0`, mencabut status penyelesaian pelajaran siswa (`completedLessons()->detach()`) secara atomik dalam `DB::transaction`, serta otomatis mengirimkan email notifikasi revisi (`AssignmentRevisionRequired`) ke setiap siswa terpilih.

### Fixed

- **Bulk Revision Modal Trapping & Backdrop Issue**: Memperbaiki isu tampilan (_bug UI_) di mana modal "Konfirmasi Revisi Masal" terkurung di dalam area kartu (_card/tab pane_) dan tertutup _backdrop_ gelap akibat kontainer bertransformasi (_CSS transform containing block_). Solusi: Memindahkan markup modal ke tingkat _root view_ di `submissions.blade.php` serta menyuntikkan pemindahan otomatis ke `document.body` saat halaman dimuat agar dialog melayang sempurna di tengah layar.
- **Dropdown Menu Clipping on Lesson/Module List**: Memperbaiki masalah visual di mana menu aksi _dropdown_ pada daftar pelajaran/modul terpotong atau tertutup oleh kotak _card_ di bawahnya (akibat konflik _stacking context_ elemen saudara pada DOM). Solusi: Menaikkan `z-index` menjadi `1050` pada _card_ yang sedang membuka _dropdown_ via CSS `:has()` dan event handler JavaScript (`show.bs.dropdown` / `hidden.bs.dropdown`).
- **Modal Video Background Playback**: Memperbaiki isu di mana video (YouTube maupun video unggahan) tetap berputar dan mengeluarkan suara di latar belakang ketika pop-up/modal pratinjau materi pelajaran ditutup. Ditambahkan penangan global `hidden.bs.modal` pada `layouts/app-layout.blade.php` untuk otomatis menghentikan pemutaran `<iframe>` (YouTube/Vimeo) dan me-reset tag `<video>`/`<audio>` HTML5, serta menyematkan `?enablejsapi=1` pada embed YouTube di `_lessonvideo.blade.php`.
- **Database Connection Environment Alignment**: Menyelaraskan kredensial database di berkas `.env` (`edugamesdatabase`, `edugamesuser`, `edugamespassword`) agar sesuai dengan konfigurasi kontainer MySQL di `docker-compose.yaml` untuk mencegah galat autentikasi migrasi database (`SQLSTATE[HY000] [1045] Access denied`).

## [0.4.0] - 2026-09-03

### Added

- **Contact Form Backend**: Mengaktifkan sistem backend (Model `ContactMessage`, Controller, dan Migration) untuk menangani pengiriman pesan dari formulir kontak di halaman Landing Page publik secara utuh (MVP).
- **Anti-Spam Contact Protection**: Menerapkan dua lapis keamanan anti-spam pada _endpoint_ kontak, yakni _Rate Limiting_ (`throttle:5,1`) dan pendeteksi bot otomatis **Google reCAPTCHA v3** yang aktif secara dinamis menyesuaikan variabel `.env`.
- **Contact Message Dashboard**: Menambahkan menu "Pesan Kontak" di _sidebar_ Superadmin dan Admin untuk mengelola, melihat riwayat, dan memantau status baca (`is_read`) pesan pengunjung website.
- **Direct Email Reply System**: Menambahkan fitur "Balas Pesan" di dalam Dasbor Admin. Fitur ini dirancang untuk segera mengirimkan balasan langsung (_direct reply_) ke email pengunjung menggunakan _Markdown Mail Template_ secara otomatis (_SMTP required_), lengkap dengan indikator anti pengiriman-ganda (`is_replied`).

### Fixed

- **Transaction History 500 Error (Production Bug)**: Memperbaiki _bug_ fatal (Error 500) pada halaman "Riwayat Transaksi" siswa yang terjadi di lingkungan _production_. Bug ini muncul karena sistem gagal merender judul kursus (`$item->course->title`) apabila instruktur/admin telah menghapus kursus tersebut dari peredaran (karena model `Course` menggunakan `SoftDeletes`). Solusi: Menyuntikkan `withTrashed()` pada relasi `course()` di dalam model `OrderItem` sehingga riwayat resi siswa akan tetap menampilkan nama kursus walaupun kursus aslinya telah dihapus.
- **Mobile Sidebar Scrolling Lock**: Menyempurnakan perbaikan sidebar mobile dengan mengimplementasikan _MutationObserver_ via JavaScript murni. Pendekatan ini secara otomatis mengunci _scroll_ halaman utama (`overflow: hidden`) ketika menu sidebar terbuka (`vertical-nav-type="expanded"`), sehingga posisi sidebar terjamin stabil di berbagai perangkat dan ukuran layar tanpa memblokir fungsi scroll pada elemen sidebar itu sendiri.
- **CSRF Token Mismatch (Error 419)**: Memperbaiki isu kegagalan _login_ (419 Page Expired) saat diakses melalui HTTP jaringan lokal (via IP) dengan menonaktifkan paksaan _secure cookie_ (`SESSION_SECURE_COOKIE=false`) pada berkas `.env`.
- **Polling & Wordcloud Points Not Awarded (Production Bug)**: Memperbaiki _bug_ di _production_ (cPanel) di mana siswa yang mengisi Polling atau Word Cloud **tidak mendapatkan poin sama sekali**, sementara tipe lesson lain berfungsi normal. Akar masalah: kolom `points_for_polling` dan `points_for_wordcloud` ditambahkan via migration baru, namun MySQL hanya menerapkan nilai `DEFAULT` pada row baru — row lama yang sudah ada tetap `NULL`. Nilai `NULL` menyebabkan `PointService` menghitung `$pointsToAdd = 0` dan melewati seluruh blok pemberian poin. **Solusi tiga lapis**: (1) Migration data fix-up untuk mengisi `NULL → 5` pada row yang sudah ada, (2) Cast `(int)` eksplisit pada semua nilai poin di `PointService`, (3) Logging diagnosis di `PointService` agar masalah serupa mudah dideteksi di log produksi.

### Changed

- **UI Performance Overhaul (Tahap 1-3)**: Melakukan optimasi drastis pada beban performa antarmuka (UI):
    - **Tahap 1 (Pembersihan Kode Mati)**: Membuang inisialisasi ganda `slimScroll`, kode _Modernizr_, dan _excanvas_ yang sudah usang dari _layout_ utama, serta menambahkan metode `defer` pada SweetAlert2 agar tidak menghalangi laju _rendering_ DOM.
    - **Tahap 2 (Asset Pushing & Scrollbar)**: Mencabut deklarasi global library grafik raksasa (_Chart.js_ dan _AmCharts_). Membuang plugin `mCustomScrollbar` dan menggantinya dengan gulir CSS bawaan (_Native CSS Scrollbar_).
    - **Tahap 3 (Pembersihan Hutan Ikon)**: Melakukan migrasi massal dari puluhan pustaka ikon lawas (_Themify_ dan _IcoFont_) ke _Font Awesome 6_ yang jauh lebih seragam dan ringan, menyapu bersih penggunaan ikon usang pada 64+ _file_ komponen _view_.
    - **60FPS Hardware Acceleration & Loader**: Membuang _pre-loader_ jQuery yang berat dan menggantinya dengan animasi berbasis CSS murni. Menyuntikkan trik _GPU acceleration_ (`transform: translateZ(0)`) dan pergerakan transisi _Cubic Bezier_ ala Material Design untuk seluruh interaksi statis, memastikan kelancaran sentuhan tanpa membebani CPU.

## [0.3.0] - 2026-09-02

### Added

- **Mass Sync Points**: Menambahkan fitur "Sinkronisasi Poin Massal" pada menu manajemen kursus instruktur. Fitur ini dirancang dengan algoritma pemindai (_scanner_) yang akan menyapu seluruh progres materi dan _database_ perolehan poin dari seluruh murid di suatu kursus, lalu menampilkannya dalam tabel "Pratinjau Anomali". Setelah ditinjau, instruktur dapat mengeksekusi penyelesaian seluruh anomali poin siswa dalam satu klik secara aman (menggunakan _Database Transaction_).
- **Sync Missing Points**: Menambahkan fitur interaktif "Sinkronisasi Poin" bagi instruktur pada baris materi yang mengalami anomali (_bug_) poin. Menekan tombol akan memunculkan _modal_ cerdas berisi ringkasan log data. Sistem backend menggunakan _Database Transaction_ untuk merekonsiliasi poin di tabel `point_histories` dan merekapitulasi total poin siswa di tabel `course_user` secara _real-time_.
- **Module Point Summary**: Menambahkan lencana kalkulasi otomatis (misal: "41 / 41 Poin") di sisi kanan atas setiap judul folder Modul pada halaman _Checklist_ Siswa. Sistem akan menyapu seluruh materi di dalam modul dan mengkalkulasikan rasio Poin Aktual berbanding Poin Potensial secara _on-the-fly_.
- **Assignment Submission Status Tracking**: Menambahkan sistem pelacakan status penugasan (_Assignment_) secara _real-time_ di halaman Student Progress instruktur. Jika tugas belum dinilai, statusnya akan berubah menjadi _badge_ kuning **Menunggu Penilaian**, dan jika tidak lulus, berubah menjadi _badge_ merah **Perlu Revisi** daripada sekadar berstatus 0 poin.
- **Instructor Student Progress**: Menambahkan fitur pemantauan _Student Checklist_ bagi instruktur untuk melihat detail progres materi dan perolehan poin aktual per siswa secara langsung dari halaman Manajemen Kursus, lengkap dengan indikator potensi poin untuk materi yang belum diselesaikan dan akumulasi **Total Perolehan Poin** khusus di kursus tersebut.
- **Gamification Clarity**: Menambahkan informasi "Isi Polling", "Isi Word Cloud", dan Rasio Konversi Poin ke Diamond pada panel informasi Poin & Diamond siswa agar transparan dan selaras dengan konfigurasi Superadmin.

### Changed

- **Point Sync Access Control**: Memindahkan keseluruhan arsitektur fitur **Sinkronisasi Poin (Massal & Per-Student)** dari wewenang Instruktur ke **Superadmin dan Admin**. Hal ini bertujuan agar instruktur tidak bisa memanipulasi riwayat poin siswa.
- **Point Sync Management Console**: Membangun menu "Sinkronisasi Poin" sentral bagi Admin/Superadmin dengan alur berlapis: _Pilih Instruktur -> Pilih Kursus -> Pilih Siswa/Eksekusi Sinkronisasi Massal_. Menu ini sekarang dapat diakses langsung melalui **Sidebar Navigasi** utama di bawah menu Kupon.
- **Instructor Student Progress**: Mengubah halaman pemantauan _Student Progress_ di sisi instruktur menjadi mode _read-only_ (hanya baca). Tombol sinkron dan _modal_ konfirmasi telah ditarik dari antarmuka instruktur, menyisakan panel indikator dan peringatan visual mutlak.

### Fixed

- **Missing Point History Message**: Memperbaiki redaksi teks peringatan di halaman Student Progress agar memunculkan pesan spesifik ("Data tidak ditemukan") beserta ikon segitiga peringatan jika rekaman `PointHistory` benar-benar absen dari _database_, alih-alih menampilkan pesan keliru bahwa poin berubah akibat pengaturan.
- **Sync Points Route Binding (400 Bad Request)**: Memperbaiki kegagalan pemrosesan form (_Error 400_) akibat bentrokan antara pengiriman parameter URL _slug_ (teks biasa) dengan sistem keamanan bawaan `HasHashedRouteKey`. _Route_ disesuaikan untuk menerima injeksi _Model_ secara langsung (`[$course, $student]`) agar fitur HashID dapat menerjemahkannya dengan benar.
- **Sync Points Server Error (500 Internal Server Error)**: Memperbaiki kelalaian deklarasi (_missing import_) model `User` di `CourseController` yang sebelumnya memicu `TypeError` dan melumpuhkan sistem _backend_ ketika fitur sinkronisasi diproses.
- **CourseController Syntax Error (500 Internal Server Error)**: Memperbaiki _closing brace_ (`}`) yang hilang pada method `syncPoints` di `CourseController`, akibat penambahan method baru yang tidak rapi. Kesalahan ini menyebabkan PHP _syntax error_ dan melumpuhkan seluruh halaman daftar kursus instruktur.
- **Sync Points Modal Bug**: Memperbaiki masalah atribut tombol _close_ dan _batal_ pada _pop-up_ peringatan agar selaras dengan standar sintaks Bootstrap 5 (`data-bs-dismiss` alih-alih `data-dismiss`), serta memperbaiki _blade directive_ (`@section` menjadi `@push`) yang sempat membuat _modal_ macet.
- **Student Progress Checklist Icon**: Memperbaiki masalah visual di mana ikon centang/lingkaran status materi tampak terhimpit menjadi "kotak tumpul" akibat konflik _padding_ bawaan dari _class_ Bootstrap `.badge`. Diganti dengan struktur bundar murni untuk memastikan presisi lingkaran yang simetris sempurna di segala ukuran layar.
- **Student Progress Assignment Bug**: Memperbaiki isu di mana indikator potensi poin untuk tipe materi Tugas (_Assignment_) selalu bernilai 0 di halaman pemantauan instruktur, akibat ketidaksesuaian nama _class_ (`Assignment` vs `LessonAssignment`).
- **Site Settings Bug**: Memperbaiki `Internal Server Error` (Attempt to read property on null) yang terjadi pada seluruh sistem (termasuk cetak PDF Invoice) saat tabel `site_settings` kosong, dengan mengimplementasikan `SiteSetting::firstOrNew()` secara global.
- **Layout Stability**: Memperbaiki arsitektur layout halaman sertifikat yang hancur (CSS scope break) akibat penempatan komponen modal di luar tag `@section('content')`.
- **Mobile Sidebar Scrolling**: Memperbaiki isu di mana sidebar navigasi versi ponsel (_mobile_) ikut tergulung bersamaan dengan konten utama, dengan menyuntikkan CSS fixed-position independen.
- **Hamburger Menu Alignment**: Memperbaiki cacat keseimbangan simetris pada ikon menu hamburger beranda versi _mobile_ akibat bentrokan antara _padding_ kapsul dan _margin_ bawaan dari template.

### Changed

- **Student Progress UI**: Merapikan antarmuka fitur pemantauan _Student Checklist_, menerjemahkan nama _class_ internal menjadi label bahasa Indonesia (Artikel, Word Cloud, Dokumen / Slide, dll), serta menambahkan teks peringatan otomatis jika poin yang didapatkan saat penyelesaian materi (_Actual Points_) berbeda dengan nilai pengaturan saat ini (_Expected Points_).
- **Poin & Diamond UI/UX**: Mengoptimalkan layout dasbor poin siswa menjadi grid 4/8 di desktop dan menyembunyikan riwayat panjang ke dalam sistem navigasi _Tab_ di versi ponsel untuk mengurangi _cognitive load_.
- **Certificate Gallery**: Merombak total tampilan daftar sertifikat dari format tabel kaku menjadi format galeri kartu (_card grid_) yang jauh lebih elegan dan modern.
- **Kelola Ulasan (Feedback)**: Merombak struktur halaman dari tumpukan kartu vertikal yang memanjang menjadi sistem navigasi tab vertikal (Vertical Pills) bergaya _floating pill_ yang rapi.
- **Riwayat Transaksi**: Mengganti format tabel biasa menjadi daftar kartu transaksi bergaya _e-commerce_ premium, dilengkapi dengan garis warna indikator status (hijau/kuning/merah) yang responsif di segala layar.
- **Mobile Action Menu**: Menyempurnakan desain menu _dropdown_ ponsel di halaman beranda; mengubah tautan Login, Keranjang, Dashboard, dan Saldo Diamond dari barisan teks biasa menjadi deretan tombol blok (_full-width buttons_) dan kapsul informasi.

## [0.2.0] - 2026-09-01

### Added

- **SortableJS Integration**: Menambahkan animasi _drag-and-drop_ yang lebih halus (smooth) dan efek visual saat menggeser posisi Modul dan Pelajaran di halaman instruktur.
- **Mobile Action Dropdowns**: Memperkenalkan sistem _dropdown_ "Aksi Lainnya" (⚙️) pada halaman Kelola Kursus, Modul, dan Pelajaran untuk merapikan tombol-tombol aksi sekunder.
- **Navigasi Pelajaran (Student)**: Menambahkan tombol navigasi _Next_ dan _Previous_ yang _sticky_ (menempel di bagian bawah layar) pada halaman baca materi siswa untuk versi _mobile_.

### Fixed

- **Mobile Button Visibility**: Memperbaiki masalah hilangnya tombol utama ("Buat Kursus/Modul/Pelajaran Baru") pada halaman instruktur versi _mobile_ akibat gaya CSS bawaan tema.
- **Table Layout & Overflow**: Mengatasi masalah teks panjang (seperti judul kursus) yang tidak bisa melipat (wrap) dan menabrak/mendorong tombol aksi keluar dari layar pada versi _mobile_.
- **Flexbox Layout**: Memperbaiki isu "naik turun" (zig-zag) pada daftar Modul dan Pelajaran dengan memaksanya tetap dalam satu baris sejajar (_single-line layout_) di layar sekecil apapun, menyembunyikan label teks pada tombol utama agar muat.
- **Sidebar Auto-Close**: Memperbaiki Daftar Isi (_Table of Contents_) siswa agar otomatis tertutup saat sebuah pelajaran dipilih di layar ponsel.

### Changed

- **Instructor Dashboard**: Mengoptimalkan kartu statistik (_dashboard cards_) agar menggunakan susunan 2-kolom pada layar _mobile_, menghemat ruang gulir vertikal secara drastis.
- **Simplicity & Core Action UX**: Menyederhanakan tampilan tabel Kelola Kursus di layar _mobile_ dengan menyembunyikan kolom `#`, `Status`, dan `Kategori`, lalu menggabungkannya ke dalam satu kolom "Info Kursus" yang padat informasi.

## [0.1.0] - 2026-08-31

### Added

- Frontend validation for assignment submission file size (maksimal 20MB).
- Antarmuka (UI) Drag-and-drop modern untuk form unggah tugas (`_assignment_form.blade.php`).
- Fitur _live preview_ (pratinjau) file yang menampilkan ikon tipe file (PDF/ZIP), nama file, dan ukuran file sebelum tugas dikumpulkan.
- Tombol hapus pada pratinjau file untuk membatalkan file yang dipilih tanpa perlu _refresh_ halaman.

### Fixed

- Memperbaiki isu di mana pengunggahan file besar menyebabkan _loading_ terus-menerus (infinite load) dengan memblokir pengiriman form langsung dari sisi klien (browser) jika file melebihi 20MB.
- Mengatasi kendala di mana kode Javascript tidak dieksekusi saat form dimuat secara dinamis, dengan memindahkan logika pemeriksaan ukuran file ke atribut inline `onchange` dan `onsubmit`.

### Changed

- Memperbaiki keseluruhan _User Experience_ (UX) pada form pengumpulan tugas siswa agar lebih ramah pengguna (_user friendly_) dan interaktif.
- Menerapkan arsitektur percabangan standar industri (GitFlow) dengan branch `develop` sebagai pusat integrasi.
- Menambahkan pipeline CI otomatis (`.github/workflows/ci.yml`) untuk menjalankan Testing (PHPUnit) dan pengecekan standar penulisan kode (Laravel Pint) pada setiap Pull Request dan Push.
- Merapikan `README.md` menjadi standar industri dan merelokasi panduan instalasi lokal ke `docs/ubuntu-development-setup.md`.
- Menambahkan panduan detail mengenai arsitektur percabangan (Branching Strategy) ke dalam `docs/branching-strategy.md`.
