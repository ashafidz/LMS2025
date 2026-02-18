# DOKUMENTASI ALUR REVIEW HASIL KUIS - LMS2025

## Deskripsi Umum

Dokumen ini menjelaskan secara detail dua alur utama terkait review hasil kuis:

1. **Alur Student Melihat Hasil Kuis dari Riwayat**
2. **Alur Instructor Melihat Hasil Kuis Seorang Student**

Dokumentasi ini melengkapi dokumentasi utama sistem kuis dan ditujukan untuk membantu pengembangan lebih lanjut.

---

# BAGIAN 1: ALUR STUDENT MELIHAT HASIL KUIS DARI RIWAYAT

## 1. OVERVIEW ALUR STUDENT

Student dapat melihat hasil kuis mereka melalui 2 cara:

1. **Langsung setelah submit quiz** → Redirect otomatis ke halaman result
2. **Dari riwayat attempt di preview quiz** → Klik tombol "Lihat" di tabel riwayat

---

## 2. CARA 1: Melihat Hasil Langsung Setelah Submit

### Alur:

```
[Submit Quiz]
    → POST ke student.quiz.submit
    → Proses scoring & validasi
    → Redirect ke student.quiz.result dengan attemptId
    ↓
[Halaman Result]
```

### Detail Proses:

Sudah dijelaskan lengkap di dokumentasi utama (DOKUMENTASI_ALUR_SISTEM_KUIS.md) pada Section 7.

---

## 3. CARA 2: Melihat Hasil dari Riwayat Attempt (FOKUS DOKUMENTASI INI)

### 3.1. Halaman Preview Quiz dengan Riwayat

#### Lokasi:

Student berada di **halaman kursus** → Klik **lesson Quiz** dari sidebar → Muncul preview quiz.

#### File yang Terlibat:

-   **View Preview Quiz**: `resources/views/student/quizzes/partials/_quiz_preview_in_lesson.blade.php`
-   **View Tabel Riwayat**: `resources/views/student/quizzes/partials/_quiz_attempt_history.blade.php`
-   **Controller**: `app/Http/Controllers/Student/CourseController.php` (method `getContent`)

#### AJAX Request untuk Load Preview:

```javascript
// Dari show.blade.php
fetch("/student/lessons/{lesson}/content")
    .then((response) => response.json())
    .then((data) => {
        lessonContentEl.innerHTML = data.html; // Render preview quiz
    });
```

### 3.2. Data yang Dipersiapkan di Backend (method `getContent`)

**Controller:** `CourseController@getContent`

**Logika untuk Quiz:**

```php
if ($lessonType === 'quiz') {
    $quiz = $lesson->lessonable;
    $quiz->load('questions');

    // Hitung skor maksimal dan minimum
    $data['maxScore'] = $quiz->questions->sum('score');
    $data['minimumScore'] = $data['maxScore'] * ($quiz->pass_mark / 100);
    $data['minimumScoreScaled'] = $quiz->pass_mark;

    // PENTING: Ambil SEMUA riwayat attempt student untuk quiz ini
    if ($user && !$is_preview_for_view) {
        $allAttempts = $quiz->attempts()
            ->where('student_id', $user->id)
            ->orderBy('created_at', 'desc') // Urutkan dari terbaru
            ->get();

        $totalMaxScore = $data['maxScore'];

        // Hitung nilai scaled untuk setiap attempt
        foreach ($allAttempts as $attempt) {
            $attempt->studentScoreScaled = ($totalMaxScore > 0)
                ? min(100, round(($attempt->score / $totalMaxScore) * 100, 2))
                : 0;
        }

        $data['allAttempts'] = $allAttempts;
        $data['attemptCount'] = $allAttempts->count();
        $data['lastAttempt'] = $allAttempts->first(); // Yang terbaru
    }
}
```

**Variabel yang Dikirim ke View:**

-   `$allAttempts` → Semua percobaan quiz student (sorted desc by created_at)
-   `$attemptCount` → Jumlah total percobaan
-   `$lastAttempt` → Attempt terakhir (terbaru)
-   Setiap attempt sudah dilengkapi dengan `studentScoreScaled` (nilai 0-100)

### 3.3. Tampilan Tabel Riwayat Attempt

**File:** `resources/views/student/quizzes/partials/_quiz_attempt_history.blade.php`

**Kondisi Tampil:**

```blade
@if (!$is_preview && isset($allAttempts) && $allAttempts->isNotEmpty())
    @include('student.quizzes.partials._quiz_attempt_history', ['allAttempts' => $allAttempts])
@endif
```

Tabel riwayat **HANYA muncul** jika:

-   Bukan mode preview
-   Ada data `$allAttempts`
-   Student pernah mengerjakan quiz (allAttempts tidak kosong)

### 3.4. Struktur Tabel Riwayat

**Kolom-kolom Tabel:**

| Kolom               | Deskripsi                     | Format/Logic                                                               |
| ------------------- | ----------------------------- | -------------------------------------------------------------------------- |
| **#**               | Nomor urut                    | `$loop->iteration`                                                         |
| **Tanggal & Waktu** | Kapan quiz dimulai            | `$attempt->start_time->isoFormat('D MMM YYYY, HH:mm')`                     |
| **Skor**            | Skor mentah yang didapat      | `rtrim(rtrim($attempt->score, '0'), '.')`                                  |
| **Nilai**           | Nilai dalam skala 0-100       | `$attempt->studentScoreScaled` (sudah dihitung di backend)                 |
| **Status**          | Lulus/Gagal/Sedang Dikerjakan | Badge dengan warna: success (lulus), danger (gagal), warning (in_progress) |
| **Durasi**          | Lama mengerjakan              | `$attempt->start_time->diffForHumans($attempt->end_time, true)`            |
| **Aksi**            | Tombol untuk melihat detail   | Button "Lihat" atau "Lanjutkan"                                            |

#### Detail Kolom Status:

```blade
@if ($attempt->status == 'passed')
    <span class="badge badge-success">Lulus</span>
@elseif($attempt->status == 'failed')
    <span class="badge badge-danger">Gagal</span>
@else
    <span class="badge badge-warning">Sedang Dikerjakan</span>
@endif
```

#### Detail Kolom Aksi:

```blade
@if ($attempt->status != 'in_progress')
    {{-- Jika sudah selesai (passed/failed), tampilkan tombol Lihat --}}
    <a href="{{ route('student.quiz.result', $attempt->id) }}" class="btn btn-sm btn-outline-info">
        <i class="fa fa-eye"></i> Lihat
    </a>
@else
    {{-- Jika masih in_progress, tampilkan tombol Lanjutkan --}}
    <a href="{{ route('student.quiz.take', $attempt->id) }}" class="btn btn-sm btn-outline-warning">
        <i class="fa fa-pencil"></i> Lanjutkan
    </a>
@endif
```

**Logika Penting:**

-   Jika status = `in_progress`: Tombol "Lanjutkan" → Route `student.quiz.take`
-   Jika status = `passed` atau `failed`: Tombol "Lihat" → Route `student.quiz.result`

### 3.5. Navigasi Saat Klik "Lihat Detail"

#### Alur User:

Student **klik tombol "Lihat"** di salah satu baris riwayat.

#### Proses:

-   URL: `/student/quiz/attempt/{attempt}/result`
-   Route: `student.quiz.result`
-   Method: `GET`
-   Controller: `StudentQuizController@result`

#### Validasi di Backend:

```php
public function result($attemptId)
{
    $attempt = QuizAttempt::findOrFail($attemptId);

    // Validasi: Pastikan student terdaftar di kursus
    $student = Auth::user();
    $course = $attempt->quiz->lesson->module->course;
    if (session('active_role') === 'student') {
        if (!$student->enrollments()->where('courses.id', $course->id)->exists()) {
            abort(403, 'Anda tidak terdaftar di kursus ini.');
        }
    }

    // Validasi ownership: Pastikan attempt milik student yang login
    if ($attempt->student_id != Auth::id()) {
        abort(403);
    }

    // Validasi status: Jika masih in_progress, redirect ke take
    if ($attempt->status === 'in_progress') {
        return redirect()->route('student.quiz.take', $attempt)
            ->with('error', 'Anda harus menyelesaikan kuis terlebih dahulu.');
    }

    // Load relasi dan data
    $attempt->load(['quiz.questions.options', 'answers']);

    // Hitung skor dan nilai
    $maxPossibleScore = $attempt->quiz->questions->sum('score');
    $minimumScore = $maxPossibleScore * ($attempt->quiz->pass_mark / 100);
    $studentScoreScaled = ($maxPossibleScore > 0)
        ? min(100, round(($attempt->score / $maxPossibleScore) * 100, 2))
        : 0;
    $minimumScoreScaled = $attempt->quiz->pass_mark;

    return view('student.quizzes.result', [
        'attempt' => $attempt,
        'is_preview' => false,
        'maxPossibleScore' => $maxPossibleScore,
        'minimumScore' => $minimumScore,
        'studentScoreScaled' => $studentScoreScaled,
        'minimumScoreScaled' => $minimumScoreScaled
    ]);
}
```

### 3.6. Halaman Result (Detail Hasil Quiz)

**File:** `resources/views/student/quizzes/result.blade.php`

Detail lengkap halaman result sudah dijelaskan di dokumentasi utama (Section 7 & 8).

**Yang ditampilkan:**

1. Status kelulusan (Lulus/Gagal)
2. Informasi skor (Skor Anda, Skor Minimum, Skor Maksimum)
3. Informasi nilai (Nilai Anda, Nilai Minimum, Passing Grade)
4. Rincian jawaban per soal (jika `reveal_answers = true`)
5. Tombol "Kembali ke Kursus"

---

## 4. FITUR TAMBAHAN: Lanjutkan Quiz yang Belum Selesai

### Alur:

Jika student memiliki attempt dengan status `in_progress` (belum submit), maka:

-   Di tabel riwayat akan muncul tombol **"Lanjutkan"** (warna warning)
-   Klik tombol tersebut → Redirect ke `student.quiz.take` dengan attemptId
-   Student dapat melanjutkan mengerjakan quiz dari terakhir kali

### Cek di method `start`:

```php
public function start(Request $request, Quiz $quiz)
{
    // ... validasi lainnya ...

    // Cari attempt yang masih in_progress
    $existingAttempt = QuizAttempt::where('quiz_id', $quiz->id)
        ->where('student_id', Auth::id())
        ->where('status', 'in_progress')
        ->first();

    if ($existingAttempt) {
        // Langsung redirect ke halaman take
        return redirect()->route('student.quiz.take', $existingAttempt->id);
    }

    // ... lanjutkan dengan start baru ...
}
```

---

## 5. DIAGRAM ALUR LENGKAP - STUDENT MELIHAT HASIL

```
[Halaman Kursus]
    → Student klik Lesson Quiz di sidebar
    ↓
[AJAX Load Preview Quiz] (route: student.lessons.content)
    → Backend: Query allAttempts untuk student ini
    → Backend: Hitung studentScoreScaled untuk setiap attempt
    → Kirim data ke view
    ↓
[Preview Quiz Muncul]
    → Info quiz (judul, deskripsi, passing grade, dll)
    → Tombol "Mulai Kuis" / "Coba Lagi"
    → **TABEL RIWAYAT ATTEMPT** (jika pernah mengerjakan)
        - Kolom: #, Tanggal, Skor, Nilai, Status, Durasi, Aksi
        - Tombol per baris: "Lihat" atau "Lanjutkan"
    ↓
[Student Klik Tombol "Lihat" di Salah Satu Baris]
    ↓
[GET: student.quiz.result dengan attemptId]
    → Validasi enrollment
    → Validasi ownership (attempt milik student yang login)
    → Validasi status (jika in_progress, redirect ke take)
    → Load data attempt dengan relasi (quiz, questions, options, answers)
    → Hitung skor dan nilai
    ↓
[Halaman Result Detail]
    → Status: Lulus/Gagal
    → Card info skor (Skor Anda, Min, Max)
    → Card info nilai (Nilai Anda, Min, Passing Grade)
    → Rincian jawaban per soal (jika reveal_answers = true)
        - Opsi dengan warna:
          * Hijau: Jawaban student & benar
          * Merah: Jawaban student & salah
          * Biru: Bukan jawaban student tapi kunci jawaban
          * Abu-abu: Opsi lain
        - Penjelasan (jika ada & jawaban benar)
    → Tombol "Kembali ke Kursus"
```

---

# BAGIAN 2: ALUR INSTRUCTOR MELIHAT HASIL KUIS STUDENT

## 1. OVERVIEW ALUR INSTRUCTOR

Instructor dapat melihat hasil kuis semua student melalui fitur "Lihat Nilai" yang terdapat di:

1. **Halaman Daftar Lesson** → Button "Lihat Nilai" untuk setiap quiz lesson
2. **Langsung akses URL** → `/instructor/quizzes/{quiz}/results`

---

## 2. ENTRY POINT: Halaman Daftar Lesson (Instructor)

### Lokasi:

**Dashboard Instructor** → **Kelola Kursus** → **Pilih Kursus** → **Pilih Modul** → **Daftar Pelajaran**

### File:

-   View: `resources/views/instructor/lessons/index.blade.php`

### Tombol "Lihat Nilai":

Setiap lesson bertipe **Quiz** memiliki tombol:

```blade
<a href="{{ route('instructor.quiz.results', $lesson->lessonable_id) }}" class="btn btn-info btn-sm">
    <i class="fa fa-calculator me-1"></i>Lihat Nilai
</a>
```

**Route:** `instructor.quiz.results`

---

## 3. HALAMAN HASIL KUIS - Daftar Semua Student

### 3.1. Navigasi

#### Alur User:

Instructor **klik tombol "Lihat Nilai"** di lesson quiz.

#### Proses Backend:

-   URL: `/instructor/quizzes/{quiz}/results`
-   Route: `instructor.quiz.results`
-   Method: `GET`
-   Controller: `InstructorQuizController@showResults`

### 3.2. File yang Terlibat

**Controller:** `app/Http/Controllers/Instructor/InstructorQuizController.php`
**View:** `resources/views/instructor/quizzes/results/index.blade.php`

### 3.3. Proses di Backend (method `showResults`)

```php
public function showResults(Quiz $quiz)
{
    // 1. OTORISASI: Pastikan instructor adalah pemilik kursus
    if ($quiz->lesson->module->course->instructor_id != Auth::id()) {
        abort(403);
    }

    // 2. LOAD QUESTIONS untuk hitung total skor
    $quiz->load('questions');
    $totalMaxScore = $quiz->questions->sum('score');
    $minimumScore = ($quiz->pass_mark / 100) * $totalMaxScore;
    $minimumScoreScaled = $quiz->pass_mark; // Sudah dalam bentuk persentase 0-100

    // 3. AMBIL KURSUS terkait
    $course = $quiz->lesson->module->course;

    // 4. AMBIL SEMUA STUDENT yang terdaftar di kursus
    // Urutkan berdasarkan unique_id_number (NIM/NIP/NIDN)
    $enrolledStudents = $course->students()
        ->join('student_profiles', 'users.id', '=', 'student_profiles.user_id')
        ->orderByRaw('
            CASE
                WHEN student_profiles.unique_id_number IS NULL THEN 1
                WHEN student_profiles.unique_id_number = "" THEN 1
                ELSE 0
            END ASC,
            CAST(student_profiles.unique_id_number AS UNSIGNED) ASC
        ')
        ->select('users.*')
        ->get(); // Ambil semua (tidak pakai pagination)

    // 5. AMBIL SEMUA ATTEMPT untuk semua student
    $studentIds = $enrolledStudents->pluck('id');
    $attempts = QuizAttempt::where('quiz_id', $quiz->id)
        ->whereIn('student_id', $studentIds)
        ->get()
        ->groupBy('student_id'); // Kelompokkan per student

    // 6. PROSES DATA: Tentukan status akhir setiap student
    foreach ($enrolledStudents as $student) {
        $studentAttempts = $attempts->get($student->id);

        if ($studentAttempts) {
            // Cek apakah ada percobaan yang lulus
            $hasPassed = $studentAttempts->contains('status', 'passed');
            $student->quiz_status = $hasPassed ? 'Lulus' : 'Gagal';
            $student->attempts = $studentAttempts;
        } else {
            // Belum pernah mengerjakan
            $student->quiz_status = 'Belum Mengerjakan';
            $student->attempts = collect(); // Koleksi kosong
        }
    }

    return view('instructor.quizzes.results.index', compact(
        'quiz',
        'enrolledStudents',
        'minimumScore',
        'totalMaxScore',
        'minimumScoreScaled'
    ));
}
```

### 3.4. Tampilan Halaman Results Index

**File:** `resources/views/instructor/quizzes/results/index.blade.php`

#### Header:

-   **Judul:** "Hasil Kuis"
-   **Sub-judul:** "Judul Kuis: {nama_quiz}"
-   **Breadcrumb:** Home > Kursus Saya > Modul Saya > Daftar Pelajaran > Hasil Kuis

#### Card Utama:

**Judul Card:** "Riwayat Pengerjaan Siswa"
**Deskripsi:** "Tabel ini menampilkan status pengerjaan kuis untuk semua siswa yang terdaftar di kursus ini."

#### Tabel Daftar Student:

| Kolom                 | Deskripsi                             | Data                                                                           |
| --------------------- | ------------------------------------- | ------------------------------------------------------------------------------ |
| **NIM/NIP/NIDN**      | Nomor identifikasi unik student       | `$student->studentProfile->unique_id_number` atau `-`                          |
| **Nama Siswa**        | Nama lengkap student (link ke profil) | `<a href="{{ route('profile.show', $student->id) }}">{{ $student->name }}</a>` |
| **Status Pengerjaan** | Status akhir quiz student             | Label badge: "Lulus" (hijau), "Gagal" (merah), "Belum Mengerjakan" (abu-abu)   |
| **Aksi**              | Tombol untuk melihat riwayat detail   | Button "Lihat Riwayat" (jika pernah mengerjakan)                               |

#### Detail Kolom Status:

```blade
@php
    $statusClass = '';
    if ($student->quiz_status === 'Lulus') $statusClass = 'label-success';
    elseif ($student->quiz_status === 'Gagal') $statusClass = 'label-danger';
    else $statusClass = 'label-default';
@endphp
<label class="label {{ $statusClass }}">{{ $student->quiz_status }}</label>
```

#### Detail Kolom Aksi:

```blade
@if($student->attempts->isNotEmpty())
    <button type="button" class="btn btn-primary btn-sm"
            data-toggle="modal" data-target="#historyModal-{{ $student->id }}">
        Lihat Riwayat
    </button>
@else
    <button class="btn btn-secondary btn-sm" disabled>Lihat Riwayat</button>
@endif
```

**Logika:**

-   Jika student pernah mengerjakan (`$student->attempts->isNotEmpty()`): Button aktif
-   Jika belum pernah: Button disabled

---

## 4. MODAL RIWAYAT PENGERJAAN PER STUDENT

### 4.1. Trigger Modal

#### Alur User:

Instructor **klik tombol "Lihat Riwayat"** di baris student.

#### Proses:

Modal dengan ID `historyModal-{{ $student->id }}` akan muncul.

### 4.2. Struktur Modal

**Modal dibuat untuk SETIAP student** yang pernah mengerjakan quiz:

```blade
@foreach ($enrolledStudents as $student)
    @if($student->attempts->isNotEmpty())
        <div class="modal fade" id="historyModal-{{ $student->id }}" tabindex="-1" role="dialog">
            <!-- Isi modal -->
        </div>
    @endif
@endforeach
```

#### Header Modal:

```blade
<div class="modal-header">
    <h5 class="modal-title">Riwayat Pengerjaan: {{ $student->name }}</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
```

### 4.3. Tabel Riwayat di Dalam Modal

**Kolom-kolom:**

| Kolom             | Deskripsi                           | Format                                                                        |
| ----------------- | ----------------------------------- | ----------------------------------------------------------------------------- |
| **Waktu Selesai** | Kapan quiz selesai dikerjakan       | `$attempt->end_time->format('d M Y, H:i')` atau "Dalam Pengerjaan"            |
| **Skor**          | Skor mentah + skor minimum          | **Skor student** (bold)<br>Min: {minimumScore} (kecil, muted)                 |
| **Nilai**         | Nilai scaled 0-100 + nilai minimum  | **Nilai student** (bold, primary)<br>Min: {minimumScoreScaled} (kecil, muted) |
| **Status**        | Lulus/Gagal                         | Badge success (Lulus) atau danger (Gagal)                                     |
| **Aksi**          | Tombol untuk periksa jawaban detail | Button "Periksa Jawaban"                                                      |

#### Detail Kolom Skor:

```blade
<td>
    <strong>{{ rtrim(rtrim(number_format($attempt->score, 2, ',', '.'), '0'), ',') }}</strong>
    <br>
    <small class="text-muted">Min: {{ rtrim(rtrim(number_format($minimumScore, 2, ',', '.'), '0'), ',') }}</small>
</td>
```

#### Detail Kolom Nilai:

```blade
@php
    // Hitung nilai student dalam skala 0-100
    $studentScoreScaled = ($totalMaxScore > 0)
        ? min(100, round(($attempt->score / $totalMaxScore) * 100, 2))
        : 0;
@endphp
<td>
    <strong>{{ rtrim(rtrim(number_format($studentScoreScaled, 2, ',', '.'), '0'), ',') }}</strong>
    <br>
    <small class="text-muted">Min: {{ rtrim(rtrim(number_format($minimumScoreScaled, 2, ',', '.'), '0'), ',') }}</small>
</td>
```

#### Detail Kolom Aksi:

```blade
<td class="text-center">
    <a href="{{ route('instructor.quiz.review_attempt', $attempt->id) }}"
       class="btn btn-inverse btn-sm">
        Periksa Jawaban
    </a>
</td>
```

**Route:** `instructor.quiz.review_attempt`

### 4.4. Footer Modal:

```blade
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
</div>
```

---

## 5. HALAMAN PERIKSA JAWABAN DETAIL

### 5.1. Navigasi

#### Alur User:

Instructor **klik tombol "Periksa Jawaban"** di salah satu attempt dalam modal.

#### Proses Backend:

-   URL: `/instructor/quiz-attempts/{attempt}/review`
-   Route: `instructor.quiz.review_attempt`
-   Method: `GET`
-   Controller: `InstructorQuizController@reviewAttempt`

### 5.2. File yang Terlibat

**Controller:** `app/Http/Controllers/Instructor/InstructorQuizController.php` (method `reviewAttempt`)
**View:** `resources/views/instructor/quizzes/results/show.blade.php`

### 5.3. Proses di Backend (method `reviewAttempt`)

```php
public function reviewAttempt(QuizAttempt $attempt)
{
    // 1. OTORISASI: Pastikan instructor adalah pemilik kursus
    if ($attempt->quiz->lesson->module->course->instructor_id != Auth::id()) {
        abort(403);
    }

    // 2. EAGER LOAD semua relasi yang dibutuhkan
    $attempt->load([
        'quiz.questions.options',
        'answers',
        'student'
    ]);

    // 3. HITUNG SKOR & NILAI
    $quiz = $attempt->quiz;
    $quiz->load('questions');
    $totalMaxScore = $quiz->questions->sum('score');
    $minimumScore = ($quiz->pass_mark / 100) * $totalMaxScore;

    // Hitung nilai student dalam skala 0-100
    $studentScoreScaled = ($totalMaxScore > 0)
        ? min(100, round(($attempt->score / $totalMaxScore) * 100, 2))
        : 0;
    $minimumScoreScaled = $quiz->pass_mark;

    return view('instructor.quizzes.results.show', compact(
        'attempt',
        'totalMaxScore',
        'minimumScore',
        'studentScoreScaled',
        'minimumScoreScaled'
    ));
}
```

### 5.4. Tampilan Halaman Review Attempt

**File:** `resources/views/instructor/quizzes/results/show.blade.php`

#### Header:

-   **Judul:** "Periksa Jawaban Kuis"
-   **Sub-judul:** "Siswa: {nama_student} | Kuis: {nama_quiz}"
-   **Breadcrumb:** Home > Hasil Kuis > Periksa

#### Section 1: Kartu Hasil Ringkas

**Status Kelulusan:**

```blade
@if($attempt->status == 'passed')
    <h4 class="text-success">Status: Lulus</h4>
@else
    <h4 class="text-danger">Status: Gagal</h4>
@endif
```

**Informasi Skor (3 Card):**

1. **Skor Student** (text-primary): Skor mentah yang didapat
2. **Skor Minimum** (text-warning): Skor minimum untuk lulus
3. **Skor Maksimum** (text-info): Total skor maksimal quiz

**Informasi Nilai (3 Card):**

1. **Nilai Student** (text-primary): Nilai scaled 0-100
2. **Nilai Minimum** (text-warning): Passing grade scaled
3. **Passing Grade** (text-info): Persentase kelulusan

#### Section 2: Rincian Jawaban Siswa

**Card Header:** "Rincian Jawaban Siswa"

**Loop Setiap Soal:**

```blade
@foreach($attempt->quiz->questions as $index => $question)
    <div class="mb-5">
        <h6>Soal {{ $index + 1 }}:</h6>
        <p class="lead">{!! nl2br(e($question->question_text)) !!}</p>

        {{-- Render opsi jawaban dengan warna --}}
        <div class="options-review">
            @foreach($question->options as $option)
                {{-- Logika warna background --}}
            @endforeach
        </div>

        {{-- Penjelasan jika ada --}}
        @if($question->explanation)
            <div class="alert alert-info mt-3">
                <strong><i class="fa fa-lightbulb-o"></i> Penjelasan:</strong><br>
                {!! nl2br(e($question->explanation)) !!}
            </div>
        @endif
    </div>
    @if(!$loop->last)<hr>@endif
@endforeach
```

#### Logika Warna Opsi Jawaban:

```php
@php
    $studentAnswersForThisQuestion = $attempt->answers->where('question_id', $question->id);
    $studentAnswerIds = $studentAnswersForThisQuestion->pluck('selected_option_id')->toArray();
    $isQuestionCorrect = $studentAnswersForThisQuestion->isNotEmpty() && $studentAnswersForThisQuestion->first()->is_correct;
@endphp

@foreach($question->options as $option)
    @php
        $isStudentAnswer = in_array($option->id, $studentAnswerIds);
        $isCorrectAnswer = $option->is_correct;
        $labelClass = '';

        if ($isStudentAnswer && $isCorrectAnswer) {
            $labelClass = 'bg-success text-white'; // Jawaban student & benar
        }
        elseif ($isStudentAnswer && !$isCorrectAnswer) {
            $labelClass = 'bg-danger text-white'; // Jawaban student & salah
        }
        elseif (!$isStudentAnswer && $isCorrectAnswer) {
            $labelClass = 'bg-info text-white'; // Kunci jawaban (tidak dijawab student)
        }
    @endphp
    <div class="p-2 rounded mb-2 {{ $labelClass }}">
        @if($isStudentAnswer)
            <i class="fa fa-check-circle-o mr-2"></i> <strong>Jawaban Siswa</strong>
        @elseif($isCorrectAnswer)
            <i class="fa fa-check mr-2"></i> <strong>Kunci Jawaban</strong>
        @else
            <i class="fa fa-circle-o mr-2" style="opacity: 0.5;"></i>
        @endif
        {{ $option->option_text }}
    </div>
@endforeach
```

**Warna Background:**

-   **Hijau (bg-success)**: Jawaban student DAN benar
-   **Merah (bg-danger)**: Jawaban student tapi SALAH
-   **Biru (bg-info)**: BUKAN jawaban student, tapi ini kunci jawaban
-   **Tidak berwarna**: Opsi lain yang tidak relevan

**Perbedaan dengan View Student:**

-   Instructor **SELALU** bisa lihat rincian jawaban (tidak ada setting `reveal_answers`)
-   Penjelasan **SELALU** ditampilkan jika ada (tidak tergantung apakah soal dijawab benar)

---

## 6. DIAGRAM ALUR LENGKAP - INSTRUCTOR MELIHAT HASIL

```
[Dashboard Instructor]
    → Kelola Kursus
    → Pilih Kursus
    → Pilih Modul
    → Daftar Pelajaran
    ↓
[Halaman Daftar Lesson]
    → Setiap quiz lesson ada button "Lihat Nilai"
    ↓
[Klik "Lihat Nilai"]
    ↓
[GET: instructor.quiz.results dengan quizId]
    → Otorisasi: Cek instructor = pemilik kursus
    → Load quiz dengan questions
    → Hitung totalMaxScore, minimumScore, minimumScoreScaled
    → Query semua student yang terdaftar di kursus (ordered by NIM)
    → Query semua attempt untuk quiz ini
    → Group attempt per student
    → Loop student: Tentukan status akhir (Lulus/Gagal/Belum Mengerjakan)
    → Attach attempts ke setiap student
    ↓
[Halaman Hasil Kuis - Index]
    → Tabel daftar semua student
    → Kolom: NIM, Nama, Status, Aksi
    → Button "Lihat Riwayat" (jika pernah mengerjakan)
    ↓
[Klik "Lihat Riwayat" di Salah Satu Student]
    ↓
[Modal Riwayat Muncul]
    → Tabel riwayat semua attempt student tersebut
    → Kolom: Waktu Selesai, Skor, Nilai, Status, Aksi
    → Sorted by created_at desc (terbaru di atas)
    → Button "Periksa Jawaban" per attempt
    ↓
[Klik "Periksa Jawaban" di Salah Satu Attempt]
    ↓
[GET: instructor.quiz.review_attempt dengan attemptId]
    → Otorisasi: Cek instructor = pemilik kursus
    → Eager load: quiz, questions, options, answers, student
    → Hitung skor, nilai, minimum
    ↓
[Halaman Periksa Jawaban - Show]
    → Card hasil ringkas:
        - Status (Lulus/Gagal)
        - Info skor (Student, Min, Max)
        - Info nilai (Student, Min, Passing Grade)
    → Card rincian jawaban:
        - Loop setiap soal
        - Tampil opsi dengan warna:
          * Hijau: Jawaban student & benar
          * Merah: Jawaban student & salah
          * Biru: Kunci jawaban (tidak dijawab)
          * Abu-abu: Opsi lain
        - Penjelasan (jika ada)
```

---

## 7. ROUTES RINGKASAN

### Routes untuk Student:

```php
// Melihat hasil quiz (bisa dari redirect submit atau dari riwayat)
Route::get('/student/quiz/attempt/{attempt}/result', [StudentQuizController::class, 'result'])
    ->name('student.quiz.result');

// AJAX load preview quiz (termasuk riwayat attempt)
Route::get('/student/lessons/{lesson}/content', [CourseController::class, 'getContent'])
    ->name('student.lessons.content');
```

### Routes untuk Instructor:

```php
// Halaman daftar hasil quiz semua student
Route::get('/instructor/quizzes/{quiz}/results', [InstructorQuizController::class, 'showResults'])
    ->name('instructor.quiz.results');

// Halaman periksa jawaban detail satu attempt
Route::get('/instructor/quiz-attempts/{attempt}/review', [InstructorQuizController::class, 'reviewAttempt'])
    ->name('instructor.quiz.review_attempt');
```

---

## 8. PERBEDAAN UTAMA: STUDENT VS INSTRUCTOR

| Aspek              | Student                                           | Instructor                                      |
| ------------------ | ------------------------------------------------- | ----------------------------------------------- |
| **Akses**          | Hanya hasil quiz sendiri                          | Hasil quiz SEMUA student di kursus              |
| **Entry Point**    | Preview quiz di lesson (via AJAX)                 | Halaman daftar lesson → button "Lihat Nilai"    |
| **Daftar Attempt** | Tabel riwayat di preview quiz                     | Modal per student dengan button "Lihat Riwayat" |
| **Reveal Answers** | Tergantung setting `reveal_answers`               | SELALU bisa lihat semua jawaban                 |
| **Penjelasan**     | Hanya tampil jika jawaban benar & ada explanation | SELALU tampil jika ada explanation              |
| **Sorting**        | Attempt terbaru di atas (desc)                    | Student sorted by NIM, Attempt terbaru di atas  |
| **Validasi**       | Cek ownership (attempt milik student login)       | Cek ownership (instructor = pemilik kursus)     |
| **View File**      | `student/quizzes/result.blade.php`                | `instructor/quizzes/results/show.blade.php`     |

---

## 9. FITUR SECURITY & VALIDASI

### A. Validasi untuk Student:

1. **Enrollment Check**: Pastikan student terdaftar di kursus
2. **Ownership Check**: Pastikan attempt milik student yang login
3. **Status Check**: Jika status `in_progress`, redirect ke take quiz

### B. Validasi untuk Instructor:

1. **Authorization Check**: Pastikan instructor adalah pemilik kursus terkait quiz
2. **No Ownership Check**: Instructor bisa lihat attempt siapapun di kursusnya

### C. Abort 403:

Jika validasi gagal, sistem akan return `abort(403)` dengan pesan error.

---

## 10. DATA YANG DIHITUNG & DITAMPILKAN

### A. Skor (Score):

-   **Skor Mentah**: Total poin yang didapat dari soal-soal yang dijawab benar
-   **Skor Minimum**: `$maxPossibleScore * ($quiz->pass_mark / 100)`
-   **Skor Maksimum**: `$quiz->questions->sum('score')`

### B. Nilai (Grade):

-   **Nilai Student (Scaled 0-100)**: `min(100, round(($attempt->score / $maxPossibleScore) * 100, 2))`
-   **Nilai Minimum (Scaled 0-100)**: `$quiz->pass_mark` (sudah dalam bentuk persentase)
-   **Passing Grade**: `$quiz->pass_mark` %

### C. Formula:

```
Skor Student = Σ(soal yang benar).score
Nilai Student = (Skor Student / Skor Maksimum) × 100

Passing Grade = X% (setting quiz)
Skor Minimum = Skor Maksimum × (Passing Grade / 100)
Nilai Minimum = Passing Grade

Status = (Nilai Student >= Nilai Minimum) ? "Lulus" : "Gagal"
```

---

## 11. FILE-FILE PENTING (REFERENSI CEPAT)

### Controllers:

-   `app/Http/Controllers/Student/StudentQuizController.php`
    -   Method: `result`
-   `app/Http/Controllers/Student/CourseController.php`
    -   Method: `getContent` (untuk load preview quiz dengan riwayat)
-   `app/Http/Controllers/Instructor/InstructorQuizController.php`
    -   Method: `showResults`, `reviewAttempt`

### Views - Student:

-   `resources/views/student/quizzes/result.blade.php` (halaman hasil detail)
-   `resources/views/student/quizzes/partials/_quiz_preview_in_lesson.blade.php` (preview quiz)
-   `resources/views/student/quizzes/partials/_quiz_attempt_history.blade.php` (tabel riwayat)

### Views - Instructor:

-   `resources/views/instructor/quizzes/results/index.blade.php` (daftar student)
-   `resources/views/instructor/quizzes/results/show.blade.php` (periksa jawaban detail)
-   `resources/views/instructor/lessons/index.blade.php` (entry point: button "Lihat Nilai")

### Routes:

-   `routes/web.php` (baris 156-171 untuk student, 432-435 untuk instructor)

---

## 12. USE CASE SCENARIOS

### Scenario 1: Student Ingin Review Quiz Sebelumnya

1. Student masuk ke halaman kursus
2. Klik lesson quiz dari sidebar
3. Preview quiz muncul dengan tabel riwayat
4. Lihat skor/nilai di tabel riwayat
5. Klik "Lihat" di attempt yang ingin direview
6. Halaman result muncul dengan rincian jawaban (jika diizinkan)

### Scenario 2: Instructor Ingin Cek Nilai Semua Student

1. Instructor masuk dashboard
2. Kelola kursus → Pilih kursus → Pilih modul → Daftar lesson
3. Klik "Lihat Nilai" di lesson quiz
4. Muncul tabel semua student dengan status (Lulus/Gagal/Belum Mengerjakan)
5. Klik "Lihat Riwayat" di student tertentu
6. Modal muncul dengan tabel semua attempt student tersebut
7. Lihat skor/nilai setiap attempt

### Scenario 3: Instructor Ingin Periksa Jawaban Detail Student

1. (Lanjutan dari Scenario 2, setelah modal riwayat terbuka)
2. Klik "Periksa Jawaban" di attempt tertentu
3. Halaman detail muncul dengan:
    - Status kelulusan
    - Card info skor & nilai
    - Rincian jawaban per soal dengan warna
    - Penjelasan setiap soal (jika ada)

### Scenario 4: Student Ingin Lanjutkan Quiz yang Belum Selesai

1. Student masuk ke preview quiz
2. Di tabel riwayat ada attempt dengan status "Sedang Dikerjakan"
3. Klik "Lanjutkan"
4. Redirect ke halaman take quiz
5. Lanjutkan mengerjakan dari terakhir kali

---

## 13. CATATAN PENTING UNTUK PENGEMBANGAN

### A. Skalabilitas:

-   Query `enrolledStudents` dan `attempts` di instructor tidak menggunakan pagination
-   Jika jumlah student sangat banyak (>1000), pertimbangkan:
    -   Pagination untuk tabel student
    -   Lazy loading untuk modal riwayat
    -   Caching untuk data quiz dan questions

### B. Performance:

-   Eager loading sudah diterapkan untuk mengurangi N+1 query problem
-   `groupBy('student_id')` untuk optimize query attempts
-   Pertimbangkan index database di kolom:
    -   `quiz_attempts.student_id`
    -   `quiz_attempts.quiz_id`
    -   `quiz_attempts.status`

### C. Fitur yang Bisa Ditambahkan:

1. **Export hasil quiz** (Excel/PDF) untuk instructor
2. **Statistik quiz** (rata-rata nilai, tingkat kesulitan per soal)
3. **Filter & search** di tabel student (cari by nama/NIM)
4. **Sort tabel** (by skor, by nilai, by waktu)
5. **Grafik visualisasi** (distribusi nilai, perbandingan attempt)
6. **Notifikasi** untuk student saat instructor memberikan feedback
7. **Feedback/komentar** dari instructor ke attempt student

---

## 14. KESIMPULAN

Sistem review hasil kuis di LMS2025 sudah lengkap dengan fitur:

### Untuk Student:

✅ Tabel riwayat semua attempt di preview quiz
✅ Detail hasil per attempt (skor, nilai, status, durasi)
✅ Rincian jawaban per soal (jika diizinkan)
✅ Lanjutkan quiz yang belum selesai

### Untuk Instructor:

✅ Daftar semua student dengan status akhir
✅ Modal riwayat per student
✅ Periksa jawaban detail setiap attempt
✅ Selalu bisa lihat semua jawaban dan penjelasan
✅ Sorting student berdasarkan NIM

Sistem ini siap untuk dikembangkan lebih lanjut dengan fitur analitik, export, dan feedback.

---

**Dokumen ini dibuat pada:** 1 Desember 2025
**Versi:** 1.0
**Status Sistem:** Production-ready
**Melengkapi:** DOKUMENTASI_ALUR_SISTEM_KUIS.md
