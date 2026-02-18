# Diagram Alur Sistem Kuis LMS2025

Dokumen ini menjelaskan alur lengkap sistem kuis dari pendaftaran instruktur hingga monitoring hasil.

---

## 1. Alur Besar (Overview)

```mermaid
flowchart TB
    A["1. Pendaftaran Instruktur"] --> B["2. Pembuatan Kursus & Struktur"]
    B --> C["3. Bank Soal & Manajemen Soal"]
    C --> D["4. Pembuatan Quiz & Attach Soal"]
    D --> E["5. Setting Keamanan Quiz"]
    E --> F["6. Publikasi Kursus"]
    F --> G["7. Enrollment Mahasiswa"]
    G --> H["8. Pengerjaan Quiz oleh Mahasiswa"]
    H --> I["9. Penilaian & Skor"]
    I --> J["10. Monitoring & Review"]
    J --> K["11. Revisi Skor"]
    K --> L["12. Rekap Nilai"]
```

---

## 2. Pendaftaran & Persetujuan Instruktur

```mermaid
flowchart LR
    A["User Mendaftar"] --> B["Pilih Role: Instruktur"]
    B --> C["Status: Pending"]
    C --> D{"Admin/SuperAdmin Review"}
    D -->|Approve| E["Status: Approved ✅"]
    D -->|Reject| F["Status: Rejected ❌"]
    D -->|Deactivate| G["Status: Deactive ⏸️"]
    G -->|Reactivate| E
    E --> H["Akses Dashboard Instruktur"]
```

**Route terkait:**
| Aksi | Route |
|------|-------|
| Daftar Instruktur | `POST /register` |
| Halaman Pending | `GET /instructor/pending` |
| Admin Approve | `PATCH /admin/applications/{id}/approve` |
| Admin Reject | `PATCH /admin/applications/{id}/reject` |

---

## 3. Pembuatan Kursus, Modul & Pelajaran

```mermaid
flowchart TB
    A["Instruktur Dashboard"] --> B["Buat Kursus Baru"]
    B --> C["Isi Detail Kursus"]
    C --> D["Buat Modul dalam Kursus"]
    D --> E["Buat Pelajaran dalam Modul"]
    E --> F{"Tipe Pelajaran?"}
    F -->|Artikel| G["Lesson: Article"]
    F -->|Video| H["Lesson: Video"]
    F -->|Dokumen| I["Lesson: Document"]
    F -->|Quiz| J["Lesson: Quiz 📝"]
    F -->|Tugas| K["Lesson: Assignment"]
    F -->|Poin| L["Lesson: Point"]
    J --> M["Konfigurasi Quiz"]
    M --> N["Set Judul, Durasi, Pass Mark, Max Attempts"]
```

**Hierarki Struktur:**

```
Kursus (Course)
├── Modul 1
│   ├── Pelajaran 1 (Artikel)
│   ├── Pelajaran 2 (Video)
│   └── Pelajaran 3 (Quiz) ← FOKUS DOKUMEN INI
├── Modul 2
│   └── ...
└── Modul N
```

**Route terkait:**
| Aksi | Route |
|------|-------|
| Buat Kursus | `POST /instructor/courses` |
| Buat Modul | `POST /instructor/courses/{course}/modules` |
| Buat Pelajaran | `POST /instructor/modules/{module}/lessons` |

---

## 4. Bank Soal & Manajemen Soal

```mermaid
flowchart TB
    A["Bank Soal"] --> B["Buat Topik Soal"]
    B --> C["Buat Soal dalam Topik"]
    C --> D["Isi Pertanyaan + Opsi Jawaban"]
    D --> E["Tandai Jawaban Benar + Set Skor"]
    E --> F["Soal Tersimpan di Bank ✅"]

    F --> G["Clone Soal ke Topik Lain"]
    F --> H["Pindahkan Soal antar Topik"]
    F --> I["Edit / Hapus Soal"]
```

**Route terkait:**
| Aksi | Route |
|------|-------|
| List Topik | `GET /instructor/question-topics` |
| Buat Topik | `POST /instructor/question-topics` |
| Buat Soal | `POST /instructor/question-topics/{topic}/questions` |
| Clone Soal | `POST /questions/{question}/clone` |
| Pindah Topik | `PATCH /questions/{question}/move` |

---

## 5. Attach Soal ke Quiz

```mermaid
flowchart LR
    A["Halaman Quiz: Manage Questions"] --> B["Browse Bank Soal"]
    B --> C["Pilih Soal dari Bank"]
    C --> D["Attach Soal ke Quiz"]
    D --> E["Soal Muncul di Quiz ✅"]
    E --> F["Detach Soal (Hapus dari Quiz)"]
```

**Route terkait:**
| Aksi | Route |
|------|-------|
| Manage Questions | `GET /instructor/quizzes/{quiz}/manage-questions` |
| Browse Bank | `GET /instructor/quizzes/{quiz}/browse-bank` |
| Attach Soal | `POST /instructor/quizzes/{quiz}/attach-questions` |
| Detach Soal | `DELETE /instructor/quizzes/{quiz}/detach-question/{question}` |

---

## 6. Setting Keamanan Quiz (Proctoring)

```mermaid
flowchart TB
    A["Halaman Security Settings"] --> B{"Fitur Keamanan"}
    B --> C["🖥️ Tab Detection"]
    B --> D["📷 Camera Detection"]

    C --> C1["Enable/Disable"]
    C --> C2["Set Tab Violation Threshold"]

    D --> D1["Enable/Disable"]
    D --> D2["Set Camera Violation Threshold"]
    D --> D3["Set Interval Deteksi (3-30 detik)"]

    C2 --> E["Jika Pelanggaran ≥ Threshold → Quiz Diblokir & Auto Submit"]
    D2 --> E
    D3 --> F["Interval = Jeda antar pengecekan wajah AI"]
```

**Penjelasan Setting:**

| Setting                           | Deskripsi                                      |
| --------------------------------- | ---------------------------------------------- |
| `enable_tab_detection`            | Deteksi perpindahan tab/window                 |
| `tab_violation_threshold`         | Batas pelanggaran tab sebelum quiz diblokir    |
| `enable_camera_detection`         | Aktifkan webcam AI untuk deteksi wajah         |
| `camera_violation_threshold`      | Batas pelanggaran kamera sebelum quiz diblokir |
| `face_detection_interval_seconds` | Jeda (detik) antar pengecekan wajah oleh AI    |

**Route terkait:**
| Aksi | Route |
|------|-------|
| Edit Setting | `GET /quiz/{quiz}/security` |
| Simpan Setting | `POST /quiz/{quiz}/security` |
| Hapus Setting | `DELETE /quiz/{quiz}/security` |

---

## 7. Publikasi Kursus & Enrollment Mahasiswa

```mermaid
flowchart LR
    A["Instruktur Submit for Review"] --> B{"Admin Review"}
    B -->|Publish| C["Kursus Published ✅"]
    B -->|Reject| D["Kursus Rejected ❌"]
    C --> E["Mahasiswa Enroll ke Kursus"]
    E --> F["Mahasiswa Akses Konten Kursus"]
```

**Route terkait:**
| Aksi | Route |
|------|-------|
| Submit Review | `PATCH /instructor/courses/{course}/submit-review` |
| Admin Publish | `PATCH /admin/publication/{course}/publish` |
| Enroll Mahasiswa | `POST /admin/course-enrollments/{course}` |

---

## 8. Pengerjaan Quiz oleh Mahasiswa

```mermaid
flowchart TB
    A["Mahasiswa Buka Lesson Quiz"] --> B["Klik: Mulai Quiz"]
    B --> C["Halaman Start Quiz"]
    C --> D["POST: Begin Quiz"]
    D --> E["Buat Quiz Attempt Baru"]
    E --> F["Halaman Take Quiz"]

    F --> G["Jawab Soal Satu per Satu"]
    G --> H["Timer Berjalan"]

    subgraph proctoring ["🔒 Proctoring (Jika Aktif)"]
        H --> I["Tab Detection: Deteksi Pindah Tab"]
        H --> J["Camera Detection: AI Cek Wajah Setiap N Detik"]
        I --> I1["POST: Log Tab Violation"]
        J --> J1["POST: Log Camera Violation + Screenshot"]
        I1 --> K{"Pelanggaran ≥ Threshold?"}
        J1 --> K
        K -->|Ya| L["Quiz Diblokir 🔒"]
        L --> M["Auto Submit Setelah 5 Detik"]
    end

    G --> N["Klik: Selesaikan Quiz"]
    N --> O["Modal Konfirmasi"]
    O --> P["POST: Submit Quiz"]
    M --> P

    P --> Q["Hitung Skor"]
```

**Proses Perhitungan Skor saat Submit:**

```
1. Loop setiap soal → cek jawaban → hitung totalScore (skor mentah)
2. maxPossibleScore = sum(semua skor soal)
3. percentageScore = (totalScore / maxPossibleScore) × 100
4. scaled_score = round(percentageScore, 2)       ← DISIMPAN KE DB
5. status = percentageScore >= pass_mark ? 'passed' : 'failed'
6. Simpan: score, scaled_score, status, end_time
```

**3 Jenis Skor yang Disimpan:**

| Kolom           | Keterangan                      | Kapan Diisi              |
| --------------- | ------------------------------- | ------------------------ |
| `score`         | Skor mentah (jumlah poin benar) | Saat submit              |
| `scaled_score`  | Skor skala 0-100                | Saat submit              |
| `revised_score` | Skor revisi instruktur          | Saat instruktur merevisi |

**Route terkait:**
| Aksi | Route |
|------|-------|
| Start Quiz | `GET /student/quiz/{quiz}/start` |
| Begin Quiz | `POST /student/quiz/{quiz}/begin` |
| Take Quiz | `GET /student/quiz/attempt/{attempt}` |
| Submit Quiz | `POST /student/quiz/attempt/{attempt}/submit` |
| Hasil Quiz | `GET /student/quiz/attempt/{attempt}/result` |
| Log Tab | `POST /student/quiz/attempt/{attempt}/log-tab-violation` |
| Log Camera | `POST /student/quiz/attempt/{attempt}/log-camera-violation` |

---

## 9. Halaman Hasil Quiz (Student)

```mermaid
flowchart LR
    A["Submit Quiz"] --> B["Halaman Hasil"]
    B --> C["Tampilkan:"]
    C --> D["Skor Mentah (score)"]
    C --> E["Nilai Skala 0-100 (scaled_score)"]
    C --> F["Status: Lulus/Gagal"]
    C --> G["Skor Minimum & Passing Grade"]
```

---

## 10. Monitoring & Review oleh Instruktur

### 10a. Monitoring Per Kursus

```mermaid
flowchart TB
    A["Instruktur Dashboard"] --> B["Pilih Kursus"]
    B --> C["Halaman: Course Monitoring Overview"]
    C --> D["Lihat Semua Quiz dalam Kursus"]
    D --> E["Klik Quiz → Monitoring Per Quiz"]
```

**Route:** `GET /course/{course}/monitoring`

---

### 10b. Monitoring Per Quiz

```mermaid
flowchart TB
    A["Monitoring Per Quiz"] --> B["Tabel Semua Mahasiswa"]
    B --> C["Setiap Mahasiswa:"]
    C --> D["Skor Terbaik"]
    C --> E["Total Pelanggaran (Tab + Camera)"]
    C --> F["Status: Lulus/Gagal"]
    B --> G["Klik: Lihat Detail"]
    G --> H["Modal: Riwayat Semua Attempt"]
    H --> I["Setiap Attempt:"]
    I --> I1["Waktu Mulai"]
    I --> I2["Skor + Skor Revisi"]
    I --> I3["Jumlah Pelanggaran"]
    I --> I4["Button: Detail → Monitoring Detail"]
    I --> I5["Button: Nilai Kuis → Periksa Jawaban"]
```

**Route:** `GET /quiz/{quiz}/monitoring`

---

### 10c. Monitoring Per Attempt (Detail)

```mermaid
flowchart TB
    A["Monitoring Detail Per Attempt"] --> B["Informasi Mahasiswa"]
    B --> B1["Nama, Skor Mentah, Scaled Score"]
    B --> B2["Skor Revisi (jika ada)"]

    A --> C["Log Pelanggaran Tab"]
    C --> C1["Tabel: Waktu setiap pindah tab"]

    A --> D["Log Pelanggaran Kamera"]
    D --> D1["Tabel: Jenis Pelanggaran + Screenshot"]
    D --> D2["Jenis: face_not_detected, look_left, look_right, look_up, look_down"]

    A --> E["Form Revisi Skor"]
    E --> E1["Input: Skor Revisi Baru"]
    E --> E2["Input: Catatan Revisi"]
    E --> E3["Submit → Simpan revised_score"]

    A --> F["Button: Nilai Kuis → Periksa Jawaban"]
```

**Route:** `GET /quiz/attempt/{attempt}/monitoring-detail`

---

## 11. Periksa Jawaban & Revisi Skor

```mermaid
flowchart TB
    A["Halaman Periksa Jawaban"] --> B["Kartu Ringkasan"]
    B --> B1["Skor Student vs Minimum vs Maksimum"]
    B --> B2["Nilai Skala 0-100 vs Passing Grade"]
    B --> B3["Informasi Revisi Skor"]
    B3 --> B3a["Skor Revisi (atau -)"]
    B3 --> B3b["Direvisi Oleh (atau -)"]
    B3 --> B3c["Waktu Revisi (atau -)"]
    B3 --> B3d["Catatan Revisi (atau -)"]

    A --> C["Rincian Jawaban Per Soal"]
    C --> C1["Jawaban Mahasiswa (Hijau=Benar, Merah=Salah)"]
    C --> C2["Kunci Jawaban (Biru)"]
    C --> C3["Penjelasan Soal"]

    A --> D["Button: Monitor → Monitoring Detail"]
```

**Route:** `GET /instructor/quiz-attempts/{attempt}/review`

---

## 12. Hasil Kuis (Daftar Per Quiz)

```mermaid
flowchart TB
    A["Halaman Hasil Kuis"] --> B["Tabel Semua Mahasiswa"]
    B --> C["Status Pengerjaan: Lulus/Gagal/Belum"]
    B --> D["Klik: Lihat Riwayat"]
    D --> E["Modal: Semua Attempt"]
    E --> F["Setiap Attempt:"]
    F --> F1["Skor Mentah + Skor Revisi"]
    F --> F2["Nilai Skala 0-100"]
    F --> F3["Status"]
    F --> F4["Button: Periksa Jawaban"]
    F --> F5["Button: Monitor"]
```

**Route:** `GET /instructor/quizzes/{quiz}/results`

---

## 13. Rekap Nilai Per Modul

```mermaid
flowchart TB
    A["Halaman Rekap Nilai"] --> B["Pilih Modul"]
    B --> C["Tabel Mahasiswa × Pelajaran"]
    C --> D["Setiap Sel = Nilai Quiz"]

    D --> E{"Ada revised_score?"}
    E -->|Ya| F["Tampilkan: (revised_score / maxScore) × 100"]
    E -->|Tidak| G{"Ada scaled_score?"}
    G -->|Ya| H["Tampilkan: scaled_score langsung"]
    G -->|Tidak| I["Fallback: (score / maxScore) × 100"]

    C --> J["Download PDF"]
    C --> K["Download Excel"]
```

**Prioritas Skor di Rekap:**

```
1. revised_score (jika instruktur sudah merevisi) → dihitung ulang ke skala 0-100
2. scaled_score (jika tersedia di database) → langsung dipakai
3. Fallback: hitung manual dari score/maxScore × 100 (untuk data lama)
```

**Route terkait:**
| Aksi | Route |
|------|-------|
| Halaman Rekap | `GET /instructor/courses/{course}/recap` |
| Data Modul (AJAX) | `GET /instructor/modules/{module}/recap-data` |
| Download PDF | `GET /instructor/modules/{module}/recap/pdf` |
| Download Excel | `GET /instructor/modules/{module}/recap/excel` |

---

## 14. Navigasi Antar Halaman

```mermaid
flowchart LR
    A["Hasil Kuis\n(results/index)"] <-->|Monitor ↔ Nilai Kuis| B["Monitoring Review\n(quiz/monitoring)"]
    A -->|Periksa Jawaban| C["Review Jawaban\n(results/show)"]
    B -->|Detail| D["Monitoring Detail\n(monitoring-detail)"]
    C <-->|Monitor ↔ Nilai Kuis| D
```

| Dari Halaman           | Button         | Menuju            |
| ---------------------- | -------------- | ----------------- |
| Hasil Kuis (index)     | **Monitor**    | Monitoring Detail |
| Periksa Jawaban (show) | **Monitor**    | Monitoring Detail |
| Monitoring Review      | **Nilai Kuis** | Periksa Jawaban   |
| Monitoring Detail      | **Nilai Kuis** | Periksa Jawaban   |
