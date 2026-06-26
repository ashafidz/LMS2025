<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\AdaptiveLesson;
use App\Models\AdaptiveLessonPointAward;
use App\Services\PointService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdaptiveLessonPointController extends Controller
{
    public function index(Course $course, AdaptiveLesson $lesson)
    {
        // Pastikan instruktur berhak mengakses course ini
        abort_if($course->instructor_id !== Auth::id(), 403);
        abort_if($lesson->module->course_id !== $course->id, 403);

        $module = $lesson->module;

        // Get students with proper sorting by unique_id_number (nulls last)
        $students = $course->students()
            ->with(['coursePoints' => fn($q) => $q->where('course_id', $course->id)])
            ->with('studentProfile')
            ->leftJoin('student_profiles', 'users.id', '=', 'student_profiles.user_id')
            ->orderByRaw('CASE WHEN student_profiles.unique_id_number IS NULL THEN 1 ELSE 0 END')
            ->orderBy('student_profiles.unique_id_number', 'asc')
            ->select('users.*')
            ->simplePaginate(20);

        return view('instructor.adaptive.lesson-points.manage', compact('lesson', 'course', 'module', 'students'));
    }

    public function award(Request $request, Course $course, AdaptiveLesson $lesson)
    {
        abort_if($course->instructor_id !== Auth::id(), 403);
        abort_if($lesson->module->course_id !== $course->id, 403);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'points' => 'required|integer|min:1',
        ]);

        $student = \App\Models\User::find($validated['user_id']);

        // 1. Simpan catatan pemberian poin di tabel spesifik adaptive
        AdaptiveLessonPointAward::create([
            'adaptive_lesson_id' => $lesson->id,
            'student_id' => $student->id,
            'instructor_id' => Auth::id(),
            'points' => $validated['points'],
        ]);

        // 2. Gunakan PointService khusus adaptive
        PointService::addManualPointsForAdaptive($student, $course, $lesson, $validated['points']);

        return back()->with('success', 'Poin berhasil diberikan kepada ' . $student->name);
    }
}
