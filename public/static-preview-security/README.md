# DOKUMENTASI PREVIEW STATIC - FITUR KEAMANAN KUIS ONLINE

## Platform E-Learning EduGames

---

## 📋 Daftar Halaman Static yang Tersedia

Folder: `/public/static-preview-security/`

### 1. **1-instructor-quiz-security-settings.html**

**Halaman:** Form Pengaturan Keamanan Kuis (Instructor)  
**Fitur:**

-   Toggle 3 opsi keamanan (Deteksi Kamera, Deteksi Tab, Pengacakan Soal)
-   Input threshold untuk batas pelanggaran
-   Interval deteksi wajah
-   Ringkasan status keamanan aktif
-   Interactive toggle dengan hide/show detail settings

**Cara Akses:**

```
http://localhost:8000/static-preview-security/1-instructor-quiz-security-settings.html
```

**Screenshot Fitur:**

-   ✅ Deteksi Kamera dengan setting threshold & interval
-   ✅ Deteksi Perpindahan Tab dengan setting threshold
-   ✅ Pengacakan Urutan Soal (Fisher-Yates)
-   ✅ Badge status real-time (ON/OFF)

---

### 2. **2-student-take-quiz-with-monitoring.html**

**Halaman:** Student Mengerjakan Kuis dengan Monitoring Aktif  
**Fitur:**

-   Video webcam feed (floating, top-right corner)
-   Panel status monitoring real-time
-   Counter pelanggaran (Tab Switch, Face Not Detected, Look Away)
-   Warning popup saat pelanggaran terdeteksi
-   Timer countdown kuis
-   Progress bar pengerjaan soal
-   Navigasi soal dengan indikator terjawab/belum

**Cara Akses:**

```
http://localhost:8000/static-preview-security/2-student-take-quiz-with-monitoring.html
```

**Screenshot Fitur:**

-   ✅ Webcam feed dengan overlay "Monitoring Aktif"
-   ✅ Monitoring panel dengan counter pelanggaran
-   ✅ Simulasi tab switch detection (real!)
-   ✅ Warning alert saat pelanggaran
-   ✅ Info keamanan aktif di sidebar

**Note:**

-   Webcam akan request permission (gunakan browser yang support getUserMedia)
-   Tab switch detection bekerja real-time (coba buka tab lain)
-   Face detection violation disimulasikan dengan random timer

---

### 3. **3-instructor-integrity-report-detail.html**

**Halaman:** Laporan Integritas Detail per Attempt (Instructor)  
**Fitur:**

-   Informasi lengkap peserta & waktu ujian
-   4 Card statistik (Skor Integritas, Tab Switch, Face Not Detected, Look Away)
-   Rincian pelanggaran per tipe
-   Badge "DITANDAI UNTUK REVIEW" jika flagged
-   Timeline pelanggaran dengan timestamp
-   Gallery screenshot bukti pelanggaran
-   Modal untuk revisi skor
-   Modal untuk preview screenshot full-size

**Cara Akses:**

```
http://localhost:8000/static-preview-security/3-instructor-integrity-report-detail.html
```

**Screenshot Fitur:**

-   ✅ Card skor integritas dengan risk level (LOW/MEDIUM/HIGH)
-   ✅ Timeline violations dengan icon & warna per tipe
-   ✅ Screenshot gallery dengan hover effect
-   ✅ Form revisi skor dengan alasan
-   ✅ Export laporan PDF button

---

### 4. **4-instructor-integrity-recap-per-quiz.html**

**Halaman:** Rekapitulasi Laporan Integritas per Kuis (Instructor)  
**Fitur:**

-   4 Card statistik overview (Total Attempts, Peserta Unik, Avg Integrity, Flagged)
-   Pie chart distribusi risk level
-   Bar chart perbandingan jenis pelanggaran
-   Tabel detail per peserta dengan sorting & pagination
-   Row highlighting untuk risk level tinggi
-   Link ke detail report per attempt
-   Insight & rekomendasi
-   Export Excel & PDF

**Cara Akses:**

```
http://localhost:8000/static-preview-security/4-instructor-integrity-recap-per-quiz.html
```

**Screenshot Fitur:**

-   ✅ 4 Stat cards dengan hover effect
-   ✅ Chart.js untuk visualisasi data
-   ✅ Tabel interaktif dengan badge risk level
-   ✅ Row click menuju detail
-   ✅ Insight box dengan rekomendasi

---

## 🎨 Style & Design Consistency

Semua halaman menggunakan:

-   **Bootstrap 4.x** dari project existing
-   **Font Awesome 6.5** untuk icons
-   **Bootstrap Icons 1.10** untuk additional icons
-   **Color scheme** sesuai template existing:
    -   Primary: `#4680ff`
    -   Success: `#2ed8b6`
    -   Warning: `#FFB64D`
    -   Danger: `#FF5370`
-   **Layout:** pcoded-content structure (sama dengan blade existing)

---

## 🚀 Cara Menggunakan Preview

### 1. Akses via Browser

Buka browser dan navigasi ke:

```
http://localhost:8000/static-preview-security/
```

Atau akses langsung file HTML:

```
http://localhost:8000/static-preview-security/1-instructor-quiz-security-settings.html
http://localhost:8000/static-preview-security/2-student-take-quiz-with-monitoring.html
http://localhost:8000/static-preview-security/3-instructor-integrity-report-detail.html
http://localhost:8000/static-preview-security/4-instructor-integrity-recap-per-quiz.html
```

### 2. Test Interaktivity

**Halaman 1 (Quiz Settings):**

-   ✅ Toggle setiap checkbox keamanan → settings muncul/hilang
-   ✅ Badge status berubah ON/OFF
-   ✅ Hover pada info icon → tooltip muncul

**Halaman 2 (Take Quiz):**

-   ✅ Allow webcam permission → video feed muncul
-   ✅ Buka tab baru → counter tab switch bertambah + warning muncul
-   ✅ Timer countdown berjalan otomatis
-   ✅ Klik Next/Previous → soal berganti
-   ✅ Random face violation setiap 10 detik (demo purpose)

**Halaman 3 (Detail Report):**

-   ✅ Klik screenshot → modal preview full-size
-   ✅ Klik "Revisi Skor" → modal form muncul
-   ✅ Timeline scroll smooth

**Halaman 4 (Recap Quiz):**

-   ✅ Chart.js render pie & bar chart
-   ✅ Tabel row hover effect
-   ✅ Klik row → redirect ke halaman 3 (detail)
-   ✅ Pagination clickable

### 3. Testing di Different Screen Sizes

Semua halaman responsive untuk:

-   Desktop (1920x1080)
-   Laptop (1366x768)
-   Tablet (768x1024)

---

## 📁 File Structure

```
public/
└── static-preview-security/
    ├── 1-instructor-quiz-security-settings.html
    ├── 2-student-take-quiz-with-monitoring.html
    ├── 3-instructor-integrity-report-detail.html
    ├── 4-instructor-integrity-rep-per-quiz.html
    └── README.md (ini)
```

---

## 🔗 Navigation Flow

```
[1. Quiz Settings]
    ↓ (After save)
[2. Student Take Quiz with Monitoring]
    ↓ (After submit)
[Student Result Page - existing]

[Instructor Dashboard]
    ↓
[4. Recap per Quiz]
    ↓ (Click detail button)
[3. Detail Report per Attempt]
    ↓ (Click revise score)
[Modal Revise Score]
```

---

## 📊 Data yang Ditampilkan (Sample Static)

### Halaman 2 (Student Take Quiz):

-   Timer: 59:45
-   Total Soal: 10
-   Sample violations untuk demo

### Halaman 3 (Detail Report):

-   Student: Ahmad Fauzi Hidayat
-   Integrity Score: 72/100 (MEDIUM RISK)
-   Tab Switch: 7x
-   Face Not Detected: 5x
-   Look Away: 9x (left 3x, right 4x, down 2x)
-   Total Violations: 21x
-   Screenshot: 6 samples

### Halaman 4 (Recap Quiz):

-   Total Attempts: 45
-   Unique Students: 32
-   Avg Integrity: 78.5
-   Flagged: 12
-   Risk Distribution: Low 18, Medium 15, High 12
-   Sample: 6 students dalam tabel

---

## 🛠️ Dependencies

**External Libraries Used:**

-   jQuery 3.6.0 (CDN)
-   Bootstrap 4.x (local from project)
-   Font Awesome 6.5 (CDN)
-   Bootstrap Icons 1.10 (CDN)
-   Chart.js 4.x (CDN) - untuk halaman 4

**Browser Requirements:**

-   Modern browser dengan support:
    -   CSS Grid
    -   Flexbox
    -   getUserMedia API (untuk webcam)
    -   Page Visibility API (untuk tab detection)

---

## 🎯 Tujuan Preview Static

1. **Visualisasi UI/UX** sebelum implementasi backend
2. **User Testing** untuk mendapat feedback design
3. **Reference untuk Developer** saat coding actual features
4. **Presentasi ke Stakeholder** tanpa perlu setup database
5. **Documentation** untuk proposal/skripsi

---

## 📝 Notes untuk Development

### Halaman 1 - Quiz Settings:

-   Implementasi: Simpan ke tabel `quiz_security_settings`
-   Validation: Min/max values untuk threshold
-   Default values sudah ditentukan di form

### Halaman 2 - Take Quiz:

-   MediaPipe Face Mesh: Perlu integrate TensorFlow.js
-   Tab Detection: Gunakan `document.addEventListener('visibilitychange')`
-   Screenshot: `canvas.toDataURL()` untuk capture
-   API endpoint: `POST /api/quiz-attempts/{id}/log-violation`

### Halaman 3 - Detail Report:

-   Query: `monitoring_logs` JOIN `quiz_attempt_integrity_summary`
-   Screenshot path: `storage/app/monitoring_screenshots/`
-   Revisi skor: Update `quiz_attempts.score` + log ke audit table

### Halaman 4 - Recap:

-   Aggregation query dari multiple attempts
-   Chart data dari database, bukan hardcode
-   Real-time filtering & sorting untuk tabel

---

## 🔄 Update Log

**Version 1.0 - 08 Des 2025**

-   ✅ Halaman 1: Quiz Security Settings
-   ✅ Halaman 2: Student Take Quiz with Monitoring
-   ✅ Halaman 3: Instructor Detail Integrity Report
-   ✅ Halaman 4: Instructor Recap per Quiz
-   ✅ Full responsive design
-   ✅ Interactive elements
-   ✅ Consistent styling dengan existing template

---

## 📞 Contact

Untuk pertanyaan atau feedback tentang preview static ini, hubungi tim development.

---

**Preview Static ini dibuat untuk keperluan penelitian:**  
_"Implementasi Fitur Keamanan Ujian Online Menggunakan Algoritma Fisher-Yates Shuffle dan MediaPipe Face Mesh pada Platform E-Learning EduGames"_
