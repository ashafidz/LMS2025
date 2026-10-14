<?php

namespace Tests\Feature\Payment;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\Order;
use App\Models\SiteSetting;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class CheckoutTest extends TestCase
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

    public function test_checkout_creates_pending_order_and_coupon_uses_count_unchanged(): void
    {
        $student = $this->makeStudent();
        $course = Course::factory()->create(['price' => 100000]);
        $coupon = Coupon::factory()->create([
            'value' => 10000,
            'type' => 'fixed',
            'uses_count' => 0,
            'is_active' => true,
        ]);

        Cart::create(['user_id' => $student->id, 'course_id' => $course->id]);

        $mockMidtrans = Mockery::mock(MidtransService::class);
        $mockMidtrans->shouldReceive('createSnapToken')
            ->once()
            ->andReturn('dummy-snap-token-123');
        $this->app->instance(MidtransService::class, $mockMidtrans);

        $this->actingAs($student);
        session(['active_role' => 'student', 'coupon' => $coupon]);

        $response = $this->post(route('checkout.process'));

        $order = Order::where('user_id', $student->id)->first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('checkout.show', $order));

        $this->assertEquals('pending', $order->status);
        $this->assertEquals(90000, (int) round($order->final_amount));
        $this->assertEquals('dummy-snap-token-123', $order->snap_token);

        // Coupon uses_count MUST be unchanged at order creation (F10 fix)
        $this->assertEquals(0, $coupon->fresh()->uses_count);
    }

    public function test_100_percent_coupon_marks_order_paid_enrolled_and_coupon_uses_count_incremented(): void
    {
        $student = $this->makeStudent();
        $course = Course::factory()->create(['price' => 100000]);
        $coupon = Coupon::factory()->percent(100)->create([
            'uses_count' => 0,
            'is_active' => true,
        ]);

        Cart::create(['user_id' => $student->id, 'course_id' => $course->id]);

        $this->actingAs($student);
        session(['active_role' => 'student', 'coupon' => $coupon]);

        $response = $this->post(route('checkout.process'));

        $order = Order::where('user_id', $student->id)->first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('payment.success', $order));

        $this->assertEquals('paid', $order->fresh()->status);
        $this->assertEquals(0, (int) round($order->fresh()->final_amount));
        $this->assertEquals(1, $student->fresh()->enrollments()->count());
        $this->assertEquals(1, $coupon->fresh()->uses_count);
        $this->assertDatabaseMissing('carts', ['user_id' => $student->id, 'course_id' => $course->id]);
    }

    public function test_expired_or_over_quota_coupon_in_session_redirects_with_error_and_no_order(): void
    {
        $student = $this->makeStudent();
        $course = Course::factory()->create(['price' => 100000]);
        $coupon = Coupon::factory()->expired()->create([
            'value' => 10000,
            'type' => 'fixed',
            'is_active' => true,
        ]);

        Cart::create(['user_id' => $student->id, 'course_id' => $course->id]);

        $this->actingAs($student);
        session(['active_role' => 'student', 'coupon' => $coupon]);

        $response = $this->post(route('checkout.process'));

        $response->assertRedirect(route('student.cart.index'));
        $response->assertSessionHas('error', 'Kupon tidak berlaku lagi.');
        $this->assertNull(session('coupon'));
        $this->assertEquals(0, Order::count());
    }

    public function test_free_checkout_when_already_enrolled_does_not_throw_and_no_duplicate_enrollment(): void
    {
        $student = $this->makeStudent();
        $course = Course::factory()->create(['price' => 0]);

        // Already enrolled prior to checkout
        $this->enroll($student, $course);
        $this->assertEquals(1, $student->fresh()->enrollments()->count());

        Cart::create(['user_id' => $student->id, 'course_id' => $course->id]);

        $this->actingAs($student);
        session(['active_role' => 'student']);

        $response = $this->post(route('checkout.process'));

        $order = Order::where('user_id', $student->id)->first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('payment.success', $order));

        $this->assertEquals('paid', $order->fresh()->status);
        $this->assertEquals(1, $student->fresh()->enrollments()->count());
    }
}
