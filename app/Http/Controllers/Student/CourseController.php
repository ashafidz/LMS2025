<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson; // Tambahkan ini
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    /**
     * Menampilkan halaman detail kursus, modul, dan daftar pelajaran.
     */
    public function show(Request $request, Course $course)
    {
        // Logika untuk pratinjau oleh admin/superadmin
        $is_preview = $request->query('preview') === 'true' && Auth::check() && Auth::user()->hasAnyRole(['admin', 'superadmin']);

        // Jika bukan mode pratinjau, kursus harus sudah dipublikasikan atau privat
        if (!$is_preview && !in_array($course->status, ['published', 'private'])) {
            abort(404, 'Kursus tidak ditemukan.');
        }

        // Eager load relasi untuk efisiensi query database
        $course->load(['modules' => function ($query) {
            $query->orderBy('order');
        }, 'modules.lessons' => function ($query) {
            $query->orderBy('order');
        }]);

        // Kirim data ke view
        return view('student.courses.show', compact('course', 'is_preview'));
    }

    public function getContent(Request $request, Lesson $lesson)
    {
        $lesson->load('lessonable', 'module.course');

        // Logika ini sudah benar
        $is_preview = $request->query('preview') === 'true' && Auth::check() && Auth::user()->hasAnyRole(['admin', 'superadmin']);

        $lessonType = strtolower(class_basename($lesson->lessonable_type));

        // Untuk kuis, kita gunakan view khusus yang akan mengarah ke halaman start kuis
        if ($lessonType === 'quiz') {
            $viewName = 'student.quizzes.partials._quiz_preview_in_lesson';
        } else {
            $viewName = 'instructor.lessons.previews._' . $lessonType;
        }

        if (!view()->exists($viewName)) {
            return response()->json(['success' => false, 'message' => 'Tipe konten tidak ditemukan.'], 404);
        }

        // DIPERBARUI: Kirim variabel $is_preview ke view
        $htmlContent = view($viewName, compact('lesson', 'is_preview'))->render();

        return response()->json([
            'success' => true,
            'title' => $lesson->title,
            'html' => $htmlContent,
        ]);
    }
}
