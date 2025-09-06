<?php

namespace App\Http\Controllers\Student;

use Carbon\Carbon;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuizAttempt;
use App\Models\PointHistory;
use Illuminate\Http\Request;
use App\Services\BadgeService;
use App\Services\PointService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class StudentQuizController extends Controller
{
    // ... (metode start, begin, take, submit, result, dan helper lainnya tidak ada perubahan) ...
    public function start(Request $request, Quiz $quiz)
    {
        // Validasi: Pastikan siswa terdaftar di kursus ini
        $student = Auth::user();
        $course = $quiz->lesson->module->course;
        if (session('active_role') === 'student') {
            if (!$student->enrollments()->where('courses.id', $course->id)->exists()) {
                abort(403, 'Anda tidak terdaftar di kursus ini.');
            }
        };

        $quiz->loadCount('questions');
        $is_preview = $request->query('preview') === 'true' && Auth::check();

        // Hitung percobaan yang sudah dilakukan
        $attemptCount = 0;
        if (Auth::check() && !$is_preview) {
            $attemptCount = $student->quizAttempts()->where('quiz_id', $quiz->id)->count();
        }


        // Cari percobaan terakhir (baik lulus maupun gagal) untuk ditampilkan di view jika ada
        $lastAttempt = null; // DIUBAH: Inisialisasi sebagai null
        if (Auth::check() && !$is_preview) { // DIUBAH: Tambahkan pengecekan ini
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
            $availabilityMessage = 'Kuis ini akan tersedia pada ' . $quiz->available_from->format('d F Y, H:i');
        }
        if ($quiz->available_to && $now->isAfter($quiz->available_to)) {
            $isAvailable = false;
            $availabilityMessage = 'Waktu pengerjaan kuis ini telah berakhir pada ' . $quiz->available_to->format('d F Y, H:i');
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
        if (session('active_role') === 'student') {
            if (!$student->enrollments()->where('courses.id', $course->id)->exists()) {
                abort(403, 'Anda tidak terdaftar di kursus ini.');
            }
        };


        // LOGIKA BARU: Cek batas pengerjaan sebelum memulai
        $user = Auth::user();
        if ($quiz->max_attempts) {
            $attemptCount = $user->quizAttempts()->where('quiz_id', $quiz->id)->count();
            if ($attemptCount >= $quiz->max_attempts) {
                return redirect()->route('student.quiz.start', $quiz)->with('error', 'Anda telah mencapai batas maksimal pengerjaan untuk kuis ini.');
            }
        }

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

        // dd($request['is_preview']);


        if ($request['is_preview'] === 'true') {
            $quiz->load('questions.options');
            $is_preview = true;
            $attempt = new QuizAttempt(['quiz_id' => $quiz->id, 'id' => 0]);
            $attempt->setRelation('quiz', $quiz);
            return view('student.quizzes.take', [
                'attempt' => $attempt,
                'is_preview' => true,
                'endTime' => null
            ]);
        }
        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'student_id' => Auth::id(),
            'status' => 'in_progress',
            'start_time' => now()
        ]);
        return redirect()->route('student.quiz.take', $attempt);
    }

    public function take($attemptId)
    {

        $attempt = QuizAttempt::with('quiz.questions.options')->findOrFail($attemptId);

        // Validasi: Pastikan siswa terdaftar di kursus ini
        $student = Auth::user();
        $course = $attempt->quiz->lesson->module->course;
        if (session('active_role') === 'student') {
            if (!$student->enrollments()->where('courses.id', $course->id)->exists()) {
                abort(403, 'Anda tidak terdaftar di kursus ini.');
            }
        };

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
            $endTime = $attempt->start_time->addMinutes($attempt->quiz->time_limit);
        }
        // --- AKHIR LOGIKA TAMBAHAN ---


        $attempt->load('quiz.questions.options');
        $is_preview = false;
        return view('student.quizzes.take', [
            'attempt' => $attempt,
            'is_preview' => false,
            'endTime' => $endTime ? $endTime->toIso8601String() : null, // Kirim sebagai string ISO 8601
        ]);
    }

    public function submit(Request $request, $attemptId)
    {
        $userAnswers = $request->input('answers', []);
        $totalScore = 0;
        $is_preview = $request->input('is_preview') === 'true';
        if ($is_preview) {
            $quiz = Quiz::with('questions.options')->findOrFail($request->input('quiz_id_preview'));
            $attempt = new QuizAttempt(['quiz_id' => $quiz->id]);
            $attempt->setRelation('quiz', $quiz);
        } else {
            $attempt = QuizAttempt::findOrFail($attemptId);

            // Validasi: Pastikan siswa terdaftar di kursus ini
            $student = Auth::user();
            $course = $attempt->quiz->lesson->module->course;
            if (!$student->enrollments()->where('courses.id', $course->id)->exists()) {
                abort(403, 'Anda tidak terdaftar di kursus ini.');
            }

            if ($attempt->student_id != Auth::id() || $attempt->status != 'in_progress') {
                abort(403);
            }
        }
        $quizQuestions = $attempt->quiz->questions;
        $studentAnswersCollection = collect();
        DB::transaction(function () use ($quizQuestions, $userAnswers, $attempt, &$totalScore, $is_preview, &$studentAnswersCollection) {
            foreach ($quizQuestions as $question) {
                $isCorrect = false;
                $userAnswerForQuestion = $userAnswers[$question->id] ?? null;
                if ($userAnswerForQuestion) {
                    $isCorrect = $this->checkAnswer($question, $userAnswerForQuestion);
                }
                if ($isCorrect) {
                    $totalScore += $question->score;
                }
                if (!$is_preview) {
                    $this->storeStudentAnswer($attempt, $question, $userAnswerForQuestion, $isCorrect);
                } else {
                    $this->collectStudentAnswers($studentAnswersCollection, $question, $userAnswerForQuestion, $isCorrect);
                }
            }
            if (!$is_preview) {
                // 1. Kalkulasi Skor dan Status Kelulusan (Tidak berubah)
                $maxPossibleScore = $attempt->quiz->questions->sum('score');
                $percentageScore = ($maxPossibleScore > 0) ? ($totalScore / $maxPossibleScore) * 100 : 0;
                $newStatus = $percentageScore >= $attempt->quiz->pass_mark ? 'passed' : 'failed';

                // 2. Set dan Simpan Hasil Attempt (Tidak berubah)
                $attempt->score = $totalScore;
                $attempt->status = $newStatus;
                $attempt->end_time = now();
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
                    $quizExceededTimeLimit = $attempt->end_time > $attempt->start_time->addMinutes($timeLimitInMinutes);
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
                    !$hasEarnedPointsBefore && // <-- Menggunakan variabel baru dari pengecekan PointHistory
                    !$quizExceededTimeLimit
                ) {
                    // KODE BARU (Menggunakan Named Arguments, urutan tidak masalah)
                    PointService::addPoints(
                        user: Auth::user(),
                        course: $attempt->quiz->lesson->module->course,
                        activity: 'pass_quiz', // Jelas ini untuk 'activity'
                        lesson: $attempt->quiz->lesson, // Jelas ini untuk 'lesson'
                        description_meta: $attempt->quiz->title
                    );

                    $student = Auth::user();
                    $lesson = $attempt->quiz->lesson;

                    // apabila lesson belum pernah dicomplete
                    if (!$student->completedLessons->contains($attempt->quiz->lesson_id)) {
                        // Tandai pelajaran sebagai selesai
                        $student->completedLessons()->syncWithoutDetaching($lesson->id);
                    }
                }

                // 5. Logika Setelah Lulus (Tidak berubah)
                if ($newStatus === 'passed') {
                    $student = Auth::user();
                    $lesson = $attempt->quiz->lesson;
                    // Cek perolehan badge
                    BadgeService::checkQuizCompletionBadges($student);
                }
            }
        });
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
        // check ownership attemp
        $attempt = QuizAttempt::findOrFail($attemptId);

        // Validasi: Pastikan siswa terdaftar di kursus ini
        $student = Auth::user();
        $course = $attempt->quiz->lesson->module->course;
        if (session('active_role') === 'student') {
            if (!$student->enrollments()->where('courses.id', $course->id)->exists()) {
                abort(403, 'Anda tidak terdaftar di kursus ini.');
            }
        }

        if ($attempt->student_id != Auth::id()) {
            abort(403);
        }


        $attempt = QuizAttempt::findOrFail($attemptId);

        if ($attempt->status === 'in_progress') {
            return redirect()->route('student.quiz.take', $attempt)->with('error', 'Anda harus menyelesaikan kuis terlebih dahulu.');
        }
        $attempt->load(['quiz.questions.options', 'answers']);

        // quiz max score
        $maxPossibleScore = $attempt->quiz->questions->sum('score');
        // quiz minimum score, not percentage
        $minimumScore = $maxPossibleScore * ($attempt->quiz->pass_mark / 100);

        // Hitung nilai student dalam skala 0-100 (sama seperti di InstructorRecapController)
        $studentScoreScaled = ($maxPossibleScore > 0) ? min(100, round(($attempt->score / $maxPossibleScore) * 100, 2)) : 0;
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
    // Ganti metode ini di dalam app/Http/Controllers/Student/StudentQuizController.php

    /**
     * METODE BARU: Memeriksa satu jawaban via AJAX dan memberikan umpan balik.
     */
    public function checkAnswerAjax(Request $request)
    {
        $validated = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'answer' => 'present', // Boleh null (tidak dijawab)
        ]);

        $question = Question::with('options')->find($validated['question_id']);
        $userAnswer = $validated['answer'];

        $isCorrect = $this->checkAnswer($question, $userAnswer);

        // KIRIM DATA DEBUG BERSAMA DENGAN RESPONS JSON
        return response()->json([
            'correct' => $isCorrect,
            'explanation' => $isCorrect ? $question->explanation : null,

            // --- DATA DEBUG ---
            // Tambahkan baris ini untuk melihat apa yang sebenarnya terjadi:
            'debug_info' => [
                'received_answer' => $userAnswer,
                'is_correct_result' => $isCorrect,
                'question_type_checked' => $question->question_type,
            ]
            // --- AKHIR DATA DEBUG ---
        ]);
    }

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
                $userOptions = collect((array)$userAnswer)->map(fn($id) => (int)$id)->sort()->values()->toArray();
                return $correctOptions == $userOptions;

            case 'drag_and_drop':
                // Ambil semua opsi yang merupakan kunci jawaban (memiliki gap_id)
                $correctAnswers = $question->options->whereNotNull('correct_gap_identifier');

                if ($correctAnswers->isEmpty() && (is_null($userAnswer) || empty($userAnswer))) {
                    return true;
                }
                if (!is_array($userAnswer)) {
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
                    if (!isset($userAnswer[$blankId]) || $userAnswer[$blankId] != $correctAnswer->id) {
                        return false;
                    }
                }

                // Jika semua blank yang ada di kunci jawaban berhasil dicocokkan, maka benar
                return true;
        }
        return false;
    }

    private function storeStudentAnswer(QuizAttempt $attempt, Question $question, $userAnswer, bool $isCorrect)
    {
        if (is_array($userAnswer)) {
            $answersToStore = [];
            foreach ($userAnswer as $key => $value) {
                if (!empty($value)) {
                    $answersToStore[] = ['question_id' => $question->id, 'selected_option_id' => $value, 'is_correct' => $isCorrect];
                }
            }
            if (!empty($answersToStore)) {
                $attempt->answers()->createMany($answersToStore);
            }
        } else if ($userAnswer) {
            $attempt->answers()->create(['question_id' => $question->id, 'selected_option_id' => $userAnswer, 'is_correct' => $isCorrect]);
        }
    }

    private function collectStudentAnswers($collection, Question $question, $userAnswer, bool $isCorrect)
    {
        if (is_array($userAnswer)) {
            foreach ($userAnswer as $key => $value) {
                if (!empty($value)) {
                    $collection->push(['question_id' => $question->id, 'selected_option_id' => $value, 'is_correct' => $isCorrect]);
                }
            }
        } else if ($userAnswer) {
            $collection->push(['question_id' => $question->id, 'selected_option_id' => $userAnswer, 'is_correct' => $isCorrect]);
        }
    }
}
