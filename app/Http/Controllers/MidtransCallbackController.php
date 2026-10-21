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

        $result = $settlementService->applyGatewayStatus($order, $payload);

        if ($result === 'amount_mismatch') {
            Log::warning('Midtrans gross_amount mismatch', [
                'order_code' => $orderCode,
                'payload_gross_amount' => (int) round((float) ($payload['gross_amount'] ?? 0)),
                'order_final_amount' => (int) round((float) $order->final_amount),
            ]);

            return response()->json(['message' => 'Gross amount mismatch'], 422);
        }

        return response()->json(['message' => 'OK'], 200);
    }
}
