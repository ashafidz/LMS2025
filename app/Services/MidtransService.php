<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class MidtransService
{
    /**
     * Configure Midtrans settings from configuration.
     */
    public function configure(): void
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = (bool) config('midtrans.is_production', false);
        Config::$isSanitized = (bool) config('midtrans.is_sanitized', true);
        Config::$is3ds = (bool) config('midtrans.is_3ds', true);
    }

    /**
     * Generate Snap token for an order.
     */
    public function createSnapToken(Order $order): string
    {
        $this->configure();

        $order->loadMissing('user');

        $params = [
            'transaction_details' => [
                'order_id' => $order->order_code,
                'gross_amount' => (int) round($order->final_amount),
            ],
            'customer_details' => [
                'first_name' => $order->user->name,
                'email' => $order->user->email,
            ],
        ];

        return Snap::getSnapToken($params);
    }

    /**
     * Fetch status of an order from Midtrans.
     */
    public function fetchStatus(string $orderCode): array
    {
        $this->configure();

        try {
            $response = (array) Transaction::status($orderCode);

            return [
                'transaction_status' => $response['transaction_status'] ?? null,
                'fraud_status' => $response['fraud_status'] ?? null,
                'gross_amount' => $response['gross_amount'] ?? null,
                'status_code' => $response['status_code'] ?? null,
            ];
        } catch (\Throwable $e) {
            Log::warning('Midtrans fetchStatus error: '.$e->getMessage(), [
                'order_code' => $orderCode,
            ]);

            return [];
        }
    }

    /**
     * Validate signature key against payload.
     */
    public function isValidSignature(array $payload): bool
    {
        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $signatureKey = (string) ($payload['signature_key'] ?? '');
        $serverKey = (string) config('midtrans.server_key');

        if ($signatureKey === '' || $serverKey === '') {
            return false;
        }

        $expectedSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        return hash_equals($expectedSignature, $signatureKey);
    }
}
