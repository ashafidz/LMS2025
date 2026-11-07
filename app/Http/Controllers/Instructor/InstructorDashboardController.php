<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseReview;
use App\Models\LessonDiscussion;
use App\Services\HashIdService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InstructorDashboardController extends Controller
{
    /**
     * Menampilkan halaman dasbor instruktur dengan data statistik.
     */
    public function index()
    {
        $instructor = Auth::user();

        // Ambil semua ID kursus milik instruktur
        $courseIds = $instructor->courses()->pluck('id');

        // 1. Hitung Total Kursus
        $totalCourses = $courseIds->count();

        // 2. Hitung Total Siswa Unik
        $totalStudents = DB::table('course_enrollments')
            ->whereIn('course_id', $courseIds)
            ->distinct('user_id')
            ->count('user_id');

        // 3. Ambil data kursus dengan jumlah siswa dan pelajarannya
        $courses = $instructor->courses()
            ->withCount('students', 'lessons')
            ->latest()
            ->get();

        // 4. Hitung Siswa Selesai vs Belum Selesai (agregat)
        // $totalCompletedStudents = 0;
        // $totalInProgressStudents = 0;

        // foreach ($courses as $course) {
        //     $completedCount = 0;
        //     if ($course->lessons_count > 0) {
        //         // FIX: Specify table name untuk kolom id yang ambiguous
        //         $lessonIds = $course->lessons()->select('lessons.id')->pluck('lessons.id');

        //         // Hitung berapa banyak siswa yang telah menyelesaikan SEMUA pelajaran di kursus ini
        //         $completedCount = DB::table('course_enrollments')
        //             ->where('course_id', $course->id)
        //             ->whereExists(function ($query) use ($lessonIds, $course) {
        //                 $query->select(DB::raw(1))
        //                     ->from('lesson_user')
        //                     ->whereColumn('lesson_user.user_id', 'course_enrollments.user_id')
        //                     ->whereIn('lesson_user.lesson_id', $lessonIds)
        //                     ->groupBy('lesson_user.user_id')
        //                     ->havingRaw('COUNT(DISTINCT lesson_user.lesson_id) = ?', [$course->lessons_count]);
        //             })
        //             ->count();
        //     }
        //     $course->completed_students_count = $completedCount;
        //     $course->inprogress_students_count = $course->students_count - $completedCount;

        //     $totalCompletedStudents += $course->completed_students_count;
        //     $totalInProgressStudents += $course->inprogress_students_count;
        // }
        $totalCompletedStudents = 0;
        $totalInProgressStudents = 0;

        // Get unique students who have completed all lessons in any course
        $completedStudents = collect();

        foreach ($courses as $course) {
            $completedCount = 0;
            if ($course->lessons_count > 0) {
                // FIX: Specify table name untuk kolom id yang ambiguous
                $lessonIds = $course->lessons()->select('lessons.id')->pluck('lessons.id');

                // Get IDs of students who completed this course
                $completedStudentIds = DB::table('course_enrollments')
                    ->where('course_id', $course->id)
                    ->whereExists(function ($query) use ($lessonIds, $course) {
                        $query->select(DB::raw(1))
                            ->from('lesson_user')
                            ->whereColumn('lesson_user.user_id', 'course_enrollments.user_id')
                            ->whereIn('lesson_user.lesson_id', $lessonIds)
                            ->groupBy('lesson_user.user_id')
                            ->havingRaw('COUNT(DISTINCT lesson_user.lesson_id) = ?', [$course->lessons_count]);
                    })
                    ->pluck('user_id');

                $completedCount = $completedStudentIds->count();
                $completedStudents = $completedStudents->merge($completedStudentIds);
            }

            $course->completed_students_count = $completedCount;
            $course->inprogress_students_count = $course->students_count - $completedCount;
        }

        // Count unique completed students
        $totalCompletedStudents = $completedStudents->unique()->count();
        // Make sure in-progress count + completed count equals total unique students
        $totalInProgressStudents = $totalStudents - $totalCompletedStudents;

        // --- Read-only extras for the "Perlu ditindaklanjuti" card and the side cards (own courses only) ---

        // Submissions waiting for a grade, grouped per assignment (one query). The link needs the assignment's hashed key.
        $toGrade = DB::table('assignment_submissions as s')
            ->join('lessons as l', function ($join) {
                $join->on('l.lessonable_id', '=', 's.assignment_id')
                    ->where('l.lessonable_type', '=', \App\Models\LessonAssignment::class);
            })
            ->join('modules as m', 'm.id', '=', 'l.module_id')
            ->join('courses as c', 'c.id', '=', 'm.course_id')
            ->where('c.instructor_id', $instructor->id)
            ->whereNull('c.deleted_at')
            ->where('s.status', 'submitted')
            ->groupBy('s.assignment_id', 'l.title', 'c.title')
            ->orderByRaw('MIN(s.submitted_at) asc')
            ->limit(5)
            ->get([
                's.assignment_id',
                'l.title as lesson_title',
                'c.title as course_title',
                DB::raw('count(*) as submissions_count'),
            ])
            ->map(function ($row) {
                $row->url = route('instructor.assignment.submissions', HashIdService::encode($row->assignment_id));

                return $row;
            });

        // Counts of the instructor's own courses that need attention, by status.
        $courseStates = $instructor->courses()
            ->whereIn('status', ['pending_review', 'rejected', 'draft'])
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($n) => (int) $n);

        $lessonIdsOfMine = DB::table('lessons')
            ->join('modules', 'modules.id', '=', 'lessons.module_id')
            ->whereIn('modules.course_id', $courseIds)
            ->select('lessons.id');

        $recentDiscussions = LessonDiscussion::query()
            ->whereIn('lesson_id', $lessonIdsOfMine)
            ->where('user_id', '!=', $instructor->id)
            ->where('is_deleted', false)
            ->with(['user:id,name', 'lesson:id,title,module_id', 'lesson.module:id,course_id', 'lesson.module.course:id,title'])
            ->latest()
            ->take(5)
            ->get();

        $recentReviews = CourseReview::query()
            ->whereIn('course_id', $courseIds)
            ->with(['user:id,name', 'course:id,title'])
            ->latest()
            ->take(3)
            ->get();

        return view('instructor.dashboard', compact(
            'totalCourses',
            'totalStudents',
            'totalCompletedStudents',
            'totalInProgressStudents',
            'courses',
            'toGrade',
            'courseStates',
            'recentDiscussions',
            'recentReviews'
        ));
    }
}
