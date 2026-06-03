# 📘 SOP Deployment Proyek Laravel Baru (VPS NUC Lab)

Dokumen ini adalah **Standar Operasional Prosedur (SOP)** yang bisa kamu ikuti setiap kali kamu punya **proyek Laravel baru** yang ingin di-deploy ke server NUC Lab menggunakan infrastruktur modern (Docker + Self-Hosted Runner + Cloudflare Tunnel).

Anggap ini sebagai "Buku Resep" yang tinggal kamu ikuti *step-by-step*.

---

## 🏗️ TAHAP 1: Persiapan Codebase di Lokal (Laptop)

Sebelum memikirkan server, pastikan proyek Laravel kamu siap di-containerize.

### 1. Siapkan File Konfigurasi Docker
Salin file-file berikut dari proyek LMS2025-v2 ke proyek baru kamu (pastikan strukturnya sama):
- `Dockerfile`
- `docker-compose.yml`
- `.dockerignore`
- Folder `docker/nginx/default.conf`
- Folder `docker/php/custom.ini`

**Penting di `docker-compose.yml`**:
Ganti semua nama container, volume, dan network agar tidak bentrok dengan proyek lain. 
- Misal `lms-app` diganti jadi `proyekbaru-app`.
- Misal `lms_network` diganti jadi `proyekbaru_network`.

### 2. Siapkan File CI/CD
Salin file `.github/workflows/deploy-vps.yml`.
- Sesuaikan nama runner jika diperlukan: `runs-on: [self-hosted, linux, nuc-lab-proyekbaru]`
- Sesuaikan `working-directory` ke path yang baru (misal: `/opt/services/proyekbaru`).
- Sesuaikan nama container di baris `docker exec ...` (misal: `docker exec proyekbaru-app php artisan ...`).

### 3. Paksa HTTPS di Laravel
Agar tidak terkena error *Mixed Content* saat melewati Cloudflare Tunnel:
1. Di `bootstrap/app.php`:
   ```php
   $middleware->trustProxies(at: '*');
   ```
2. Di `app/Providers/AppServiceProvider.php`:
   ```php
   if (env('APP_ENV') === 'production') {
       \Illuminate\Support\Facades\URL::forceScheme('https');
   }
   ```

### 4. Push ke GitHub
Buat repository GitHub baru, commit semua perubahan di atas, dan push ke branch `main`.

---

## 🖥️ TAHAP 2: Setup Awal di VPS NUC Lab

Masuk ke server NUC Lab kamu via SSH (`ssh pokemon@192.168.0.223`).

### 1. Clone Repository
```bash
mkdir -p /opt/services
cd /opt/services

# Ganti dengan URL repo proyek baru
git clone https://github.com/akhmadgibran/PROYEK_BARU.git proyekbaru
cd proyekbaru
```

### 2. Setup File `.env`
```bash
cp .env.example .env
nano .env
```
Sesuaikan:
- `APP_ENV=production`
- `APP_URL=https://subdomainbaru.digitaltwinassistance.com`
- `DB_HOST=mysql` (nama service di docker-compose)
- `REDIS_HOST=redis` (nama service di docker-compose)

### 3. Build Docker Pertama Kali
```bash
docker compose up -d --build
```
Tunggu sampai selesai. Setelah selesai, migrasi database dan generate key:
```bash
docker exec proyekbaru-app php artisan key:generate
docker exec proyekbaru-app php artisan migrate --force
docker exec proyekbaru-app php artisan storage:link
```

---

## 🌐 TAHAP 3: Publikasi via Cloudflare Tunnel

Proyek sudah menyala di localhost VPS (misal port 8081). Sekarang kita buka jalurnya ke internet.

### 1. Edit Config Cloudflared
```bash
sudo nano /etc/cloudflared/config.yml
```
Tambahkan rute baru di bawah `ingress:` (sebelum `http_status:404`):
```yaml
  - hostname: subdomainbaru.digitaltwinassistance.com
    service: http://localhost:8081  # Sesuaikan dengan port Nginx di docker-compose proyek ini
```

### 2. Daftarkan DNS
```bash
cloudflared tunnel route dns <NAMA_TUNNEL> subdomainbaru.digitaltwinassistance.com
sudo systemctl restart cloudflared
```
*Website proyek barumu sekarang sudah bisa diakses lewat internet!*

---

## 🤖 TAHAP 4: Setup Otomatisasi CI/CD (GitHub Actions)

Karena runner lama terikat ke repo `LMS2025`, kita harus mendaftarkan runner baru khusus untuk repo ini.

### 1. Dapatkan Token Baru dari GitHub
1. Buka repo baru di GitHub.
2. Ke **Settings** > **Actions** > **Runners**.
3. Klik **New self-hosted runner** > **Linux** > **x64**.
4. Salin Token-nya (`--token ABC123XYZ`).

### 2. Install Runner Kedua di VPS
Di NUC Lab, buat folder terpisah untuk runner proyek baru ini:
```bash
cd ~
mkdir -p actions-runner-proyekbaru && cd actions-runner-proyekbaru

# Download dan ekstrak
curl -o actions-runner-linux-x64.tar.gz -L https://github.com/actions/runner/releases/download/v2.334.0/actions-runner-linux-x64-2.334.0.tar.gz
tar xzf ./actions-runner-linux-x64.tar.gz

# Registrasi (Ganti TOKEN_BARU)
./config.sh \
  --url https://github.com/akhmadgibran/PROYEK_BARU \
  --token TOKEN_BARU \
  --name "nuc-lab-proyekbaru" \
  --labels "self-hosted,linux,nuc-lab-proyekbaru" \
  --work "_work" \
  --unattended

# Install sebagai service
sudo ./svc.sh install
sudo ./svc.sh start
```

### 3. Tes CI/CD
Lakukan perubahan kecil di kodemu di laptop, lalu jalankan:
```bash
git add .
git commit -m "tes deploy otomatis"
git push origin main
```
Buka tab **Actions** di GitHub, dan lihat proses deployment berjalan otomatis langsung ke server NUC Lab-mu.

---
*Prosedur ini bisa diulang untuk sebanyak apa pun proyek Laravel yang ingin kamu host di server NUC Lab.*
