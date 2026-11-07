<?php

namespace Tests\Feature\Payment;

use App\Models\Course;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PointHistory;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class XenditCallbackTest extends TestCase
{
    use RefreshDatabase;

    protected string $token = 'test-xendit-callback-token';

    protected function setUp(): void
    {
        parent::setUp();
        config(['xendit.callback_token' => $this->token]);

        SiteSetting::create([
            'site_name' => 'ruangstudi.id',
            'points_for_purchase' => 100,
        ]);
        cache()->forget('site_settings');
    }

    protected function makeOrder(int $amount = 100000, bool $withItem = false): Order
    {
        $student = $this->makeStudent();
        $order = Order::factory()->create([
            'user_id' => $student->id,
            'status' => 'pending',
            'final_amount' => $amount,
            'xendit_invoice_id' => 'inv_'.uniqid(),
        ]);

        if ($withItem) {
            OrderItem::create([
                'order_id' => $order->id,
                'course_id' => Course::factory()->create()->id,
                'price_at_purchase' => $amount,
            ]);
        }

        return $order;
    }

    protected function payload(Order $order, string $status = 'PAID', ?int $amount = null): array
    {
        return [
            'id' => $order->xendit_invoice_id,
            'external_id' => $order->order_code,
            'status' => $status,
            'amount' => $amount ?? (int) $order->final_amount,
        ];
    }

    protected function postCallback(array $payload, ?string $token = null)
    {
        return $this->postJson(route('xendit.callback'), $payload, [
            'x-callback-token' => $token ?? $this->token,
        ]);
    }

    public function test_wrong_token_returns_403_and_order_stays_pending(): void
    {
        $order = $this->makeOrder();

        $this->postCallback($this->payload($order), 'wrong-token')->assertStatus(403);

        $this->assertEquals('pending', $order->fresh()->status);
        $this->assertEquals(0, $order->user->enrollments()->count());
    }

    public function test_missing_token_header_returns_403(): void
    {
        $order = $this->makeOrder();

        $this->postJson(route('xendit.callback'), $this->payload($order))->assertStatus(403);

        $this->assertEquals('pending', $order->fresh()->status);
    }

    public function test_empty_configured_token_rejects_everything(): void
    {
        config(['xendit.callback_token' => '']);
        $order = $this->makeOrder();

        $this->postCallback($this->payload($order), '')->assertStatus(403);

        $this->assertEquals('pending', $order->fresh()->status);
    }

    public function test_paid_marks_order_paid_and_enrolls_user(): void
    {
        $order = $this->makeOrder(100000, true);
        $student = $order->user;

        $this->postCallback($this->payload($order, 'PAID'))
            ->assertStatus(200)
            ->assertJson(['message' => 'OK']);

        $this->assertEquals('paid', $order->fresh()->status);
        $this->assertEquals(1, $student->fresh()->enrollments()->count());
        $this->assertEquals(1, PointHistory::where('user_id', $student->id)->count());
    }

    public function test_settled_marks_order_paid(): void
    {
        $order = $this->makeOrder(100000, true);

        $this->postCallback($this->payload($order, 'SETTLED'))->assertStatus(200);

        $this->assertEquals('paid', $order->fresh()->status);
    }

    public function test_expired_marks_order_failed(): void
    {
        $order = $this->makeOrder(100000, true);

        $this->postCallback($this->payload($order, 'EXPIRED'))->assertStatus(200);

        $this->assertEquals('failed', $order->fresh()->status);
        $this->assertEquals(0, $order->user->enrollments()->count());
    }

    public function test_pending_status_is_ignored(): void
    {
        $order = $this->makeOrder();

        $this->postCallback($this->payload($order, 'PENDING'))->assertStatus(200);

        $this->assertEquals('pending', $order->fresh()->status);
    }

    public function test_same_callback_twice_keeps_single_enrollment_and_point_history(): void
    {
        $order = $this->makeOrder(75000, true);
        $student = $order->user;
        $payload = $this->payload($order);

        $this->postCallback($payload)->assertStatus(200);
        $this->postCallback($payload)->assertStatus(200);

        $this->assertEquals(1, $student->fresh()->enrollments()->count());
        $this->assertEquals(1, PointHistory::where('user_id', $student->id)->count());
    }

    public function test_amount_mismatch_returns_422_and_order_stays_pending(): void
    {
        $order = $this->makeOrder(100000);

        $this->postCallback($this->payload($order, 'PAID', 50000))->assertStatus(422);

        $this->assertEquals('pending', $order->fresh()->status);
        $this->assertEquals(0, $order->user->enrollments()->count());
    }

    public function test_invoice_id_mismatch_returns_422_and_order_stays_pending(): void
    {
        $order = $this->makeOrder();
        $payload = $this->payload($order);
        $payload['id'] = 'inv_someone_else';

        $this->postCallback($payload)->assertStatus(422);

        $this->assertEquals('pending', $order->fresh()->status);
    }

    public function test_unknown_external_id_returns_404(): void
    {
        $this->postCallback([
            'id' => 'inv_x',
            'external_id' => 'UNKNOWN-ORDER-CODE',
            'status' => 'PAID',
            'amount' => 100000,
        ])->assertStatus(404)->assertJson(['message' => 'Order not found']);
    }

    public function test_expired_then_late_paid_marks_order_paid(): void
    {
        $order = $this->makeOrder(100000, true);

        $this->postCallback($this->payload($order, 'EXPIRED'))->assertStatus(200);
        $this->assertEquals('failed', $order->fresh()->status);

        $this->postCallback($this->payload($order, 'PAID'))->assertStatus(200);
        $this->assertEquals('paid', $order->fresh()->status);
        $this->assertEquals(1, $order->user->enrollments()->count());
    }
}
