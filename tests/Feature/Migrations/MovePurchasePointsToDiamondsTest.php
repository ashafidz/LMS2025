<?php

namespace Tests\Feature\Migrations;

use App\Models\Course;
use App\Models\CourseUser;
use App\Models\DiamondHistory;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PointHistory;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Data fixes of hotfix 2.3.1 (ADR-016): old purchase points leave the point history; paid ones become diamonds.
 * Diamond balances without history get an opening history row.
 */
class MovePurchasePointsToDiamondsTest extends TestCase
{
    use RefreshDatabase;

    private function migration(string $file)
    {
        return require database_path('migrations/'.$file);
    }

    private function buyer(Course $course, Lesson $lesson, float $finalAmount, bool $converted = false): User
    {
        $student = $this->makeStudent();
        PointHistory::create(['user_id' => $student->id, 'course_id' => $course->id, 'lesson_id' => $lesson->id, 'points' => 72, 'description' => 'Materi']);
        PointHistory::create(['user_id' => $student->id, 'course_id' => $course->id, 'lesson_id' => null, 'points' => 100, 'description' => 'Membeli kursus: '.$course->title]);
        CourseUser::create(['user_id' => $student->id, 'course_id' => $course->id, 'points_earned' => 172, 'is_converted_to_diamond' => $converted]);
        $order = Order::factory()->create(['user_id' => $student->id, 'status' => 'paid', 'final_amount' => $finalAmount]);
        OrderItem::create(['order_id' => $order->id, 'course_id' => $course->id, 'price_at_purchase' => 150000]);

        return $student;
    }

    private function points(User $student, Course $course): int
    {
        return (int) CourseUser::where('user_id', $student->id)->where('course_id', $course->id)->value('points_earned');
    }

    public function test_purchase_points_move_to_diamonds_only_for_paid_orders_and_can_be_rolled_back(): void
    {
        SiteSetting::create(['site_name' => 'ruangstudi.id', 'point_to_diamond_rate' => 1]);
        $migration = $this->migration('2026_11_24_000100_move_purchase_points_to_diamonds.php');
        $migration->down(); // already ran on the empty test database

        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['module_id' => Module::factory()->create(['course_id' => $course->id])->id]);
        $paid = $this->buyer($course, $lesson, 150000);
        $free = $this->buyer($course, $lesson, 0);
        $converted = $this->buyer($course, $lesson, 150000, true);

        $migration->up();

        $this->assertSame(72, $this->points($paid, $course));
        $this->assertSame(100, (int) $paid->fresh()->diamond_balance);
        $this->assertSame(1, DiamondHistory::where('user_id', $paid->id)->count());

        $this->assertSame(72, $this->points($free, $course));
        $this->assertSame(0, (int) $free->fresh()->diamond_balance);
        $this->assertSame(0, DiamondHistory::where('user_id', $free->id)->count());

        $this->assertSame(0, PointHistory::whereIn('user_id', [$paid->id, $free->id])->whereNull('lesson_id')->count());

        $this->assertSame(172, $this->points($converted, $course));
        $this->assertSame(1, PointHistory::where('user_id', $converted->id)->whereNull('lesson_id')->count());

        $migration->down();

        foreach ([$paid, $free, $converted] as $student) {
            $this->assertSame(172, $this->points($student, $course));
            $this->assertSame(0, (int) $student->fresh()->diamond_balance);
            $this->assertSame(1, PointHistory::where('user_id', $student->id)->whereNull('lesson_id')->count());
            $this->assertSame(0, DiamondHistory::where('user_id', $student->id)->count());
        }

        $migration->up(); // leave the schema as the other tests expect
    }

    public function test_opening_diamond_history_matches_balances_without_changing_them(): void
    {
        $migration = $this->migration('2026_11_24_000200_add_opening_diamond_histories.php');
        $withoutHistory = $this->makeStudent(['diamond_balance' => 149]);
        $consistent = $this->makeStudent(['diamond_balance' => 20]);
        DiamondHistory::create(['user_id' => $consistent->id, 'diamonds' => 20, 'description' => 'Konversi']);

        $migration->up();

        $this->assertSame(149, (int) $withoutHistory->fresh()->diamond_balance);
        $this->assertSame(149, (int) DiamondHistory::where('user_id', $withoutHistory->id)->sum('diamonds'));
        $this->assertSame(1, DiamondHistory::where('user_id', $consistent->id)->count());

        $migration->down();
        $this->assertSame(0, DiamondHistory::where('user_id', $withoutHistory->id)->count());
    }
}
