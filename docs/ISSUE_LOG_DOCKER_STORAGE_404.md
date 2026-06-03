# Log Resolusi Masalah: Gambar Upload (Logo & Thumbnail) 404 Not Found di VPS (Docker)

**Tanggal:** 3 Juni 2026
**Status:** ✅ Solved (Terselesaikan)

## 📌 Deskripsi Masalah
Setelah melakukan *deployment* aplikasi Laravel ke VPS menggunakan Docker, gambar-gambar yang diunggah pengguna (seperti Logo Situs dan Thumbnail Kursus) tidak dapat dimuat di browser. 
Inspeksi pada tab *Network* menunjukkan bahwa browser awalnya memanggil URL yang salah kaprah tanpa imbuhan `/storage/`, dan setelah itu diperbaiki, server merespons dengan HTTP Status **404 Not Found**. Padahal, file tersebut berhasil terunggah ke dalam *Docker Named Volume* (`storage_data`).

---

## 🔍 Analisis Akar Masalah (Root Causes)

Setelah dibedah lebih dalam, terdapat rantai 3 masalah sekaligus yang menyebabkan *bug* ini:

### 1. Hilangnya Prefix `/storage/` pada URL Gambar
- **Penyebab:** Laravel secara otomatis menggunakan *disk storage* bernama `local` karena file `.env` di server VPS tidak mendefinisikan variabel `FILESYSTEM_DISK=public`. Disk `local` tidak didesain untuk membuat URL publik, sehingga fungsi `Storage::url()` memotong kata `/storage/` dari *link* gambar.
- **Dampak:** Browser mencari gambar ke URL seperti `domain.com/foto.png` bukannya `domain.com/storage/foto.png`.

### 2. Pembajakan URL (Hijacking) oleh Regex Nginx
- **Penyebab:** Konfigurasi `docker/nginx/default.conf` memiliki blok `location /storage` (sebagai alias ke `storage/app/public`), namun tepat di atasnya terdapat blok *Regex* (Regular Expression) `location ~* \.(js|css|png|jpg|jpeg...)$` untuk melakukan *caching* *static assets*. Dalam hierarki Nginx, pencarian berbasis Regex dieksekusi lebih dahulu dibandingkan *Prefix* biasa.
- **Dampak:** Saat URL berubah menjadi `/storage/foto.png`, Nginx melihat akhiran `.png` dan langsung memprosesnya dengan blok Regex. Ia kemudian mencari file tersebut di folder `public/storage` bawaan aplikasi (yang mana isinya kosong), mengabaikan instruksi alias menuju *Named Volume* Docker. Inilah penyebab munculnya kode *error* **404 Not Found**.

### 3. Nginx Tidak Membaca Konfigurasi Baru
- **Penyebab:** Perintah `docker compose up -d` yang ada di skrip CI/CD hanya memperbarui kontainer jika ada perubahan definisi di `docker-compose.yml`. File konfigurasi yang di-*bind-mount* (seperti `default.conf`) yang berubah akibat `git pull` tidak otomatis membuat Nginx sadar akan aturan baru, karena Nginx me-*load* konfigurasi ke RAM pada saat *startup*.
- **Dampak:** Meski perbaikan Nginx sudah dikirimkan, server tetap merespons dengan kesalahan yang sama karena menggunakan konfigurasi usang yang tersangkut di memori.

---

## 🛠️ Solusi (Resolusi yang Diterapkan)

### 1. Mengamankan Default Filesystem Disk
Untuk menghindari ketergantungan pada file `.env` server VPS, nilai *default fallback* dari sistem penyimpanan telah dimodifikasi secara permanen dari dalam kode.
**File:** `config/filesystems.php`
```diff
- 'default' => env('FILESYSTEM_DISK', 'local'),
+ 'default' => env('FILESYSTEM_DISK', 'public'),
```

### 2. Mengunci Prioritas Blok Nginx (Modifier `^~`)
Untuk mencegah blok Regex *caching* membajak permintaan yang ditujukan khusus untuk folder `/storage`, modifikasi prioritas `^~` (Hentikan Regex jika cocok) ditambahkan.
**File:** `docker/nginx/default.conf`
```diff
- location /storage {
+ location ^~ /storage {
      alias /var/www/html/storage/app/public;
      access_log off;
```

### 3. Otomatisasi Reload Konfigurasi Nginx
Agar setiap perubahan konfigurasi Nginx langsung berlaku di VPS secara transparan tanpa perlu masuk via SSH, instruksi khusus ditambahkan pada akhir alur penyebaran CI/CD.
**File:** `.github/workflows/deploy-vps.yml`
```yaml
          docker exec lms-app php artisan up

+         echo "🔄 Restarting Nginx to apply config changes..."
+         docker restart lms-nginx

          echo "✅ Deploy selesai!"
```

## 📝 Kesimpulan
Konfigurasi lingkungan Nginx-Docker kini telah matang. Seluruh aset gambar dinamis (unggahan) akan dengan persisten disimpan di *Named Volume* dan dijamin terlayani dengan stabil oleh Nginx dengan optimisasi parameter HTTP *Cache-Control* yang tepat.
