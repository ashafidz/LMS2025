<?php

namespace Tests\Feature\Performance;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\PointHistory;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionTopic;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptIntegritySummary;
use App\Models\QuizSecuritySetting;
use App\Models\StudentAnswer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class QueryBudgetTest extends TestCase
{
    use RefreshDatabase;

    private const QUESTION_COUNT = 30;

    protected User $student;

    protected Course $course;

    protected Quiz $quiz;

    protected Lesson $lesson;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();

        $instructor = $this->makeInstructor();
        $this->student = $this->makeStudent();

        $this->course = Course::factory()->create([
            'instructor_id' => $instructor->id,
            'category_id' => CourseCategory::factory()->create()->id,
            'status' => 'published',
        ]);
        $module = Module::factory()->create(['course_id' => $this->course->id]);

        $this->quiz = Quiz::factory()->create(['title' => 'Budget Quiz', 'pass_mark' => 70]);
        $this->lesson = Lesson::factory()->create([
            'module_id' => $module->id,
            'title' => 'Quiz Lesson',
            'lessonable_id' => $this->quiz->id,
            'lessonable_type' => Quiz::class,
        ]);

        QuizSecuritySetting::create([
            'quiz_id' => $this->quiz->id,
            'enable_camera_detection' => true,
            'enable_tab_detection' => true,
            'camera_violation_threshold' => 10,
            'tab_violation_threshold' => 5,
        ]);

        $topic = QuestionTopic::create([
            'instructor_id' => $instructor->id,
            'name' => 'General Topic',
            'available_for_all_courses' => true,
        ]);

        for ($i = 1; $i <= self::QUESTION_COUNT; $i++) {
            $question = Question::create([
                'topic_id' => $topic->id,
                'question_text' => "Question {$i}",
                'score' => 1,
                'question_type' => 'multiple_choice_single',
            ]);
            foreach ([true, false, false, false] as $isCorrect) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'option_text' => $isCorrect ? 'right' : 'wrong',
                    'is_correct' => $isCorrect,
                ]);
            }
            $this->quiz->questions()->attach($question->id, ['order' => $i]);
        }

        $this->enroll($this->student, $this->course);
    }

    /**
     * Run a request and return [response, all queries, schema queries].
     */
    private function measure(callable $request): array
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        /** @var TestResponse $response */
        $response = $request();
        $queries = collect(DB::getQueryLog());
        DB::disableQueryLog();

        $schemaQueries = $queries->filter(fn ($q) => str_contains($q['query'], 'sqlite_master'))->count();

        return [$response, $queries->count(), $schemaQueries];
    }

    private function asStudent()
    {
        return $this->actingAs($this->student)->withSession(['active_role' => 'student']);
    }

    private function startAttempt(): QuizAttempt
    {
        $this->asStudent()->post(route('student.quiz.begin', $this->quiz));

        return QuizAttempt::where('student_id', $this->student->id)->latest('id')->firstOrFail();
    }

    public function test_student_pages_run_no_schema_queries(): void
    {
        // quiz.start redirects once an attempt is in progress, so visit it before starting
        $pages = [
            route('student.courses.show', $this->course->slug),
            route('student.quiz.start', $this->quiz),
        ];
        foreach ($pages as $url) {
            [$response, , $schemaQueries] = $this->measure(fn () => $this->asStudent()->get($url));
            $response->assertOk();
            $this->assertSame(0, $schemaQueries, "Schema queries found on {$url}");
        }

        $pages = [route('student.quiz.take', $this->startAttempt())];

        foreach ($pages as $url) {
            [$response, , $schemaQueries] = $this->measure(fn () => $this->asStudent()->get($url));
            $response->assertOk();
            $this->assertSame(0, $schemaQueries, "Schema queries found on {$url}");
        }
    }

    public function test_quiz_begin_stays_within_query_budget(): void
    {
        [$response, $queries] = $this->measure(fn () => $this->asStudent()->post(route('student.quiz.begin', $this->quiz)));

        $response->assertRedirect();
        $attempt = QuizAttempt::where('student_id', $this->student->id)->firstOrFail();
        $this->assertSame(self::QUESTION_COUNT, DB::table('quiz_attempt_question_order')->where('attempt_id', $attempt->id)->count());
        $this->assertLessThanOrEqual(15, $queries, "quiz.begin ran {$queries} queries");
    }

    public function test_quiz_submit_stays_within_query_budget_and_scores_correctly(): void
    {
        $attempt = $this->startAttempt();

        // Answer the first 20 questions right and the rest wrong
        $answers = [];
        foreach ($this->quiz->questions()->with('options')->get()->values() as $index => $question) {
            $answers[$question->id] = $question->options->firstWhere('is_correct', $index < 20)->id;
        }

        [$response, $queries] = $this->measure(fn () => $this->asStudent()->post(route('student.quiz.submit', $attempt), ['answers' => $answers]));

        $response->assertRedirect(route('student.quiz.result', $attempt->id));
        $attempt->refresh();
        $this->assertSame('failed', $attempt->status);
        $this->assertEquals(20, $attempt->score);
        $this->assertSame(self::QUESTION_COUNT, StudentAnswer::where('attempt_id', $attempt->id)->count());
        $this->assertSame(20, StudentAnswer::where('attempt_id', $attempt->id)->where('is_correct', true)->count());
        $this->assertNotNull(StudentAnswer::where('attempt_id', $attempt->id)->first()->getRawOriginal('created_at'));
        $this->assertLessThanOrEqual(20, $queries, "quiz.submit ran {$queries} queries");
    }

    public function test_camera_violation_counts_and_stores_screenshot(): void
    {
        Storage::fake('public');
        $attempt = $this->startAttempt();
        $this->travelTo(now()->subMinutes(5));
        QuizAttemptIntegritySummary::create(['attempt_id' => $attempt->id]);
        $this->travelBack();

        $this->asStudent()->post(route('student.quiz.log_camera_violation', $attempt), [
            'violation_type' => 'look_left',
            'screenshot' => UploadedFile::fake()->image('shot.jpg', 480, 360),
        ])->assertOk()->assertJson(['success' => true, 'violation_count' => 1]);

        $this->asStudent()->post(route('student.quiz.log_camera_violation', $attempt), [
            'violation_type' => 'look_down',
        ])->assertOk()->assertJson([
            'success' => true,
            'violation_count' => 2,
            'violation_breakdown' => ['look_left' => 1, 'look_down' => 1, 'look_right' => 0],
            'should_block' => false,
        ]);

        $this->assertDatabaseHas('quiz_attempt_integrity_summary', [
            'attempt_id' => $attempt->id,
            'total_face_violations' => 2,
            'look_left_count' => 1,
            'look_down_count' => 1,
        ]);
        $this->assertCount(1, Storage::disk('public')->allFiles('screenshots/violations/'.$attempt->id));
        $summary = QuizAttemptIntegritySummary::where('attempt_id', $attempt->id)->first();
        $this->assertTrue($summary->getRawOriginal('updated_at') > $summary->getRawOriginal('created_at'), 'updated_at not touched');
    }

    public function test_tab_violation_counts_up_per_call(): void
    {
        $attempt = $this->startAttempt();

        $this->asStudent()->postJson(route('student.quiz.log_tab_violation', $attempt))
            ->assertOk()->assertJson(['violation_count' => 1]);
        $this->asStudent()->postJson(route('student.quiz.log_tab_violation', $attempt))
            ->assertOk()->assertJson(['violation_count' => 2]);
    }

    public function test_certificate_pdf_is_stored_and_reused(): void
    {
        Storage::fake('local');
        $this->student->completedLessons()->attach($this->lesson->id);
        $this->course->reviews()->create(['user_id' => $this->student->id, 'rating' => 5, 'comment' => 'Bagus']);

        $first = $this->asStudent()->get(route('student.certificate.download', $this->course));
        $first->assertOk();
        $files = Storage::disk('local')->allFiles('certificates');
        $this->assertCount(1, $files);

        $second = $this->asStudent()->get(route('student.certificate.download', $this->course));
        $second->assertOk();
        $this->assertSame($files, Storage::disk('local')->allFiles('certificates'));
        $this->assertStringContainsString('sertifikat-', $second->headers->get('content-disposition'));
    }

    public function test_leaderboards_do_not_run_a_query_per_student(): void
    {
        // Warm-up request so one-time queries (e.g. the permission cache) are not counted
        $this->asStudent()->getJson(route('student.course.leaderboard', $this->course));

        $counts = [];
        foreach ([3, 15] as $total) {
            while (DB::table('course_enrollments')->where('course_id', $this->course->id)->count() < $total) {
                $other = $this->makeStudent();
                $this->enroll($other, $this->course);
                PointHistory::create(['user_id' => $other->id, 'course_id' => $this->course->id, 'lesson_id' => $this->lesson->id, 'points' => 10, 'description' => 'x']);
                DB::table('course_user')->insert(['user_id' => $other->id, 'course_id' => $this->course->id, 'points_earned' => 10]);
            }

            [$course, $courseQueries] = $this->measure(fn () => $this->asStudent()->getJson(route('student.course.leaderboard', $this->course)));
            [$module, $moduleQueries] = $this->measure(fn () => $this->asStudent()->getJson(route('student.module.leaderboard', $this->lesson->module_id)));
            $course->assertOk()->assertJson(['success' => true]);
            $module->assertOk()->assertJson(['success' => true]);
            $counts[$total] = [$courseQueries, $moduleQueries];
        }

        $this->assertSame($counts[3], $counts[15], 'Leaderboard query count grows with the number of students');
    }
}
