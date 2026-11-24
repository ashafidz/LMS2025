<?php

namespace Tests\Feature\Points;

use App\Models\Course;
use App\Models\CourseUser;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\PointHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Leaderboards (hotfix 2.3.1): every student's points are shown, a fixed order for equal points, and the
 * student's own place outside the top 100.
 */
class LeaderboardPointsTest extends TestCase
{
    use RefreshDatabase;

    private User $instructor;

    private Course $course;

    private Module $module;

    private Lesson $lesson;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        $this->instructor = $this->makeInstructor();
        $this->course = Course::factory()->create(['instructor_id' => $this->instructor->id, 'status' => 'published']);
        $this->module = Module::factory()->create(['course_id' => $this->course->id]);
        $this->lesson = Lesson::factory()->create(['module_id' => $this->module->id]);
    }

    /**
     * @return array<int, User>
     */
    private function studentsWithPoints(int $count, int $points, string $prefix): array
    {
        $students = [];
        for ($i = 1; $i <= $count; $i++) {
            $student = $this->makeStudent(['name' => $prefix.' '.$i]);
            $this->enroll($student, $this->course);
            CourseUser::create(['user_id' => $student->id, 'course_id' => $this->course->id, 'points_earned' => $points]);
            PointHistory::create([
                'user_id' => $student->id, 'course_id' => $this->course->id, 'lesson_id' => $this->lesson->id,
                'points' => $points, 'description' => 'Menyelesaikan artikel: '.$this->lesson->title,
            ]);
            $students[] = $student;
        }

        return $students;
    }

    private function asInstructor()
    {
        return $this->actingAs($this->instructor)->withSession(['active_role' => 'instructor']);
    }

    public function test_course_leaderboard_shows_points_for_students_beyond_the_top_100(): void
    {
        $this->studentsWithPoints(100, 200, 'Atas');
        $this->studentsWithPoints(1, 50, 'Ke101');

        $html = $this->asInstructor()->getJson(route('instructor.course.leaderboard', $this->course))
            ->assertOk()->json('html');

        $peringkat = substr($html, strpos($html, 'id="peringkat"'));
        $row = substr($peringkat, strpos($peringkat, 'Ke101 1'), 300);
        $this->assertStringContainsString('#101', substr($peringkat, strpos($peringkat, 'Ke101 1') - 400, 400));
        $this->assertMatchesRegularExpression('/>\s*50\s*</', $row);
    }

    public function test_module_leaderboard_lists_more_than_100_students(): void
    {
        $this->studentsWithPoints(100, 200, 'Atas');
        $this->studentsWithPoints(1, 50, 'Ke101');

        $html = $this->asInstructor()->getJson(route('instructor.module.leaderboard', $this->module))
            ->assertOk()->json('html');

        $this->assertStringContainsString('Ke101 1', $html);
    }

    public function test_equal_points_are_ordered_by_who_reached_them_first(): void
    {
        [$late] = $this->studentsWithPoints(1, 80, 'Telat');
        [$early] = $this->studentsWithPoints(1, 80, 'Awal');
        CourseUser::where('user_id', $early->id)->update(['updated_at' => Carbon::parse('2026-01-01 08:00:00')]);
        CourseUser::where('user_id', $late->id)->update(['updated_at' => Carbon::parse('2026-01-02 08:00:00')]);
        PointHistory::where('user_id', $early->id)->update(['created_at' => Carbon::parse('2026-01-01 08:00:00')]);
        PointHistory::where('user_id', $late->id)->update(['created_at' => Carbon::parse('2026-01-02 08:00:00')]);

        $html = $this->asInstructor()->getJson(route('instructor.course.leaderboard', $this->course))->json('html');
        $peringkat = substr($html, strpos($html, 'id="peringkat"'));
        $this->assertLessThan(strpos($peringkat, 'Telat 1'), strpos($peringkat, 'Awal 1'));

        $student = $this->actingAs($late)->withSession(['active_role' => 'student']);
        $courseHtml = $student->getJson(route('student.course.leaderboard', $this->course))->json('html');
        $this->assertLessThan(strpos($courseHtml, 'Telat 1'), strpos($courseHtml, 'Awal 1'));

        $moduleHtml = $student->getJson(route('student.module.leaderboard', $this->module))->json('html');
        $this->assertLessThan(strpos($moduleHtml, 'Telat 1'), strpos($moduleHtml, 'Awal 1'));
    }

    public function test_student_outside_the_top_100_sees_their_own_rank(): void
    {
        $this->studentsWithPoints(100, 200, 'Atas');
        [$last] = $this->studentsWithPoints(1, 50, 'Ke101');

        $html = $this->actingAs($last)->withSession(['active_role' => 'student'])
            ->getJson(route('student.course.leaderboard', $this->course))
            ->assertOk()->json('html');

        $this->assertStringContainsString('Peringkat Anda: #101', $html);
        $this->assertStringNotContainsString('Ke101 1', substr($html, 0, strpos($html, 'Peringkat Anda')));
    }

    public function test_student_without_points_sees_a_no_points_message(): void
    {
        $this->studentsWithPoints(3, 10, 'Lain');
        $fresh = $this->makeStudent();
        $this->enroll($fresh, $this->course);

        $student = $this->actingAs($fresh)->withSession(['active_role' => 'student']);
        $this->assertStringContainsString('Anda belum punya poin di kursus ini.',
            $student->getJson(route('student.course.leaderboard', $this->course))->json('html'));
        $this->assertStringContainsString('Anda belum punya poin di modul ini.',
            $student->getJson(route('student.module.leaderboard', $this->module))->json('html'));
    }

    public function test_student_inside_the_top_100_gets_no_extra_row(): void
    {
        [$student] = $this->studentsWithPoints(1, 10, 'Satu');

        $html = $this->actingAs($student)->withSession(['active_role' => 'student'])
            ->getJson(route('student.course.leaderboard', $this->course))->json('html');

        $this->assertStringNotContainsString('Peringkat Anda', $html);
    }
}
