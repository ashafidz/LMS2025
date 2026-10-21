<?php

namespace Tests\Feature\Authorization;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionTopic;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstructorOwnershipTest extends TestCase
{
    use RefreshDatabase;

    protected User $instructorA;

    protected User $instructorB;

    protected Course $course;

    protected Module $module;

    protected Lesson $lesson;

    protected Quiz $quiz;

    protected QuestionTopic $topicA;

    protected Question $questionA;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();

        $this->instructorA = $this->makeInstructor();
        $this->instructorB = $this->makeInstructor();

        $category = CourseCategory::factory()->create();

        $this->course = Course::factory()->create([
            'instructor_id' => $this->instructorA->id,
            'category_id' => $category->id,
            'title' => 'Course by A',
        ]);

        $this->module = Module::factory()->create([
            'course_id' => $this->course->id,
            'title' => 'Module 1',
        ]);

        $this->quiz = Quiz::factory()->create([
            'title' => 'Quiz 1',
        ]);

        $this->lesson = Lesson::factory()->create([
            'module_id' => $this->module->id,
            'title' => 'Lesson Quiz 1',
            'lessonable_id' => $this->quiz->id,
            'lessonable_type' => Quiz::class,
        ]);

        $this->topicA = QuestionTopic::create([
            'instructor_id' => $this->instructorA->id,
            'name' => 'Topic A',
            'available_for_all_courses' => true,
        ]);

        $this->questionA = Question::create([
            'topic_id' => $this->topicA->id,
            'question_text' => 'What is 2+2?',
            'score' => 10,
            'question_type' => 'multiple_choice_single',
        ]);

        QuestionOption::create([
            'question_id' => $this->questionA->id,
            'option_text' => '4',
            'is_correct' => true,
        ]);
        QuestionOption::create([
            'question_id' => $this->questionA->id,
            'option_text' => '5',
            'is_correct' => false,
        ]);
    }

    public function test_instructor_b_cannot_update_or_delete_instructor_a_module(): void
    {
        $this->actingAs($this->instructorB)
            ->withSession(['active_role' => 'instructor']);

        $responseUpdate = $this->put(route('instructor.modules.update', $this->module), [
            'title' => 'Hacked Module Title',
        ]);
        $responseUpdate->assertStatus(403);

        $responseDestroy = $this->delete(route('instructor.modules.destroy', $this->module));
        $responseDestroy->assertStatus(403);
    }

    public function test_instructor_a_can_update_and_delete_own_module(): void
    {
        $this->actingAs($this->instructorA)
            ->withSession(['active_role' => 'instructor']);

        $responseUpdate = $this->put(route('instructor.modules.update', $this->module), [
            'title' => 'Updated Module Title',
            'points_required' => 10,
        ]);
        $responseUpdate->assertStatus(302);
        $this->assertDatabaseHas('modules', [
            'id' => $this->module->id,
            'title' => 'Updated Module Title',
            'points_required' => 10,
        ]);

        $responseDestroy = $this->delete(route('instructor.modules.destroy', $this->module));
        $responseDestroy->assertStatus(302);
        $this->assertDatabaseMissing('modules', [
            'id' => $this->module->id,
        ]);
    }

    public function test_instructor_b_cannot_update_delete_or_toggle_polling_on_instructor_a_lesson(): void
    {
        $this->actingAs($this->instructorB)
            ->withSession(['active_role' => 'instructor']);

        $responseUpdate = $this->put(route('instructor.lessons.update', $this->lesson), [
            'title' => 'Hacked Lesson Title',
        ]);
        $responseUpdate->assertStatus(403);

        $responseToggle = $this->patch(route('instructor.lessons.polling.toggle_status', $this->lesson));
        $responseToggle->assertStatus(403);

        $responseDestroy = $this->delete(route('instructor.lessons.destroy', $this->lesson));
        $responseDestroy->assertStatus(403);
    }

    public function test_instructor_b_cannot_attach_or_detach_questions_on_instructor_a_quiz(): void
    {
        $this->actingAs($this->instructorB)
            ->withSession(['active_role' => 'instructor']);

        $responseAttach = $this->post(route('instructor.quizzes.attach_questions', $this->quiz), [
            'question_ids' => [$this->questionA->id],
        ]);
        $responseAttach->assertStatus(403);

        // Pre-attach by A
        $this->quiz->questions()->attach($this->questionA->id);

        $responseDetach = $this->delete(route('instructor.quizzes.detach_question', [
            'quiz' => $this->quiz->id,
            'question' => $this->questionA->id,
        ]));
        $responseDetach->assertStatus(403);
    }

    public function test_instructor_a_can_attach_and_detach_own_questions(): void
    {
        $this->actingAs($this->instructorA)
            ->withSession(['active_role' => 'instructor']);

        $responseAttach = $this->post(route('instructor.quizzes.attach_questions', $this->quiz), [
            'question_ids' => [$this->questionA->id],
        ]);
        $responseAttach->assertStatus(302);
        $this->assertDatabaseHas('quiz_question', [
            'quiz_id' => $this->quiz->id,
            'question_id' => $this->questionA->id,
        ]);

        $responseDetach = $this->delete(route('instructor.quizzes.detach_question', [
            'quiz' => $this->quiz->id,
            'question' => $this->questionA->id,
        ]));
        $responseDetach->assertStatus(302);
        $this->assertDatabaseMissing('quiz_question', [
            'quiz_id' => $this->quiz->id,
            'question_id' => $this->questionA->id,
        ]);
    }

    public function test_instructor_b_cannot_award_lesson_points_on_instructor_a_course(): void
    {
        $student = $this->makeStudent();
        $this->enroll($student, $this->course);

        $this->actingAs($this->instructorB)
            ->withSession(['active_role' => 'instructor']);

        $response = $this->post(route('instructor.lesson_points.award', $this->lesson), [
            'student_id' => $student->id,
            'points' => 50,
        ]);
        $response->assertStatus(403);
    }

    public function test_instructor_b_cannot_access_or_modify_instructor_a_course(): void
    {
        $this->actingAs($this->instructorB)
            ->withSession(['active_role' => 'instructor']);

        $this->get(route('instructor.courses.show', $this->course))->assertStatus(403);
        $this->get(route('instructor.courses.edit', $this->course))->assertStatus(403);
        $this->put(route('instructor.courses.update', $this->course), [
            'title' => 'Malicious update',
            'category_id' => $this->course->category_id,
            'availability_type' => 'lifetime',
            'payment_type' => 'money',
        ])->assertStatus(403);
        $this->delete(route('instructor.courses.destroy', $this->course))->assertStatus(403);
    }
}
