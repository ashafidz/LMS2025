<?php

namespace Tests\Feature\Student;

use App\Models\PointHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

class MyPointsPageTest extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    public function test_points_page_charts_the_last_twelve_months(): void
    {
        $student = $this->makeStudent();
        $course = $this->makeCourse($this->makeInstructor());

        $thisMonth = now()->startOfMonth()->addDays(2);
        $lastMonth = now()->subMonthNoOverflow()->startOfMonth()->addDays(2);

        foreach ([[$thisMonth, 10], [$thisMonth, 5], [$thisMonth, -3], [$lastMonth, 20], [$lastMonth, -7]] as [$when, $points]) {
            $history = PointHistory::create(['user_id' => $student->id, 'course_id' => $course->id, 'points' => $points, 'description' => 'Uji']);
            $history->forceFill(['created_at' => $when, 'updated_at' => $when])->save();
        }

        $response = $this->actingAs($student)->withSession(['active_role' => 'student'])
            ->get(route('student.points.index'))
            ->assertOk()
            ->assertViewHas('chartLabels', fn ($labels) => count($labels) === 12)
            ->assertViewHas('chartDataEarned', fn ($data) => count($data) === 12 && $data[11] === 15 && $data[10] === 20 && array_sum($data) === 35)
            ->assertViewHas('chartDataSpent', fn ($data) => count($data) === 12 && $data[11] === 3 && $data[10] === 7 && array_sum($data) === 10);
    }
}
