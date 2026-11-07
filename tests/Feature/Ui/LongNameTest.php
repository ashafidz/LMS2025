<?php

namespace Tests\Feature\Ui;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

/**
 * Long course, module and lesson names must stay readable: cut only by CSS, full name in the page and in `title`.
 */
class LongNameTest extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    public function test_learning_page_keeps_full_long_names_and_adds_title_attributes(): void
    {
        $instructor = $this->makeInstructor();
        $student = $this->makeStudent();
        $course = $this->makeCourse($instructor);
        $module = $this->makeModule($course);
        $longModule = 'Modul '.str_repeat('Panjang', 30);
        $longLesson = 'Pelajaran '.str_repeat('Sangatpanjang', 15);
        $module->update(['title' => $longModule]);
        $lesson = $this->makeArticleLesson($module);
        $lesson->update(['title' => $longLesson]);
        $this->enroll($student, $course);

        $response = $this->actingAs($student)->withSession(['active_role' => 'student'])
            ->get(route('student.courses.show', $course->slug))
            ->assertOk();

        $response->assertSee($longModule, false);
        $response->assertSee($longLesson, false);
        $response->assertSee('title="'.$longModule.'"', false);
        $response->assertSee('title="'.$longLesson.'"', false);
    }

    public function test_lesson_manager_title_is_short_and_module_name_stays_on_the_page(): void
    {
        $instructor = $this->makeInstructor();
        $course = $this->makeCourse($instructor);
        $module = $this->makeModule($course);
        $module->update(['title' => 'Modul Zat Padat Cair dan Gas']);

        $response = $this->actingAs($instructor)->withSession(['active_role' => 'instructor'])
            ->get(route('instructor.modules.lessons.index', $module))
            ->assertOk();

        $response->assertSee('Kelola Pelajaran');
        $response->assertDontSee('Kelola Pelajaran Modul');
        $response->assertSee('Modul Zat Padat Cair dan Gas');
    }

    public function test_public_and_dashboard_css_break_long_names(): void
    {
        $public = file_get_contents(public_path('css/ruang-studi.css'));
        $this->assertStringContainsString('.rs-lessons li > span:last-child { min-width: 0; overflow-wrap: anywhere; }', $public);
        $this->assertStringContainsString('.rs-modules summary > span { min-width: 0; overflow-wrap: anywhere; }', $public);

        $dash = file_get_contents(public_path('css/ruang-studi-dashboard.css'));
        $this->assertStringContainsString('.rs-dash .card .card-header h4, .rs-dash .card .card-header h5 { min-width: 0; overflow-wrap: anywhere; }', $dash);
        // `anywhere` on every table cell breaks short words mid-word in a crowded table.
        $this->assertStringNotContainsString('.rs-dash .table td:not(:has(', $dash);
        $this->assertDoesNotMatchRegularExpression('/\.rs-dash \.table th[^{]*\{[^}]*overflow-wrap: anywhere/', $dash);
    }
}
