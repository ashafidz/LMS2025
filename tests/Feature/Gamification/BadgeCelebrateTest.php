<?php

namespace Tests\Feature\Gamification;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsBadgeFixtures;
use Tests\TestCase;

class BadgeCelebrateTest extends TestCase
{
    use BuildsBadgeFixtures;
    use RefreshDatabase;

    private function giveBadge($student, array $attributes = [])
    {
        $badge = $this->makeBadge([['metric' => 'quiz_passed', 'target' => 1]], $attributes);
        $badge->users()->attach($student->id, ['created_at' => now()]);

        return $badge;
    }

    public function test_new_badge_shows_celebration_once_then_is_marked_seen(): void
    {
        $student = $this->makeStudent();
        $this->giveBadge($student, ['title' => 'Juara Kelas']);

        $this->actingAs($student)->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('rs-swal-badge', false)
            ->assertSee('Nanti saja', false)
            ->assertSee('Selamat! Kamu dapat badge baru', false)
            ->assertSee('Juara Kelas', false);

        $this->assertNotNull(DB::table('badge_user')->where('user_id', $student->id)->value('seen_at'));

        $this->actingAs($student)->get(route('student.dashboard'))
            ->assertOk()
            ->assertDontSee('rs-swal-badge', false);
    }

    public function test_badge_modal_is_registered_before_the_toast_queue_starts(): void
    {
        $student = $this->makeStudent();
        $this->giveBadge($student);

        $html = $this->actingAs($student)
            ->withSession(['success' => 'Berhasil disimpan.'])
            ->get(route('student.dashboard'))
            ->assertOk()
            ->getContent();

        $gate = strpos($html, 'window.rsModalGate =');
        $queue = strpos($html, 'rsFlashQueue');

        $this->assertNotFalse($gate);
        $this->assertNotFalse($queue);
        $this->assertLessThan($queue, $gate);
        $this->assertStringContainsString('window.rsModalGate || Promise.resolve()', $html);
    }

    public function test_badge_title_is_never_printed_as_raw_html(): void
    {
        $student = $this->makeStudent();
        $this->giveBadge($student, ['title' => '<img src=x onerror=alert(1)>']);

        $this->actingAs($student)->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('rs-swal-badge', false)
            ->assertDontSee('<img src=x onerror=alert(1)>', false);
    }
}
