<?php

namespace Tests\Feature\Badges;

use App\Models\Badge;
use App\Services\BadgeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsBadgeFixtures;
use Tests\TestCase;

class LegacyBadgeMigrationTest extends TestCase
{
    use BuildsBadgeFixtures;
    use RefreshDatabase;

    private function migration()
    {
        return require database_path('migrations/2026_10_25_100200_migrate_legacy_quiz_badge_rule.php');
    }

    private function legacyBadge(): Badge
    {
        return Badge::factory()->create(['id' => 1, 'title' => 'Lulus 10 Kuis']);
    }

    public function test_legacy_badge_gets_the_quiz_rule_and_owners_are_marked_seen(): void
    {
        $this->legacyBadge();
        $owner = $this->makeStudent();
        DB::table('badge_user')->insert(['user_id' => $owner->id, 'badge_id' => 1, 'created_at' => now()->subYear()]);

        $this->migration()->up();

        $rule = DB::table('badge_rules')->where('badge_id', 1)->first();
        $this->assertSame('quiz_passed', $rule->metric);
        $this->assertSame(10, (int) $rule->target);
        $this->assertSame('all', $rule->scope);
        $this->assertNotNull(DB::table('badge_user')->where('user_id', $owner->id)->value('seen_at'));
    }

    public function test_running_twice_does_not_duplicate_the_rule(): void
    {
        $this->legacyBadge();

        $this->migration()->up();
        $this->migration()->up();

        $this->assertSame(1, DB::table('badge_rules')->where('badge_id', 1)->count());
    }

    public function test_existing_rules_on_badge_one_are_left_alone(): void
    {
        $badge = $this->legacyBadge();
        $this->makeBadgeRule($badge, 'lessons_completed', 3);

        $this->migration()->up();

        $this->assertSame(['lessons_completed'], DB::table('badge_rules')->where('badge_id', 1)->pluck('metric')->all());
    }

    public function test_migration_is_harmless_when_badge_one_does_not_exist(): void
    {
        $this->migration()->up();

        $this->assertSame(0, DB::table('badge_rules')->count());
    }

    public function test_down_removes_only_the_migrated_rule(): void
    {
        $this->legacyBadge();
        $this->migration()->up();
        $other = Badge::factory()->create();
        $this->makeBadgeRule($other, 'quiz_passed', 10);

        $this->migration()->down();

        $this->assertSame(0, DB::table('badge_rules')->where('badge_id', 1)->count());
        $this->assertSame(1, DB::table('badge_rules')->where('badge_id', $other->id)->count());
    }

    public function test_old_owners_keep_the_badge_and_new_students_need_ten_distinct_quizzes(): void
    {
        $this->legacyBadge();
        $old = $this->makeStudent();
        DB::table('badge_user')->insert(['user_id' => $old->id, 'badge_id' => 1, 'created_at' => now()->subYear()]);
        $this->migration()->up();

        $newcomer = $this->makeStudent();
        [, $module] = $this->makeCourseWithModule();
        $quizzes = collect(range(1, 10))->map(fn () => $this->makeQuizIn($module));
        $quizzes->take(9)->each(fn ($quiz) => $this->passQuiz($newcomer, $quiz));
        // Mengulang kuis yang sama tidak menambah hitungan
        $this->passQuiz($newcomer, $quizzes->first());
        $this->passQuiz($newcomer, $quizzes->first());

        $this->assertCount(0, BadgeService::evaluate($newcomer));

        $this->passQuiz($newcomer, $quizzes->last());
        $this->assertCount(1, BadgeService::evaluate($newcomer));
        $this->assertDatabaseHas('badge_user', ['user_id' => $old->id, 'badge_id' => 1]);
    }

    private function makeBadgeRule(Badge $badge, string $metric, int $target): void
    {
        \App\Models\BadgeRule::factory()->create(['badge_id' => $badge->id, 'metric' => $metric, 'target' => $target]);
    }
}
