<?php

namespace Tests\Feature\Payment;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PointHistory;
use App\Models\SiteSetting;
use App\Services\OrderSettlementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderSettlementServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_mark_paid_is_idempotent_and_enrolls_once(): void
    {
        SiteSetting::create([
            'site_name' => 'ruangstudi.id',
            'points_for_purchase' => 50,
        ]);
        cache()->forget('site_settings');

        $student = $this->makeStudent();
        $course = Course::factory()->create();
        $coupon = Coupon::factory()->create(['uses_count' => 0]);

        // Put course in cart
        Cart::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);

        $order = Order::factory()->create([
            'user_id' => $student->id,
            'coupon_id' => $coupon->id,
            'status' => 'pending',
            'final_amount' => 90000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'course_id' => $course->id,
            'price_at_purchase' => 90000,
        ]);

        $service = app(OrderSettlementService::class);

        // First call -> returns true
        $res1 = $service->markPaid($order);
        $this->assertTrue($res1);
        $this->assertEquals('paid', $order->fresh()->status);
        $this->assertEquals(1, $student->fresh()->enrollments()->count());
        $this->assertEquals(1, PointHistory::where('user_id', $student->id)->where('course_id', $course->id)->count());
        $this->assertEquals(1, $coupon->fresh()->uses_count);
        $this->assertDatabaseMissing('carts', ['user_id' => $student->id, 'course_id' => $course->id]);

        // Second call -> returns false (idempotent), no duplicate enrollment or points
        $res2 = $service->markPaid($order);
        $this->assertFalse($res2);
        $this->assertEquals(1, $student->fresh()->enrollments()->count());
        $this->assertEquals(1, PointHistory::where('user_id', $student->id)->where('course_id', $course->id)->count());
        $this->assertEquals(1, $coupon->fresh()->uses_count);
    }

    public function test_mark_failed_sets_status_only_from_pending(): void
    {
        $student = $this->makeStudent();
        $order = Order::factory()->create([
            'user_id' => $student->id,
            'status' => 'pending',
        ]);

        $service = app(OrderSettlementService::class);
        $this->assertTrue($service->markFailed($order, 'cancelled'));
        $this->assertEquals('cancelled', $order->fresh()->status);

        // Cannot mark failed once cancelled
        $this->assertFalse($service->markFailed($order, 'failed'));
    }
}
