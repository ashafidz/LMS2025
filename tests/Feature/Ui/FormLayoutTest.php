<?php

namespace Tests\Feature\Ui;

use App\Models\QuestionTopic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

class FormLayoutTest extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    private function css(): string
    {
        return file_get_contents(public_path('css/ruang-studi-dashboard.css'));
    }

    public function test_course_create_form_sits_in_the_centred_column(): void
    {
        $this->actingAs($this->makeInstructor())->withSession(['active_role' => 'instructor'])
            ->get(route('instructor.courses.create'))
            ->assertOk()
            ->assertSee('rs-page-form', false)
            ->assertSee('rs-form', false)
            ->assertDontSee('<h3>Informasi Kursus</h3>', false);
    }

    public function test_coupon_create_form_sits_in_the_centred_column(): void
    {
        $this->actingAs($this->makeAdmin())->withSession(['active_role' => 'admin'])
            ->get(route('admin.coupons.create'))
            ->assertOk()
            ->assertSee('rs-page-form', false)
            ->assertSee('rs-form', false);
    }

    public function test_quiz_lesson_create_form_sits_in_the_centred_column(): void
    {
        $instructor = $this->makeInstructor();
        $module = $this->makeModule($this->makeCourse($instructor));

        $this->actingAs($instructor)->withSession(['active_role' => 'instructor'])
            ->get(route('instructor.modules.lessons.create', [$module, 'type' => 'quiz']))
            ->assertOk()
            ->assertSee('rs-page-form', false)
            ->assertSee('rs-form', false);
    }

    public function test_question_bank_forms_use_the_stacked_layout(): void
    {
        $instructor = $this->makeInstructor();
        $topic = QuestionTopic::create(['instructor_id' => $instructor->id, 'name' => 'T', 'available_for_all_courses' => true]);

        foreach (['multiple_choice_single', 'multiple_choice_multiple', 'true_false', 'drag_and_drop'] as $type) {
            $this->actingAs($instructor)->withSession(['active_role' => 'instructor'])
                ->get(route('instructor.question-bank.questions.create', [$topic, 'type' => $type]))
                ->assertOk()
                ->assertSee('rs-page-form', false)
                ->assertSee('rs-form rs-form--bank', false);
        }
    }

    public function test_multiple_choice_correct_pick_has_its_own_text(): void
    {
        $instructor = $this->makeInstructor();
        $topic = QuestionTopic::create(['instructor_id' => $instructor->id, 'name' => 'T', 'available_for_all_courses' => true]);

        $this->actingAs($instructor)->withSession(['active_role' => 'instructor'])
            ->get(route('instructor.question-bank.questions.create', [$topic, 'type' => 'multiple_choice_multiple']))
            ->assertOk()
            ->assertSee('rs-correct-pick', false)
            ->assertSee('Jawaban benar', false)
            ->assertSee('name="options[${optionIndex}][is_correct]"', false);
    }

    public function test_css_has_the_centred_column_and_no_old_card_limit(): void
    {
        $css = $this->css();

        $this->assertStringContainsString('.rs-page-form', $css);
        $this->assertStringNotContainsString('.rs-form-card { max-width: 860px; }', $css);
        $this->assertStringNotContainsString('max-width: 760px', $css);
        $this->assertStringNotContainsString('rs-form--wide', $css);
    }

    public function test_css_has_the_laptop_scale_and_switch_fix(): void
    {
        $css = $this->css();

        $this->assertStringContainsString('@media (min-width: 768px) and (max-width: 1439.98px)', $css);
        $this->assertStringNotContainsString('.form-check:not(.form-switch)', $css);
        $this->assertLessThan(strpos($css, '@media (max-width: 767.98px)'), strpos($css, '@media (min-width: 768px) and (max-width: 1439.98px)'));
    }
}
