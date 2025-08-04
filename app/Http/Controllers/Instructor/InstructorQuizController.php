<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstructorQuizController extends Controller
{
    /**
     * Menampilkan daftar semua percobaan (attempts) untuk kuis tertentu.
     */
    public function showResults(Quiz $quiz)
    {
        // Otorisasi: Pastikan instruktur adalah pemilik kursus
        if ($quiz->lesson->module->course->instructor_id !== Auth::id()) {
            abort(403);
        }

        // Ambil semua attempt untuk kuis ini, beserta data siswa
        $attempts = $quiz->attempts()
            ->with('student')
            ->latest()
            ->paginate(20);

        return view('instructor.quizzes.results.index', compact('quiz', 'attempts'));
    }

    /**
     * Menampilkan rincian jawaban untuk satu percobaan (attempt) spesifik.
     */
    public function reviewAttempt(QuizAttempt $attempt)
    {
        // Otorisasi: Pastikan instruktur adalah pemilik kursus
        if ($attempt->quiz->lesson->module->course->instructor_id !== Auth::id()) {
            abort(403);
        }

        // Eager load semua relasi yang dibutuhkan untuk halaman hasil
        $attempt->load([
            'quiz.questions.options',
            'answers',
            'student'
        ]);

        return view('instructor.quizzes.results.show', compact('attempt'));
    }
}
