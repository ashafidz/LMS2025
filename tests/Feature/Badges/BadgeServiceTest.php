<?php

namespace Tests\Feature\Badges;

use App\Jobs\EvaluateUserBadges;
use App\Services\BadgeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsBadgeFixtures;
use Tests\TestCase;

class BadgeServiceTest extends TestCase
{
    use BuildsBadgeFixtures;
    use RefreshDatabase;

    private function passQuizzes($student, int $count): void
    {
        [, $module] = $this->makeCourseWithModule();
        for ($i = 0; $i < $count; $i++) {
            $this->passQuiz($student, $this->makeQuizIn($module), 80);
        }
    }

    public function test_awards_badge_when_all_rules_are_met(): void
    {
        $student = $this->makeStudent();
        $badge = $this->makeBadge([
            ['metric' => 'quiz_passed', 'target' => 2],
            ['metric' => 'quiz_perfect', 'target' => 1],
        ]);
        $this->passQuizzes($student, 2);
        [, $module] = $this->makeCourseWithModule();
        $this->passQuiz($student, $this->makeQuizIn($module), 100);

        $awarded = BadgeService::evaluate($student);

        $this->assertSame([$badge->id], $awarded->pluck('id')->all());
        $this->assertDatabaseHas('badge_user', ['user_id' => $student->id, 'badge_id' => $badge->id, 'seen_at' => null]);
        $this->assertNotNull(DB::table('badge_user')->where('user_id', $student->id)->value('created_at'));
    }

    public function test_does_not_award_when_one_rule_is_short(): void
    {
        $student = $this->makeStudent();
        $this->makeBadge([
            ['metric' => 'quiz_passed', 'target' => 2],
            ['metric' => 'quiz_perfect', 'target' => 1],
        ]);
        $this->passQuizzes($student, 3);

        $this->assertCount(0, BadgeService::evaluate($student));
        $this->assertDatabaseCount('badge_user', 0);
    }

    public function test_badge_is_never_revoked_when_data_drops(): void
    {
        $student = $this->makeStudent();
        $badge = $this->makeBadge([['metric' => 'quiz_passed', 'target' => 1]]);
        $this->passQuizzes($student, 1);
        BadgeService::evaluate($student);

        $student->quizAttempts()->delete();
        BadgeService::evaluate($student);

        $this->assertDatabaseHas('badge_user', ['user_id' => $student->id, 'badge_id' => $badge->id]);
    }

    public function test_inactive_badge_and_badge_without_rules_are_not_awarded(): void
    {
        $student = $this->makeStudent();
        $this->makeBadge([['metric' => 'quiz_passed', 'target' => 1]], ['is_active' => false]);
        $this->makeBadge([]);
        $this->passQuizzes($student, 1);

        $this->assertCount(0, BadgeService::evaluate($student));
    }

    public function test_only_students_are_evaluated(): void
    {
        $instructor = $this->makeInstructor();
        $this->makeBadge([['metric' => 'quiz_passed', 'target' => 1]]);
        [, $module] = $this->makeCourseWithModule();
        $this->passQuiz($instructor, $this->makeQuizIn($module));

        $this->assertCount(0, BadgeService::evaluate($instructor));
    }

    public function test_evaluating_twice_does_not_duplicate(): void
    {
        $student = $this->makeStudent();
        $this->makeBadge([['metric' => 'quiz_passed', 'target' => 1]]);
        $this->passQuizzes($student, 1);

        $this->assertCount(1, BadgeService::evaluate($student));
        $this->assertCount(0, BadgeService::evaluate($student));
        $this->assertDatabaseCount('badge_user', 1);
    }

    public function test_progress_reports_current_and_target_per_rule(): void
    {
        $student = $this->makeStudent();
        $badge = $this->makeBadge([
            ['metric' => 'quiz_passed', 'target' => 10],
            ['metric' => 'lessons_completed', 'target' => 5],
        ]);
        $this->passQuizzes($student, 7);

        $progress = BadgeService::progressFor($student, $badge);

        $this->assertFalse($progress['met']);
        $this->assertSame(7, $progress['rules'][0]['current']);
        $this->assertSame(10, $progress['rules'][0]['target']);
        $this->assertFalse($progress['rules'][0]['met']);
        $this->assertSame('kuis', $progress['rules'][0]['unit']);
        $this->assertSame(0, $progress['rules'][1]['current']);
    }

    public function test_progress_is_met_only_when_every_rule_is_met(): void
    {
        $student = $this->makeStudent();
        $badge = $this->makeBadge([['metric' => 'quiz_passed', 'target' => 2]]);
        $this->passQuizzes($student, 2);

        $this->assertTrue(BadgeService::progressFor($student, $badge)['met']);
    }

    public function test_backfill_awards_all_qualifying_students_in_chunks(): void
    {
        $a = $this->makeStudent();
        $b = $this->makeStudent();
        $c = $this->makeStudent();
        $badge = $this->makeBadge([['metric' => 'quiz_passed', 'target' => 1]]);
        $this->passQuizzes($a, 1);
        $this->passQuizzes($b, 2);

        $this->assertSame(2, BadgeService::backfill($badge));
        $this->assertSame(0, BadgeService::backfill($badge));
        $this->assertDatabaseMissing('badge_user', ['user_id' => $c->id]);
    }

    public function test_backfill_does_nothing_for_inactive_or_rule_less_badge(): void
    {
        $student = $this->makeStudent();
        $this->passQuizzes($student, 1);
        $inactive = $this->makeBadge([['metric' => 'quiz_passed', 'target' => 1]], ['is_active' => false]);
        $empty = $this->makeBadge([]);

        $this->assertSame(0, BadgeService::backfill($inactive));
        $this->assertSame(0, BadgeService::backfill($empty));
    }

    public function test_queue_for_dispatches_unique_job_for_user(): void
    {
        Bus::fake();
        $student = $this->makeStudent();

        BadgeService::queueFor($student);
        BadgeService::queueFor($student->id);

        Bus::assertDispatched(EvaluateUserBadges::class, fn ($job) => $job->userId === $student->id);
        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldBeUnique::class, new EvaluateUserBadges($student->id));
    }

    public function test_job_evaluates_the_user(): void
    {
        $student = $this->makeStudent();
        $badge = $this->makeBadge([['metric' => 'quiz_passed', 'target' => 1]]);
        $this->passQuizzes($student, 1);

        (new EvaluateUserBadges($student->id))->handle();

        $this->assertDatabaseHas('badge_user', ['user_id' => $student->id, 'badge_id' => $badge->id]);
    }

    public function test_job_ignores_deleted_user(): void
    {
        (new EvaluateUserBadges(999999))->handle();

        $this->assertDatabaseCount('badge_user', 0);
    }

    public function test_award_command_backfills_all_active_badges_or_one(): void
    {
        $student = $this->makeStudent();
        $one = $this->makeBadge([['metric' => 'quiz_passed', 'target' => 1]]);
        $two = $this->makeBadge([['metric' => 'quiz_passed', 'target' => 1]]);
        $this->passQuizzes($student, 1);

        $this->artisan('badges:award', ['--badge' => $one->id])->assertExitCode(0);
        $this->assertDatabaseHas('badge_user', ['badge_id' => $one->id, 'user_id' => $student->id]);
        $this->assertDatabaseMissing('badge_user', ['badge_id' => $two->id, 'user_id' => $student->id]);

        $this->artisan('badges:award')->assertExitCode(0);
        $this->assertDatabaseHas('badge_user', ['badge_id' => $two->id, 'user_id' => $student->id]);
    }

    public function test_award_command_is_scheduled_daily(): void
    {
        $events = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events())
            ->filter(fn ($event) => str_contains($event->command, 'badges:award'));

        $this->assertCount(1, $events);
        $this->assertSame('0 2 * * *', $events->first()->expression);
    }

    public function test_progress_has_no_sentence_unless_asked_and_evaluate_never_looks_up_names(): void
    {
        $student = $this->makeStudent();
        [$course, $module] = $this->makeCourseWithModule();
        $this->passQuiz($student, $this->makeQuizIn($module));
        $badge = $this->makeBadge([['metric' => 'quiz_passed', 'target' => 1, 'scope' => 'course', 'scope_id' => $course->id]]);

        $this->assertArrayNotHasKey('sentence', BadgeService::progressFor($student, $badge)['rules'][0]);
        $this->assertSame(
            'Lulus kuis minimal 1 kuis di kursus '.$course->title,
            BadgeService::progressFor($student, $badge, true)['rules'][0]['sentence']
        );

        \DB::flushQueryLog();
        \DB::enableQueryLog();
        BadgeService::evaluate($student);
        $titleLookups = collect(\DB::getQueryLog())->filter(fn ($q) => str_contains($q['query'], '"title" from "courses"'))->count();

        $this->assertSame(0, $titleLookups, 'evaluate tidak boleh mengambil nama kursus untuk kalimat syarat');
        $this->assertDatabaseHas('badge_user', ['user_id' => $student->id, 'badge_id' => $badge->id]);
    }

    public function test_evaluation_job_unique_lock_is_short(): void
    {
        $this->assertSame(10, (new EvaluateUserBadges(1))->uniqueFor);
    }
}
