<?php

namespace Tests\Feature\Badges;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsBadgeFixtures;
use Tests\TestCase;

class BadgeStudentTest extends TestCase
{
    use BuildsBadgeFixtures;
    use RefreshDatabase;

    private function student()
    {
        return $this->makeStudent();
    }

    public function test_my_badges_shows_real_progress_for_unearned_badges(): void
    {
        $student = $this->student();
        [, $module] = $this->makeCourseWithModule();
        for ($i = 0; $i < 7; $i++) {
            $this->passQuiz($student, $this->makeQuizIn($module));
        }
        $this->makeBadge([['metric' => 'quiz_passed', 'target' => 10]], ['title' => 'Jago Kuis']);

        $this->actingAs($student)->get(route('student.badges.index'))
            ->assertOk()
            ->assertSee('Jago Kuis')
            ->assertSee('7 dari 10 kuis');
    }

    public function test_my_badges_shows_earned_state_with_date_and_no_progress_bar(): void
    {
        $student = $this->student();
        $badge = $this->makeBadge([['metric' => 'quiz_passed', 'target' => 10]], ['title' => 'Sudah Punya']);
        $badge->users()->attach($student->id, ['created_at' => now(), 'seen_at' => now()]);

        $this->actingAs($student)->get(route('student.badges.index'))
            ->assertOk()
            ->assertSee('Diperoleh')
            ->assertSee('Didapat pada '.now()->format('d M Y'))
            ->assertDontSee('0 dari 10 kuis');
    }

    public function test_badge_without_rules_is_marked_as_given_by_admin_without_fake_progress(): void
    {
        $student = $this->student();
        $this->makeBadge([], ['title' => 'Penghargaan Khusus']);

        $this->actingAs($student)->get(route('student.badges.index'))
            ->assertOk()
            ->assertSee('Penghargaan Khusus')
            ->assertSee('Diberikan oleh admin')
            ->assertDontSee('0 / 1');
    }

    public function test_inactive_badges_are_hidden_from_students(): void
    {
        $student = $this->student();
        $this->makeBadge([['metric' => 'quiz_passed', 'target' => 1]], ['title' => 'Badge Nonaktif', 'is_active' => false]);

        $this->actingAs($student)->get(route('student.badges.index'))->assertOk()->assertDontSee('Badge Nonaktif');
    }

    public function test_new_badge_notification_shows_once_then_is_marked_seen(): void
    {
        $student = $this->student();
        $badge = $this->makeBadge([['metric' => 'quiz_passed', 'target' => 1]], ['title' => 'Badge Baru Keren']);
        $badge->users()->attach($student->id, ['created_at' => now()]);

        $this->actingAs($student)->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('Selamat! Kamu mendapat badge baru', false)
            ->assertSee('Badge Baru Keren', false);

        $this->assertNotNull(DB::table('badge_user')->where('user_id', $student->id)->value('seen_at'));

        $this->actingAs($student)->get(route('student.dashboard'))
            ->assertOk()
            ->assertDontSee('Selamat! Kamu mendapat badge baru', false);
    }

    public function test_notification_escapes_badge_title(): void
    {
        $student = $this->student();
        $badge = $this->makeBadge([['metric' => 'quiz_passed', 'target' => 1]], ['title' => '<img src=x onerror=alert(1)>']);
        $badge->users()->attach($student->id, ['created_at' => now()]);

        $response = $this->actingAs($student)->get(route('student.dashboard'))->assertOk();

        $response->assertSee('Selamat! Kamu mendapat badge baru', false);
        $response->assertDontSee('<img src=x onerror=alert(1)>', false);
    }

    public function test_notification_shows_at_most_three_badges_but_marks_all_seen(): void
    {
        $student = $this->student();
        foreach (range(1, 5) as $n) {
            $badge = $this->makeBadge([['metric' => 'quiz_passed', 'target' => 1]], ['title' => "Lencana-$n"]);
            $badge->users()->attach($student->id, ['created_at' => now()]);
        }

        $html = $this->actingAs($student)->get(route('student.dashboard'))->assertOk()->getContent();

        $this->assertSame(3, preg_match_all('/Lencana-\d/', $html));
        $this->assertSame(0, DB::table('badge_user')->where('user_id', $student->id)->whereNull('seen_at')->count());
    }

    public function test_notification_is_not_consumed_outside_student_role(): void
    {
        $user = $this->makeInstructor();
        $user->assignRole('student');
        $badge = $this->makeBadge([['metric' => 'quiz_passed', 'target' => 1]]);
        $badge->users()->attach($user->id, ['created_at' => now()]);

        $this->actingAs($user)->withSession(['active_role' => 'instructor'])->get(route('instructor.dashboard'));

        $this->assertNull(DB::table('badge_user')->where('user_id', $user->id)->value('seen_at'));
    }

    public function test_inactive_badge_does_not_notify_but_is_marked_seen(): void
    {
        $student = $this->student();
        $badge = $this->makeBadge([['metric' => 'quiz_passed', 'target' => 1]], ['title' => 'Mati Suri', 'is_active' => false]);
        $badge->users()->attach($student->id, ['created_at' => now()]);

        $this->actingAs($student)->get(route('student.dashboard'))->assertOk()->assertDontSee('Mati Suri', false);

        $this->assertNotNull(DB::table('badge_user')->where('user_id', $student->id)->value('seen_at'));
    }
}
