<?php

namespace App\Http\Controllers\Superadmin;

use App\Models\User;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\CourseUser;
use App\Models\SiteSetting;
use App\Models\PointHistory;
use Illuminate\Http\Request;
use App\Models\AssignmentSubmission;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class PointSyncController extends Controller
{
    /**
     * Halaman pemilihan instruktur.
     */
    public function index()
    {
        $instructors = User::role('instructor')->orderBy('name')->get();

        return view('superadmin.point-sync.index', compact('instructors'));
    }

    /**
     * Halaman daftar kursus milik instruktur yang dipilih.
     */
    public function showCourses(User $instructor)
    {
        $courses = Course::where('instructor_id', $instructor->id)
            ->with('category')
            ->withCount('students')
            ->latest()
            ->get();

        return view('superadmin.point-sync.courses', compact('instructor', 'courses'));
    }

    /**
     * Preview mass sync anomalies for the whole course.
     */
    public function massSyncPreview(Course $course)
    {
        $course->load('instructor');
        $anomalies = $this->getMassSyncAnomalies($course);

        return view('superadmin.point-sync.mass-sync-preview', compact('course', 'anomalies'));
    }

    /**
     * Execute mass sync points for the whole course.
     */
    public function massSyncExecute(Request $request, Course $course)
    {
        $anomalies = $this->getMassSyncAnomalies($course);

        if ($anomalies->isEmpty()) {
            return back()->with('info', 'Tidak ada data poin yang perlu disinkronkan.');
        }

        DB::transaction(function () use ($course, $anomalies) {
            foreach ($anomalies as $anomaly) {
                $student = $anomaly['student'];
                $lesson = $anomaly['lesson'];
                $expectedPoints = $anomaly['expected_points'];
                $description = $anomaly['description'];
                $actualPoints = $anomaly['actual_points'];

                $pointDifference = $expectedPoints - $actualPoints;

                PointHistory::updateOrCreate(
                    [
                        'user_id' => $student->id,
                        'course_id' => $course->id,
                        'lesson_id' => $lesson->id,
                    ],
                    [
                        'points' => $expectedPoints,
                        'description' => $description
                    ]
                );

                if ($pointDifference != 0) {
                    $courseUser = CourseUser::where('user_id', $student->id)
                        ->where('course_id', $course->id)
                        ->first();

                    if ($courseUser) {
                        $courseUser->increment('points_earned', $pointDifference);
                    } else {
                        CourseUser::create([
                            'user_id' => $student->id,
                            'course_id' => $course->id,
                            'points_earned' => max(0, $pointDifference)
                        ]);
                    }
                }
            }
        });

        return redirect()->route('superadmin.point-sync.mass-sync.preview', $course)
            ->with('success', 'Sinkronisasi massal berhasil dieksekusi. ' . count($anomalies) . ' riwayat poin telah diperbaiki.');
    }

    /**
     * Show student progress checklist for a specific course (with sync buttons).
     */
    public function studentProgress(Course $course, User $student)
    {
        $isEnrolled = $course->students()->where('user_id', $student->id)->exists();
        if (!$isEnrolled) {
            abort(404, 'Siswa tidak terdaftar di kursus ini.');
        }

        $course->load('modules.lessons', 'instructor');
        $completedLessonIds = $student->completedLessons()->pluck('lessons.id')->toArray();

        $pointHistories = PointHistory::where('user_id', $student->id)
            ->where('course_id', $course->id)
            ->whereNotNull('lesson_id')
            ->get()
            ->keyBy('lesson_id');

        $assignmentSubmissions = AssignmentSubmission::where('user_id', $student->id)
            ->get()
            ->keyBy('assignment_id');

        $siteSettings = SiteSetting::firstOrNew();

        return view('superadmin.point-sync.student-progress', compact(
            'course',
            'student',
            'completedLessonIds',
            'pointHistories',
            'assignmentSubmissions',
            'siteSettings'
        ));
    }

    /**
     * Execute sync points for a single student on a specific lesson.
     */
    public function syncPoints(Request $request, Course $course, User $student)
    {
        $validated = $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'expected_points' => 'required|integer|min:0',
            'description' => 'required|string',
        ]);

        $lesson = Lesson::findOrFail($validated['lesson_id']);

        DB::transaction(function () use ($course, $student, $lesson, $validated) {
            $existingHistory = PointHistory::where('user_id', $student->id)
                ->where('course_id', $course->id)
                ->where('lesson_id', $lesson->id)
                ->first();

            $pointDifference = 0;

            if ($existingHistory) {
                $pointDifference = $validated['expected_points'] - $existingHistory->points;
                $existingHistory->update([
                    'points' => $validated['expected_points'],
                    'description' => $validated['description']
                ]);
            } else {
                $pointDifference = $validated['expected_points'];
                PointHistory::create([
                    'user_id' => $student->id,
                    'course_id' => $course->id,
                    'lesson_id' => $lesson->id,
                    'points' => $validated['expected_points'],
                    'description' => $validated['description']
                ]);
            }

            if ($pointDifference != 0) {
                $courseUser = CourseUser::where('user_id', $student->id)
                    ->where('course_id', $course->id)
                    ->first();
                if ($courseUser) {
                    $courseUser->increment('points_earned', $pointDifference);
                } else {
                    CourseUser::create([
                        'user_id' => $student->id,
                        'course_id' => $course->id,
                        'points_earned' => $pointDifference
                    ]);
                }
            }
        });

        return back()->with('success', 'Poin siswa berhasil disinkronkan.');
    }

    /**
     * Get all point anomalies for a given course across all enrolled students.
     */
    private function getMassSyncAnomalies(Course $course)
    {
        $siteSettings = SiteSetting::firstOrNew();
        $course->load('modules.lessons.lessonable');
        $students = $course->students()->get();

        $anomalies = [];

        foreach ($students as $student) {
            $completedLessonIds = $student->completedLessons()->pluck('lessons.id')->toArray();

            $pointHistories = PointHistory::where('user_id', $student->id)
                ->where('course_id', $course->id)
                ->whereNotNull('lesson_id')
                ->get()
                ->keyBy('lesson_id');

            $assignmentSubmissions = AssignmentSubmission::where('user_id', $student->id)
                ->get()
                ->keyBy('assignment_id');

            foreach ($course->modules as $module) {
                foreach ($module->lessons as $lesson) {
                    $typeStr = class_basename($lesson->lessonable_type);
                    $potentialPoints = 0;
                    $syncDescription = '';
                    $displayType = '';

                    if ($typeStr == 'LessonArticle') {
                        $potentialPoints = $siteSettings->points_for_article;
                        $displayType = 'Artikel';
                        $syncDescription = 'Menyelesaikan artikel: ' . $lesson->title;
                    } elseif ($typeStr == 'LessonVideo') {
                        $potentialPoints = $siteSettings->points_for_video;
                        $displayType = 'Video';
                        $syncDescription = 'Menyelesaikan video: ' . $lesson->title;
                    } elseif ($typeStr == 'LessonDocument') {
                        $potentialPoints = $siteSettings->points_for_document;
                        $displayType = 'Dokumen / Slide';
                        $syncDescription = 'Menyelesaikan dokumen: ' . $lesson->title;
                    } elseif ($typeStr == 'Quiz') {
                        $potentialPoints = $siteSettings->points_for_quiz;
                        $displayType = 'Kuis';
                        $syncDescription = 'Lulus kuis: ' . $lesson->title;
                    } elseif ($typeStr == 'LessonAssignment') {
                        $potentialPoints = $siteSettings->points_for_assignment;
                        $displayType = 'Tugas';
                        $syncDescription = 'Mengirimkan tugas: ' . $lesson->title;
                    } elseif ($typeStr == 'LessonPolling') {
                        $potentialPoints = $siteSettings->points_for_polling;
                        $displayType = 'Polling';
                        $syncDescription = 'Mengisi polling: ' . $lesson->title;
                    } elseif ($typeStr == 'LessonWordcloud') {
                        $potentialPoints = $siteSettings->points_for_wordcloud;
                        $displayType = 'Word Cloud';
                        $syncDescription = 'Mengisi word cloud: ' . $lesson->title;
                    }

                    $isCompleted = in_array($lesson->id, $completedLessonIds);
                    $assignmentStatus = null;
                    if ($typeStr == 'LessonAssignment' && isset($assignmentSubmissions[$lesson->lessonable_id])) {
                        $assignmentStatus = $assignmentSubmissions[$lesson->lessonable_id]->status;
                    }

                    $actualPoints = isset($pointHistories[$lesson->id]) ? $pointHistories[$lesson->id]->points : 0;

                    if ($isCompleted || in_array($assignmentStatus, ['submitted', 'revision_required'])) {
                        if ($actualPoints != $potentialPoints) {
                            $anomalies[] = [
                                'student' => $student,
                                'lesson' => $lesson,
                                'display_type' => $displayType,
                                'actual_points' => $actualPoints,
                                'expected_points' => $potentialPoints,
                                'description' => $syncDescription,
                                'is_missing' => !isset($pointHistories[$lesson->id])
                            ];
                        }
                    }
                }
            }
        }

        return collect($anomalies);
    }
}
