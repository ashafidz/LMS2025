# Teori & Teknis: Sistem Deteksi Wajah dan Pelanggaran Kamera

> Dokumentasi ini menjelaskan secara **teknis dan teoritis** bagaimana sistem monitoring kamera di LMS2025 bekerja — mulai dari akuisisi gambar, analisis pose kepala, logika pelanggaran, hingga penyimpanan bukti. Semua penjelasan berbasis langsung pada kode di `resources/views/student/quizzes/take.blade.php` dan `app/Http/Controllers/Student/StudentQuizController.php`.

---

## Daftar Isi

1. [Gambaran Arsitektur](#1-gambaran-arsitektur)
2. [Technology Stack](#2-technology-stack)
3. [Fase 1 — Inisialisasi Kamera & MediaPipe](#3-fase-1--inisialisasi-kamera--mediapipe)
4. [Fase 2 — Ekstraksi Facial Landmarks](#4-fase-2--ekstraksi-facial-landmarks)
5. [Fase 3 — Kalkulasi Head Pose (Yaw & Pitch)](#5-fase-3--kalkulasi-head-pose-yaw--pitch)
6. [Fase 4 — Pengecekan Threshold (Violation Detection)](#6-fase-4--pengecekan-threshold-violation-detection)
7. [Fase 5 — Sustained Violation Tracking](#7-fase-5--sustained-violation-tracking)
8. [Fase 6 — Deteksi Wajah Tidak Terlihat](#8-fase-6--deteksi-wajah-tidak-terlihat)
9. [Fase 7 — Pencatatan Pelanggaran ke Server](#9-fase-7--pencatatan-pelanggaran-ke-server)
10. [Fase 8 — Auto-Block & Submit Otomatis](#10-fase-8--auto-block--submit-otomatis)
11. [Konfigurasi oleh Instruktur](#11-konfigurasi-oleh-instruktur)
12. [Skema Database](#12-skema-database)
13. [Diagram Alur Lengkap](#13-diagram-alur-lengkap)

---

## 1. Gambaran Arsitektur

Sistem monitoring kamera berjalan sepenuhnya di **sisi klien (browser siswa)**, kecuali pencatatan log yang dikirim ke server via AJAX. Tidak ada stream video yang dikirim ke server — hanya **metadata pose** dan **screenshot JPEG** saat pelanggaran terjadi.

```
[Kamera Webcam]
      │  frame video (320x240 px, ~30 fps)
      ▼
[MediaPipe Face Mesh WASM]
      │  468 koordinat titik wajah (x, y, z) per frame
      ▼
[calculateHeadPose()]
      │  {yaw, pitch} — 2 angka desimal
      ▼
[checkPoseViolation()]
      │  'look_left' | 'look_right' | 'look_up' | 'look_down' | null
      ▼
[Sustained Violation Timer]
      │  hitung durasi pelanggaran berlangsung
      ▼
[logCameraViolation()]  ──screenshot──▶  [Laravel Server]
      │                                        │
      │                                  [MonitoringLog]
      │                                  [QuizAttemptIntegritySummary]
      ▼
[should_block check]
      │  jika total >= threshold
      ▼
[handleCameraBlock()] → Auto-submit kuis
```

---

## 2. Technology Stack

| Komponen       | Teknologi                               | Versi / Sumber                               |
| -------------- | --------------------------------------- | -------------------------------------------- |
| Face Detection | **MediaPipe Face Mesh**                 | `cdn.jsdelivr.net/npm/@mediapipe/face_mesh`  |
| Model AI       | TensorFlow Lite (WASM)                  | Dijalankan di dalam browser (WebAssembly)    |
| Video Pipeline | **MediaPipe Camera Utils**              | `@mediapipe/camera_utils`                    |
| Screenshot     | HTML5 Canvas API (`toBlob`)             | Native browser                               |
| Backend        | **Laravel 11**                          | PHP 8.2                                      |
| Log Storage    | MySQL + Laravel Storage (disk `public`) | `storage/app/public/screenshots/violations/` |

### Mengapa MediaPipe Face Mesh?

MediaPipe Face Mesh menggunakan model neural network ringan yang dapat berjalan di **WebAssembly** tanpa GPU khusus. Model ini telah dilatih untuk mendeteksi tepat **468 titik landmark** yang tersebar di seluruh permukaan wajah manusia secara real-time, bahkan di hardware kelas menengah. Koordinat setiap titik dinormalisasi dalam rentang `[0.0, 1.0]` relatif terhadap ukuran frame video.

---

## 3. Fase 1 — Inisialisasi Kamera & MediaPipe

**Kode:** `initializeCamera()` di `take.blade.php`

```javascript
faceMesh = new FaceMesh({
    locateFile: (file) =>
        `https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/${file}`,
});

faceMesh.setOptions({
    maxNumFaces: 1, // Hanya deteksi 1 orang (siswa)
    refineLandmarks: true, // Aktifkan landmark presisi tinggi (iris, bibir)
    minDetectionConfidence: 0.5, // Threshold keyakinan awal
    minTrackingConfidence: 0.5, // Threshold keyakinan tracking antar-frame
});
```

Setelah Face Mesh siap, kamera diakses via `Camera` dari `@mediapipe/camera_utils`:

```javascript
camera = new Camera(cameraPreview, {
    onFrame: async () => {
        await faceMesh.send({ image: cameraPreview });
    },
    width: 320,
    height: 240,
});
```

Setiap frame video (sekitar 30x per detik), `faceMesh.send()` dipanggil untuk memproses gambar. Hasilnya dikembalikan secara asynchronous melalui callback `faceMesh.onResults(onFaceMeshResults)`.

> **Mengapa 320x240?** Resolusi ini dipilih sebagai trade-off antara akurasi dan performa. Resolusi yang lebih tinggi meningkatkan beban CPU browser siswa tanpa kontribusi signifikan pada akurasi deteksi arah kepala.

**Inisialisasi ditunda 1 detik** setelah page load (`setTimeout(..., 1000)`) untuk memastikan semua elemen DOM sudah siap.

---

## 4. Fase 2 — Ekstraksi Facial Landmarks

**Kode:** `onFaceMeshResults(results)` di `take.blade.php`

Ketika MediaPipe selesai memproses satu frame, ia mengembalikan objek `results`:

```javascript
results.multiFaceLandmarks; // Array of face arrays
results.multiFaceLandmarks[0]; // Array 468 titik untuk wajah pertama
results.multiFaceLandmarks[0][1]; // Titik ke-2: ujung hidung — { x, y, z }
```

Sistem hanya menggunakan **4 landmark kunci** dari 468 titik yang tersedia:

| Index   | Bagian Wajah                                   | Fungsi                   |
| ------- | ---------------------------------------------- | ------------------------ |
| `[1]`   | Ujung hidung (nose tip)                        | Referensi sentral wajah  |
| `[33]`  | Sudut dalam mata kiri (dari perspektif kamera) | Batas lateral kiri       |
| `[263]` | Sudut dalam mata kanan                         | Batas lateral kanan      |
| `[152]` | Dagu (chin bottom)                             | Referensi vertikal bawah |

Semua koordinat bersifat **ternormalisasi** (0.0 = tepi kiri/atas frame, 1.0 = tepi kanan/bawah frame).

> **Mengapa hanya 4 titik?** Karena head pose estimation tidak membutuhkan semua 468 titik. Hubungan geometris antara hidung, mata, dan dagu sudah cukup untuk mengekstrak yaw dan pitch dengan akurasi yang memadai untuk use case proctoring.

### Ilustrasi Koordinat Landmark per Kondisi Kepala

Berikut aproksimasi koordinat ternormalisasi (0.0–1.0) keempat landmark untuk masing-masing kondisi kepala. Nilai yang **ditebalkan** adalah koordinat yang berubah signifikan dibanding posisi lurus.

| Landmark             | Menghadap Lurus | Menoleh Kanan | Menoleh Kiri | Melihat Atas |  Menunduk  |
| -------------------- | :-------------: | :-----------: | :----------: | :----------: | :--------: |
| Hidung `[1]` x       |      0.50       |  **0.40** ←   |  **0.60** →  |     0.50     |    0.50    |
| Hidung `[1]` y       |      0.58       |     0.58      |     0.58     |  **0.50** ↑  | **0.63** ↓ |
| Mata Kiri `[33]` x   |      0.38       |     0.38      |     0.38     |     0.38     |    0.38    |
| Mata Kanan `[263]` x |      0.62       |     0.62      |     0.62     |     0.62     |    0.62    |
| Rata-rata Mata y     |      0.46       |     0.46      |     0.46     |   **0.47**   |  **0.45**  |
| Dagu `[152]` y       |      0.82       |     0.82      |     0.82     |  **0.88** ↓  | **0.81** ↑ |

> Panah ← → menandai hidung bergeser horizontal (menoleh). Panah ↑ ↓ menandai perubahan vertikal (mendongak/menunduk). Karena sumbu y meningkat ke bawah frame: y lebih kecil = posisi lebih tinggi di frame.

**Apa yang terjadi secara fisik:**

- **Menoleh kanan:** Kepala berputar ke kanan. Dari sudut kamera (non-mirrored), hidung bergerak ke kiri frame (x turun 0.50 → 0.40). Hidung hampir bertemu dengan mata kiri `[33]`, sekaligus menjauh dari mata kanan `[263]`.
- **Menoleh kiri:** Kebalikannya. Hidung bergerak ke kanan frame (x naik 0.50 → 0.60). Kali ini hidung mendekati mata kanan `[263]` dan menjauh dari mata kiri `[33]`.
- **Melihat atas:** Kepala mendongak ke belakang. Hidung naik di frame (y turun 0.58 → 0.50) hingga hampir sejajar vertikal dengan mata. Dagu merendah jauh (y naik 0.82 → 0.88) karena rahang ikut terbuka ke bawah.
- **Menunduk:** Kepala menunduk ke depan. Hidung merendah (y naik 0.58 → 0.63), menjauhi mata secara vertikal. Dagu mendekat ke hidung (y turun 0.82 → 0.81) karena wajah terlipat ke bawah.

---

## 5. Fase 3 — Kalkulasi Head Pose (Yaw & Pitch)

**Kode:** `calculateHeadPose(landmarks)` di `take.blade.php`

Ini adalah inti matematika dari sistem. Dua sudut dihitung:

- **Yaw**: rotasi horizontal (menoleh kiri/kanan)
- **Pitch**: rotasi vertikal (mendongak/menunduk)

### 5.1 Kalkulasi Yaw (Menoleh Horizontal)

Ide dasarnya: **ketika kepala menghadap lurus ke depan, hidung berada tepat di tengah antara kedua mata**. Ketika kepala menoleh, hidung akan bergeser mendekati salah satu mata.

```javascript
const eyeDistance = Math.abs(rightEye.x - leftEye.x);
const noseToLeftEye = Math.abs(nose.x - leftEye.x);
const noseToRightEye = Math.abs(nose.x - rightEye.x);

yaw = (noseToRightEye - noseToLeftEye) / eyeDistance;
```

**Derivasi matematis:**

Misalkan posisi horizontal (x):

- `L` = x mata kiri, `R` = x mata kanan, `N` = x hidung
- Jarak antar mata = `D = |R - L|`
- Hidung ke mata kiri = `dl = |N - L|`
- Hidung ke mata kanan = `dr = |N - R|`

$$\text{yaw} = \frac{d_r - d_l}{D}$$

- Saat kepala lurus: `dl ≈ dr` → yaw ≈ 0
- Saat menoleh kanan: hidung mendekati R → `dr` kecil, `dl` besar → yaw positif
- Saat menoleh kiri: hidung mendekati L → `dl` kecil, `dr` besar → yaw negatif

Rentang tipikal: **[-1.0, +1.0]**, walaupun tidak ter-clamp secara eksplisit.

#### Contoh Perhitungan Yaw: Menoleh Kanan vs Menoleh Kiri

Menggunakan koordinat dari tabel di atas. Jarak antar-mata konsisten di semua kondisi: `eyeDistance = |0.62 - 0.38| = 0.24`

**Kondisi A — Menghadap Lurus (nilai referensi):**

| Variabel         | Nilai   | Cara Hitung          |
| ---------------- | ------- | -------------------- |
| `eyeDistance`    | 0.24    | \|0.62 - 0.38\|      |
| `noseToLeftEye`  | 0.12    | \|0.50 - 0.38\|      |
| `noseToRightEye` | 0.12    | \|0.50 - 0.62\|      |
| **yaw**          | **0.0** | (0.12 - 0.12) / 0.24 |

Hasil: `yaw = 0.0` → ✅ Aman (jauh dari threshold ±0.45)

---

**Kondisi B — Menoleh Kanan (`look_right`):**

Kepala berputar ke kanan. Dari kamera, hidung bergeser ke kiri frame: `nose.x = 0.50 → 0.40`

| Variabel         | Nilai Normal (Lurus) | Nilai     | Cara Hitung                                       |
| ---------------- | :------------------: | --------- | ------------------------------------------------- |
| `eyeDistance`    |         0.24         | 0.24      | \|0.62 - 0.38\|                                   |
| `noseToLeftEye`  |         0.12         | **0.02**  | \|0.40 - 0.38\| — hidung hampir bertemu mata kiri |
| `noseToRightEye` |         0.12         | **0.22**  | \|0.40 - 0.62\| — hidung menjauhi mata kanan      |
| **yaw**          |       **0.0**        | **+0.83** | (0.22 - 0.02) / 0.24 = 0.20 / 0.24                |

Hasil: `yaw = +0.83 > +0.45` → 🚨 Pelanggaran `'look_right'`

---

**Kondisi C — Menoleh Kiri (`look_left`):**

Kepala berputar ke kiri. Hidung bergeser ke kanan frame: `nose.x = 0.50 → 0.60`

| Variabel         | Nilai Normal (Lurus) | Nilai     | Cara Hitung                                        |
| ---------------- | :------------------: | --------- | -------------------------------------------------- |
| `eyeDistance`    |         0.24         | 0.24      | \|0.62 - 0.38\|                                    |
| `noseToLeftEye`  |         0.12         | **0.22**  | \|0.60 - 0.38\| — hidung menjauhi mata kiri        |
| `noseToRightEye` |         0.12         | **0.02**  | \|0.60 - 0.62\| — hidung hampir bertemu mata kanan |
| **yaw**          |       **0.0**        | **-0.83** | (0.02 - 0.22) / 0.24 = -0.20 / 0.24                |

Hasil: `yaw = -0.83 < -0.45` → 🚨 Pelanggaran `'look_left'`

### 5.2 Kalkulasi Pitch (Menunduk/Mendongak)

Ide dasarnya: **ketika kepala tegak, titik mata berada di sekitar setengah jarak antara ujung hidung dan dagu**. Ketika mendongak atau menunduk, rasio ini berubah.

```javascript
const faceHeight = Math.abs(chin.y - nose.y);
const eyeToNose = Math.abs(nose.y - (leftEye.y + rightEye.y) / 2);

pitch = eyeToNose / faceHeight - 0.5;
```

**Derivasi matematis:**

Misalkan posisi vertikal (y):

- `C` = y dagu, `N` = y hidung, `M` = rata-rata y kedua mata
- Tinggi referensi wajah = `H = |C - N|`
- Jarak mata-ke-hidung = `e = |N - M|`

$$\text{pitch} = \frac{e}{H} - 0.5$$

- Saat kepala tegak: `e/H ≈ 0.5` → pitch ≈ 0
- Saat mendongak ke atas: rasio `e/H` menurun → pitch menjadi negatif
- Saat menunduk ke bawah: rasio `e/H` meningkat → pitch menjadi positif

> **Catatan arah pada implementasi ini:**
> Dalam sistem koordinat MediaPipe, sumbu Y meningkat ke bawah (seperti CSS). Karena itu:
>
> - **pitch negatif** (e/H < 0.5) → melihat ke atas
> - **pitch positif** (e/H > 0.5) → menunduk ke bawah

#### Contoh Perhitungan Pitch: Melihat Atas vs Menunduk

Menggunakan koordinat dari tabel di atas. Rata-rata y kedua mata ≈ 0.46 pada kondisi lurus.

**Kondisi A — Menghadap Lurus (nilai referensi):**

| Variabel      | Nilai   | Cara Hitung                       |
| ------------- | ------- | --------------------------------- |
| `nose.y`      | 0.58    | —                                 |
| `eyeCenter_y` | 0.46    | (0.46 + 0.46) / 2                 |
| `chin.y`      | 0.82    | —                                 |
| `eyeToNose`   | 0.12    | \|0.58 - 0.46\|                   |
| `faceHeight`  | 0.24    | \|0.82 - 0.58\|                   |
| **pitch**     | **0.0** | (0.12 / 0.24) - 0.5 = 0.50 - 0.50 |

Hasil: `pitch = 0.0` → ✅ Aman

---

**Kondisi B — Melihat Atas (`look_up`):**

Kepala mendongak ke atas. Hidung naik di frame dan hampir sejajar dengan mata (y turun). Dagu merendah jauh karena kepala mendongak ke belakang.

| Variabel      | Nilai Normal (Lurus) | Nilai     | Cara Hitung                                      |
| ------------- | :------------------: | --------- | ------------------------------------------------ |
| `nose.y`      |         0.58         | **0.50**  | ↑ naik mendekati mata                            |
| `eyeCenter_y` |         0.46         | 0.47      | (0.47 + 0.47) / 2                                |
| `chin.y`      |         0.82         | **0.88**  | ↓ turun jauh karena kepala mendongak             |
| `eyeToNose`   |         0.12         | **0.03**  | \|0.50 - 0.47\| — mata dan hidung hampir sejajar |
| `faceHeight`  |         0.24         | **0.38**  | \|0.88 - 0.50\| — jangkauan wajah melebar        |
| **pitch**     |       **0.0**        | **-0.42** | (0.03 / 0.38) - 0.5 = 0.08 - 0.50                |

Hasil: `pitch = -0.42 < -0.30` → 🚨 Pelanggaran `'look_up'`

---

**Kondisi C — Menunduk (`look_down`):**

Kepala menunduk ke bawah. Hidung merendah menjauhi mata secara vertikal. Dagu mendekat ke hidung karena wajah terlipat ke depan.

| Variabel      | Nilai Normal (Lurus) | Nilai     | Cara Hitung                                       |
| ------------- | :------------------: | --------- | ------------------------------------------------- |
| `nose.y`      |         0.58         | **0.63**  | ↓ turun menjauhi mata                             |
| `eyeCenter_y` |         0.46         | 0.45      | (0.45 + 0.45) / 2                                 |
| `chin.y`      |         0.82         | **0.81**  | ↑ mendekat ke hidung                              |
| `eyeToNose`   |         0.12         | **0.18**  | \|0.63 - 0.45\| — jarak mata-hidung melebar       |
| `faceHeight`  |         0.24         | **0.18**  | \|0.81 - 0.63\| — jangkauan hidung-dagu menyempit |
| **pitch**     |       **0.0**        | **+0.50** | (0.18 / 0.18) - 0.5 = 1.00 - 0.50                 |

Hasil: `pitch = +0.50 > +0.35` → 🚨 Pelanggaran `'look_down'`

---

## 6. Fase 4 — Pengecekan Threshold (Violation Detection)

**Kode:** `checkPoseViolation(pose)` di `take.blade.php`

```javascript
const YAW_THRESHOLD = 0.45; // Ambang menoleh kiri/kanan
const PITCH_UP_THRESHOLD = -0.3; // Ambang mendongak
const PITCH_DOWN_THRESHOLD = 0.35; // Ambang menunduk

if (detectLookRight && pose.yaw > YAW_THRESHOLD) return "look_right";
if (detectLookLeft && pose.yaw < -YAW_THRESHOLD) return "look_left";
if (detectLookUp && pose.pitch < PITCH_UP_THRESHOLD) return "look_up";
if (detectLookDown && pose.pitch > PITCH_DOWN_THRESHOLD) return "look_down";
return null;
```

Setiap tipe juga difilter oleh flag `detectLookRight`, `detectLookLeft`, dll. — boolean yang di-inject dari PHP berdasarkan konfigurasi instruktur. Jika instruktur menonaktifkan "Menoleh Kanan", maka `detectLookRight = false` dan cabang `if` tersebut tidak pernah dieksekusi.

### Interpretasi nilai threshold

| Threshold      | Arti praktis                                                                   |
| -------------- | ------------------------------------------------------------------------------ |
| `yaw = 0.45`   | Hidung harus bergeser sejauh ≈45% dari jarak antar-mata untuk dianggap menoleh |
| `pitch = -0.3` | Rasio e/H harus turun ke 0.2 (turun 0.3 dari 0.5) untuk dianggap mendongak     |
| `pitch = 0.35` | Rasio e/H harus naik ke 0.85 untuk dianggap menunduk signifikan                |

### Rekapitulasi Keempat Kondisi

Berikut ringkasan nilai yaw/pitch dari contoh perhitungan di atas beserta keputusan pelanggaran:

| Kondisi Kepala  |    Yaw    |   Pitch   | Threshold yang Dilampaui |     Status      |
| --------------- | :-------: | :-------: | :----------------------: | :-------------: |
| Menghadap lurus |    0.0    |    0.0    |            —             |     ✅ Aman     |
| Menoleh kanan   | **+0.83** |    0.0    |       yaw > +0.45        | 🚨 `look_right` |
| Menoleh kiri    | **-0.83** |    0.0    |       yaw < -0.45        | 🚨 `look_left`  |
| Melihat atas    |    0.0    | **-0.42** |      pitch < -0.30       |  🚨 `look_up`   |
| Menunduk        |    0.0    | **+0.50** |      pitch > +0.35       | 🚨 `look_down`  |

---

## 7. Fase 5 — Sustained Violation Tracking

**Kode:** blok `if (violation)` di dalam `onFaceMeshResults()`

Ini adalah lapisan toleransi kedua. Bahkan jika pose melampaui threshold, sistem **tidak langsung melaporkan pelanggaran**. Pelanggaran harus _bertahan_ selama durasi minimum yang dikonfigurasi instruktur (`violationDuration`, default 3 detik).

```
Siklus deteksi (per detectionInterval ms):

Frame ke-N:    violation = 'look_left'
               sustainedViolationType = null
               → simpan: sustainedViolationType = 'look_left'
               → catat waktu mulai: sustainedViolationStart = now

Frame ke-N+1:  violation = 'look_left'  (masih berlangsung)
               elapsed = now - sustainedViolationStart  →  1.2 detik
               elapsed < violationDuration (3 detik)
               → log: "⏳ 1.2s / 3.0s" (belum dihitung)

Frame ke-N+2:  violation = 'look_left'  (masih berlangsung)
               elapsed = 3.1 detik >= violationDuration
               → logCameraViolation('look_left')  ← DIHITUNG
               → reset: sustainedViolationType = null

Frame ke-N+3:  violation = null (kembali normal)
               (tidak ada action)
```

Jika di tengah-tengah siswa kembali menghadap layar:

```
Frame ke-N+1:  violation = null
               sustainedViolationType = 'look_left' (ada)
               → reset tanpa catat pelanggaran
               → log: "✅ Pelanggaran look_left berhenti sebelum durasi tercapai"
```

### Pengaruh `detectionInterval`

`onFaceMeshResults` dipanggil ~30x/detik oleh MediaPipe, tetapi pengecekan pelanggaran dibatasi oleh `detectionInterval` (default 5 detik):

```javascript
if (now - lastViolationTime < detectionInterval) return;
```

Ini berarti setelah satu pelanggaran dicatat, sistem "istirahat" selama `detectionInterval` ms sebelum memeriksa lagi. Ini mencegah satu insiden memicu banyak pelanggaran secara cepat berturut-turut.

> **Trade-off:** `detectionInterval` yang panjang membuat sistem lebih ringan di CPU, tetapi mengurangi granularitas deteksi. Nilai yang terlalu singkat bisa menghasilkan false positive karena fluktuasi pose alami.

---

## 8. Fase 6 — Deteksi Wajah Tidak Terlihat

**Kode:** blok `if (!results.multiFaceLandmarks || ...)` di `onFaceMeshResults()`

Berbeda dari pose violation yang menggunakan sistem timer, `face_not_detected` menggunakan **counter berturutan**:

```javascript
const NO_FACE_THRESHOLD = 3; // hardcoded

if (!results.multiFaceLandmarks || results.multiFaceLandmarks.length === 0) {
    noFaceDetectedCount++;
    if (noFaceDetectedCount >= NO_FACE_THRESHOLD) {
        if (detectFaceNotDetected) {
            logCameraViolation("face_not_detected");
            lastViolationTime = now;
        }
        noFaceDetectedCount = 0;
    }
    return;
}
noFaceDetectedCount = 0; // reset jika wajah terlihat kembali
```

Sistem memerlukan **3 siklus deteksi berturutan** tanpa wajah sebelum mencatat pelanggaran. Dengan `detectionInterval` = 5 detik, artinya wajah harus tidak terdeteksi selama **≥ 15 detik** sebelum dihitung sebagai pelanggaran. Ini menghindari false positive dari:

- Siswa bersin atau batuk sejenak
- Fluktuasi pencahayaan sesaat
- Frame drop sementara pada kamera

---

## 9. Fase 7 — Pencatatan Pelanggaran ke Server

**Kode:** `logCameraViolation(violationType)` (JS) → `StudentQuizController@logCameraViolation` (PHP)

### 9.1 Capture Screenshot

Sebelum mengirim data pelanggaran, browser mengambil snapshot frame video saat itu:

```javascript
tempCanvas.width  = cameraPreview.videoWidth;   // 320
tempCanvas.height = cameraPreview.videoHeight;  // 240
tempCtx.drawImage(cameraPreview, 0, 0, ...);
tempCanvas.toBlob(blob => resolve(blob), 'image/jpeg', 0.8);
```

Format: **JPEG**, kualitas 0.8. Screenshot kemudian dikirim sebagai `multipart/form-data`.

### 9.2 HTTP Request ke Server

```javascript
const formData = new FormData();
formData.append("violation_type", violationType);
formData.append("timestamp", new Date().toISOString());
formData.append("screenshot", blob, `violation_${Date.now()}.jpg`);

fetch("/student/quiz/attempt/{attemptId}/log-camera-violation", {
    method: "POST",
    headers: { "X-CSRF-TOKEN": csrfToken },
    body: formData,
});
```

### 9.3 Pemrosesan di Server

**Controller:** `app/Http/Controllers/Student/StudentQuizController.php` — method `logCameraViolation()`

Server melakukan validasi berlapis:

1. Decode Hashid dari `attemptId` — ID diobfuscate dengan Hashids untuk mencegah enumeration attack
2. Cek kepemilikan: `$attempt->student_id == Auth::id()`
3. Cek status kuis: harus `in_progress`
4. Cek fitur kamera aktif di `quiz_security_settings`
5. Validasi `violation_type` harus salah satu dari whitelist: `['face_not_detected', 'look_left', 'look_right', 'look_down', 'look_up']`

Screenshot disimpan ke:

```
storage/app/public/screenshots/violations/{attemptId}/{violationType}_{timestamp}_{uniqid}.jpg
```

Kemudian dua record diperbarui di database:

**a) `MonitoringLog`** — log detail per kejadian:

```php
MonitoringLog::create([
    'attempt_id'          => $attempt->id,
    'violation_type'      => $violationType,
    'violation_timestamp' => now(),
    'screenshot_path'     => $screenshotPath,
]);
```

**b) `QuizAttemptIntegritySummary`** — aggregat per sesi kuis:

```php
$summary->increment($fieldMap[$violationType]);  // misal: look_left_count++
$summary->increment('total_face_violations');    // total++
```

### 9.4 Response & Keputusan Blokir

Server mengembalikan JSON:

```json
{
    "success": true,
    "violation_count": 7,
    "violation_breakdown": {
        "face_not_detected": 2,
        "look_left": 3,
        "look_right": 1,
        "look_down": 1,
        "look_up": 0
    },
    "threshold": 10,
    "should_block": false,
    "risk_level": "Medium"
}
```

Kunci `should_block = true` dikirim ketika `total_face_violations >= camera_violation_threshold`. Browser langsung menjalankan `handleCameraBlock()` setelah menerima ini.

---

## 10. Fase 8 — Auto-Block & Submit Otomatis

**Kode:** `handleCameraBlock()` di `take.blade.php`

Ketika `should_block = true` diterima dari server:

1. Set `isCameraBlocked = true` → hentikan semua pengecekan selanjutnya
2. Set `isQuizBlocked = true` → flag global quiz
3. Set `expelled-flag` input tersembunyi ke `'1'` → dikirim saat form submit → server set `expelled_by_violation = true` di tabel `quiz_attempts`
4. Disable semua input (radio, checkbox, textarea)
5. Disable tombol navigasi dan submit
6. Tampilkan SweetAlert countdown 5 detik (tidak bisa ditutup dengan klik luar / ESC)
7. Setelah countdown habis → `form.submit()` → kuis tersubmit otomatis

---

## 11. Konfigurasi oleh Instruktur

Seluruh parameter sistem dikonfigurasi dari halaman **Pengaturan Keamanan Kuis** dan disimpan di tabel `quiz_security_settings`. Data di-inject ke JavaScript via Blade saat halaman kuis dimuat siswa.

| Parameter                         | Tipe    | Default | Range | Fungsi                                       |
| --------------------------------- | ------- | ------- | ----- | -------------------------------------------- |
| `enable_camera_detection`         | boolean | false   | —     | Master switch seluruh sistem kamera          |
| `camera_violation_threshold`      | integer | 3       | 1–20  | Total pelanggaran sebelum kuis diblokir      |
| `face_detection_interval_seconds` | integer | 5       | 3–30  | Jeda waktu antar pengecekan (detik)          |
| `violation_duration_seconds`      | integer | 3       | 0–10  | Durasi bertahan pelanggaran sebelum dihitung |
| `detect_face_not_detected`        | boolean | true    | —     | Aktifkan/nonaktifkan deteksi wajah hilang    |
| `detect_look_left`                | boolean | true    | —     | Aktifkan/nonaktifkan deteksi menoleh kiri    |
| `detect_look_right`               | boolean | true    | —     | Aktifkan/nonaktifkan deteksi menoleh kanan   |
| `detect_look_up`                  | boolean | true    | —     | Aktifkan/nonaktifkan deteksi mendongak       |
| `detect_look_down`                | boolean | true    | —     | Aktifkan/nonaktifkan deteksi menunduk        |

---

## 12. Skema Database

### Tabel `quiz_security_settings`

Menyimpan konfigurasi per-kuis.

```
id, quiz_id (FK → quizzes.id),
enable_camera_detection (bool),
enable_tab_detection (bool),
enable_question_shuffle (bool),
camera_violation_threshold (int),
tab_violation_threshold (int),
face_detection_interval_seconds (int),
detect_face_not_detected (bool),
detect_look_left (bool),
detect_look_right (bool),
detect_look_up (bool),
detect_look_down (bool),
violation_duration_seconds (int),
created_at, updated_at
```

### Tabel `monitoring_logs`

Log detail setiap kejadian pelanggaran. Tidak ada `updated_at` (immutable — hanya insert, tidak pernah diubah).

```
id,
attempt_id (FK → quiz_attempts.id),
violation_type (enum: tab_switch | face_not_detected | look_left | look_right | look_down | look_up),
violation_timestamp (datetime),
duration_seconds (int, nullable),
screenshot_path (string, nullable),
additional_data (JSON, nullable),
created_at
```

### Tabel `quiz_attempt_integrity_summaries`

Agregat per sesi kuis, digunakan untuk evaluasi cepat dan keputusan blokir.

```
id,
attempt_id (FK → quiz_attempts.id, unique),
total_tab_switches (int),
total_face_violations (int),
face_not_detected_count (int),
look_left_count (int),
look_right_count (int),
look_down_count (int),
look_up_count (int),
risk_level (string),
created_at, updated_at
```

---

## 13. Diagram Alur Lengkap

```
PAGE LOAD
   │
   ├── @if($hasCameraDetection)  ← PHP: cek enable_camera_detection di DB
   │         │
   │    Inject ke JS (via Blade):
   │    - cameraThreshold, detectionInterval
   │    - violationDuration
   │    - detectLookLeft/Right/Up/Down, detectFaceNotDetected
   │         │
   │    setTimeout(initializeCamera, 1000ms)
   │         │
   │    [FaceMesh init + Camera.start()]
   │         │
   │    LOOP: onFrame() → faceMesh.send(videoFrame) ~30x/detik
   │
   │
onFaceMeshResults(results) dipanggil setiap frame:
   │
   ├── isCameraBlocked? → return immediately (stop semua)
   │
   ├── now - lastViolationTime < detectionInterval?
   │   └── YES → return (throttle — belum waktunya cek lagi)
   │
   ├── multiFaceLandmarks empty?
   │   ├── YES → noFaceDetectedCount++
   │   │         noFaceDetectedCount >= 3?
   │   │         ├── YES + detectFaceNotDetected?
   │   │         │     └── logCameraViolation('face_not_detected')
   │   │         └── NO  → tunggu lebih lanjut
   │   └── NO  → noFaceDetectedCount = 0
   │
   ├── calculateHeadPose(landmarks[1, 33, 263, 152])
   │   ├── yaw   = (noseToRightEye - noseToLeftEye) / eyeDistance
   │   └── pitch = (eyeToNose / faceHeight) - 0.5
   │
   ├── checkPoseViolation({yaw, pitch})
   │   ├── detectLookRight && yaw  >  0.45  → 'look_right'
   │   ├── detectLookLeft  && yaw  < -0.45  → 'look_left'
   │   ├── detectLookUp    && pitch < -0.30 → 'look_up'
   │   ├── detectLookDown  && pitch >  0.35 → 'look_down'
   │   └── (none match) → null
   │
   ├── violation != null?
   │   ├── violationDuration == 0
   │   │   └── logCameraViolation() langsung
   │   ├── sustainedViolationType == violation (sama, masih berlangsung)
   │   │   ├── elapsed >= violationDuration
   │   │   │   └── logCameraViolation() + reset timer
   │   │   └── elapsed < violationDuration
   │   │       └── log progress "⏳ Xs/Ys", tunggu frame berikutnya
   │   └── violation baru (berbeda dari sebelumnya)
   │       └── set sustainedViolationType + catat sustainedViolationStart = now
   │
   └── violation == null + sustainedViolationType ada?
       └── reset timer (pelanggaran batal, tidak jadi dihitung)


logCameraViolation(type):
   ├── captureScreenshot()
   │   └── canvas.drawImage(videoEl) → toBlob() → JPEG kualitas 0.8
   │
   ├── POST /student/quiz/attempt/{hashId}/log-camera-violation
   │   └── multipart/form-data: violation_type, timestamp, screenshot file
   │
   └── Response JSON:
       ├── Update violationCounts + UI counters
       └── should_block == true?
           └── handleCameraBlock()
               ├── isCameraBlocked = true
               ├── expelled-flag input = '1'
               ├── Disable semua input & tombol
               ├── SweetAlert countdown 5 detik (tidak bisa ditutup)
               └── form.submit() → kuis dikumpulkan otomatis
```
