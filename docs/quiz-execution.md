# Dokumentasi Pengerjaan Kuis (Sisi Siswa) - LMS2025

Dokumen ini menjelaskan mekanisme teknis saat siswa mengerjakan kuis, mulai dari pengacakan soal hingga sistem keamanan AI.

---

## 1. Pengacakan Soal (Fisher-Yates Shuffle)
Sistem menggunakan algoritma **Fisher-Yates** untuk memastikan setiap siswa mendapatkan urutan soal yang berbeda (jika fitur acak diaktifkan oleh instruktur).

```php
// app/Services/QuizShuffleService.php

private function fisherYatesShuffle(array $array): array
{
    $count = count($array);
    for ($i = $count - 1; $i > 0; $i--) {
        $j = random_int(0, $i); // Ambil index acak
        $temp = $array[$i];     // Proses Tukar (Swap)
        $array[$i] = $array[$j];
        $array[$j] = $temp;
    }
    return $array;
}
```
Urutan yang sudah diacak akan disimpan di tabel `quiz_attempt_question_order` agar saat siswa me-refresh halaman, urutan soal tetap konsisten.

---

## 2. Deteksi Perpindahan Tab (Tab Switching)
Menggunakan **Page Visibility API** pada JavaScript untuk mendeteksi kapan siswa meninggalkan halaman kuis.

```javascript
// resources/views/student/quizzes/take.blade.php

document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        // User pindah tab atau minimize window
        fetch('{{ route('student.quiz.log_tab_violation', $attempt) }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ timestamp: new Date().toISOString() })
        })
        .then(response => response.json())
        .then(data => {
            if (data.should_block) {
                // Jika melewati batas (threshold), kunci kuis dan auto-submit
                Swal.fire('Kuis Diblokir!', data.message, 'error');
                form.submit(); 
            }
        });
    }
});
```

---

## 3. Deteksi Wajah AI (Face Detection)
Menggunakan **MediaPipe Face Mesh** untuk mendeteksi arah pandangan siswa secara real-time melalui kamera.

### Logika Perhitungan Arah Kepala (Head Pose)
Sistem menghitung posisi koordinat hidung terhadap mata untuk menentukan apakah siswa menoleh.
```javascript
function calculateHeadPose(landmarks) {
    const nose = landmarks[1];      // Ujung hidung
    const leftEye = landmarks[33];  // Mata kiri
    const rightEye = landmarks[263]; // Mata kanan

    // Hitung Yaw (Horizontal: Kiri - Kanan)
    const eyeDistance = Math.abs(rightEye.x - leftEye.x);
    const noseToLeftEye = Math.abs(nose.x - leftEye.x);
    const noseToRightEye = Math.abs(nose.x - rightEye.x);
    
    // Jika hidung lebih dekat ke satu mata, berarti menoleh
    let yaw = (noseToRightEye - noseToLeftEye) / eyeDistance;
    return { yaw: yaw }; // Minus = Kiri, Plus = Kanan
}
```

---

## 4. Penyimpanan Jawaban (Backend)
Sistem mendukung berbagai tipe soal: Pilihan Ganda (Tunggal/Jamak), Benar/Salah, dan Drag & Drop.

```php
// app/Http/Controllers/Student/StudentQuizController.php

private function storeStudentAnswer(QuizAttempt $attempt, Question $question, $userAnswer, bool $isCorrect)
{
    if (is_array($userAnswer)) {
        // Untuk tipe soal dengan banyak jawaban (Multiple Choice Multiple / Drag & Drop)
        foreach ($userAnswer as $value) {
            $attempt->answers()->create([
                'question_id' => $question->id,
                'selected_option_id' => $value,
                'is_correct' => $isCorrect
            ]);
        }
    } else {
        // Untuk tipe soal jawaban tunggal
        $attempt->answers()->create([
            'question_id' => $question->id,
            'selected_option_id' => $userAnswer,
            'is_correct' => $isCorrect
        ]);
    }
}
```

---

## 5. Alur Penyelesaian (Submit)
Saat kuis selesai (atau waktu habis), sistem akan:
1.  Menghitung total skor berdasarkan poin per soal.
2.  Menentukan status 'passed' atau 'failed' berdasarkan `pass_mark`.
3.  Memberikan poin ke siswa jika lulus (menggunakan `PointService`).
4.  Mengecek apakah siswa berhak mendapatkan **Badge** baru.
