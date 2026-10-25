<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\MonitoringLog;
use App\Models\PointHistory;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptIntegritySummary;
use App\Models\StudentAnswer;
use App\Services\BadgeService;
use App\Services\HashIdService;
use App\Services\LessonAccess;
use App\Services\PointService;
use App\Services\QuizShuffleService;
use App\Services\StudentWriteLock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class StudentQuizController extends Controller
{
    private const SUBMIT_GRACE_SECONDS = QuizAttempt::SUBMIT_GRACE_SECONDS;

    private const MODULE_LOCKED_MESSAGE = 'Modul ini masih terkunci. Kumpulkan poin yang dibutuhkan untuk membukanya.';

    // ... (metode start, begin, take, submit, result, dan helper lainnya tidak ada perubahan) ...
    public function start(Request $request, Quiz $quiz)
    {
        // Validasi: Pastikan siswa terdaftar di kursus ini
        $student = Auth::user();
        $course = $quiz->lesson->module->course;

        abort_unless(LessonAccess::canViewCourse($student, $course), 403, 'Anda tidak terdaftar di kursus ini.');

        if ($request->query('preview') === 'true') {
            abort_unless(LessonAccess::isManager(Auth::user(), $course), 403);
            $is_preview = true;
        } else {
            $is_preview = false;
        }

        if (! $is_preview && ! LessonAccess::isModuleUnlocked($student, $quiz->lesson->module)) {
            return redirect()->route('student.courses.show', $course->slug)->with('error', self::MODULE_LOCKED_MESSAGE);
        }

        $quiz->loadCount('questions');

        // Hitung percobaan yang sudah dilakukan
        $attemptCount = 0;
        if (Auth::check() && ! $is_preview) {
            $attemptCount = $student->quizAttempts()->where('quiz_id', $quiz->id)->count();
        }

        // Cari percobaan terakhir (baik lulus maupun gagal) untuk ditampilkan di view jika ada
        $lastAttempt = null; // DIUBAH: Inisialisasi sebagai null
        if (Auth::check() && ! $is_preview) { // DIUBAH: Tambahkan pengecekan ini
            $lastAttempt = QuizAttempt::where('student_id', $student->id)
                ->where('quiz_id', $quiz->id)
                ->orderBy('created_at', 'desc')
                ->first();
        }

        // ! logic untuk redirect paksa setelah lulus
        // // cari percobaan terakhir apakah student ini sudah pernah mengerjakan dan lulus
        // $lastAttempt = QuizAttempt::where('student_id', $student->id)
        //     ->where('quiz_id', $quiz->id)
        //     ->where('status', 'passed')
        //     ->orderBy('created_at', 'desc')
        //     ->first();

        // if ($lastAttempt) {
        //     return redirect()->route('student.quiz.result', $lastAttempt->id);
        // }

        // Cari percobaan (attempt) yang sedang berlangsung untuk kuis ini oleh siswa yang login.
        $existingAttempt = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('student_id', Auth::id())
            ->where('status', 'in_progress')
            ->first();

        // Jika ditemukan attempt yang masih 'in_progress'...
        if ($existingAttempt) {
            // ...langsung alihkan (redirect) pengguna ke halaman pengerjaan kuis yang sudah ada.
            return redirect()->route('student.quiz.take', $existingAttempt->id);
        }

        // LOGIKA BARU: Cek ketersediaan kuis berdasarkan jadwal
        $now = Carbon::now();
        $isAvailable = true;
        $availabilityMessage = '';

        if ($quiz->available_from && $now->isBefore($quiz->available_from)) {
            $isAvailable = false;
            $availabilityMessage = 'Kuis ini akan tersedia pada '.$quiz->available_from->format('d F Y, H:i');
        }
        if ($quiz->available_to && $now->isAfter($quiz->available_to)) {
            $isAvailable = false;
            $availabilityMessage = 'Waktu pengerjaan kuis ini telah berakhir pada '.$quiz->available_to->format('d F Y, H:i');
        }

        // $quiz->loadCount('questions');
        // $is_preview = $request->query('preview') === 'true' && Auth::check();
        return view('student.quizzes.start', compact('quiz', 'is_preview', 'attemptCount', 'lastAttempt', 'isAvailable', 'availabilityMessage'));
    }

    public function begin(Request $request, Quiz $quiz)
    {
        // Validasi: Pastikan siswa terdaftar di kursus ini
        $student = Auth::user();
        $course = $quiz->lesson->module->course;

        abort_unless(LessonAccess::canViewCourse($student, $course), 403, 'Anda tidak terdaftar di kursus ini.');

        if ($request['is_preview'] !== 'true' && ! LessonAccess::isModuleUnlocked($student, $quiz->lesson->module)) {
            return redirect()->route('student.courses.show', $course->slug)->with('error', self::MODULE_LOCKED_MESSAGE);
        }

        // Cek batas, attempt berjalan, dan pembuatan attempt dikunci per siswa agar klik ganda tidak membuat dua attempt
        $user = Auth::user();
        [$outcome, $payload] = StudentWriteLock::run($user, function () use ($request, $quiz, $user, $course, $student) {
            // Attempt yang masih berjalan dilanjutkan dulu (sebelum cek batas), agar klik ganda tidak memunculkan pesan batas maksimal
            // Cari percobaan (attempt) yang sedang berlangsung untuk kuis ini oleh siswa yang login.
            $existingAttempt = QuizAttempt::where('quiz_id', $quiz->id)
                ->where('student_id', Auth::id())
                ->where('status', 'in_progress')
                ->first();

            // Jika ditemukan attempt yang masih 'in_progress'...
            if ($existingAttempt) {
                // ...langsung alihkan (redirect) pengguna ke halaman pengerjaan kuis yang sudah ada.
                return ['take', $existingAttempt];
            }

            // LOGIKA BARU: Cek batas pengerjaan sebelum memulai
            if ($quiz->max_attempts) {
                $attemptCount = $user->quizAttempts()->where('quiz_id', $quiz->id)->count();
                if ($attemptCount >= $quiz->max_attempts) {
                    return ['error', 'Anda telah mencapai batas maksimal pengerjaan untuk kuis ini.'];
                }
            }

            // Jadwal kuis ditegakkan di server, bukan hanya disembunyikan di halaman mulai
            if ($request['is_preview'] !== 'true') {
                if ($quiz->available_from && now()->isBefore($quiz->available_from)) {
                    return ['error', 'Kuis ini akan tersedia pada '.$quiz->available_from->format('d F Y, H:i')];
                }
                if ($quiz->available_to && now()->isAfter($quiz->available_to)) {
                    return ['error', 'Waktu pengerjaan kuis ini telah berakhir pada '.$quiz->available_to->format('d F Y, H:i')];
                }
            }

            // preview mode (tidak generate shuffle)
            if ($request['is_preview'] === 'true') {
                abort_unless(LessonAccess::isManager($student, $course), 403);

                return ['preview', null];
            }

            // create quize attampt
            // $attempt = QuizAttempt::create([
            //     'quiz_id' => $quiz->id,
            //     'student_id' => Auth::id(),
            //     'status' => 'in_progress',
            //     'start_time' => now()
            // ]);

            // =====================================================================
            // FISHER-YATES SHUFFLE INTEGRATION
            // =====================================================================

            // Create quiz attempt
            $attempt = QuizAttempt::create([
                'quiz_id' => $quiz->id,
                'student_id' => Auth::id(),
                'status' => 'in_progress',
                'start_time' => now(),
            ]);

            // Generate shuffled question order (Fisher-Yates Algorithm)
            $shuffleService = new QuizShuffleService;
            $shuffleResult = $shuffleService->generateShuffledOrder($attempt);

            if (! $shuffleResult) {
                // Jika shuffle gagal, rollback dan tampilkan error
                $attempt->delete();
                Log::error('Failed to generate shuffled order for attempt', [
                    'attempt_id' => $attempt->id,
                    'quiz_id' => $quiz->id,
                    'student_id' => Auth::id(),
                ]);

                return ['error', 'Terjadi kesalahan saat memulai kuis. Silakan coba lagi.'];
            }

            // =====================================================================
            // END FISHER-YATES INTEGRATION
            // =====================================================================

            return ['take', $attempt];
        });

        if ($outcome === 'error') {
            return redirect()->route('student.quiz.start', $quiz)->with('error', $payload);
        }

        if ($outcome === 'preview') {
            $quiz->load('questions.options');
            $attempt = new QuizAttempt(['quiz_id' => $quiz->id, 'id' => 0]);
            $attempt->setRelation('quiz', $quiz);

            return view('student.quizzes.take', [
                'attempt' => $attempt,
                'is_preview' => true,
                'endTime' => null,
                'serverNowMs' => null,
                'endTimeMs' => null,
            ]);
        }

        return redirect()->route('student.quiz.take', $payload);
    }

    public function take($attemptId)
    {
        $attemptId = HashIdService::decode($attemptId) ?? $attemptId;
        $attempt = QuizAttempt::with('quiz.questions.options')->findOrFail($attemptId);

        abort_unless($attempt->student_id == Auth::id(), 403);

        // Validasi: Pastikan siswa terdaftar di kursus ini
        $student = Auth::user();
        $course = $attempt->quiz->lesson->module->course;
        abort_unless(LessonAccess::canViewCourse($student, $course), 403, 'Anda tidak terdaftar di kursus ini.');

        // $attempt = QuizAttempt::findOrFail($attemptId);
        if ($attempt->status !== 'in_progress') {
            return redirect()->route('student.quiz.result', $attempt)->with('info', 'Anda sudah menyelesaikan kuis ini.');
        }

        // --- TAMBAHKAN LOGIKA INI ---
        $endTime = null;
        // Pastikan ada batas waktu dan start_time tidak kosong
        if ($attempt->quiz->time_limit == 0 || $attempt->quiz->time_limit == null) {
            $endTime = null;
        } elseif ($attempt->quiz->time_limit > 0 && $attempt->start_time) {

            // Hitung waktu berakhir: start_time + time_limit (dalam menit)
            // Carbon akan otomatis menangani objek DateTime
            $endTime = $attempt->start_time->copy()->addMinutes($attempt->quiz->time_limit);
        }
        // --- AKHIR LOGIKA TAMBAHAN ---

        // =====================================================================
        // LOAD QUESTIONS BERDASARKAN SHUFFLED ORDER
        // =====================================================================

        // Load questions sesuai urutan yang sudah diacak
        $shuffleService = new QuizShuffleService;
        $questions = $shuffleService->getShuffledQuestions($attempt);

        // Set questions ke quiz relation (agar tetap kompatibel dengan view existing)
        $attempt->quiz->setRelation('questions', $questions);

        // =====================================================================
        // END SHUFFLED QUESTIONS LOADING
        // =====================================================================

        // Jangan load ulang questions karena sudah di-shuffle di atas
        // $attempt->load('quiz.questions.options'); // DIHAPUS - akan menimpa urutan shuffle

        $is_preview = false;

        return view('student.quizzes.take', [
            'attempt' => $attempt,
            'is_preview' => false,
            'endTime' => $endTime ? $endTime->toIso8601String() : null, // Kirim sebagai string ISO 8601
            // Jam server dan batas waktu dalam milidetik. Browser menghitung selisih jam perangkat terhadap server
            // saat halaman dimuat, jadi jam perangkat yang salah atau perangkat yang sempat sleep tidak memengaruhi sisa waktu.
            'serverNowMs' => $endTime ? now()->getTimestampMs() : null,
            'endTimeMs' => $endTime ? $endTime->getTimestampMs() : null,
        ]);
    }

    public function submit(Request $request, $attemptId)
    {
        $userAnswers = $request->input('answers', []);
        $totalScore = 0;
        $is_preview = $request->input('is_preview') === 'true';
        if ($is_preview) {
            $quiz = Quiz::with('questions.options')->findOrFail($request->input('quiz_id_preview'));
            $course = $quiz->lesson->module->course;
            abort_unless(LessonAccess::isManager(Auth::user(), $course), 403);

            $attempt = new QuizAttempt(['quiz_id' => $quiz->id]);
            $attempt->setRelation('quiz', $quiz);
        } else {
            $attemptId = HashIdService::decode($attemptId) ?? $attemptId;
            $attempt = QuizAttempt::findOrFail($attemptId);

            abort_unless($attempt->student_id == Auth::id(), 403);

            // Validasi: Pastikan siswa terdaftar di kursus ini
            $student = Auth::user();
            $course = $attempt->quiz->lesson->module->course;
            abort_unless(LessonAccess::canViewCourse($student, $course), 403, 'Anda tidak terdaftar di kursus ini.');

            // Attempt yang sudah selesai (misalnya submit ulang lewat tombol back) diarahkan ke halaman hasil
            if ($attempt->status != 'in_progress') {
                return redirect()->route('student.quiz.result', $attempt->id);
            }
        }
        // Muat opsi semua soal sekaligus agar pengecekan jawaban tidak query per soal
        $attempt->quiz->loadMissing('questions.options');
        $quizQuestions = $attempt->quiz->questions;
        $studentAnswersCollection = collect();
        $grade = function () use ($quizQuestions, $userAnswers, $attempt, &$totalScore, $is_preview, &$studentAnswersCollection) {
            $answerRows = [];
            foreach ($quizQuestions as $question) {
                $isCorrect = false;
                $userAnswerForQuestion = $userAnswers[$question->id] ?? null;
                if ($userAnswerForQuestion) {
                    $isCorrect = $this->checkAnswer($question, $userAnswerForQuestion);
                }
                if ($isCorrect) {
                    $totalScore += $question->score;
                }
                if (! $is_preview) {
                    array_push($answerRows, ...$this->buildStudentAnswerRows($attempt, $question, $userAnswerForQuestion, $isCorrect));
                } else {
                    $this->collectStudentAnswers($studentAnswersCollection, $question, $userAnswerForQuestion, $isCorrect);
                }
            }
            if (! empty($answerRows)) {
                // Simpan semua jawaban siswa dalam satu query
                StudentAnswer::insert($answerRows);
            }
            if (! $is_preview) {
                // 1. Kalkulasi Skor dan Status Kelulusan (Tidak berubah)
                $maxPossibleScore = $attempt->quiz->questions->sum('score');
                $percentageScore = ($maxPossibleScore > 0) ? ($totalScore / $maxPossibleScore) * 100 : 0;
                $newStatus = $percentageScore >= $attempt->quiz->pass_mark ? 'passed' : 'failed';

                // Batas waktu ditegakkan di server: lewat waktu (plus toleransi) dan kuis tidak mengizinkan
                // jawaban terlambat -> jawaban tetap disimpan untuk instruktur, tetapi tidak lulus.
                $timeLimit = (int) $attempt->quiz->time_limit;
                $failedByDeadline = false;
                if ($timeLimit > 0 && ! $attempt->quiz->allow_exceed_time_limit) {
                    $deadline = $attempt->start_time->copy()->addMinutes($timeLimit)->addSeconds(self::SUBMIT_GRACE_SECONDS);
                    if (now()->greaterThan($deadline)) {
                        $newStatus = 'failed';
                        $failedByDeadline = true;
                    }
                }

                // Status dikeluarkan karena pelanggaran dihitung dari log server, bukan dari isian browser
                $integrity = QuizAttemptIntegritySummary::where('attempt_id', $attempt->id)->first();
                $security = $attempt->quiz->securitySetting;
                $expelledByViolation = (bool) ($integrity && $security && (
                    ($security->enable_tab_detection && $integrity->total_tab_switches >= ($security->tab_violation_threshold ?? 5))
                    || ($security->enable_camera_detection && $integrity->total_face_violations >= ($security->camera_violation_threshold ?? 10))
                ));

                // 2. Set dan Simpan Hasil Attempt (Tidak berubah)
                $attempt->score = $totalScore;
                $attempt->scaled_score = round($percentageScore, 2); // Simpan skor skala 0-100 langsung di database
                $attempt->status = $newStatus;
                $attempt->end_time = now();
                $attempt->expelled_by_violation = $expelledByViolation;
                // Dicatat sekarang agar perubahan batas waktu kuis sesudahnya tidak mengubah alasan gagalnya
                $attempt->failed_by_deadline = $failedByDeadline;
                $attempt->save();

                // // 3. Cek Kondisi Tambahan
                // $quizAllowExceedTimeLimit = (bool) $attempt->quiz->allow_exceed_time_limit;
                // $quizExceededTimeLimit = $attempt->end_time > $attempt->start_time->addMinutes($attempt->quiz->time_limit);

                // ======================================================================
                // PERBAIKAN DITERAPKAN DI SINI
                // ======================================================================
                // 3. Cek Kondisi Tambahan dengan Logika yang Diperbaiki
                $timeLimitInMinutes = $attempt->quiz->time_limit;
                $quizExceededTimeLimit = false; // Default: false

                // Lakukan pengecekan HANYA jika ada batas waktu yang valid (> 0)
                if ($timeLimitInMinutes > 0) {
                    $quizExceededTimeLimit = $attempt->end_time > $attempt->start_time->copy()->addMinutes($timeLimitInMinutes)->addSeconds(self::SUBMIT_GRACE_SECONDS);
                }

                $quizAllowExceedTimeLimit = (bool) $attempt->quiz->allow_exceed_time_limit;
                // ======================================================================
                // AKHIR DARI PERBAIKAN
                // ======================================================================

                // Cek apakah siswa sudah pernah DAPAT POIN untuk lesson yang berisi kuis ini.
                // Kita gunakan model PointHistory .
                $hasEarnedPointsBefore = PointHistory::where('user_id', Auth::id())
                    ->where('lesson_id', $attempt->quiz->lesson->id)
                    ->exists();

                // 4. Logika Pemberian Poin (dengan pengecekan yang sudah disesuaikan)
                // Poin diberikan HANYA JIKA:
                // a. Statusnya 'passed'
                // b. Belum pernah dapat poin untuk lesson ini sebelumnya
                // c. DAN TIDAK dalam kondisi lewat waktu
                if (
                    $newStatus === 'passed' &&
                    ! $hasEarnedPointsBefore && // <-- Menggunakan variabel baru dari pengecekan PointHistory
                    ! $quizExceededTimeLimit
                ) {
                    // KODE BARU (Menggunakan Named Arguments, urutan tidak masalah)
                    PointService::addPoints(
                        user: Auth::user(),
                        course: $attempt->quiz->lesson->module->course,
                        activity: 'pass_quiz', // Jelas ini untuk 'activity'
                        lesson: $attempt->quiz->lesson, // Jelas ini untuk 'lesson'
                        description_meta: $attempt->quiz->title
                    );

                }

                // Lulus menandai lesson selesai, walaupun poinnya sudah pernah diberikan (misalnya dari
                // sinkronisasi admin). Lewat waktu tetap dihitung selesai jika kuis mengizinkannya.
                // Poin tetap hanya sekali dan hanya jika tepat waktu (blok di atas).
                if ($newStatus === 'passed' && (! $quizExceededTimeLimit || $attempt->quiz->allow_exceed_time_limit)) {
                    Auth::user()->completedLessons()->syncWithoutDetaching($attempt->quiz->lesson->id);
                }

                // 5. Logika Setelah Lulus (Tidak berubah)
                if ($newStatus === 'passed') {
                    // Cek perolehan badge (lulus ulang tanpa poin tetap bisa mengubah hitungan kuis sempurna)
                    BadgeService::queueFor(Auth::user());
                }
            }
        };

        if ($is_preview) {
            DB::transaction($grade);
        } else {
            // Dikunci per siswa: submit ganda (klik ganda, auto-submit + submit manual) hanya dinilai sekali
            $alreadySubmitted = StudentWriteLock::run(Auth::user(), function () use ($attempt, $grade) {
                // Cek ulang di dalam kunci: request lain mungkin sudah menyelesaikan attempt ini
                if (QuizAttempt::whereKey($attempt->id)->value('status') !== 'in_progress') {
                    return true;
                }
                $grade();

                return false;
            });

            if ($alreadySubmitted) {
                return redirect()->route('student.quiz.result', $attempt->id);
            }
        }

        if ($is_preview) {
            $maxPossibleScore = $attempt->quiz->questions->sum('score');
            $percentageScore = ($maxPossibleScore > 0) ? ($totalScore / $maxPossibleScore) * 100 : 0;
            $attempt->score = $totalScore;
            $attempt->status = $percentageScore >= $attempt->quiz->pass_mark ? 'passed' : 'failed';
            $attempt->setRelation('answers', $studentAnswersCollection->mapInto(\App\Models\StudentAnswer::class));
            // quiz max score
            // $maxPossibleScore = $attempt->quiz->questions->sum('score');
            // quiz minimum score, not percentage
            $minimumScore = $maxPossibleScore * ($attempt->quiz->pass_mark / 100);

            // Hitung nilai student dalam skala 0-100 (sama seperti di InstructorRecapController)
            $studentScoreScaled = ($maxPossibleScore > 0) ? min(100, round(($attempt->score / $maxPossibleScore) * 100, 2)) : 0;
            $minimumScoreScaled = $attempt->quiz->pass_mark; // pass_mark sudah dalam bentuk persentase 0-100

            return view('student.quizzes.result', compact('attempt', 'is_preview', 'maxPossibleScore', 'minimumScore', 'studentScoreScaled', 'minimumScoreScaled'));
        }

        return redirect()->route('student.quiz.result', $attempt->id);
    }

    public function result($attemptId)
    {
        $attemptId = HashIdService::decode($attemptId) ?? $attemptId;
        $attempt = QuizAttempt::findOrFail($attemptId);

        abort_unless($attempt->student_id == Auth::id(), 403);

        // Validasi: Pastikan siswa terdaftar di kursus ini
        $student = Auth::user();
        $course = $attempt->quiz->lesson->module->course;
        abort_unless(LessonAccess::canViewCourse($student, $course), 403, 'Anda tidak terdaftar di kursus ini.');

        if ($attempt->status === 'in_progress') {
            return redirect()->route('student.quiz.take', $attempt)->with('error', 'Anda harus menyelesaikan kuis terlebih dahulu.');
        }
        $attempt->load(['quiz.questions.options', 'answers']);

        // quiz max score
        $maxPossibleScore = $attempt->quiz->questions->sum('score');
        // quiz minimum score, not percentage
        $minimumScore = $maxPossibleScore * ($attempt->quiz->pass_mark / 100);

        // Gunakan scaled_score dari database, fallback ke kalkulasi manual untuk data lama
        $studentScoreScaled = $attempt->scaled_score
            ?? (($maxPossibleScore > 0) ? min(100, round(($attempt->score / $maxPossibleScore) * 100, 2)) : 0);
        $minimumScoreScaled = $attempt->quiz->pass_mark; // pass_mark sudah dalam bentuk persentase 0-100

        return view('student.quizzes.result', ['attempt' => $attempt, 'is_preview' => false, 'maxPossibleScore' => $maxPossibleScore, 'minimumScore' => $minimumScore, 'studentScoreScaled' => $studentScoreScaled, 'minimumScoreScaled' => $minimumScoreScaled]);
    }

    // public function checkAnswerAjax(Request $request)
    // {
    //     $validated = $request->validate(['question_id' => 'required|exists:questions,id', 'answer' => 'present']);
    //     $question = Question::with('options')->find($validated['question_id']);
    //     $userAnswer = $validated['answer'];
    //     $isCorrect = $this->checkAnswer($question, $userAnswer);

    //     return response()->json(['correct' => $isCorrect, 'explanation' => $isCorrect ? $question->explanation : null]);
    // }

    /**
     * Memeriksa apakah jawaban pengguna benar.
     */
    private function checkAnswer(Question $question, $userAnswer): bool
    {
        switch ($question->question_type) {
            case 'multiple_choice_single':
            case 'true_false':
                $correctOption = $question->options->firstWhere('is_correct', true);

                return $correctOption && $correctOption->id == $userAnswer;

            case 'multiple_choice_multiple':
                $correctOptions = $question->options->where('is_correct', true)->pluck('id')->sort()->values()->toArray();
                $userOptions = collect((array) $userAnswer)->map(fn ($id) => (int) $id)->sort()->values()->toArray();

                return $correctOptions == $userOptions;

            case 'drag_and_drop':
                // Ambil semua opsi yang merupakan kunci jawaban (memiliki gap_id)
                $correctAnswers = $question->options->whereNotNull('correct_gap_identifier');

                if ($correctAnswers->isEmpty() && (is_null($userAnswer) || empty($userAnswer))) {
                    return true;
                }
                if (! is_array($userAnswer)) {
                    return false;
                }

                // Jumlah jawaban yang diberikan harus sama dengan jumlah blank yang ada
                if ($correctAnswers->count() != count($userAnswer)) {
                    return false;
                }

                // Cocokkan setiap jawaban siswa dengan kunci jawaban
                foreach ($correctAnswers as $correctAnswer) {
                    $blankId = $correctAnswer->correct_gap_identifier;
                    // Jika ada blank yang tidak dijawab, atau jawabannya salah, kembalikan false
                    if (! isset($userAnswer[$blankId]) || $userAnswer[$blankId] != $correctAnswer->id) {
                        return false;
                    }
                }

                // Jika semua blank yang ada di kunci jawaban berhasil dicocokkan, maka benar
                return true;
        }

        return false;
    }

    private function buildStudentAnswerRows(QuizAttempt $attempt, Question $question, $userAnswer, bool $isCorrect): array
    {
        $now = now();
        $row = fn ($optionId) => [
            'attempt_id' => $attempt->id,
            'question_id' => $question->id,
            'selected_option_id' => $optionId,
            'is_correct' => $isCorrect,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        if (is_array($userAnswer)) {
            $rows = [];
            foreach ($userAnswer as $key => $value) {
                if (! empty($value)) {
                    $rows[] = $row($value);
                }
            }

            return $rows;
        }

        return $userAnswer ? [$row($userAnswer)] : [];
    }

    private function collectStudentAnswers($collection, Question $question, $userAnswer, bool $isCorrect)
    {
        if (is_array($userAnswer)) {
            foreach ($userAnswer as $key => $value) {
                if (! empty($value)) {
                    $collection->push(['question_id' => $question->id, 'selected_option_id' => $value, 'is_correct' => $isCorrect]);
                }
            }
        } elseif ($userAnswer) {
            $collection->push(['question_id' => $question->id, 'selected_option_id' => $userAnswer, 'is_correct' => $isCorrect]);
        }
    }

    /**
     * Mencatat pelanggaran pindah tab (Tab Switching)
     */
    public function logTabViolation(Request $request, $attemptId)
    {
        try {
            $attemptId = HashIdService::decode($attemptId) ?? $attemptId;
            // Cari data percobaan kuis siswa
            $attempt = QuizAttempt::with('quiz.securitySetting')->findOrFail($attemptId);

            // Validasi: Pastikan yang mengirim log adalah siswa yang sedang mengerjakan kuis tersebut
            if ($attempt->student_id != Auth::id()) {
                return response()->json(['error' => 'Tidak diizinkan'], 403);
            }

            // Validasi: Pastikan kuis masih dalam status aktif (in_progress)
            if ($attempt->status !== 'in_progress') {
                return response()->json(['error' => 'Kuis tidak sedang berlangsung'], 400);
            }

            // Cek apakah fitur deteksi tab aktif untuk kuis ini
            $securitySetting = $attempt->quiz->securitySetting;
            if (! $securitySetting || ! $securitySetting->enable_tab_detection) {
                return response()->json(['message' => 'Deteksi tab tidak aktif'], 200);
            }

            // Simpan detail log pelanggaran ke tabel 'monitoring_logs'
            MonitoringLog::create([
                'attempt_id' => $attempt->id,
                'violation_type' => 'tab_switch', // Tipe: Pindah tab
                'violation_timestamp' => now(), // Waktu kejadian
            ]);

            // Ambil atau buat ringkasan integritas (summary) untuk percobaan kuis ini
            $summary = QuizAttemptIntegritySummary::firstOrCreate(
                ['attempt_id' => $attempt->id],
                [
                    'total_tab_switches' => 0,
                    'total_face_violations' => 0,
                ]
            );

            // Tambah jumlah pelanggaran tab (+1) secara otomatis
            $summary->increment('total_tab_switches');

            // Cek apakah jumlah pelanggaran sudah melebihi batas (threshold) yang diatur instruktur
            $threshold = $securitySetting->tab_violation_threshold ?? 5;
            $shouldBlock = $summary->total_tab_switches >= $threshold;

            // Kirim respon balik ke browser (apakah kuis harus diblokir atau sekadar peringatan)
            return response()->json([
                'success' => true,
                'violation_count' => $summary->total_tab_switches, // Total pelanggaran saat ini
                'threshold' => $threshold, // Batas maksimal
                'should_block' => $shouldBlock, // Status: Blokir atau tidak
                'message' => $shouldBlock
                    ? 'Anda telah melebihi batas perpindahan tab yang diizinkan!'
                    : "Peringatan: Anda telah pindah tab {$summary->total_tab_switches} kali. Batas maksimal: {$threshold} kali.",
            ]);
        } catch (\Exception $e) {
            // Jika ada kesalahan teknis, catat ke log server
            Log::error('Gagal mencatat pelanggaran tab', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'attempt_id' => $attemptId,
            ]);

            return response()->json([
                'error' => 'Gagal mencatat pelanggaran',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mencatat pelanggaran kamera (Deteksi Wajah / Arah Pandangan)
     */
    public function logCameraViolation(Request $request, $attemptId)
    {
        try {
            $attemptId = HashIdService::decode($attemptId) ?? $attemptId;
            // Cari data percobaan kuis siswa
            $attempt = QuizAttempt::with('quiz.securitySetting')->findOrFail($attemptId);

            // Validasi: Pastikan yang mengirim data adalah pemilik sesi kuis
            if ($attempt->student_id != Auth::id()) {
                return response()->json(['error' => 'Tidak diizinkan'], 403);
            }

            // Validasi: Pastikan kuis sedang berlangsung
            if ($attempt->status !== 'in_progress') {
                return response()->json(['error' => 'Kuis tidak sedang berlangsung'], 400);
            }

            // Cek apakah fitur deteksi kamera aktif
            $securitySetting = $attempt->quiz->securitySetting;
            if (! $securitySetting || ! $securitySetting->enable_camera_detection) {
                return response()->json(['message' => 'Deteksi kamera tidak aktif'], 200);
            }

            // Validasi tipe pelanggaran yang dikirim dari browser
            $violationType = $request->input('violation_type');
            $validTypes = ['face_not_detected', 'look_left', 'look_right', 'look_down', 'look_up'];

            if (! in_array($violationType, $validTypes)) {
                return response()->json(['error' => 'Tipe pelanggaran tidak valid'], 400);
            }

            // Proses upload bukti screenshot jika dilampirkan
            $screenshotPath = null;
            // Screenshot yang bukan gambar atau terlalu besar diabaikan; pelanggarannya tetap dicatat
            $screenshotIsValid = $request->hasFile('screenshot') && Validator::make(
                $request->only('screenshot'),
                ['screenshot' => ['file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:1024']]
            )->passes();

            if ($screenshotIsValid) {
                try {
                    $screenshot = $request->file('screenshot');

                    // Tentukan folder penyimpanan berdasarkan ID pengerjaan kuis
                    $directory = 'screenshots/violations/'.$attempt->id;

                    // Buat nama file unik (tipe_waktu_id.jpg)
                    $filename = $violationType.'_'.time().'_'.uniqid().'.jpg';

                    // Simpan file ke storage privat (dibuka lewat FileDownloadController)
                    $screenshotPath = $screenshot->storeAs($directory, $filename, 'local');
                } catch (\Exception $e) {
                    Log::error('Gagal mengunggah screenshot', ['error' => $e->getMessage()]);
                    // Lanjut tanpa screenshot jika upload gagal
                }
            }

            // Simpan log detail ke database
            MonitoringLog::create([
                'attempt_id' => $attempt->id,
                'violation_type' => $violationType, // Jenis gerakan/pelanggaran
                'violation_timestamp' => now(), // Waktu kejadian
                'duration_seconds' => $request->input('duration_seconds'), // Berapa lama kejadian berlangsung
                'screenshot_path' => $screenshotPath, // Path bukti foto
                'additional_data' => $request->input('additional_data'),
            ]);

            // Ambil atau inisialisasi ringkasan integritas (summary)
            $summary = QuizAttemptIntegritySummary::firstOrCreate(
                ['attempt_id' => $attempt->id],
                [
                    'total_tab_switches' => 0,
                    'total_face_violations' => 0,
                    'face_not_detected_count' => 0,
                    'look_left_count' => 0,
                    'look_right_count' => 0,
                    'look_down_count' => 0,
                    'look_up_count' => 0,
                ]
            );

            // Tambah counter khusus untuk tipe pelanggaran tertentu (misal: menoleh kiri)
            $fieldMap = [
                'face_not_detected' => 'face_not_detected_count',
                'look_left' => 'look_left_count',
                'look_right' => 'look_right_count',
                'look_down' => 'look_down_count',
                'look_up' => 'look_up_count',
            ];

            // Tambah counter tipe pelanggaran dan total pelanggaran kamera dalam satu query
            $field = $fieldMap[$violationType];
            QuizAttemptIntegritySummary::whereKey($summary->getKey())
                ->incrementEach([$field => 1, 'total_face_violations' => 1], ['updated_at' => now()]);
            $summary->{$field} = (int) $summary->{$field} + 1;
            $summary->total_face_violations = (int) $summary->total_face_violations + 1;
            $summary->syncOriginalAttributes([$field, 'total_face_violations']);

            // Cek apakah total pelanggaran kamera melebihi batas yang diizinkan
            $threshold = $securitySetting->camera_violation_threshold ?? 10;
            $shouldBlock = $summary->total_face_violations >= $threshold;

            return response()->json([
                'success' => true,
                'violation_count' => $summary->total_face_violations,
                'violation_breakdown' => [ // Rincian masing-masing pelanggaran
                    'face_not_detected' => $summary->face_not_detected_count,
                    'look_left' => $summary->look_left_count,
                    'look_right' => $summary->look_right_count,
                    'look_down' => $summary->look_down_count,
                    'look_up' => $summary->look_up_count,
                ],
                'threshold' => $threshold,
                'should_block' => $shouldBlock, // Instruksi untuk blokir layar
                'risk_level' => $summary->risk_level, // Level risiko (Low/Medium/High)
                'message' => $shouldBlock
                    ? 'Kuis diblokir karena terlalu banyak pelanggaran kamera!'
                    : "Pelanggaran kamera dicatat: {$summary->total_face_violations}/{$threshold}",
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal mencatat pelanggaran kamera', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'attempt_id' => $attemptId,
            ]);

            return response()->json([
                'error' => 'Gagal mencatat pelanggaran',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
