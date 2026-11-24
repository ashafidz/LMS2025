<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\PointHistory;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\PointLedger;
use App\Services\StudentWriteLock;
use Illuminate\Http\Request;

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

        // One student at a time under the student's write lock, so a sync never races with the student's own
        // actions. Lesson rows are fixed first, then the course total is rebuilt from the history.
        foreach ($anomalies->groupBy(fn ($anomaly) => $anomaly['student']->id) as $studentAnomalies) {
            $student = $studentAnomalies->first()['student'];

            StudentWriteLock::run($student, function () use ($course, $student, $studentAnomalies) {
                foreach ($studentAnomalies as $anomaly) {
                    if ($anomaly['lesson']) {
                        $this->writeLessonPoints($student, $course, $anomaly['lesson'], $anomaly['expected_points'], $anomaly['description']);
                    }
                }

                PointLedger::recalculate($student->id, $course->id);
            });
        }

        return redirect()->route('superadmin.point-sync.mass-sync.preview', $course)
            ->with('success', 'Sinkronisasi massal berhasil dieksekusi. '.count($anomalies).' riwayat poin telah diperbaiki.');
    }

    /**
     * Show student progress checklist for a specific course (with sync buttons).
     */
    public function studentProgress(Course $course, User $student)
    {
        $isEnrolled = $course->students()->where('user_id', $student->id)->exists();
        if (! $isEnrolled) {
            abort(404, 'Siswa tidak terdaftar di kursus ini.');
        }

        $course->load('modules.lessons', 'instructor');
        $completedLessonIds = $student->completedLessons()->pluck('lessons.id')->toArray();

        [$lessonPoints, $otherPointHistories, $totalPoints] = PointLedger::progressFor($student->id, $course->id);

        $assignmentSubmissions = AssignmentSubmission::where('user_id', $student->id)
            ->get()
            ->keyBy('assignment_id');

        $siteSettings = SiteSetting::firstOrNew();

        return view('superadmin.point-sync.student-progress', compact(
            'course',
            'student',
            'completedLessonIds',
            'lessonPoints',
            'otherPointHistories',
            'totalPoints',
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

        // The lesson must belong to this course
        abort_unless((int) $lesson->module?->course_id === (int) $course->id, 404);

        StudentWriteLock::run($student, function () use ($course, $student, $lesson, $validated) {
            $this->writeLessonPoints($student, $course, $lesson, (int) $validated['expected_points'], $validated['description']);
            PointLedger::recalculate($student->id, $course->id);
        });

        return back()->with('success', 'Poin siswa berhasil disinkronkan.');
    }

    /**
     * Keep exactly one history row for this student and lesson (the oldest) with the expected points.
     */
    private function writeLessonPoints(User $student, Course $course, Lesson $lesson, int $points, string $description): void
    {
        $rows = PointHistory::where('user_id', $student->id)
            ->where('course_id', $course->id)
            ->where('lesson_id', $lesson->id)
            ->orderBy('id')
            ->get();

        if ($rows->isEmpty()) {
            PointHistory::create([
                'user_id' => $student->id,
                'course_id' => $course->id,
                'lesson_id' => $lesson->id,
                'points' => $points,
                'description' => $description,
            ]);

            return;
        }

        $rows->first()->update(['points' => $points, 'description' => $description]);
        PointHistory::whereIn('id', $rows->slice(1)->pluck('id'))->delete();
    }

    /**
     * Get all point anomalies for a given course across all enrolled students:
     * - a completed lesson whose recorded points differ from the current setting (or are missing),
     * - a lesson with more than one history row (duplicate award), except manual-points lessons,
     * - a course total (course_user) that differs from the history.
     */
    private function getMassSyncAnomalies(Course $course)
    {
        $siteSettings = SiteSetting::firstOrNew();
        $course->load('modules.lessons.lessonable');
        $students = $course->students()->get();

        $settingByType = [
            'LessonArticle' => ['points_for_article', 'Artikel', 'Menyelesaikan artikel: '],
            'LessonVideo' => ['points_for_video', 'Video', 'Menyelesaikan video: '],
            'LessonDocument' => ['points_for_document', 'Dokumen / Slide', 'Menyelesaikan dokumen: '],
            'Quiz' => ['points_for_quiz', 'Kuis', 'Lulus kuis: '],
            'LessonAssignment' => ['points_for_assignment', 'Tugas', 'Mengirimkan tugas: '],
            'LessonPolling' => ['points_for_polling', 'Polling', 'Mengisi polling: '],
            'LessonWordcloud' => ['points_for_wordcloud', 'Word Cloud', 'Mengisi word cloud: '],
        ];

        $drift = PointLedger::drift($course->id)->keyBy('user_id');

        $anomalies = [];

        foreach ($students as $student) {
            $completedLessonIds = $student->completedLessons()->pluck('lessons.id')->toArray();

            $historiesByLesson = PointHistory::where('user_id', $student->id)
                ->where('course_id', $course->id)
                ->whereNotNull('lesson_id')
                ->get()
                ->groupBy('lesson_id');

            $assignmentSubmissions = AssignmentSubmission::where('user_id', $student->id)
                ->get()
                ->keyBy('assignment_id');

            $studentHasLessonFix = false;

            foreach ($course->modules as $module) {
                foreach ($module->lessons as $lesson) {
                    $typeStr = class_basename($lesson->lessonable_type);

                    // Manual points (LessonPoint) and lessons without points are never anomalies
                    if (! isset($settingByType[$typeStr])) {
                        continue;
                    }

                    [$settingKey, $displayType, $prefix] = $settingByType[$typeStr];
                    $potentialPoints = (int) $siteSettings->{$settingKey};
                    $rows = $historiesByLesson->get($lesson->id, collect());
                    $actualPoints = (int) $rows->sum('points');

                    $isCompleted = in_array($lesson->id, $completedLessonIds);
                    $assignmentStatus = null;
                    if ($typeStr == 'LessonAssignment' && isset($assignmentSubmissions[$lesson->lessonable_id])) {
                        $assignmentStatus = $assignmentSubmissions[$lesson->lessonable_id]->status;
                    }
                    $shouldHavePoints = $isCompleted || in_array($assignmentStatus, ['submitted', 'revision_required']);

                    $isDuplicate = $rows->count() > 1;
                    $isWrong = $shouldHavePoints && $actualPoints != $potentialPoints;

                    if ($isDuplicate || $isWrong) {
                        $studentHasLessonFix = true;
                        $anomalies[] = [
                            'kind' => $isDuplicate ? 'duplicate' : 'lesson',
                            'student' => $student,
                            'lesson' => $lesson,
                            'display_type' => $displayType,
                            'actual_points' => $actualPoints,
                            // A duplicate on a lesson the student no longer qualifies for keeps one row as it was
                            'expected_points' => $shouldHavePoints ? $potentialPoints : (int) $rows->first()->points,
                            'description' => $prefix.$lesson->title,
                            'is_missing' => $rows->isEmpty(),
                        ];
                    }
                }
            }

            // Total differs from the history and no lesson fix above will rebuild it anyway
            if (! $studentHasLessonFix && $drift->has($student->id)) {
                $row = $drift->get($student->id);
                $anomalies[] = [
                    'kind' => 'total',
                    'student' => $student,
                    'lesson' => null,
                    'display_type' => 'Total kursus',
                    'actual_points' => $row->counter,
                    'expected_points' => $row->ledger,
                    'description' => null,
                    'is_missing' => false,
                ];
            }
        }

        return collect($anomalies);
    }
}
