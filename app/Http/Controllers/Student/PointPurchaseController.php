<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\PointService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PointPurchaseController extends Controller
{
    /**
     * Memproses pembelian kursus menggunakan poin.
     */
    public function purchase(Request $request, Course $course)
    {
        $user = Auth::user();

        // 1. Validasi Awal
        if ($course->payment_type !== 'points') {
            return back()->with('error', 'Kursus ini tidak dapat dibeli dengan poin.');
        }
        if ($user->points_balance < $course->points_price) {
            return back()->with('error', 'Poin Anda tidak cukup untuk membeli kursus ini.');
        }
        if ($user->enrollments()->where('course_id', $course->id)->exists()) {
            return back()->with('info', 'Anda sudah memiliki kursus ini.');
        }

        // 2. Proses Transaksi Poin
        DB::transaction(function () use ($user, $course) {
            // Gunakan PointService untuk mengurangi poin dan mencatat riwayat
            PointService::usePoints($user, $course->points_price, 'Membeli kursus: ' . $course->title);

            // Daftarkan siswa ke kursus
            $user->enrollments()->attach($course->id, ['enrolled_at' => now()]);
        });

        // 3. Arahkan ke halaman sukses atau halaman "Kursus Saya"
        return redirect()->route('student.my_courses')->with('success', 'Selamat! Anda berhasil membeli kursus "' . $course->title . '" dengan poin.');
    }
}
