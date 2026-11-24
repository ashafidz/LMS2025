<?php

namespace Tests\Feature\Points;

use App\Models\Course;
use App\Models\CourseUser;
use App\Models\Lesson;
use App\Models\LessonPoint;
use App\Models\Module;
use App\Models\PointHistory;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Superadmin point sync (hotfix 2.3.1): finds duplicate rows and totals that differ from the history, and
 * fixes them by rebuilding the total from the history.
 */
class PointSyncTest extends TestCase
{
    use RefreshDatabase;

    private Course $course;

    private Module $module;

    private Lesson $article;

    private User $student;

    private User $superadmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        SiteSetting::create(['site_name' => 'ruangstudi.id', 'points_for_article' => 3]);
        cache()->forget('site_settings');

        $this->course = Course::factory()->create(['instructor_id' => $this->makeInstructor()->id]);
        $this->module = Module::factory()->create(['course_id' => $this->course->id]);
        $this->article = Lesson::factory()->create(['module_id' => $this->module->id, 'title' => 'Artikel Satu']);
        $this->student = $this->makeStudent(['name' => 'Siswa Sinkron']);
        $this->enroll($this->student, $this->course);
        $this->student->completedLessons()->syncWithoutDetaching($this->article->id);
        $this->superadmin = $this->makeSuperadmin();
    }

    private function asSuperadmin()
    {
        return $this->actingAs($this->superadmin)->withSession(['active_role' => 'superadmin']);
    }

    private function history(Lesson $lesson, int $points): void
    {
        PointHistory::create([
            'user_id' => $this->student->id, 'course_id' => $this->course->id, 'lesson_id' => $lesson->id,
            'points' => $points, 'description' => 'Menyelesaikan artikel: '.$lesson->title,
        ]);
    }

    private function total(): int
    {
        return (int) CourseUser::where('user_id', $this->student->id)->where('course_id', $this->course->id)->value('points_earned');
    }

    public function test_duplicate_rows_are_found_and_reduced_to_one(): void
    {
        $this->history($this->article, 3);
        $this->history($this->article, 3);
        CourseUser::create(['user_id' => $this->student->id, 'course_id' => $this->course->id, 'points_earned' => 6]);

        $this->asSuperadmin()->get(route('superadmin.point-sync.mass-sync.preview', $this->course))
            ->assertOk()->assertSee('Riwayat ganda');

        $this->asSuperadmin()->post(route('superadmin.point-sync.mass-sync.execute', $this->course))->assertRedirect();

        $this->assertSame(1, PointHistory::where('user_id', $this->student->id)->where('lesson_id', $this->article->id)->count());
        $this->assertSame(3, $this->total());
    }

    public function test_a_total_that_differs_from_the_history_is_found_and_rebuilt(): void
    {
        $this->history($this->article, 3);
        CourseUser::create(['user_id' => $this->student->id, 'course_id' => $this->course->id, 'points_earned' => 103]);

        $this->asSuperadmin()->get(route('superadmin.point-sync.mass-sync.preview', $this->course))
            ->assertOk()->assertSee('Total tidak sama dengan riwayat poin.');

        $this->asSuperadmin()->post(route('superadmin.point-sync.mass-sync.execute', $this->course))->assertRedirect();

        $this->assertSame(3, $this->total());
    }

    public function test_manual_points_lessons_with_several_rows_are_not_anomalies(): void
    {
        $session = LessonPoint::create(['title' => 'Sesi', 'description' => 'Sesi']);
        $manual = Lesson::factory()->create([
            'module_id' => $this->module->id, 'lessonable_type' => LessonPoint::class, 'lessonable_id' => $session->id,
        ]);
        $this->history($this->article, 3);
        $this->history($manual, 10);
        $this->history($manual, 5);
        CourseUser::create(['user_id' => $this->student->id, 'course_id' => $this->course->id, 'points_earned' => 18]);

        $this->asSuperadmin()->get(route('superadmin.point-sync.mass-sync.preview', $this->course))
            ->assertOk()->assertSee('Sistem Poin Stabil');
    }

    public function test_single_lesson_sync_rebuilds_the_total(): void
    {
        CourseUser::create(['user_id' => $this->student->id, 'course_id' => $this->course->id, 'points_earned' => 50]);

        $this->asSuperadmin()->post(route('superadmin.point-sync.sync-points', [$this->course, $this->student]), [
            'lesson_id' => $this->article->id,
            'expected_points' => 3,
            'description' => 'Menyelesaikan artikel: Artikel Satu',
        ])->assertRedirect();

        $this->assertSame(3, $this->total());
    }

    public function test_single_lesson_sync_rejects_a_lesson_from_another_course(): void
    {
        $otherLesson = Lesson::factory()->create();

        $this->asSuperadmin()->post(route('superadmin.point-sync.sync-points', [$this->course, $this->student]), [
            'lesson_id' => $otherLesson->id,
            'expected_points' => 3,
            'description' => 'x',
        ])->assertNotFound();
    }
}
