# 📊 Panduan Setup Monitoring & Alerting Server (Grafana & Uptime Kuma)

Dokumen ini berisi panduan teknis langkah demi langkah (step-by-step) untuk mengonfigurasi sistem *Monitoring* dan *Alerting* pada VPS NUC Lab. 

Sistem ini memastikan notifikasi akan otomatis dikirimkan ke Discord dan Telegram jika server mengalami kendala kritis.

---

## 📌 Daftar Isi
1. [Uptime Kuma: Monitor Layanan](#1-uptime-kuma-monitor-layanan)
2. [Troubleshooting Cockpit (TLS Error)](#2-troubleshooting-cockpit-tls-error)
3. [Setup Kredensial Notifikasi (Discord & Telegram)](#3-setup-kredensial-notifikasi-discord--telegram)
4. [Konfigurasi Alert di Grafana](#4-konfigurasi-alert-di-grafana)
5. [Membuat Alert Rules Inti (CPU, RAM, Storage)](#5-membuat-alert-rules-inti-cpu-ram-storage)

---

## 1. Uptime Kuma: Monitor Layanan

Uptime Kuma digunakan untuk memonitor apakah website/layanan bisa diakses dari luar. Tambahkan monitor berikut pada dashboard Uptime Kuma:

| Nama Monitor | Tipe | Target / URL | Fungsi |
|---|---|---|---|
| **LMS Website** | `HTTP(s)` | `https://edukasiv2.digitaltwinassistance.com` | Memastikan website utama menyala |
| **LMS Health Check** | `HTTP(s)` | `https://edukasiv2.digitaltwinassistance.com/up` | Mengecek endpoint health bawaan Laravel |
| **LMS MySQL** | `TCP Port` | `192.168.0.223:3306` | Memastikan service database di container hidup |

---

## 2. Troubleshooting Cockpit (TLS Error)

**Masalah:** Status Cockpit di Uptime Kuma `Down` dengan error `502 Bad Gateway`, sementara service di Linux `active (running)`. Di log muncul pesan `gnutls_handshake failed: A TLS fatal alert has been received.`.

**Penyebab:** Cloudflare Tunnel mencoba melakukan verifikasi SSL/TLS saat meneruskan traffic ke `https://localhost:9090` (Cockpit). Karena Cockpit menggunakan *self-signed certificate*, verifikasi gagal.

**Solusi:**
Buka konfigurasi Cloudflared di `/etc/cloudflared/config.yml` dan tambahkan `noTLSVerify: true` pada block Cockpit:
```yaml
  - hostname: cockpit.digitaltwinassistance.com
    service: https://localhost:9090
    originRequest:
      noTLSVerify: true
```
Setelah itu restart service: `sudo systemctl restart cloudflared`.

---

## 3. Setup Kredensial Notifikasi (Discord & Telegram)

Sebelum masuk ke Grafana, siapkan URL dan Token dari platform pengirim pesan.

### A. Discord Webhook
1. Buka Discord, klik kanan pada channel tujuan (misal `#server-alerts`) > **Edit Channel**.
2. Pilih **Integrations** > **Webhooks** > **New Webhook**.
3. Salin **Webhook URL** (contoh: `https://discord.com/api/webhooks/...`).

### B. Telegram Bot & Chat ID
1. Chat dengan **@BotFather** di Telegram.
2. Ketik `/newbot`, ikuti instruksi, dan simpan **BOT API Token** (contoh: `123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11`).
3. Mulai *Start* chat dengan bot tersebut.
4. Buka browser: `https://api.telegram.org/bot<TOKEN>/getUpdates`.
5. Cari angka `"id"` di dalam struktur `"chat"`. Itu adalah **Chat ID** kamu (contoh: `123456789`).

---

## 4. Konfigurasi Alert di Grafana

Langkah ini menghubungkan Grafana dengan Discord dan Telegram yang sudah dibuat di atas.

### A. Membuat Contact Point (Destinasi Notifikasi)
1. Di Grafana, masuk ke **Alerting** (🔔) > **Contact points**.
2. Klik **+ Add contact point**.
3. Beri nama: `Semua Alert (Discord & Telegram)`.
4. **Integration 1 (Discord)**: 
   - Pilih `Discord`, paste *Webhook URL*.
5. Scroll ke bawah, klik **+ Add contact point integration**.
6. **Integration 2 (Telegram)**:
   - Pilih `Telegram`, masukkan *BOT API Token* dan *Chat ID*.
7. Klik **Save contact point**.

### B. Mengatur Notification Policy (Aturan Rute)
1. Masuk ke **Alerting** (🔔) > **Notification policies**.
2. Edit **Default policy**.
3. Ubah bagian **Contact point** ke nama yang baru dibuat: `Semua Alert (Discord & Telegram)`.
4. Klik **Save**. (Sekarang semua alert akan otomatis dikirim ke Discord dan Telegram bersamaan).

---

## 5. Membuat Alert Rules Inti (CPU, RAM, Storage)

Untuk memonitor kesehatan server (VPS), kita perlu membuat Alert Rules berdasarkan metric dari `node-exporter` (Data source: **Prometheus**).

Buka **Alerting** (🔔) > **Alert rules** > **+ New alert rule**. Atur pengelompokan sebagai berikut untuk *semua* aturan di bawah ini:
- **Folder**: `VPS Monitor`
- **Evaluation group**: `Cek 1 Menit` (Interval: 1m)
- **Pending period ("For")**: `5m` *(Sangat Penting! Ini mencegah spam alert/alarm palsu saat terjadi spike beberapa detik saja. Alert hanya dikirim jika batas dilewati selama 5 menit berturut-turut).*

---

### A. Alert CPU Usage (Tinggi)
Mendeteksi apakah CPU VPS bekerja sangat berat secara terus-menerus.
- **Rule Name**: `Peringatan CPU Server Tinggi`
- **Rumus (Code)**:
  ```promql
  100 - (avg by (instance) (rate(node_cpu_seconds_total{mode="idle"}[5m])) * 100)
  ```
- **Alert Condition (IS ABOVE)**: `80` (Diatas 80%)
- **Summary**: `🚨 Pemakaian CPU VPS di atas 80%!`
- **Description**: `Perhatian! Pemakaian rata-rata CPU NUC Lab kamu berada di atas 80% selama 5 menit berturut-turut. Segera cek container Docker.`

---

### B. Alert RAM Usage (Sisa Menipis)
Mendeteksi apakah kapasitas Memori/RAM server hampir habis terpakai.
- **Rule Name**: `Peringatan RAM Server Tinggi`
- **Rumus (Code)**:
  ```promql
  (1 - (node_memory_MemAvailable_bytes / node_memory_MemTotal_bytes)) * 100
  ```
- **Alert Condition (IS ABOVE)**: `85` (Diatas 85%)
- **Summary**: `🚨 Pemakaian RAM VPS Kritis (Di atas 85%)!`
- **Description**: `Peringatan! Sisa RAM di NUC Lab menipis. Pemakaian memori di atas 85% selama 5 menit. Cek apakah ada proses Docker yang memakan memori (Memory Leak).`

---

### C. Alert Storage / Disk Space (Hampir Penuh)
Mendeteksi apakah kapasitas Hardisk utama (`/`) akan segera penuh. Server akan crash (Database corrupt) jika storage mencapai 100%.
- **Rule Name**: `Peringatan Storage Server Penuh`
- **Rumus (Code)**:
  ```promql
  100 - ((node_filesystem_avail_bytes{mountpoint="/"} / node_filesystem_size_bytes{mountpoint="/"}) * 100)
  ```
- **Alert Condition (IS ABOVE)**: `85` (Diatas 85%)
- **Summary**: `💽 Kapasitas Hardisk VPS Hampir Penuh (>85%)!`
- **Description**: `Peringatan! Sisa ruang penyimpanan (Storage) di NUC Lab menipis. Segera bersihkan log lama, image Docker yang tidak terpakai (docker system prune), atau backup database agar server tidak crash.`

---
*Dokumen ini diperbarui terakhir kali saat setup environment LMS2025.*
