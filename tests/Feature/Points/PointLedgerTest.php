<?php

namespace Tests\Feature\Points;

use App\Models\Course;
use App\Models\CourseUser;
use App\Models\DiamondHistory;
use App\Models\Lesson;
use App\Models\LessonPoint;
use App\Models\Module;
use App\Models\PointHistory;
use App\Models\User;
use App\Services\PointLedger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The point history is the source of truth; course_user.points_earned is a cache rebuilt from it (ADR-016).
 */
class PointLedgerTest extends TestCase
{
    use RefreshDatabase;

    private User $instructor;

    private Course $course;

    private Module $module;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        $this->instructor = $this->makeInstructor();
        $this->course = Course::factory()->create(['instructor_id' => $this->instructor->id]);
        $this->module = Module::factory()->create(['course_id' => $this->course->id]);
    }

    private function history(User $user, ?Lesson $lesson, int $points, string $description = 'Menyelesaikan artikel: X'): void
    {
        PointHistory::create([
            'user_id' => $user->id, 'course_id' => $this->course->id, 'lesson_id' => $lesson?->id,
            'points' => $points, 'description' => $description,
        ]);
    }

    private function total(User $user): ?int
    {
        $value = CourseUser::where('user_id', $user->id)->where('course_id', $this->course->id)->value('points_earned');

        return $value === null ? null : (int) $value;
    }

    private function asInstructor()
    {
        return $this->actingAs($this->instructor)->withSession(['active_role' => 'instructor']);
    }

    public function test_recalculate_rebuilds_the_total_from_history(): void
    {
        $lesson = Lesson::factory()->create(['module_id' => $this->module->id]);
        [$higher, $lower, $missing] = [$this->makeStudent(), $this->makeStudent(), $this->makeStudent()];

        foreach ([$higher, $lower, $missing] as $student) {
            $this->history($student, $lesson, 30);
        }
        CourseUser::create(['user_id' => $higher->id, 'course_id' => $this->course->id, 'points_earned' => 130]);
        CourseUser::create(['user_id' => $lower->id, 'course_id' => $this->course->id, 'points_earned' => 5]);

        $drift = PointLedger::drift($this->course->id)->keyBy('user_id');
        $this->assertCount(3, $drift);
        $this->assertSame(130, $drift[$higher->id]->counter);
        $this->assertSame(0, $drift[$missing->id]->counter);
        $this->assertSame(30, $drift[$missing->id]->ledger);

        foreach ([$higher, $lower, $missing] as $student) {
            $this->assertSame(30, PointLedger::recalculate($student->id, $this->course->id));
            $this->assertSame(30, $this->total($student));
        }
        $this->assertCount(0, PointLedger::drift($this->course->id));
    }

    public function test_drift_ignores_soft_deleted_courses(): void
    {
        $student = $this->makeStudent();
        CourseUser::create(['user_id' => $student->id, 'course_id' => $this->course->id, 'points_earned' => 99]);
        $this->course->delete();

        $this->assertCount(0, PointLedger::drift());
    }

    public function test_deleting_a_lesson_removes_its_points_from_the_total(): void
    {
        $keep = Lesson::factory()->create(['module_id' => $this->module->id]);
        $gone = Lesson::factory()->create(['module_id' => $this->module->id]);
        [$a, $b] = [$this->makeStudent(), $this->makeStudent()];
        foreach ([[$a, 10], [$b, 20]] as [$student, $extra]) {
            $this->history($student, $keep, 3);
            $this->history($student, $gone, $extra);
            CourseUser::create(['user_id' => $student->id, 'course_id' => $this->course->id, 'points_earned' => 3 + $extra]);
        }

        $this->asInstructor()->delete(route('instructor.lessons.destroy', $gone))->assertRedirect();

        $this->assertSame(3, $this->total($a));
        $this->assertSame(3, $this->total($b));
    }

    public function test_deleting_a_module_removes_the_points_of_all_its_lessons(): void
    {
        $other = Module::factory()->create(['course_id' => $this->course->id]);
        $keep = Lesson::factory()->create(['module_id' => $other->id]);
        $first = Lesson::factory()->create(['module_id' => $this->module->id]);
        $second = Lesson::factory()->create(['module_id' => $this->module->id]);
        $student = $this->makeStudent();
        $this->history($student, $keep, 5);
        $this->history($student, $first, 3);
        $this->history($student, $second, 15);
        CourseUser::create(['user_id' => $student->id, 'course_id' => $this->course->id, 'points_earned' => 23]);

        $this->asInstructor()->delete(route('instructor.modules.destroy', $this->module))->assertRedirect();

        $this->assertSame(5, $this->total($student));
    }

    public function test_manual_points_work_without_an_existing_total_and_add_up(): void
    {
        $session = LessonPoint::create(['title' => 'Sesi', 'description' => 'Sesi kelas']);
        $lesson = Lesson::factory()->create([
            'module_id' => $this->module->id, 'lessonable_type' => LessonPoint::class, 'lessonable_id' => $session->id,
        ]);
        $student = $this->makeStudent();
        $this->enroll($student, $this->course);

        $this->asInstructor()->post(route('instructor.lesson_points.award', $lesson), ['user_id' => $student->id, 'points' => 10])->assertRedirect();
        $this->assertSame(10, $this->total($student));

        $this->asInstructor()->post(route('instructor.lesson_points.award', $lesson), ['user_id' => $student->id, 'points' => 5])->assertRedirect();
        $this->assertSame(15, $this->total($student));
        $this->assertSame(2, PointHistory::where('user_id', $student->id)->where('lesson_id', $lesson->id)->count());
    }

    public function test_progress_page_sums_every_row_of_a_lesson_and_shows_points_outside_lessons(): void
    {
        $session = LessonPoint::create(['title' => 'Sesi', 'description' => 'Sesi kelas']);
        $lesson = Lesson::factory()->create([
            'module_id' => $this->module->id, 'title' => 'Sesi Poin', 'lessonable_type' => LessonPoint::class, 'lessonable_id' => $session->id,
        ]);
        $student = $this->makeStudent();
        $this->enroll($student, $this->course);
        $this->history($student, $lesson, 10, 'Menerima poin dari sesi: Sesi Poin');
        $this->history($student, $lesson, 5, 'Menerima poin dari sesi: Sesi Poin');
        $this->history($student, null, 100, 'Membeli kursus: Contoh');
        CourseUser::create(['user_id' => $student->id, 'course_id' => $this->course->id, 'points_earned' => 115]);

        $html = $this->asInstructor()->get(route('instructor.courses.student_progress', [$this->course, $student]))
            ->assertOk()->getContent();

        $this->assertStringContainsString('rs-points-total', $html);
        $this->assertMatchesRegularExpression('/rs-points-total">.*?115<\/h3>/s', $html);
        $this->assertStringContainsString('15 / 0 Poin', $html);
        $this->assertStringContainsString('Poin di luar materi', $html);
        $this->assertStringContainsString('Membeli kursus: Contoh', $html);

        $superadmin = $this->makeSuperadmin();
        $superHtml = $this->actingAs($superadmin)->withSession(['active_role' => 'superadmin'])
            ->get(route('superadmin.point-sync.student-progress', [$this->course, $student]))
            ->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/rs-points-total">.*?115<\/h3>/s', $superHtml);
        $this->assertStringContainsString('Poin di luar materi', $superHtml);
    }

    public function test_reconcile_reports_without_fix_and_repairs_with_fix(): void
    {
        $lesson = Lesson::factory()->create(['module_id' => $this->module->id]);
        $student = $this->makeStudent(['diamond_balance' => 149]);
        $this->history($student, $lesson, 30);
        CourseUser::create(['user_id' => $student->id, 'course_id' => $this->course->id, 'points_earned' => 35]);

        $this->artisan('points:reconcile')->assertSuccessful();
        $this->assertSame(35, $this->total($student));

        $this->artisan('points:reconcile --fix')->assertSuccessful();
        $this->assertSame(30, $this->total($student));

        // Diamond drift is only reported
        $this->assertSame(149, (int) $student->fresh()->diamond_balance);
        $this->assertSame(0, DiamondHistory::where('user_id', $student->id)->count());
    }
}
