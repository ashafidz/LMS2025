<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Module;
use App\Models\PointHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InstructorLeaderboardController extends Controller
{
    /**
     * Mengambil data leaderboard DAN daftar siswa untuk keseluruhan kursus.
     */
    public function courseLeaderboard(Course $course)
    {
        // 1. Ambil peringkat berdasarkan total poin
        $leaderboardRanks = $course->points()
            ->with('user')
            ->whereHas('user.enrollments', function ($query) use ($course) {
                $query->where('course_id', $course->id);
            })
            ->orderBy('points_earned', 'desc')
            ->take(100)
            ->get();

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
            $rankData = $leaderboardRanks->firstWhere('user_id', $student->id);
            $student->points_earned = $rankData->points_earned ?? 0;
        }

        // 3. Buat collection terpisah untuk tab "Peringkat" (urut berdasarkan poin)
        $enrolledStudentsByPoints = $enrolledStudents->sortByDesc('points_earned')->values();

        $title = "Data Siswa & Peringkat: " . $course->title;

        // Render partial view baru dan kirim sebagai respons
        $html = view('instructor.leaderboards._course_data_modal', compact(
            'leaderboardRanks',
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
        $course = $module->course;
        $lessonIds = $module->lessons()->pluck('id');

        $leaderboardRanks = PointHistory::whereIn('lesson_id', $lessonIds)
            ->select('user_id', DB::raw('SUM(points) as total_points'))
            ->groupBy('user_id')
            ->whereHas('user.enrollments', function ($query) use ($course) {
                $query->where('course_id', $course->id);
            })
            ->orderBy('total_points', 'desc')
            ->with('user')
            ->take(100)
            ->get();

        $title = "Papan Peringkat Modul: " . $module->title;

        $html = view('instructor.leaderboards._modal_content', compact('leaderboardRanks', 'title'))->render();

        return response()->json(['html' => $html]);
    }
}
