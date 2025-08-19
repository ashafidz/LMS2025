<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GamificationController extends Controller
{
    /**
     * Menampilkan halaman "Poinku".
     */
    public function points()
    {
        $user = Auth::user();

        // // Ambil data poin per kursus dari tabel pivot
        // $pointsPerCourse = $user->coursePoints()->with('course')->simplePaginate(10);

        // // Ambil semua riwayat poin untuk modal
        // $pointHistories = $user->pointHistories()->with('course')->latest()->get()->groupBy('course_id');

        // CORRECT: The relationship already returns Course models.
        $pointsPerCourse = $user->coursePoints()->simplePaginate(10);

        // This line is correct because PointHistory has a 'course' relationship.
        $pointHistories = $user->pointHistories()->with('course')->latest()->get()->groupBy('course_id');

        // Hitung total poin dari semua kursus
        $totalPoints = $user->coursePoints()->sum('points_earned');

        // Ambil riwayat diamond, urutkan dari yang terbaru
        $diamondHistories = $user->diamondHistories()->latest()->simplePaginate(10);

        return view('student.my-points.index', compact(
            'user',
            'pointsPerCourse',
            'totalPoints',
            'pointHistories',
            'diamondHistories'
        ));
    }

    /**
     * Menampilkan halaman "Diamondku".
     */
    public function diamonds()
    {
        $user = Auth::user();

        // Ambil riwayat diamond, urutkan dari yang terbaru
        $diamondHistories = $user->diamondHistories()->latest()->simplePaginate(10);

        return view('student.my-diamonds.index', compact('user', 'diamondHistories'));
    }
}
