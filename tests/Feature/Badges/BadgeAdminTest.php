<?php

namespace Tests\Feature\Badges;

use App\Jobs\BackfillBadge;
use App\Models\Badge;
use App\Models\CourseCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsBadgeFixtures;
use Tests\TestCase;

class BadgeAdminTest extends TestCase
{
    use BuildsBadgeFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public static function roles(): array
    {
        return [['admin'], ['superadmin']];
    }

    private function as(string $role)
    {
        $user = $role === 'admin' ? $this->makeAdmin() : $this->makeSuperadmin();

        return $this->actingAs($user)->withSession(['active_role' => $role]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Jago Kuis',
            'description' => 'Lulus banyak kuis',
            'icon' => UploadedFile::fake()->image('ikon.png', 100, 100),
            'is_active' => 1,
            'rules' => [
                ['metric' => 'quiz_passed', 'target' => 10, 'scope' => 'all', 'scope_id' => null],
            ],
        ], $overrides);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('roles')]
    public function test_manager_can_create_badge_with_rules_and_icon(string $role): void
    {
        $this->as($role)->post(route("$role.badges.store"), $this->payload([
            'rules' => [
                ['metric' => 'quiz_passed', 'target' => 10, 'scope' => 'all', 'scope_id' => ''],
                ['metric' => 'reviews_written', 'target' => 2, 'scope' => 'all', 'scope_id' => ''],
            ],
        ]))->assertRedirect(route("$role.badges.index"))->assertSessionHasNoErrors();

        $badge = Badge::firstOrFail();
        $this->assertSame('Jago Kuis', $badge->title);
        $this->assertTrue($badge->is_active);
        $this->assertCount(2, $badge->rules);
        $this->assertSame(['quiz_passed', 'reviews_written'], $badge->rules->pluck('metric')->all());
        $this->assertSame([0, 1], $badge->rules->pluck('position')->all());
        Storage::disk('public')->assertExists($badge->icon_path);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('roles')]
    public function test_pages_render_for_manager_and_show_plain_language_summary(string $role): void
    {
        $badge = $this->makeBadge([['metric' => 'quiz_passed', 'target' => 10]], ['title' => 'Jago Kuis']);
        $student = $this->makeStudent();
        $badge->users()->attach($student->id, ['created_at' => now()]);

        $this->as($role)->get(route("$role.badges.index"))
            ->assertOk()
            ->assertSee('Jago Kuis')
            ->assertSee('Lulus kuis minimal 10 kuis di semua kursus')
            ->assertDontSee('quiz_passed');

        $this->as($role)->get(route("$role.badges.create"))->assertOk()->assertSee('Syarat mendapatkan badge');
        $this->as($role)->get(route("$role.badges.edit", $badge))->assertOk()->assertSee('Jago Kuis');
    }

    public function test_instructor_and_student_cannot_manage_badges(): void
    {
        $badge = $this->makeBadge([['metric' => 'quiz_passed', 'target' => 1]]);

        foreach ([$this->makeInstructor(), $this->makeStudent()] as $user) {
            $this->actingAs($user);
            $this->get('/admin/badges')->assertForbidden();
            $this->get('/superadmin/badges')->assertForbidden();
            $this->post('/admin/badges', [])->assertForbidden();
            $this->post('/superadmin/badges', [])->assertForbidden();
            $this->delete('/admin/badges/'.$badge->getRouteKey())->assertForbidden();
        }

        $this->assertDatabaseCount('badges', 1);
    }

    public function test_svg_and_oversized_icons_are_rejected(): void
    {
        $svg = UploadedFile::fake()->createWithContent('x.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');
        $this->as('admin')->post(route('admin.badges.store'), $this->payload(['icon' => $svg]))->assertSessionHasErrors('icon');

        $big = UploadedFile::fake()->image('besar.png', 100, 100)->size(1500);
        $this->as('admin')->post(route('admin.badges.store'), $this->payload(['icon' => $big]))->assertSessionHasErrors('icon');

        $this->assertDatabaseCount('badges', 0);
    }

    public function test_rules_are_validated_with_plain_messages(): void
    {
        $category = CourseCategory::factory()->create();
        $cases = [
            'tanpa syarat' => [[], 'rules'],
            'lebih dari 3' => [array_fill(0, 4, ['metric' => 'quiz_passed', 'target' => 1, 'scope' => 'all']), 'rules'],
            'ukuran tidak dikenal' => [[['metric' => 'hack', 'target' => 1, 'scope' => 'all']], 'rules.0.metric'],
            'target nol' => [[['metric' => 'quiz_passed', 'target' => 0, 'scope' => 'all']], 'rules.0.target'],
            'target terlalu besar' => [[['metric' => 'quiz_passed', 'target' => 100001, 'scope' => 'all']], 'rules.0.target'],
            'cakupan tidak didukung' => [[['metric' => 'certificates_earned', 'target' => 1, 'scope' => 'course', 'scope_id' => 1]], 'rules.0.scope'],
            'cakupan tanpa pilihan' => [[['metric' => 'quiz_passed', 'target' => 1, 'scope' => 'course', 'scope_id' => '']], 'rules.0.scope_id'],
            'kursus tidak ada' => [[['metric' => 'quiz_passed', 'target' => 1, 'scope' => 'course', 'scope_id' => 99999]], 'rules.0.scope_id'],
            'kategori tidak ada' => [[['metric' => 'quiz_passed', 'target' => 1, 'scope' => 'category', 'scope_id' => 99999]], 'rules.0.scope_id'],
        ];

        foreach ($cases as $name => [$rules, $errorKey]) {
            $this->as('admin')->post(route('admin.badges.store'), $this->payload(['rules' => $rules]))
                ->assertSessionHasErrors($errorKey, "kasus: $name");
        }

        $this->as('admin')->post(route('admin.badges.store'), $this->payload([
            'rules' => [['metric' => 'quiz_passed', 'target' => 1, 'scope' => 'category', 'scope_id' => $category->id]],
        ]))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('badges', 1);
    }

    public function test_unlocked_badge_rules_are_replaced_on_update(): void
    {
        $badge = $this->makeBadge([['metric' => 'quiz_passed', 'target' => 10]]);

        $this->as('admin')->put(route('admin.badges.update', $badge), [
            'title' => 'Baru',
            'description' => 'Deskripsi baru',
            'is_active' => 1,
            'rules' => [['metric' => 'lessons_completed', 'target' => 5, 'scope' => 'all', 'scope_id' => '']],
        ])->assertRedirect(route('admin.badges.index'))->assertSessionHasNoErrors();

        $badge->refresh()->load('rules');
        $this->assertSame('Badge berhasil diperbarui.', session('success'));
        $this->assertSame('Baru', $badge->title);
        $this->assertSame(['lessons_completed'], $badge->rules->pluck('metric')->all());
        $this->assertSame(5, $badge->rules->first()->target);
    }

    public function test_locked_badge_keeps_rules_but_allows_other_changes(): void
    {
        $badge = $this->makeBadge([['metric' => 'quiz_passed', 'target' => 10]]);
        $badge->users()->attach($this->makeStudent()->id, ['created_at' => now()]);

        $this->as('admin')->get(route('admin.badges.edit', $badge))
            ->assertOk()->assertSee('Syarat dikunci')->assertSee('Duplikat');

        $this->as('admin')->put(route('admin.badges.update', $badge), [
            'title' => 'Judul Baru',
            'description' => 'Deskripsi baru',
            'is_active' => 0,
            'rules' => [['metric' => 'lessons_completed', 'target' => 1, 'scope' => 'all', 'scope_id' => '']],
        ])->assertRedirect(route('admin.badges.index'))->assertSessionHasNoErrors();

        $badge->refresh()->load('rules');
        $this->assertSame('Badge diperbarui. Syarat tidak diubah karena badge sudah dimiliki siswa.', session('warning'));
        $this->assertNull(session('success'));
        $this->as('admin')->get(route('admin.badges.index'))
            ->assertSee('<div class="alert alert-warning">Badge diperbarui. Syarat tidak diubah karena badge sudah dimiliki siswa.</div>', false);
        $this->assertSame('Judul Baru', $badge->title);
        $this->assertFalse($badge->is_active);
        $this->assertSame(['quiz_passed'], $badge->rules->pluck('metric')->all());
        $this->assertSame(10, $badge->rules->first()->target);
    }

    public function test_locked_badge_can_be_updated_without_sending_rules(): void
    {
        $badge = $this->makeBadge([['metric' => 'quiz_passed', 'target' => 10]]);
        $badge->users()->attach($this->makeStudent()->id, ['created_at' => now()]);

        $this->as('admin')->put(route('admin.badges.update', $badge), [
            'title' => 'Hanya judul',
            'description' => 'x',
            'is_active' => 1,
        ])->assertSessionHasNoErrors();

        $this->assertSame('Badge berhasil diperbarui.', session('success'));
        $this->assertSame('Hanya judul', $badge->fresh()->title);
        $this->assertCount(1, $badge->fresh()->rules);
    }

    public function test_update_can_replace_the_icon_and_removes_the_old_file(): void
    {
        Storage::disk('public')->put('badge-icons/lama.png', 'x');
        $badge = $this->makeBadge([['metric' => 'quiz_passed', 'target' => 1]], ['icon_path' => 'badge-icons/lama.png']);

        $this->as('admin')->put(route('admin.badges.update', $badge), [
            'title' => 'T', 'description' => 'D', 'is_active' => 1,
            'icon' => UploadedFile::fake()->image('baru.png', 50, 50),
            'rules' => [['metric' => 'quiz_passed', 'target' => 1, 'scope' => 'all', 'scope_id' => '']],
        ])->assertSessionHasNoErrors();

        Storage::disk('public')->assertMissing('badge-icons/lama.png');
        Storage::disk('public')->assertExists($badge->fresh()->icon_path);
    }

    public function test_duplicate_copies_rules_and_icon_but_not_owners(): void
    {
        Storage::disk('public')->put('badge-icons/asal.png', 'ikon');
        $badge = $this->makeBadge([
            ['metric' => 'quiz_passed', 'target' => 10],
            ['metric' => 'reviews_written', 'target' => 2],
        ], ['title' => 'Jago Kuis', 'icon_path' => 'badge-icons/asal.png']);
        $badge->users()->attach($this->makeStudent()->id, ['created_at' => now()]);

        $this->as('admin')->post(route('admin.badges.duplicate', $badge))->assertRedirect();

        $copy = Badge::where('id', '!=', $badge->id)->firstOrFail()->load('rules');
        $this->assertSame('Salinan dari Jago Kuis', $copy->title);
        $this->assertFalse($copy->is_active);
        $this->assertSame(['quiz_passed', 'reviews_written'], $copy->rules->pluck('metric')->all());
        $this->assertSame(0, $copy->users()->count());
        $this->assertNotSame($badge->icon_path, $copy->icon_path);
        Storage::disk('public')->assertExists($copy->icon_path);
        Storage::disk('public')->assertExists('badge-icons/asal.png');
        $this->as('admin')->get(route('admin.badges.edit', $copy))->assertOk()->assertDontSee('Syarat dikunci');
    }

    public function test_preview_count_reports_qualifying_students(): void
    {
        [, $module] = $this->makeCourseWithModule();
        $a = $this->makeStudent();
        $b = $this->makeStudent();
        $this->makeStudent();
        $this->passQuiz($a, $this->makeQuizIn($module));
        $this->passQuiz($b, $this->makeQuizIn($module));

        $this->as('admin')->postJson(route('admin.badges.preview-count'), [
            'rules' => [['metric' => 'quiz_passed', 'target' => 1, 'scope' => 'all', 'scope_id' => '']],
        ])->assertOk()->assertJson(['count' => 2, 'capped' => false, 'total_students' => 3]);

        $this->as('admin')->postJson(route('admin.badges.preview-count'), [
            'rules' => [['metric' => 'hack', 'target' => 1, 'scope' => 'all']],
        ])->assertStatus(422);
    }

    public function test_backfill_dispatches_a_job_and_grants_to_qualifying_students(): void
    {
        $student = $this->makeStudent();
        [, $module] = $this->makeCourseWithModule();
        $this->passQuiz($student, $this->makeQuizIn($module));
        $badge = $this->makeBadge([['metric' => 'quiz_passed', 'target' => 1]]);

        Bus::fake();
        $this->as('admin')->post(route('admin.badges.backfill', $badge))
            ->assertRedirect()->assertSessionHas('success');
        Bus::assertDispatched(BackfillBadge::class, fn ($job) => $job->badgeId === $badge->id);

        (new BackfillBadge($badge->id))->handle();
        $this->assertDatabaseHas('badge_user', ['user_id' => $student->id, 'badge_id' => $badge->id]);
    }

    public function test_backfill_refuses_inactive_or_rule_less_badge(): void
    {
        Bus::fake();
        $inactive = $this->makeBadge([['metric' => 'quiz_passed', 'target' => 1]], ['is_active' => false]);
        $empty = $this->makeBadge([]);

        $this->as('admin')->post(route('admin.badges.backfill', $inactive))->assertSessionHas('error');
        $this->as('admin')->post(route('admin.badges.backfill', $empty))->assertSessionHas('error');

        Bus::assertNotDispatched(BackfillBadge::class);
    }

    public function test_destroy_removes_badge_and_icon(): void
    {
        Storage::disk('public')->put('badge-icons/hapus.png', 'x');
        $badge = $this->makeBadge([['metric' => 'quiz_passed', 'target' => 1]], ['icon_path' => 'badge-icons/hapus.png']);

        $this->as('admin')->delete(route('admin.badges.destroy', $badge))->assertRedirect();

        $this->assertDatabaseMissing('badges', ['id' => $badge->id]);
        $this->assertDatabaseMissing('badge_rules', ['badge_id' => $badge->id]);
        Storage::disk('public')->assertMissing('badge-icons/hapus.png');
    }

    public function test_equipped_badge_is_cleared_when_badge_is_deleted(): void
    {
        $student = $this->makeStudent();
        $badge = $this->makeBadge([['metric' => 'quiz_passed', 'target' => 1]]);
        $badge->users()->attach($student->id, ['created_at' => now()]);
        $student->update(['equipped_badge_id' => $badge->id]);

        $this->as('superadmin')->delete(route('superadmin.badges.destroy', $badge));

        $this->assertNull($student->fresh()->equipped_badge_id);
    }
}
