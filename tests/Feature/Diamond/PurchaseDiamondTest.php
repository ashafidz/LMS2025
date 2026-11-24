<?php

namespace Tests\Feature\Diamond;

use App\Models\Course;
use App\Models\CourseUser;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PointHistory;
use App\Models\SiteSetting;
use App\Services\OrderSettlementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A course purchase gives diamonds, not points, and only when the order was really paid (ADR-016).
 */
class PurchaseDiamondTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        SiteSetting::create(['site_name' => 'ruangstudi.id', 'diamonds_for_purchase' => 100]);
        cache()->forget('site_settings');
    }

    private function order(float $finalAmount): array
    {
        $student = $this->makeStudent();
        $course = Course::factory()->create();
        $order = Order::factory()->create(['user_id' => $student->id, 'status' => 'pending', 'final_amount' => $finalAmount]);
        OrderItem::create(['order_id' => $order->id, 'course_id' => $course->id, 'price_at_purchase' => 150000]);

        return [$student, $course, $order];
    }

    public function test_a_paid_order_gives_diamonds_once_and_no_points(): void
    {
        [$student, $course, $order] = $this->order(150000);

        app(OrderSettlementService::class)->markPaid($order);
        app(OrderSettlementService::class)->markPaid($order);

        $student->refresh();
        $this->assertSame(100, (int) $student->diamond_balance);
        $this->assertSame(1, $student->diamondHistories()->count());
        $this->assertSame('Bonus pembelian kursus: '.$course->title, $student->diamondHistories()->value('description'));
        $this->assertSame(0, PointHistory::where('user_id', $student->id)->count());
        $this->assertSame(0, (int) CourseUser::where('user_id', $student->id)->value('points_earned'));
    }

    public function test_a_free_order_gives_no_diamonds_and_no_points(): void
    {
        [$student, , $order] = $this->order(0);

        app(OrderSettlementService::class)->markPaid($order);

        $student->refresh();
        $this->assertSame(1, $student->enrollments()->count());
        $this->assertSame(0, (int) $student->diamond_balance);
        $this->assertSame(0, $student->diamondHistories()->count());
        $this->assertSame(0, PointHistory::where('user_id', $student->id)->count());
    }

    public function test_setting_zero_gives_no_diamonds(): void
    {
        SiteSetting::query()->update(['diamonds_for_purchase' => 0]);
        [$student, , $order] = $this->order(150000);

        app(OrderSettlementService::class)->markPaid($order);

        $this->assertSame(0, (int) $student->fresh()->diamond_balance);
        $this->assertSame(0, $student->diamondHistories()->count());
    }
}
