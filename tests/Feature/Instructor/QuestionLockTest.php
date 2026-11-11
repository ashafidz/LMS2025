<?php

namespace Tests\Feature\Instructor;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Question;
use App\Models\QuestionTopic;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A bank question is locked only while a quiz of a live course uses it.
 * Deleting a course is a soft delete, so its quiz_question rows remain and must not keep the lock.
 */
class QuestionLockTest extends TestCase
{
    use RefreshDatabase;

    private User $instructor;

    private QuestionTopic $topic;

    private Question $question;

    private Course $course;

    protected function setUp(): void
    {
        parent::setUp();
        $this->instructor = $this->makeInstructor();
        $this->topic = QuestionTopic::create([
            'instructor_id' => $this->instructor->id,
            'name' => 'Topik Uji',
            'available_for_all_courses' => true,
        ]);
        $this->question = Question::create([
            'topic_id' => $this->topic->id,
            'question_text' => 'Soal uji',
            'question_type' => 'multiple_choice_single',
            'score' => 10,
        ]);

        $this->course = Course::factory()->create(['instructor_id' => $this->instructor->id]);
        $module = Module::factory()->create(['course_id' => $this->course->id]);
        $quiz = Quiz::factory()->create();
        Lesson::factory()->forQuiz($quiz)->create(['module_id' => $module->id]);
        $quiz->questions()->attach($this->question->id);
    }

    private function asInstructor(): static
    {
        return $this->actingAs($this->instructor)->withSession(['active_role' => 'instructor']);
    }

    public function test_question_used_in_live_course_quiz_is_locked(): void
    {
        $this->assertTrue($this->question->isLocked());

        $this->asInstructor()->get(route('instructor.question-bank.questions.edit', $this->question))
            ->assertRedirect(route('instructor.question-bank.questions.index', $this->topic))
            ->assertSessionHas('error');

        $this->asInstructor()->get(route('instructor.question-bank.questions.index', $this->topic))
            ->assertOk()->assertSee('Locked');

        $this->asInstructor()->get(route('instructor.question-bank.topics.index'))
            ->assertOk()->assertSee('Terkunci (digunakan dalam kuis)');
    }

    public function test_question_is_editable_after_its_course_is_deleted(): void
    {
        $this->asInstructor()->delete(route('instructor.courses.destroy', $this->course))->assertRedirect();
        $this->assertSoftDeleted($this->course);

        $this->assertFalse($this->question->fresh()->isLocked());

        $this->asInstructor()->get(route('instructor.question-bank.questions.edit', $this->question))
            ->assertOk();

        $this->asInstructor()->get(route('instructor.question-bank.questions.index', $this->topic))
            ->assertOk()->assertSee('Editable')->assertDontSee('>Locked<', false);

        $this->asInstructor()->get(route('instructor.question-bank.topics.index'))
            ->assertOk()->assertDontSee('Terkunci (digunakan dalam kuis)');
    }

    public function test_question_can_be_moved_after_its_course_is_deleted(): void
    {
        $target = QuestionTopic::create([
            'instructor_id' => $this->instructor->id,
            'name' => 'Topik Tujuan',
            'available_for_all_courses' => true,
        ]);

        $this->asInstructor()->patch(route('instructor.question-bank.questions.move', $this->question), [
            'target_topic_id' => $target->id,
        ])->assertSessionHas('error');
        $this->assertSame($this->topic->id, $this->question->fresh()->topic_id);

        $this->course->delete();

        $this->asInstructor()->patch(route('instructor.question-bank.questions.move', $this->question), [
            'target_topic_id' => $target->id,
        ])->assertSessionHasNoErrors();
        $this->assertSame($target->id, $this->question->fresh()->topic_id);
    }

    public function test_locked_question_cannot_be_deleted_until_its_course_is_deleted(): void
    {
        $this->asInstructor()->delete(route('instructor.question-bank.questions.destroy', $this->question))
            ->assertSessionHas('error');
        $this->assertNotSoftDeleted($this->question);

        $this->course->delete();

        $this->asInstructor()->delete(route('instructor.question-bank.questions.destroy', $this->question))
            ->assertSessionHas('success');
        $this->assertSoftDeleted($this->question);
    }

    public function test_question_stays_locked_when_another_live_course_still_uses_it(): void
    {
        $otherCourse = Course::factory()->create(['instructor_id' => $this->instructor->id]);
        $module = Module::factory()->create(['course_id' => $otherCourse->id]);
        $quiz = Quiz::factory()->create();
        Lesson::factory()->forQuiz($quiz)->create(['module_id' => $module->id]);
        $quiz->questions()->attach($this->question->id);

        $this->course->delete();

        $this->assertTrue($this->question->fresh()->isLocked());
    }
}
