# DOKUMENTASI ALUR INSTRUCTOR MENGELOLA KUIS - LMS2025

## Deskripsi Umum

Dokumen ini menjelaskan secara detail alur lengkap instructor dalam mengelola kuis di sistem LMS2025, mulai dari membuat kuis baru, mengedit kuis, menambahkan soal, hingga menghapus kuis. Dokumentasi ini melengkapi dokumentasi sistem kuis yang sudah ada dan ditujukan untuk membantu pengembangan lebih lanjut.

---

## OVERVIEW FITUR PENGELOLAAN KUIS

Instructor memiliki kontrol penuh atas kuis yang dibuat dalam kursusnya, meliputi:

### Fitur Utama:

1. **Membuat Kuis Baru** (Create Quiz)
2. **Mengedit Setting Kuis** (Edit Quiz Settings)
3. **Mengelola Soal Kuis** (Manage Quiz Questions)
    - Menambah soal dari Bank Soal
    - Menghapus soal dari kuis
4. **Melihat Hasil Kuis Student** (View Quiz Results) - sudah dijelaskan di dokumentasi terpisah
5. **Menghapus Kuis** (Delete Quiz)

---

# BAGIAN 1: MEMBUAT KUIS BARU

## 1.1. Entry Point: Halaman Daftar Lesson

### Lokasi:

**Dashboard Instructor** → **Kelola Kursus** → **Pilih Kursus** → **Pilih Modul** → **Daftar Pelajaran**

### Navigasi:

-   URL: `/instructor/modules/{module}/lessons`
-   Route: `instructor.modules.lessons.index`
-   View: `resources/views/instructor/lessons/index.blade.php`

### Tombol "Tambah Pelajaran Baru":

Di halaman daftar lesson, ada tombol untuk menambah pelajaran baru dengan dropdown untuk memilih tipe:

-   Article
-   Video
-   **Quiz** ← FOKUS
-   Assignment
-   Document
-   Link Collection
-   Lesson Point

---

## 1.2. Klik "Buat Kuis" → Halaman Create Quiz

### Navigasi:

-   URL: `/instructor/modules/{module}/lessons/create?type=quiz`
-   Route: `instructor.modules.lessons.create` (dengan query parameter `type=quiz`)
-   Controller: `LessonController@create`
-   View: `resources/views/instructor/lessons/create-quiz.blade.php`

### Proses di Backend (method `create`):

```php
public function create(Request $request, Module $module)
{
    $type = $request->query('type');
    $validTypes = ['article', 'video', 'quiz', 'assignment', 'document', 'link', 'lessonpoin'];

    if (!in_array($type, $validTypes)) {
        abort(404, 'Tipe pelajaran tidak valid.');
    }

    $viewName = "instructor.lessons.create-{$type}"; // create-quiz
    return view($viewName, compact('module'));
}
```

---

## 1.3. Tampilan Form Create Quiz

### Header Halaman:

-   **Judul**: "Buat Pelajaran Baru"
-   **Sub-judul**: "Tipe: Kuis"
-   **Breadcrumb**: Home > Modul Saya > {nama_modul} > Buat Kuis

### Form Fields:

#### A. Informasi Pelajaran Umum (HIDDEN)

**Field:** `title` (hidden input)

-   **Fungsi**: Digunakan untuk judul lesson
-   **Sinkronisasi**: Nilai otomatis disamakan dengan `quiz_title` via JavaScript
-   **Validasi**: Required

**JavaScript Sync:**

```javascript
const quizTitleInput = document.getElementById("quiz_title_input");
const lessonTitleInput = document.getElementById("lesson_title_input");

function syncTitles() {
    lessonTitleInput.value = quizTitleInput.value;
}

// Sync saat halaman load
syncTitles();

// Sync saat input berubah
quizTitleInput.addEventListener("input", syncTitles);
```

#### B. Informasi Spesifik Kuis

**1. Judul Kuis**

-   **Field**: `quiz_title`
-   **Type**: text
-   **Validasi**: Required, max 255 char
-   **Placeholder**: "Contoh: Ujian Pemahaman Dasar PHP"

**2. Deskripsi Kuis**

-   **Field**: `quiz_description`
-   **Type**: textarea (3 rows)
-   **Validasi**: Optional
-   **Placeholder**: "Jelaskan instruksi atau topik yang dicakup dalam kuis ini..."

**3. Nilai Kelulusan (%)**

-   **Field**: `pass_mark`
-   **Type**: number
-   **Validasi**: Required, min 0, max 100
-   **Default**: 75

**4. Batas Waktu (Menit)**

-   **Field**: `time_limit`
-   **Type**: number
-   **Validasi**: Optional, min 1
-   **Placeholder**: "Kosongkan jika tidak ada batas waktu"
-   **Catatan**: Jika kosong = tanpa batas waktu

**5. Opsi Batas Waktu**

-   **Field**: `allow_exceed_time_limit`
-   **Type**: checkbox (form-switch)
-   **Value**: 1 (jika checked), 0 (jika tidak)
-   **Label**: "Izinkan siswa tetap mengirim jawaban setelah waktu habis (tidak akan mendapat poin)."
-   **Default**: Unchecked (0)

**6. Opsi Hasil Kuis**

-   **Field**: `reveal_answers`
-   **Type**: checkbox (form-switch)
-   **Value**: 1 (jika checked), 0 (jika tidak)
-   **Label**: "Tampilkan rincian jawaban (benar/salah) kepada siswa di halaman hasil."
-   **Default**: Checked (1)

**7. Batas Pengerjaan**

-   **Field**: `attempt_limit_type` (radio button)
-   **Options**:
    -   `unlimited` (default, checked)
    -   `limited`
-   **Field Tambahan**: `max_attempts` (number input)
    -   **Type**: number
    -   **Validasi**: Required jika `attempt_limit_type = limited`, min 1
    -   **Placeholder**: "Masukkan jumlah percobaan, misal: 3"
    -   **Display**: Hidden by default, tampil jika `limited` dipilih

**JavaScript Toggle:**

```javascript
function toggleMaxAttempts(value) {
    const container = document.getElementById("max-attempts-container");
    const input = container.querySelector("input");

    if (value === "limited") {
        container.style.display = "block";
        input.required = true;
    } else {
        container.style.display = "none";
        input.required = false;
        input.value = "";
    }
}
```

**8. Jadwal Ketersediaan (Opsional)**

-   **Field**: `available_from` (datetime-local)
    -   **Label**: "Mulai Tersedia Pada"
    -   **Validasi**: Optional, date
-   **Field**: `available_to` (datetime-local)
    -   **Label**: "Tersedia Hingga"
    -   **Validasi**: Optional, date, after_or_equal:available_from
-   **Catatan**: Kosongkan jika kuis bisa dikerjakan kapan saja

#### C. Tombol Aksi

-   **Batal**: Kembali ke daftar lesson
-   **Simpan & Lanjutkan**: Submit form

---

## 1.4. Submit Form → Proses Store

### Form Submission:

```html
<form
    action="{{ route('instructor.modules.lessons.store', $module) }}"
    method="POST"
>
    @csrf
    <input type="hidden" name="lesson_type" value="quiz" />
    <!-- Fields lainnya -->
</form>
```

### Proses Backend (method `store`):

**Controller**: `LessonController@store`

**Validasi:**

```php
$request->validate([
    'title' => 'required|string|max:255',
    'lesson_type' => 'required|in:article,video,quiz,assignment,document,link,lessonpoin',
]);
```

**Untuk Quiz Khusus:**

```php
$validated = $request->validate([
    'quiz_title' => 'required|string|max:255',
    'quiz_description' => 'nullable|string',
    'pass_mark' => 'required|integer|min:0|max:100',
    'time_limit' => 'nullable|integer|min:1',
    'allow_exceed_time_limit' => 'required|boolean',
    'reveal_answers' => 'required|boolean',
    'max_attempts' => 'nullable|integer|min:1',
    'available_from' => 'nullable|date',
    'available_to' => 'nullable|date|after_or_equal:available_from',
]);
```

**Konversi Timezone untuk Jadwal:**

```php
if (!empty($validated['available_from'])) {
    // Ambil timezone instruktur
    $instructorTimezone = Auth::user()->timezone ?? config('app.timezone');

    // Konversi dari timezone lokal ke UTC
    $availableFrom = Carbon::parse($validated['available_from'], $instructorTimezone)->utc();
    $validated['available_from'] = $availableFrom;
}

if (!empty($validated['available_to'])) {
    $instructorTimezone = Auth::user()->timezone ?? config('app.timezone');
    $availableTo = Carbon::parse($validated['available_to'], $instructorTimezone)->utc();
    $validated['available_to'] = $availableTo;
}
```

**Membuat Quiz:**

```php
$lessonable = Quiz::create([
    'title' => $validated['quiz_title'],
    'description' => $validated['quiz_description'],
    'pass_mark' => $validated['pass_mark'],
    'time_limit' => $validated['time_limit'],
    'allow_exceed_time_limit' => $validated['allow_exceed_time_limit'],
    'reveal_answers' => $validated['reveal_answers'],
    'max_attempts' => $validated['max_attempts'],
    'available_from' => $validated['available_from'],
    'available_to' => $validated['available_to'],
]);
```

**Membuat Lesson (Polymorphic Relation):**

```php
$lastOrder = $module->lessons()->max('order') ?? 0;

$lessonable->lesson()->create([
    'module_id' => $module->id,
    'title' => $request->input('title'), // Dari hidden input yang sync dengan quiz_title
    'order' => $lastOrder + 1,
]);
```

**Redirect:**

```php
return redirect()
    ->route('instructor.modules.lessons.index', $module)
    ->with('success', 'Pelajaran berhasil dibuat.');
```

---

## 1.5. Database Tables & Relations

### Tabel: `quizzes`

```sql
CREATE TABLE quizzes (
    id BIGINT UNSIGNED PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    pass_mark INT NOT NULL DEFAULT 60,
    time_limit INT NULL,
    allow_exceed_time_limit BOOLEAN DEFAULT FALSE,
    reveal_answers BOOLEAN DEFAULT FALSE,
    max_attempts INT NULL,
    available_from DATETIME NULL,
    available_to DATETIME NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Tabel: `lessons`

```sql
CREATE TABLE lessons (
    id BIGINT UNSIGNED PRIMARY KEY,
    module_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    order INT NOT NULL,
    lessonable_type VARCHAR(255) NOT NULL, -- App\Models\Quiz
    lessonable_id BIGINT UNSIGNED NOT NULL, -- quiz.id
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
);
```

### Relasi Polymorphic:

```php
// Di Model Quiz
public function lesson()
{
    return $this->morphOne(Lesson::class, 'lessonable');
}

// Di Model Lesson
public function lessonable()
{
    return $this->morphTo();
}
```

---

# BAGIAN 2: MENGEDIT KUIS

## 2.1. Entry Point: Halaman Daftar Lesson

### Tombol Edit:

Di setiap baris lesson bertipe quiz, ada tombol:

-   **Icon**: Edit (pencil)
-   **Route**: `instructor.lessons.edit`

---

## 2.2. Klik "Edit" → Halaman Edit Quiz

### Navigasi:

-   URL: `/instructor/lessons/{lesson}/edit`
-   Route: `instructor.lessons.edit`
-   Controller: `LessonController@edit`
-   View: `resources/views/instructor/lessons/edit-quiz.blade.php`

### Proses di Backend (method `edit`):

```php
public function edit(Lesson $lesson)
{
    $lesson->load('lessonable');
    $type = $lesson->lessonable_type;
    $shortType = strtolower(class_basename($type)); // "quiz"

    $viewName = "instructor.lessons.edit-{$shortType}"; // edit-quiz
    return view($viewName, compact('lesson'));
}
```

---

## 2.3. Tampilan Form Edit Quiz

**Struktur hampir sama dengan Create Quiz**, dengan perbedaan:

### Header:

-   **Judul**: "Edit Pelajaran"
-   **Sub-judul**: "Tipe: Kuis"
-   **Breadcrumb**: Home > Modul > {nama_modul} > Edit Kuis

### Form Method:

```html
<form
    action="{{ route('instructor.lessons.update', $lesson->id) }}"
    method="POST"
>
    @csrf @method('PUT')
    <!-- Fields -->
</form>
```

### Pre-filled Values:

Semua field di-fill dengan data existing:

```blade
<input type="text"
       name="quiz_title"
       value="{{ old('quiz_title', $lesson->lessonable->title) }}"
       required>

<textarea name="quiz_description">{{ old('quiz_description', $lesson->lessonable->description) }}</textarea>

<input type="number"
       name="pass_mark"
       value="{{ old('pass_mark', $lesson->lessonable->pass_mark) }}">

<input type="number"
       name="time_limit"
       value="{{ old('time_limit', $lesson->lessonable->time_limit) }}">
```

### Checkbox Pre-checked:

```blade
<input type="checkbox"
       name="allow_exceed_time_limit"
       value="1"
       {{ old('allow_exceed_time_limit', $lesson->lessonable->allow_exceed_time_limit) ? 'checked' : '' }}>

<input type="checkbox"
       name="reveal_answers"
       value="1"
       {{ old('reveal_answers', $lesson->lessonable->reveal_answers) ? 'checked' : '' }}>
```

### Radio Button Pre-selected:

```blade
@php
    $max_attempts = old('max_attempts', $lesson->lessonable->max_attempts);
    $limit_type = old('attempt_limit_type', is_null($max_attempts) ? 'unlimited' : 'limited');
@endphp

<input type="radio"
       name="attempt_limit_type"
       value="unlimited"
       {{ $limit_type == 'unlimited' ? 'checked' : '' }}>

<input type="radio"
       name="attempt_limit_type"
       value="limited"
       {{ $limit_type == 'limited' ? 'checked' : '' }}>
```

### Datetime-local Pre-filled:

```blade
<input type="datetime-local"
       name="available_from"
       value="{{ old('available_from', $lesson->lessonable->available_from ? $lesson->lessonable->available_from->format('Y-m-d\TH:i') : '') }}">

<input type="datetime-local"
       name="available_to"
       value="{{ old('available_to', $lesson->lessonable->available_to ? $lesson->lessonable->available_to->format('Y-m-d\TH:i') : '') }}">
```

**Catatan**: Format `Y-m-d\TH:i` diperlukan untuk input type `datetime-local`.

---

## 2.4. Submit Update → Proses Update

### Proses Backend (method `update`):

**Controller**: `LessonController@update`

**Validasi**: Sama seperti store

**Update Lesson Title:**

```php
$lesson->update(['title' => $request->input('title')]);
```

**Update Quiz (Lessonable):**

```php
$lessonable = $lesson->lessonable;

// Validasi untuk quiz
$validated = $request->validate([
    'quiz_title' => 'required|string|max:255',
    'quiz_description' => 'nullable|string',
    'pass_mark' => 'required|integer|min:0|max:100',
    'time_limit' => 'nullable|integer|min:1',
    'allow_exceed_time_limit' => 'required|boolean',
    'reveal_answers' => 'required|boolean',
    'max_attempts' => 'nullable|integer|min:1',
    'available_from' => 'nullable|date',
    'available_to' => 'nullable|date|after_or_equal:available_from',
]);

// Konversi timezone (sama seperti store)
if (!empty($validated['available_from'])) {
    $instructorTimezone = Auth::user()->timezone ?? config('app.timezone');
    $validated['available_from'] = Carbon::parse($validated['available_from'], $instructorTimezone)->utc();
}

if (!empty($validated['available_to'])) {
    $instructorTimezone = Auth::user()->timezone ?? config('app.timezone');
    $validated['available_to'] = Carbon::parse($validated['available_to'], $instructorTimezone)->utc();
}

// Update quiz
$lessonable->update([
    'title' => $validated['quiz_title'],
    'description' => $validated['quiz_description'],
    'pass_mark' => $validated['pass_mark'],
    'time_limit' => $validated['time_limit'],
    'allow_exceed_time_limit' => $validated['allow_exceed_time_limit'],
    'reveal_answers' => $validated['reveal_answers'],
    'max_attempts' => $validated['max_attempts'],
    'available_from' => $validated['available_from'],
    'available_to' => $validated['available_to'],
]);
```

**Redirect:**

```php
return redirect()
    ->route('instructor.modules.lessons.index', $lesson->module)
    ->with('success', 'Pelajaran berhasil diperbarui.');
```

---

# BAGIAN 3: MENGELOLA SOAL KUIS

## 3.1. Entry Point: Halaman Daftar Lesson

### Tombol "Kelola Soal":

Di setiap baris lesson bertipe quiz, ada tombol:

-   **Icon**: List/Gear
-   **Text**: "Kelola Soal"
-   **Route**: `instructor.quizzes.manage_questions`

---

## 3.2. Klik "Kelola Soal" → Halaman Manage Questions

### Navigasi:

-   URL: `/instructor/quizzes/{quiz}/manage-questions`
-   Route: `instructor.quizzes.manage_questions`
-   Controller: `QuizQuestionController@index`
-   View: `resources/views/instructor/quizzes/manage-questions.blade.php`

### Proses di Backend (method `index`):

```php
public function index(Quiz $quiz)
{
    // Load soal yang sudah ditambahkan ke quiz
    $attachedQuestions = $quiz->questions()->orderBy('pivot_order')->get();

    return view('instructor.quizzes.manage-questions', compact('quiz', 'attachedQuestions'));
}
```

---

## 3.3. Tampilan Halaman Manage Questions

### Header:

-   **Judul**: "Kelola Soal Kuis"
-   **Sub-judul**: "Judul Kuis: {nama_quiz}"
-   **Breadcrumb**: Home > Kursus Saya > Modul Saya > Daftar Pelajaran > Kelola Soal

### Card Utama:

**Header Card:**

-   **Judul**: "Daftar Soal dalam Kuis Ini"
-   **Deskripsi**: "Daftar soal yang telah ditambahkan ke kuis."
-   **Button**: "Tambah Soal" (di kanan)

### Tabel Soal:

| Kolom         | Deskripsi                                            |
| ------------- | ---------------------------------------------------- |
| **#**         | Nomor urut                                           |
| **Teks Soal** | Preview teks soal (limit 100 char, strip HTML tags)  |
| **Tipe**      | Jenis soal (Multiple Choice Single, True False, dll) |
| **Aksi**      | Button "Hapus" untuk detach soal dari quiz           |

**Contoh Row:**

```blade
<tr>
    <th scope="row">{{ $loop->iteration }}</th>
    <td>{{ Str::limit(strip_tags($question->question_text), 100) }}</td>
    <td>{{ ucfirst(str_replace('_', ' ', $question->question_type)) }}</td>
    <td class="text-center">
        <form action="{{ route('instructor.quizzes.detach_question', ['quiz' => $quiz, 'question' => $question]) }}"
              method="POST"
              onsubmit="return confirm('Apakah Anda yakin ingin menghapus soal ini dari kuis?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm">
                <i class="fa fa-trash"></i> Hapus
            </button>
        </form>
    </td>
</tr>
```

---

## 3.4. Modal: Tambah Soal Baru

### Trigger:

Klik button "Tambah Soal" → Modal `#addQuestionModal` muncul.

### Isi Modal:

**Header**: "Tambah Soal Baru"

**Body**: 2 Opsi dalam List Group

**Opsi 1: Ambil dari Bank Soal**

-   **Icon**: fa-university
-   **Judul**: "Ambil dari Bank Soal"
-   **Deskripsi**: "Pilih soal yang sudah pernah Anda buat sebelumnya."
-   **Link**: Route `instructor.quizzes.browse_bank`

**Opsi 2: Buat Soal Baru di Bank Soal**

-   **Icon**: fa-plus-circle
-   **Judul**: "Buat Soal Baru di Bank Soal"
-   **Deskripsi**: "Ini akan membuka halaman Bank Soal di tab baru."
-   **Link**: Route `instructor.question-bank.topics.index` (target="\_blank")

---

## 3.5. Opsi 1: Ambil dari Bank Soal

### Navigasi:

-   URL: `/instructor/quizzes/{quiz}/browse-bank`
-   Route: `instructor.quizzes.browse_bank`
-   Controller: `QuizQuestionController@browseBank`
-   View: `resources/views/instructor/quizzes/browse-bank.blade.php`

### Proses di Backend (method `browseBank`):

**Load Relasi:**

```php
$quiz->load('lesson.module.course');
$currentCourseId = $quiz->lesson->module->course->id;
$user = Auth::user();
```

**Ambil Soal yang Sudah Ada di Quiz:**

```php
$existingQuestionIds = $quiz->questions()->pluck('questions.id');
```

**Filter Topik Soal:**

```php
$topics = $user->questionTopics()
    ->where(function ($query) use ($currentCourseId) {
        // Topik tersedia untuk semua kursus
        $query->where('available_for_all_courses', true)
            // ATAU topik terhubung ke kursus ini
            ->orWhereHas('courses', function ($subQuery) use ($currentCourseId) {
                $subQuery->where('course_id', $currentCourseId);
            });
    })
    ->withCount(['questions' => function ($query) use ($existingQuestionIds) {
        // Hitung hanya soal yang BELUM ada di kuis
        $query->whereNotIn('id', $existingQuestionIds);
    }])
    ->latest()
    ->get();
```

**Load Soal Jika Topik Dipilih:**

```php
$questionsInTopic = null;

if ($request->has('topic_id')) {
    $selectedTopic = QuestionTopic::findOrFail($request->topic_id);

    // Pastikan instructor adalah pemilik topik
    if ($user->id == $selectedTopic->instructor_id) {
        $questionsInTopic = $selectedTopic->questions()
            ->whereNotIn('id', $existingQuestionIds) // Exclude soal yang sudah ada
            ->get();
    }
}
```

---

## 3.6. Tampilan Browse Bank

### Layout: 2 Kolom

#### Kolom Kiri (col-md-4): Daftar Topik

**Card Header**: "Topik Soal Anda"

**List Group:**

-   Setiap topik adalah link dengan badge jumlah soal available
-   Topik yang sedang dipilih memiliki class `active`

```blade
@foreach ($topics as $topic)
    <a href="{{ route('instructor.quizzes.browse_bank', ['quiz' => $quiz, 'topic_id' => $topic->id]) }}"
       class="list-group-item list-group-item-action d-flex justify-content-between align-items-center
              {{ request('topic_id') == $topic->id ? 'active' : '' }}">
        {{ $topic->name }}
        <span class="badge badge-primary badge-pill">{{ $topic->questions_count }}</span>
    </a>
@endforeach
```

**Jika Tidak Ada Topik:**

```blade
<p class="text-muted">Anda belum memiliki topik soal di Bank Soal.</p>
```

#### Kolom Kanan (col-md-8): Daftar Soal

**Card Header**: "Pilih Soal"
**Deskripsi**: "Centang soal yang ingin Anda tambahkan ke kuis."

**Jika Belum Pilih Topik:**

```blade
<div class="text-center">
    <p class="text-muted">
        <i class="fa fa-arrow-left"></i>
        Silakan pilih topik di sebelah kiri untuk menampilkan daftar soal.
    </p>
</div>
```

**Jika Sudah Pilih Topik:**

Form dengan tabel soal:

```html
<form
    action="{{ route('instructor.quizzes.attach_questions', $quiz) }}"
    method="POST"
>
    @csrf
    <table class="table">
        <thead>
            <tr>
                <th style="width: 5%;">Pilih</th>
                <th>Tipe</th>
                <th>Teks Soal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($questionsInTopic as $question)
            <tr>
                <td>
                    <input
                        type="checkbox"
                        name="question_ids[]"
                        value="{{ $question->id }}"
                    />
                </td>
                <td>
                    {{ ucfirst(str_replace('_', ' ', $question->question_type))
                    }}
                </td>
                <td>
                    {{ Str::limit(strip_tags($question->question_text), 100) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <button type="submit" class="btn btn-primary">
        <i class="fa fa-plus-circle"></i> Tambah Soal Terpilih ke Kuis
    </button>
</form>
```

---

## 3.7. Submit Attach Questions → Proses Attach

### Form Submission:

```html
<form
    action="{{ route('instructor.quizzes.attach_questions', $quiz) }}"
    method="POST"
>
    @csrf
    <input type="checkbox" name="question_ids[]" value="1" />
    <input type="checkbox" name="question_ids[]" value="5" />
    <input type="checkbox" name="question_ids[]" value="8" />
</form>
```

### Proses Backend (method `attachQuestions`):

**Controller**: `QuizQuestionController@attachQuestions`

**Validasi:**

```php
$validated = $request->validate([
    'question_ids' => 'required|array',
    'question_ids.*' => 'exists:questions,id',
]);
```

**Attach Soal ke Quiz:**

```php
$quiz->questions()->syncWithoutDetaching($validated['question_ids']);
```

**Penjelasan `syncWithoutDetaching`:**

-   Menambahkan soal baru ke pivot table `quiz_question`
-   TIDAK menghapus soal yang sudah ada
-   Jika soal sudah attached, akan diabaikan (tidak duplikat)

**Redirect:**

```php
return redirect()
    ->route('instructor.quizzes.manage_questions', $quiz)
    ->with('success', 'Soal berhasil ditambahkan ke kuis.');
```

---

## 3.8. Detach Question dari Quiz

### Trigger:

Klik button "Hapus" di tabel manage questions.

### Form Submission:

```html
<form
    action="{{ route('instructor.quizzes.detach_question', ['quiz' => $quiz, 'question' => $question]) }}"
    method="POST"
    onsubmit="return confirm('Apakah Anda yakin ingin menghapus soal ini dari kuis?');"
>
    @csrf @method('DELETE')
    <button type="submit" class="btn btn-danger btn-sm">
        <i class="fa fa-trash"></i> Hapus
    </button>
</form>
```

### Proses Backend (method `detachQuestion`):

**Controller**: `QuizQuestionController@detachQuestion`

**Detach Soal:**

```php
public function detachQuestion(Quiz $quiz, Question $question)
{
    $quiz->questions()->detach($question->id);

    return back()->with('success', 'Soal berhasil dihapus dari kuis.');
}
```

**Penjelasan `detach`:**

-   Menghapus relasi di pivot table `quiz_question`
-   Soal tetap ada di Bank Soal (tidak dihapus permanent)
-   Hanya relasi dengan quiz yang dihapus

---

## 3.9. Pivot Table: quiz_question

### Schema:

```sql
CREATE TABLE quiz_question (
    quiz_id BIGINT UNSIGNED NOT NULL,
    question_id BIGINT UNSIGNED NOT NULL,
    order INT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    PRIMARY KEY (quiz_id, question_id),
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
);
```

### Relasi di Model:

**Model Quiz:**

```php
public function questions()
{
    return $this->belongsToMany(Question::class, 'quiz_question')
                ->withPivot('order')
                ->withTimestamps();
}
```

**Model Question:**

```php
public function quizzes()
{
    return $this->belongsToMany(Quiz::class, 'quiz_question')
                ->withPivot('order')
                ->withTimestamps();
}
```

---

# BAGIAN 4: MENGHAPUS KUIS

## 4.1. Entry Point: Halaman Daftar Lesson

### Tombol Delete:

Di setiap baris lesson, ada tombol:

-   **Icon**: Trash
-   **Route**: `instructor.lessons.destroy`

---

## 4.2. Klik "Delete" → Proses Destroy

### Form Submission:

```html
<form
    action="{{ route('instructor.lessons.destroy', $lesson) }}"
    method="POST"
    onsubmit="return confirm('Apakah Anda yakin ingin menghapus pelajaran ini?');"
>
    @csrf @method('DELETE')
    <button type="submit" class="btn btn-danger btn-sm">
        <i class="fa fa-trash"></i> Hapus
    </button>
</form>
```

### Proses Backend (method `destroy`):

**Controller**: `LessonController@destroy`

```php
public function destroy(Lesson $lesson)
{
    DB::transaction(function () use ($lesson) {
        $lessonable = $lesson->lessonable;

        if ($lessonable) {
            $shortType = strtolower(class_basename($lessonable));

            // Untuk quiz, tidak ada file yang perlu dihapus
            // Hanya delete record

            $lessonable->delete(); // Delete quiz
        }

        $lesson->delete(); // Delete lesson
    });

    return back()->with('success', 'Pelajaran berhasil dihapus.');
}
```

**Cascade Delete:**

-   Saat `quiz` dihapus, relasi di `quiz_question` otomatis dihapus (ON DELETE CASCADE)
-   Saat `lesson` dihapus, relasi di `lesson_user` (completion) otomatis dihapus
-   `QuizAttempt` dan `StudentAnswer` **TIDAK** otomatis dihapus (untuk keperluan audit/riwayat)

---

# BAGIAN 5: BANK SOAL (QUESTION BANK)

## 5.1. Overview Bank Soal

**Bank Soal** adalah fitur terpisah untuk mengelola soal-soal yang bisa digunakan di berbagai quiz. Instructor dapat:

-   Membuat topik soal
-   Membuat soal dalam topik
-   Mengatur ketersediaan topik (per kursus atau semua kursus)

### Routes Bank Soal:

#### Topik:

```php
Route::get('/instructor/question-topics', [QuestionTopicController::class, 'index'])
    ->name('instructor.question-bank.topics.index');
Route::get('/instructor/question-topics/create', [QuestionTopicController::class, 'create'])
    ->name('instructor.question-bank.topics.create');
Route::post('/instructor/question-topics', [QuestionTopicController::class, 'store'])
    ->name('instructor.question-bank.topics.store');
Route::get('/instructor/question-topics/{topic}/edit', [QuestionTopicController::class, 'edit'])
    ->name('instructor.question-bank.topics.edit');
Route::put('/instructor/question-topics/{topic}', [QuestionTopicController::class, 'update'])
    ->name('instructor.question-bank.topics.update');
Route::delete('/instructor/question-topics/{topic}', [QuestionTopicController::class, 'destroy'])
    ->name('instructor.question-bank.topics.destroy');
```

#### Soal (Questions):

```php
Route::get('/instructor/question-topics/{topic}/questions', [QuestionController::class, 'index'])
    ->name('instructor.question-bank.questions.index');
Route::get('/instructor/question-topics/{topic}/questions/create', [QuestionController::class, 'create'])
    ->name('instructor.question-bank.questions.create');
Route::post('/instructor/question-topics/{topic}/questions', [QuestionController::class, 'store'])
    ->name('instructor.question-bank.questions.store');
Route::get('/questions/{question}/edit', [QuestionController::class, 'edit'])
    ->name('instructor.question-bank.questions.edit');
Route::put('/questions/{question}', [QuestionController::class, 'update'])
    ->name('instructor.question-bank.questions.update');
Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])
    ->name('instructor.question-bank.questions.destroy');
Route::post('questions/{question}/clone', [QuestionController::class, 'clone'])
    ->name('instructor.question-bank.questions.clone');
Route::patch('/questions/{question}/move', [QuestionController::class, 'move'])
    ->name('instructor.question-bank.questions.move');
```

---

## 5.2. Model Question Topic

### Schema:

```sql
CREATE TABLE question_topics (
    id BIGINT UNSIGNED PRIMARY KEY,
    instructor_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    available_for_all_courses BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (instructor_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### Pivot Table: course_question_topic

```sql
CREATE TABLE course_question_topic (
    course_id BIGINT UNSIGNED NOT NULL,
    question_topic_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (course_id, question_topic_id),
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (question_topic_id) REFERENCES question_topics(id) ON DELETE CASCADE
);
```

### Relasi:

```php
// Model QuestionTopic
public function instructor()
{
    return $this->belongsTo(User::class, 'instructor_id');
}

public function questions()
{
    return $this->hasMany(Question::class, 'topic_id');
}

public function courses()
{
    return $this->belongsToMany(Course::class, 'course_question_topic');
}
```

---

## 5.3. Model Question

### Schema:

```sql
CREATE TABLE questions (
    id BIGINT UNSIGNED PRIMARY KEY,
    topic_id BIGINT UNSIGNED NOT NULL,
    question_text TEXT NOT NULL,
    question_type ENUM('multiple_choice_single', 'multiple_choice_multiple', 'true_false', 'drag_and_drop') NOT NULL,
    score INT NOT NULL DEFAULT 1,
    explanation TEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (topic_id) REFERENCES question_topics(id) ON DELETE CASCADE
);
```

### Tipe Soal:

1. **multiple_choice_single**: Pilihan ganda (1 jawaban benar)
2. **multiple_choice_multiple**: Pilihan ganda kompleks (bisa lebih dari 1 jawaban benar)
3. **true_false**: Benar/Salah
4. **drag_and_drop**: Isi titik-titik/Menjodohkan

---

## 5.4. Model Question Option

### Schema:

```sql
CREATE TABLE question_options (
    id BIGINT UNSIGNED PRIMARY KEY,
    question_id BIGINT UNSIGNED NOT NULL,
    option_text VARCHAR(255) NOT NULL,
    is_correct BOOLEAN DEFAULT FALSE,
    correct_gap_identifier VARCHAR(50) NULL, -- untuk drag_and_drop (contoh: "BLANK_1")
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
);
```

---

# BAGIAN 6: DIAGRAM ALUR LENGKAP

## 6.1. Alur Create Quiz

```
[Halaman Daftar Lesson]
    → Klik "Tambah Pelajaran Baru"
    → Pilih "Quiz"
    ↓
[Halaman Create Quiz]
    → Form Input:
        - Judul Kuis
        - Deskripsi
        - Pass Mark
        - Time Limit
        - Allow Exceed Time
        - Reveal Answers
        - Max Attempts
        - Available From/To
    → Klik "Simpan & Lanjutkan"
    ↓
[POST: instructor.modules.lessons.store]
    → Validasi input
    → Konversi timezone untuk jadwal
    → Create Quiz record
    → Create Lesson record (polymorphic)
    → Redirect ke Daftar Lesson
```

## 6.2. Alur Edit Quiz

```
[Halaman Daftar Lesson]
    → Klik "Edit" di lesson quiz
    ↓
[Halaman Edit Quiz]
    → Form pre-filled dengan data existing
    → Ubah data yang diperlukan
    → Klik "Simpan Perubahan"
    ↓
[PUT: instructor.lessons.update]
    → Validasi input
    → Konversi timezone
    → Update Lesson title
    → Update Quiz data
    → Redirect ke Daftar Lesson
```

## 6.3. Alur Manage Questions

```
[Halaman Daftar Lesson]
    → Klik "Kelola Soal" di lesson quiz
    ↓
[Halaman Manage Questions]
    → Tabel soal yang sudah ada
    → Klik "Tambah Soal"
    ↓
[Modal Pilihan]
    → Opsi 1: Ambil dari Bank Soal
    → Opsi 2: Buat Soal Baru (tab baru)
    ↓ (Pilih Opsi 1)
[Halaman Browse Bank]
    → Kolom Kiri: List Topik
    → Klik topik
    → Kolom Kanan: Tabel soal dalam topik
    → Centang soal yang ingin ditambahkan
    → Klik "Tambah Soal Terpilih ke Kuis"
    ↓
[POST: instructor.quizzes.attach_questions]
    → Validasi question_ids
    → syncWithoutDetaching ke pivot table
    → Redirect ke Manage Questions
    ↓
[Tabel Soal Updated]
    → Soal baru muncul di tabel
```

## 6.4. Alur Delete Question dari Quiz

```
[Halaman Manage Questions]
    → Klik "Hapus" di salah satu soal
    → Confirm dialog
    ↓
[DELETE: instructor.quizzes.detach_question]
    → Detach dari pivot table
    → Redirect back
    ↓
[Tabel Soal Updated]
    → Soal dihapus dari tabel
    → Soal tetap ada di Bank Soal
```

## 6.5. Alur Delete Quiz

```
[Halaman Daftar Lesson]
    → Klik "Hapus" di lesson quiz
    → Confirm dialog
    ↓
[DELETE: instructor.lessons.destroy]
    → Delete Quiz record
    → Delete Lesson record
    → Cascade delete quiz_question
    → Redirect back
    ↓
[Daftar Lesson Updated]
    → Lesson quiz dihapus dari list
```

---

# BAGIAN 7: SECURITY & AUTHORIZATION

## 7.1. Ownership Check

**Semua route quiz harus melalui validasi ownership:**

```php
// Di Controller atau Middleware
if ($quiz->lesson->module->course->instructor_id != Auth::id()) {
    abort(403, 'Anda tidak memiliki akses ke kuis ini.');
}
```

## 7.2. Role Check

**Hanya role instructor yang bisa akses:**

```php
// Di routes/web.php
Route::middleware(['auth', 'role:instructor'])->group(function () {
    // Routes instructor
});
```

## 7.3. Validasi Data

**Semua input harus divalidasi:**

-   Required fields tidak boleh kosong
-   Number fields harus numeric dengan min/max
-   Date fields harus valid dan logis (to >= from)
-   Checkbox/Radio harus boolean atau value yang valid

---

# BAGIAN 8: FITUR TAMBAHAN & BEST PRACTICES

## 8.1. Timezone Handling

**Problem:** Instructor dari timezone berbeda input jadwal quiz.

**Solution:** Simpan semua datetime dalam UTC, konversi ke timezone user saat display.

```php
// Saat Store/Update
$instructorTimezone = Auth::user()->timezone ?? config('app.timezone');
$validated['available_from'] = Carbon::parse($validated['available_from'], $instructorTimezone)->utc();

// Saat Display (di Model dengan trait HasLocalDates)
public function getAvailableFromAttribute($value)
{
    return $value ? Carbon::parse($value)->timezone(Auth::user()->timezone ?? config('app.timezone')) : null;
}
```

## 8.2. Soft Delete (Opsional)

**Untuk Quiz yang perlu audit trail:**

```php
// Tambahkan di migration
$table->softDeletes();

// Di Model Quiz
use SoftDeletes;
```

## 8.3. Order/Sorting Questions

**Untuk mengatur urutan soal di quiz:**

```php
// Tambahkan kolom order di pivot table
->withPivot('order')

// Method untuk reorder
public function reorderQuestions(Request $request, Quiz $quiz)
{
    $questionIds = $request->input('question_ids'); // [3, 1, 5, 2]

    foreach ($questionIds as $index => $questionId) {
        $quiz->questions()->updateExistingPivot($questionId, ['order' => $index + 1]);
    }

    return response()->json(['success' => true]);
}
```

## 8.4. Preview Quiz untuk Instructor

**Link preview dari manage questions:**

```blade
<a href="{{ route('student.quiz.start', ['quiz' => $quiz->id, 'preview' => 'true']) }}"
   class="btn btn-info"
   target="_blank">
    <i class="fa fa-eye"></i> Preview Quiz
</a>
```

## 8.5. Duplicate Quiz

**Fitur untuk duplikasi quiz ke modul lain:**

```php
public function duplicate(Quiz $quiz, Request $request)
{
    $newQuiz = $quiz->replicate();
    $newQuiz->save();

    // Duplikasi relasi soal
    $quiz->questions()->each(function ($question) use ($newQuiz) {
        $newQuiz->questions()->attach($question->id, [
            'order' => $question->pivot->order
        ]);
    });

    // Create lesson baru
    $newQuiz->lesson()->create([
        'module_id' => $request->input('target_module_id'),
        'title' => $quiz->title . ' (Copy)',
        'order' => Module::find($request->input('target_module_id'))->lessons()->max('order') + 1,
    ]);

    return redirect()->back()->with('success', 'Quiz berhasil diduplikasi.');
}
```

---

# BAGIAN 9: ROUTES RINGKASAN

```php
// ========== LESSON MANAGEMENT (termasuk Quiz) ==========
Route::get('/instructor/modules/{module}/lessons', [LessonController::class, 'index'])
    ->name('instructor.modules.lessons.index');

Route::get('/instructor/modules/{module}/lessons/create', [LessonController::class, 'create'])
    ->name('instructor.modules.lessons.create'); // ?type=quiz

Route::post('/instructor/modules/{module}/lessons', [LessonController::class, 'store'])
    ->name('instructor.modules.lessons.store');

Route::get('/instructor/lessons/{lesson}/edit', [LessonController::class, 'edit'])
    ->name('instructor.lessons.edit');

Route::put('/instructor/lessons/{lesson}', [LessonController::class, 'update'])
    ->name('instructor.lessons.update');

Route::delete('/instructor/lessons/{lesson}', [LessonController::class, 'destroy'])
    ->name('instructor.lessons.destroy');

// ========== QUIZ QUESTIONS MANAGEMENT ==========
Route::get('/instructor/quizzes/{quiz}/manage-questions', [QuizQuestionController::class, 'index'])
    ->name('instructor.quizzes.manage_questions');

Route::get('/instructor/quizzes/{quiz}/browse-bank', [QuizQuestionController::class, 'browseBank'])
    ->name('instructor.quizzes.browse_bank'); // ?topic_id=X

Route::post('/instructor/quizzes/{quiz}/attach-questions', [QuizQuestionController::class, 'attachQuestions'])
    ->name('instructor.quizzes.attach_questions');

Route::delete('/instructor/quizzes/{quiz}/detach-question/{question}', [QuizQuestionController::class, 'detachQuestion'])
    ->name('instructor.quizzes.detach_question');

// ========== QUIZ RESULTS ==========
Route::get('/instructor/quizzes/{quiz}/results', [InstructorQuizController::class, 'showResults'])
    ->name('instructor.quiz.results');

Route::get('/instructor/quiz-attempts/{attempt}/review', [InstructorQuizController::class, 'reviewAttempt'])
    ->name('instructor.quiz.review_attempt');

// ========== QUESTION BANK (Topik) ==========
Route::get('/instructor/question-topics', [QuestionTopicController::class, 'index'])
    ->name('instructor.question-bank.topics.index');

Route::get('/instructor/question-topics/create', [QuestionTopicController::class, 'create'])
    ->name('instructor.question-bank.topics.create');

Route::post('/instructor/question-topics', [QuestionTopicController::class, 'store'])
    ->name('instructor.question-bank.topics.store');

Route::get('/instructor/question-topics/{topic}/edit', [QuestionTopicController::class, 'edit'])
    ->name('instructor.question-bank.topics.edit');

Route::put('/instructor/question-topics/{topic}', [QuestionTopicController::class, 'update'])
    ->name('instructor.question-bank.topics.update');

Route::delete('/instructor/question-topics/{topic}', [QuestionTopicController::class, 'destroy'])
    ->name('instructor.question-bank.topics.destroy');

// ========== QUESTION BANK (Soal) ==========
Route::get('/instructor/question-topics/{topic}/questions', [QuestionController::class, 'index'])
    ->name('instructor.question-bank.questions.index');

Route::get('/instructor/question-topics/{topic}/questions/create', [QuestionController::class, 'create'])
    ->name('instructor.question-bank.questions.create');

Route::post('/instructor/question-topics/{topic}/questions', [QuestionController::class, 'store'])
    ->name('instructor.question-bank.questions.store');

Route::get('/questions/{question}/edit', [QuestionController::class, 'edit'])
    ->name('instructor.question-bank.questions.edit');

Route::put('/questions/{question}', [QuestionController::class, 'update'])
    ->name('instructor.question-bank.questions.update');

Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])
    ->name('instructor.question-bank.questions.destroy');

Route::post('questions/{question}/clone', [QuestionController::class, 'clone'])
    ->name('instructor.question-bank.questions.clone');

Route::patch('/questions/{question}/move', [QuestionController::class, 'move'])
    ->name('instructor.question-bank.questions.move');
```

---

# BAGIAN 10: FILE-FILE PENTING

## Controllers:

-   `app/Http/Controllers/Instructor/LessonController.php`
    -   Method: `create`, `store`, `edit`, `update`, `destroy`
-   `app/Http/Controllers/Instructor/QuizQuestionController.php`
    -   Method: `index`, `browseBank`, `attachQuestions`, `detachQuestion`
-   `app/Http/Controllers/Instructor/InstructorQuizController.php`
    -   Method: `showResults`, `reviewAttempt`
-   `app/Http/Controllers/Instructor/QuestionTopicController.php` (Bank Soal)
-   `app/Http/Controllers/Instructor/QuestionController.php` (Bank Soal)

## Models:

-   `app/Models/Quiz.php`
-   `app/Models/Lesson.php`
-   `app/Models/Question.php`
-   `app/Models/QuestionTopic.php`
-   `app/Models/QuestionOption.php`

## Views - Lesson:

-   `resources/views/instructor/lessons/index.blade.php`
-   `resources/views/instructor/lessons/create-quiz.blade.php`
-   `resources/views/instructor/lessons/edit-quiz.blade.php`

## Views - Quiz Questions:

-   `resources/views/instructor/quizzes/manage-questions.blade.php`
-   `resources/views/instructor/quizzes/browse-bank.blade.php`

## Views - Quiz Results:

-   `resources/views/instructor/quizzes/results/index.blade.php`
-   `resources/views/instructor/quizzes/results/show.blade.php`

---

# BAGIAN 11: KESIMPULAN

Sistem pengelolaan kuis untuk instructor di LMS2025 sudah sangat lengkap dengan fitur:

### ✅ Fitur Create:

-   Form komprehensif dengan berbagai setting
-   Timezone handling untuk jadwal
-   Validasi lengkap

### ✅ Fitur Edit:

-   Pre-filled data existing
-   Update semua field termasuk jadwal
-   JavaScript sync untuk title

### ✅ Fitur Manage Questions:

-   Integrasi dengan Bank Soal
-   Filter topik berdasarkan course
-   Attach/Detach soal dengan mudah
-   Tidak duplikat soal

### ✅ Fitur View Results:

-   Lihat semua student
-   Modal riwayat per student
-   Review jawaban detail

### ✅ Fitur Delete:

-   Cascade delete relasi
-   Konfirmasi sebelum hapus
-   Preservasi riwayat attempt

### ✅ Security:

-   Ownership check
-   Role-based access
-   Validasi input lengkap

### 🚀 Potensi Pengembangan:

-   Reorder questions dalam quiz
-   Duplicate quiz
-   Import/export soal
-   Statistik per soal
-   Randomize soal saat take quiz
-   Question pool dengan random selection
-   Timed sections
-   Auto-grading improvement

---

**Dokumen ini dibuat pada:** 2 Desember 2025
**Versi:** 1.0
**Status Sistem:** Production-ready
**Melengkapi:** DOKUMENTASI_ALUR_SISTEM_KUIS.md & DOKUMENTASI_ALUR_REVIEW_HASIL_KUIS.md
