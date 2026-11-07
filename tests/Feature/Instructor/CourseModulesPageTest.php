<?php

namespace Tests\Feature\Instructor;

use App\Models\Module;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

class CourseModulesPageTest extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    private function as($user)
    {
        return $this->actingAs($user)->withSession(['active_role' => 'instructor']);
    }

    private function listUrl($course): string
    {
        return route('instructor.courses.index', ['open' => $course->getRouteKey()]).'#course-'.$course->getRouteKey();
    }

    public function test_old_module_url_redirects_to_the_course_list_with_the_course_open(): void
    {
        $instructor = $this->makeInstructor();
        $course = $this->makeCourse($instructor);

        $this->as($instructor)
            ->get(route('instructor.courses.modules.index', $course))
            ->assertRedirect($this->listUrl($course));
    }

    public function test_another_instructor_still_gets_403_on_the_old_url(): void
    {
        $course = $this->makeCourse($this->makeInstructor());

        $this->as($this->makeInstructor())
            ->get(route('instructor.courses.modules.index', $course))
            ->assertForbidden();
    }

    public function test_store_update_and_destroy_redirect_to_the_open_course_with_the_flash(): void
    {
        $instructor = $this->makeInstructor();
        $course = $this->makeCourse($instructor);

        $this->as($instructor)
            ->post(route('instructor.courses.modules.store', $course), ['title' => 'Modul Baru', 'points_required' => 0])
            ->assertRedirect($this->listUrl($course))
            ->assertSessionHas('success', 'Modul berhasil dibuat.');

        $module = Module::where('course_id', $course->id)->firstOrFail();

        $this->as($instructor)
            ->put(route('instructor.modules.update', $module), ['title' => 'Modul Diubah', 'points_required' => 0])
            ->assertRedirect($this->listUrl($course))
            ->assertSessionHas('success', 'Modul berhasil diperbarui.');

        $this->as($instructor)
            ->delete(route('instructor.modules.destroy', $module))
            ->assertRedirect($this->listUrl($course))
            ->assertSessionHas('success', 'Modul berhasil dihapus.');

        $this->assertDatabaseMissing('modules', ['id' => $module->id]);
    }

    public function test_course_list_shows_each_courses_modules_with_lesson_counts_and_manage_links(): void
    {
        $instructor = $this->makeInstructor();
        $course = $this->makeCourse($instructor, ['title' => 'Kursus Berisi Modul']);
        $withLessons = $this->makeModule($course);
        $withLessons->update(['title' => 'Modul Dua Materi']);
        $this->makeArticleLesson($withLessons);
        $this->makeArticleLesson($withLessons);
        $empty = $this->makeModule($course);
        $empty->update(['title' => 'Modul Kosong']);

        $this->as($instructor)
            ->get(route('instructor.courses.index', ['open' => $course->getRouteKey()]))
            ->assertOk()
            ->assertSee('id="course-'.$course->getRouteKey().'"', false)
            ->assertSee('data-status="published"', false)
            ->assertSee('data-title="kursus berisi modul"', false)
            ->assertSee('Modul Dua Materi')
            ->assertSee('2 materi')
            ->assertSee('Belum ada materi')
            ->assertSee(route('instructor.modules.lessons.index', $withLessons), false)
            ->assertSee(route('instructor.modules.edit', $withLessons), false)
            ->assertSee(route('instructor.module.leaderboard', $withLessons), false)
            ->assertSee('data-reorder-url="'.route('instructor.courses.modules.reorder', $course).'"', false)
            ->assertSee(route('instructor.courses.modules.create', $course), false)
            ->assertSee('Modul kursus ini');
    }

    private function panelIsHidden(string $html, $course): bool
    {
        $this->assertMatchesRegularExpression('/<div class="rs-mods" id="mods-'.$course->getRouteKey().'"[^>]*>/', $html);
        preg_match('/<div class="rs-mods" id="mods-'.$course->getRouteKey().'"[^>]*>/', $html, $m);

        return (bool) preg_match('/\bhidden\b/', $m[0]);
    }

    public function test_only_the_requested_course_panel_starts_open_and_a_single_course_opens_itself(): void
    {
        $instructor = $this->makeInstructor();
        $a = $this->makeCourse($instructor);
        $b = $this->makeCourse($instructor);

        $html = $this->as($instructor)->get(route('instructor.courses.index', ['open' => $a->getRouteKey()]))->getContent();
        $this->assertFalse($this->panelIsHidden($html, $a));
        $this->assertTrue($this->panelIsHidden($html, $b));

        $solo = $this->makeInstructor();
        $only = $this->makeCourse($solo);
        $html = $this->as($solo)->get(route('instructor.courses.index'))->getContent();
        $this->assertFalse($this->panelIsHidden($html, $only));
    }

    public function test_course_list_query_count_is_the_same_for_one_and_ten_courses(): void
    {
        $instructor = $this->makeInstructor();
        $count = function () use ($instructor) {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->as($instructor)->get(route('instructor.courses.index'))->assertOk();

            return count(DB::getQueryLog());
        };
        $addCourse = function () use ($instructor) {
            $course = $this->makeCourse($instructor);
            foreach (range(1, 3) as $i) {
                $this->makeArticleLesson($this->makeModule($course));
            }
            $this->enroll($this->makeStudent(), $course);
        };

        $addCourse();
        $count(); // warm-up
        $one = $count();
        foreach (range(1, 9) as $i) {
            $addCourse();
        }

        $this->assertSame($one, $count());
    }

    public function test_delete_course_form_asks_with_the_site_dialog_in_danger_style(): void
    {
        $instructor = $this->makeInstructor();
        $course = $this->makeCourse($instructor);

        $this->as($instructor)
            ->get(route('instructor.courses.index'))
            ->assertOk()
            ->assertSee('data-confirm-danger', false)
            ->assertDontSee('onsubmit="return confirm', false);
    }
}
