<?php

namespace Tests\Feature\Instructor;

use App\Models\Lesson;
use App\Models\Quiz;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

/**
 * The form allows an empty time limit ("Kosongkan jika tidak ada batas waktu"); saving it used to fail with
 * "Column 'time_limit' cannot be null" (HTTP 500).
 */
class QuizWithoutTimeLimitTest extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    private function quizFields(array $overrides = []): array
    {
        return $overrides + [
            'quiz_title' => 'Kuis Bab 1',
            'quiz_description' => null,
            'pass_mark' => 75,
            'time_limit' => '',
            'allow_exceed_time_limit' => 0,
            'reveal_answers' => 1,
            'max_attempts' => '',
            'available_from' => '',
            'available_to' => '',
        ];
    }

    public function test_a_quiz_lesson_can_be_created_without_a_time_limit(): void
    {
        $owner = $this->makeInstructor();
        $module = $this->makeModule($this->makeCourse($owner));

        $this->actingAs($owner)->withSession(['active_role' => 'instructor'])
            ->post(route('instructor.modules.lessons.store', $module), ['title' => 'Kuis', 'lesson_type' => 'quiz'] + $this->quizFields())
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertNull(Quiz::firstOrFail()->time_limit);
    }

    public function test_the_time_limit_can_be_removed_from_an_existing_quiz(): void
    {
        $owner = $this->makeInstructor();
        $module = $this->makeModule($this->makeCourse($owner));
        $this->actingAs($owner)->withSession(['active_role' => 'instructor'])
            ->post(route('instructor.modules.lessons.store', $module), ['title' => 'Kuis', 'lesson_type' => 'quiz'] + $this->quizFields(['time_limit' => 30]));
        $this->assertSame(30, Quiz::firstOrFail()->time_limit);

        $this->actingAs($owner)->withSession(['active_role' => 'instructor'])
            ->put(route('instructor.lessons.update', Lesson::firstOrFail()), ['title' => 'Kuis'] + $this->quizFields())
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertNull(Quiz::firstOrFail()->time_limit);
    }
}
