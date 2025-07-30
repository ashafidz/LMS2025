<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\DiamondService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DiamondPurchaseController extends Controller
{
    /**
     * Memproses pembelian kursus menggunakan diamond.
     */
    public function purchase(Request $request, Course $course)
    {
        $user = Auth::user();

        // 1. Validasi Awal
        if ($course->payment_type !== 'diamonds') {
            return back()->with('error', 'Kursus ini tidak dapat dibeli dengan diamond.');
        }
        if ($user->diamond_balance < $course->diamond_price) {
            return back()->with('error', 'Diamond Anda tidak cukup untuk membeli kursus ini.');
        }
        if ($user->enrollments()->where('course_id', $course->id)->exists()) {
            return back()->with('info', 'Anda sudah memiliki kursus ini.');
        }

        // 2. Gunakan DiamondService untuk memproses transaksi
        $purchaseSuccess = DiamondService::useForPurchase($user, $course);

        if ($purchaseSuccess) {
            // 3. Arahkan ke halaman "Kursus Saya" dengan pesan sukses
            return redirect()->route('student.my_courses')->with('success', 'Selamat! Anda berhasil membeli kursus "' . $course->title . '" dengan diamond.');
        }

        return back()->with('error', 'Terjadi kesalahan saat memproses pembelian.');
    }
}
