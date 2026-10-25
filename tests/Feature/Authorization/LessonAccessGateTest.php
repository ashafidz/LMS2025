<?php

namespace Tests\Feature\Authorization;

use App\Models\Lesson;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionTopic;
use App\Models\Quiz;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

class LessonAccessGateTest extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    public function test_preview_query_does_not_unlock_locked_module_for_student(): void
    {
        $owner = $this->makeInstructor();
        $course = $this->makeCourse($owner);
        $lesson = $this->makeArticleLesson($this->makeModule($course, 100));
        $student = $this->makeStudent();
        $this->enroll($student, $course);

        $response = $this->actingAs($student)->getJson(route('student.lessons.content', $lesson).'?preview=true');

        $response->assertOk()->assertJson(['is_locked' => true]);
    }

    public function test_student_cannot_complete_lesson_in_locked_module(): void
    {
        $course = $this->makeCourse($this->makeInstructor());
        $lesson = $this->makeArticleLesson($this->makeModule($course, 100));
        $student = $this->makeStudent();
        $this->enroll($student, $course);

        $this->actingAs($student)->postJson(route('student.lessons.complete', $lesson))->assertForbidden();

        $this->assertDatabaseMissing('lesson_user', ['user_id' => $student->id, 'lesson_id' => $lesson->id]);
    }

    public function test_other_instructor_cannot_read_lesson_content_of_someone_elses_course(): void
    {
        $course = $this->makeCourse($this->makeInstructor());
        $lesson = $this->makeArticleLesson($this->makeModule($course));
        $otherInstructor = $this->makeInstructor();

        $this->actingAs($otherInstructor)->getJson(route('student.lessons.content', $lesson))->assertForbidden();
    }

    public function test_other_instructor_gets_404_on_preview_of_unpublished_course(): void
    {
        $course = $this->makeCourse($this->makeInstructor(), ['status' => 'draft']);
        $otherInstructor = $this->makeInstructor();

        $this->actingAs($otherInstructor)
            ->get(route('student.courses.show', ['course' => $course->slug, 'preview' => 'true']))
            ->assertNotFound();
    }

    public function test_owner_can_preview_draft_course_and_open_locked_module(): void
    {
        $owner = $this->makeInstructor();
        $course = $this->makeCourse($owner, ['status' => 'draft']);
        $lesson = $this->makeArticleLesson($this->makeModule($course, 100));

        $this->actingAs($owner)
            ->get(route('student.courses.show', ['course' => $course->slug, 'preview' => 'true']))
            ->assertOk();

        $response = $this->actingAs($owner)->getJson(route('student.lessons.content', $lesson).'?preview=true');

        $response->assertOk()->assertJsonMissing(['is_locked' => true]);
    }

    public function test_student_cannot_begin_quiz_in_locked_module(): void
    {
        $course = $this->makeCourse($this->makeInstructor());
        $module = $this->makeModule($course, 100);
        $quiz = Quiz::factory()->create();
        $topic = QuestionTopic::create([
            'instructor_id' => $course->instructor_id,
            'name' => 'Topik',
            'available_for_all_courses' => true,
        ]);
        $question = Question::create([
            'topic_id' => $topic->id,
            'question_text' => '2 + 2 = ?',
            'score' => 100,
            'question_type' => 'multiple_choice_single',
        ]);
        QuestionOption::create(['question_id' => $question->id, 'option_text' => '4', 'is_correct' => true]);
        QuestionOption::create(['question_id' => $question->id, 'option_text' => '5', 'is_correct' => false]);
        $quiz->questions()->attach($question->id, ['order' => 1]);
        Lesson::factory()->create([
            'module_id' => $module->id,
            'lessonable_id' => $quiz->id,
            'lessonable_type' => Quiz::class,
        ]);
        $student = $this->makeStudent();
        $this->enroll($student, $course);

        $this->actingAs($student)->post(route('student.quiz.begin', $quiz))->assertRedirect();

        $this->assertDatabaseMissing('quiz_attempts', ['student_id' => $student->id, 'quiz_id' => $quiz->id]);
    }

    public function test_enrolled_student_keeps_access_in_open_module(): void
    {
        $course = $this->makeCourse($this->makeInstructor());
        $lesson = $this->makeArticleLesson($this->makeModule($course));
        $student = $this->makeStudent();
        $this->enroll($student, $course);

        $this->actingAs($student)->getJson(route('student.lessons.content', $lesson))->assertOk();
        $this->actingAs($student)->postJson(route('student.lessons.complete', $lesson))->assertOk();

        $this->assertDatabaseHas('lesson_user', ['user_id' => $student->id, 'lesson_id' => $lesson->id]);
    }
}
