<?php

namespace Tests\Feature\Gamification;

use App\Models\Lesson;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionTopic;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\SiteSetting;
use App\Services\PointService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

class PointsGainedTest extends TestCase
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
        $this->setPoints(['points_for_article' => 10, 'points_for_quiz' => 20]);

        $this->student = $this->makeStudent();
        $this->course = $this->makeCourse($this->makeInstructor());
        $this->module = $this->makeModule($this->course);
        $this->enroll($this->student, $this->course);
    }

    private function setPoints(array $values): void
    {
        DB::table('site_settings')->update($values);
        cache()->forget('site_settings');
    }

    private function quizLesson(): array
    {
        $quiz = Quiz::factory()->create(['pass_mark' => 50, 'max_attempts' => null]);
        Lesson::factory()->create([
            'module_id' => $this->module->id,
            'lessonable_type' => Quiz::class,
            'lessonable_id' => $quiz->id,
        ]);
        $topic = QuestionTopic::create(['instructor_id' => $this->course->instructor_id, 'name' => 'T', 'available_for_all_courses' => true]);
        $question = Question::create(['topic_id' => $topic->id, 'question_text' => '1 + 1 = ?', 'score' => 10, 'question_type' => 'multiple_choice_single']);
        $right = QuestionOption::create(['question_id' => $question->id, 'option_text' => '2', 'is_correct' => true]);
        $quiz->questions()->attach($question->id, ['order' => 1]);

        return [$quiz, $question, $right];
    }

    private function passQuiz(Quiz $quiz, Question $question, QuestionOption $right)
    {
        $this->actingAs($this->student)->post(route('student.quiz.begin', $quiz));
        $attempt = QuizAttempt::where('student_id', $this->student->id)->latest('id')->firstOrFail();

        return $this->actingAs($this->student)->post(route('student.quiz.submit', $attempt), ['answers' => [$question->id => $right->id]]);
    }

    public function test_completing_an_article_returns_the_points_once(): void
    {
        $lesson = $this->makeArticleLesson($this->module);

        $this->actingAs($this->student)->postJson(route('student.lessons.complete', $lesson))
            ->assertOk()
            ->assertJson(['success' => true, 'points' => 10]);

        $this->actingAs($this->student)->postJson(route('student.lessons.complete', $lesson))
            ->assertOk()
            ->assertJson(['success' => true, 'points' => 0]);
    }

    public function test_first_quiz_pass_flashes_points_and_a_repeat_pass_does_not(): void
    {
        [$quiz, $question, $right] = $this->quizLesson();

        $this->passQuiz($quiz, $question, $right)->assertSessionHas('rs_points', 20);

        $this->flushSession();

        $this->passQuiz($quiz, $question, $right)->assertSessionMissing('rs_points');
    }

    public function test_add_points_returns_zero_when_the_setting_is_zero(): void
    {
        $this->setPoints(['points_for_article' => 0]);
        $lesson = $this->makeArticleLesson($this->module);

        $earned = PointService::addPoints($this->student, $this->course, 'complete_article', $lesson, $lesson->title);

        $this->assertSame(0, $earned);
    }

    public function test_add_points_returns_the_amount_when_points_are_given(): void
    {
        $lesson = $this->makeArticleLesson($this->module);

        $this->assertSame(10, PointService::addPoints($this->student, $this->course, 'complete_article', $lesson, $lesson->title));
    }

    public function test_points_flash_component_renders_only_for_a_positive_number(): void
    {
        $this->actingAs($this->student)->withSession(['rs_points' => 15])->get(route('student.dashboard'))
            ->assertOk()->assertSee('rsPointsGained(15)', false);

        $this->actingAs($this->student)->withSession(['rs_points' => 0])->get(route('student.dashboard'))
            ->assertOk()->assertDontSee('rsPointsGained(', false);
    }
}
