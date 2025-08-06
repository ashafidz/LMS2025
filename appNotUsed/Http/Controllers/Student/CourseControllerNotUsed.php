<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    /**
     * Menampilkan halaman detail kursus, modul, dan daftar pelajaran.
     */
    public function show(Request $request, Course $course)
    {
        $user = Auth::user();
        $is_preview = false;

        if ($user && $request->query('preview') === 'true') {
            if ($user->hasAnyRole(['admin', 'superadmin']) || $user->id === $course->instructor_id) {
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

        // DIPERBARUI: Ambil juga ID pelajaran yang sudah diselesaikan oleh siswa
        $completedLessonIds = [];
        if ($user) {
            $completedLessonIds = $user->completedLessons()
                ->whereIn('lesson_id', $course->lessons->pluck('id'))
                ->pluck('lesson_id')
                ->toArray();
        }

        $course->load(['modules' => fn($q) => $q->orderBy('order'), 'modules.lessons' => fn($q) => $q->orderBy('order')]);

        if ($is_preview || $is_enrolled) {
            return view('student.courses.show', compact('course', 'is_preview', 'completedLessonIds'));
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
            if ($user->hasAnyRole(['admin', 'superadmin']) || ($user->hasRole('instructor') && $lesson->module->course->instructor_id === $user->id) || $user->enrollments()->where('course_id', $lesson->module->course->id)->exists()) {
                $canAccess = true;
            }
        }

        if (!$canAccess) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $lessonType = strtolower(class_basename($lesson->lessonable_type));
        $is_preview_for_view = $request->query('preview') === 'true';

        $viewName = ($lessonType === 'quiz') ? 'student.quizzes.partials._quiz_preview_in_lesson' : 'instructor.lessons.previews._' . $lessonType;

        if (!view()->exists($viewName)) {
            return response()->json(['success' => false, 'message' => 'Tipe konten tidak ditemukan.'], 404);
        }

        $htmlContent = view($viewName, ['lesson' => $lesson, 'is_preview' => $is_preview_for_view])->render();

        return response()->json([
            'success' => true,
            'title' => $lesson->title,
            'html' => $htmlContent,
        ]);
    }

    /**
     * METODE BARU: Menandai sebuah pelajaran sebagai selesai.
     */
    public function markAsComplete(Request $request, Lesson $lesson)
    {
        $user = Auth::user();

        // Pastikan siswa terdaftar di kursus ini sebelum menandai selesai
        $is_enrolled = $user->enrollments()->where('course_id', $lesson->module->course_id)->exists();

        if (!$is_enrolled) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        // Gunakan syncWithoutDetaching untuk menambahkan record tanpa duplikat
        $user->completedLessons()->syncWithoutDetaching($lesson->id);

        return response()->json(['success' => true, 'message' => 'Pelajaran ditandai selesai.']);
    }
}
