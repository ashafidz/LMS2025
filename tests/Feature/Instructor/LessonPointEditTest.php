<?php

namespace Tests\Feature\Instructor;

use App\Models\Lesson;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

/**
 * The edit view is picked from the class name ("lessonpoint"), but the file was named edit-lessonpoin, so opening the
 * edit form of a "Poin" lesson failed with HTTP 500 (View not found).
 */
class LessonPointEditTest extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    public function test_the_edit_form_of_a_point_lesson_opens(): void
    {
        $owner = $this->makeInstructor();
        $module = $this->makeModule($this->makeCourse($owner));

        $this->actingAs($owner)->withSession(['active_role' => 'instructor'])
            ->post(route('instructor.modules.lessons.store', $module), [
                'title' => 'Poin bab 1',
                'lesson_type' => 'lessonpoin',
                'lessonpoin_title' => 'Kumpulkan poin',
                'lessonpoin_description' => 'Selesaikan untuk mendapat poin.',
            ])->assertSessionHasNoErrors();

        $this->actingAs($owner)->withSession(['active_role' => 'instructor'])
            ->get(route('instructor.lessons.edit', Lesson::firstOrFail()))
            ->assertOk()
            ->assertSee('Kumpulkan poin');
    }
}
