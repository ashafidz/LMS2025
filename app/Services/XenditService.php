<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Order;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XenditService
{
    /**
     * Buat invoice Xendit untuk sebuah order.
     *
     * @return array{id: string, invoice_url: string}
     *
     * @throws \RuntimeException
     */
    public function createInvoice(Order $order): array
    {
        $order->loadMissing('user');
        $user = $order->user;

        $response = $this->client()->post($this->baseUrl().'/v2/invoices', [
            'external_id' => $order->order_code,
            'amount' => (int) round($order->final_amount),
            'payer_email' => $user->email,
            'description' => 'Pembelian kursus '.config('app.name').' ('.$order->order_code.')',
            'currency' => 'IDR',
            'invoice_duration' => Coupon::RESERVATION_HOURS * 3600,
            'success_redirect_url' => route('payment.success', $order),
            'failure_redirect_url' => route('payment.failed', $order),
            'customer' => [
                'given_names' => $user->name,
                'email' => $user->email,
            ],
        ]);

        $id = $response->json('id');
        $url = $response->json('invoice_url');

        if (! $response->successful() || ! $id || ! $url) {
            Log::warning('Xendit createInvoice gagal', [
                'order_code' => $order->order_code,
                'status' => $response->status(),
                'error_code' => $response->json('error_code'),
            ]);

            throw new \RuntimeException('Gagal membuat invoice Xendit.');
        }

        return ['id' => (string) $id, 'invoice_url' => (string) $url];
    }

    /**
     * Ambil status invoice dari Xendit (query server ke server).
     *
     * @return array{status: string, amount: int}|array{}
     */
    public function fetchStatus(Order $order): array
    {
        if (! $order->xendit_invoice_id) {
            return [];
        }

        try {
            $response = $this->client()->get($this->baseUrl().'/v2/invoices/'.$order->xendit_invoice_id);

            if (! $response->successful()) {
                Log::warning('Xendit fetchStatus gagal', [
                    'order_code' => $order->order_code,
                    'status' => $response->status(),
                ]);

                return [];
            }

            return $this->normalize((array) $response->json());
        } catch (\Throwable $e) {
            Log::warning('Xendit fetchStatus error: '.$e->getMessage(), [
                'order_code' => $order->order_code,
            ]);

            return [];
        }
    }

    /**
     * Ubah payload invoice Xendit (API atau webhook) menjadi format netral.
     *
     * @return array{status: 'paid'|'failed'|'pending', amount: int}
     */
    public function normalize(array $invoice): array
    {
        $status = match (strtoupper((string) ($invoice['status'] ?? ''))) {
            'PAID', 'SETTLED' => 'paid',
            'EXPIRED' => 'failed',
            default => 'pending',
        };

        return [
            'status' => $status,
            'amount' => (int) round((float) ($invoice['amount'] ?? 0)),
        ];
    }

    public function isValidCallbackToken(?string $token): bool
    {
        $expected = (string) config('xendit.callback_token');

        if ($expected === '' || $token === null || $token === '') {
            return false;
        }

        return hash_equals($expected, $token);
    }

    private function baseUrl(): string
    {
        return rtrim((string) config('xendit.base_url'), '/');
    }

    private function client(): PendingRequest
    {
        $secret = (string) config('xendit.secret_key');

        if ($secret === '') {
            throw new \RuntimeException('XENDIT_SECRET_KEY belum diisi.');
        }

        return Http::withBasicAuth($secret, '')->acceptJson()->timeout(15);
    }
}
