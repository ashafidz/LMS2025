# Dokumentasi Manajemen Kuis - LMS2025

Dokumen ini berisi cuplikan kode dan penjelasan mengenai implementasi fitur menambah (add) dan mengubah (edit) kuis pada sistem LMS2025. Sistem ini menggunakan struktur **Polymorphic Relationship** di mana kuis dianggap sebagai salah satu tipe konten dari sebuah `Lesson`.

---

## 1. Definisi Route (Jalur URL)
Rute ini didefinisikan di dalam grup middleware `instructor` untuk memastikan hanya instruktur yang bisa mengaksesnya.

```php
// routes/web.php

Route::middleware(['auth', 'role:instructor'])->group(function () {
    // Menambah kuis baru (disimpan sebagai lesson di bawah modul tertentu)
    Route::post('/instructor/modules/{module}/lessons', [LessonController::class, 'store'])
        ->name('instructor.modules.lessons.store');

    // Mengedit kuis yang sudah ada
    Route::put('/instructor/lessons/{lesson}', [LessonController::class, 'update'])
        ->name('instructor.lessons.update');
});
```

---

## 2. Controller (Logika Bisnis)
Implementasi pada `LessonController` menggunakan transaksi database untuk menjamin integritas data antara tabel `lessons` dan tabel `quizzes`.

### Fungsi Store (Menambah)
```php
// app/Http/Controllers/Instructor/LessonController.php

public function store(Request $request, Module $module)
{
    $validated = $request->validate([
        'quiz_title'       => 'required|string|max:255',
        'quiz_description' => 'nullable|string',
        'pass_mark'        => 'required|integer|min:0|max:100',
        'time_limit'       => 'nullable|integer|min:1',
        'max_attempts'     => 'nullable|integer|min:1',
    ]);

    DB::transaction(function () use ($request, $module, $validated) {
        // 1. Buat entitas Quiz
        $quiz = Quiz::create([
            'title'        => $validated['quiz_title'],
            'description'  => $validated['quiz_description'],
            'pass_mark'    => $validated['pass_mark'],
            'time_limit'   => $validated['time_limit'],
            'max_attempts' => $validated['max_attempts'],
        ]);

        // 2. Buat entitas Lesson yang merujuk ke Quiz tadi
        $lastOrder = $module->lessons()->max('order') ?? 0;
        $quiz->lesson()->create([
            'module_id' => $module->id,
            'title'     => $validated['quiz_title'],
            'order'     => $lastOrder + 1,
        ]);
    });

    return redirect()->back()->with('success', 'Kuis berhasil dibuat.');
}
```

### Fungsi Update (Mengedit)
```php
public function update(Request $request, Lesson $lesson)
{
    $validated = $request->validate([
        'quiz_title' => 'required|string|max:255',
        // ... validasi lainnya
    ]);

    DB::transaction(function () use ($request, $lesson, $validated) {
        // Update data pada tabel lesson
        $lesson->update(['title' => $validated['quiz_title']]);

        // Update data pada tabel kuis melalui relasi lessonable
        $lesson->lessonable->update([
            'title'       => $validated['quiz_title'],
            'description' => $request->quiz_description,
            'pass_mark'   => $request->pass_mark,
            // ... field lainnya
        ]);
    });

    return redirect()->back()->with('success', 'Kuis berhasil diperbarui.');
}
```

---

## 3. View (Formulir Blade)
Struktur HTML dasar untuk formulir penambahan kuis.

```html
<!-- resources/views/instructor/lessons/create-quiz.blade.php -->

<form action="{{ route('instructor.modules.lessons.store', $module) }}" method="POST">
    @csrf
    <input type="hidden" name="lesson_type" value="quiz">

    <div class="form-group">
        <label>Judul Kuis</label>
        <input type="text" name="quiz_title" class="form-control" required>
    </div>

    <div class="form-group">
        <label>Deskripsi (Opsional)</label>
        <textarea name="quiz_description" class="form-control" rows="3"></textarea>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Nilai Kelulusan (%)</label>
                <input type="number" name="pass_mark" class="form-control" value="75" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Batas Waktu (Menit)</label>
                <input type="number" name="time_limit" class="form-control">
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Simpan Kuis</button>
</form>
```

---

## 4. Model & Relasi
Definisi relasi pada model Eloquent untuk mendukung Polymorphic Relationship.

```php
// app/Models/Lesson.php
public function lessonable()
{
    return $this->morphTo();
}

// app/Models/Quiz.php
public function lesson()
{
    return $this->morphOne(Lesson::class, 'lessonable');
}

public function securitySetting()
{
    return $this->hasOne(QuizSecuritySetting::class);
}
```

---

## 5. Keamanan Kuis (Quiz Security)
Bagian ini mengatur fitur pencegahan kecurangan seperti deteksi wajah, deteksi tab, dan pengacakan soal.

### Rute Keamanan
```php
// routes/web.php
Route::get('quiz/{quiz}/security', [QuizSecurityController::class, 'edit'])->name('instructor.quiz.security.edit');
Route::post('quiz/{quiz}/security', [QuizSecurityController::class, 'update'])->name('instructor.quiz.security.update');
```

### Logika Update Keamanan
```php
// app/Http/Controllers/Instructor/QuizSecurityController.php
public function update(Request $request, Quiz $quiz)
{
    $validated = $request->validate([
        'enable_camera_detection' => 'boolean',
        'enable_tab_detection' => 'boolean',
        'enable_question_shuffle' => 'boolean',
        'camera_violation_threshold' => 'integer|min:1',
        'tab_violation_threshold' => 'integer|min:1',
    ]);

    $quiz->securitySetting()->updateOrCreate(
        ['quiz_id' => $quiz->id],
        $validated
    );

    return response()->json(['success' => true, 'message' => 'Keamanan diperbarui!']);
}
```

### Fitur Keamanan yang Tersedia:
- **Deteksi Kamera**: Menggunakan MediaPipe untuk mendeteksi jika wajah hilang atau menoleh.
- **Deteksi Tab**: Mencatat setiap kali siswa keluar dari tab kuis (Page Visibility API).
- **Pengacakan Soal**: Menggunakan algoritma Fisher-Yates untuk mengacak urutan pertanyaan.
