<?php

namespace Tests\Feature\Payment;

use App\Models\Order;
use App\Services\XenditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class XenditServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'xendit.secret_key' => 'xnd_development_test',
            'xendit.callback_token' => 'tok',
            'xendit.base_url' => 'https://api.xendit.co',
        ]);
    }

    protected function order(array $attrs = []): Order
    {
        return Order::factory()->create(array_merge([
            'user_id' => $this->makeStudent()->id,
            'final_amount' => 111000.00,
        ], $attrs));
    }

    public function test_create_invoice_sends_expected_request(): void
    {
        Http::fake([
            'api.xendit.co/v2/invoices' => Http::response(['id' => 'inv_1', 'invoice_url' => 'https://checkout.xendit.co/web/inv_1'], 200),
        ]);
        $order = $this->order();

        $result = app(XenditService::class)->createInvoice($order);

        $this->assertSame(['id' => 'inv_1', 'invoice_url' => 'https://checkout.xendit.co/web/inv_1'], $result);

        Http::assertSent(function (Request $request) use ($order) {
            $body = $request->data();

            return $request->url() === 'https://api.xendit.co/v2/invoices'
                && $request->method() === 'POST'
                && $request->hasHeader('Authorization', 'Basic '.base64_encode('xnd_development_test:'))
                && $body['external_id'] === $order->order_code
                && $body['amount'] === 111000
                && is_int($body['amount'])
                && $body['invoice_duration'] === 86400
                && $body['currency'] === 'IDR'
                && $body['success_redirect_url'] === route('payment.success', $order)
                && $body['failure_redirect_url'] === route('payment.failed', $order);
        });
    }

    public function test_create_invoice_throws_on_http_failure(): void
    {
        Http::fake(['api.xendit.co/*' => Http::response(['error_code' => 'INVALID_API_KEY'], 401)]);

        $this->expectException(\RuntimeException::class);

        app(XenditService::class)->createInvoice($this->order());
    }

    public function test_create_invoice_throws_without_secret_and_sends_no_request(): void
    {
        config(['xendit.secret_key' => '']);
        Http::fake();

        try {
            app(XenditService::class)->createInvoice($this->order());
            $this->fail('RuntimeException expected');
        } catch (\RuntimeException $e) {
            Http::assertNothingSent();
            $this->assertTrue(true);
        }
    }

    public function test_fetch_status_without_invoice_id_returns_empty_without_request(): void
    {
        Http::fake();

        $this->assertSame([], app(XenditService::class)->fetchStatus($this->order()));
        Http::assertNothingSent();
    }

    public function test_fetch_status_returns_normalized_status(): void
    {
        Http::fake([
            'api.xendit.co/v2/invoices/inv_9' => Http::response(['id' => 'inv_9', 'status' => 'SETTLED', 'amount' => 111000], 200),
        ]);

        $result = app(XenditService::class)->fetchStatus($this->order(['xendit_invoice_id' => 'inv_9']));

        $this->assertSame(['status' => 'paid', 'amount' => 111000], $result);
    }

    public function test_fetch_status_returns_empty_on_error(): void
    {
        Http::fake(['api.xendit.co/*' => Http::response([], 500)]);

        $this->assertSame([], app(XenditService::class)->fetchStatus($this->order(['xendit_invoice_id' => 'inv_9'])));
    }

    public function test_normalize_maps_statuses(): void
    {
        $service = app(XenditService::class);

        $this->assertSame('paid', $service->normalize(['status' => 'PAID', 'amount' => 1])['status']);
        $this->assertSame('paid', $service->normalize(['status' => 'settled', 'amount' => 1])['status']);
        $this->assertSame('failed', $service->normalize(['status' => 'EXPIRED', 'amount' => 1])['status']);
        $this->assertSame('pending', $service->normalize(['status' => 'PENDING', 'amount' => 1])['status']);
        $this->assertSame('pending', $service->normalize([])['status']);
        $this->assertSame(50000, $service->normalize(['status' => 'PAID', 'amount' => 50000.0])['amount']);
    }
}
