<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
            ->get();

        // 4. Hitung Siswa Selesai vs Belum Selesai (agregat)
        $totalCompletedStudents = 0;
        $totalInProgressStudents = 0;

        foreach ($courses as $course) {
            $completedCount = 0;
            if ($course->lessons_count > 0) {
                // FIX: Specify table name untuk kolom id yang ambiguous
                $lessonIds = $course->lessons()->select('lessons.id')->pluck('lessons.id');

                // Hitung berapa banyak siswa yang telah menyelesaikan SEMUA pelajaran di kursus ini
                $completedCount = DB::table('course_enrollments')
                    ->where('course_id', $course->id)
                    ->whereExists(function ($query) use ($lessonIds, $course) {
                        $query->select(DB::raw(1))
                            ->from('lesson_user')
                            ->whereColumn('lesson_user.user_id', 'course_enrollments.user_id')
                            ->whereIn('lesson_user.lesson_id', $lessonIds)
                            ->groupBy('lesson_user.user_id')
                            ->havingRaw('COUNT(DISTINCT lesson_user.lesson_id) = ?', [$course->lessons_count]);
                    })
                    ->count();
            }
            $course->completed_students_count = $completedCount;
            $course->inprogress_students_count = $course->students_count - $completedCount;

            $totalCompletedStudents += $course->completed_students_count;
            $totalInProgressStudents += $course->inprogress_students_count;
        }

        return view('instructor.dashboard', compact(
            'totalCourses',
            'totalStudents',
            'totalCompletedStudents',
            'totalInProgressStudents',
            'courses'
        ));
    }
}
