<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Menampilkan halaman keranjang belanja dengan rincian biaya dan kupon publik.
     */
    public function index()
    {
        $user = Auth::user();
        $cartItems = $user->carts()->with('course')->get();
        $settings = SiteSetting::first();

        $subtotal = $cartItems->sum(fn ($item) => $item->course->price);
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
        $vatAmount = ($priceAfterDiscount * ($settings?->vat_percentage ?? 0)) / 100;
        $subtotalBeforeFee = $priceAfterDiscount + $vatAmount;
        $transactionFee = ($settings?->transaction_fee_fixed ?? 0) + (($subtotalBeforeFee * ($settings?->transaction_fee_percentage ?? 0)) / 100);
        $finalTotal = max(0, $subtotalBeforeFee + $transactionFee);

        // --- LOGIKA BARU: Ambil Kupon Publik ---
        $courseIdsInCart = $cartItems->pluck('course_id');
        $publicCoupons = Coupon::where('is_public', true)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->where(function ($query) use ($courseIdsInCart) {
                // Tampilkan kupon yang berlaku untuk semua kursus ATAU yang berlaku untuk salah satu kursus di keranjang
                $query->whereNull('course_id')
                    ->orWhereIn('course_id', $courseIdsInCart);
            })
            ->get();
        // --- AKHIR LOGIKA BARU ---

        // LOGIKA BARU: Ambil 10 kursus terpopuler
        $popularCourses = Course::where('status', 'published')
            ->withCount('students')
            ->orderBy('students_count', 'desc')
            ->take(10)
            ->get();

        return view('student.cart.index', compact(
            'cartItems',
            'subtotal',
            'coupon',
            'discount',
            'vatAmount',
            'transactionFee',
            'finalTotal',
            'publicCoupons',
            'popularCourses'

        ));
    }

    /**
     * Menambahkan kursus ke keranjang belanja.
     */
    public function add(Course $course)
    {
        $user = Auth::user();
        if ($user->carts()->where('course_id', $course->id)->exists()) {
            return back()->with('info', 'Kursus ini sudah ada di keranjang Anda.');
        }
        if ($user->enrollments()->where('course_id', $course->id)->exists()) {
            return back()->with('info', 'Anda sudah terdaftar di kursus ini.');
        }
        $user->carts()->create(['course_id' => $course->id]);

        // return back()->with('success', 'Kursus berhasil ditambahkan ke keranjang.');
        return redirect()->route('student.cart.index')->with('success', 'Kursus berhasil ditambahkan ke keranjang.');
    }

    /**
     * Menghapus item dari keranjang belanja.
     */
    public function remove(Cart $cart)
    {
        if ($cart->user_id != Auth::id()) {
            abort(403);
        }
        $cart->delete();
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
        $user = Auth::user();
        $validated = $request->validate(['code' => 'required|string']);
        $coupon = Coupon::where('code', $validated['code'])->first();

        // Validasi Kupon
        if (! $coupon || ! $coupon->isUsableBy($user)) {
            return back()->withErrors(['code' => 'Kode kupon tidak valid atau sudah tidak berlaku.']);
        }

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
