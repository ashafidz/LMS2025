# 🎓 Panduan Persiapan Sidang TA

## "Implementasi Fitur Keamanan Ujian Online Pada Platform E-Learning EduGames"

**Nama:** Akhmad Nabil Gibran
**NIM:** 2331730053
**Dosen Pembimbing:** Novita Dewi Susanti, S.Kom., M.Cs
**Penguji 1:** Agustono Heriadi, S.ST., M.Kom.
**Penguji 2:** Afta Ramadhan Zayn, S.Kom., M.Kom.
**Prodi:** D3 Manajemen Informatika — PSDKU Polinema di Kota Kediri

---

## 📋 Ringkasan Proyek

| Aspek | Detail |
|---|---|
| **Platform** | EduGames — e-learning milik **Wahana Media Digital** |
| **Masalah Utama** | Rendahnya integritas ujian online: tab switching, mencontek fisik (menoleh), kolusi antar siswa |
| **Solusi** | 3 fitur keamanan terintegrasi |
| **Tech Stack** | Laravel (PHP), MySQL, Bootstrap 5, TensorFlow.js, MediaPipe Face Mesh, JavaScript |
| **Pengujian** | Black Box Testing — 8 skenario, semua **Berhasil** |
| **Aktor** | Instructor & Student |

### 3 Fitur Keamanan yang Diimplementasikan:

1. **🔀 Pengacakan Soal** — Algoritma Fisher-Yates Shuffle
2. **📸 Deteksi Kamera** — MediaPipe Face Mesh (468 landmark wajah)
3. **🖥️ Deteksi Perpindahan Tab** — Page Visibility API

### Novelty (Kebaruan):

> Mengintegrasikan **3 fitur keamanan sekaligus** (Tab Detection + Fisher-Yates + Face Mesh) ke dalam **satu platform LMS utuh** dengan **Laporan Pelanggaran bertingkat** (per Percobaan, per Kuis, per Kursus). Penelitian sebelumnya hanya fokus pada 1–2 aspek saja.

---

## 🔥 BAGIAN 1: Pertanyaan Fundamental

### Q: Jelaskan secara singkat apa yang Anda kerjakan di TA ini.

> Saya mengimplementasikan tiga fitur keamanan pada platform e-learning EduGames untuk mengatasi rendahnya integritas ujian online. Fitur pertama adalah **Deteksi Perpindahan Tab** yang mencatat jika siswa membuka tab/aplikasi lain. Fitur kedua adalah **Pengacakan Soal** menggunakan algoritma **Fisher-Yates Shuffle** agar setiap siswa mendapat urutan soal berbeda sehingga meminimalisir kolusi. Fitur ketiga adalah **Deteksi Kamera** menggunakan model **MediaPipe Face Mesh** yang memantau orientasi wajah secara real-time — mendeteksi menoleh kiri/kanan, menunduk, mendongak, dan wajah tidak terdeteksi. Semua pelanggaran dicatat dalam **Laporan Pelanggaran bertingkat** (per Percobaan, per Kuis, per Kursus) untuk membantu Instructor mengevaluasi integritas ujian.

### Q: Apa rumusan masalah penelitian Anda?

1. Apakah Fisher-Yates Shuffle dapat menghasilkan urutan soal acak dan berbeda untuk setiap peserta?
2. Apakah Deteksi Kamera (MediaPipe Face Mesh) mampu mengidentifikasi pergerakan wajah (menoleh, menunduk, mendongak) dengan akurasi memadai?
3. Apakah sistem dapat mendeteksi dan merekam aktivitas perpindahan tab sebagai data pelanggaran?
4. Apakah Laporan Pelanggaran sesuai dengan data yang terekam dan berguna untuk evaluasi Instructor?

### Q: Apa tujuan penelitian Anda?

1. Mengimplementasikan Fisher-Yates Shuffle dan menguji distribusi soal acak dan unik
2. Mengimplementasikan deteksi kamera (MediaPipe Face Mesh) dan mengukur akurasinya
3. Mengimplementasikan deteksi perpindahan tab dan memverifikasi pencatatan pelanggaran
4. Menghasilkan Laporan Pelanggaran bertingkat dan memvalidasi kesesuaiannya

### Q: Apa batasan masalah?

1. Deteksi kamera **tidak mendeteksi multi-face** (hanya satu wajah)
2. **Tidak ada SPK** (Sistem Pendukung Keputusan) otomatis apakah siswa terbukti curang
3. Kuis dilaksanakan **kapan saja** selama jadwal (bukan real-time proctoring terjadwal)
4. Hanya membahas **penambahan fitur keamanan** pada sistem kuis yang sudah ada

---

## 🔥 BAGIAN 2: Algoritma Fisher-Yates Shuffle ⭐

### Q: Jelaskan cara kerja algoritma Fisher-Yates Shuffle!

Algoritma Fisher-Yates Shuffle bekerja secara **in-place** (tanpa array tambahan):

```
Input: Array soal [Q1, Q2, Q3, Q4, Q5]  (n = 5)

for i = n-1 downto 1:
    k = random(0, i)          // Pilih indeks acak dari 0 sampai i
    swap(array[k], array[i])   // Tukar elemen

Iterasi 1: i=4, k=random(0,4), misal k=2 → tukar Q3 ↔ Q5
Iterasi 2: i=3, k=random(0,3), misal k=0 → tukar Q1 ↔ Q4
Iterasi 3: i=2, k=random(0,2), misal k=1 → tukar Q2 ↔ Q3
Iterasi 4: i=1, k=random(0,1), misal k=0 → tukar Q1 ↔ Q2

Output: Urutan soal teracak yang UNIK per siswa
```

**Kompleksitas:**
- Waktu: **O(n)** — linear, hanya 1 pass
- Ruang: **O(1)** — in-place, tanpa array tambahan

### Q: Mengapa memilih Fisher-Yates dan bukan metode lain?

| Metode | Waktu | Distribusi | Keterangan |
|---|---|---|---|
| **Fisher-Yates Shuffle** ✅ | O(n) | Seragam (uniform) | Setiap permutasi punya peluang sama: 1/n! |
| `array_sort` + `rand()` | O(n log n) | Tidak merata | Lebih lambat, distribusi bias |
| Naive random swap | O(n) | **Bias** | n^n kemungkinan, tidak habis dibagi n! |

**Kunci jawaban:** Fisher-Yates menghasilkan **distribusi permutasi seragam** — setiap dari n! kemungkinan urutan punya probabilitas yang persis sama.

### Q: Bagaimana implementasinya di PHP/Laravel?

```php
// Dari laporan — mapping pseudocode ke PHP
for ($i = count($questions) - 1; $i > 0; $i--) {
    $k = random_int(0, $i);  // random_int() kriptografis aman
    // Swap
    $temp = $questions[$k];
    $questions[$k] = $questions[$i];
    $questions[$i] = $temp;
}

// Simpan urutan ke database
foreach ($shuffledQuestions as $order => $question) {
    QuizAttemptQuestionOrder::create([
        'attempt_id'     => $attempt->id,
        'question_id'    => $question->id,
        'shuffled_order' => $order + 1,
    ]);
}
```

### Q: Bagaimana jika siswa refresh halaman? Apakah soal teracak ulang?

**TIDAK.** Urutan soal disimpan di tabel `quiz_attempt_question_order` dengan kolom `shuffled_order`. Saat refresh, sistem **membaca urutan dari database**, bukan mengacak ulang. Ini menjamin:
- Konsistensi urutan soal selama sesi
- Instructor bisa review persis urutan yang dihadapi siswa

### Q: Apakah Fisher-Yates benar-benar menghasilkan distribusi uniform?

**Ya.** Secara matematis, algoritma ini menghasilkan tepat **n!** permutasi yang berbeda, masing-masing dengan probabilitas **1/n!**. Ini dibuktikan oleh **Knuth (1997)** dalam *The Art of Computer Programming*. Berbeda dengan naive swap yang menghasilkan **n^n** kemungkinan — yang tidak habis dibagi n!, sehingga beberapa permutasi lebih sering muncul.

---

## 🔥 BAGIAN 3: MediaPipe Face Mesh & Deteksi Kamera ⭐

### Q: Apa itu MediaPipe Face Mesh?

MediaPipe Face Mesh adalah solusi **computer vision** dari Google yang memperkirakan **468 koordinat landmark 3D wajah** secara real-time. Berjalan di browser menggunakan **TensorFlow.js** dengan akselerasi GPU via **WebGL**. Tidak memerlukan hardware khusus — cukup webcam standar.

### Q: Landmark mana yang digunakan dan mengapa?

Dari 468 titik, hanya **4 titik kunci** yang digunakan:

| Index | Nama Landmark | Fungsi |
|---|---|---|
| **1** | Nose tip (ujung hidung) | Referensi utama orientasi wajah |
| **33** | Left eye outer corner (sudut luar mata kiri) | Menghitung Yaw |
| **263** | Right eye outer corner (sudut luar mata kanan) | Menghitung Yaw |
| **10** | Forehead / Top of head (dahi) | Menghitung Pitch |

**Posisi default (saat wajah lurus ke depan, di tengah kamera):**

| Index | Landmark | Koordinat X | Koordinat Y | Keterangan |
|---|---|---|---|---|
| **1** | Hidung | **0.50** | **0.55** | Tengah horizontal, sedikit di bawah tengah vertikal |
| **33** | Mata kiri | **0.38** | — | Di sebelah kiri hidung |
| **263** | Mata kanan | **0.62** | — | Di sebelah kanan hidung |
| **10** | Dahi | — | **0.40** | Di atas hidung |

```
Visualisasi posisi default di layar kamera (wajah lurus):

  x: 0.0    0.38    0.50    0.62    1.0
      │       │       │       │       │
      │       │       │       │       │
y:0.0─┼───────┼───────┼───────┼───────┤
      │       │       │       │       │
 0.40─┤·······│·····[DAHI]····│·······│  ← dahi y=0.40
      │       │       │       │       │
      │·····[MATA👁]··│··[👁MATA]·····│  ← mata kiri x=0.38, mata kanan x=0.62
      │       │       │       │       │
 0.55─┤·······│·[HIDUNG👃]····│·······│  ← hidung x=0.50, y=0.55
      │       │       │       │       │
y:1.0─┴───────┴───────┴───────┴───────┘
```

> ⚠️ **Penting:** Nilai-nilai di atas adalah **nilai tipikal/contoh**, bukan konstanta tetap. Posisi sebenarnya bergantung pada:
> - Bentuk & ukuran wajah setiap orang berbeda
> - Jarak wajah ke kamera
> - Posisi duduk terhadap kamera
>
> **Yang penting bukan nilai absolutnya**, tapi **perubahan relatif** dari posisi normal → itulah yang diukur oleh Yaw dan Pitch.

### Q: Bagaimana cara menghitung arah pandangan?

> **Catatan:** Koordinat landmark dari MediaPipe Face Mesh sudah **dinormalisasi** ke rentang 0–1.
> - Sumbu X: `0.0` = tepi kiri frame → `1.0` = tepi kanan frame
> - Sumbu Y: `0.0` = tepi atas frame → `1.0` = tepi bawah frame

---

#### 📖 Penjelasan Istilah-Istilah (Wajib Paham Sebelum Lanjut!)

**🔸 Landmark**
Landmark itu **titik-titik penanda** di wajah. Bayangkan kamu menandai bagian-bagian wajah (ujung hidung, sudut mata, dahi) dengan **stiker kecil**. Setiap stiker punya **koordinat (x, y)** — yaitu posisinya di layar kamera. MediaPipe Face Mesh bisa mendeteksi **468 titik** seperti ini secara otomatis, tapi kita hanya butuh **4 titik** saja.

```
Ilustrasi 468 landmark di wajah:

         ·  ·  ·  ·  ·
       ·  ·  ·  ·  ·  ·  ·
      ·  ·  👁  ·  ·  👁  ·  ·     ← ratusan titik kecil di seluruh wajah
       ·  ·  ·  👃  ·  ·  ·
        ·  ·  ·  ·  ·  ·
          ·  · 👄 ·  ·

Yang kita pakai cuma 4:
  • Titik #1  = ujung hidung (👃)
  • Titik #33 = sudut luar mata kiri (👁 kiri)
  • Titik #263 = sudut luar mata kanan (👁 kanan)
  • Titik #10 = dahi atas
```

---

**🔸 Yaw (dibaca: "yoo")**
Yaw artinya **gerakan menoleh ke kiri atau ke kanan**. Bayangkan kamu menggelengkan kepala seperti bilang "tidak" — itu gerakan Yaw.

```
Analogi sederhana:

  Geleng KIRI ←  |  → Geleng KANAN
                 |
            Lurus ke depan

Dalam pesawat terbang:
  • Yaw = belok kiri/kanan (seperti setir mobil)
  • Pitch = naik/turun (mendongak/menunduk)
  • Roll = miring ke samping (tidak kita pakai)
```

Dalam konteks TA ini, **Yaw mengukur seberapa jauh hidung bergeser dari titik tengah kedua mata secara horizontal (kiri-kanan).**

---

**🔸 Pitch (dibaca: "pitch")**
Pitch artinya **gerakan menunduk atau mendongak**. Bayangkan kamu mengangguk — itu gerakan Pitch.

```
Analogi sederhana:

  Mendongak ↑  (melihat langit-langit)
              |
         Lurus ke depan
              |
  Menunduk ↓  (melihat meja)
```

Dalam konteks TA ini, **Pitch mengukur jarak vertikal antara hidung dan dahi.** Kalau menunduk, jarak makin besar. Kalau mendongak, jarak makin kecil.

---

**🔸 Midpoint (Titik Tengah)**
Midpoint itu **titik tengah** antara dua titik. Cara hitungnya: **jumlahkan kedua nilai, lalu bagi 2.**

```
Contoh sehari-hari:
  Kamu berdiri di posisi 3, temanmu di posisi 7.
  Titik tengah kalian = (3 + 7) / 2 = 5

Dalam TA ini:
  Mata kiri di posisi x = 0.38
  Mata kanan di posisi x = 0.62
  Midpoint = (0.38 + 0.62) / 2 = 0.50

  → Artinya titik tengah antara kedua mata ada di posisi 0.50
  → Kalau hidung juga di 0.50, berarti wajah LURUS
  → Kalau hidung BUKAN di 0.50, berarti wajah MENOLEH
```

---

**🔸 Threshold (Ambang Batas)**
Threshold itu **batas toleransi**. Ibarat speedometer mobil — kalau kecepatan masih di bawah 60 km/jam, aman. Tapi kalau sudah **melewati 60 km/jam**, maka dianggap kelebihan kecepatan.

```
Dalam TA ini:
  Threshold Yaw  = ±0.04  → kalau Yaw melebihi 0.04 atau kurang dari -0.04,
                             dianggap menoleh
  Threshold Pitch = ±0.045 → kalau Pitch berubah lebih dari 0.045 dari normal,
                             dianggap menunduk/mendongak

  Kenapa ada threshold?
  → Karena wajah manusia tidak pernah 100% diam. Selalu ada gerakan kecil.
  → Threshold memastikan gerakan KECIL (misal kedip, garuk hidung) TIDAK
    dianggap curang.
```

---

**🔸 Baseline (Nilai Dasar / Acuan)**
Baseline itu **nilai normal saat wajah menghadap lurus**. Ini jadi patokan untuk membandingkan apakah wajah sudah berubah posisi.

```
Analogi:
  Suhu tubuh normal = 36.5°C (ini BASELINE)
  Suhu 37.5°C = baseline + 1°C → mungkin demam ringan
  Suhu 39°C   = baseline + 2.5°C → pasti demam → PELANGGARAN!

Dalam TA ini:
  ┌─────────────────────────────────────────────────────────────────┐
  │ YAW (kiri/kanan):                                             │
  │   Baseline = 0.00 (hidung tepat di tengah kedua mata)         │
  │   Yaw +0.06  = baseline + 0.06 → melebihi threshold 0.04     │
  │              → MENOLEH KIRI!                                  │
  │   Yaw -0.065 = baseline - 0.065 → melebihi threshold 0.04    │
  │              → MENOLEH KANAN!                                 │
  ├─────────────────────────────────────────────────────────────────┤
  │ PITCH (menunduk/mendongak):                                   │
  │   Baseline = 0.15 (jarak normal hidung-dahi saat wajah lurus) │
  │   Pitch 0.24 = baseline + 0.09 → melebihi threshold 0.045    │
  │              → MENUNDUK!                                      │
  │   Pitch 0.08 = baseline - 0.07 → melebihi threshold 0.045    │
  │              → MENDONGAK!                                     │
  └─────────────────────────────────────────────────────────────────┘
```

---

**🔸 Koordinat Normalisasi (0–1)**
MediaPipe tidak memberikan posisi dalam pixel (misal "pixel ke-320"). Semua koordinat sudah **dinormalisasi** ke rentang **0 sampai 1**, tidak peduli resolusi kamera.

```
Bayangkan layar kamera sebagai kotak:

  (0,0) ─────────────────── (1,0)
    │                         │
    │    Wajah kamu di sini   │
    │         (0.5, 0.5)      │
    │     = tengah layar      │
    │                         │
  (0,1) ─────────────────── (1,1)

  x = 0.0 → paling KIRI
  x = 1.0 → paling KANAN
  x = 0.5 → tepat di TENGAH horizontal

  y = 0.0 → paling ATAS
  y = 1.0 → paling BAWAH
  y = 0.5 → tepat di TENGAH vertikal
```

---

**📝 Ringkasan Istilah dalam Satu Tabel:**

| Istilah | Artinya (Bahasa Sederhana) | Analogi |
|---|---|---|
| **Landmark** | Titik penanda di wajah | Stiker di ujung hidung, mata, dahi |
| **Yaw** | Gerakan menoleh kiri/kanan | Gelengkan kepala ("tidak-tidak") |
| **Pitch** | Gerakan menunduk/mendongak | Anggukkan kepala ("iya-iya") |
| **Midpoint** | Titik tengah antara 2 titik | Tengah-tengah antara mata kiri & kanan |
| **Threshold** | Batas toleransi | Batas kecepatan di jalan raya |
| **Baseline** | Nilai normal / acuan | Suhu tubuh normal 36.5°C |
| **Normalisasi 0–1** | Posisi relatif di layar (bukan pixel) | Persentase: 0.5 = 50% = tengah layar |

---

#### 🔹 YAW — Mendeteksi Menoleh Kiri/Kanan

**Rumus:**
```
midpoint_x = (left_eye_x + right_eye_x) / 2
Yaw = nose_x - midpoint_x
```

**Logika:** Jika wajah lurus, ujung hidung berada tepat di tengah antara kedua mata. Jika menoleh, hidung bergeser dari titik tengah mata.

| Nilai Yaw | Interpretasi |
|---|---|
| Yaw ≈ 0 | Wajah lurus ke depan |
| Yaw > +0.04 | **Menoleh ke kiri** (look_left) |
| Yaw < -0.04 | **Menoleh ke kanan** (look_right) |

---

**✅ Contoh 1 — Wajah LURUS ke depan:**

```
Landmark yang terdeteksi:
  Nose tip (index 1):   x = 0.50
  Left eye (index 33):  x = 0.38
  Right eye (index 263): x = 0.62

Perhitungan:
  midpoint_x = (0.38 + 0.62) / 2 = 0.50
  Yaw = 0.50 - 0.50 = 0.00

Hasil: Yaw = 0.00 → Tidak melewati threshold (±0.04)
Kesimpulan: ✅ NORMAL — Wajah menghadap lurus ke depan
```

---

**🔴 Contoh 2 — MENOLEH KE KIRI (look_left):**

> Saat menoleh ke kiri, hidung bergeser ke kiri (nilai x mengecil), tapi lebih lambat dibanding mata. Secara visual: hidung "tertinggal" ke kiri relatif dari titik tengah mata.

```
Landmark yang terdeteksi:
  Nose tip (index 1):   x = 0.43    ← hidung bergeser ke kiri
  Left eye (index 33):  x = 0.36    ← mata kiri bergeser ke kiri
  Right eye (index 263): x = 0.61   ← mata kanan bergeser ke kiri

Perhitungan:
  midpoint_x = (0.36 + 0.61) / 2 = 0.485
  Yaw = 0.43 - 0.485 = -0.055 ... ?

  ⚠️ TUNGGU — dari laporan, menoleh kiri menghasilkan Yaw POSITIF.
  Ini karena saat menoleh kiri, hidung bergeser relatif
  ke KANAN dari titik tengah mata (dalam perspektif kamera).

  Koreksi — yang sebenarnya terjadi saat menoleh kiri:
  Nose tip (index 1):   x = 0.53    ← hidung bergeser ke kanan relatif dari midpoint
  Left eye (index 33):  x = 0.36
  Right eye (index 263): x = 0.58

Perhitungan yang benar:
  midpoint_x = (0.36 + 0.58) / 2 = 0.47
  Yaw = 0.53 - 0.47 = +0.06

Hasil: Yaw = +0.06 → Melewati threshold +0.04
Kesimpulan: 🔴 PELANGGARAN — Menoleh ke kiri (look_left)
```

**Penjelasan visual:**
```
  Kamera melihat wajah dari depan:

  Wajah LURUS:              Wajah MENOLEH KIRI:
  ┌─────────────┐           ┌─────────────┐
  │  👁    👁   │           │ 👁      👁  │
  │     👃     │           │       👃    │  ← hidung bergeser ke kanan
  │     👄     │           │      👄    │     dari midpoint mata (di kamera)
  └─────────────┘           └─────────────┘
       Yaw ≈ 0                  Yaw = +0.06
```

---

**🔴 Contoh 3 — MENOLEH KE KANAN (look_right):**

> Saat menoleh ke kanan, hidung bergeser ke KIRI relatif dari titik tengah mata (dalam perspektif kamera).

```
Landmark yang terdeteksi:
  Nose tip (index 1):   x = 0.45    ← hidung bergeser ke kiri relatif dari midpoint
  Left eye (index 33):  x = 0.40
  Right eye (index 263): x = 0.63

Perhitungan:
  midpoint_x = (0.40 + 0.63) / 2 = 0.515
  Yaw = 0.45 - 0.515 = -0.065

Hasil: Yaw = -0.065 → Melewati threshold -0.04
Kesimpulan: 🔴 PELANGGARAN — Menoleh ke kanan (look_right)
```

**Penjelasan visual:**
```
  Wajah LURUS:              Wajah MENOLEH KANAN:
  ┌─────────────┐           ┌─────────────┐
  │  👁    👁   │           │  👁     👁  │
  │     👃     │           │  👃        │  ← hidung bergeser ke kiri
  │     👄     │           │  👄        │     dari midpoint mata (di kamera)
  └─────────────┘           └─────────────┘
       Yaw ≈ 0                  Yaw = -0.065
```

---

**📝 Ringkasan Yaw:**

| Posisi Wajah | Nose x | Left Eye x | Right Eye x | Midpoint x | Yaw | Hasil |
|---|---|---|---|---|---|---|
| Lurus | 0.50 | 0.38 | 0.62 | 0.50 | **0.00** | ✅ Normal |
| Menoleh Kiri | 0.53 | 0.36 | 0.58 | 0.47 | **+0.06** | 🔴 look_left |
| Menoleh Kanan | 0.45 | 0.40 | 0.63 | 0.515 | **-0.065** | 🔴 look_right |

---

#### 🔹 PITCH — Mendeteksi Menunduk/Mendongak

**Rumus:**
```
Pitch = nose_y - forehead_y
```

**Logika:** Saat wajah lurus, jarak vertikal antara dahi dan hidung relatif tetap. Saat menunduk, hidung bergerak menjauh (ke bawah) dari dahi. Saat mendongak, hidung mendekat ke dahi.

| Nilai Pitch | Interpretasi |
|---|---|
| Pitch ≈ 0.15 (baseline) | Wajah normal (lurus) |
| Pitch > baseline + 0.045 | **Menunduk** (look_down) |
| Pitch < baseline - 0.045 | **Mendongak** (look_up) |

> **Catatan:** Sumbu Y di MediaPipe: nilai **bertambah ke bawah**. Jadi `y = 0.3` lebih bawah dari `y = 0.2`.

---

**✅ Contoh 1 — Wajah LURUS ke depan:**

```
Landmark yang terdeteksi:
  Nose tip (index 1):    y = 0.55
  Forehead (index 10):   y = 0.40

Perhitungan:
  Pitch = 0.55 - 0.40 = 0.15

Hasil: Pitch = 0.15 → Ini menjadi baseline (nilai normal)
Kesimpulan: ✅ NORMAL — Wajah menghadap lurus ke depan
```

---

**🔴 Contoh 2 — MENUNDUK (look_down):**

> Saat menunduk, dahi bergerak ke atas (y mengecil) dan hidung bergerak ke bawah (y membesar) relatif di kamera. Jarak vertikal antara hidung dan dahi membesar → Pitch meningkat.

```
Landmark yang terdeteksi:
  Nose tip (index 1):    y = 0.62    ← hidung turun ke bawah
  Forehead (index 10):   y = 0.38    ← dahi naik ke atas

Perhitungan:
  Pitch = 0.62 - 0.38 = 0.24
  Selisih dari baseline: 0.24 - 0.15 = +0.09

Hasil: Selisih = +0.09 → Melewati threshold +0.045
Kesimpulan: 🔴 PELANGGARAN — Menunduk (look_down)
```

**Penjelasan visual:**
```
  Wajah LURUS:              Wajah MENUNDUK:
  ┌─────────────┐           ┌─────────────┐
  │             │           │  (dahi)      │  ← dahi y=0.38 (naik)
  │  (dahi)     │ y=0.40    │             │
  │             │           │             │
  │  (hidung)   │ y=0.55    │             │
  │             │           │  (hidung)   │  ← hidung y=0.62 (turun)
  └─────────────┘           └─────────────┘
    Pitch = 0.15              Pitch = 0.24
    (baseline)                (+0.09 dari baseline)
```

---

**🔴 Contoh 3 — MENDONGAK / Melihat ke Atas (look_up):**

> Saat mendongak, dahi bergerak ke bawah (y membesar) dan hidung juga mendekat ke dahi. Jarak vertikal mengecil → Pitch menurun.

```
Landmark yang terdeteksi:
  Nose tip (index 1):    y = 0.50    ← hidung naik ke atas
  Forehead (index 10):   y = 0.42    ← dahi turun ke bawah

Perhitungan:
  Pitch = 0.50 - 0.42 = 0.08
  Selisih dari baseline: 0.08 - 0.15 = -0.07

Hasil: Selisih = -0.07 → Melewati threshold -0.045
Kesimpulan: 🔴 PELANGGARAN — Mendongak (look_up)
```

**Penjelasan visual:**
```
  Wajah LURUS:              Wajah MENDONGAK:
  ┌─────────────┐           ┌─────────────┐
  │             │           │             │
  │  (dahi)     │ y=0.40    │  (dahi)     │  ← dahi y=0.42
  │             │           │  (hidung)   │  ← hidung y=0.50 (mendekat ke dahi)
  │  (hidung)   │ y=0.55    │             │
  │             │           │             │
  └─────────────┘           └─────────────┘
    Pitch = 0.15              Pitch = 0.08
    (baseline)                (-0.07 dari baseline)
```

---

**📝 Ringkasan Pitch:**

| Posisi Wajah | Nose y | Forehead y | Pitch | Selisih dari Baseline | Hasil |
|---|---|---|---|---|---|
| Lurus | 0.55 | 0.40 | **0.15** | 0 (baseline) | ✅ Normal |
| Menunduk | 0.62 | 0.38 | **0.24** | +0.09 | 🔴 look_down |
| Mendongak | 0.50 | 0.42 | **0.08** | -0.07 | 🔴 look_up |

---

**📝 Ringkasan Lengkap Semua Arah:**

| Arah Pandangan | Rumus yang Berubah | Tanda Nilai | Threshold |
|---|---|---|---|
| **Menoleh Kiri** | Yaw = nose_x - midpoint_x | Yaw **positif** (> +0.04) | +0.04 |
| **Menoleh Kanan** | Yaw = nose_x - midpoint_x | Yaw **negatif** (< -0.04) | -0.04 |
| **Menunduk** | Pitch = nose_y - forehead_y | Pitch **membesar** dari baseline | +0.045 dari baseline |
| **Mendongak** | Pitch = nose_y - forehead_y | Pitch **mengecil** dari baseline | -0.045 dari baseline |

### Q: Jelaskan mekanisme "sustained violation"!

Pelanggaran **tidak langsung dicatat** saat pertama terdeteksi. Ada mekanisme **state machine**:

```
┌──────┐    pelanggaran    ┌──────────┐    durasi ≥ threshold    ┌───────────┐
│ IDLE │ ───terdeteksi───► │ TRACKING │ ─────────────────────►  │ CONFIRMED │
└──────┘                   └──────────┘                         └───────────┘
   ▲                           │                                      │
   │         wajah normal      │                                      │
   └───────────────────────────┘             log dikirim ke server ────┘
```

**2 Parameter kunci:**
1. **Detection Interval** (default 5 detik): Jeda minimum antar pemeriksaan frame
2. **Violation Duration** (default 3 detik): Pelanggaran harus **bertahan** selama durasi ini sebelum dianggap valid

**Tujuan:** Mencegah **false positive** akibat gerakan refleks sesaat (misal bersin, menggaruk).

### Q: Jelaskan proses kalibrasi kamera!

Sebelum deteksi aktif, ada fase **warm-up** dengan **2 syarat** yang harus terpenuhi:

| Syarat | Nilai |
|---|---|
| Timer minimum | **10 detik** harus terlewati |
| Frame wajah terdeteksi | Minimal **5 frame** wajah berhasil dideteksi |

- Jika wajah blur/tidak jelas, frame tersebut **tidak dihitung**
- Overlay kalibrasi muncul selama proses
- Setelah kedua syarat terpenuhi → overlay menghilang dengan **fade-out**, status berubah jadi "Aktif" (badge hijau)

### Q: Contoh timeline deteksi dari laporan!

**Kasus Student Andi** (threshold=5, interval=5s, duration=3s):

| Waktu | Kejadian | Pelanggaran ke- |
|---|---|---|
| 31s | Menoleh kiri (bertahan ≥3s) | **#1** look_left |
| 46s | Wajah tidak terdeteksi (bertahan ≥3s) | **#2** face_not_detected |
| 61s | Menoleh kanan (bertahan ≥3s) | **#3** look_right |
| 71s | Menunduk (bertahan ≥3s) | **#4** look_down |
| 76s | Mendongak — tapi `look_up` **NONAKTIF** | ❌ Tidak dihitung |
| 86s | Menoleh kiri lagi (bertahan ≥3s) | **#5** = **THRESHOLD! DIBLOKIR** |

**Respons server saat threshold tercapai:**
```json
{
    "success": true,
    "violation_count": 5,
    "violation_breakdown": {
        "face_not_detected": 1,
        "look_left": 2,
        "look_right": 1,
        "look_down": 1,
        "look_up": 0
    },
    "should_block": true,
    "message": "Kuis diblokir karena terlalu banyak pelanggaran kamera!"
}
```

### Q: Apa yang terjadi saat siswa diblokir/expelled?

5 aksi otomatis:
1. Semua **input soal di-disable** (tidak bisa diubah)
2. **Tombol navigasi** dinonaktifkan
3. **SweetAlert countdown 5 detik** muncul
4. Flag `expelled_by_violation` diset ke **1**
5. Jawaban **otomatis di-submit** ke server

### Q: Apa parameter yang bisa dikonfigurasi Instructor?

| Parameter | Range | Default | Penjelasan |
|---|---|---|---|
| Aktifkan Deteksi Kamera | On/Off | Off | Master switch |
| Batas Toleransi Pelanggaran | 1–20 | 3 | Jumlah pelanggaran sebelum diblokir |
| Interval Deteksi | 3–30 detik | 5 | Frekuensi pemeriksaan wajah |
| Durasi Pelanggaran | 0–10 detik | 3 | Berapa lama pelanggaran harus bertahan (0 = langsung) |
| Wajah Tidak Terdeteksi | On/Off | On | Toggle per jenis pelanggaran |
| Menoleh Kiri | On/Off | On | Toggle per jenis pelanggaran |
| Menoleh Kanan | On/Off | On | Toggle per jenis pelanggaran |
| Melihat Ke Atas | On/Off | On | Toggle per jenis pelanggaran |
| Menunduk | On/Off | On | Toggle per jenis pelanggaran |

---

## 🔥 BAGIAN 4: Deteksi Perpindahan Tab

### Q: Bagaimana cara kerja deteksi perpindahan tab?

Menggunakan **Page Visibility API** bawaan JavaScript:

```javascript
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        // Tab tidak aktif → catat pelanggaran ke server
        logViolation('tab_switch');
    }
});
```

Setiap perpindahan dicatat ke tabel `monitoring_logs` dengan `violation_type = 'tab_switch'`.

### Q: Apa kelemahan deteksi tab?

- ❌ Tidak bisa mendeteksi **dual monitor** (siswa melihat layar kedua tanpa berpindah tab)
- ❌ Tidak mendeteksi penggunaan **device kedua** (HP, tablet)
- ❌ Tergantung pada browser — beberapa browser mungkin berbeda perilaku `visibilitychange`

---

## 🔥 BAGIAN 5: Arsitektur & Database

### Q: Jelaskan arsitektur sistem!

```
┌─────────────┐     HTTP/AJAX      ┌──────────────┐      Query      ┌─────────┐
│   Browser   │ ◄───────────────►  │    Laravel    │ ◄─────────────► │  MySQL  │
│             │                    │   (Backend)   │                 │   DB    │
│ TensorFlow  │                    │              │                 │         │
│ MediaPipe   │                    │  Controller  │                 │ Tables  │
│ JavaScript  │                    │  Model       │                 │         │
└─────────────┘                    └──────────────┘                 └─────────┘
   Client-side                        Server-side                    Database
  (Deteksi wajah)                   (Business logic)              (Penyimpanan)
```

- **Client-side:** TensorFlow.js + MediaPipe Face Mesh (deteksi wajah), Page Visibility API (deteksi tab)
- **Server-side:** Laravel (PHP) — MVC architecture, business logic, API endpoints
- **Database:** MySQL — penyimpanan data kuis, attempt, log pelanggaran

### Q: Tabel database apa saja yang ditambahkan?

| Tabel | Fungsi | Kolom Kunci |
|---|---|---|
| `quiz_attempt_question_order` | Urutan soal teracak per attempt | `attempt_id`, `question_id`, `shuffled_order` |
| `quiz_attempt_integrity_summary` | Rangkuman pelanggaran per attempt | `total_tab_switches`, `total_face_violations`, `look_left_count`, `look_right_count`, `face_not_detected_count` |
| `monitoring_logs` | Log detail setiap pelanggaran | `violation_type`, `violation_timestamp`, `screenshot_path`, `duration_seconds`, `additional_data` (JSON) |

### Q: Mengapa deteksi wajah dilakukan di client-side (browser) dan bukan di server?

| Aspek | Client-side ✅ | Server-side |
|---|---|---|
| **Beban server** | Tidak membebani server | Berat — harus proses video |
| **Latency** | Real-time, tanpa delay jaringan | Ada delay upload + proses |
| **Bandwidth** | Hemat — hanya kirim log + screenshot | Boros — harus streaming video |
| **Privasi** | Video tidak pernah diupload | Video tersimpan di server |
| **Skalabilitas** | Proses di device masing-masing | Server jadi bottleneck |

---

## 🔥 BAGIAN 5B: Laporan Pelanggaran Bertingkat (Sisi Instructor) ⭐

### Q: Apa itu Laporan Pelanggaran Bertingkat?

Laporan Pelanggaran Bertingkat adalah fitur **dashboard analitik** yang memungkinkan Instructor meninjau data pelanggaran dari **3 level berbeda** — mulai dari gambaran umum seluruh kursus hingga detail spesifik per percobaan siswa.

```
Alur Navigasi Instructor (dari luas → spesifik):

┌─────────────────────────────────────────────────────┐
│  LEVEL 1: Laporan per KURSUS                        │
│  "Bagaimana integritas ujian di seluruh kursus ini?" │
│  ┌───────────────────────────────────────────────┐   │
│  │  LEVEL 2: Laporan per KUIS                    │   │
│  │  "Kuis mana yang paling banyak pelanggaran?"  │   │
│  │  ┌─────────────────────────────────────────┐  │   │
│  │  │  LEVEL 3: Laporan per PERCOBAAN         │  │   │
│  │  │  "Apa bukti kecurangan siswa ini?"      │  │   │
│  │  │  → Screenshot, timeline, revisi skor    │  │   │
│  │  └─────────────────────────────────────────┘  │   │
│  └───────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────┘
```

---

### 📊 LEVEL 1 — Laporan Pelanggaran per KURSUS

**Tujuan:** Memberikan gambaran besar integritas ujian di **seluruh kuis** dalam satu kursus.

**Data yang ditampilkan:**

| Panel Metrik | Keterangan |
|---|---|
| Total Kuis | Jumlah kuis yang ada di kursus ini |
| Total Student | Jumlah siswa yang berpartisipasi |
| Total Percobaan (Attempts) | Total sesi pengerjaan dari seluruh kuis |
| Total Perpindahan Tab | Akumulasi tab switch dari semua kuis |
| Total Pelanggaran Kamera | Akumulasi face violation dari semua kuis |
| Total Dikeluarkan dari Kuis | Siswa yang di-expelled karena melebihi threshold |

**Tabel "Pelanggaran per Kuis":**
- Menampilkan setiap kuis dalam kursus beserta:
  - Label fitur keamanan yang aktif (🟢 Tab Detection, 🟢 Camera Detection, 🟢 Shuffle)
  - Letak modul
  - Jumlah pelanggaran per kuis
- Tombol aksi: 👁 **Lihat Detail** → navigasi ke Level 2

**Cuplikan kode (dari laporan):**
```php
// app/Http/Controllers/Instructor/InstructorQuizController.php

public function courseMonitoringOverview(Course $course)
{
    // Ambil semua kuis di kursus ini beserta attempt & integrity summary
    $quizzes = Quiz::whereHas('lesson.module', function ($query) use ($course) {
        $query->where('course_id', $course->id);
    })->with(['attempts.integritySummary'])->get();

    foreach ($quizzes as $quiz) {
        // Hitung total pelanggaran tab dan kamera per kuis
        $tabViolations = $quiz->attempts->sum(
            fn($a) => $a->integritySummary?->total_tab_switches ?? 0
        );
        $cameraViolations = $quiz->attempts->sum(
            fn($a) => $a->integritySummary?->total_face_violations ?? 0
        );

        $quizData[] = [
            'quiz' => $quiz,
            'total_violations' => $tabViolations + $cameraViolations,
            'unique_students' => $quiz->attempts->pluck('student_id')
                                     ->unique()->count()
        ];
    }
}
```

---

### 📊 LEVEL 2 — Laporan Pelanggaran per KUIS

**Tujuan:** Meninjau data pelanggaran dari **seluruh siswa** pada satu kuis spesifik.

**Data yang ditampilkan:**

| Panel Metrik | Keterangan |
|---|---|
| Total Percobaan | Berapa kali kuis ini dikerjakan (semua siswa) |
| Total Perpindahan Tab | Akumulasi tab switch pada kuis ini |
| Total Pelanggaran Kamera | Akumulasi face violation pada kuis ini |
| Total Dikeluarkan | Siswa yang di-expelled pada kuis ini |

**Tabel "Daftar Student & Sesi Terakhir":**
- Menampilkan setiap siswa yang mengerjakan kuis:
  - Waktu pengerjaan terakhir
  - Skor yang diperoleh
  - Jumlah pelanggaran tab & kamera
  - Status: apakah dikeluarkan (expelled) atau tidak
- Tombol aksi:
  - 📜 **History** → melihat riwayat semua percobaan siswa
  - 🔍 **Latest** → langsung ke detail pelanggaran percobaan terakhir (Level 3)

**Cuplikan kode (dari laporan):**
```php
public function monitoringReview(Quiz $quiz)
{
    $attempts = QuizAttempt::where('quiz_id', $quiz->id)
        ->with(['student', 'integritySummary'])
        ->orderBy('created_at', 'desc')
        ->get();

    // Kelompokkan berdasarkan siswa
    $attemptsByStudent = $attempts->groupBy('student_id')
        ->map(function ($studentAttempts) {
            return [
                'student'        => $studentAttempts->first()->student,
                'latest_attempt' => $studentAttempts->sortByDesc('created_at')->first(),
                'all_attempts'   => $studentAttempts->sortByDesc('created_at')->values()
            ];
        });

    return view('instructor.quizzes.monitoring-review',
        compact('quiz', 'attemptsByStudent', 'stats'));
}
```

> **Kenapa dikelompokkan per siswa?** Karena satu siswa bisa mencoba kuis berkali-kali (jika `max_attempts` > 1). Dengan groupBy, Instructor melihat **satu baris per siswa** dengan data percobaan terakhir, bukan baris per attempt.

---

### 📊 LEVEL 3 — Laporan Pelanggaran per PERCOBAAN (Paling Detail)

**Tujuan:** Menyajikan **data forensik lengkap** untuk satu sesi pengerjaan kuis oleh satu siswa.

**Data yang ditampilkan:**

**1. Panel Informasi Peserta:**
| Data | Contoh |
|---|---|
| Nama Siswa | Student 1 |
| Nama Kuis | Quiz Bab 1 |
| Waktu Mulai | 2026-05-20 10:00:00 |
| Waktu Selesai | 2026-05-20 10:15:00 |
| Skor | 70/100 |
| Status | Expelled (dikeluarkan) |

**2. Panel Rincian Pelanggaran Kamera:**
| Jenis Pelanggaran | Jumlah |
|---|---|
| Wajah Tidak Terdeteksi | 1 kali |
| Menoleh Kiri | 2 kali |
| Menoleh Kanan | 1 kali |
| Menunduk | 1 kali |
| Mendongak | 0 kali |
| **Total Pelanggaran Kamera** | **5 kali** |
| Total Perpindahan Tab | 3 kali |

> Data ini diambil dari tabel `quiz_attempt_integrity_summary`

**3. Timeline Pelanggaran (Kronologis):**

```
⏱️ Timeline Pelanggaran Student 1:

10:00:31  🔴 look_left       — Menoleh ke kiri selama 5 detik
10:00:46  🔴 face_not_detected — Wajah tidak terdeteksi selama 5 detik
10:01:01  🔴 look_right      — Menoleh ke kanan selama 5 detik
10:01:11  🔴 look_down       — Menunduk selama 5 detik
10:01:26  🔴 look_left       — Menoleh ke kiri (THRESHOLD! DIBLOKIR)
10:05:00  🟡 tab_switch      — Berpindah tab
10:07:30  🟡 tab_switch      — Berpindah tab
10:09:15  🟡 tab_switch      — Berpindah tab
```

> Data ini diambil dari tabel `monitoring_logs`

**4. Galeri Bukti Foto/Screenshot:**
- Menampilkan **tangkapan layar otomatis dari webcam** saat pelanggaran terjadi
- Setiap screenshot disimpan di `screenshots/violations/{attempt_id}/`
- Format nama file: `{violation_type}_{timestamp}.jpg`
- Contoh: `look_left_1716188431.jpg`

**5. Form Revisi Skor:**
- Instructor bisa **mengubah skor** siswa berdasarkan bukti kecurangan
- Input: Skor baru + catatan alasan revisi
- Data tersimpan di tabel `quiz_attempts`:
  - `revised_score` — skor setelah direvisi
  - `revised_by` — ID Instructor yang merevisi
  - `revised_at` — waktu revisi
  - `revision_note` — catatan alasan (misal: "Diturunkan karena 5x pelanggaran kamera")

---

### Q: Bagaimana alur Instructor meninjau dan menindaklanjuti kecurangan?

```
Alur lengkap dari deteksi → tindakan:

1. Siswa mengerjakan kuis
   ↓
2. Sistem mendeteksi pelanggaran (tab/kamera)
   ↓
3. Log pelanggaran + screenshot tersimpan otomatis
   ↓
4. Instructor membuka Laporan per Kursus (Level 1)
   → Melihat kuis mana yang paling banyak pelanggaran
   ↓
5. Instructor klik detail kuis tertentu (Level 2)
   → Melihat siswa mana yang paling banyak melanggar
   ↓
6. Instructor klik detail percobaan siswa (Level 3)
   → Melihat bukti foto, timeline kronologis, rincian jenis pelanggaran
   ↓
7. Instructor menilai apakah bukti valid
   → Jika valid: melakukan Revisi Skor + catatan alasan
   → Jika false positive: skor tetap, tidak ada tindakan
```

---

### Q: Mengapa laporan dibuat bertingkat (3 level)?

> **Jawaban untuk sidang:**
> Laporan dibuat bertingkat karena masing-masing level melayani **kebutuhan analisis yang berbeda**:
>
> - **Level 1 (per Kursus):** Untuk **monitoring tingkat tinggi** — Instructor bisa langsung tahu apakah ada masalah integritas di kursusnya tanpa harus membuka satu per satu.
> - **Level 2 (per Kuis):** Untuk **identifikasi kuis bermasalah** — Instructor tahu kuis mana yang paling banyak pelanggaran dan siswa mana yang perlu perhatian.
> - **Level 3 (per Percobaan):** Untuk **investigasi dan pengambilan keputusan** — Instructor melihat bukti konkret (screenshot, timeline) sebelum memutuskan apakah perlu revisi skor.
>
> Pendekatan ini mengikuti prinsip **drill-down analysis** — dari gambaran besar menuju detail spesifik, sehingga Instructor tidak kewalahan dengan terlalu banyak data sekaligus.

---

### Q: Tabel database mana yang mendukung fitur laporan?

| Tabel | Level Laporan | Data yang Disediakan |
|---|---|---|
| `quiz_attempt_integrity_summary` | Level 2 & 3 | Total pelanggaran, breakdown per jenis pelanggaran |
| `monitoring_logs` | Level 3 | Detail per pelanggaran: jenis, waktu, screenshot path, data JSON |
| `quiz_attempts` | Semua level | Skor, status, waktu, expelled flag, revisi skor |

**Relasi antar tabel:**
```
quiz_attempts (1) ──── (1) quiz_attempt_integrity_summary
      │                        (rangkuman per attempt)
      │
      └──── (banyak) monitoring_logs
                     (log detail setiap pelanggaran)
```

---

### Q: Apa bedanya fitur Revisi Skor dengan langsung mengubah nilai di database?

> Fitur Revisi Skor **bukan** sekadar UPDATE nilai. Sistem menyimpan:
> - `score` (skor asli) — **tidak diubah**, tetap sebagai arsip
> - `revised_score` — skor baru hasil revisi
> - `revised_by` — **siapa** yang merevisi (akuntabilitas)
> - `revised_at` — **kapan** direvisi
> - `revision_note` — **alasan** revisi
>
> Ini memastikan ada **audit trail** (jejak audit) lengkap. Skor asli tetap tersimpan sebagai bukti, dan ada transparansi siapa yang mengubah, kapan, dan mengapa.

---

## 🔥 BAGIAN 6: Pengujian

### Q: Metode pengujian apa yang digunakan?

**Black Box Testing** — fokus pada validasi **fungsional** (input → output yang diharapkan), tanpa melihat internal code.

### 8 Skenario Pengujian:

| No | Skenario | Prosedur | Hasil |
|---|---|---|---|
| 1 | Pengaturan Opsi Keamanan | Instructor mengaktifkan toggle fitur keamanan | ✅ Berhasil |
| 2 | Validasi Fisher-Yates | 2 student mulai kuis bersamaan, cek urutan soal | ✅ Berhasil (urutan berbeda) |
| 3 | Deteksi Perpindahan Tab | Student membuka tab baru saat kuis | ✅ Berhasil (terdeteksi + dicatat) |
| 4 | Deteksi Kamera | Student menoleh, menunduk, mendongak | ✅ Berhasil (terdeteksi + screenshot) |
| 5 | Laporan per Kursus | Instructor buka dashboard rekapitulasi | ✅ Berhasil |
| 6 | Laporan per Kuis | Instructor buka laporan per kuis | ✅ Berhasil |
| 7 | Laporan per Percobaan | Instructor buka detail percobaan (galeri bukti, timeline) | ✅ Berhasil |
| 8 | Revisi Skor | Instructor ubah nilai + catatan alasan | ✅ Berhasil |

### Q: Mengapa Black Box, bukan White Box?

> Black Box dipilih karena fokus penelitian adalah **validasi fungsional** — apakah fitur berjalan sesuai requirement. White Box lebih cocok untuk menguji internal logic/code coverage. Keduanya bersifat **komplementer** — Black Box bukan pengganti White Box, melainkan pendekatan yang mendeteksi **jenis kesalahan berbeda** (fungsi hilang, kesalahan antarmuka, masalah akses database, dll).

---

## 🔥 BAGIAN 7: Penelitian Terdahulu & Perbandingan

### 5 Penelitian Relevan:

| No | Judul/Penulis | Fitur Utama | Perbedaan dengan TA ini |
|---|---|---|---|
| 1 | LMS + Laravel (Anam, 2022) | Manajemen pengguna & materi | Tidak ada fitur keamanan ujian |
| 2 | E-Learning + Ujian Online (Melani, 2023) | Video + kuis + hasil instan | Tidak ada anti-cheat |
| 3 | LMS + MediaPipe Face Mesh (Bimantoro, 2024) | Deteksi wajah + tab switching | Tidak ada pengacakan soal, beda platform |
| 4 | Proctoring + WebRTC (Agustinus, 2024) | Active tab detection + live proctoring | Berbasis server (berat), tidak ada Fisher-Yates |
| 5 | Fisher-Yates + Ujian Online (Lubis, 2025) | Pengacakan soal | Tidak ada deteksi kamera |

### Kebaruan TA ini vs penelitian sebelumnya:

> **Integrasi 3 fitur sekaligus** dalam satu platform LMS + **laporan bertingkat 3 level**. Belum ada penelitian sebelumnya yang menggabungkan Fisher-Yates + MediaPipe Face Mesh + Tab Detection dalam satu sistem utuh.

---

## 🧠 BAGIAN 8: Pertanyaan Jebakan & Cara Menjawab

### ❓ "Kenapa tidak pakai proctoring berbasis server (WebRTC)?"

> Pendekatan client-side dipilih karena: (1) Tidak membebani bandwidth server, (2) Tidak memerlukan infrastruktur streaming, (3) Privasi lebih baik karena video tidak diupload penuh. Trade-off-nya adalah deteksi terbatas pada kemampuan browser/device client.

### ❓ "Bagaimana jika siswa menutup/menutupi kamera?"

> Sistem mendeteksi `face_not_detected`. Jika wajah tidak terdeteksi selama `violation_duration_seconds`, dicatat sebagai pelanggaran. Jika akumulasi mencapai threshold, siswa otomatis dikeluarkan.

### ❓ "Bagaimana menangani false positive pada deteksi wajah?"

> Tiga mekanisme pencegahan: (1) `detection_interval` agar tidak setiap frame diperiksa, (2) `violation_duration` agar gerakan sesaat (bersin, menggaruk) tidak dihitung, (3) Instructor bisa **review screenshot bukti** dan melakukan **revisi skor** jika ternyata false positive.

### ❓ "Kenapa Yaw/Pitch dihitung dari landmark, bukan rotasi 3D langsung?"

> Pendekatan koordinat landmark lebih sederhana dan ringan secara komputasi di browser. Rumus berbasis koordinat normalisasi (0-1) dari Face Mesh cukup akurat untuk menentukan arah pandangan tanpa transformasi rotasi 3D yang kompleks.

### ❓ "Bagaimana jika koneksi internet terputus saat kuis?"

> Urutan soal sudah tersimpan di `quiz_attempt_question_order`. Saat reconnect dan refresh, urutan tetap konsisten. Deteksi kamera berjalan **client-side** sehingga tidak bergantung pada koneksi real-time — log pelanggaran akan dikirim saat koneksi kembali.

### ❓ "Apa kekurangan sistem Anda? Kalau disuruh bikin ulang, apa yang diubah?"

> Kekurangan: (1) Belum bisa deteksi multi-face, (2) Tidak ada audio detection, (3) Bisa lag di device spesifikasi rendah. Jika bikin ulang, saya akan menambahkan **optimasi resource** agar ringan di perangkat menengah ke bawah, dan **notifikasi otomatis via email** agar Instructor langsung tahu saat ada pelanggaran berisiko tinggi.

### ❓ "Mengapa tidak menggunakan metode pengujian lain seperti UAT (User Acceptance Test)?"

> Fokus penelitian ini adalah validasi fungsional teknis, sehingga Black Box Testing sudah sesuai. UAT memerlukan pengujian oleh end-user dalam jumlah signifikan yang berada di luar cakupan batasan masalah. Namun, UAT bisa menjadi saran pengembangan selanjutnya.

### ❓ "Seberapa akurat deteksi Face Mesh? Ada pengukuran kuantitatifnya?"

> Dalam penelitian ini, akurasi diuji secara fungsional — apakah sistem berhasil mendeteksi gerakan yang dilakukan. Dari skenario Black Box, semua gerakan (menoleh kiri/kanan, menunduk, mendongak, wajah hilang) berhasil terdeteksi. Untuk pengukuran akurasi kuantitatif (precision, recall, F1-score), itu bisa menjadi topik pengembangan yang lebih mendalam.

---

## 📊 Angka-Angka Penting — HAFALKAN!

| Item | Nilai |
|---|---|
| Jumlah landmark Face Mesh | **468** titik 3D |
| Titik landmark yang digunakan | **4** titik (index 1, 33, 263, 10) |
| Default threshold pelanggaran kamera | **3** (range: 1-20) |
| Default detection interval | **5** detik (range: 3-30) |
| Default violation duration | **3** detik (range: 0-10) |
| Syarat kalibrasi | **10** detik + **5** frame wajah |
| Countdown saat expelled | **5** detik |
| Kompleksitas Fisher-Yates | **O(n)** waktu, **O(1)** ruang |
| Jumlah skenario Black Box | **8** skenario |
| Hasil pengujian | **8/8 Berhasil** (100%) |
| Jumlah aktor | **2** (Instructor, Student) |
| Jumlah use case | **6** use case |
| Jumlah penelitian relevan | **5** penelitian |
| Jumlah tabel database baru | **3** tabel utama (question_order, integrity_summary, monitoring_logs) |
| Level laporan pelanggaran | **3** level (Percobaan, Kuis, Kursus) |

---

## 🎤 Tips Presentasi Sidang

### Struktur Presentasi:

1. **Buka dengan masalah** (30 detik)
   > "Platform EduGames menghadapi permasalahan rendahnya integritas ujian online. Siswa bisa dengan mudah membuka tab lain, bertanya ke teman, atau mencontek secara fisik..."

2. **Jelaskan solusi** (2-3 menit)
   > "Untuk mengatasi masalah tersebut, saya mengimplementasikan 3 fitur keamanan..."

3. **Demo sistem** (3-5 menit) — Jika diminta
   - Tunjukkan Instructor mengaktifkan fitur keamanan
   - Tunjukkan 2 siswa mendapat urutan soal berbeda
   - Tunjukkan deteksi kamera bekerja (menoleh → terdeteksi → screenshot)
   - Tunjukkan laporan pelanggaran

4. **Kesimpulan & Saran** (1-2 menit)

### Do's:
- ✅ Jelaskan **konsep dulu**, detail teknis kalau ditanya
- ✅ **Akui batasan** dengan elegan: *"Sistem ini belum menangani multi-face, itu bisa jadi pengembangan selanjutnya"*
- ✅ Jawab dengan **percaya diri** — kamu yang paling tahu tentang proyek ini
- ✅ Gunakan **istilah dari laporan** (sustained violation, threshold, landmark, yaw, pitch)

### Don'ts:
- ❌ Jangan diam terlalu lama — kalau bingung, mulai dengan *"Berdasarkan pemahaman saya..."*
- ❌ Jangan mengarang jawaban — lebih baik bilang *"Itu di luar cakupan batasan masalah, tapi bisa jadi saran pengembangan"*
- ❌ Jangan baca slide kata per kata
- ❌ Jangan panik kalau pertanyaan sulit — tarik napas, berpikir sebentar

---

## 💡 Template Jawaban Darurat

Jika ditanya sesuatu yang **tidak kamu tahu**:

> *"Terima kasih atas pertanyaannya, Pak/Bu. Berdasarkan pemahaman saya, [jawab sebaik mungkin]. Namun, hal tersebut berada di luar cakupan batasan masalah penelitian ini dan bisa menjadi topik yang menarik untuk pengembangan selanjutnya."*

Jika ditanya **perbandingan dengan tools/metode lain** yang tidak kamu riset:

> *"Dalam penelitian ini, saya memilih [metode yang dipakai] karena [alasan dari laporan]. Untuk perbandingan mendalam dengan [metode lain], itu bisa menjadi studi komparatif yang menarik di penelitian berikutnya."*

---

> **⚡ INGAT 3 HAL INI — Ini yang paling sering ditanya penguji:**
>
> 1. 🔀 **Fisher-Yates Shuffle** → Pseudocode + contoh iterasi + kenapa O(n) + kenapa distribusi uniform
> 2. 📸 **MediaPipe Face Mesh** → 4 landmark + Yaw/Pitch + sustained violation + kalibrasi
> 3. 🆕 **Novelty** → Integrasi 3 fitur + laporan bertingkat 3 level = belum ada di penelitian sebelumnya

---

*Dokumen ini dibuat berdasarkan analisis lengkap Laporan Akhir EduGames.*
*Terakhir diperbarui: 3 Juli 2026*
