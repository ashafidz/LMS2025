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
        // $leaderboardRanks = $course->points()
        //     ->with('user')
        //     ->orderBy('points_earned', 'desc')
        //     ->take(100)
        //     ->get();
        // 1. Ambil peringkat berdasarkan total poin
        $leaderboardRanks = $course->points()
            ->with('user')
            // DIPERBARUI: Tambahkan filter untuk memastikan siswa masih terdaftar
            ->whereHas('user.enrollments', function ($query) use ($course) {
                $query->where('course_id', $course->id);
            })
            ->orderBy('points_earned', 'desc')
            ->take(100)
            ->get();

        // 2. Ambil semua siswa yang terdaftar di kursus dan urutkan berdasarkan unique_id_number
        $enrolledStudents = $course->students()
            ->join('student_profiles', 'users.id', '=', 'student_profiles.user_id')
            ->orderBy('student_profiles.unique_id_number', 'asc')
            ->select('users.*') // Penting untuk menghindari konflik kolom dari tabel yang di-join
            ->get();

        // Gabungkan data poin ke dalam data siswa untuk ditampilkan di tab "Daftar Siswa"
        foreach ($enrolledStudents as $student) {
            $rankData = $leaderboardRanks->firstWhere('user_id', $student->id);
            $student->points_earned = $rankData->points_earned ?? 0;
        }

        $enrolledStudents = $enrolledStudents->sortByDesc('points_earned');
        $title = "Data Siswa & Peringkat: " . $course->title;

        // Render partial view baru dan kirim sebagai respons
        $html = view('instructor.leaderboards._course_data_modal', compact(
            'leaderboardRanks',
            'enrolledStudents',
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

        // $leaderboardRanks = PointHistory::whereIn('lesson_id', $lessonIds)
        //     ->select('user_id', DB::raw('SUM(points) as total_points'))
        //     ->groupBy('user_id')
        //     ->orderBy('total_points', 'desc')
        //     ->with('user')
        //     ->take(100)
        //     ->get();

        $leaderboardRanks = PointHistory::whereIn('lesson_id', $lessonIds)
            ->select('user_id', DB::raw('SUM(points) as total_points'))
            ->groupBy('user_id')
            // DIPERBARUI: Tambahkan filter untuk memastikan siswa masih terdaftar
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
