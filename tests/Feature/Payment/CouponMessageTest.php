<?php

namespace Tests\Feature\Payment;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponMessageTest extends TestCase
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

    public function test_apply_coupon_returns_inactive_expired_or_future_message(): void
    {
        $student = $this->makeStudent();
        $this->actingAs($student);

        // Unknown code
        $res = $this->post(route('student.cart.apply_coupon'), ['code' => 'NONEXISTENT']);
        $res->assertSessionHasErrors(['code' => 'Kode kupon tidak valid atau sudah tidak berlaku.']);

        // Inactive coupon
        $inactive = Coupon::factory()->create(['code' => 'INACTIVE1', 'is_active' => false]);
        $res = $this->post(route('student.cart.apply_coupon'), ['code' => $inactive->code]);
        $res->assertSessionHasErrors(['code' => 'Kode kupon tidak valid atau sudah tidak berlaku.']);

        // Expired coupon
        $expired = Coupon::factory()->create([
            'code' => 'EXPIRED1',
            'is_active' => true,
            'expires_at' => now()->subDay(),
        ]);
        $res = $this->post(route('student.cart.apply_coupon'), ['code' => $expired->code]);
        $res->assertSessionHasErrors(['code' => 'Kode kupon tidak valid atau sudah tidak berlaku.']);

        // Future coupon
        $future = Coupon::factory()->create([
            'code' => 'FUTURE1',
            'is_active' => true,
            'starts_at' => now()->addDay(),
        ]);
        $res = $this->post(route('student.cart.apply_coupon'), ['code' => $future->code]);
        $res->assertSessionHasErrors(['code' => 'Kode kupon tidak valid atau sudah tidak berlaku.']);
    }

    public function test_apply_coupon_returns_total_quota_reached_message(): void
    {
        $student = $this->makeStudent();
        $this->actingAs($student);

        $coupon = Coupon::factory()->create([
            'code' => 'QUOTAOUT',
            'is_active' => true,
            'max_uses' => 5,
            'uses_count' => 5,
        ]);

        $res = $this->post(route('student.cart.apply_coupon'), ['code' => $coupon->code]);
        $res->assertSessionHasErrors(['code' => 'Kuota kupon ini sudah habis.']);
    }

    public function test_apply_coupon_returns_user_quota_reached_message(): void
    {
        $student = $this->makeStudent();
        $this->actingAs($student);

        $coupon = Coupon::factory()->create([
            'code' => 'USERLIMIT',
            'is_active' => true,
            'max_uses_per_user' => 1,
        ]);

        $student->coupons()->attach($coupon->id, ['uses_count' => 1]);

        $res = $this->post(route('student.cart.apply_coupon'), ['code' => $coupon->code]);
        $res->assertSessionHasErrors(['code' => 'Anda telah mencapai batas maksimal penggunaan untuk kupon ini.']);
    }

    public function test_apply_coupon_returns_course_not_in_cart_message(): void
    {
        $student = $this->makeStudent();
        $this->actingAs($student);

        $c1 = Course::factory()->create(['price' => 100000]);
        $c2 = Course::factory()->create(['price' => 50000]);

        // Student only has c2 in cart
        Cart::create(['user_id' => $student->id, 'course_id' => $c2->id]);

        // Coupon is only for c1
        $coupon = Coupon::factory()->create([
            'code' => 'COURSEONLY',
            'is_active' => true,
            'course_id' => $c1->id,
        ]);

        $res = $this->post(route('student.cart.apply_coupon'), ['code' => $coupon->code]);
        $res->assertSessionHasErrors(['code' => 'Kupon ini tidak berlaku untuk kursus yang ada di keranjang Anda.']);
    }

    public function test_apply_coupon_valid_returns_success_flash(): void
    {
        $student = $this->makeStudent();
        $this->actingAs($student);

        $coupon = Coupon::factory()->create([
            'code' => 'VALIDCOUPON',
            'is_active' => true,
        ]);

        $res = $this->post(route('student.cart.apply_coupon'), ['code' => $coupon->code]);
        $res->assertSessionHas('success', 'Kupon berhasil diterapkan.');
        $this->assertSame($coupon->id, session('coupon')->id);
    }

    public function test_checkout_with_coupon_that_became_expired_after_applying_redirects_with_message_no_order(): void
    {
        $student = $this->makeStudent();
        $course = Course::factory()->create(['price' => 100000]);
        Cart::create(['user_id' => $student->id, 'course_id' => $course->id]);

        $coupon = Coupon::factory()->create([
            'code' => 'EXPIREDLATER',
            'is_active' => true,
            'expires_at' => now()->subMinute(),
        ]);

        $this->actingAs($student);
        session(['active_role' => 'student', 'coupon' => $coupon]);

        $res = $this->post(route('checkout.process'));
        $res->assertRedirect(route('student.cart.index'));
        $res->assertSessionHas('error', 'Kupon tidak bisa dipakai: Kode kupon tidak valid atau sudah tidak berlaku.');
        $this->assertNull(session('coupon'));
        $this->assertDatabaseCount('orders', 0);
    }
}
