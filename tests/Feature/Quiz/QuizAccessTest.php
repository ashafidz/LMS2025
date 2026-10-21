<?php

namespace Tests\Feature\Quiz;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionTopic;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizAccessTest extends TestCase
{
    use RefreshDatabase;

    protected User $instructor;

    protected User $studentA;

    protected User $studentB;

    protected Course $course;

    protected Quiz $quiz;

    protected Lesson $lesson;

    protected Question $question;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();

        $this->instructor = $this->makeInstructor();
        $this->studentA = $this->makeStudent();
        $this->studentB = $this->makeStudent();

        $category = CourseCategory::factory()->create();

        $this->course = Course::factory()->create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $category->id,
            'status' => 'published',
        ]);

        $module = Module::factory()->create([
            'course_id' => $this->course->id,
        ]);

        $this->quiz = Quiz::factory()->create([
            'title' => 'Test Quiz',
            'pass_mark' => 70,
        ]);

        $this->lesson = Lesson::factory()->create([
            'module_id' => $module->id,
            'title' => 'Quiz Lesson',
            'lessonable_id' => $this->quiz->id,
            'lessonable_type' => Quiz::class,
        ]);

        $topic = QuestionTopic::create([
            'instructor_id' => $this->instructor->id,
            'name' => 'General Topic',
            'available_for_all_courses' => true,
        ]);

        $this->question = Question::create([
            'topic_id' => $topic->id,
            'question_text' => '2 + 2 = ?',
            'score' => 100,
            'question_type' => 'multiple_choice_single',
        ]);

        $optionCorrect = QuestionOption::create([
            'question_id' => $this->question->id,
            'option_text' => '4',
            'is_correct' => true,
        ]);

        QuestionOption::create([
            'question_id' => $this->question->id,
            'option_text' => '5',
            'is_correct' => false,
        ]);

        $this->quiz->questions()->attach($this->question->id, ['order' => 1]);
    }

    public function test_non_enrolled_student_cannot_begin_quiz_even_with_tampered_active_role(): void
    {
        // studentA is not enrolled
        $response = $this->actingAs($this->studentA)
            ->withSession(['active_role' => 'foo'])
            ->post(route('student.quiz.begin', $this->quiz));

        $response->assertStatus(403);
    }

    public function test_student_b_cannot_take_student_a_attempt(): void
    {
        $this->enroll($this->studentA, $this->course);
        $this->enroll($this->studentB, $this->course);

        $attempt = QuizAttempt::create([
            'quiz_id' => $this->quiz->id,
            'student_id' => $this->studentA->id,
            'status' => 'in_progress',
            'start_time' => now(),
        ]);

        $response = $this->actingAs($this->studentB)
            ->withSession(['active_role' => 'student'])
            ->get(route('student.quiz.take', $attempt));

        $response->assertStatus(403);
    }

    public function test_student_cannot_preview_quiz(): void
    {
        $this->enroll($this->studentA, $this->course);

        // Preview in start
        $responseStart = $this->actingAs($this->studentA)
            ->withSession(['active_role' => 'student'])
            ->get(route('student.quiz.start', ['quiz' => $this->quiz, 'preview' => 'true']));
        $responseStart->assertStatus(403);

        // Preview in begin
        $responseBegin = $this->actingAs($this->studentA)
            ->withSession(['active_role' => 'student'])
            ->post(route('student.quiz.begin', $this->quiz), [
                'is_preview' => 'true',
            ]);
        $responseBegin->assertStatus(403);
    }

    public function test_course_instructor_can_preview_quiz(): void
    {
        $responseStart = $this->actingAs($this->instructor)
            ->withSession(['active_role' => 'instructor'])
            ->get(route('student.quiz.start', ['quiz' => $this->quiz, 'preview' => 'true']));
        $responseStart->assertStatus(200);

        $responseBegin = $this->actingAs($this->instructor)
            ->withSession(['active_role' => 'instructor'])
            ->post(route('student.quiz.begin', $this->quiz), [
                'is_preview' => 'true',
            ]);
        $responseBegin->assertStatus(200);
    }

    public function test_enrolled_student_can_begin_and_submit_quiz(): void
    {
        $this->enroll($this->studentA, $this->course);

        $responseBegin = $this->actingAs($this->studentA)
            ->withSession(['active_role' => 'student'])
            ->post(route('student.quiz.begin', $this->quiz));

        $responseBegin->assertStatus(302);

        $attempt = QuizAttempt::where('quiz_id', $this->quiz->id)
            ->where('student_id', $this->studentA->id)
            ->first();

        $this->assertNotNull($attempt);
        $this->assertEquals('in_progress', $attempt->status);

        $correctOption = $this->question->options()->where('is_correct', true)->first();

        $responseSubmit = $this->actingAs($this->studentA)
            ->withSession(['active_role' => 'student'])
            ->post(route('student.quiz.submit', $attempt), [
                'answers' => [
                    $this->question->id => $correctOption->id,
                ],
            ]);

        $responseSubmit->assertStatus(302);

        $attempt->refresh();
        $this->assertEquals('passed', $attempt->status);
        $this->assertEquals(100, $attempt->score);
    }

    public function test_check_answer_route_returns_404(): void
    {
        $response = $this->actingAs($this->studentA)
            ->post('/student/quiz/check-answer', [
                'question_id' => $this->question->id,
                'answer' => 1,
            ]);

        $response->assertStatus(404);
    }
}
