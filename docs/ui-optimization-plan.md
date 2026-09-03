# Rencana Optimasi UI Dashboard (LMS 2025)

Dokumen ini memetakan strategi optimasi performa UI/UX dari sisi *Frontend* (CSS/JS/Assets) pada dashboard. Optimasi diurutkan dari yang paling **aman (Low Risk)** hingga yang **paling berisiko (High Risk)** terhadap stabilitas fitur (mengingat dashboard ini menggunakan *legacy template* berbasis jQuery).

---

## 🟢 Tahap 1: Tingkat Risiko Rendah (Sangat Aman)
*Perubahan pada tahap ini tidak akan merusak tampilan (layout) maupun fungsi inti dari sistem.*

### 1. Penghapusan Script Usang (*Legacy/Dead Code*)
- **Masalah:** Terdapat beberapa file JavaScript yang ditujukan khusus untuk *browser* lawas (seperti Internet Explorer 8/9). Saat ini *browser* modern tidak membutuhkannya, tetapi file ini tetap diunduh dan diproses oleh HP/Komputer.
- **Solusi:**
  - Hapus pemanggilan `<script src=".../excanvas.js"></script>`.
  - Hapus pemanggilan `<script src=".../modernizr.js"></script>`.

### 2. Mengatasi Duplikasi *Scrollbar Plugin*
- **Masalah:** Template saat ini memuat dua pustaka untuk mengubah gaya (style) *scrollbar* sekaligus, yaitu `jquery.slimscroll.js` dan `jquery.mCustomScrollbar.js`. Memuat dua plugin dengan fungsi yang sama akan memboroskan memori.
- **Solusi:** 
  - Tentukan satu saja yang benar-benar digunakan oleh template (biasanya `mCustomScrollbar`).
  - Hapus pemanggilan script plugin yang tidak terpakai dari `app-layout.blade.php`.

### 3. Implementasi `defer` pada File JavaScript Eksternal
- **Masalah:** File JavaScript yang dimuat tanpa atribut `defer` akan menghentikan proses *render* layar (Render-Blocking) sampai file tersebut selesai diunduh.
- **Solusi:** 
  - Tambahkan atribut `defer` pada file JS yang tidak perlu langsung jalan di detik pertama (misalnya SweetAlert2, Bootstrap Bundle). Contoh: `<script src="..." defer></script>`.

---

## 🟡 Tahap 2: Tingkat Risiko Menengah (Perlu Pengujian Manual)
*Perubahan pada tahap ini memiliki dampak signifikan terhadap kecepatan, namun membutuhkan pengujian (testing) di beberapa halaman untuk memastikan tidak ada error.*

### 1. *Asset Pushing* (*Lazy Load*) untuk Library Grafik (Chart)
- **Masalah:** Pustaka grafik (Chart.js dan kumpulan AmCharts) sangat raksasa ukurannya. Saat ini file-file tersebut dimuat secara global di `app-layout.blade.php`, yang artinya ikut terbawa saat user membuka halaman "Settings" atau "Profil" yang tidak memiliki grafik sama sekali.
- **Solusi:** 
  - Hapus deklarasi Chart.js dan AmCharts dari `<head>` dan `<body>` di `app-layout.blade.php`.
  - Pindahkan pemanggilan script tersebut ke dalam *Blade Directive* `@push('scripts')` secara eksklusif HANYA di halaman *view* yang membutuhkannya (misalnya: `superadmin/dashboard.blade.php`).

### 2. Mematikan *Pre-loader* Animasi CSS/JS
- **Masalah:** Template memiliki animasi memutar (*spinner*) 4 warna saat halaman dimuat. Fitur ini memaksa browser mem-parsing banyak elemen DOM dan CSS Animation di saat CPU juga sedang berusaha me-render konten asli. Pada HP kentang, hal ini malah memperlambat kemunculan konten.
- **Solusi:** 
  - Hapus atau komentari blok HTML `<div class="theme-loader">...</div>` di layout utama. Konten akan muncul jauh lebih cepat.

### 3. Standardisasi Plugin Scrollbar menjadi CSS Murni
- **Masalah:** Memodifikasi *scrollbar* menggunakan perhitungan jQuery akan memicu kalkulasi ulang (Reflow) pada DOM setiap kali user melakukan proses *scrolling*, menyebabkan FPS (*frame per second*) turun drastis di HP spesifikasi rendah.
- **Solusi:**
  - Copot sepenuhnya library `mCustomScrollbar.js` maupun `slimscroll`.
  - Ganti menggunakan CSS murni yang bebas beban GPU (0 JS overhead):
    ```css
    ::-webkit-scrollbar { width: 8px; }
    ::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }
    ```

---

## 🔴 Tahap 3: Tingkat Risiko Tinggi (Rawan Mengacaukan Tampilan)
*Perubahan tahap ini berpotensi membuat ikon menghilang atau beberapa gaya layout hancur, sehingga memerlukan penyesuaian/pencarian massal pada file Blade di seluruh project.*

### 1. Pembersihan Hutan Pustaka Ikon (*Icon Font Hell*)
- **Masalah:** Saat ini dashboard memaksa HP pengguna untuk men-download 4 set ikon secara bersamaan (Themify Icons, Bootstrap Icons, Font Awesome, dan IcoFont). Ini sangat berat.
- **Solusi:**
  - Pilih HANYA 1 atau maksimal 2 set ikon (misalnya hanya menyisakan Font Awesome dan Bootstrap Icons).
  - Hapus pemanggilan CSS Themify dan IcoFont dari `app-layout.blade.php`.
  - **Risiko:** Anda harus melakukan "Find & Replace" massal di seluruh file `.blade.php` (misal: mengganti class `<i class="ti-home"></i>` (Themify) menjadi `<i class="fa fa-home"></i>` (Font Awesome)).

### 2. Mengurangi Ketergantungan jQuery Core secara Bertahap
- **Masalah:** Manipulasi *backend-frontend* (seperti pengiriman form dengan AJAX) seringkali dibuat menggunakan penulisan jQuery usang.
- **Solusi:**
  - Tulis ulang logika interaktif sederhana (seperti `toggle` sidebar atau validasi input) menggunakan `Vanilla JS` (JavaScript Murni modern).
  - **Risiko:** Sangat butuh ketelitian karena template inti (`pcoded.js`) bergantung 100% pada jQuery.

### 3. Migrasi Layout (*Re-templating*)
- **Masalah:** Akar lambatnya UI berasal dari desain lawas template `pcoded` itu sendiri yang mungkin dirakit 5-6 tahun lalu dengan arsitektur DOM bersarang (Nested DOM) yang dalam.
- **Solusi:**
  - Bermigrasi ke *modern lightweight template* (misalnya template Tailwind CSS, Tabler, atau Bootstrap 5 murni tanpa jQuery).
  - **Risiko:** Merombak seluruh pondasi Frontend dari awal. Waktu pengerjaan bisa berhari-hari/berminggu-minggu.
