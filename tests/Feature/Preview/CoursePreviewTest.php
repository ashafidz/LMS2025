<?php

namespace Tests\Feature\Preview;

use App\Models\Course;
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
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

/**
 * Preview mode (course owner or admin) must never write anything on behalf of the user.
 */
class CoursePreviewTest extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    private User $owner;

    private Course $course;

    private Module $module;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        $this->owner = $this->makeInstructor();
        $this->owner->assignRole('student'); // dual role, as in real life
        $this->course = $this->makeCourse($this->owner);
        $this->module = $this->makeModule($this->course);
        // The owner is also enrolled as a student: the case that used to write real data.
        $this->enroll($this->owner, $this->course);
    }

    private function asOwner()
    {
        return $this->actingAs($this->owner)->withSession(['active_role' => 'instructor']);
    }

    private function lessonFor($lessonable): Lesson
    {
        return Lesson::factory()->create([
            'module_id' => $this->module->id,
            'lessonable_type' => $lessonable::class,
            'lessonable_id' => $lessonable->id,
        ]);
    }

    private function makeQuizLesson(array $quizAttributes = []): Quiz
    {
        $quiz = Quiz::factory()->create($quizAttributes + ['pass_mark' => 70]);
        $this->lessonFor($quiz);
        $topic = QuestionTopic::create(['instructor_id' => $this->owner->id, 'name' => 'Topik', 'available_for_all_courses' => true]);
        $question = Question::create(['topic_id' => $topic->id, 'question_text' => '2 + 2 = ?', 'score' => 100, 'question_type' => 'multiple_choice_single']);
        QuestionOption::create(['question_id' => $question->id, 'option_text' => '4', 'is_correct' => true]);
        QuestionOption::create(['question_id' => $question->id, 'option_text' => '5', 'is_correct' => false]);
        $quiz->questions()->attach($question->id, ['order' => 1]);

        return $quiz;
    }

    private function runningAttempt(Quiz $quiz): QuizAttempt
    {
        return QuizAttempt::create(['quiz_id' => $quiz->id, 'student_id' => $this->owner->id, 'status' => 'in_progress', 'start_time' => now()]);
    }

    public function test_quiz_preview_ignores_the_owners_running_attempt(): void
    {
        $quiz = $this->makeQuizLesson();
        $attempt = $this->runningAttempt($quiz);

        $this->asOwner()->get(route('student.quiz.start', ['quiz' => $quiz, 'preview' => 'true']))
            ->assertOk()
            ->assertViewIs('student.quizzes.start');

        $before = QuizAttempt::count();
        $this->asOwner()->post(route('student.quiz.begin', $quiz), ['is_preview' => 'true'])
            ->assertOk()
            ->assertViewIs('student.quizzes.take');

        $this->assertSame($before, QuizAttempt::count());
        $this->assertSame('in_progress', $attempt->fresh()->status);
    }

    public function test_quiz_preview_ignores_the_attempt_limit(): void
    {
        $quiz = $this->makeQuizLesson(['max_attempts' => 1]);
        QuizAttempt::create(['quiz_id' => $quiz->id, 'student_id' => $this->owner->id, 'status' => 'failed', 'start_time' => now()->subHour()]);

        $this->asOwner()->post(route('student.quiz.begin', $quiz), ['is_preview' => 'true'])
            ->assertOk()
            ->assertViewIs('student.quizzes.take');
    }

    public function test_polling_and_wordcloud_submit_in_preview_save_nothing(): void
    {
        $polling = LessonPolling::create(['question' => 'Pilih satu', 'is_active' => true, 'allow_multiple' => false, 'show_results' => true]);
        $option = LessonPollingOption::create(['lesson_polling_id' => $polling->id, 'text' => 'A', 'order' => 1]);
        $pollLesson = $this->lessonFor($polling);
        $cloud = LessonWordcloud::create(['question' => 'Satu kata', 'max_words' => 1, 'is_active' => true]);
        $cloudLesson = $this->lessonFor($cloud);

        $this->asOwner()->postJson(route('student.lessons.polling.submit', $pollLesson).'?preview=true', ['polling_option_id' => $option->id])
            ->assertStatus(422)->assertJson(['success' => false]);
        $this->asOwner()->postJson(route('student.lessons.wordcloud.submit', $cloudLesson).'?preview=true', ['word' => 'Belajar'])
            ->assertStatus(422)->assertJson(['success' => false]);

        $this->assertSame(0, DB::table('lesson_polling_responses')->count());
        $this->assertSame(0, $cloud->responses()->count());
        $this->assertSame(0, PointHistory::where('user_id', $this->owner->id)->count());
    }

    public function test_polling_still_works_for_a_normal_student(): void
    {
        $student = $this->makeStudent();
        $this->enroll($student, $this->course);
        $polling = LessonPolling::create(['question' => 'Pilih satu', 'is_active' => true, 'allow_multiple' => false, 'show_results' => true]);
        $option = LessonPollingOption::create(['lesson_polling_id' => $polling->id, 'text' => 'A', 'order' => 1]);
        $lesson = $this->lessonFor($polling);

        $this->actingAs($student)->withSession(['active_role' => 'student'])
            ->postJson(route('student.lessons.polling.submit', $lesson), ['polling_option_id' => $option->id])
            ->assertOk()->assertJson(['success' => true]);
        $this->assertSame(1, DB::table('lesson_polling_responses')->where('user_id', $student->id)->count());
    }

    public function test_preview_content_for_polling_wordcloud_and_assignment_is_disabled(): void
    {
        $polling = LessonPolling::create(['question' => 'Pilih satu', 'is_active' => true, 'allow_multiple' => false, 'show_results' => true]);
        LessonPollingOption::create(['lesson_polling_id' => $polling->id, 'text' => 'A', 'order' => 1]);
        $cloud = LessonWordcloud::create(['question' => 'Satu kata', 'max_words' => 1, 'is_active' => true]);
        $assignment = LessonAssignment::create(['instructions' => 'Kumpulkan', 'pass_mark' => 70]);

        foreach ([
            [$polling, 'Siswa akan memilih jawaban di sini'],
            [$cloud, 'Siswa akan menulis kata di sini'],
            [$assignment, 'Ini adalah pratinjau'],
        ] as [$lessonable, $note]) {
            $lesson = $this->lessonFor($lessonable);
            $html = $this->asOwner()->getJson(route('student.lessons.content', $lesson).'?preview=true')
                ->assertOk()->json('html');
            $this->assertStringContainsString('disabled', $html);
            $this->assertStringContainsString($note, $html);
            $this->assertStringNotContainsString('type="file"', $html);
        }
    }

    public function test_lesson_manager_modal_has_no_live_assignment_form(): void
    {
        $lesson = $this->lessonFor(LessonAssignment::create(['instructions' => 'Kumpulkan', 'pass_mark' => 70]));

        $this->asOwner()->get(route('instructor.modules.lessons.index', $this->module))
            ->assertOk()
            ->assertDontSee('Kirim Tugas Sekarang')
            ->assertSee('Ini adalah pratinjau');
        $this->assertNotNull($lesson->id);
    }

    public function test_course_preview_shows_no_progress_points_or_active_feedback(): void
    {
        $lesson = Lesson::factory()->create(['module_id' => $this->module->id]);
        $this->owner->completedLessons()->attach($lesson->id);

        $response = $this->asOwner()->get(route('student.courses.show', ['course' => $this->course->slug, 'preview' => 'true']))
            ->assertOk();

        $response->assertSee('id="toc-progress-done">0</span> dari', false);
        $response->assertDontSee('Pointmu');
        $response->assertSee('Tidak tersedia di mode pratinjau');
        $this->assertMatchesRegularExpression('/id="load-review-form"\s+disabled/', $response->getContent());
    }

    public function test_quiz_preview_result_shows_answer_breakdown_even_when_hidden_from_students(): void
    {
        $quiz = $this->makeQuizLesson(['reveal_answers' => false]);
        $question = $quiz->questions()->first();

        $this->asOwner()->post(route('student.quiz.submit', 0), [
            'is_preview' => 'true',
            'quiz_id_preview' => $quiz->id,
            'answers' => [$question->id => $question->options()->where('is_correct', true)->value('id')],
        ])->assertOk()
            ->assertSee('Rincian Jawaban')
            ->assertSee('Hasil Pratinjau Kuis')
            ->assertDontSee('disembunyikan oleh penyelenggara');
    }

    public function test_admin_preview_breadcrumbs_use_admin_pages(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->withSession(['active_role' => 'admin'])
            ->get(route('student.courses.show', ['course' => $this->course->slug, 'preview' => 'true']))
            ->assertOk()
            ->assertSee(route('admin.dashboard'), false)
            ->assertSee(route('admin.publication.index'), false)
            ->assertDontSee(route('instructor.courses.index'), false);
    }

    public function test_course_owner_cannot_review_their_own_course(): void
    {
        $this->asOwner()->postJson(route('student.course.review.store', $this->course), ['course_rating' => 5])
            ->assertForbidden();
    }
}
