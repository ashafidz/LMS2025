# Dokumentasi Dasbor Integritas Kursus - LMS2025

Dokumen ini menjelaskan mekanisme teknis halaman **Course Quiz Violation Overview** yang merangkum seluruh aktivitas pelanggaran dari SEMUA kuis yang ada dalam satu kursus.

---

## 1. Logika Agregasi Global (Global Aggregation)
Sistem melakukan iterasi ke seluruh kuis yang dimiliki oleh kursus tersebut, kemudian menjumlahkan statistik dari tiap kuis untuk mendapatkan angka total kursus.

```php
// app/Http/Controllers/Instructor/InstructorQuizController.php

public function courseMonitoringOverview(Course $course)
{
    $quizzes = Quiz::whereHas('lesson.module', function ($query) use ($course) {
        $query->where('course_id', $course->id);
    })->with(['attempts.integritySummary'])->get();

    foreach ($quizzes as $quiz) {
        $tabViolations = $quiz->attempts->sum(fn($a) => $a->integritySummary?->total_tab_switches ?? 0);
        $cameraViolations = $quiz->attempts->sum(fn($a) => $a->integritySummary?->total_face_violations ?? 0);
        
        $quizData[] = [
            'quiz' => $quiz,
            'total_violations' => $tabViolations + $cameraViolations,
            'unique_students' => $quiz->attempts->pluck('student_id')->unique()->count()
        ];
    }
    // ... totalStats calculation
}
```

---

## 2. Metrik Utama (High-Level Stats)
Halaman ini menggunakan 6 kartu statistik untuk memberikan ringkasan instan kepada instruktur:
1.  **Total Kuis**: Jumlah kuis yang tersedia di kursus.
2.  **Student**: Jumlah siswa unik yang sudah berpartisipasi dalam kuis.
3.  **Total Attempts**: Akumulasi berapa kali seluruh kuis dikerjakan.
4.  **Tab Switches**: Akumulasi pelanggaran perpindahan tab di seluruh kuis.
5.  **Camera Violations**: Akumulasi pelanggaran deteksi wajah di seluruh kuis.
6.  **Total Violations**: Gabungan seluruh jenis pelanggaran (Indikator kesehatan kursus).

---

## 3. Daftar Pelanggaran per Kuis (Quizzes Table)
Tabel ini membedah statistik per kuis untuk mengidentifikasi kuis mana yang memiliki tingkat kecurangan tertinggi.

### Indikator Fitur Keamanan
Sistem menampilkan *badge* fitur yang aktif untuk setiap kuis agar instruktur tahu mengapa data pelanggaran muncul:
- `<span class="badge badge-info">Tab Detection</span>`
- `<span class="badge badge-primary">Camera Detection</span>`
- `<span class="badge badge-secondary">Shuffle</span>`

---

## 4. Alur Navigasi
Halaman ini berfungsi sebagai **pintu masuk utama** monitoring:
- **Level 1 (Halaman ini)**: Overview satu kursus (Banyak Kuis).
- **Level 2 (Review Kuis)**: Klik ikon mata untuk melihat Overview satu kuis (Banyak Siswa).
- **Level 3 (Detail Pelanggaran)**: Klik "Latest" atau "Detail" untuk melihat satu sesi (Bukti Foto/Log).

---

## 5. Kegunaan Audit
Halaman ini sangat berguna bagi admin atau instruktur untuk:
- Mengevaluasi apakah soal kuis terlalu sulit sehingga banyak siswa mencoba mencari jawaban di tab lain.
- Memastikan fitur keamanan (AI/Tab) sudah diaktifkan di kuis-kuis yang krusial (seperti UTS/UAS).
