<?php

namespace Tests\Feature\Student;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Lesson;
use App\Models\LessonAssignment;
use App\Models\LessonPolling;
use App\Models\LessonPollingOption;
use App\Models\LessonWordcloud;
use App\Models\Module;
use App\Models\PointHistory;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionTopic;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\StudentAnswer;
use App\Models\User;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Repeating a student action (double click, back button, retry) must not award points or store data twice.
 * Truly parallel requests are covered by scripts/loadtest/race.sh on MySQL; these tests pin the behaviour.
 */
class RepeatedActionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;

    protected Course $course;

    protected Module $module;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        $this->seed(SiteSettingSeeder::class);

        $instructor = $this->makeInstructor();
        $this->student = $this->makeStudent();
        $this->course = Course::factory()->create([
            'instructor_id' => $instructor->id,
            'category_id' => CourseCategory::factory()->create()->id,
            'status' => 'published',
        ]);
        $this->module = Module::factory()->create(['course_id' => $this->course->id]);
        $this->enroll($this->student, $this->course);
    }

    private function asStudent()
    {
        return $this->actingAs($this->student)->withSession(['active_role' => 'student']);
    }

    private function lessonFor($lessonable): Lesson
    {
        return Lesson::factory()->create([
            'module_id' => $this->module->id,
            'lessonable_type' => $lessonable::class,
            'lessonable_id' => $lessonable->id,
        ]);
    }

    private function pointsFor(Lesson $lesson): int
    {
        return PointHistory::where('user_id', $this->student->id)->where('lesson_id', $lesson->id)->count();
    }

    public function test_marking_a_lesson_complete_twice_awards_points_once(): void
    {
        $lesson = Lesson::factory()->create(['module_id' => $this->module->id]);

        $this->asStudent()->postJson(route('student.lessons.complete', $lesson))->assertOk()->assertJson(['success' => true]);
        $this->asStudent()->postJson(route('student.lessons.complete', $lesson))->assertOk()->assertJson(['success' => true]);

        $this->assertSame(1, $this->pointsFor($lesson));
        $this->assertSame(1, DB::table('course_user')->where('user_id', $this->student->id)->count());
        $this->assertDatabaseHas('lesson_user', ['user_id' => $this->student->id, 'lesson_id' => $lesson->id]);
    }

    public function test_second_polling_submission_is_rejected_without_extra_points(): void
    {
        $polling = LessonPolling::create(['question' => 'Pilih satu', 'is_active' => true, 'allow_multiple' => false, 'show_results' => true]);
        $option = LessonPollingOption::create(['lesson_polling_id' => $polling->id, 'text' => 'A', 'order' => 1]);
        $lesson = $this->lessonFor($polling);

        $this->asStudent()->postJson(route('student.lessons.polling.submit', $lesson), ['polling_option_id' => $option->id])
            ->assertOk()->assertJson(['success' => true]);
        $this->asStudent()->postJson(route('student.lessons.polling.submit', $lesson), ['polling_option_id' => $option->id])
            ->assertOk()->assertJson(['success' => false, 'message' => 'Anda sudah mengisi polling ini.']);

        $this->assertSame(1, DB::table('lesson_polling_responses')->where('user_id', $this->student->id)->count());
        $this->assertLessThanOrEqual(1, $this->pointsFor($lesson));
    }

    public function test_second_wordcloud_submission_is_rejected_without_extra_points(): void
    {
        $wordcloud = LessonWordcloud::create(['question' => 'Satu kata', 'max_words' => 1, 'is_active' => true]);
        $lesson = $this->lessonFor($wordcloud);

        $this->asStudent()->postJson(route('student.lessons.wordcloud.submit', $lesson), ['word' => 'Belajar'])
            ->assertOk()->assertJson(['success' => true]);
        $this->asStudent()->postJson(route('student.lessons.wordcloud.submit', $lesson), ['word' => 'Lagi'])
            ->assertOk()->assertJson(['success' => false, 'message' => 'Anda sudah berpartisipasi dalam Word Cloud ini.']);

        $this->assertSame(['belajar'], DB::table('lesson_wordcloud_responses')->where('user_id', $this->student->id)->pluck('word')->all());
        $this->assertLessThanOrEqual(1, $this->pointsFor($lesson));
    }

    public function test_resubmitting_an_assignment_replaces_the_file_and_awards_points_once(): void
    {
        Storage::fake('public');
        $assignment = LessonAssignment::create(['instructions' => 'Kerjakan', 'pass_mark' => 70]);
        $lesson = $this->lessonFor($assignment);

        $this->asStudent()->post(route('student.assignment.submit', $assignment), [
            'submission_file' => UploadedFile::fake()->create('tugas1.pdf', 10, 'application/pdf'),
        ])->assertRedirect();
        $firstPath = $assignment->submissions()->first()->file_path;

        $this->asStudent()->post(route('student.assignment.submit', $assignment), [
            'submission_file' => UploadedFile::fake()->create('tugas2.pdf', 10, 'application/pdf'),
        ])->assertRedirect();

        $this->assertSame(1, $assignment->submissions()->count());
        $this->assertSame(1, $this->pointsFor($lesson));
        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertExists($assignment->submissions()->first()->file_path);
    }

    public function test_quiz_begin_twice_reuses_the_attempt_and_double_submit_goes_to_result(): void
    {
        $quiz = Quiz::factory()->create(['pass_mark' => 50, 'max_attempts' => 1]);
        $lesson = $this->lessonFor($quiz);
        $topic = QuestionTopic::create(['instructor_id' => $this->course->instructor_id, 'name' => 'T', 'available_for_all_courses' => true]);
        $question = Question::create(['topic_id' => $topic->id, 'question_text' => '1 + 1 = ?', 'score' => 10, 'question_type' => 'multiple_choice_single']);
        $right = QuestionOption::create(['question_id' => $question->id, 'option_text' => '2', 'is_correct' => true]);
        QuestionOption::create(['question_id' => $question->id, 'option_text' => '3', 'is_correct' => false]);
        $quiz->questions()->attach($question->id, ['order' => 1]);

        $first = $this->asStudent()->post(route('student.quiz.begin', $quiz));
        $second = $this->asStudent()->post(route('student.quiz.begin', $quiz));
        $this->assertSame(1, QuizAttempt::where('student_id', $this->student->id)->count());
        $this->assertSame($first->headers->get('Location'), $second->headers->get('Location'));

        $attempt = QuizAttempt::where('student_id', $this->student->id)->firstOrFail();
        $payload = ['answers' => [$question->id => $right->id]];
        $this->asStudent()->post(route('student.quiz.submit', $attempt), $payload)
            ->assertRedirect(route('student.quiz.result', $attempt->id));
        $this->asStudent()->post(route('student.quiz.submit', $attempt), $payload)
            ->assertRedirect(route('student.quiz.result', $attempt->id));

        $this->assertSame(1, StudentAnswer::where('attempt_id', $attempt->id)->count());
        $this->assertSame('passed', $attempt->fresh()->status);
        $this->assertSame(1, $this->pointsFor($lesson));

        // max_attempts = 1 is reached now
        $this->asStudent()->post(route('student.quiz.begin', $quiz))
            ->assertRedirect(route('student.quiz.start', $quiz))
            ->assertSessionHas('error');
    }
}
