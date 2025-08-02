<?php

namespace App\Http\Controllers\Student;

use Exception;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\PointHistory;
use Illuminate\Http\Request;
use App\Services\PointService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    /**
     * Menampilkan halaman detail kursus, baik untuk siswa maupun pratinjau.
     */
    public function show(Request $request, Course $course)
    {
        $user = Auth::user();
        $is_preview = false;

        if ($user && $request->query('preview') === 'true') {
            if ($user->hasAnyRole(['admin', 'superadmin', 'instructor']) || $user->id === $course->instructor_id) {
                $is_preview = true;
            }
        }

        if (!$is_preview && $course->status !== 'published') {
            abort(404, 'Kursus tidak ditemukan.');
        }

        $is_enrolled = false;
        if ($user) {
            $is_enrolled = $user->enrollments()->where('course_id', $course->id)->exists();
        }

        $course->load(['modules.lessons']);

        $completedLessonIds = [];
        $currentCoursePoints = 0;
        if ($user) {
            $completedLessonIds = $user->completedLessons()
                ->whereIn('lesson_id', $course->lessons->pluck('id'))
                ->pluck('lesson_id')
                ->toArray();


            // Ambil total poin siswa di kursus ini
            $courseUserPivot = $user->coursePoints()->where('course_id', $course->id)->first();
            $currentCoursePoints = $courseUserPivot->pivot->points_earned ?? 0;
        }

        // LOGIKA BARU: Cek kelayakan untuk sertifikat
        $isEligibleForCertificate = false;
        if ($user && !$is_preview) {
            $totalLessons = $course->lessons->count();
            $completedLessonsCount = count($completedLessonIds);
            $hasReviewed = $course->reviews()->where('user_id', $user->id)->exists();

            if ($totalLessons > 0 && $completedLessonsCount >= $totalLessons && $hasReviewed) {
                $isEligibleForCertificate = true;
            }
        }



        // $course->load(['modules' => fn($q) => $q->orderBy('order'), 'modules.lessons' => fn($q) => $q->orderBy('order')]);

        if ($is_preview || $is_enrolled) {
            return view('student.courses.show', compact('course', 'is_preview', 'completedLessonIds', 'isEligibleForCertificate', 'currentCoursePoints'));
        } else {
            return view('details-course', compact('course', 'is_enrolled'));
        }
    }

    /**
     * Mengambil konten pelajaran dalam format HTML untuk AJAX.
     */
    public function getContent(Request $request, Lesson $lesson)
    {
        $lesson->load('lessonable', 'module.course.instructor');
        $user = Auth::user();

        $canAccess = false;
        if ($user) {
            if ($user->hasAnyRole(['admin', 'superadmin', 'instructor']) || ($user->hasRole('instructor') && $lesson->module->course->instructor_id === $user->id) || $user->enrollments()->where('course_id', $lesson->module->course->id)->exists()) {
                $canAccess = true;
            }
        }

        if (!$canAccess) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        // --- LOGIKA KUNCI MODUL ---
        $module = $lesson->module;
        if ($module->points_required > 0 && !$request->query('preview')) {
            $courseUserPivot = $user->coursePoints()->where('course_id', $module->course_id)->first();
            $userPoints = $courseUserPivot->pivot->points_earned ?? 0;

            if ($userPoints < $module->points_required) {
                $htmlContent = view('student.courses.partials._locked_content', ['module' => $module])->render();
                return response()->json([
                    'success' => true, // Sukses memuat, tapi kontennya adalah pesan terkunci
                    'title' => 'Modul Terkunci',
                    'html' => $htmlContent,
                    'is_locked' => true,
                ]);
            }
        }
        // --- AKHIR LOGIKA KUNCI MODUL ---



        // --- LOGIKA BARU: Ambil data diskusi ---
        $discussions = $lesson->discussions()
            ->whereNull('parent_id') // Hanya ambil komentar utama
            ->with(['user', 'replies.user', 'user.equippedBadge']) // Eager load user dan balasan
            ->latest()
            ->get();

        $lessonType = strtolower(class_basename($lesson->lessonable_type));
        $is_preview_for_view = $request->query('preview') === 'true';

        // Variabel untuk data tambahan
        $data = [
            'lesson' => $lesson,
            'is_preview' => $is_preview_for_view,
            'discussions' => $discussions
        ];

        // 2. Render HTML untuk forum diskusi secara terpisah
        $discussionHtml = view('student.courses.partials._discussion_forum', $data)->render();

        if ($lessonType === 'quiz') {
            $viewName = 'student.quizzes.partials._quiz_preview_in_lesson';

            $quiz = $lesson->lessonable;
            $quiz->load('questions'); // Eager load soal
            // HITUNG TOTAL SKOR MAKSIMAL
            $data['maxScore'] = $quiz->questions->sum('score');

            // LOGIKA BARU: Ambil riwayat kuis siswa & hitung total skor
            if ($user && !$is_preview_for_view) {
                $quizAttempts = $quiz->attempts()->where('student_id', $user->id)->get();
                $data['attemptCount'] = $quizAttempts->count();
                $data['lastAttempt'] = $quizAttempts->last();
            }
        } elseif ($lessonType === 'lessonpoint') {
            // Pastikan nama file Anda adalah '_lessonpoint.blade.php' (tanpa s)
            $viewName = 'instructor.lessons.previews._lessonpoint';
        } else {
            $viewName = 'instructor.lessons.previews._' . $lessonType;
        }

        if (!view()->exists($viewName)) {
            return response()->json(['success' => false, 'message' => 'Tipe konten tidak ditemukan.'], 404);
        }

        $htmlContent = view($viewName, $data)->render();

        return response()->json([
            'success' => true,
            'title' => $lesson->title,
            'html' => $htmlContent,
            'is_preview' => $is_preview_for_view,
            'discussion_html' => $discussionHtml,
            'is_locked' => false,
        ]);
    }

    /**
     * Menandai sebuah pelajaran sebagai selesai.
     */
    public function markAsComplete(Request $request, Lesson $lesson)
    {
        $user = Auth::user();
        $is_enrolled = $user->enrollments()->where('course_id', $lesson->module->course_id)->exists();

        if (!$is_enrolled) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        // Cek apakah pelajaran ini sudah pernah diselesaikan untuk mencegah poin ganda
        $alreadyCompleted = $user->completedLessons()->where('lesson_id', $lesson->id)->exists();

        $user->completedLessons()->syncWithoutDetaching($lesson->id);
        // Berikan poin HANYA jika pelajaran ini BARU saja diselesaikan
        if (!$alreadyCompleted) {
            $lessonType = strtolower(class_basename($lesson->lessonable_type));
            $activity = null;
            $course = $lesson->module->course;

            if ($lessonType === 'lessonarticle') $activity = 'complete_article';
            if ($lessonType === 'lessonvideo') $activity = 'complete_video';
            if ($lessonType === 'lessondocument') $activity = 'complete_document';

            if ($activity) {
                PointService::addPoints($user, $lesson, $course,  $activity, $lesson->title);
            }
        }
        return response()->json(['success' => true, 'message' => 'Pelajaran ditandai selesai.']);
    }


    /**
     * METODE BARU: Mengambil data leaderboard untuk kursus tertentu via AJAX.
     */
    // public function getLeaderboard(Course $course)
    // {

    //     $leaderboardRanks = $course->points()
    //         ->whereHas('user') 
    //         ->with('user')
    //         ->orderBy('points_earned', 'desc')
    //         ->take(100)
    //         ->get();

    //     // Render partial view dan kirim sebagai respons
    //     $html = view('student.courses.partials._leaderboard', compact('leaderboardRanks'))->render();

    //     return response()->json(['success' => true, 'html' => $html]);
    // }

    /**
     * METODE BARU: Mengambil data leaderboard untuk kursus tertentu via AJAX.
     */
    public function getLeaderboard(Course $course)
    {
        // 1. Tambahkan logging untuk menandai awal proses
        Log::info('Attempting to get leaderboard for course ID: ' . $course->id);

        try {
            // 2. Logging sebelum eksekusi query
            Log::info('Executing leaderboard database query...');

            $leaderboardRanks = $course->points()
                ->whereHas('user')
                ->with('user')
                ->orderBy('points_earned', 'desc')
                ->take(100)
                ->get();

            // 3. Logging setelah query berhasil
            Log::info('Query successful. Found ' . $leaderboardRanks->count() . ' ranks.');

            // 4. Logging sebelum me-render view
            Log::info('Rendering partial view: student.courses.partials._leaderboard');

            $html = view('student.courses.partials._leaderboard', compact('leaderboardRanks'))->render();

            // 5. Logging setelah view berhasil di-render
            Log::info('View rendered successfully for course ID: ' . $course->id);

            return response()->json(['success' => true, 'html' => $html]);
        } catch (Exception $e) {
            // 6. JIKA TERJADI ERROR, tangkap dan catat semuanya ke log
            Log::error('!!! CRITICAL ERROR in getLeaderboard for course ID: ' . $course->id . ' !!!');
            Log::error('Error Message: ' . $e->getMessage());
            Log::error('File: ' . $e->getFile() . ' on line ' . $e->getLine());
            Log::error('Stack Trace: ' . $e->getTraceAsString()); // Mencatat detail lengkap error

            // Kirim respons JSON yang valid untuk mencegah error di frontend
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan internal pada server. Silakan cek log untuk detail.'
            ], 500); // Kirim status 500
        }
    }


    /**
     * Mengambil data leaderboard untuk modul tertentu via AJAX.
     */
    public function getModuleLeaderboard(Module $module)
    {
        // Ambil ID semua pelajaran di dalam modul ini
        $lessonIds = $module->lessons()->pluck('id');

        // Hitung total poin per siswa HANYA dari pelajaran-pelajaran tersebut
        $leaderboardRanks = PointHistory::whereIn('lesson_id', $lessonIds)
            ->select('user_id', DB::raw('SUM(points) as total_points'))
            ->groupBy('user_id')
            ->orderBy('total_points', 'desc')
            ->with('user') // Eager load data user
            ->take(100)
            ->get();

        $html = view('student.courses.partials._module-leaderboard', compact('module', 'leaderboardRanks'))->render();

        return response()->json(['success' => true, 'html' => $html]);
    }
}
