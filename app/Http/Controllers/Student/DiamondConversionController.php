<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\DiamondService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DiamondConversionController extends Controller
{
    /**
     * Memproses permintaan konversi poin ke diamond.
     */
    public function convert(Request $request, Course $course)
    {
        $user = Auth::user();

        // 1. Validasi Kelayakan di Sisi Server (Sangat Penting untuk Keamanan)
        $isEligible = $this->checkEligibility($user, $course);
        if (!$isEligible) {
            return redirect()->back()->with('error', 'Anda belum memenuhi syarat untuk mengonversi poin dari kursus ini.');
        }

        // 2. Panggil DiamondService untuk melakukan konversi
        $conversionSuccess = DiamondService::convertFromPoints($user, $course);

        if ($conversionSuccess) {
            return redirect()->route('student.points.index')->with('success', 'Selamat! Poin Anda berhasil dikonversi menjadi diamond.');
        }

        return redirect()->back()->with('error', 'Terjadi kesalahan atau poin dari kursus ini sudah pernah dikonversi.');
    }

    /**
     * Helper method untuk memeriksa kelayakan konversi.
     */
    private function checkEligibility($user, $course): bool
    {
        // Syarat 1: Progres belajar harus 100%
        $totalLessons = $course->lessons()->count();
        if ($totalLessons == 0) return false; // Tidak bisa konversi jika tidak ada pelajaran

        $completedLessonsCount = $user->completedLessons()->whereIn('lesson_id', $course->lessons->pluck('id'))->count();
        if ($completedLessonsCount < $totalLessons) {
            return false;
        }

        // Syarat 2: Harus sudah memberikan ulasan untuk kursus
        $hasReviewed = $course->reviews()->where('user_id', $user->id)->exists();
        if (!$hasReviewed) {
            return false;
        }

        return true;
    }
}
