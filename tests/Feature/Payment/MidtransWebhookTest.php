<?php

namespace Tests\Feature\Payment;

use App\Models\Course;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PointHistory;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MidtransWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected string $serverKey = 'test-midtrans-server-key';

    protected function setUp(): void
    {
        parent::setUp();
        config(['midtrans.server_key' => $this->serverKey]);

        SiteSetting::create([
            'site_name' => 'ruangstudi.id',
            'points_for_purchase' => 100,
        ]);
        cache()->forget('site_settings');
    }

    protected function makeSignature(string $orderId, string $statusCode, string $grossAmount): string
    {
        return hash('sha512', $orderId.$statusCode.$grossAmount.$this->serverKey);
    }

    public function test_invalid_signature_returns_403_and_order_stays_pending(): void
    {
        $student = $this->makeStudent();
        $order = Order::factory()->create([
            'user_id' => $student->id,
            'status' => 'pending',
            'final_amount' => 50000,
        ]);

        $response = $this->postJson(route('midtrans.notification'), [
            'order_id' => $order->order_code,
            'status_code' => '200',
            'gross_amount' => '50000.00',
            'signature_key' => 'invalid-signature-key',
            'transaction_status' => 'settlement',
        ]);

        $response->assertStatus(403);
        $this->assertEquals('pending', $order->fresh()->status);
        $this->assertEquals(0, $student->fresh()->enrollments()->count());
    }

    public function test_valid_settlement_marks_order_paid_and_enrolls_user(): void
    {
        $student = $this->makeStudent();
        $course = Course::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $student->id,
            'status' => 'pending',
            'final_amount' => 100000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'course_id' => $course->id,
            'price_at_purchase' => 100000,
        ]);

        $signature = $this->makeSignature($order->order_code, '200', '100000.00');

        $response = $this->postJson(route('midtrans.notification'), [
            'order_id' => $order->order_code,
            'status_code' => '200',
            'gross_amount' => '100000.00',
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'OK']);
        $this->assertEquals('paid', $order->fresh()->status);
        $this->assertEquals(1, $student->fresh()->enrollments()->count());
        $this->assertEquals(1, PointHistory::where('user_id', $student->id)->where('course_id', $course->id)->count());
    }

    public function test_same_notification_twice_keeps_single_enrollment_and_point_history(): void
    {
        $student = $this->makeStudent();
        $course = Course::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $student->id,
            'status' => 'pending',
            'final_amount' => 75000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'course_id' => $course->id,
            'price_at_purchase' => 75000,
        ]);

        $signature = $this->makeSignature($order->order_code, '200', '75000.00');

        $payload = [
            'order_id' => $order->order_code,
            'status_code' => '200',
            'gross_amount' => '75000.00',
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
        ];

        $this->postJson(route('midtrans.notification'), $payload)->assertStatus(200);
        $this->postJson(route('midtrans.notification'), $payload)->assertStatus(200);

        $this->assertEquals(1, $student->fresh()->enrollments()->count());
        $this->assertEquals(1, PointHistory::where('user_id', $student->id)->where('course_id', $course->id)->count());
    }

    public function test_gross_amount_mismatch_returns_422_and_order_stays_pending(): void
    {
        $student = $this->makeStudent();
        $order = Order::factory()->create([
            'user_id' => $student->id,
            'status' => 'pending',
            'final_amount' => 100000,
        ]);

        // Mismatch gross_amount
        $signature = $this->makeSignature($order->order_code, '200', '50000.00');

        $response = $this->postJson(route('midtrans.notification'), [
            'order_id' => $order->order_code,
            'status_code' => '200',
            'gross_amount' => '50000.00',
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
        ]);

        $response->assertStatus(422);
        $this->assertEquals('pending', $order->fresh()->status);
        $this->assertEquals(0, $student->fresh()->enrollments()->count());
    }

    public function test_unknown_order_id_returns_404(): void
    {
        $signature = $this->makeSignature('UNKNOWN-ORDER-CODE', '200', '100000.00');

        $response = $this->postJson(route('midtrans.notification'), [
            'order_id' => 'UNKNOWN-ORDER-CODE',
            'status_code' => '200',
            'gross_amount' => '100000.00',
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
        ]);

        $response->assertStatus(404);
        $response->assertJson(['message' => 'Order not found']);
    }

    public function test_expire_marks_failed_then_subsequent_settlement_marks_paid(): void
    {
        $student = $this->makeStudent();
        $course = Course::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $student->id,
            'status' => 'pending',
            'final_amount' => 100000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'course_id' => $course->id,
            'price_at_purchase' => 100000,
        ]);

        // 1. Expire notification
        $sigExpire = $this->makeSignature($order->order_code, '200', '100000.00');
        $this->postJson(route('midtrans.notification'), [
            'order_id' => $order->order_code,
            'status_code' => '200',
            'gross_amount' => '100000.00',
            'signature_key' => $sigExpire,
            'transaction_status' => 'expire',
        ])->assertStatus(200);

        $this->assertEquals('failed', $order->fresh()->status);
        $this->assertEquals(0, $student->fresh()->enrollments()->count());

        // 2. Late settlement notification
        $sigSettlement = $this->makeSignature($order->order_code, '200', '100000.00');
        $this->postJson(route('midtrans.notification'), [
            'order_id' => $order->order_code,
            'status_code' => '200',
            'gross_amount' => '100000.00',
            'signature_key' => $sigSettlement,
            'transaction_status' => 'settlement',
        ])->assertStatus(200);

        $this->assertEquals('paid', $order->fresh()->status);
        $this->assertEquals(1, $student->fresh()->enrollments()->count());
    }
}
