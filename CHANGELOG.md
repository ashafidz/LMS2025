# Changelog

All notable changes to this project will be documented in this file.

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
