<?php

namespace Tests\Feature\Payment;

use App\Models\Course;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SiteSetting;
use App\Services\XenditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class PaymentReturnTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        SiteSetting::create([
            'site_name' => 'ruangstudi.id',
            'points_for_purchase' => 100,
        ]);
        cache()->forget('site_settings');
    }

    public function test_other_user_gets_403_on_all_return_routes_and_checkout_show(): void
    {
        $owner = $this->makeStudent();
        $otherStudent = $this->makeStudent();

        $order = Order::factory()->create([
            'user_id' => $owner->id,
            'status' => 'pending',
            'xendit_invoice_id' => 'inv_dummy',
            'payment_url' => 'https://checkout-staging.xendit.co/web/inv_dummy',
        ]);

        $this->actingAs($otherStudent);
        session(['active_role' => 'student']);

        $this->get(route('payment.success', $order))->assertStatus(403);
        $this->get(route('payment.pending', $order))->assertStatus(403);
        $this->get(route('payment.failed', $order))->assertStatus(403);
        $this->get(route('payment.cancelled', $order))->assertStatus(403);
        $this->get(route('checkout.show', $order))->assertStatus(403);
    }

    public function test_owner_opens_success_and_gateway_returns_pending_keeps_order_pending(): void
    {
        $owner = $this->makeStudent();
        $course = Course::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $owner->id,
            'status' => 'pending',
            'final_amount' => 100000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'course_id' => $course->id,
            'price_at_purchase' => 100000,
        ]);

        $mockXendit = Mockery::mock(XenditService::class);
        $mockXendit->shouldReceive('fetchStatus')
            ->once()
            ->with(Mockery::on(fn ($arg) => $arg->is($order)))
            ->andReturn([
                'status' => 'pending',
                'amount' => 100000,
            ]);
        $this->app->instance(XenditService::class, $mockXendit);

        $this->actingAs($owner);
        session(['active_role' => 'student']);

        $response = $this->get(route('payment.success', $order));

        $response->assertStatus(200);
        $response->assertSee('Pembayaran sedang diproses');
        $this->assertEquals('pending', $order->fresh()->status);
        $this->assertEquals(0, $owner->fresh()->enrollments()->count());
    }

    public function test_owner_opens_success_and_gateway_returns_settlement_marks_paid_and_enrolled(): void
    {
        $owner = $this->makeStudent();
        $course = Course::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $owner->id,
            'status' => 'pending',
            'final_amount' => 100000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'course_id' => $course->id,
            'price_at_purchase' => 100000,
        ]);

        $mockXendit = Mockery::mock(XenditService::class);
        $mockXendit->shouldReceive('fetchStatus')
            ->once()
            ->with(Mockery::on(fn ($arg) => $arg->is($order)))
            ->andReturn([
                'status' => 'paid',
                'amount' => 100000,
            ]);
        $this->app->instance(XenditService::class, $mockXendit);

        $this->actingAs($owner);
        session(['active_role' => 'student']);

        $response = $this->get(route('payment.success', $order));

        $response->assertStatus(200);
        $response->assertSee('Pembelian Berhasil');
        $this->assertEquals('paid', $order->fresh()->status);
        $this->assertEquals(1, $owner->fresh()->enrollments()->count());
    }

    public function test_owner_opens_cancelled_keeps_status_pending(): void
    {
        $owner = $this->makeStudent();

        $order = Order::factory()->create([
            'user_id' => $owner->id,
            'status' => 'pending',
        ]);

        $this->actingAs($owner);
        session(['active_role' => 'student']);

        $response = $this->get(route('payment.cancelled', $order));

        $response->assertStatus(200);
        $this->assertEquals('pending', $order->fresh()->status);
    }
}
