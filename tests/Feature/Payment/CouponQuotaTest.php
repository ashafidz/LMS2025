<?php

namespace Tests\Feature\Payment;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\Order;
use App\Models\SiteSetting;
use App\Services\OrderSettlementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponQuotaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        SiteSetting::create([
            'site_name' => 'ruangstudi.id',
            'points_for_purchase' => 100,
            'vat_percentage' => 0,
            'transaction_fee_fixed' => 0,
            'transaction_fee_percentage' => 0,
        ]);
        cache()->forget('site_settings');
    }

    public function test_pending_order_reserves_total_quota_so_second_user_gets_quota_exhausted(): void
    {
        $userA = $this->makeStudent();
        $userB = $this->makeStudent();

        $course = Course::factory()->create(['price' => 100000]);
        Cart::create(['user_id' => $userB->id, 'course_id' => $course->id]);

        $coupon = Coupon::factory()->create([
            'code' => 'TOTAL1',
            'is_active' => true,
            'max_uses' => 1,
            'uses_count' => 0,
        ]);

        Order::factory()->create([
            'user_id' => $userA->id,
            'coupon_id' => $coupon->id,
            'status' => 'pending',
            'created_at' => now(),
        ]);

        $this->actingAs($userB);
        $res = $this->post(route('student.cart.apply_coupon'), ['code' => $coupon->code]);
        $res->assertSessionHasErrors(['code' => 'Kuota kupon ini sudah habis.']);
    }

    public function test_pending_order_older_than_24_hours_does_not_reserve_quota(): void
    {
        $userA = $this->makeStudent();
        $userB = $this->makeStudent();

        $course = Course::factory()->create(['price' => 100000]);
        Cart::create(['user_id' => $userB->id, 'course_id' => $course->id]);

        $coupon = Coupon::factory()->create([
            'code' => 'EXPIRED_RES',
            'is_active' => true,
            'max_uses' => 1,
            'uses_count' => 0,
        ]);

        $order = Order::factory()->create([
            'user_id' => $userA->id,
            'coupon_id' => $coupon->id,
            'status' => 'pending',
        ]);
        // Set created_at to 25 hours ago
        $order->created_at = now()->subHours(25);
        $order->save();

        $this->actingAs($userB);
        $res = $this->post(route('student.cart.apply_coupon'), ['code' => $coupon->code]);
        $res->assertSessionHas('success', 'Kupon berhasil diterapkan.');
    }

    public function test_failed_or_cancelled_order_does_not_reserve_quota(): void
    {
        $userA = $this->makeStudent();
        $userB = $this->makeStudent();

        $course = Course::factory()->create(['price' => 100000]);
        Cart::create(['user_id' => $userB->id, 'course_id' => $course->id]);

        $coupon = Coupon::factory()->create([
            'code' => 'FAILED_RES',
            'is_active' => true,
            'max_uses' => 1,
            'uses_count' => 0,
        ]);

        $order = Order::factory()->create([
            'user_id' => $userA->id,
            'coupon_id' => $coupon->id,
            'status' => 'failed',
            'created_at' => now(),
        ]);

        $this->actingAs($userB);
        $res = $this->post(route('student.cart.apply_coupon'), ['code' => $coupon->code]);
        $res->assertSessionHas('success', 'Kupon berhasil diterapkan.');

        // Test with cancelled
        $order->update(['status' => 'cancelled']);
        $this->assertEquals(0, $coupon->reservedCount());
    }

    public function test_pending_order_reserves_per_user_quota(): void
    {
        $userA = $this->makeStudent();
        $userB = $this->makeStudent();

        $course = Course::factory()->create(['price' => 100000]);
        Cart::create(['user_id' => $userA->id, 'course_id' => $course->id]);
        Cart::create(['user_id' => $userB->id, 'course_id' => $course->id]);

        $coupon = Coupon::factory()->create([
            'code' => 'USERLIMIT1',
            'is_active' => true,
            'max_uses_per_user' => 1,
            'max_uses' => 10,
            'uses_count' => 0,
        ]);

        Order::factory()->create([
            'user_id' => $userA->id,
            'coupon_id' => $coupon->id,
            'status' => 'pending',
            'created_at' => now(),
        ]);

        // User A gets per-user limit error
        $this->actingAs($userA);
        $resA = $this->post(route('student.cart.apply_coupon'), ['code' => $coupon->code]);
        $resA->assertSessionHasErrors(['code' => 'Anda telah mencapai batas maksimal penggunaan untuk kupon ini.']);

        // User B can use it
        $this->actingAs($userB);
        $resB = $this->post(route('student.cart.apply_coupon'), ['code' => $coupon->code]);
        $resB->assertSessionHas('success', 'Kupon berhasil diterapkan.');
    }

    public function test_pending_order_then_mark_paid_does_not_double_count(): void
    {
        $user = $this->makeStudent();
        $course = Course::factory()->create(['price' => 100000]);

        $coupon = Coupon::factory()->create([
            'code' => 'DOUBLECOUNT',
            'is_active' => true,
            'max_uses' => 5,
            'uses_count' => 0,
        ]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'coupon_id' => $coupon->id,
            'status' => 'pending',
            'created_at' => now(),
        ]);
        $order->items()->create([
            'course_id' => $course->id,
            'price_at_purchase' => $course->price,
        ]);

        // Prior to markPaid: uses_count = 0, reservedCount = 1, total = 1
        $this->assertSame(0, $coupon->uses_count);
        $this->assertSame(1, $coupon->reservedCount());
        $this->assertSame(1, $coupon->uses_count + $coupon->reservedCount());

        app(OrderSettlementService::class)->markPaid($order);

        $coupon->refresh();
        $this->assertSame(1, $coupon->uses_count);
        $this->assertSame(0, $coupon->reservedCount());
        $this->assertSame(1, $coupon->uses_count + $coupon->reservedCount());
    }
}
