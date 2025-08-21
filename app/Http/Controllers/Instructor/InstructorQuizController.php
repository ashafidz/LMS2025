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
     * Menampilkan daftar semua siswa di kursus dan status pengerjaan kuis mereka.
     */
    public function showResults(Quiz $quiz)
    {
        // Otorisasi
        if ($quiz->lesson->module->course->instructor_id != Auth::id()) {
            abort(403);
        }

        // Load questions untuk menghitung total skor maksimal
        $quiz->load('questions');
        $totalMaxScore = $quiz->questions->sum('score');
        $minimumScore = ($quiz->pass_mark / 100) * $totalMaxScore;

        // 1. Ambil kursus yang terkait dengan kuis ini
        $course = $quiz->lesson->module->course;

        // 2. Ambil semua siswa yang terdaftar di kursus tersebut dan urutkan berdasarkan unique_id_number
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
            ->get(); // Hapus pagination, ambil semua data

        // 3. Ambil semua percobaan kuis untuk semua siswa (tidak lagi dibatasi per halaman)
        $studentIds = $enrolledStudents->pluck('id');
        $attempts = QuizAttempt::where('quiz_id', $quiz->id)
            ->whereIn('student_id', $studentIds)
            ->get()
            ->groupBy('student_id'); // Kelompokkan berdasarkan ID siswa

        // 4. Proses data untuk menentukan status akhir setiap siswa
        foreach ($enrolledStudents as $student) {
            $studentAttempts = $attempts->get($student->id);

            if ($studentAttempts) {
                // Cek apakah ada percobaan yang lulus
                $hasPassed = $studentAttempts->contains('status', 'passed');
                $student->quiz_status = $hasPassed ? 'Lulus' : 'Gagal';
                $student->attempts = $studentAttempts;
            } else {
                $student->quiz_status = 'Belum Mengerjakan';
                $student->attempts = collect(); // Koleksi kosong
            }
        }

        return view('instructor.quizzes.results.index', compact('quiz', 'enrolledStudents', 'minimumScore'));
    }

    /**
     * Menampilkan rincian jawaban untuk satu percobaan (attempt) spesifik.
     */
    public function reviewAttempt(QuizAttempt $attempt)
    {
        // Otorisasi: Pastikan instruktur adalah pemilik kursus
        if ($attempt->quiz->lesson->module->course->instructor_id != Auth::id()) {
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
