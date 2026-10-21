<?php

namespace Tests\Feature\Payment;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\Order;
use App\Models\SiteSetting;
use App\Services\CartPricingService;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class CartPricingServiceTest extends TestCase
{
    use RefreshDatabase;

    private CartPricingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CartPricingService;
    }

    public function test_no_coupon_vat_11_fee_0(): void
    {
        $course = Course::factory()->create(['price' => 100000]);
        $item = new Cart(['course_id' => $course->id]);
        $item->setRelation('course', $course);
        $cartItems = collect([$item]);

        $settings = new SiteSetting([
            'vat_percentage' => 11,
            'transaction_fee_fixed' => 0,
            'transaction_fee_percentage' => 0,
        ]);

        $result = $this->service->calculate($cartItems, null, $settings);

        $this->assertSame(100000, $result['subtotal']);
        $this->assertSame(0, $result['discount']);
        $this->assertSame(11000, $result['vat_amount']);
        $this->assertSame(0, $result['transaction_fee']);
        $this->assertSame(111000, $result['final_total']);
        $this->assertNull($result['coupon']);
    }

    public function test_global_percent_coupon_10_percent_on_two_courses(): void
    {
        $c1 = Course::factory()->create(['price' => 100000]);
        $c2 = Course::factory()->create(['price' => 50000]);

        $i1 = new Cart(['course_id' => $c1->id]);
        $i1->setRelation('course', $c1);
        $i2 = new Cart(['course_id' => $c2->id]);
        $i2->setRelation('course', $c2);

        $cartItems = collect([$i1, $i2]);

        $coupon = Coupon::factory()->percent(10)->create([
            'course_id' => null,
        ]);

        $settings = new SiteSetting([
            'vat_percentage' => 0,
            'transaction_fee_fixed' => 0,
            'transaction_fee_percentage' => 0,
        ]);

        $result = $this->service->calculate($cartItems, $coupon, $settings);

        $this->assertSame(150000, $result['subtotal']);
        $this->assertSame(15000, $result['discount']);
        $this->assertSame(135000, $result['final_total']);
        $this->assertSame($coupon->id, $result['coupon']->id);
    }

    public function test_course_scoped_percent_coupon_50_percent_applies_only_to_target_course(): void
    {
        $c1 = Course::factory()->create(['price' => 100000]);
        $c2 = Course::factory()->create(['price' => 50000]);

        $i1 = new Cart(['course_id' => $c1->id]);
        $i1->setRelation('course', $c1);
        $i2 = new Cart(['course_id' => $c2->id]);
        $i2->setRelation('course', $c2);

        $cartItems = collect([$i1, $i2]);

        $coupon = Coupon::factory()->percent(50)->create([
            'course_id' => $c2->id,
        ]);

        $settings = new SiteSetting([
            'vat_percentage' => 0,
            'transaction_fee_fixed' => 0,
            'transaction_fee_percentage' => 0,
        ]);

        $result = $this->service->calculate($cartItems, $coupon, $settings);

        $this->assertSame(150000, $result['subtotal']);
        $this->assertSame(25000, $result['discount']);
        $this->assertSame(125000, $result['final_total']);
        $this->assertSame($coupon->id, $result['coupon']->id);
    }

    public function test_course_scoped_coupon_whose_course_is_not_in_cart_returns_zero_discount_and_null_coupon(): void
    {
        $c1 = Course::factory()->create(['price' => 100000]);
        $otherCourse = Course::factory()->create(['price' => 50000]);

        $i1 = new Cart(['course_id' => $c1->id]);
        $i1->setRelation('course', $c1);
        $cartItems = collect([$i1]);

        $coupon = Coupon::factory()->percent(50)->create([
            'course_id' => $otherCourse->id,
        ]);

        $settings = new SiteSetting([
            'vat_percentage' => 0,
            'transaction_fee_fixed' => 0,
            'transaction_fee_percentage' => 0,
        ]);

        $result = $this->service->calculate($cartItems, $coupon, $settings);

        $this->assertSame(100000, $result['subtotal']);
        $this->assertSame(0, $result['discount']);
        $this->assertSame(100000, $result['final_total']);
        $this->assertNull($result['coupon']);
    }

    public function test_fixed_coupon_exceeding_subtotal_caps_at_subtotal_and_results_in_zero_vat_and_fee(): void
    {
        $c1 = Course::factory()->create(['price' => 150000]);

        $i1 = new Cart(['course_id' => $c1->id]);
        $i1->setRelation('course', $c1);
        $cartItems = collect([$i1]);

        $coupon = Coupon::factory()->create([
            'type' => 'fixed',
            'value' => 200000,
            'course_id' => null,
        ]);

        $settings = new SiteSetting([
            'vat_percentage' => 11,
            'transaction_fee_fixed' => 5000,
            'transaction_fee_percentage' => 2.5,
        ]);

        $result = $this->service->calculate($cartItems, $coupon, $settings);

        $this->assertSame(150000, $result['subtotal']);
        $this->assertSame(150000, $result['discount']);
        $this->assertSame(0, $result['vat_amount']);
        $this->assertSame(0, $result['transaction_fee']);
        $this->assertSame(0, $result['final_total']);
        $this->assertSame($coupon->id, $result['coupon']->id);
    }

    public function test_vat_11_and_fee_2_point_5_percent_on_99999_returns_all_integers(): void
    {
        $c1 = Course::factory()->create(['price' => 99999]);

        $i1 = new Cart(['course_id' => $c1->id]);
        $i1->setRelation('course', $c1);
        $cartItems = collect([$i1]);

        $settings = new SiteSetting([
            'vat_percentage' => 11,
            'transaction_fee_fixed' => 0,
            'transaction_fee_percentage' => 2.5,
        ]);

        $result = $this->service->calculate($cartItems, null, $settings);

        $this->assertIsInt($result['subtotal']);
        $this->assertIsInt($result['discount']);
        $this->assertIsFloat($result['vat_percentage']);
        $this->assertIsInt($result['vat_amount']);
        $this->assertIsInt($result['transaction_fee']);
        $this->assertIsInt($result['final_total']);

        $this->assertSame(99999, $result['subtotal']);
        $this->assertSame(11000, $result['vat_amount']);
        $this->assertSame(2775, $result['transaction_fee']);
        $this->assertSame(113774, $result['final_total']);
    }

    public function test_feature_cart_page_total_equals_order_final_amount_created_by_checkout(): void
    {
        $student = $this->makeStudent();
        $c1 = Course::factory()->create(['price' => 100000]);
        $c2 = Course::factory()->create(['price' => 50000]);

        SiteSetting::create([
            'site_name' => 'ruangstudi.id',
            'points_for_purchase' => 100,
            'vat_percentage' => 11,
            'transaction_fee_fixed' => 2000,
            'transaction_fee_percentage' => 2,
        ]);
        cache()->forget('site_settings');

        Cart::create(['user_id' => $student->id, 'course_id' => $c1->id]);
        Cart::create(['user_id' => $student->id, 'course_id' => $c2->id]);

        $coupon = Coupon::factory()->percent(20)->create([
            'course_id' => null,
            'is_active' => true,
        ]);

        $this->actingAs($student);
        session(['active_role' => 'student', 'coupon' => $coupon]);

        $cartResponse = $this->get(route('student.cart.index'));
        $cartResponse->assertOk();
        $cartFinalTotal = $cartResponse->viewData('finalTotal');

        $mockMidtrans = Mockery::mock(MidtransService::class);
        $mockMidtrans->shouldReceive('createSnapToken')
            ->once()
            ->andReturn('dummy-snap-token');
        $this->app->instance(MidtransService::class, $mockMidtrans);

        $checkoutResponse = $this->post(route('checkout.process'));
        $order = Order::where('user_id', $student->id)->first();
        $this->assertNotNull($order);
        $checkoutResponse->assertRedirect(route('checkout.show', $order));

        $this->assertSame($cartFinalTotal, (int) round($order->final_amount));
    }

    public function test_feature_checkout_with_session_coupon_for_course_no_longer_in_cart_redirects_with_error(): void
    {
        $student = $this->makeStudent();
        $c1 = Course::factory()->create(['price' => 100000]);
        $c2 = Course::factory()->create(['price' => 50000]);

        SiteSetting::create([
            'site_name' => 'ruangstudi.id',
            'points_for_purchase' => 100,
            'vat_percentage' => 0,
            'transaction_fee_fixed' => 0,
            'transaction_fee_percentage' => 0,
        ]);
        cache()->forget('site_settings');

        // Only c1 in cart
        Cart::create(['user_id' => $student->id, 'course_id' => $c1->id]);

        // Coupon is scoped to c2
        $coupon = Coupon::factory()->percent(20)->create([
            'course_id' => $c2->id,
            'is_active' => true,
        ]);

        $this->actingAs($student);
        session(['active_role' => 'student', 'coupon' => $coupon]);

        $response = $this->post(route('checkout.process'));
        $response->assertRedirect(route('student.cart.index'));
        $response->assertSessionHas('error', 'Kupon dilepas karena kursusnya tidak ada lagi di keranjang.');

        $this->assertNull(session('coupon'));
        $this->assertDatabaseCount('orders', 0);
    }
}
