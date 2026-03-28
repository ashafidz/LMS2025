# Fitur Deteksi Wajah — Penjelasan untuk Sidang Tugas Akhir

> Dokumen ini dibuat khusus untuk keperluan presentasi dan tanya jawab sidang Tugas Akhir. Isinya merangkum fitur **Camera Monitoring & Face Violation Detection** yang diimplementasikan pada sistem LMS2025, dilengkapi penjelasan alasan teknis dan akademis di balik setiap keputusan desain.

---

## 1. Latar Belakang & Motivasi

Sistem LMS (_Learning Management System_) yang hanya berbasis soal dan waktu tidak cukup untuk menjamin integritas ujian online. Salah satu kelemahan utamanya adalah siswa dapat melihat ke sumber lain (contekan, perangkat lain, orang lain di ruangan) selama mengerjakan kuis tanpa bisa terdeteksi.

**Masalah yang diselesaikan fitur ini:**

> _"Bagaimana sistem dapat mendeteksi secara otomatis bahwa siswa tidak fokus ke layar saat mengerjakan kuis online?"_

**Solusi yang diimplementasikan:**
Sistem menggunakan kamera webcam siswa untuk menganalisis **arah kepala secara real-time** menggunakan library _computer vision_ berbasis AI, kemudian mencatat setiap penyimpangan sebagai pelanggaran.

---

## 2. Gambaran Umum Sistem

Sistem ini sepenuhnya berjalan di **sisi klien (browser siswa)** — tidak ada video yang dikirim ke server. Yang dikirim ke server hanya:

- Jenis pelanggaran (`look_left`, `look_right`, `look_up`, `look_down`, `face_not_detected`)
- Waktu kejadian
- Satu _screenshot_ JPEG saat pelanggaran terdeteksi

Ini menjaga privasi siswa sekaligus meminimalkan beban jaringan/server.

```
Kamera → AI (browser) → Analisis Pose → Pelanggaran? → Kirim Log ke Server
                                                  ↓
                                        Screenshot + Metadata
```

---

## 3. Teknologi yang Digunakan

| Komponen      | Teknologi                        | Alasan Pemilihan                                                |
| ------------- | -------------------------------- | --------------------------------------------------------------- |
| Deteksi wajah | **MediaPipe Face Mesh** (Google) | Ringan, berjalan di browser via WebAssembly, tidak perlu GPU    |
| Model AI      | TensorFlow Lite (WASM)           | Model telah dilatih dan dioptimalkan untuk real-time di browser |
| Backend       | **Laravel 11** (PHP 8.2)         | Framework utama LMS2025                                         |
| Database      | **MySQL**                        | Penyimpanan log pelanggaran dan konfigurasi                     |

### Mengapa MediaPipe Face Mesh?

MediaPipe Face Mesh adalah library open-source dari Google yang mampu mendeteksi **468 titik landmark** pada wajah manusia secara real-time di dalam browser biasa. Kelebihannya:

- Tidak memerlukan instalasi software tambahan di perangkat siswa
- Bekerja di WebAssembly → performa mendekati native tanpa plugin
- Model sudah terlatih dan terbukti akurat untuk berbagai kondisi pencahayaan
- Gratis dan open-source

---

## 4. Cara Kerja — Penjelasan Singkat

Proses deteksi berjalan dalam **5 langkah berantai** setiap beberapa detik:

### Langkah 1: Ambil Frame Video

Kamera mengambil gambar beresolusi kecil (320×240 piksel) sekitar 30 kali per detik. Resolusi kecil dipilih agar tidak membebani CPU laptop/komputer siswa.

### Langkah 2: Deteksi Titik Wajah (Landmark)

MediaPipe menganalisis setiap frame dan menghasilkan **468 koordinat titik** yang tersebar di seluruh permukaan wajah. Sistem hanya mengambil **4 titik kunci**:

| Titik   | Posisi                 | Fungsi              |
| ------- | ---------------------- | ------------------- |
| `[1]`   | Ujung hidung           | Penanda pusat wajah |
| `[33]`  | Sudut dalam mata kiri  | Batas sisi kiri     |
| `[263]` | Sudut dalam mata kanan | Batas sisi kanan    |
| `[152]` | Dagu bawah             | Referensi vertikal  |

### Langkah 3: Hitung Sudut Kepala

Dari 4 titik tersebut, sistem menghitung dua sudut:

**Yaw (menoleh kiri/kanan):**
Logikanya: _ketika kepala lurus, hidung berada tepat di tengah antara kedua mata_. Jika kepala menoleh kanan, hidung bergeser mendekati mata kiri.

$$\text{yaw} = \frac{|\text{hidung} - \text{mata kanan}| - |\text{hidung} - \text{mata kiri}|}{|\text{mata kanan} - \text{mata kiri}|}$$

**Pitch (mendongak/menunduk):**
Logikanya: _ketika kepala tegak, mata berada di sekitar setengah jarak antara hidung dan dagu_. Saat mendongak, mata dan hidung hampir sejajar.

$$\text{pitch} = \frac{|\text{hidung}_y - \text{rata-rata mata}_y|}{|\text{dagu}_y - \text{hidung}_y|} - 0{,}5$$

### Langkah 4: Bandingkan dengan Ambang Batas

| Kondisi       | Rumus           | Ambang | Arti                                                |
| ------------- | --------------- | ------ | --------------------------------------------------- |
| Menoleh kanan | `yaw > +0.45`   | +0.45  | Hidung bergeser >45% jarak antar-mata ke sisi kanan |
| Menoleh kiri  | `yaw < -0.45`   | -0.45  | Hidung bergeser >45% jarak antar-mata ke sisi kiri  |
| Mendongak     | `pitch < -0.30` | -0.30  | Rasio turun ke <0.20 dari normal 0.50               |
| Menunduk      | `pitch > +0.35` | +0.35  | Rasio naik ke >0.85 dari normal 0.50                |

### Langkah 5: Catat Pelanggaran (jika berlangsung cukup lama)

Sistem tidak langsung mencatat pelanggaran saat threshold terlampaui. Ada **grace period** yang dapat dikonfigurasi instruktur (default 3 detik). Jika kepala kembali normal sebelum durasi tercapai, pelanggaran dibatalkan.

---

## 5. Contoh Skenario Nyata

### Skenario A: Siswa menoleh ke kanan (melihat catatan di meja kanan)

```
Posisi normal   → nose.x = 0.50 → yaw = 0.0   ✅
Menoleh kanan   → nose.x = 0.40 → yaw = +0.83  🚨 > threshold +0.45
Timer dimulai...
Setelah 3 detik masih menoleh → dicatat sebagai 'look_right'
```

### Skenario B: Siswa bersin (kepala turun sejenak lalu kembali)

```
Menunduk        → pitch = +0.50  🚨 > threshold +0.35
Timer dimulai...
Setelah 1.2 detik kepala kembali lurus → timer reset, TIDAK dicatat
```

### Skenario C: Wajah tidak terdeteksi (siswa pergi dari depan kamera)

```
Siklus ke-1: wajah tidak ada → counter = 1
Siklus ke-2: wajah tidak ada → counter = 2
Siklus ke-3: wajah tidak ada → counter = 3 ≥ threshold → dicatat 'face_not_detected'
(3 × 5 detik interval = minimal 15 detik tidak terdeteksi)
```

---

## 6. Konfigurasi oleh Instruktur

Salah satu keunggulan sistem ini adalah **fleksibilitas konfigurasi**. Instruktur dapat menyesuaikan perilaku sistem melalui halaman Pengaturan Keamanan Kuis:

| Pengaturan              | Default  | Fungsi                                               |
| ----------------------- | -------- | ---------------------------------------------------- |
| Aktifkan Deteksi Kamera | Mati     | Master switch                                        |
| Threshold Pelanggaran   | 3        | Berapa kali melanggar sebelum kuis diblokir otomatis |
| Interval Deteksi        | 5 detik  | Seberapa sering sistem memeriksa                     |
| Durasi Pelanggaran      | 3 detik  | Berapa lama harus bertahan sebelum dihitung          |
| Deteksi: Wajah Hilang   | ✅ Aktif | Bisa dinonaktifkan per-kuis                          |
| Deteksi: Menoleh Kiri   | ✅ Aktif | Bisa dinonaktifkan per-kuis                          |
| Deteksi: Menoleh Kanan  | ✅ Aktif | Bisa dinonaktifkan per-kuis                          |
| Deteksi: Melihat Atas   | ✅ Aktif | Bisa dinonaktifkan per-kuis                          |
| Deteksi: Menunduk       | ✅ Aktif | Bisa dinonaktifkan per-kuis                          |

Model opt-out dipilih (semua aktif secara default) karena tujuan utamanya adalah monitoring — instruktur menonaktifkan jika ada alasan khusus (misal: kuis open-book, siswa perlu melihat kertas).

---

## 7. Apa yang Disimpan di Server?

Setiap pelanggaran yang dicatat menghasilkan dua record di database:

**a) Log Detail (`monitoring_logs`):**
Setiap kejadian disimpan satu baris: jenis pelanggaran, waktu, dan path screenshot.

**b) Ringkasan Sesi (`quiz_attempt_integrity_summaries`):**
Agregat per siswa per percobaan kuis: total pelanggaran, rincian per tipe, dan level risiko (Low/Medium/High).

Instruktur dapat melihat kembali semua bukti ini dari dashboard pemantauan kuis.

---

## 8. Keamanan & Privasi

Beberapa pertimbangan keamanan yang diimplementasikan:

| Aspek                         | Implementasi                                                                     |
| ----------------------------- | -------------------------------------------------------------------------------- |
| **Tidak ada streaming video** | Hanya screenshot statis yang dikirim saat pelanggaran, bukan video terus-menerus |
| **ID disamarkan**             | `attemptId` di URL menggunakan Hashids (obfuscation) untuk mencegah enumerasi ID |
| **Validasi kepemilikan**      | Server memverifikasi bahwa attempt milik siswa yang sedang login                 |
| **Whitelist violation type**  | Validasi server menolak nilai `violation_type` yang tidak ada di daftar resmi    |
| **CSRF Protection**           | Semua AJAX request menyertakan CSRF token Laravel                                |

---

## 9. Keterbatasan Sistem

Kejujuran akademis mengharuskan mengakui keterbatasan implementasi ini:

1. **Bukan PnP (Perspective-n-Point) yang sesungguhnya** — Algoritma yang digunakan adalah aproksimasi berbasis rasio landmark, bukan solusi geometri 3D penuh. Ini lebih ringan tapi kurang presisi dibanding metode akademis seperti solvePnP pada OpenCV.

2. **Tergantung pencahayaan** — MediaPipe dapat gagal mendeteksi wajah pada kondisi backlit (cahaya dari belakang siswa) atau pencahayaan sangat rendah.

3. **Satu wajah saja** — Sistem dikonfigurasi `maxNumFaces: 1`. Jika ada orang lain masuk ke frame, wajah mana yang dianalisis tidak dapat dijamin.

4. **Tidak ada deteksi mata** — Sistem mendeteksi arah _kepala_, bukan arah _pandangan mata_. Siswa bisa menoleh dengan mata tetap ke depan dan sistem tidak mendeteksinya.

5. **Bisa disiasati** — Seperti semua sistem proctoring berbasis kamera, ini bukan solusi mutlak. Fungsinya sebagai deterrent (pencegah) dan bukti pendukung, bukan bukti definitif kecurangan.

---

## 10. Poin-Poin Kemungkinan Pertanyaan Penguji

**Q: Mengapa tidak menggunakan server-side video analysis?**

> A: Server-side analysis memerlukan bandwidth tinggi, infrastruktur GPU/komputasi mahal, dan menimbulkan masalah privasi yang lebih kompleks. Client-side analysis dengan MediaPipe WASM memberikan keseimbangan yang baik antara akurasi, biaya, dan privasi.

**Q: Seberapa akurat sistem ini?**

> A: Sistem tidak dikalibrasi untuk akurasi persentase tertentu karena tujuannya adalah deterrent, bukan pendeteksi kecurangan yang definitif. Pengaturan threshold, durasi pelanggaran, dan interval deteksi dapat disetel oleh instruktur untuk menyesuaikan tingkat sensitivitas dengan konteks ujian.

**Q: Apa bedanya dengan sistem proctoring komersial seperti ProctorU?**

> A: Sistem komersial umumnya menggunakan server-side AI, rekaman video penuh, dan kadang melibatkan proctor manusia. Implementasi ini lebih ringan, gratis, dan menjaga privasi lebih baik — cocok untuk skala LMS institusi pendidikan dengan resource terbatas.

**Q: Mengapa threshold yaw = 0.45?**

> A: Nilai 0.45 berarti hidung harus bergeser sejauh 45% dari lebar jarak antar-mata sebelum dianggap pelanggaran. Nilai ini dipilih setelah pengujian empiris menunjukkan bahwa nilai lebih kecil (0.30) terlalu sensitif dan memicu false positive saat siswa hanya menggerakkan kepala sedikit secara alami. Nilai 0.45 memberikan toleransi gerakan kepala normal sehari-hari.

**Q: Bagaimana sistem mencegah false positive (pelanggaran yang tidak nyata)?**

> A: Ada dua lapis toleransi: (1) `detectionInterval` — sistem hanya memeriksa setiap N detik, bukan setiap frame; (2) `violationDuration` — pelanggaran hanya dihitung jika posisi kepala melanggar threshold secara _bertahan_ selama durasi minimum. Ini mencegah batuk, bersin, atau gerakan kepala spontan dihitung sebagai pelanggaran.

---

## 11. Alur Teknis Ringkas (untuk Referensi Cepat)

```
[Siswa membuka kuis]
        ↓
[PHP/Blade inject konfigurasi ke JS]
  - threshold, interval, durasi
  - 5 flag deteksi (on/off per tipe)
        ↓
[Browser: MediaPipe init + akses kamera]
        ↓
[Loop ~30fps: kirim frame ke Face Mesh]
        ↓
[468 landmark → ambil 4 titik kunci]
        ↓
[Hitung yaw (horizontal) & pitch (vertikal)]
        ↓
[Bandingkan dengan threshold]
        ↓ (jika melampaui threshold)
[Mulai timer grace period]
        ↓ (jika bertahan >= durasi)
[Capture screenshot + POST ke server]
        ↓
[Server validasi, simpan log, hitung total]
        ↓
[Return: should_block?]
   ├── Tidak → lanjutkan kuis
   └── Ya   → blokir kuis, countdown 5 detik, auto-submit
```
