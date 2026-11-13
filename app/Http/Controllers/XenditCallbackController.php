<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderSettlementService;
use App\Services\XenditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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

        if ($this->shouldForwardToFlowPlay($request)) {
            return $this->forwardToFlowPlay($request);
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

    /**
     * Invoice FlowPlay memakai akun Xendit yang sama; callback-nya diteruskan, bukan diproses di sini.
     */
    private function shouldForwardToFlowPlay(Request $request): bool
    {
        $url = (string) config('xendit.flowplay_callback_url');
        $prefix = (string) config('xendit.flowplay_external_id_prefix');
        $externalId = $request->input('external_id');

        return $url !== '' && $prefix !== '' && is_string($externalId) && str_starts_with($externalId, $prefix);
    }

    private function forwardToFlowPlay(Request $request): JsonResponse
    {
        $externalId = (string) $request->input('external_id');

        try {
            $response = Http::withHeaders(['x-callback-token' => (string) $request->header('x-callback-token')])
                ->withBody($request->getContent(), 'application/json')
                // Redirect tidak diikuti: POST bisa berubah jadi GET (body hilang) dan header token ikut ke host lain.
                ->withoutRedirecting()
                ->timeout(15)
                ->post((string) config('xendit.flowplay_callback_url'));
        } catch (\Throwable $e) {
            Log::warning('Xendit FlowPlay relay failed', ['external_id' => $externalId, 'error' => get_class($e)]);

            return response()->json(['message' => 'Forward failed'], 502);
        }

        if ($response->successful()) {
            return response()->json(['message' => 'Forwarded'], 200);
        }

        Log::warning('Xendit FlowPlay relay non-2xx', ['external_id' => $externalId, 'status' => $response->status()]);

        if ($response->status() >= 400 && $response->status() < 500) {
            return response()->json(['message' => 'Forward rejected'], $response->status());
        }

        return response()->json(['message' => 'Forward failed'], 502);
    }
}
