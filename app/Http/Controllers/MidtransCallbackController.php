<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\MidtransService;
use App\Services\OrderSettlementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransCallbackController extends Controller
{
    /**
     * Handle payment notification from Midtrans.
     */
    public function handle(
        Request $request,
        MidtransService $midtransService,
        OrderSettlementService $settlementService
    ): JsonResponse {
        $payload = $request->all();

        if (! $midtransService->isValidSignature($payload)) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $orderCode = $payload['order_id'] ?? null;
        $order = Order::where('order_code', $orderCode)->first();

        if (! $order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $payloadGrossAmount = (int) round((float) ($payload['gross_amount'] ?? 0));
        $orderFinalAmount = (int) round((float) $order->final_amount);

        if ($payloadGrossAmount !== $orderFinalAmount) {
            Log::warning('Midtrans gross_amount mismatch', [
                'order_code' => $orderCode,
                'payload_gross_amount' => $payloadGrossAmount,
                'order_final_amount' => $orderFinalAmount,
            ]);

            return response()->json(['message' => 'Gross amount mismatch'], 422);
        }

        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus = $payload['fraud_status'] ?? '';

        if (
            ($transactionStatus === 'capture' && $fraudStatus === 'accept')
            || $transactionStatus === 'settlement'
        ) {
            $settlementService->markPaid($order);
        } elseif (in_array($transactionStatus, ['deny', 'cancel', 'failure'], true)) {
            $settlementService->markFailed($order, 'failed');
        } elseif ($transactionStatus === 'expire') {
            $settlementService->markFailed($order, 'failed');
        }

        return response()->json(['message' => 'OK'], 200);
    }
}
