<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentBadgeController extends Controller
{
    /**
     * Menampilkan halaman "Badge Saya".
     */
    public function index()
    {
        $user = Auth::user();

        // Ambil semua badge yang aktif, beserta informasi apakah user sudah memilikinya
        $allBadges = Badge::where('is_active', true)
            ->withExists(['users as has_badge' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }])
            ->get();

        // Contoh data progres (Anda bisa membuatnya lebih dinamis nanti)
        // Di sini kita akan menambahkan data progres secara manual untuk contoh
        foreach ($allBadges as $badge) {
            if (stripos($badge->title, 'Kuis') !== false) {
                $passedQuizzesCount = $user->quizAttempts()->where('status', 'passed')->count();
                $badge->progress_current = $passedQuizzesCount;
                $badge->progress_target = 10; // Asumsi target dari deskripsi
            } else {
                $badge->progress_current = 0;
                $badge->progress_target = 1;
            }
        }

        return view('student.my-badges.index', compact('allBadges'));
    }
}
