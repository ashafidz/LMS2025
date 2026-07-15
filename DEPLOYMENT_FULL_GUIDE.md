# 🚀 Panduan Lengkap Deployment LMS2025 — cPanel → VPS Docker + CI/CD

> Dokumen ini merekam **seluruh proses** migrasi proyek Laravel LMS2025 dari hosting cPanel ke VPS lokal yang di-containerize dengan Docker, dilengkapi CI/CD otomatis via GitHub Actions Self-Hosted Runner, dan diekspos ke internet melalui Cloudflare Tunnel.

---

## 📌 Daftar Isi
1. [Latar Belakang & Tujuan](#1-latar-belakang--tujuan)
2. [Arsitektur Sistem](#2-arsitektur-sistem)
3. [Struktur File Konfigurasi](#3-struktur-file-konfigurasi)
4. [Tahap 1 — Persiapan Repo Baru](#4-tahap-1--persiapan-repo-baru)
5. [Tahap 2 — Setup Docker di Lokal](#5-tahap-2--setup-docker-di-lokal)
6. [Tahap 3 — Setup Awal di VPS](#6-tahap-3--setup-awal-di-vps)
7. [Tahap 4 — Migrasi Data (Database & Storage)](#7-tahap-4--migrasi-data-database--storage)
8. [Tahap 5 — Setup Cloudflare Tunnel](#8-tahap-5--setup-cloudflare-tunnel)
9. [Tahap 6 — Setup CI/CD Self-Hosted Runner](#9-tahap-6--setup-cicd-self-hosted-runner)
10. [Troubleshooting yang Pernah Terjadi](#10-troubleshooting-yang-pernah-terjadi)
11. [Referensi Perintah Harian](#11-referensi-perintah-harian)

---

## 1. Latar Belakang & Tujuan

**Situasi Awal:**
- Proyek LMS Laravel berjalan di **cPanel Hosting** (`repo lama: LMS2025`)
- CI/CD lama men-deploy ke cPanel via rsync + SSH
- Storage Laravel (gambar, video, dll) ada di cPanel
- Database MySQL ada di cPanel

**Tujuan Migrasi:**
- Membuat environment development baru yang **terisolasi** untuk pengembangan besar tanpa mengganggu produksi lama
- Menggunakan **VPS lokal (Intel NUC)** sebagai server production baru
- Men-containerize seluruh stack dengan **Docker** untuk konsistensi environment
- Mengekspos aplikasi ke internet via **Cloudflare Tunnel** (tanpa IP publik)
- Mengotomatisasi deployment dengan **GitHub Actions Self-Hosted Runner**

**Hasil Akhir:**
```
Developer (Laptop)
       │ git push
       ▼
GitHub (repo: LMS2025-v2)
       │ trigger CI/CD
       ▼
Self-Hosted Runner (NUC Lab)
       │ docker compose build & up
       ▼
NUC Lab VPS (192.168.0.223)
       │ Port 8080 (Nginx)
       ▼
Cloudflare Tunnel
       │
       ▼
https://edukasiv2.digitaltwinassistance.com ✅
```

---

## 2. Arsitektur Sistem

### Stack Container Docker

```
┌─────────────────────────────────────────────────────────┐
│  Docker Network: lms-network                            │
│                                                         │
│  ┌──────────┐    ┌──────────┐    ┌──────────┐          │
│  │  nginx   │───►│   app    │───►│  mysql   │          │
│  │ :8080→80 │    │ PHP-FPM  │    │  :3306   │          │
│  │          │    │  :9000   │    │ MySQL 8.0│          │
│  └──────────┘    └──────────┘    └──────────┘          │
│        │               │         ┌──────────┐          │
│        │               └────────►│  redis   │          │
│        │                         │  :6379   │          │
│        │                         └──────────┘          │
│   public_data                                           │
│   storage_data              ┌──────────────┐           │
│                             │    queue     │           │
│                             │ queue:work   │           │
│                             └──────────────┘           │
└─────────────────────────────────────────────────────────┘
```

### Docker Volumes

| Volume | Digunakan Oleh | Isi |
|--------|---------------|-----|
| `storage_data` | `app`, `nginx`, `queue` | Laravel storage (upload files) |
| `public_data` | `app`, `nginx` | Compiled Vite assets (CSS/JS) |
| `mysql_data` | `mysql` | Data MySQL yang persisten |
| `redis_data` | `redis` | Data Redis yang persisten |

### Spesifikasi VPS

| Parameter | Nilai |
|-----------|-------|
| Hardware | Intel NUC |
| OS | Ubuntu Server |
| IP Lokal | `192.168.0.223` |
| SSH User | `pokemon` |
| Project Path | `/opt/services/lms2025` |
| Docker Compose | v2 (sudah terinstall) |
| Cloudflared | Sudah terinstall, tunnel aktif |

---

## 3. Struktur File Konfigurasi

File-file berikut ditambahkan/dimodifikasi khusus untuk deployment VPS Docker:

```
LMS2025-v2/
├── Dockerfile                          ← Build image PHP-FPM Laravel
├── docker-compose.yml                  ← Orkestrasi 5 container
├── .dockerignore                        ← Exclude file dari build context
├── .env.docker.example                 ← Template .env untuk Docker VPS
├── docker/
│   ├── nginx/
│   │   └── default.conf                ← Konfigurasi Nginx
│   └── php/
│       └── custom.ini                  ← Custom PHP config (upload limits)
├── .github/
│   └── workflows/
│       └── deploy-vps.yml              ← CI/CD workflow (Self-Hosted Runner)
├── app/Providers/AppServiceProvider.php ← Ditambah forceScheme HTTPS
├── bootstrap/app.php                   ← Ditambah trustProxies
└── DEPLOYMENT_FULL_GUIDE.md            ← File ini
```

---

## 4. Tahap 1 — Persiapan Repo Baru

### A. Buat Repo Baru di GitHub
Buat repository baru (contoh: `LMS2025-v2`) melalui UI GitHub.

### B. Push Kode dari Lokal ke Repo Baru
```bash
# Di direktori project lokal (LMS2025)
git remote add new https://github.com/wahanamediadigital/edugames-v2.git
git push new main
```

### C. Clone ke Direktori Baru (Opsional)
```bash
git clone https://github.com/wahanamediadigital/edugames-v2.git LMS2025-v2
```

---

## 5. Tahap 2 — Setup Docker di Lokal

### A. Dockerfile

File `Dockerfile` mendefinisikan cara membangun image Laravel. Strategi penting:
- **Layer caching**: `composer.json` dan `package.json` di-COPY sebelum source code, sehingga `composer install` dan `npm ci` tidak diulang jika dependency tidak berubah.
- **--no-scripts**: `composer dump-autoload` menggunakan `--no-scripts` agar `artisan` tidak dipanggil saat build (karena belum ada koneksi database).
- **Shared public volume**: Saat container start, isi folder `public/` (hasil build Vite) disalin ke volume `shared_public` agar bisa diakses oleh Nginx.

```dockerfile
FROM php:8.3-fpm

# Install PHP extensions: pdo_mysql, gd, redis, zip, dll
# Install Node.js 20 untuk build Vite

WORKDIR /var/www/html

# Copy dependency files → install → copy source code
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

COPY package.json package-lock.json ./
RUN npm ci

COPY . .

RUN composer dump-autoload --optimize --no-dev --no-scripts
RUN npm run build && rm -rf node_modules

# Entrypoint: copy public assets ke shared volume lalu jalankan php-fpm
CMD ["/usr/local/bin/start.sh"]
```

### B. docker-compose.yml

Mendefinisikan 5 service:
- `app`: PHP-FPM, mount `storage_data` dan `public_data`
- `nginx`: Nginx Alpine, expose port 8080, mount `public_data` sebagai `/var/www/html/public`
- `mysql`: MySQL 8.0 dengan healthcheck
- `redis`: Redis Alpine dengan healthcheck
- `queue`: Container terpisah yang menjalankan `php artisan queue:work`

> **Kunci Desain**: Volume `public_data` adalah "jembatan" antara container `app` dan `nginx`. Container `app` mengisi volume ini saat startup, dan container `nginx` membacanya sebagai document root.

### C. Konfigurasi Nginx (`docker/nginx/default.conf`)

```nginx
server {
    listen 80;
    root /var/www/html/public;  # ← dari volume public_data
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        # Catatan: gunakan $document_root BUKAN $realpath_root
        # $realpath_root tidak bekerja karena file fisik ada di container 'app'
        include fastcgi_params;
    }

    location /storage {
        alias /var/www/html/storage/app/public;
    }
}
```

### D. Fix HTTPS Laravel (`bootstrap/app.php` dan `AppServiceProvider.php`)

Karena Cloudflare Tunnel meneruskan traffic sebagai HTTP ke Nginx lokal, Laravel tidak tahu aplikasi diakses via HTTPS. Dua fix diperlukan:

**1. Trust Proxy** (`bootstrap/app.php`):
```php
$middleware->trustProxies(at: '*');
```

**2. Force HTTPS Scheme** (`AppServiceProvider.php`):
```php
if (env('APP_ENV') === 'production') {
    \Illuminate\Support\Facades\URL::forceScheme('https');
}
```

---

## 6. Tahap 3 — Setup Awal di VPS

Langkah ini hanya dilakukan **satu kali** saat server baru.

### A. Clone Repository

```bash
# Buat direktori layanan
mkdir -p /opt/services
cd /opt/services

# Clone dengan Personal Access Token
git clone https://USERNAME:GITHUB_TOKEN@github.com/wahanamediadigital/edugames-v2.git lms2025

cd lms2025

# Simpan kredensial agar git pull berikutnya tidak perlu input
git config credential.helper store
```

### B. Konfigurasi Environment

```bash
cp .env.docker.example .env
nano .env
```

Konfigurasi kritis yang **harus diubah** dari nilai default:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://edukasiv2.digitaltwinassistance.com

# Host menggunakan nama service Docker, BUKAN localhost/127.0.0.1
DB_HOST=mysql
DB_DATABASE=lms2025
DB_USERNAME=root
DB_PASSWORD=PASSWORD_AMAN_KAMU

REDIS_HOST=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
```

### C. Build & Jalankan Container

```bash
# Build image dan jalankan semua container
docker compose up -d --build

# Cek status semua container
docker compose ps
```

Output yang diharapkan: semua container `Up` dan MySQL/Redis berstatus `(healthy)`.

---

## 7. Tahap 4 — Migrasi Data (Database & Storage)

### A. Import Database

Upload file SQL dump ke VPS terlebih dahulu (dari mesin lokal, via `scp` atau file manager).

```bash
# Import langsung dari file di VPS ke container MySQL
# WAJIB: kutip tunggal ('') tanpa spasi setelah -p
docker exec -i lms-mysql mysql -u root -p'PASSWORD_DB' lms2025 < /tmp/nama_file_dump.sql
```

> ⚠️ **Jebakan Umum**: Jangan beri spasi antara `-p` dan password. Jangan gunakan kutip ganda (`"`) untuk password yang mengandung karakter khusus seperti `$`, `{`, `}`. Selalu gunakan kutip tunggal (`'`).

**Reset Database (jika perlu mengulang):**
Jika password `.env` berubah setelah MySQL pertama kali diinisialisasi, data di volume lama tidak akan otomatis terupdate. Solusi (hanya aman jika data masih kosong):
```bash
docker compose down
docker volume rm lms2025_mysql_data
docker compose up -d
# Setelah sehat, ulangi import dump
```

### B. Migrasi Laravel Storage

```bash
# Cari lokasi mountpoint volume di host
VOLUME_PATH=$(docker volume inspect lms2025_storage_data -f '{{ .Mountpoint }}')

# Salin isi storage lama ke dalam mountpoint volume
sudo cp -r /path/storage_lama/app/* $VOLUME_PATH/app/

# Kembalikan kepemilikan ke www-data
docker exec lms-app chown -R www-data:www-data /var/www/html/storage

# Buat symlink storage
docker exec lms-app php artisan storage:link
```

### C. Post-Deploy Commands

```bash
docker exec lms-app php artisan key:generate
docker exec lms-app php artisan storage:link
docker exec lms-app php artisan optimize:clear
```

---

## 8. Tahap 5 — Setup Cloudflare Tunnel

Cloudflare Tunnel sudah terinstall di VPS dan tunnel sudah aktif. Yang perlu ditambahkan adalah route untuk subdomain baru.

### A. Edit Konfigurasi Tunnel

```bash
sudo nano /etc/cloudflared/config.yml
```

Tambahkan ingress baru (sebelum baris `service: http_status:404`):

```yaml
tunnel: <TUNNEL_ID>
credentials-file: /etc/cloudflared/<TUNNEL_ID>.json

ingress:
  # ... ingress lain yang sudah ada ...

  # Tambahkan ini:
  - hostname: edukasiv2.digitaltwinassistance.com
    service: http://localhost:8080
    originRequest:
      noTLSVerify: true

  - service: http_status:404  # ← harus selalu di paling bawah
```

### B. Daftarkan DNS dan Restart

```bash
# Daftarkan CNAME di Cloudflare DNS
cloudflared tunnel route dns <NAMA_TUNNEL> edukasiv2.digitaltwinassistance.com

# Restart cloudflared agar konfigurasi baru terbaca
sudo systemctl restart cloudflared
```

> 💡 **Catatan**: Perintahnya adalah `cloudflared` (dengan `d` di akhir), bukan `cloudflare`.

---

## 9. Tahap 6 — Setup CI/CD Self-Hosted Runner

### Mengapa Self-Hosted Runner?

VPS ini menggunakan **IP Lokal** (`192.168.0.223`) di belakang NAT dan tidak memiliki IP publik static. Karena itu, GitHub Actions tidak bisa SSH masuk ke VPS dari luar.

Solusinya adalah **Self-Hosted Runner**: program kecil yang berjalan di VPS dan secara aktif "mendengarkan" perintah dari GitHub. GitHub tidak perlu "masuk" ke VPS — VPS-lah yang menghubungi GitHub.

```
GitHub ──(instruksi via HTTPS outbound)──► Runner di VPS ──► docker compose
```



### B. Download & Install Runner

```bash
cd ~
mkdir -p actions-runner && cd actions-runner

# Download runner (sesuaikan versi dengan yang terbaru)
curl -o actions-runner-linux-x64.tar.gz -L \
  https://github.com/actions/runner/releases/download/v2.334.0/actions-runner-linux-x64-2.334.0.tar.gz

tar xzf ./actions-runner-linux-x64.tar.gz
```

### C. Dapatkan Token Registrasi

Buka: `https://github.com/wahanamediadigital/edugames-v2/settings/actions/runners/new`

Pilih **Linux** > **x64**, salin token dari baris `--token XXXXXX`.

### D. Registrasi Runner ke GitHub

```bash
./config.sh \
  --url https://github.com/wahanamediadigital/edugames-v2 \
  --token TOKEN_DARI_GITHUB \
  --name "nuc-lab-runner" \
  --labels "self-hosted,linux,x64,nuc-lab" \
  --work "_work" \
  --unattended
```

Output sukses:
```
√ Connected to GitHub
√ Runner successfully added
√ Settings Saved.
```

### E. Install sebagai Systemd Service (Auto-start)

```bash
sudo ./svc.sh install
sudo ./svc.sh start
sudo ./svc.sh status
```

Output yang diharapkan: `Active: active (running)` dan `√ Connected to GitHub`.

### F. Workflow CI/CD (`.github/workflows/deploy-vps.yml`)

```yaml
name: Deploy to VPS (Docker)

on:
  push:
    branches:
      - main

jobs:
  deploy:
    name: 🚀 Build & Deploy
    runs-on: [self-hosted, linux, nuc-lab]  # ← pakai runner kita

    steps:
      - name: 🚚 Checkout code
        uses: actions/checkout@v4

      - name: 🚀 Deploy langsung di VPS
        working-directory: /opt/services/lms2025
        run: |
          set -e
          git pull origin main
          docker compose build app
          docker compose up -d --remove-orphans
          sleep 10
          docker exec lms-app php artisan down --retry=5
          docker exec lms-app php artisan migrate --force
          docker exec lms-app php artisan optimize:clear
          docker exec lms-app php artisan storage:link
          docker exec lms-app php artisan config:cache
          docker exec lms-app php artisan route:cache
          docker exec lms-app php artisan view:cache
          docker exec lms-app php artisan up
```

---

## 10. Troubleshooting yang Pernah Terjadi

### ❌ Error: "File not found." (Halaman Kosong)

**Gejala**: Browser menampilkan teks biru "File not found." dengan background putih.

**Penyebab**: Nginx tidak bisa menemukan `index.php` karena file ada di dalam container `app`, bukan di container `nginx`. Nginx mencoba resolve path menggunakan `$realpath_root` yang tidak berfungsi lintas-container.

**Solusi**:
1. Ubah `$realpath_root` menjadi `$document_root` di `docker/nginx/default.conf`
2. Buat shared volume `public_data` yang diisi oleh container `app` saat startup dan di-mount oleh container `nginx`
3. Tambahkan script entrypoint di `Dockerfile` yang menyalin `public/*` ke `shared_public/`

---

### ❌ Error: CSS/JS ter-load via HTTP (Mixed Content)

**Gejala**: Di browser DevTools, banyak asset (CSS, JS, gambar) berstatus `(blocked:mixed-content)` karena diload via `http://` padahal halaman diakses via `https://`.

**Penyebab**: Cloudflare Tunnel meneruskan request ke Nginx menggunakan HTTP lokal. Laravel tidak tahu bahwa request aslinya adalah HTTPS, sehingga fungsi `asset()` menghasilkan URL dengan skema `http://`.

**Solusi** (dua lapis):
```php
// bootstrap/app.php — percayai semua proxy
$middleware->trustProxies(at: '*');

// AppServiceProvider.php — paksa skema HTTPS
if (env('APP_ENV') === 'production') {
    \Illuminate\Support\Facades\URL::forceScheme('https');
}
```

> ⚠️ Setelah mengubah file PHP, **wajib build ulang Docker** karena kode di-bake ke dalam image saat build.

---

### ❌ Error: `composer dump-autoload` gagal saat Docker build

**Gejala**: Build Docker gagal dengan error "Database file does not exist" atau "Table not found".

**Penyebab**: Composer menjalankan post-autoload scripts (termasuk `artisan package:discover`) yang mencoba konek ke database yang belum ada saat build.

**Solusi**: Tambahkan flag `--no-scripts` pada perintah `composer dump-autoload`:
```dockerfile
RUN composer dump-autoload --optimize --no-dev --no-scripts
```

---

### ❌ Error: Password MySQL dengan karakter khusus tidak terbaca

**Gejala**: Perintah `docker exec mysql ...` menampilkan halaman `--help` mysql alih-alih menjalankan query.

**Penyebab**: Karakter khusus di password (`$`, `{`, `}`) diinterpretasikan oleh shell sebagai variabel environment.

**Solusi**: Selalu gunakan kutip tunggal (`'`) dan **tanpa spasi** setelah `-p`:
```bash
# ❌ Salah
docker exec -i lms-mysql mysql -u root -p ${PASSWORD} lms2025

# ✅ Benar
docker exec -i lms-mysql mysql -u root -p'PASSWORD_LITERAL' lms2025
```

---

### ❌ Perubahan kode tidak terlihat setelah `git pull`

**Penyebab**: Pada arsitektur Docker production ini, source code di-*bake* ke dalam image saat `docker compose build`. Melakukan `git pull` saja hanya memperbarui file di host VPS, tidak di dalam container yang sedang berjalan.

**Solusi**: Selalu jalankan build ulang setelah pull:
```bash
git pull origin main
docker compose up -d --build
```

---

## 11. Referensi Perintah Harian

### Deploy Manual

```bash
cd /opt/services/lms2025
git pull origin main
docker compose build app
docker compose up -d --remove-orphans
docker exec lms-app php artisan optimize:clear
```

### Cek Status

```bash
# Status semua container
docker compose ps

# Log real-time container app
docker compose logs -f app

# Log CI/CD Runner
sudo journalctl -u actions.runner.wahanamediadigital-edugames-v2.nuc-lab.service -f
```

### Maintenance Mode

```bash
# Aktifkan maintenance mode
docker exec lms-app php artisan down

# Nonaktifkan maintenance mode
docker exec lms-app php artisan up
```

### Reset Penuh (Bahaya! Hapus semua data)

```bash
docker compose down
docker volume rm lms2025_mysql_data lms2025_storage_data lms2025_public_data lms2025_redis_data
docker compose up -d --build
```

### Restart Runner CI/CD

```bash
sudo systemctl restart actions.runner.wahanamediadigital-edugames-v2.nuc-lab.service
sudo systemctl status actions.runner.wahanamediadigital-edugames-v2.nuc-lab.service
```

---

*Dokumentasi ini dibuat pada 26 Mei 2026. Revisi terakhir setelah CI/CD Self-Hosted Runner berhasil berjalan pertama kali.*
