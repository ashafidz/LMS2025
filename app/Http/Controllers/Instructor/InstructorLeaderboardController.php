<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseUser;
use App\Models\Module;
use App\Models\PointHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class InstructorLeaderboardController extends Controller
{
    /**
     * Mengambil data leaderboard DAN daftar siswa untuk keseluruhan kursus.
     */
    public function courseLeaderboard(Course $course)
    {
        Gate::authorize('manage', $course);

        // 1. Poin semua siswa di kursus ini (bukan hanya 100 teratas: siswa di luar 100 dulu tampil 0)
        $pointsByUser = CourseUser::where('course_id', $course->id)
            ->get(['user_id', 'points_earned', 'updated_at'])
            ->keyBy('user_id');

        // 2. Ambil semua siswa yang terdaftar di kursus dan urutkan berdasarkan unique_id_number
        $enrolledStudents = $course->students()
            ->join('student_profiles', 'users.id', '=', 'student_profiles.user_id')
            ->orderByRaw('
                CASE 
                    WHEN student_profiles.unique_id_number IS NULL THEN 1
                    WHEN student_profiles.unique_id_number = "" THEN 1
                    ELSE 0 
                END ASC,
                CAST(student_profiles.unique_id_number AS UNSIGNED) ASC
            ')
            ->select('users.*')
            ->get();

        // Gabungkan data poin ke dalam data siswa
        foreach ($enrolledStudents as $student) {
            $row = $pointsByUser->get($student->id);
            $student->points_earned = (int) ($row->points_earned ?? 0);
            // Poin sama: yang lebih dulu mencapai poin itu di atas; tanpa poin di paling bawah
            $student->reached_at = $row?->updated_at?->getTimestamp() ?? PHP_INT_MAX;
        }

        // 3. Buat collection terpisah untuk tab "Peringkat" (urut poin, lalu waktu mencapai, lalu id)
        $enrolledStudentsByPoints = $enrolledStudents->sortBy([
            ['points_earned', 'desc'],
            ['reached_at', 'asc'],
            ['id', 'asc'],
        ])->values();

        $title = 'Data Siswa & Peringkat: '.$course->title;

        // Render partial view baru dan kirim sebagai respons
        $html = view('instructor.leaderboards._course_data_modal', compact(
            'course',
            'enrolledStudents',        // Untuk tab "Daftar Siswa" (urut berdasarkan NIM)
            'enrolledStudentsByPoints', // Untuk tab "Peringkat" (urut berdasarkan poin)
            'title'
        ))->render();

        return response()->json(['html' => $html]);
    }

    /**
     * Mengambil data leaderboard untuk satu modul spesifik.
     */
    public function moduleLeaderboard(Module $module)
    {
        Gate::authorize('manage', $module->course);

        $course = $module->course;
        $lessonIds = $module->lessons()->pluck('id');

        $leaderboardRanks = PointHistory::whereIn('lesson_id', $lessonIds)
            ->select('user_id', DB::raw('SUM(points) as total_points'), DB::raw('MAX(created_at) as reached_at'))
            ->groupBy('user_id')
            ->whereHas('user.enrollments', function ($query) use ($course) {
                $query->where('course_id', $course->id);
            })
            ->orderByDesc('total_points')
            ->orderBy('reached_at')
            ->orderBy('user_id')
            ->with('user')
            ->get();

        $title = 'Papan Peringkat Modul: '.$module->title;

        $html = view('instructor.leaderboards._modal_content', compact('leaderboardRanks', 'title'))->render();

        return response()->json(['html' => $html]);
    }
}
