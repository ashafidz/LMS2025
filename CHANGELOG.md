# Changelog

All notable changes to this project will be documented in this file.

## [2026-09-02]
### Added
- **Instructor Student Progress**: Menambahkan fitur pemantauan *Student Checklist* bagi instruktur untuk melihat detail progres materi dan perolehan poin aktual per siswa secara langsung dari halaman Manajemen Kursus, lengkap dengan indikator potensi poin untuk materi yang belum diselesaikan dan akumulasi **Total Perolehan Poin** khusus di kursus tersebut.
- **Gamification Clarity**: Menambahkan informasi "Isi Polling", "Isi Word Cloud", dan Rasio Konversi Poin ke Diamond pada panel informasi Poin & Diamond siswa agar transparan dan selaras dengan konfigurasi Superadmin.

### Fixed
- **Student Progress Checklist Icon**: Memperbaiki masalah visual di mana ikon centang/lingkaran status materi tampak terhimpit menjadi "kotak tumpul" akibat konflik *padding* bawaan dari *class* Bootstrap `.badge`. Diganti dengan struktur bundar murni untuk memastikan presisi lingkaran yang simetris sempurna di segala ukuran layar.
- **Student Progress Assignment Bug**: Memperbaiki isu di mana indikator potensi poin untuk tipe materi Tugas (*Assignment*) selalu bernilai 0 di halaman pemantauan instruktur, akibat ketidaksesuaian nama *class* (`Assignment` vs `LessonAssignment`).
- **Site Settings Bug**: Memperbaiki `Internal Server Error` (Attempt to read property on null) yang terjadi pada seluruh sistem (termasuk cetak PDF Invoice) saat tabel `site_settings` kosong, dengan mengimplementasikan `SiteSetting::firstOrNew()` secara global.
- **Layout Stability**: Memperbaiki arsitektur layout halaman sertifikat yang hancur (CSS scope break) akibat penempatan komponen modal di luar tag `@section('content')`.
- **Mobile Sidebar Scrolling**: Memperbaiki isu di mana sidebar navigasi versi ponsel (*mobile*) ikut tergulung bersamaan dengan konten utama, dengan menyuntikkan CSS fixed-position independen.
- **Hamburger Menu Alignment**: Memperbaiki cacat keseimbangan simetris pada ikon menu hamburger beranda versi *mobile* akibat bentrokan antara *padding* kapsul dan *margin* bawaan dari template.

### Changed
- **Student Progress UI**: Merapikan antarmuka fitur pemantauan *Student Checklist*, menerjemahkan nama *class* internal menjadi label bahasa Indonesia (Artikel, Word Cloud, Dokumen / Slide, dll), serta menambahkan teks peringatan otomatis jika poin yang didapatkan saat penyelesaian materi (*Actual Points*) berbeda dengan nilai pengaturan saat ini (*Expected Points*).
- **Poin & Diamond UI/UX**: Mengoptimalkan layout dasbor poin siswa menjadi grid 4/8 di desktop dan menyembunyikan riwayat panjang ke dalam sistem navigasi *Tab* di versi ponsel untuk mengurangi *cognitive load*.
- **Certificate Gallery**: Merombak total tampilan daftar sertifikat dari format tabel kaku menjadi format galeri kartu (*card grid*) yang jauh lebih elegan dan modern.
- **Kelola Ulasan (Feedback)**: Merombak struktur halaman dari tumpukan kartu vertikal yang memanjang menjadi sistem navigasi tab vertikal (Vertical Pills) bergaya *floating pill* yang rapi.
- **Riwayat Transaksi**: Mengganti format tabel biasa menjadi daftar kartu transaksi bergaya *e-commerce* premium, dilengkapi dengan garis warna indikator status (hijau/kuning/merah) yang responsif di segala layar.
- **Mobile Action Menu**: Menyempurnakan desain menu *dropdown* ponsel di halaman beranda; mengubah tautan Login, Keranjang, Dashboard, dan Saldo Diamond dari barisan teks biasa menjadi deretan tombol blok (*full-width buttons*) dan kapsul informasi.
## [2026-09-01]
### Added
- **SortableJS Integration**: Menambahkan animasi *drag-and-drop* yang lebih halus (smooth) dan efek visual saat menggeser posisi Modul dan Pelajaran di halaman instruktur.
- **Mobile Action Dropdowns**: Memperkenalkan sistem *dropdown* "Aksi Lainnya" (⚙️) pada halaman Kelola Kursus, Modul, dan Pelajaran untuk merapikan tombol-tombol aksi sekunder.
- **Navigasi Pelajaran (Student)**: Menambahkan tombol navigasi *Next* dan *Previous* yang *sticky* (menempel di bagian bawah layar) pada halaman baca materi siswa untuk versi *mobile*.

### Fixed
- **Mobile Button Visibility**: Memperbaiki masalah hilangnya tombol utama ("Buat Kursus/Modul/Pelajaran Baru") pada halaman instruktur versi *mobile* akibat gaya CSS bawaan tema.
- **Table Layout & Overflow**: Mengatasi masalah teks panjang (seperti judul kursus) yang tidak bisa melipat (wrap) dan menabrak/mendorong tombol aksi keluar dari layar pada versi *mobile*.
- **Flexbox Layout**: Memperbaiki isu "naik turun" (zig-zag) pada daftar Modul dan Pelajaran dengan memaksanya tetap dalam satu baris sejajar (*single-line layout*) di layar sekecil apapun, menyembunyikan label teks pada tombol utama agar muat.
- **Sidebar Auto-Close**: Memperbaiki Daftar Isi (*Table of Contents*) siswa agar otomatis tertutup saat sebuah pelajaran dipilih di layar ponsel.

### Changed
- **Instructor Dashboard**: Mengoptimalkan kartu statistik (*dashboard cards*) agar menggunakan susunan 2-kolom pada layar *mobile*, menghemat ruang gulir vertikal secara drastis.
- **Simplicity & Core Action UX**: Menyederhanakan tampilan tabel Kelola Kursus di layar *mobile* dengan menyembunyikan kolom `#`, `Status`, dan `Kategori`, lalu menggabungkannya ke dalam satu kolom "Info Kursus" yang padat informasi.

## [2026-08-31]
### Added
- Frontend validation for assignment submission file size (maksimal 20MB).
- Antarmuka (UI) Drag-and-drop modern untuk form unggah tugas (`_assignment_form.blade.php`).
- Fitur *live preview* (pratinjau) file yang menampilkan ikon tipe file (PDF/ZIP), nama file, dan ukuran file sebelum tugas dikumpulkan.
- Tombol hapus pada pratinjau file untuk membatalkan file yang dipilih tanpa perlu _refresh_ halaman.

### Fixed
- Memperbaiki isu di mana pengunggahan file besar menyebabkan *loading* terus-menerus (infinite load) dengan memblokir pengiriman form langsung dari sisi klien (browser) jika file melebihi 20MB.
- Mengatasi kendala di mana kode Javascript tidak dieksekusi saat form dimuat secara dinamis, dengan memindahkan logika pemeriksaan ukuran file ke atribut inline `onchange` dan `onsubmit`.

### Changed
- Memperbaiki keseluruhan *User Experience* (UX) pada form pengumpulan tugas siswa agar lebih ramah pengguna (*user friendly*) dan interaktif.
- Menerapkan arsitektur percabangan standar industri (GitFlow) dengan branch `develop` sebagai pusat integrasi.
- Menambahkan pipeline CI otomatis (`.github/workflows/ci.yml`) untuk menjalankan Testing (PHPUnit) dan pengecekan standar penulisan kode (Laravel Pint) pada setiap Pull Request dan Push.
- Merapikan `README.md` menjadi standar industri dan merelokasi panduan instalasi lokal ke `docs/ubuntu-development-setup.md`.
- Menambahkan panduan detail mengenai arsitektur percabangan (Branching Strategy) ke dalam `docs/branching-strategy.md`.

