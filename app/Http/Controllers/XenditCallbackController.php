<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderSettlementService;
use App\Services\XenditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class XenditCallbackController extends Controller
{
    /**
     * Handle invoice callback from Xendit.
     */
    public function handle(
        Request $request,
        XenditService $xendit,
        OrderSettlementService $settlementService
    ): JsonResponse {
        if (! $xendit->isValidCallbackToken($request->header('x-callback-token'))) {
            return response()->json(['message' => 'Invalid callback token'], 403);
        }

        $order = Order::where('order_code', $request->input('external_id'))->first();

        if (! $order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if (! $order->xendit_invoice_id || (string) $request->input('id') !== $order->xendit_invoice_id) {
            Log::warning('Xendit invoice id mismatch', ['order_code' => $order->order_code]);

            return response()->json(['message' => 'Invoice mismatch'], 422);
        }

        $normalized = $xendit->normalize($request->all());
        $result = $settlementService->applyGatewayStatus($order, $normalized);

        if ($result === 'amount_mismatch') {
            Log::warning('Xendit amount mismatch', [
                'order_code' => $order->order_code,
                'payload_amount' => $normalized['amount'],
                'order_final_amount' => (int) round((float) $order->final_amount),
            ]);

            return response()->json(['message' => 'Amount mismatch'], 422);
        }

        return response()->json(['message' => 'OK'], 200);
    }
}
