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

        $order = $this->processPaidOrder($user, $cartItems, $subtotal, $discount, $vatAmount, $vatPercentage, $transactionFee, $finalTotal, $couponId);
        return redirect()->route('checkout.show', $order);
    }

    /**
     * Menampilkan halaman konfirmasi pembayaran dan membuat Snap Token baru.
     */
    public function show(Order $order)
    {
        if (!in_array($order->status, ['pending', 'failed', 'cancelled'])) {
            return redirect()->route('student.transactions.index')->with('info', 'Pesanan ini sudah tidak bisa dibayar.');
        }

        // Selalu buat Snap Token baru setiap kali halaman ini dikunjungi
        $this->generateSnapToken($order);

        $midtransClientKey = config('midtrans.client_key');

        // KODE YANG BENAR: Mengirim semua variabel yang dibutuhkan ke view
        return view('student.checkout.show', [
            'order' => $order,
            'snapToken' => $order->snap_token,
            'midtransClientKey' => $midtransClientKey
        ]);
    }

    // --- Helper Methods ---

    private function processPaidOrder($user, $cartItems, $subtotal, $discount, $vatAmount, $vatPercentage, $transactionFee, $finalTotal, $couponId)
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
            ]);
            foreach ($cartItems as $cartItem) {
                $order->items()->create(['course_id' => $cartItem->course_id, 'price_at_purchase' => $cartItem->course->price]);
            }
            $user->carts()->delete();
            session()->forget('coupon');
            return $order;
        });
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

    private function generateSnapToken(Order $order)
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
        $params = [
            'transaction_details' => ['order_id' => $order->order_code, 'gross_amount' => $order->final_amount],
            'customer_details' => ['first_name' => $order->user->name, 'email' => $order->user->email],
        ];
        $snapToken = Snap::getSnapToken($params);
        $order->snap_token = $snapToken;
        $order->save();
    }
}
