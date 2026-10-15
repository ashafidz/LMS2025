<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\MidtransService;
use App\Services\OrderSettlementService;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function success(
        Order $order,
        MidtransService $midtransService,
        OrderSettlementService $settlementService
    ) {
        // Pastikan hanya pemilik pesanan yang bisa mengakses
        // if ($order->user_id !== Auth::id()) {
        //     abort(403);
        // }
        abort_unless($order->user_id === Auth::id(), 403);

        if ($order->status === 'pending') {
            $statusData = $midtransService->fetchStatus($order->order_code);
            if (! empty($statusData)) {
                $settlementService->applyGatewayStatus($order, $statusData);
            }
        }

        $order->refresh();

        return view('student.checkout.success', compact('order'));
    }

    public function pending(Order $order)
    {
        // if ($order->user_id !== auth()->id()) {
        //     abort(403);
        // }
        abort_unless($order->user_id === Auth::id(), 403);

        // Status sudah 'pending' dari awal, jadi tidak perlu diubah
        return view('student.checkout.pending', compact('order'));
    }

    public function failed(Order $order)
    {
        // if ($order->user_id !== auth()->id()) {
        //     abort(403);
        // }
        abort_unless($order->user_id === Auth::id(), 403);

        // Do not change status client-side. Webhook decides.

        return view('student.checkout.failed', compact('order'));
    }

    /**
     * Menangani pesanan yang dibatalkan oleh pengguna.
     */
    public function cancelled(Order $order)
    {
        // if ($order->user_id !== auth()->id()) {
        //     abort(403);
        // }
        abort_unless($order->user_id === Auth::id(), 403);

        // Do not change status client-side. Webhook decides.

        return view('student.checkout.cancelled', compact('order'));
    }
}
