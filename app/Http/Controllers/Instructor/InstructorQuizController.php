<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptIntegritySummary;
use App\Models\MonitoringLog;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        // Hitung nilai minimum dalam skala 0-100 (sama seperti di InstructorRecapController)
        $minimumScoreScaled = $quiz->pass_mark; // pass_mark sudah dalam bentuk persentase 0-100

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

        return view('instructor.quizzes.results.index', compact('quiz', 'enrolledStudents', 'minimumScore', 'totalMaxScore', 'minimumScoreScaled'));
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
            'student',
            'revisedBy',
        ]);

        // Hitung total skor maksimal dan nilai minimum
        $quiz = $attempt->quiz;
        $quiz->load('questions');
        $totalMaxScore = $quiz->questions->sum('score');
        $minimumScore = ($quiz->pass_mark / 100) * $totalMaxScore;

        // Gunakan scaled_score dari database, fallback ke kalkulasi manual untuk data lama
        $studentScoreScaled = $attempt->scaled_score
            ?? (($totalMaxScore > 0) ? min(100, round(($attempt->score / $totalMaxScore) * 100, 2)) : 0);
        $minimumScoreScaled = $quiz->pass_mark; // pass_mark sudah dalam bentuk persentase 0-100

        return view('instructor.quizzes.results.show', compact('attempt', 'totalMaxScore', 'minimumScore', 'studentScoreScaled', 'minimumScoreScaled'));
    }

    /**
     * Menampilkan halaman ringkasan monitoring kejujuran untuk satu kuis tertentu.
     */
    public function monitoringReview(Quiz $quiz)
    {
        // Otorisasi: Hanya instruktur pembuat kursus yang bisa melihat data ini
        if ($quiz->lesson->module->course->instructor_id != Auth::id()) {
            abort(403);
        }

        // Ambil semua data pengerjaan (attempts) untuk kuis ini
        $attempts = QuizAttempt::where('quiz_id', $quiz->id)
            ->with([
                'student', // Ambil data identitas siswa
                'integritySummary', // Ambil ringkasan total pelanggaran kamera/tab
                'monitoringLogs' => function ($query) {
                    // Ambil detail riwayat pelanggaran, urutkan dari yang terbaru
                    $query->orderBy('violation_timestamp', 'desc');
                }
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // Kelompokkan data berdasarkan siswa (karena satu siswa bisa mencoba kuis berkali-kali)
        $attemptsByStudent = $attempts->groupBy('student_id')->map(function ($studentAttempts) {
            return [
                'student' => $studentAttempts->first()->student,
                'latest_attempt' => $studentAttempts->sortByDesc('created_at')->first(), // Percobaan terbaru
                'all_attempts' => $studentAttempts->sortByDesc('created_at')->values() // Daftar semua percobaan
            ];
        });

        // Hitung statistik keseluruhan untuk ditampilkan di dasbor ringkasan
        $stats = [
            'total_attempts' => $attempts->count(), // Total berapa kali kuis dikerjakan
            'total_tab_violations' => $attempts->sum(fn($a) => $a->integritySummary?->total_tab_switches ?? 0), // Total semua pindah tab
            'total_camera_violations' => $attempts->sum(fn($a) => $a->integritySummary?->total_face_violations ?? 0), // Total semua pelanggaran kamera
            'total_expelled' => $attempts->where('expelled_by_violation', true)->count(), // Total dikeluarkan karena pelanggaran
        ];

        return view('instructor.quizzes.monitoring-review', compact('quiz', 'attemptsByStudent', 'stats'));
    }

    /**
     * Menampilkan rincian log monitoring (bukti visual & waktu) untuk satu sesi pengerjaan (attempt).
     */
    public function monitoringDetail(QuizAttempt $attempt)
    {
        // Otorisasi: Pastikan instruktur yang login adalah yang berhak melihat kuis ini
        if ($attempt->quiz->lesson->module->course->instructor_id != Auth::id()) {
            abort(403);
        }

        // Muat semua relasi data yang dibutuhkan
        $attempt->load([
            'student',
            'quiz.questions', // Load questions untuk menghitung total skor maksimal
            'integritySummary',
            'revisedBy', // Load data instruktur yang merevisi (jika ada)
            'monitoringLogs' => function ($query) {
                // Urutkan riwayat pelanggaran berdasarkan urutan waktu kejadian (kronologis)
                $query->orderBy('violation_timestamp', 'asc');
            }
        ]);

        // Hitung total skor maksimal quiz
        $totalMaxScore = $attempt->quiz->questions->sum('score');

        // Kelompokkan log berdasarkan jenisnya (misalnya: kumpulkan semua log 'pindah tab' jadi satu grup)
        $logsByType = $attempt->monitoringLogs->groupBy('violation_type');

        return view('instructor.quizzes.monitoring-detail', compact('attempt', 'logsByType', 'totalMaxScore'));
    }

    /**
     * Menyimpan revisi skor untuk satu percobaan (attempt) quiz.
     * Skor asli tetap utuh, skor revisi disimpan di kolom terpisah.
     */
    public function reviseScore(Request $request, QuizAttempt $attempt)
    {
        // Otorisasi: Pastikan instruktur yang login adalah pemilik kursus
        if ($attempt->quiz->lesson->module->course->instructor_id != Auth::id()) {
            abort(403);
        }

        // Validasi input
        $request->validate([
            'revised_score' => 'required|numeric|min:0',
            'revision_note' => 'required|string|max:500',
        ], [
            'revised_score.required' => 'Skor revisi wajib diisi.',
            'revised_score.numeric' => 'Skor revisi harus berupa angka.',
            'revised_score.min' => 'Skor revisi tidak boleh kurang dari 0.',
            'revision_note.required' => 'Catatan/alasan revisi wajib diisi.',
            'revision_note.max' => 'Catatan revisi maksimal 500 karakter.',
        ]);

        // Simpan revisi skor
        $attempt->update([
            'revised_score' => $request->revised_score,
            'revised_by' => Auth::id(),
            'revised_at' => now(),
            'revision_note' => $request->revision_note,
        ]);

        return redirect()->back()->with('success', 'Revisi skor berhasil disimpan.');
    }

    /**
     * Menampilkan overview monitoring untuk seluruh course
     */
    public function courseMonitoringOverview(Course $course)
    {
        // Otorisasi
        if ($course->instructor_id != Auth::id()) {
            abort(403);
        }

        // Ambil semua quiz dalam course beserta attempts dan integrity summary
        $quizzes = Quiz::whereHas('lesson.module', function ($query) use ($course) {
            $query->where('course_id', $course->id);
        })
            ->with([
                'lesson.module',
                'attempts.integritySummary',
                'attempts.student'
            ])
            ->get();

        // Hitung statistik global
        $totalAttempts = 0;
        $totalTabViolations = 0;
        $totalCameraViolations = 0;
        $totalStudents = [];

        // Data per quiz
        $quizData = [];

        foreach ($quizzes as $quiz) {
            $quizAttempts = $quiz->attempts;
            $attemptCount = $quizAttempts->count();
            $tabViolations = $quizAttempts->sum(fn($a) => $a->integritySummary?->total_tab_switches ?? 0);
            $cameraViolations = $quizAttempts->sum(fn($a) => $a->integritySummary?->total_face_violations ?? 0);

            // Track unique students
            foreach ($quizAttempts as $attempt) {
                $totalStudents[$attempt->student_id] = true;
            }

            $totalAttempts += $attemptCount;
            $totalTabViolations += $tabViolations;
            $totalCameraViolations += $cameraViolations;

            $quizData[] = [
                'quiz' => $quiz,
                'attempt_count' => $attemptCount,
                'tab_violations' => $tabViolations,
                'camera_violations' => $cameraViolations,
                'total_violations' => $tabViolations + $cameraViolations,
                'unique_students' => $quizAttempts->pluck('student_id')->unique()->count()
            ];
        }

        $stats = [
            'total_quizzes' => $quizzes->count(),
            'total_attempts' => $totalAttempts,
            'total_students' => count($totalStudents),
            'total_tab_violations' => $totalTabViolations,
            'total_camera_violations' => $totalCameraViolations,
            'total_violations' => $totalTabViolations + $totalCameraViolations,
        ];

        return view('instructor.quizzes.course-monitoring-overview', compact('course', 'quizData', 'stats'));
    }
}
