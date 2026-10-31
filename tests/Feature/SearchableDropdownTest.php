<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\QuestionTopic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchableDropdownTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shell_loads_select2_assets(): void
    {
        $instructor = $this->makeInstructor();

        $this->actingAs($instructor)
            ->withSession(['active_role' => 'instructor'])
            ->get(route('instructor.dashboard'))
            ->assertOk()
            ->assertSee('vendor/select2/select2.min.css')
            ->assertSee('vendor/select2/select2.min.js')
            ->assertSee('vendor/select2/id.js');
    }

    public function test_instructor_question_bank_topics_has_course_filter_select(): void
    {
        $instructor = $this->makeInstructor();
        $course = Course::factory()->create(['instructor_id' => $instructor->id]);
        QuestionTopic::create([
            'instructor_id' => $instructor->id,
            'name' => 'Topik Uji',
            'available_for_all_courses' => true,
        ]);

        $this->actingAs($instructor)
            ->withSession(['active_role' => 'instructor'])
            ->get(route('instructor.question-bank.topics.index'))
            ->assertOk()
            ->assertSee('id="filter_course"', false)
            ->assertSee('name="filter_course"', false)
            ->assertSee('class="form-control"', false)
            ->assertSee($course->title);
    }
}
