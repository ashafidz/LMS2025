<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Services\PointService;
use Illuminate\Http\Request;

class LessonPointController extends Controller
{
    public function index(Lesson $lesson)
    {
        $course = $lesson->module->course;
        $students = $course->students()->with(['coursePoints' => fn($q) => $q->where('course_id', $course->id)])->paginate(20);
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
        $description = "Poin manual dari pelajaran: " . $lesson->title;
        PointService::addManualPoints($student, $course, $validated['points'], $description);
        return back()->with('success', 'Poin berhasil diberikan kepada ' . $student->name);
    }
}
