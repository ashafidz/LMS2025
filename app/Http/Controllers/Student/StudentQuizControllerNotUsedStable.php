<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentQuizController extends Controller
{
    // ... (metode start, begin, take, submit, result, dan helper lainnya tidak perlu diubah) ...
    public function start(Request $request, Quiz $quiz)
    {
        $quiz->loadCount('questions');
        $is_preview = $request->query('preview') === 'true' && Auth::check();
        return view('student.quizzes.start', compact('quiz', 'is_preview'));
    }

    public function begin(Request $request, Quiz $quiz)
    {
        if ($request->input('is_preview') === 'true') {
            $quiz->load('questions.options');
            $is_preview = true;
            $attempt = new QuizAttempt(['quiz_id' => $quiz->id, 'id' => 0]);
            $attempt->setRelation('quiz', $quiz);
            return view('student.quizzes.take', compact('attempt', 'is_preview'));
        }

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'student_id' => Auth::id(),
            'status' => 'in_progress',
            'start_time' => now(),
        ]);
        return redirect()->route('student.quiz.take', $attempt);
    }

    public function take($attemptId)
    {
        $attempt = QuizAttempt::findOrFail($attemptId);
        if ($attempt->student_id !== Auth::id()) {
            abort(403);
        }
        if ($attempt->status !== 'in_progress') {
            return redirect()->route('student.quiz.result', $attempt)->with('info', 'Anda sudah menyelesaikan kuis ini.');
        }
        $attempt->load('quiz.questions.options');
        $is_preview = false;
        return view('student.quizzes.take', compact('attempt', 'is_preview'));
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
            if ($attempt->student_id !== Auth::id() || $attempt->status !== 'in_progress') {
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
                $maxPossibleScore = $attempt->quiz->questions->sum('score');
                $percentageScore = ($maxPossibleScore > 0) ? ($totalScore / $maxPossibleScore) * 100 : 0;
                $attempt->score = $totalScore;
                $attempt->status = $percentageScore >= $attempt->quiz->pass_mark ? 'passed' : 'failed';
                $attempt->end_time = now();
                $attempt->save();
            }
        });

        if ($is_preview) {
            $maxPossibleScore = $attempt->quiz->questions->sum('score');
            $percentageScore = ($maxPossibleScore > 0) ? ($totalScore / $maxPossibleScore) * 100 : 0;
            $attempt->score = $totalScore;
            $attempt->status = $percentageScore >= $attempt->quiz->pass_mark ? 'passed' : 'failed';
            $attempt->setRelation('answers', $studentAnswersCollection->mapInto(\App\Models\StudentAnswer::class));
            return view('student.quizzes.result', compact('attempt', 'is_preview'));
        }

        return redirect()->route('student.quiz.result', $attempt->id);
    }

    public function result($attemptId)
    {
        $attempt = QuizAttempt::findOrFail($attemptId);
        if ($attempt->student_id !== Auth::id()) {
            abort(403);
        }
        if ($attempt->status === 'in_progress') {
            return redirect()->route('student.quiz.take', $attempt)->with('error', 'Anda harus menyelesaikan kuis terlebih dahulu.');
        }
        $attempt->load(['quiz.questions.options', 'answers']);
        return view('student.quizzes.result', ['attempt' => $attempt, 'is_preview' => false]);
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
                // Ambil semua opsi yang memiliki gap_id (ini adalah kunci jawaban)
                $correctAnswers = $question->options->whereNotNull('correct_gap_identifier');

                // Jika tidak ada kunci jawaban dan siswa tidak menjawab, anggap benar
                if ($correctAnswers->isEmpty() && empty($userAnswer)) {
                    return true;
                }
                // Jika jumlah jawaban siswa tidak sama dengan jumlah kunci jawaban, pasti salah
                if (!is_array($userAnswer) || $correctAnswers->count() != count($userAnswer)) {
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
                // Jika semua cocok, kembalikan true
                return true;
        }
        return false;
    }

    private function storeStudentAnswer(QuizAttempt $attempt, Question $question, $userAnswer, bool $isCorrect)
    {
        if (is_array($userAnswer)) {
            $answersToStore = [];
            foreach ($userAnswer as $key => $value) {
                if (!empty($value)) { // Hanya simpan jika ada jawaban
                    $answersToStore[] = [
                        'question_id' => $question->id,
                        'selected_option_id' => $value,
                        'is_correct' => $isCorrect,
                    ];
                }
            }
            if (!empty($answersToStore)) {
                $attempt->answers()->createMany($answersToStore);
            }
        } else if ($userAnswer) {
            $attempt->answers()->create([
                'question_id' => $question->id,
                'selected_option_id' => $userAnswer,
                'is_correct' => $isCorrect,
            ]);
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
