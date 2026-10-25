<?php

namespace Tests\Feature\Quiz;

use App\Models\Lesson;
use App\Models\PointHistory;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionTopic;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptIntegritySummary;
use App\Models\QuizSecuritySetting;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

class QuizTimingTest extends TestCase
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

    private function quizWithQuestion(array $attributes = []): array
    {
        $quiz = Quiz::factory()->create(array_merge(['pass_mark' => 50, 'max_attempts' => null, 'time_limit' => 10], $attributes));
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

    private function begin(Quiz $quiz): QuizAttempt
    {
        $this->actingAs($this->student)->post(route('student.quiz.begin', $quiz));

        return QuizAttempt::where('student_id', $this->student->id)->where('quiz_id', $quiz->id)->firstOrFail();
    }

    private function submit(QuizAttempt $attempt, Question $question, QuestionOption $right, array $extra = [])
    {
        return $this->actingAs($this->student)
            ->post(route('student.quiz.submit', $attempt), array_merge(['answers' => [$question->id => $right->id]], $extra));
    }

    public function test_submit_after_deadline_fails_without_points_or_completion(): void
    {
        [$quiz, $lesson, $question, $right] = $this->quizWithQuestion();
        $attempt = $this->begin($quiz);

        $this->travel(13)->minutes();
        $this->submit($attempt, $question, $right);

        $this->assertSame('failed', $attempt->fresh()->status);
        $this->assertSame(0, PointHistory::where('user_id', $this->student->id)->where('lesson_id', $lesson->id)->count());
        $this->assertDatabaseMissing('lesson_user', ['user_id' => $this->student->id, 'lesson_id' => $lesson->id]);
        $this->assertSame(1, $attempt->answers()->count());
    }

    public function test_submit_within_grace_period_is_graded_normally(): void
    {
        [$quiz, $lesson, $question, $right] = $this->quizWithQuestion();
        $attempt = $this->begin($quiz);

        $this->travel(10)->minutes();
        $this->travel(30)->seconds();
        $this->submit($attempt, $question, $right);

        $this->assertSame('passed', $attempt->fresh()->status);
        $this->assertDatabaseHas('lesson_user', ['user_id' => $this->student->id, 'lesson_id' => $lesson->id]);
    }

    public function test_late_submit_can_pass_when_quiz_allows_exceeding_time(): void
    {
        [$quiz, , $question, $right] = $this->quizWithQuestion(['allow_exceed_time_limit' => true]);
        $attempt = $this->begin($quiz);

        $this->travel(15)->minutes();
        $this->submit($attempt, $question, $right);

        $this->assertSame('passed', $attempt->fresh()->status);
    }

    public function test_begin_is_rejected_after_available_to(): void
    {
        [$quiz] = $this->quizWithQuestion(['available_to' => now()->subDay()]);

        $this->actingAs($this->student)->post(route('student.quiz.begin', $quiz))
            ->assertRedirect(route('student.quiz.start', $quiz))
            ->assertSessionHas('error');

        $this->assertSame(0, QuizAttempt::where('student_id', $this->student->id)->count());
    }

    public function test_begin_is_rejected_before_available_from(): void
    {
        [$quiz] = $this->quizWithQuestion(['available_from' => now()->addDay()]);

        $this->actingAs($this->student)->post(route('student.quiz.begin', $quiz))->assertSessionHas('error');

        $this->assertSame(0, QuizAttempt::where('student_id', $this->student->id)->count());
    }

    public function test_expelled_flag_comes_from_server_logs_not_from_the_form(): void
    {
        [$quiz, , $question, $right] = $this->quizWithQuestion();
        QuizSecuritySetting::create([
            'quiz_id' => $quiz->id,
            'enable_tab_detection' => true,
            'enable_camera_detection' => false,
            'tab_violation_threshold' => 3,
        ]);
        $attempt = $this->begin($quiz);
        QuizAttemptIntegritySummary::create(['attempt_id' => $attempt->id, 'total_tab_switches' => 3]);

        $this->submit($attempt, $question, $right, ['expelled_by_violation' => '0']);

        $this->assertTrue((bool) $attempt->fresh()->expelled_by_violation);
    }

    public function test_forged_expelled_flag_is_ignored_without_violations(): void
    {
        [$quiz, , $question, $right] = $this->quizWithQuestion();
        $attempt = $this->begin($quiz);

        $this->submit($attempt, $question, $right, ['expelled_by_violation' => '1']);

        $this->assertFalse((bool) $attempt->fresh()->expelled_by_violation);
    }

    public function test_submit_within_extended_grace_of_two_minutes_still_passes(): void
    {
        [$quiz, , $question, $right] = $this->quizWithQuestion();
        $attempt = $this->begin($quiz);

        $this->travel(11)->minutes();
        $this->travel(50)->seconds();
        $this->submit($attempt, $question, $right);

        $this->assertSame('passed', $attempt->fresh()->status);
    }

    public function test_take_page_gets_server_clock_and_deadline_in_milliseconds(): void
    {
        [$quiz] = $this->quizWithQuestion();
        $attempt = $this->begin($quiz);

        $this->travel(4)->minutes();

        $response = $this->actingAs($this->student)->get(route('student.quiz.take', $attempt))->assertOk();

        $serverNowMs = $response->viewData('serverNowMs');
        $endTimeMs = $response->viewData('endTimeMs');
        $this->assertSame(now()->getTimestampMs(), $serverNowMs);
        $this->assertSame($attempt->fresh()->start_time->copy()->addMinutes(10)->getTimestampMs(), $endTimeMs);
        $this->assertEqualsWithDelta(360000, $endTimeMs - $serverNowMs, 1000);
        $response->assertSee('const clockOffset', false);
    }

    public function test_result_page_explains_a_late_failure(): void
    {
        [$quiz, , $question, $right] = $this->quizWithQuestion();
        $attempt = $this->begin($quiz);

        $this->travel(13)->minutes();
        $this->submit($attempt, $question, $right);

        $this->actingAs($this->student)->get(route('student.quiz.result', $attempt))
            ->assertOk()
            ->assertSee('Waktu pengerjaan sudah habis.');
    }

    public function test_result_page_has_no_late_reason_for_a_normal_failure(): void
    {
        [$quiz, , $question] = $this->quizWithQuestion();
        $attempt = $this->begin($quiz);

        $this->actingAs($this->student)->post(route('student.quiz.submit', $attempt), ['answers' => []]);

        $this->assertSame('failed', $attempt->fresh()->status);
        $this->actingAs($this->student)->get(route('student.quiz.result', $attempt))
            ->assertOk()
            ->assertDontSee('Waktu pengerjaan sudah habis.');
    }

    public function test_late_reason_mentions_sufficient_score_only_when_score_was_enough(): void
    {
        [$quiz, , $question, $right] = $this->quizWithQuestion();
        $attempt = $this->begin($quiz);
        $this->travel(13)->minutes();
        $this->submit($attempt, $question, $right);

        $this->actingAs($this->student)->get(route('student.quiz.result', $attempt))
            ->assertSee('Waktu pengerjaan sudah habis.')
            ->assertSee('walaupun nilainya mencukupi');
    }

    public function test_late_reason_is_neutral_when_score_was_not_enough(): void
    {
        [$quiz] = $this->quizWithQuestion();
        $attempt = $this->begin($quiz);
        $this->travel(13)->minutes();
        $this->actingAs($this->student)->post(route('student.quiz.submit', $attempt), ['answers' => []]);

        $this->actingAs($this->student)->get(route('student.quiz.result', $attempt))
            ->assertSee('Waktu pengerjaan sudah habis.')
            ->assertDontSee('walaupun nilainya mencukupi');
    }
}
