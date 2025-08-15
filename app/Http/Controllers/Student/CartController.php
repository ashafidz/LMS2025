<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SiteSetting;

class CartController extends Controller
{
    /**
     * Menampilkan halaman keranjang belanja dengan rincian biaya.
     */
    public function index()
    {

        $popularCourses = Course::where('status', 'published')
            ->withCount('students')
            ->orderBy('students_count', 'desc')
            ->take(10)
            ->get();





        $cartItems = Auth::user()->carts()->with('course')->get();
        $settings = SiteSetting::first(); // Ambil data pengaturan

        // Hitung Subtotal
        $subtotal = $cartItems->sum(function ($item) {
            return $item->course->price;
        });

        // Hitung Diskon dari Kupon (jika ada)
        $coupon = session()->get('coupon');
        $discount = 0;
        if ($coupon) {
            if ($coupon->type === 'fixed') {
                $discount = $coupon->value;
            } elseif ($coupon->type === 'percent') {
                $discount = ($subtotal * $coupon->value) / 100;
            }
        }

        $priceAfterDiscount = $subtotal - $discount;

        // Hitung PPN (berdasarkan harga setelah diskon)
        $vatAmount = ($priceAfterDiscount * $settings->vat_percentage) / 100;

        // Hitung Biaya Transaksi (berdasarkan harga setelah diskon + PPN)
        $subtotalBeforeFee = $priceAfterDiscount + $vatAmount;
        $transactionFee = $settings->transaction_fee_fixed + (($subtotalBeforeFee * $settings->transaction_fee_percentage) / 100);

        // Hitung Total Akhir
        $finalTotal = $subtotalBeforeFee + $transactionFee;
        if ($finalTotal < 0) {
            $finalTotal = 0;
        }

        return view('student.cart.index', compact(
            'cartItems',
            'subtotal',
            'coupon',
            'discount',
            'vatAmount',
            'transactionFee',
            'finalTotal',
            'popularCourses'
        ));
    }

    /**
     * Menambahkan kursus ke keranjang belanja.
     */
    public function add(Course $course)
    {
        $user = Auth::user();

        // Cek apakah kursus sudah ada di keranjang
        $existingCartItem = $user->carts()->where('course_id', $course->id)->first();
        if ($existingCartItem) {
            return back()->with('info', 'Kursus ini sudah ada di keranjang Anda.');
        }

        // Cek apakah pengguna sudah terdaftar di kursus ini
        // Anda perlu menambahkan relasi 'enrollments' di model User jika belum ada
        if ($user->enrollments()->where('course_id', $course->id)->exists()) {
            return back()->with('info', 'Anda sudah terdaftar di kursus ini.');
        }

        // Tambahkan ke keranjang
        $user->carts()->create([
            'course_id' => $course->id,
        ]);

        return redirect('/cart')->with('success', 'Kursus berhasil ditambahkan ke keranjang.');
    }

    /**
     * Menghapus item dari keranjang belanja.
     */
    public function remove(Cart $cart)
    {
        // Pastikan pengguna hanya bisa menghapus item dari keranjangnya sendiri
        if ($cart->user_id != Auth::id()) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus item ini.');
        }

        $cart->delete();

        // Hapus juga kupon jika keranjang menjadi kosong
        if (Auth::user()->carts()->count() === 0) {
            session()->forget('coupon');
        }

        return back()->with('success', 'Item berhasil dihapus dari keranjang.');
    }

    /**
     * Menerapkan kode kupon ke keranjang.
     */
    public function applyCoupon(Request $request)
    {
        $validated = $request->validate(['code' => 'required|string']);

        $coupon = Coupon::where('code', $validated['code'])->first();

        // Validasi Kupon
        if (!$coupon) {
            return back()->withErrors(['code' => 'Kode kupon tidak valid.']);
        }
        if (!$coupon->is_active) {
            return back()->withErrors(['code' => 'Kupon ini sudah tidak aktif.']);
        }
        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            return back()->withErrors(['code' => 'Kupon ini sudah kedaluwarsa.']);
        }
        if ($coupon->starts_at && $coupon->starts_at->isFuture()) {
            return back()->withErrors(['code' => 'Kupon ini belum mulai berlaku.']);
        }
        if ($coupon->max_uses && $coupon->uses_count >= $coupon->max_uses) {
            return back()->withErrors(['code' => 'Kuota penggunaan kupon ini sudah habis.']);
        }

        // Validasi lingkup kupon
        if ($coupon->course_id) {
            $cartHasApplicableCourse = Auth::user()->carts()->where('course_id', $coupon->course_id)->exists();
            if (!$cartHasApplicableCourse) {
                return back()->withErrors(['code' => 'Kupon ini tidak berlaku untuk kursus yang ada di keranjang Anda.']);
            }
        }

        // Simpan kupon ke session
        session()->put('coupon', $coupon);

        return back()->with('success', 'Kupon berhasil diterapkan.');
    }

    /**
     * Menghapus kupon yang sedang diterapkan.
     */
    public function removeCoupon()
    {
        session()->forget('coupon');
        return back()->with('success', 'Kupon berhasil dihapus.');
    }
}
