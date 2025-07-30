<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Midtrans\Config;
use Midtrans\Snap;

class CheckoutController extends Controller
{
    /**
     * Memproses item di keranjang menjadi sebuah pesanan (order).
     */
    public function process(Request $request)
    {
        $user = Auth::user();
        $cartItems = $user->carts()->with('course')->get();
        $settings = SiteSetting::firstOrFail();

        if ($cartItems->isEmpty()) {
            return redirect()->route('student.cart.index')->with('error', 'Keranjang Anda kosong.');
        }

        $subtotal = $cartItems->sum(fn($item) => $item->course->price);
        $coupon = session()->get('coupon');
        $discount = 0;
        $couponId = null;

        if ($coupon) {
            if ($coupon->type === 'fixed') {
                $discount = $coupon->value;
            } elseif ($coupon->type === 'percent') {
                $discount = ($subtotal * $coupon->value) / 100;
            }
            $couponId = $coupon->id;
        }

        $priceAfterDiscount = $subtotal - $discount;
        $vatAmount = ($priceAfterDiscount * $settings->vat_percentage) / 100;
        $subtotalBeforeFee = $priceAfterDiscount + $vatAmount;
        $transactionFee = $settings->transaction_fee_fixed + (($subtotalBeforeFee * $settings->transaction_fee_percentage) / 100);
        $finalTotal = max(0, $subtotalBeforeFee + $transactionFee);
        $vatPercentage = $settings->vat_percentage;

        if ($finalTotal == 0) {
            $order = $this->processFreeOrder($user, $cartItems, $subtotal, $discount, $vatAmount, $vatPercentage, $transactionFee, $couponId);
            // Untuk pesanan gratis, kita perlu meneruskan objek order ke view sukses
            return redirect()->route('payment.success', $order)->with('success_message', 'Anda berhasil mendapatkan kursus secara gratis!');
        }

        // Buat pesanan terlebih dahulu, LALU buat snap token
        $order = $this->createOrderRecord($user, $cartItems, $subtotal, $discount, $vatAmount, $vatPercentage, $transactionFee, $finalTotal, $couponId);
        // Setelah order dibuat dan punya ID, generate snap token dan simpan
        try {
            $this->generateAndSaveSnapToken($order);
        } catch (\Exception $e) {
            // Jika gagal membuat token, hapus order yang baru dibuat agar tidak jadi sampah
            $order->delete();
            // Kembalikan ke keranjang dengan pesan error
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
        // Cek apakah order masih bisa dibayar
        if ($order->status !== 'pending') {
            return redirect()->route('student.transactions.index')->with('info', 'Status pesanan ini adalah ' . $order->status . '.');
        }

        // Cek apakah snap_token ada. Jika tidak, redirect dengan error.
        if (!$order->snap_token) {
            return redirect()->route('student.transactions.index')->with('error', 'Sesi pembayaran untuk pesanan ini tidak ditemukan atau rusak.');
        }

        // Ambil client key dari config
        $midtransClientKey = config('midtrans.client_key');

        // Langsung tampilkan view dengan data yang sudah ada
        return view('student.checkout.show', [
            'order' => $order,
            'snapToken' => $order->snap_token, // Gunakan token dari DB
            'midtransClientKey' => $midtransClientKey
        ]);
    }

    // --- Helper Methods ---

    // Helper baru untuk MENCATAT order ke database
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
                    'price_at_purchase' => $cartItem->course->price
                ]);
            }

            return $order;
        });
    }

    // Helper baru untuk MEMBUAT DAN MENYIMPAN snap token
    private function generateAndSaveSnapToken(Order $order)
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $params = [
            'transaction_details' => [
                'order_id' => $order->order_code,
                'gross_amount' => $order->final_amount,
            ],
            'customer_details' => [
                'first_name' => $order->user->name,
                'email' => $order->user->email,
            ],
        ];

        // Request Snap Token ke Midtrans
        $snapToken = Snap::getSnapToken($params);

        // Simpan token ke database
        $order->snap_token = $snapToken;
        $order->save();
    }

    private function processFreeOrder($user, $cartItems, $subtotal, $discount, $vatAmount, $vatPercentage, $transactionFee, $couponId)
    {
        return DB::transaction(function () use ($user, $cartItems, $subtotal, $discount, $vatAmount, $vatPercentage, $transactionFee, $couponId) {
            $order = $user->orders()->create([
                'total_amount' => $subtotal,
                'discount_amount' => $discount,
                'vat_amount' => $vatAmount,
                'vat_percentage_at_purchase' => $vatPercentage,
                'transaction_fee_amount' => $transactionFee,
                'final_amount' => 0,
                'coupon_id' => $couponId,
                'status' => 'paid',
            ]);
            foreach ($cartItems as $cartItem) {
                $order->items()->create(['course_id' => $cartItem->course_id, 'price_at_purchase' => $cartItem->course->price]);
                $user->enrollments()->attach($cartItem->course_id, ['enrolled_at' => now()]);
            }
            $user->carts()->delete();
            session()->forget('coupon');
            return $order;
        });
    }
}
