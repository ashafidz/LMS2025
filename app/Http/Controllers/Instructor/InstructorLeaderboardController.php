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
     * Mengambil data leaderboard untuk keseluruhan kursus.
     */
    public function courseLeaderboard(Course $course)
    {
        // Ambil peringkat berdasarkan total poin di tabel pivot course_user
        $leaderboardRanks = $course->points()
            ->with('user')
            ->orderBy('points_earned', 'desc')
            ->take(100)
            ->get();

        $title = "Papan Peringkat: " . $course->title;

        // Render partial view dan kirim sebagai respons
        $html = view('instructor.leaderboards._modal_content', compact('leaderboardRanks', 'title'))->render();

        return response()->json(['html' => $html]);
    }

    /**
     * Mengambil data leaderboard untuk satu modul spesifik.
     */
    public function moduleLeaderboard(Module $module)
    {
        $lessonIds = $module->lessons()->pluck('id');

        $leaderboardRanks = PointHistory::whereIn('lesson_id', $lessonIds)
            ->select('user_id', DB::raw('SUM(points) as total_points'))
            ->groupBy('user_id')
            ->orderBy('total_points', 'desc')
            ->with('user')
            ->take(100)
            ->get();

        $title = "Papan Peringkat Modul: " . $module->title;

        $html = view('instructor.leaderboards._modal_content', compact('leaderboardRanks', 'title'))->render();

        return response()->json(['html' => $html]);
    }
}
