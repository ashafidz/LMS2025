<?php

namespace Tests\Feature\Student;

use App\Models\CourseReview;
use App\Models\Lesson;
use App\Models\LessonAssignment;
use App\Models\PointHistory;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionTopic;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

class ManualCompletionTest extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    private $student;

    private $course;

    private $module;

    protected function setUp(): void
    {
        parent::setUp();

        SiteSetting::create([
            'site_name' => 'ruangstudi.id',
            'points_for_purchase' => 100,
            'vat_percentage' => 0,
            'transaction_fee_fixed' => 0,
            'transaction_fee_percentage' => 0,
        ]);
        cache()->forget('site_settings');

        $this->student = $this->makeStudent();
        $this->course = $this->makeCourse($this->makeInstructor());
        $this->module = $this->makeModule($this->course);
        $this->enroll($this->student, $this->course);
    }

    private function quizLesson(array $quizAttributes = []): array
    {
        $quiz = Quiz::factory()->create(array_merge(['pass_mark' => 50, 'max_attempts' => null], $quizAttributes));
        $lesson = Lesson::factory()->create([
            'module_id' => $this->module->id,
            'lessonable_type' => Quiz::class,
            'lessonable_id' => $quiz->id,
        ]);
        $topic = QuestionTopic::create(['instructor_id' => $this->course->instructor_id, 'name' => 'T', 'available_for_all_courses' => true]);
        $question = Question::create(['topic_id' => $topic->id, 'question_text' => '1 + 1 = ?', 'score' => 10, 'question_type' => 'multiple_choice_single']);
        $right = QuestionOption::create(['question_id' => $question->id, 'option_text' => '2', 'is_correct' => true]);
        QuestionOption::create(['question_id' => $question->id, 'option_text' => '3', 'is_correct' => false]);
        $quiz->questions()->attach($question->id, ['order' => 1]);

        return [$quiz, $lesson, $question, $right];
    }

    public function test_quiz_lesson_cannot_be_marked_complete_manually(): void
    {
        [, $lesson] = $this->quizLesson();

        $this->actingAs($this->student)->postJson(route('student.lessons.complete', $lesson))->assertForbidden();

        $this->assertDatabaseMissing('lesson_user', ['user_id' => $this->student->id, 'lesson_id' => $lesson->id]);
    }

    public function test_assignment_lesson_cannot_be_marked_complete_manually(): void
    {
        $assignment = LessonAssignment::create(['instructions' => 'Kumpulkan tugas', 'pass_mark' => 70]);
        $lesson = Lesson::factory()->create([
            'module_id' => $this->module->id,
            'lessonable_type' => LessonAssignment::class,
            'lessonable_id' => $assignment->id,
        ]);

        $this->actingAs($this->student)->postJson(route('student.lessons.complete', $lesson))->assertForbidden();

        $this->assertDatabaseMissing('lesson_user', ['user_id' => $this->student->id, 'lesson_id' => $lesson->id]);
    }

    public function test_article_lesson_completes_once_and_awards_points_once(): void
    {
        $lesson = $this->makeArticleLesson($this->module);

        $this->actingAs($this->student)->postJson(route('student.lessons.complete', $lesson))->assertOk();
        $this->actingAs($this->student)->postJson(route('student.lessons.complete', $lesson))->assertOk();

        $this->assertDatabaseHas('lesson_user', ['user_id' => $this->student->id, 'lesson_id' => $lesson->id]);
        $this->assertSame(1, PointHistory::where('user_id', $this->student->id)->where('lesson_id', $lesson->id)->count());
    }

    public function test_certificate_is_denied_when_quiz_is_not_passed_even_if_everything_else_is_done(): void
    {
        $article = $this->makeArticleLesson($this->module);
        [, $quizLesson] = $this->quizLesson();
        CourseReview::create(['course_id' => $this->course->id, 'user_id' => $this->student->id, 'rating' => 5, 'comment' => 'Bagus']);

        $this->actingAs($this->student)->postJson(route('student.lessons.complete', $article))->assertOk();
        $this->actingAs($this->student)->postJson(route('student.lessons.complete', $quizLesson))->assertForbidden();

        $this->actingAs($this->student)->getJson(route('student.certificate.preview', $this->course))->assertForbidden();
    }

    public function test_passing_the_quiz_marks_its_lesson_complete(): void
    {
        [$quiz, $lesson, $question, $right] = $this->quizLesson();

        $this->actingAs($this->student)->post(route('student.quiz.begin', $quiz));
        $attempt = QuizAttempt::where('student_id', $this->student->id)->firstOrFail();
        $this->actingAs($this->student)->post(route('student.quiz.submit', $attempt), ['answers' => [$question->id => $right->id]]);

        $this->assertSame('passed', $attempt->fresh()->status);
        $this->assertDatabaseHas('lesson_user', ['user_id' => $this->student->id, 'lesson_id' => $lesson->id]);
    }

    public function test_passing_marks_lesson_complete_even_when_points_were_already_given(): void
    {
        [$quiz, $lesson, $question, $right] = $this->quizLesson();
        PointHistory::create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'lesson_id' => $lesson->id,
            'points' => 5,
            'description' => 'Poin manual',
        ]);

        $this->actingAs($this->student)->post(route('student.quiz.begin', $quiz));
        $attempt = QuizAttempt::where('student_id', $this->student->id)->firstOrFail();
        $this->actingAs($this->student)->post(route('student.quiz.submit', $attempt), ['answers' => [$question->id => $right->id]]);

        $this->assertDatabaseHas('lesson_user', ['user_id' => $this->student->id, 'lesson_id' => $lesson->id]);
        $this->assertSame(1, PointHistory::where('user_id', $this->student->id)->where('lesson_id', $lesson->id)->count());
    }

    public function test_passing_late_when_exceeding_time_is_allowed_completes_lesson_without_points(): void
    {
        [$quiz, $lesson, $question, $right] = $this->quizLesson(['time_limit' => 1, 'allow_exceed_time_limit' => true]);

        $this->actingAs($this->student)->post(route('student.quiz.begin', $quiz));
        $attempt = QuizAttempt::where('student_id', $this->student->id)->firstOrFail();

        $this->travel(5)->minutes();
        $this->actingAs($this->student)->post(route('student.quiz.submit', $attempt), ['answers' => [$question->id => $right->id]]);

        $this->assertSame('passed', $attempt->fresh()->status);
        $this->assertDatabaseHas('lesson_user', ['user_id' => $this->student->id, 'lesson_id' => $lesson->id]);
        $this->assertSame(0, PointHistory::where('user_id', $this->student->id)->where('lesson_id', $lesson->id)->count());
    }
}
