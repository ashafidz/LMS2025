<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Services\PointService;
use Illuminate\Http\Request;
use App\Models\User; // Pastikan User di-import
use App\Models\LessonPointAward; // Import model baru
use Illuminate\Support\Facades\Auth; // Import Auth

class LessonPointController extends Controller
{
    public function index(Lesson $lesson)
    {
        $course = $lesson->module->course;
        $students = $course->students()->with(['coursePoints' => fn($q) => $q->where('course_id', $course->id)])->simplePaginate(20);
        return view('instructor.lesson-points.manage', compact('lesson', 'course', 'students'));
    }
    public function award(Request $request, Lesson $lesson)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'points' => 'required|integer|min:1',
        ]);
        $student = \App\Models\User::find($validated['user_id']);
        $course = $lesson->module->course;

        // 1. Simpan catatan pemberian poin di tabel baru yang spesifik
        LessonPointAward::create([
            'lesson_id' => $lesson->id,
            'student_id' => $student->id,
            'instructor_id' => Auth::id(),
            'points' => $validated['points'],
        ]);

        // $description = "Poin manual dari pelajaran: " . $lesson->title;
        // PointService::addManualPoints($student, $course, $lesson, $validated['points'], $description);

        // 2. Gunakan PointService untuk memperbarui total poin dan riwayat umum
        $description = "Menerima " . $validated['points'] . " poin dari instruktur di sesi: " . $lesson->title;
        PointService::addManualPoints($student, $course, $lesson, $validated['points'],); // Kita modifikasi service sedikit
        return back()->with('success', 'Poin berhasil diberikan kepada ' . $student->name);
    }
}
