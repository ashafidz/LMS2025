<?php

namespace Tests\Feature\Diamond;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\DiamondService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DiamondServiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Course $course;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();

        $category = CourseCategory::factory()->create();

        $this->user = $this->makeStudent();
        $this->user->update(['diamond_balance' => 100]);

        $this->course = Course::factory()->create([
            'category_id' => $category->id,
            'diamond_price' => 50,
        ]);

        SiteSetting::create([
            'point_to_diamond_rate' => 0.1, // 10 points = 1 diamond
        ]);
        cache()->forget('site_settings');
    }

    public function test_use_for_purchase_returns_false_when_insufficient_balance(): void
    {
        $this->user->update(['diamond_balance' => 20]);

        $result = DiamondService::useForPurchase($this->user, $this->course);

        $this->assertFalse($result);
        $this->assertEquals(20, $this->user->fresh()->diamond_balance);
        $this->assertFalse($this->user->fresh()->enrollments()->where('courses.id', $this->course->id)->exists());
    }

    public function test_use_for_purchase_returns_false_when_already_enrolled(): void
    {
        $this->enroll($this->user, $this->course);

        $result = DiamondService::useForPurchase($this->user, $this->course);

        $this->assertFalse($result);
        $this->assertEquals(100, $this->user->fresh()->diamond_balance);
    }

    public function test_use_for_purchase_succeeds_when_sufficient_balance(): void
    {
        $result = DiamondService::useForPurchase($this->user, $this->course);

        $this->assertTrue($result);
        $this->assertEquals(50, $this->user->fresh()->diamond_balance);
        $this->assertTrue($this->user->fresh()->enrollments()->where('courses.id', $this->course->id)->exists());
        $this->assertDatabaseHas('diamond_histories', [
            'user_id' => $this->user->id,
            'diamonds' => -50,
        ]);
    }

    public function test_convert_from_points_twice_second_returns_false_and_balance_increased_once(): void
    {
        $this->user->update(['diamond_balance' => 0]);

        DB::table('course_user')->insert([
            'user_id' => $this->user->id,
            'course_id' => $this->course->id,
            'points_earned' => 200,
            'is_converted_to_diamond' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // First conversion (200 points * 0.1 = 20 diamonds)
        $result1 = DiamondService::convertFromPoints($this->user, $this->course);
        $this->assertTrue($result1);
        $this->assertEquals(20, $this->user->fresh()->diamond_balance);

        // Second conversion attempt must return false and balance stays 20
        $result2 = DiamondService::convertFromPoints($this->user, $this->course);
        $this->assertFalse($result2);
        $this->assertEquals(20, $this->user->fresh()->diamond_balance);
    }
}
