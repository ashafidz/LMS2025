# DOKUMENTASI ALUR SISTEM KUIS - LMS2025

## Deskripsi Umum

Dokumen ini menjelaskan secara detail alur lengkap student mengerjakan kuis di sistem LMS2025, mulai dari melihat kuis di dalam kursus hingga mendapatkan hasil/skor. Dokumentasi ini dibuat untuk membantu pengembangan sistem kuis lebih lanjut.

---

## 1. HALAMAN AWAL: Melihat Daftar Kursus & Masuk ke Detail Kursus

### Lokasi File:

-   View: `resources/views/student/courses/show.blade.php`
-   Controller: `app/Http/Controllers/Student/CourseController.php` (method `show`)
-   Route: `/student/courses/{course:slug}` → `student.courses.show`

### Alur:

1. Student membuka halaman kursus yang sudah di-enroll
2. Halaman menampilkan:
    - Header dengan judul kursus dan poin student saat ini
    - Kolom utama (kiri): Area konten yang akan berubah saat lesson dipilih
    - Kolom sidebar (kanan): Daftar isi kursus (accordion per modul)

### Detail Tampilan Sidebar:

-   Setiap modul bisa diklik untuk expand/collapse
-   Di dalam modul ada list lesson termasuk **lesson bertipe Quiz**
-   Icon untuk quiz: `bi bi-pencil-square`
-   Jika lesson sudah complete, ada icon check hijau: `fa fa-check-circle text-success`

---

## 2. MELIHAT PREVIEW KUIS DI DALAM LESSON

### Alur User:

Student **mengklik link lesson Quiz** dari sidebar daftar isi kursus.

### Proses Backend:

**JavaScript di halaman `show.blade.php`** menangkap klik dan melakukan AJAX request:

```javascript
// Event listener untuk link lesson
lessonLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
        e.preventDefault();
        loadLessonContent(this.dataset.lessonId);
    });
});
```

**AJAX Request:**

-   Endpoint: `/student/lessons/{lesson}/content`
-   Method: `GET`
-   Route: `student.lessons.content`
-   Controller: `CourseController@getContent`

### File yang Terlibat:

-   Controller: `app/Http/Controllers/Student/CourseController.php` (method `getContent`)
-   Partial View Quiz: `resources/views/student/quizzes/partials/_quiz_preview_in_lesson.blade.php`

### Data yang Ditampilkan di Preview:

1. **Judul & Deskripsi Quiz**
2. **Informasi Quiz:**

    - Jumlah soal
    - Total skor maksimal
    - Skor minimum (dari pass_mark)
    - Passing grade (dalam %)
    - Nilai minimum (scaled 0-100)
    - Batas waktu (menit atau "Tidak ada")
    - Kesempatan mengerjakan (jumlah attempt saat ini vs max_attempts)
    - Skor terakhir (jika sudah pernah mengerjakan)
    - Status terakhir (Lulus/Gagal/Sedang Dikerjakan)
    - Boleh melebihi batas waktu (Ya/Tidak)
    - Tersedia mulai (tanggal)
    - Tutup pada (tanggal)

3. **Riwayat Semua Percobaan (Attempt History)**

    - Tabel menampilkan semua attempt sebelumnya
    - Kolom: No, Waktu Mulai, Waktu Selesai, Durasi, Skor, Nilai (0-100), Status, Aksi
    - Button "Lihat Detail" untuk melihat hasil attempt sebelumnya

4. **Tombol Aksi:**
    - **"Mulai Kuis"** (jika belum pernah mengerjakan)
    - **"Coba Lagi"** (jika sudah pernah mengerjakan)
    - Tombol disabled "Kesempatan Habis" (jika attempt >= max_attempts)
    - Tombol disabled "Tidak Tersedia" (jika belum/sudah lewat jadwal)

### Validasi Sebelum Mulai:

-   Cek apakah quiz tersedia berdasarkan `available_from` dan `available_to`
-   Cek apakah masih ada kesempatan (attempt count < max_attempts)
-   Jika ada attempt yang status `in_progress`, student akan langsung diredirect ke halaman take quiz

---

## 3. HALAMAN START QUIZ (Halaman Konfirmasi Sebelum Mulai)

### Alur User:

Student **mengklik tombol "Mulai Kuis" atau "Coba Lagi"** dari preview.

### Navigasi:

-   Dari preview quiz, klik tombol mengarah ke route: `student.quiz.start`
-   URL: `/student/quiz/{quiz}/start`
-   Controller: `StudentQuizController@start`

### File yang Terlibat:

-   Controller: `app/Http/Controllers/Student/StudentQuizController.php` (method `start`)
-   View: `resources/views/student/quizzes/start.blade.php`

### Proses di Backend (method `start`):

1. **Validasi enrollment**: Pastikan student terdaftar di kursus
2. **Hitung attempt count**: Berapa kali sudah mencoba quiz ini
3. **Cari attempt in_progress**: Jika ada attempt yang masih berlangsung, redirect ke halaman take
4. **Cari last attempt**: Tampilkan skor/status terakhir untuk informasi
5. **Cek ketersediaan berdasarkan jadwal**:
    - Jika sebelum `available_from` → tidak tersedia
    - Jika setelah `available_to` → waktu habis
6. **Cek batas attempt**: Jika sudah >= max_attempts → tombol disabled

### Tampilan Halaman Start:

**Header:**

-   Judul: "Mulai Kuis"
-   Breadcrumb: Home > Kursus Saya > Kuis

**Card Utama:**

-   Judul quiz
-   Deskripsi quiz
-   **4 Informasi Utama (dengan icon):**
    1. Jumlah Soal (icon: question-circle)
    2. Batas Waktu (icon: clock)
    3. Nilai Kelulusan % (icon: check-square)
    4. Kesempatan (icon: repeat) → contoh: "1 / 3 Kali" atau "Tanpa Batas"

**Tombol:**

-   **Mode Student:**
    -   "Mulai Kuis" (percobaan pertama)
    -   "Coba Lagi" (percobaan berikutnya)
    -   "Kesempatan Habis" (disabled, jika attempt habis)
    -   "Tidak Tersedia" (disabled, jika di luar jadwal)
-   **Mode Preview (untuk instructor/admin):**
    -   "Mulai Kuis (Preview)"

### Form Submission:

```html
<form action="{{ route('student.quiz.begin', $quiz->id) }}" method="POST">
    @csrf
    <button type="submit" class="btn btn-primary btn-lg">Mulai Kuis</button>
</form>
```

---

## 4. BEGIN QUIZ - Membuat Attempt Baru

### Alur User:

Student **mengklik tombol "Mulai Kuis"** di halaman start.

### Proses Backend:

-   Route: `POST /student/quiz/{quiz}/begin`
-   Controller: `StudentQuizController@begin`

### Logika di method `begin`:

1. **Validasi enrollment**: Pastikan student terdaftar
2. **Cek batas attempt**: Jika sudah mencapai max_attempts, redirect dengan error
3. **Cek existing attempt in_progress**: Jika ada, redirect ke halaman take
4. **Mode Preview**: Jika request dari preview, langsung ke view take dengan data dummy
5. **Buat QuizAttempt baru:**
    ```php
    QuizAttempt::create([
        'quiz_id' => $quiz->id,
        'student_id' => Auth::id(),
        'status' => 'in_progress',
        'start_time' => now()
    ]);
    ```
6. **Redirect ke halaman take**: `student.quiz.take` dengan attemptId

### Model QuizAttempt:

**Tabel:** `quiz_attempts`

**Kolom:**

-   `id`
-   `quiz_id` (foreign key)
-   `student_id` (foreign key)
-   `score` (nullable, diisi saat submit)
-   `status` (enum: 'in_progress', 'passed', 'failed')
-   `start_time` (datetime)
-   `end_time` (datetime, nullable)
-   `created_at`, `updated_at`

---

## 5. HALAMAN TAKE QUIZ - Mengerjakan Soal

### Alur User:

Setelah attempt dibuat, student diarahkan ke halaman pengerjaan quiz.

### Navigasi:

-   URL: `/student/quiz/attempt/{attempt}`
-   Route: `student.quiz.take`
-   Controller: `StudentQuizController@take`

### File yang Terlibat:

-   Controller: `app/Http/Controllers/Student/StudentQuizController.php` (method `take`)
-   View: `resources/views/student/quizzes/take.blade.php`

### Proses di Backend (method `take`):

1. **Load attempt dengan relasi**: quiz, questions, options
2. **Validasi enrollment**: Pastikan student terdaftar
3. **Validasi status**: Jika bukan 'in_progress', redirect ke result
4. **Hitung endTime** (waktu berakhir):
    ```php
    if ($attempt->quiz->time_limit > 0 && $attempt->start_time) {
        $endTime = $attempt->start_time->addMinutes($attempt->quiz->time_limit);
    }
    ```
5. **Kirim data ke view**: attempt, is_preview, endTime (format ISO 8601)

### Tampilan Halaman Take:

**Header:**

-   Judul quiz (kiri)
-   Timer countdown (kanan) → akan countdown dari endTime

**Body:**

-   **Kolom Kiri (col-lg-9)**: Area Soal

    -   Progress bar (menunjukkan % soal yang sudah dijawab)
    -   Konten soal (ditampilkan satu per satu, slide system)
    -   Tombol navigasi:
        -   "Sebelumnya" (muncul jika bukan soal pertama)
        -   "Selanjutnya" (muncul jika bukan soal terakhir)
        -   "Selesaikan Kuis" (muncul di soal terakhir)

-   **Kolom Kanan (col-lg-3)**: Navigasi Soal
    -   Grid tombol angka (1, 2, 3, dst)
    -   Warna tombol:
        -   **Biru (primary)**: Soal yang sedang aktif
        -   **Hijau (success)**: Soal yang sudah dijawab
        -   **Abu-abu (outline-secondary)**: Soal yang belum dijawab

### Tipe Soal yang Didukung:

#### A. Multiple Choice Single (Pilihan Ganda)

```html
<div class="form-check">
    <input
        class="form-check-input"
        type="radio"
        name="answers[{{ $question->id }}]"
        value="{{ $option->id }}"
    />
    <label class="form-check-label">{{ $option->option_text }}</label>
</div>
```

#### B. True/False (Benar/Salah)

-   Sama seperti multiple choice single, tapi hanya 2 opsi

#### C. Multiple Choice Multiple (Pilihan Ganda Kompleks)

```html
<div class="form-check">
    <input
        class="form-check-input"
        type="checkbox"
        name="answers[{{ $question->id }}][]"
        value="{{ $option->id }}"
    />
    <label class="form-check-label">{{ $option->option_text }}</label>
</div>
```

#### D. Drag and Drop (Isi Titik-titik/Menjodohkan)

-   Soal dengan format: `"Ibu kota Indonesia adalah [[BLANK_1]]"`
-   Sistem akan render menjadi dropdown select di posisi blank

```html
<select name="answers[{{ $question->id }}][BLANK_1]" class="form-control">
    <option value="">-- Pilih Jawaban --</option>
    <option value="{{ $option->id }}">{{ $option->option_text }}</option>
</select>
```

### JavaScript Timer:

**File:** `resources/views/student/quizzes/take.blade.php` (bagian @push('scripts'))

**Fungsi Timer:**

1. Ambil `endTime` dari backend (format ISO 8601)
2. Jika ada `time_limit`, hitung mundur setiap detik
3. Jika waktu habis:
    - **allow_exceed_time_limit = false**: Auto submit form + alert
    - **allow_exceed_time_limit = true**: Timer jadi negatif (merah), tapi bisa lanjut mengerjakan
4. Display format: `MM:SS` (contoh: `15:30`)

### JavaScript Navigasi Soal:

1. **Semua soal di-render sekaligus**, tapi disembunyikan (`display: none`)
2. **currentQuestionIndex** melacak soal yang sedang ditampilkan
3. **Tombol Prev/Next**: Ubah index dan panggil `showQuestion(index)`
4. **Tombol navigasi (1,2,3...)**: Langsung lompat ke soal tertentu
5. **Progress Bar**: Dihitung dari jumlah soal yang sudah dijawab / total soal

### Event Listener Form:

```javascript
form.addEventListener("change", function () {
    updateNavigationStatus();
    updateProgressBar();
});
```

Setiap kali input berubah (radio checked, checkbox checked, select changed), update status tombol navigasi dan progress bar.

### Modal Konfirmasi Selesai:

**Modal ID:** `confirmSubmitModal`

**Alur:**

1. User klik "Selesaikan Kuis"
2. Modal muncul dengan pertanyaan: "Apakah Anda yakin ingin menyelesaikan kuis ini?"
3. User klik "Ya, Selesaikan"
4. Form disubmit ke endpoint submit

---

## 6. SUBMIT QUIZ - Mengirim Jawaban

### Alur User:

Student **mengklik "Ya, Selesaikan"** di modal konfirmasi.

### Form Submission:

-   URL: `/student/quiz/attempt/{attempt}/submit`
-   Route: `student.quiz.submit`
-   Method: `POST`
-   Controller: `StudentQuizController@submit`

### Data yang Dikirim:

```
answers[question_id_1] = option_id
answers[question_id_2][] = [option_id_1, option_id_2] // untuk multiple
answers[question_id_3][BLANK_1] = option_id // untuk drag and drop
```

### Proses di Backend (method `submit`):

#### 1. Ambil Data Request

```php
$userAnswers = $request->input('answers', []);
$is_preview = $request->input('is_preview') === 'true';
```

#### 2. Load Attempt & Questions

```php
$attempt = QuizAttempt::findOrFail($attemptId);
$quizQuestions = $attempt->quiz->questions;
```

#### 3. Loop Setiap Soal & Hitung Skor

```php
foreach ($quizQuestions as $question) {
    $userAnswerForQuestion = $userAnswers[$question->id] ?? null;
    $isCorrect = $this->checkAnswer($question, $userAnswerForQuestion);

    if ($isCorrect) {
        $totalScore += $question->score;
    }

    $this->storeStudentAnswer($attempt, $question, $userAnswerForQuestion, $isCorrect);
}
```

#### 4. Method `checkAnswer` - Logika Validasi Jawaban

**A. Multiple Choice Single & True/False:**

```php
$correctOption = $question->options->firstWhere('is_correct', true);
return $correctOption && $correctOption->id == $userAnswer;
```

**B. Multiple Choice Multiple:**

```php
$correctOptions = $question->options->where('is_correct', true)
                                     ->pluck('id')->sort()->values()->toArray();
$userOptions = collect((array)$userAnswer)->map(fn($id) => (int)$id)
                                           ->sort()->values()->toArray();
return $correctOptions == $userOptions;
```

**C. Drag and Drop:**

```php
$correctAnswers = $question->options->whereNotNull('correct_gap_identifier');

foreach ($correctAnswers as $correctAnswer) {
    $blankId = $correctAnswer->correct_gap_identifier;
    if (!isset($userAnswer[$blankId]) || $userAnswer[$blankId] != $correctAnswer->id) {
        return false;
    }
}
return true;
```

#### 5. Simpan Jawaban Student (method `storeStudentAnswer`)

**Tabel:** `student_answers`

**Kolom:**

-   `attempt_id`
-   `question_id`
-   `selected_option_id`
-   `is_correct` (boolean)

**Untuk Multiple/Drag-Drop:**

-   Disimpan multiple rows (satu row per opsi yang dipilih)

#### 6. Hitung Skor Persentase & Status

```php
$maxPossibleScore = $attempt->quiz->questions->sum('score');
$percentageScore = ($maxPossibleScore > 0) ? ($totalScore / $maxPossibleScore) * 100 : 0;
$newStatus = $percentageScore >= $attempt->quiz->pass_mark ? 'passed' : 'failed';
```

#### 7. Update Attempt

```php
$attempt->score = $totalScore;
$attempt->status = $newStatus;
$attempt->end_time = now();
$attempt->save();
```

#### 8. Cek Apakah Melebihi Waktu

```php
$timeLimitInMinutes = $attempt->quiz->time_limit;
$quizExceededTimeLimit = false;

if ($timeLimitInMinutes > 0) {
    $quizExceededTimeLimit = $attempt->end_time > $attempt->start_time->addMinutes($timeLimitInMinutes);
}

$quizAllowExceedTimeLimit = (bool) $attempt->quiz->allow_exceed_time_limit;
```

#### 9. Logika Pemberian Poin (PENTING!)

**Poin diberikan HANYA jika:**

-   Status = 'passed'
-   Belum pernah dapat poin untuk lesson ini sebelumnya (cek `PointHistory`)
-   **TIDAK melebihi batas waktu** (jika time_limit > 0)

```php
$hasEarnedPointsBefore = PointHistory::where('user_id', Auth::id())
    ->where('lesson_id', $attempt->quiz->lesson->id)
    ->exists();

if ($newStatus === 'passed' && !$hasEarnedPointsBefore && !$quizExceededTimeLimit) {
    PointService::addPoints(
        user: Auth::user(),
        course: $attempt->quiz->lesson->module->course,
        activity: 'pass_quiz',
        lesson: $attempt->quiz->lesson,
        description_meta: $attempt->quiz->title
    );

    // Tandai lesson sebagai complete
    if (!$student->completedLessons->contains($attempt->quiz->lesson_id)) {
        $student->completedLessons()->syncWithoutDetaching($lesson->id);
    }
}
```

#### 10. Cek Badge (jika lulus)

```php
if ($newStatus === 'passed') {
    BadgeService::checkQuizCompletionBadges($student);
}
```

#### 11. Redirect ke Result

```php
return redirect()->route('student.quiz.result', $attempt->id);
```

---

## 7. HALAMAN RESULT - Melihat Hasil Quiz

### Alur User:

Setelah submit, student diarahkan ke halaman hasil.

### Navigasi:

-   URL: `/student/quiz/attempt/{attempt}/result`
-   Route: `student.quiz.result`
-   Controller: `StudentQuizController@result`

### File yang Terlibat:

-   Controller: `app/Http/Controllers/Student/StudentQuizController.php` (method `result`)
-   View: `resources/views/student/quizzes/result.blade.php`

### Proses di Backend (method `result`):

1. **Validasi ownership**: Pastikan attempt milik student yang login
2. **Validasi enrollment**: Pastikan student terdaftar di kursus
3. **Validasi status**: Jika masih 'in_progress', redirect ke take
4. **Load relasi**: quiz, questions, options, answers
5. **Hitung data untuk tampilan:**
    ```php
    $maxPossibleScore = $attempt->quiz->questions->sum('score');
    $minimumScore = $maxPossibleScore * ($attempt->quiz->pass_mark / 100);
    $studentScoreScaled = ($maxPossibleScore > 0)
        ? min(100, round(($attempt->score / $maxPossibleScore) * 100, 2))
        : 0;
    $minimumScoreScaled = $attempt->quiz->pass_mark;
    ```

### Tampilan Halaman Result:

**Header:**

-   Judul: "Hasil Kuis: {nama_quiz}"
-   Breadcrumb: Home > Kursus > Hasil Kuis

**Card Hasil Ringkas:**

**A. Status Kelulusan:**

-   **Jika Lulus:**
    -   Teks: "Selamat, Anda Lulus!"
    -   Icon: Check circle hijau (fa-4x)
-   **Jika Gagal:**
    -   Teks: "Sayang sekali, Anda Gagal."
    -   Icon: Times circle merah (fa-4x)

**B. Informasi Skor (4 Card dengan bg-light):**

1. **Skor Anda** (text-primary): Skor mentah yang didapat (contoh: 75)
2. **Skor Minimum** (text-info): Skor minimum untuk lulus (contoh: 60)
3. **Skor Maksimum** (text-info): Total skor maksimal quiz (contoh: 100)
4. **Nilai Anda** (text-primary): Skor dalam skala 0-100 (contoh: 75.00)
5. **Nilai Minimum** (text-info): Passing grade scaled (contoh: 60.00)
6. **Passing Grade** (text-info): Persentase kelulusan (contoh: 60%)

**C. Tombol:**

-   "Kembali ke Kursus" → Redirect ke halaman kursus detail
-   (Mode preview: "Kembali ke Preview Kursus")

---

## 8. RINCIAN JAWABAN (Opsional - Berdasarkan Setting)

### Kondisi Tampil:

Hanya tampil jika `$attempt->quiz->reveal_answers == true`

### Isi Rincian:

**Loop setiap soal:**

#### Header Soal:

-   Nomor soal: "Soal 1:"
-   Teks soal (dengan replacement `[[BLANK_X]]` menjadi `___`)

#### Opsi Jawaban:

Setiap opsi ditampilkan dengan warna background berbeda:

**Warna Background:**

1. **bg-success (hijau)**: Jawaban student DAN benar

    - Icon: `fa-check-circle-o`
    - Label: "Jawaban Anda (Benar)"

2. **bg-danger (merah)**: Jawaban student tapi SALAH

    - Icon: `fa-times-circle-o`
    - Label: "Jawaban Anda (Salah)"

3. **bg-info (biru)**: BUKAN jawaban student, tapi ini kunci jawaban

    - Icon: `fa-check`
    - Label: "Kunci Jawaban"

4. **Tidak berwarna**: Opsi yang tidak dipilih dan bukan kunci jawaban
    - Icon: `fa-circle-o` (opacity 0.5)

#### Penjelasan (Explanation):

Jika soal dijawab **BENAR** dan ada `$question->explanation`:

```html
<div class="alert alert-success mt-3">
    <strong><i class="fa fa-lightbulb-o"></i> Penjelasan:</strong><br />
    {!! nl2br(e($question->explanation)) !!}
</div>
```

### Jika Jawaban Disembunyikan:

```html
<div class="alert alert-info">
    Jawaban Anda telah disembunyikan oleh penyelenggara kursus ini.
</div>
```

---

## 9. KEMBALI KE PREVIEW QUIZ - Melihat Riwayat Attempt

### Alur User:

Setelah melihat hasil, student klik "Kembali ke Kursus" → Klik lagi lesson quiz → Melihat preview dengan riwayat attempt.

### Tabel Riwayat (di partial `_quiz_preview_in_lesson.blade.php`):

**Kolom Tabel:**

1. **No** (urutan)
2. **Waktu Mulai** (format: d M Y, H:i)
3. **Waktu Selesai** (format: d M Y, H:i)
4. **Durasi** (contoh: "15 menit 30 detik")
5. **Skor** (format: rtrim desimal)
6. **Nilai** (skala 0-100 dengan 2 desimal)
7. **Status**:
    - Badge success: Lulus
    - Badge danger: Gagal
    - Badge warning: Sedang Dikerjakan (seharusnya tidak muncul di history)
8. **Aksi**:
    - Button "Lihat Detail" → Route `student.quiz.result` dengan attemptId

**Data untuk setiap attempt:**

```php
foreach ($allAttempts as $attempt) {
    $attempt->studentScoreScaled = ($totalMaxScore > 0)
        ? min(100, round(($attempt->score / $totalMaxScore) * 100, 2))
        : 0;
}
```

---

## 10. FITUR TAMBAHAN & EDGE CASES

### A. Mode Preview (untuk Instructor/Admin)

**Ciri-ciri:**

-   Query parameter `?preview=true` di URL
-   Banner kuning: "Mode Pratinjau - Hasil tidak disimpan"
-   Input hidden: `<input type="hidden" name="is_preview" value="true">`
-   Tidak membuat `QuizAttempt` di database
-   Tidak simpan `StudentAnswer`
-   Tidak beri poin
-   Langsung tampilkan hasil setelah submit

### B. Modul Terkunci (Points Required)

**Logika di `CourseController@getContent`:**

```php
if ($module->points_required > 0 && !$request->query('preview')) {
    $userPoints = $courseUserPivot->pivot->points_earned ?? 0;

    if ($userPoints < $module->points_required) {
        return response()->json([
            'success' => true,
            'title' => 'Modul Terkunci',
            'html' => view('student.courses.partials._locked_content', ['module' => $module])->render(),
            'is_locked' => true,
        ]);
    }
}
```

**Tampilan:**

-   Icon lock
-   Pesan: "Modul ini memerlukan {X} poin untuk dibuka"

### C. Quiz dengan Jadwal (Available From/To)

**Validasi di `StudentQuizController@start`:**

```php
$now = Carbon::now();
$isAvailable = true;

if ($quiz->available_from && $now->isBefore($quiz->available_from)) {
    $isAvailable = false;
    $availabilityMessage = 'Kuis ini akan tersedia pada ' . $quiz->available_from->format('d F Y, H:i');
}

if ($quiz->available_to && $now->isAfter($quiz->available_to)) {
    $isAvailable = false;
    $availabilityMessage = 'Waktu pengerjaan kuis ini telah berakhir pada ' . $quiz->available_to->format('d F Y, H:i');
}
```

### D. Attempt In Progress (Lanjut Mengerjakan)

**Cek di `begin` dan `start`:**

```php
$existingAttempt = QuizAttempt::where('quiz_id', $quiz->id)
    ->where('student_id', Auth::id())
    ->where('status', 'in_progress')
    ->first();

if ($existingAttempt) {
    return redirect()->route('student.quiz.take', $existingAttempt->id);
}
```

### E. Prevent Caching (Middleware)

**Route:**

```php
Route::get('/student/quiz/attempt/{attempt}', [StudentQuizController::class, 'take'])
    ->middleware('prevent.caching')
    ->name('student.quiz.take');
```

**Tujuan:**

-   Mencegah browser cache halaman quiz
-   Memastikan timer dan data selalu fresh

---

## 11. ALUR LENGKAP DIAGRAM

```
[Halaman Kursus]
    → Klik Lesson Quiz di Sidebar
    ↓
[AJAX Load Preview Quiz] (route: student.lessons.content)
    → Tampil: Info quiz, riwayat attempt, tombol "Mulai Kuis"/"Coba Lagi"
    ↓
[Klik "Mulai Kuis"]
    ↓
[Halaman Start Quiz] (route: student.quiz.start)
    → Tampil: Konfirmasi info quiz, kesempatan, tombol "Mulai Kuis"
    ↓
[POST: Begin Quiz] (route: student.quiz.begin)
    → Validasi enrollment, attempt count
    → Buat QuizAttempt baru (status: in_progress, start_time: now())
    → Redirect ke Take Quiz
    ↓
[Halaman Take Quiz] (route: student.quiz.take)
    → Load soal-soal dengan opsi
    → Tampil slide per slide dengan navigasi
    → Timer countdown (jika ada time_limit)
    → Student menjawab soal
    ↓
[Klik "Selesaikan Kuis"]
    → Modal konfirmasi muncul
    ↓
[Klik "Ya, Selesaikan"]
    ↓
[POST: Submit Quiz] (route: student.quiz.submit)
    → Loop setiap soal:
        - Cek jawaban student (checkAnswer)
        - Hitung skor
        - Simpan jawaban ke student_answers
    → Hitung persentase skor
    → Tentukan status (passed/failed)
    → Update attempt (score, status, end_time)
    → Cek apakah melebihi waktu
    → JIKA lulus DAN tidak melebihi waktu DAN belum pernah dapat poin:
        - Beri poin via PointService
        - Tandai lesson complete
        - Cek badge
    → Redirect ke Result
    ↓
[Halaman Result] (route: student.quiz.result)
    → Tampil status (Lulus/Gagal)
    → Tampil skor & nilai
    → Tampil rincian jawaban (jika reveal_answers = true)
    → Tombol "Kembali ke Kursus"
    ↓
[Klik "Kembali ke Kursus"]
    → Kembali ke halaman kursus detail
    → (Student bisa klik quiz lagi untuk melihat riwayat atau coba lagi)
```

---

## 12. DATABASE SCHEMA TERKAIT QUIZ

### Tabel: `quizzes`

```
- id
- title (string)
- description (text, nullable)
- pass_mark (integer, default 60) // dalam persen 0-100
- time_limit (integer, nullable) // dalam menit
- allow_exceed_time_limit (boolean, default false)
- reveal_answers (boolean, default false)
- max_attempts (integer, nullable) // null = unlimited
- available_from (datetime, nullable)
- available_to (datetime, nullable)
- created_at, updated_at
```

### Tabel: `lessons`

```
- id
- module_id (foreign key)
- title (string)
- order (integer)
- lessonable_type (string) // contoh: App\Models\Quiz
- lessonable_id (integer)
- created_at, updated_at
```

### Tabel: `quiz_attempts`

```
- id
- quiz_id (foreign key)
- student_id (foreign key) // ke users.id
- score (decimal, nullable) // skor mentah
- status (enum: 'in_progress', 'passed', 'failed')
- start_time (datetime)
- end_time (datetime, nullable)
- created_at, updated_at
```

### Tabel: `questions`

```
- id
- question_text (text)
- question_type (enum: 'multiple_choice_single', 'multiple_choice_multiple', 'true_false', 'drag_and_drop')
- score (integer, default 1)
- explanation (text, nullable)
- created_at, updated_at
```

### Tabel: `quiz_question` (pivot)

```
- quiz_id (foreign key)
- question_id (foreign key)
- order (integer)
```

### Tabel: `question_options`

```
- id
- question_id (foreign key)
- option_text (string)
- is_correct (boolean, default false)
- correct_gap_identifier (string, nullable) // untuk drag_and_drop, contoh: "BLANK_1"
- created_at, updated_at
```

### Tabel: `student_answers`

```
- id
- attempt_id (foreign key) // ke quiz_attempts.id
- question_id (foreign key)
- selected_option_id (foreign key) // ke question_options.id
- is_correct (boolean)
- created_at, updated_at
```

### Tabel: `lesson_user` (pivot - lesson completion)

```
- lesson_id (foreign key)
- user_id (foreign key)
- created_at, updated_at
```

### Tabel: `point_histories`

```
- id
- user_id (foreign key)
- course_id (foreign key)
- lesson_id (foreign key, nullable)
- activity (string) // contoh: 'pass_quiz'
- points (integer)
- description (string)
- created_at, updated_at
```

---

## 13. ROUTES RINGKASAN

```php
// Melihat preview quiz di dalam lesson (AJAX)
Route::get('/student/lessons/{lesson}/content', [CourseController::class, 'getContent'])
    ->name('student.lessons.content');

// Halaman start quiz (konfirmasi sebelum mulai)
Route::get('/student/quiz/{quiz}/start', [StudentQuizController::class, 'start'])
    ->name('student.quiz.start');

// Begin quiz (membuat attempt baru)
Route::post('/student/quiz/{quiz}/begin', [StudentQuizController::class, 'begin'])
    ->name('student.quiz.begin');

// Halaman take quiz (mengerjakan soal)
Route::get('/student/quiz/attempt/{attempt}', [StudentQuizController::class, 'take'])
    ->middleware('prevent.caching')
    ->name('student.quiz.take');

// Submit quiz (mengirim jawaban)
Route::post('/student/quiz/attempt/{attempt}/submit', [StudentQuizController::class, 'submit'])
    ->name('student.quiz.submit');

// Halaman result (melihat hasil)
Route::get('/student/quiz/attempt/{attempt}/result', [StudentQuizController::class, 'result'])
    ->name('student.quiz.result');

// Cek jawaban via AJAX (opsional, untuk fitur hint/feedback)
Route::post('/student/quiz/check-answer', [StudentQuizController::class, 'checkAnswerAjax'])
    ->name('student.quiz.check_answer');
```

---

## 14. POIN PENTING UNTUK PENGEMBANGAN SELANJUTNYA

### A. Sistem Poin

-   Poin **HANYA** diberikan jika:
    1. Quiz lulus (status = 'passed')
    2. Belum pernah dapat poin untuk lesson ini (cek `PointHistory`)
    3. Tidak melebihi batas waktu (jika `time_limit > 0`)
-   Activity: `'pass_quiz'`
-   Points ditentukan di `PointService`

### B. Lesson Completion

-   Quiz otomatis ditandai complete jika lulus dan dapat poin
-   Menggunakan pivot table `lesson_user`
-   Sync menggunakan `syncWithoutDetaching` untuk mencegah duplikat

### C. Multiple Attempts

-   Student bisa coba lagi (jika masih ada kesempatan)
-   Riwayat semua attempt tersimpan
-   Hanya attempt pertama yang lulus (dan tidak melebihi waktu) yang dapat poin

### D. Timer & Exceed Time Limit

-   **allow_exceed_time_limit = false**: Auto submit saat timer habis
-   **allow_exceed_time_limit = true**: Bisa lanjut, tapi tidak dapat poin jika melebihi
-   Timer menggunakan JavaScript countdown dari `endTime` (ISO 8601)

### E. Security & Validation

-   Semua method validasi enrollment student
-   Validasi ownership attempt (hanya student yang buat bisa akses)
-   Validasi status attempt (in_progress, passed, failed)
-   Prevent caching pada halaman take quiz

---

## 15. FILE-FILE PENTING (REFERENSI CEPAT)

### Controllers:

-   `app/Http/Controllers/Student/CourseController.php`
    -   Method: `show`, `getContent`, `markAsComplete`
-   `app/Http/Controllers/Student/StudentQuizController.php`
    -   Method: `start`, `begin`, `take`, `submit`, `result`, `checkAnswerAjax`

### Models:

-   `app/Models/Quiz.php`
-   `app/Models/QuizAttempt.php`
-   `app/Models/Question.php`
-   `app/Models/QuestionOption.php` (jika ada)
-   `app/Models/StudentAnswer.php` (jika ada)
-   `app/Models/Lesson.php`
-   `app/Models/PointHistory.php`

### Views:

-   `resources/views/student/courses/show.blade.php` (halaman utama kursus)
-   `resources/views/student/quizzes/partials/_quiz_preview_in_lesson.blade.php` (preview quiz di lesson)
-   `resources/views/student/quizzes/start.blade.php` (halaman start quiz)
-   `resources/views/student/quizzes/take.blade.php` (halaman mengerjakan quiz)
-   `resources/views/student/quizzes/result.blade.php` (halaman hasil quiz)

### Services:

-   `app/Services/PointService.php` (untuk pemberian poin)
-   `app/Services/BadgeService.php` (untuk cek perolehan badge)

### Routes:

-   `routes/web.php` (baris 26, 156-171)

---

## 16. KESIMPULAN

Sistem kuis di LMS2025 sudah cukup komprehensif dengan fitur-fitur:

-   ✅ Multiple attempt dengan batas kesempatan
-   ✅ Timer countdown dengan opsi melebihi waktu
-   ✅ Berbagai tipe soal (single choice, multiple choice, true/false, drag and drop)
-   ✅ Sistem poin yang ketat (hanya lulus + tidak melebihi waktu)
-   ✅ Riwayat semua attempt
-   ✅ Reveal/hide jawaban
-   ✅ Jadwal ketersediaan quiz
-   ✅ Mode preview untuk instructor/admin
-   ✅ Validasi enrollment dan security
-   ✅ Lesson completion tracking
-   ✅ Badge system integration

Sistem ini siap untuk dikembangkan lebih lanjut dengan fitur tambahan seperti:

-   Export hasil quiz ke Excel/PDF
-   Statistik per soal (tingkat kesulitan, persentase yang menjawab benar)
-   Bank soal dengan random selection
-   Question tagging dan filtering
-   Review attempt sebelumnya dengan detail per soal
-   dll.

---

**Dokumen ini dibuat pada:** 1 Desember 2025
**Versi:** 1.0
**Status Sistem:** Production-ready
