<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // 1. Import DB facade

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


        // --- LOGIKA BARU: Menyiapkan Data untuk Grafik ---
        $pointChanges = $user->pointHistories()
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(CASE WHEN points > 0 THEN points ELSE 0 END) as points_earned'),
                DB::raw('SUM(CASE WHEN points < 0 THEN points ELSE 0 END) as points_spent')
            )
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // Siapkan array untuk 12 bulan terakhir
        $chartLabels = [];
        $chartDataEarned = [];
        $chartDataSpent = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthKey = $month->format('Y-n'); // Format YYYY-M (e.g., 2025-8)
            $chartLabels[] = $month->format('M Y'); // Format Aug 2025

            $monthlyData = $pointChanges->first(function ($item) use ($month) {
                return $item->year == $month->year && $item->month == $month->month;
            });

            $chartDataEarned[] = $monthlyData ? $monthlyData->points_earned : 0;
            // Ambil nilai absolut untuk data poin yang dihabiskan
            $chartDataSpent[] = $monthlyData ? abs($monthlyData->points_spent) : 0;
        }
        // --- AKHIR LOGIKA BARU ---

        return view('student.my-points.index', compact(
            'user',
            'pointsPerCourse',
            'totalPoints',
            'pointHistories',
            'diamondHistories',
            'chartLabels', // Kirim data ke view
            'chartDataEarned', // Kirim data ke view
            'chartDataSpent' // Kirim data ke view
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
