<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\SiteSetting;
use App\Services\CartPricingService;
use App\Services\MidtransService;
use App\Services\OrderSettlementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    /**
     * Memproses item di keranjang menjadi sebuah pesanan (order).
     */
    public function process(Request $request, MidtransService $midtransService, CartPricingService $pricingService)
    {
        $user = Auth::user();

        if (Cart::pruneUnbuyableFor($user) > 0) {
            return redirect()->route('student.cart.index')->with('error', 'Beberapa kursus dikeluarkan dari keranjang karena tidak lagi tersedia untuk dibeli. Silakan periksa keranjang Anda.');
        }

        $cartItems = $user->carts()->with('course')->get();
        $settings = SiteSetting::first();

        if ($cartItems->isEmpty()) {
            return redirect()->route('student.cart.index')->with('error', 'Keranjang Anda kosong.');
        }

        $sessionCoupon = session()->get('coupon');
        $coupon = null;

        if ($sessionCoupon) {
            $coupon = Coupon::find($sessionCoupon->id);
            if (! $coupon) {
                session()->forget('coupon');

                return redirect()->route('student.cart.index')->with('error', 'Kupon tidak berlaku lagi.');
            }
        }

        $pricing = $pricingService->calculate($cartItems, $coupon, $settings);

        if ($sessionCoupon && $pricing['coupon'] === null) {
            session()->forget('coupon');

            return redirect()->route('student.cart.index')->with('error', 'Kupon dilepas karena kursusnya tidak ada lagi di keranjang.');
        }

        if ($coupon && ($reason = $coupon->unusableReasonFor($user))) {
            session()->forget('coupon');

            return redirect()->route('student.cart.index')->with('error', 'Kupon tidak bisa dipakai: '.$reason);
        }

        $subtotal = $pricing['subtotal'];
        $discount = $pricing['discount'];
        $vatPercentage = $pricing['vat_percentage'];
        $vatAmount = $pricing['vat_amount'];
        $transactionFee = $pricing['transaction_fee'];
        $finalTotal = $pricing['final_total'];
        $couponId = $pricing['coupon']?->id;

        if ($finalTotal == 0) {
            $order = $this->processFreeOrder($user, $cartItems, $subtotal, $discount, $vatAmount, $vatPercentage, $transactionFee, $couponId);

            return redirect()->route('payment.success', $order)->with('success_message', 'Anda berhasil mendapatkan kursus secara gratis!');
        }

        // Buat pesanan terlebih dahulu, LALU buat snap token
        $order = $this->createOrderRecord($user, $cartItems, $subtotal, $discount, $vatAmount, $vatPercentage, $transactionFee, $finalTotal, $couponId);

        // Request Snap Token ke Midtrans via MidtransService
        try {
            $snapToken = $midtransService->createSnapToken($order);
            $order->snap_token = $snapToken;
            $order->save();
        } catch (\Exception $e) {
            // Jika gagal membuat token, hapus order yang baru dibuat agar tidak jadi sampah
            $order->delete();

            return redirect()->route('student.cart.index')->with('error', 'Gagal memulai sesi pembayaran. Silakan coba lagi.');
        }

        // Hapus keranjang dan session coupon SETELAH SEMUA BERHASIL
        DB::transaction(function () use ($user) {
            $user->carts()->delete();
            session()->forget('coupon');
        });

        return redirect()->route('checkout.show', $order);
    }

    /**
     * Menampilkan halaman konfirmasi pembayaran.
     */
    public function show(Order $order)
    {
        abort_unless($order->user_id === Auth::id(), 403);

        // Cek apakah order masih bisa dibayar
        if ($order->status !== 'pending') {
            return redirect()->route('student.transactions.index')->with('info', 'Status pesanan ini adalah '.$order->status.'.');
        }

        // Cek apakah snap_token ada. Jika tidak, redirect dengan error.
        if (! $order->snap_token) {
            return redirect()->route('student.transactions.index')->with('error', 'Sesi pembayaran untuk pesanan ini tidak ditemukan atau rusak.');
        }

        // Ambil client key dari config
        $midtransClientKey = config('midtrans.client_key');

        // Langsung tampilkan view dengan data yang sudah ada
        return view('student.checkout.show', [
            'order' => $order,
            'snapToken' => $order->snap_token, // Gunakan token dari DB
            'midtransClientKey' => $midtransClientKey,
        ]);
    }

    // --- Helper Methods ---

    // Helper untuk MENCATAT order ke database
    private function createOrderRecord($user, $cartItems, $subtotal, $discount, $vatAmount, $vatPercentage, $transactionFee, $finalTotal, $couponId)
    {
        return DB::transaction(function () use ($user, $cartItems, $subtotal, $discount, $vatAmount, $vatPercentage, $transactionFee, $finalTotal, $couponId) {
            $order = $user->orders()->create([
                'total_amount' => $subtotal,
                'discount_amount' => $discount,
                'vat_amount' => $vatAmount,
                'vat_percentage_at_purchase' => $vatPercentage,
                'transaction_fee_amount' => $transactionFee,
                'final_amount' => $finalTotal,
                'coupon_id' => $couponId,
                'status' => 'pending',
                // snap_token akan diisi nanti
            ]);

            foreach ($cartItems as $cartItem) {
                $order->items()->create([
                    'course_id' => $cartItem->course_id,
                    'price_at_purchase' => $cartItem->course->price,
                ]);
            }

            return $order;
        });
    }

    private function processFreeOrder($user, $cartItems, $subtotal, $discount, $vatAmount, $vatPercentage, $transactionFee, $couponId)
    {
        $order = DB::transaction(function () use ($user, $cartItems, $subtotal, $discount, $vatAmount, $vatPercentage, $transactionFee, $couponId) {
            $order = $user->orders()->create([
                'total_amount' => $subtotal,
                'discount_amount' => $discount,
                'vat_amount' => $vatAmount,
                'vat_percentage_at_purchase' => $vatPercentage,
                'transaction_fee_amount' => $transactionFee,
                'final_amount' => 0,
                'coupon_id' => $couponId,
                'status' => 'pending',
            ]);

            foreach ($cartItems as $cartItem) {
                $order->items()->create([
                    'course_id' => $cartItem->course_id,
                    'price_at_purchase' => $cartItem->course->price,
                ]);
            }

            return $order;
        });

        app(OrderSettlementService::class)->markPaid($order);
        session()->forget('coupon');

        return $order;
    }
}
