<?php

namespace Tests\Feature\Payment;

use App\Models\Cart;
use App\Models\Course;
use App\Models\Order;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartEligibilityTest extends TestCase
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

    public function test_diamond_course_cannot_be_added_to_cart(): void
    {
        $student = $this->makeStudent();
        $course = Course::factory()->create(['payment_type' => 'diamonds', 'price' => 0, 'diamond_price' => 50]);

        $this->actingAs($student);
        session(['active_role' => 'student']);

        $this->post(route('student.cart.add', $course))->assertSessionHas('error');

        $this->assertDatabaseMissing('carts', ['user_id' => $student->id, 'course_id' => $course->id]);
    }

    public function test_unpublished_courses_cannot_be_added_to_cart(): void
    {
        $student = $this->makeStudent();
        $this->actingAs($student);
        session(['active_role' => 'student']);

        foreach (['draft', 'private', 'pending_review', 'rejected'] as $status) {
            $course = Course::factory()->create(['status' => $status, 'price' => 0]);

            $this->post(route('student.cart.add', $course))->assertSessionHas('error');

            $this->assertDatabaseMissing('carts', ['user_id' => $student->id, 'course_id' => $course->id]);
        }
    }

    public function test_checkout_does_not_enroll_when_course_became_unpublished_after_add(): void
    {
        $student = $this->makeStudent();
        $course = Course::factory()->create(['price' => 100000]);
        Cart::create(['user_id' => $student->id, 'course_id' => $course->id]);

        $course->update(['status' => 'draft', 'price' => 0]);

        $this->actingAs($student);
        session(['active_role' => 'student']);

        $this->post(route('checkout.process'))
            ->assertRedirect(route('student.cart.index'))
            ->assertSessionHas('error');

        $this->assertSame(0, Order::where('user_id', $student->id)->count());
        $this->assertFalse($student->enrollments()->where('courses.id', $course->id)->exists());
        $this->assertDatabaseMissing('carts', ['user_id' => $student->id, 'course_id' => $course->id]);
    }

    public function test_cart_page_removes_unbuyable_items(): void
    {
        $student = $this->makeStudent();
        $course = Course::factory()->create(['price' => 100000]);
        Cart::create(['user_id' => $student->id, 'course_id' => $course->id]);

        $course->update(['status' => 'draft']);

        $this->actingAs($student);
        session(['active_role' => 'student']);

        $this->get(route('student.cart.index'))->assertOk();

        $this->assertDatabaseMissing('carts', ['user_id' => $student->id, 'course_id' => $course->id]);
    }

    public function test_diamond_purchase_of_unpublished_course_is_rejected(): void
    {
        $student = $this->makeStudent(['diamond_balance' => 500]);
        $course = Course::factory()->create([
            'payment_type' => 'diamonds',
            'diamond_price' => 50,
            'price' => 0,
            'status' => 'draft',
        ]);

        $this->actingAs($student);
        session(['active_role' => 'student']);

        $this->post(route('student.courses.purchase_with_diamonds', $course))->assertSessionHas('error');

        $this->assertSame(500, (int) $student->fresh()->diamond_balance);
        $this->assertFalse($student->enrollments()->where('courses.id', $course->id)->exists());
    }

    public function test_published_money_course_can_still_be_added(): void
    {
        $student = $this->makeStudent();
        $course = Course::factory()->create(['price' => 100000]);

        $this->actingAs($student);
        session(['active_role' => 'student']);

        $this->post(route('student.cart.add', $course))->assertRedirect(route('student.cart.index'));

        $this->assertDatabaseHas('carts', ['user_id' => $student->id, 'course_id' => $course->id]);
    }
}
