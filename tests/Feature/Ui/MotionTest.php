<?php

namespace Tests\Feature\Ui;

use App\Models\Lesson;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionTopic;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

class MotionTest extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

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
    }

    public function test_only_the_home_page_has_the_intro(): void
    {
        $this->get('/')->assertOk()->assertSee('id="rs-intro"', false);
        $this->get(route('about'))->assertOk()->assertDontSee('rs-intro', false);
    }

    public function test_home_page_marks_at_least_five_sections_for_reveal(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertGreaterThanOrEqual(5, substr_count($html, 'data-rs-reveal'));
    }

    public function test_student_dashboard_numbers_are_marked_for_count_up(): void
    {
        $student = $this->makeStudent();

        $this->actingAs($student)->withSession(['active_role' => 'student'])
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('data-rs-countup=', false);
    }

    public function test_passed_quiz_result_celebrates_and_failed_one_does_not(): void
    {
        $student = $this->makeStudent();
        $course = $this->makeCourse($this->makeInstructor());
        $module = $this->makeModule($course);
        $this->enroll($student, $course);

        $quiz = Quiz::factory()->create(['pass_mark' => 50, 'max_attempts' => null, 'time_limit' => 10]);
        Lesson::factory()->create(['module_id' => $module->id, 'lessonable_type' => Quiz::class, 'lessonable_id' => $quiz->id]);
        $topic = QuestionTopic::create(['instructor_id' => $course->instructor_id, 'name' => 'T', 'available_for_all_courses' => true]);
        $question = Question::create(['topic_id' => $topic->id, 'question_text' => '1 + 1 = ?', 'score' => 10, 'question_type' => 'multiple_choice_single']);
        $right = QuestionOption::create(['question_id' => $question->id, 'option_text' => '2', 'is_correct' => true]);
        QuestionOption::create(['question_id' => $question->id, 'option_text' => '3', 'is_correct' => false]);
        $quiz->questions()->attach($question->id, ['order' => 1]);

        $begin = function () use ($student, $quiz) {
            $this->actingAs($student)->post(route('student.quiz.begin', $quiz));

            return QuizAttempt::where('student_id', $student->id)->where('quiz_id', $quiz->id)->latest('id')->firstOrFail();
        };

        $failed = $begin();
        $this->actingAs($student)->post(route('student.quiz.submit', $failed), ['answers' => []]);
        $this->assertSame('failed', $failed->fresh()->status);
        $this->actingAs($student)->get(route('student.quiz.result', $failed))
            ->assertOk()
            ->assertDontSee('data-rs-celebrate', false);

        $passed = $begin();
        $this->actingAs($student)->post(route('student.quiz.submit', $passed), ['answers' => [$question->id => $right->id]]);
        $this->assertSame('passed', $passed->fresh()->status);
        $this->actingAs($student)->get(route('student.quiz.result', $passed))
            ->assertOk()
            ->assertSee('data-rs-celebrate="quiz-'.$passed->id.'"', false);
    }

    public function test_both_layouts_load_the_motion_script(): void
    {
        $this->get('/')->assertOk()->assertSee('js/rs-motion.js', false);

        $this->actingAs($this->makeStudent())->withSession(['active_role' => 'student'])
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('js/rs-motion.js', false);
    }

    public function test_quiz_take_page_has_timer_pill_and_one_time_warning_keys(): void
    {
        $student = $this->makeStudent();
        $course = $this->makeCourse($this->makeInstructor());
        $module = $this->makeModule($course);
        $this->enroll($student, $course);
        $quiz = Quiz::factory()->create(['pass_mark' => 50, 'max_attempts' => null, 'time_limit' => 10]);
        Lesson::factory()->create(['module_id' => $module->id, 'lessonable_type' => Quiz::class, 'lessonable_id' => $quiz->id]);
        $topic = QuestionTopic::create(['instructor_id' => $course->instructor_id, 'name' => 'T', 'available_for_all_courses' => true]);
        $question = Question::create(['topic_id' => $topic->id, 'question_text' => '1 + 1 = ?', 'score' => 10, 'question_type' => 'multiple_choice_single']);
        QuestionOption::create(['question_id' => $question->id, 'option_text' => '2', 'is_correct' => true]);
        $quiz->questions()->attach($question->id, ['order' => 1]);

        $this->actingAs($student)->post(route('student.quiz.begin', $quiz));
        $attempt = QuizAttempt::where('student_id', $student->id)->firstOrFail();

        $this->actingAs($student)->get(route('student.quiz.take', $attempt))
            ->assertOk()
            ->assertSee('rs-quiz-timer', false)
            ->assertSee('id="quiz-timer"', false)
            ->assertSee('rs-quiz-warn:'.$attempt->id.':', false)
            ->assertSee('Sisa 5 menit');
    }

    public function test_learning_page_has_check_pop_bump_and_skeleton(): void
    {
        $student = $this->makeStudent();
        $course = $this->makeCourse($this->makeInstructor());
        $module = $this->makeModule($course);
        $this->makeArticleLesson($module);
        $this->enroll($student, $course);

        $this->actingAs($student)->withSession(['active_role' => 'student'])
            ->get(route('student.courses.show', $course->slug))
            ->assertOk()
            ->assertSee('rs-check-pop', false)
            ->assertSee('rs-bump', false)
            ->assertSee('rs-skel', false);
    }

    public function test_dashboard_css_defines_stage_two_motion_inside_no_preference(): void
    {
        $css = file_get_contents(public_path('css/ruang-studi-dashboard.css'));

        foreach (['@keyframes rsTimerPulse', '@keyframes rsCheckPop', '@keyframes rsBump', '@keyframes rsShimmer', '.rs-skel__line'] as $needle) {
            $this->assertStringContainsString($needle, $css);
        }
        $block = substr($css, strpos($css, '.rs-skel { display: grid'));
        $block = substr($block, 0, strpos($block, '@keyframes rsTimerPulse'));
        $this->assertStringContainsString('@media (prefers-reduced-motion: no-preference)', $block);
        $this->assertStringContainsString('animation: rsShimmer', $block);
        $this->assertStringContainsString('animation: rsTimerPulse', $block);
    }
}
