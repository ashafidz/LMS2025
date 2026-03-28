# Teori & Teknis: Sistem Pengacakan Soal (Question Shuffle)

> Dokumentasi ini menjelaskan secara **teknis dan teoritis** bagaimana sistem pengacakan urutan soal kuis di LMS2025 bekerja — mulai dari pemicunya, algoritma yang digunakan, penyimpanan hasil acak, hingga cara soal dikirim ke siswa dan dinilai. Semua penjelasan berbasis langsung pada kode di `app/Services/QuizShuffleService.php`, `app/Http/Controllers/Student/StudentQuizController.php`, dan model terkait.

---

## Daftar Isi

1. [Gambaran Arsitektur](#1-gambaran-arsitektur)
2. [Kapan Shuffle Diaktifkan](#2-kapan-shuffle-diaktifkan)
3. [Fase 1 — Memulai Kuis (begin)](#3-fase-1--memulai-kuis-begin)
4. [Fase 2 — Algoritma Fisher-Yates Shuffle](#4-fase-2--algoritma-fisher-yates-shuffle)
5. [Fase 3 — Penyimpanan Urutan Acak ke Database](#5-fase-3--penyimpanan-urutan-acak-ke-database)
6. [Fase 4 — Mengambil Soal Sesuai Urutan Acak (take)](#6-fase-4--mengambil-soal-sesuai-urutan-acak-take)
7. [Fase 5 — Konsistensi Urutan Saat Refresh](#7-fase-5--konsistensi-urutan-saat-refresh)
8. [Fase 6 — Penilaian Jawaban (submit)](#8-fase-6--penilaian-jawaban-submit)
9. [Konfigurasi oleh Instruktur](#9-konfigurasi-oleh-instruktur)
10. [Skema Database](#10-skema-database)
11. [Diagram Alur Lengkap](#11-diagram-alur-lengkap)

---

## 1. Gambaran Arsitektur

Sistem shuffle berjalan sepenuhnya di **sisi server (Laravel backend)**. Browser siswa tidak mengetahui bahwa urutan soal sudah diacak — ia hanya menerima soal-soal yang sudah diurutkan ulang oleh server.

```
[Siswa klik "Mulai Kuis"]
         │
         ▼
[begin() — buat QuizAttempt baru]
         │
         ▼
[QuizShuffleService::generateShuffledOrder()]
         │  Fisher-Yates Algorithm
         │  Simpan {attempt_id, question_id, shuffled_order}
         ▼
[quiz_attempt_question_order] ← tabel DB (permanen per attempt)
         │
         ▼
[take() — siswa mengerjakan kuis]
         │  getShuffledQuestions() → ORDER BY shuffled_order
         ▼
[View: soal ditampilkan sesuai urutan acak]
         │
         ▼
[submit() — penilaian berdasarkan question_id, bukan urutan]
```

Titik kunci arsitektur ini:

- Urutan acak **disimpan ke database** saat attempt dibuat, bukan dihitung ulang setiap kali halaman dimuat.
- Penilaian tidak bergantung pada urutan soal — jawaban dipetakan melalui `question_id`.

---

## 2. Kapan Shuffle Diaktifkan

Fitur shuffle dikontrol oleh kolom `enable_question_shuffle` di tabel `quiz_security_settings`. Nilai default-nya adalah `false` (tidak aktif).

**Di model `Quiz`:**

```php
public function hasQuestionShuffle()
{
    return $this->securitySetting &&
        $this->securitySetting->enable_question_shuffle;
}
```

**Di `QuizShuffleService::generateShuffledOrder()`:**

```php
$securitySetting = $quiz->securitySetting;
$isShuffleEnabled = $securitySetting && $securitySetting->enable_question_shuffle;

if ($isShuffleEnabled) {
    $shuffledQuestions = $this->fisherYatesShuffle($questions);
} else {
    $shuffledQuestions = $questions; // urutan asli dari DB
}
```

> **Penting:** Bahkan jika shuffle **tidak aktif**, urutan soal tetap disimpan ke tabel `quiz_attempt_question_order` — hanya saja dengan urutan aslinya. Ini menjamin konsistensi: mekanisme pengambilan soal di `take()` selalu membaca dari tabel order yang sama, terlepas dari apakah shuffle aktif atau tidak.

---

## 3. Fase 1 — Memulai Kuis (begin)

**Kode:** `StudentQuizController::begin()` di `StudentQuizController.php`

Ketika siswa mengklik tombol "Mulai Kuis", method `begin()` dijalankan:

```php
// 1. Buat quiz attempt baru
$attempt = QuizAttempt::create([
    'quiz_id'    => $quiz->id,
    'student_id' => Auth::id(),
    'status'     => 'in_progress',
    'start_time' => now()
]);

// 2. Generate shuffled question order
$shuffleService = new QuizShuffleService();
$shuffleResult  = $shuffleService->generateShuffledOrder($attempt);

// 3. Jika gagal, rollback attempt dan tampilkan error
if (!$shuffleResult) {
    $attempt->delete();
    return redirect()->route('student.quiz.start', $quiz)
        ->with('error', 'Terjadi kesalahan saat memulai kuis. Silakan coba lagi.');
}

// 4. Redirect ke halaman pengerjaan
return redirect()->route('student.quiz.take', $attempt);
```

Urutan operasi penting di sini: `QuizAttempt` dibuat **lebih dulu**, baru kemudian shuffle dijalankan. Jika shuffle gagal, attempt dihapus (manual rollback) agar tidak ada attempt "kosong" yang tertinggal di database.

**Mode Preview (instruktur):**

Jika URL mengandung `?preview=true`, blok shuffle dilewati sepenuhnya. Soal ditampilkan dalam urutan asli. Tidak ada record yang disimpan ke database.

```php
if ($request['is_preview'] === 'true') {
    $quiz->load('questions.options');
    $attempt = new QuizAttempt(['quiz_id' => $quiz->id, 'id' => 0]);
    // → return view langsung, tanpa generateShuffledOrder()
}
```

---

## 4. Fase 2 — Algoritma Fisher-Yates Shuffle

**Kode:** `QuizShuffleService::fisherYatesShuffle()` di `QuizShuffleService.php`

Fisher-Yates adalah algoritma pengacakan array yang **tidak bias** — setiap permutasi dari array memiliki probabilitas yang sama untuk muncul. Inilah yang membedakannya dari pendekatan naif seperti `sort(() => Math.random() - 0.5)`.

### Pseudocode:

```
untuk i dari n-1 turun ke 1:
    j = bilangan acak antara 0 dan i (inklusif)
    tukar array[i] dengan array[j]
```

### Implementasi PHP:

```php
private function fisherYatesShuffle(array $array): array
{
    $count = count($array);

    for ($i = $count - 1; $i > 0; $i--) {
        $j = random_int(0, $i); // CSPRNG — cryptographically secure
        $temp     = $array[$i];
        $array[$i] = $array[$j];
        $array[$j] = $temp;
    }

    return $array;
}
```

> **Mengapa `random_int()` bukan `rand()`?**
> `random_int()` menggunakan sumber entropi kriptografis dari OS (CSPRNG), sehingga hasilnya tidak dapat diprediksi. `rand()` menggunakan PRNG (Pseudo-Random Number Generator) biasa yang bersifat deterministis dan bisa diprediksi jika seed-nya diketahui. Untuk konteks ujian (anti-cheating), `random_int()` lebih tepat.

### Contoh Langkah-Langkah (5 Soal):

Misalkan ID soal: `[10, 20, 30, 40, 50]`

| Iterasi | i   | j (acak) | Swap            | Array setelah swap                         |
| ------- | --- | -------- | --------------- | ------------------------------------------ |
| 1       | 4   | 1        | `arr[4]↔arr[1]` | `[10, **50**, 30, 40, **20**]`             |
| 2       | 3   | 3        | `arr[3]↔arr[3]` | `[10, 50, 30, **40**, 20]` (tidak berubah) |
| 3       | 2   | 0        | `arr[2]↔arr[0]` | `[**30**, 50, **10**, 40, 20]`             |
| 4       | 1   | 1        | `arr[1]↔arr[1]` | `[30, **50**, 10, 40, 20]` (tidak berubah) |

**Hasil akhir:** `[30, 50, 10, 40, 20]`

Urutan ini kemudian disimpan ke database dengan `shuffled_order` 1, 2, 3, 4, 5:

| shuffled_order | question_id |
| :------------: | :---------: |
|       1        |     30      |
|       2        |     50      |
|       3        |     10      |
|       4        |     40      |
|       5        |     20      |

---

## 5. Fase 3 — Penyimpanan Urutan Acak ke Database

**Kode:** `QuizShuffleService::generateShuffledOrder()` — bagian penyimpanan

Setelah array diacak, setiap soal beserta posisi barunya disimpan secara transaksional:

```php
DB::beginTransaction();

// Ambil semua question_id dari tabel pivot quiz_question
$questions = DB::table('quiz_question')
    ->where('quiz_id', $quiz->id)
    ->whereNull('deleted_at')
    ->pluck('question_id')
    ->toArray();

// Jalankan Fisher-Yates (jika shuffle aktif)
$shuffledQuestions = $isShuffleEnabled
    ? $this->fisherYatesShuffle($questions)
    : $questions;

// Simpan satu per satu
foreach ($shuffledQuestions as $index => $questionId) {
    QuizAttemptQuestionOrder::create([
        'attempt_id'     => $attempt->id,
        'question_id'    => $questionId,
        'shuffled_order' => $index + 1, // 1-based
    ]);
}

DB::commit();
```

Seluruh penyimpanan dibungkus dalam `DB::beginTransaction()` dan `DB::commit()`. Jika ada error di tengah proses, `DB::rollBack()` dipanggil dan tidak ada data parsial yang tersimpan.

**Constraint keunikan:**
Tabel `quiz_attempt_question_order` memiliki composite unique key `(attempt_id, question_id)` — satu soal hanya bisa muncul satu kali per attempt. Ini mencegah duplikasi akibat bug atau race condition.

---

## 6. Fase 4 — Mengambil Soal Sesuai Urutan Acak (take)

**Kode:** `StudentQuizController::take()` dan `QuizShuffleService::getShuffledQuestions()`

Ketika siswa membuka halaman pengerjaan kuis (atau me-refresh halaman):

```php
// Di controller take():
$shuffleService = new QuizShuffleService();
$questions = $shuffleService->getShuffledQuestions($attempt);

// Set ke relasi quiz agar kompatibel dengan view yang sudah ada
$attempt->quiz->setRelation('questions', $questions);
```

Di dalam service:

```php
public function getShuffledQuestions(QuizAttempt $attempt)
{
    return $attempt->questionOrders()        // JOIN ke quiz_attempt_question_order
        ->with('question.options')           // eager load soal + opsi jawaban
        ->orderBy('shuffled_order')          // urutkan berdasarkan hasil acak
        ->get()
        ->pluck('question');                 // ambil objek Question saja
}
```

Hasilnya adalah Eloquent Collection berisi objek `Question`, diurutkan persis sesuai `shuffled_order` yang tersimpan di database. View tidak perlu tahu apakah urutan ini acak atau tidak — ia hanya menerima koleksi soal dan merendernya.

---

## 7. Fase 5 — Konsistensi Urutan Saat Refresh

Salah satu keputusan desain terpenting: **urutan diacak hanya sekali, waktu `begin()`, dan hasilnya disimpan permanen**.

Ini berarti:

- Jika siswa me-refresh halaman → urutan soal tetap sama
- Jika browser crash dan siswa membuka kembali URL kuis → urutan tetap sama
- Jika jaringan terputus di tengah pengerjaan → saat kembali online, urutan tidak berubah

**Deteksi attempt yang sudah berjalan:**

Di `begin()`, sebelum membuat attempt baru, controller memeriksa apakah sudah ada attempt `in_progress`:

```php
$existingAttempt = QuizAttempt::where('quiz_id', $quiz->id)
    ->where('student_id', Auth::id())
    ->where('status', 'in_progress')
    ->first();

if ($existingAttempt) {
    return redirect()->route('student.quiz.take', $existingAttempt->id);
}
```

Jika ditemukan, siswa langsung diarahkan ke attempt yang sudah ada — tidak ada attempt baru yang dibuat, tidak ada shuffle baru yang dijalankan.

---

## 8. Fase 6 — Penilaian Jawaban (submit)

**Kode:** `StudentQuizController::submit()`

Ini adalah bagian yang sering menimbulkan pertanyaan: _apakah shuffle mempengaruhi penilaian?_ Jawabannya: **tidak sama sekali**.

Penilaian dilakukan berdasarkan `question_id`, bukan berdasarkan posisi soal:

```php
$quizQuestions = $attempt->quiz->questions; // semua soal dalam quiz

foreach ($quizQuestions as $question) {
    // Jawaban diindeks berdasarkan ID soal, bukan nomor urut:
    $userAnswerForQuestion = $userAnswers[$question->id] ?? null;

    if ($userAnswerForQuestion) {
        $isCorrect = $this->checkAnswer($question, $userAnswerForQuestion);
    }

    if ($isCorrect) {
        $totalScore += $question->score;
    }
}
```

Di view `take.blade.php`, setiap input jawaban menggunakan `question->id` sebagai key:

```html
<input
    type="radio"
    name="answers[{{ $question->id }}]"
    value="{{ $option->id }}"
/>
```

Berapapun urutan soal ditampilkan ke siswa, jawaban yang dikirim dalam form selalu dipetakan melalui `question_id` → tidak ada risiko penilaian tertukar akibat shuffle.

### Kalkulasi Skor Akhir:

```php
$maxPossibleScore = $attempt->quiz->questions->sum('score');
$percentageScore  = ($maxPossibleScore > 0)
    ? ($totalScore / $maxPossibleScore) * 100
    : 0;

$newStatus = $percentageScore >= $attempt->quiz->pass_mark ? 'passed' : 'failed';
$attempt->scaled_score = round($percentageScore, 2); // disimpan dalam skala 0-100
```

---

## 9. Konfigurasi oleh Instruktur

Fitur shuffle dikonfigurasi melalui halaman **Pengaturan Keamanan Kuis**, bersama fitur keamanan kuis lainnya.

| Parameter                 | Tipe    | Default | Fungsi                                      |
| ------------------------- | ------- | ------- | ------------------------------------------- |
| `enable_question_shuffle` | boolean | `false` | Aktifkan/nonaktifkan pengacakan urutan soal |

Model opt-in dipilih untuk shuffle (default OFF) — instruktur harus secara eksplisit mengaktifkannya. Berbeda dengan fitur kamera yang default ON. Alasannya: shuffle mempengaruhi pengalaman pengerjaan soal secara lebih fundamental, sehingga lebih aman membiarkannya mati secara default.

---

## 10. Skema Database

### Tabel `quiz_security_settings`

Menyimpan konfigurasi keamanan per-kuis, termasuk flag shuffle.

```
id
quiz_id              (FK → quizzes.id, UNIQUE)
enable_camera_detection  (bool, default false)
enable_tab_detection     (bool, default false)
enable_question_shuffle  (bool, default false)  ← flag shuffle
camera_violation_threshold
tab_violation_threshold
face_detection_interval_seconds
...kolom lainnya...
created_at, updated_at
```

### Tabel `quiz_attempt_question_order`

Menyimpan urutan soal yang telah diacak untuk setiap attempt. Bersifat **immutable** setelah dibuat — tidak ada update, hanya insert saat attempt baru dibuat dan cascade delete saat attempt dihapus.

```
id
attempt_id      (FK → quiz_attempts.id, CASCADE DELETE)
question_id     (FK → questions.id, CASCADE DELETE)
shuffled_order  (integer) — urutan 1-based hasil Fisher-Yates
created_at      (tidak ada updated_at)

UNIQUE (attempt_id, question_id)
INDEX  (attempt_id, shuffled_order)
```

**Mengapa ada index pada `(attempt_id, shuffled_order)`?**
Query `getShuffledQuestions()` selalu melakukan `WHERE attempt_id = ? ORDER BY shuffled_order`. Index composite ini memastikan query tersebut berjalan dengan O(log n) tanpa full table scan, yang penting ketika tabel tumbuh besar seiring bertambahnya attempt.

### Tabel `quiz_question` (pivot)

Tabel many-to-many antara kuis dan soal. Bukan tabel khusus shuffle, tapi digunakan sebagai sumber daftar soal saat `generateShuffledOrder()` mengambil `question_id`:

```php
$questions = DB::table('quiz_question')
    ->where('quiz_id', $quiz->id)
    ->whereNull('deleted_at')
    ->pluck('question_id')
    ->toArray();
```

---

## 11. Diagram Alur Lengkap

```
[Instruktur mengaktifkan shuffle di Pengaturan Keamanan Kuis]
         │
         ▼ quiz_security_settings.enable_question_shuffle = true


[Siswa klik "Mulai Kuis" → request POST /begin]
         │
         ├── Cek enrollment student di course ✓
         ├── Cek max_attempts belum tercapai ✓
         ├── Cek tidak ada attempt in_progress
         │   └── Ada? → redirect ke take() (tidak buat baru)
         │
         ▼

[Buat QuizAttempt baru]
  attempt = { quiz_id, student_id, status='in_progress', start_time=now() }
         │
         ▼

[QuizShuffleService::generateShuffledOrder(attempt)]
         │
         ├── DB::beginTransaction()
         │
         ├── Ambil semua question_id dari quiz_question
         │   WHERE quiz_id = ? AND deleted_at IS NULL
         │       → [10, 20, 30, 40, 50]
         │
         ├── enable_question_shuffle == true?
         │   ├── YES → Fisher-Yates Shuffle
         │   │         → [30, 50, 10, 40, 20]
         │   └── NO  → urutan asli tetap dipakai
         │             → [10, 20, 30, 40, 50]
         │
         ├── Simpan ke quiz_attempt_question_order:
         │   (attempt_id=7, question_id=30, shuffled_order=1)
         │   (attempt_id=7, question_id=50, shuffled_order=2)
         │   (attempt_id=7, question_id=10, shuffled_order=3)
         │   (attempt_id=7, question_id=40, shuffled_order=4)
         │   (attempt_id=7, question_id=20, shuffled_order=5)
         │
         ├── DB::commit()
         └── return true

         │ (jika false → attempt.delete() → error ke user)
         ▼

[Redirect → take(attempt_id)]


[StudentQuizController::take(attemptId)]
         │
         ├── Decode hashId → real attempt_id
         ├── Validasi enrollment + status='in_progress'
         ├── Hitung endTime dari start_time + time_limit
         │
         ├── QuizShuffleService::getShuffledQuestions(attempt)
         │       → JOIN quiz_attempt_question_order
         │         WHERE attempt_id = 7
         │         ORDER BY shuffled_order ASC
         │         EAGER LOAD question.options
         │       → Collection [Q30, Q50, Q10, Q40, Q20]
         │
         └── $attempt->quiz->setRelation('questions', $questions)
             → return view('student.quizzes.take', ...)


[View: Render soal ke siswa]
  Soal 1: Question 30
  Soal 2: Question 50
  Soal 3: Question 10
  Soal 4: Question 40
  Soal 5: Question 20
  (Setiap input: name="answers[question_id]")


[Siswa refresh halaman]
         │
         ▼ take() dipanggil lagi
         │
         └── getShuffledQuestions() membaca data yang SAMA dari DB
             → Urutan TETAP: [Q30, Q50, Q10, Q40, Q20] ✓


[Siswa submit → POST /submit]
         │
         ├── Terima $userAnswers = { '30': option_id, '50': option_id, ... }
         │   (key = question_id, bukan nomor urut)
         │
         ├── foreach ($quizQuestions as $question):
         │       $userAnswer = $userAnswers[$question->id]
         │       $isCorrect = checkAnswer($question, $userAnswer)
         │       $totalScore += $question->score if correct
         │
         ├── scaled_score = (totalScore / maxScore) × 100
         ├── status = passed/failed berdasarkan pass_mark
         └── Simpan attempt + redirect ke result
```
