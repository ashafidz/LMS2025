<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
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

        // --- LOGIKA BARU: Menyiapkan Data untuk Grafik ---
        // Dikelompokkan di PHP (bukan YEAR()/MONTH() di SQL) agar jalan di MySQL maupun SQLite.
        $pointChanges = $user->pointHistories()
            ->select('created_at', 'points')
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->get()
            ->groupBy(fn ($item) => $item->created_at->format('Y-n'))
            ->map(fn ($items) => [
                'earned' => $items->where('points', '>', 0)->sum('points'),
                'spent' => $items->where('points', '<', 0)->sum('points'),
            ]);

        // Siapkan array untuk 12 bulan terakhir
        $chartLabels = [];
        $chartDataEarned = [];
        $chartDataSpent = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthKey = $month->format('Y-n'); // Format YYYY-M (e.g., 2025-8)
            $chartLabels[] = $month->format('M Y'); // Format Aug 2025

            $monthlyData = $pointChanges->get($monthKey);

            $chartDataEarned[] = $monthlyData ? $monthlyData['earned'] : 0;
            // Ambil nilai absolut untuk data poin yang dihabiskan
            $chartDataSpent[] = $monthlyData ? abs($monthlyData['spent']) : 0;
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
