# Dokumentasi Laporan Integritas per Kuis - LMS2025

Dokumen ini menjelaskan mekanisme teknis halaman **Violation Review** yang merangkum seluruh aktivitas pelanggaran siswa pada satu kuis spesifik.

---

## 1. Logika Pengumpulan Data (Grouping)
Karena satu siswa dapat mencoba kuis berkali-kali (jika diizinkan), sistem mengelompokkan data berdasarkan `student_id` untuk menampilkan sesi terbaru serta riwayat pengerjaan.

```php
// app/Http/Controllers/Instructor/InstructorQuizController.php

public function monitoringReview(Quiz $quiz)
{
    $attempts = QuizAttempt::where('quiz_id', $quiz->id)
        ->with(['student', 'integritySummary'])
        ->orderBy('created_at', 'desc')
        ->get();

    // Mengelompokkan berdasarkan siswa
    $attemptsByStudent = $attempts->groupBy('student_id')->map(function ($studentAttempts) {
        return [
            'student' => $studentAttempts->first()->student,
            'latest_attempt' => $studentAttempts->sortByDesc('created_at')->first(),
            'all_attempts' => $studentAttempts->sortByDesc('created_at')->values()
        ];
    });

    return view('instructor.quizzes.monitoring-review', compact('quiz', 'attemptsByStudent', 'stats'));
}
```

---

## 2. Statistik Akumulasi (Summary Cards)
Halaman ini menampilkan kartu statistik untuk memberikan gambaran cepat mengenai total anomali yang terjadi:
- **Total Attempts**: Berapa kali kuis telah dikerjakan.
- **Total Tab Switches**: Akumulasi seluruh siswa yang berpindah tab.
- **Total Camera Violations**: Akumulasi seluruh pelanggaran gerakan wajah.
- **Total Expelled**: Jumlah siswa yang otomatis dikeluarkan sistem karena melewati batas toleransi.

---

## 3. Komponen Tabel Utama
Tabel utama menampilkan ringkasan sesi **terakhir** untuk setiap siswa.

| Kolom | Penjelasan |
|---|---|
| **Student** | Nama, Email, dan jumlah total percobaan yang dilakukan. |
| **Waktu & Skor** | Menampilkan waktu mulai dan skor (termasuk jika ada skor revisi). |
| **Tab Switch** | Jumlah perpindahan tab pada sesi terakhir. |
| **Camera Violations** | Breakdown detail (No Face, Left, Right, Down, Up). |
| **Dikeluarkan** | Status apakah siswa dikeluarkan paksa oleh sistem. |

---

## 4. Modal Riwayat (Attempt History)
Instruktur dapat melihat riwayat pengerjaan lengkap seorang siswa melalui modal "History". Ini penting untuk melihat apakah seorang siswa melakukan kecurangan yang berulang (pattern) atau hanya pada satu sesi saja.

```html
@foreach($allAttempts as $index => $historyAttempt)
<tr>
    <td>{{ $index + 1 }}</td>
    <td>{{ $historyAttempt->start_time->format('d M Y H:i') }}</td>
    <td>{{ $historyAttempt->score }}</td>
    <td>
        <a href="{{ route('instructor.quiz.monitoring.detail', $historyAttempt) }}">Detail</a>
    </td>
</tr>
@endforeach
```

---

## 5. Integrasi Navigasi
Halaman ini bertindak sebagai jembatan:
1.  **Ke Belakang**: Kembali ke daftar modul/pelajaran.
2.  **Ke Depan (Detail)**: Menuju halaman `monitoring-detail` untuk melihat bukti visual (screenshot) per sesi.
3.  **Ke Nilai**: Menuju halaman `review_attempt` untuk melihat rincian jawaban soal kuis.
