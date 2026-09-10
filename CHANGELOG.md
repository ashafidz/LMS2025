# Changelog

All notable changes to this project will be documented in this file.

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
