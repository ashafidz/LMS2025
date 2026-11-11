<?php

namespace Tests\Feature\Instructor;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Question;
use App\Models\QuestionTopic;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizBulkDetachTest extends TestCase
{
    use RefreshDatabase;

    private User $instructor;

    private Quiz $quiz;

    /** @var array<int, Question> */
    private array $questions = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->instructor = $this->makeInstructor();
        $this->quiz = $this->quizFor($this->instructor);

        $topic = QuestionTopic::create([
            'instructor_id' => $this->instructor->id,
            'name' => 'Topik Uji',
            'available_for_all_courses' => true,
        ]);
        foreach (range(1, 3) as $i) {
            $this->questions[] = Question::create([
                'topic_id' => $topic->id,
                'question_text' => 'Soal '.$i,
                'question_type' => 'multiple_choice_single',
                'score' => 10,
            ]);
        }
        $this->quiz->questions()->attach(collect($this->questions)->pluck('id'));
    }

    private function quizFor(User $instructor): Quiz
    {
        $course = Course::factory()->create(['instructor_id' => $instructor->id]);
        $module = Module::factory()->create(['course_id' => $course->id]);
        $quiz = Quiz::factory()->create();
        Lesson::factory()->forQuiz($quiz)->create(['module_id' => $module->id]);

        return $quiz;
    }

    private function asInstructor(?User $user = null): static
    {
        return $this->actingAs($user ?? $this->instructor)->withSession(['active_role' => 'instructor']);
    }

    public function test_manage_page_shows_checklist_and_bulk_button(): void
    {
        $this->asInstructor()->get(route('instructor.quizzes.manage_questions', $this->quiz))
            ->assertOk()
            ->assertSee('id="select-all-questions"', false)
            ->assertSee('form="bulk-detach-form"', false)
            ->assertSee('Hapus Terpilih');
    }

    public function test_selected_questions_are_detached_and_others_stay(): void
    {
        $otherQuiz = $this->quizFor($this->instructor);
        $otherQuiz->questions()->attach($this->questions[0]->id);

        $this->asInstructor()
            ->from(route('instructor.quizzes.manage_questions', $this->quiz))
            ->delete(route('instructor.quizzes.detach_questions', $this->quiz), [
                'question_ids' => [$this->questions[0]->id, $this->questions[1]->id],
            ])
            ->assertRedirect(route('instructor.quizzes.manage_questions', $this->quiz))
            ->assertSessionHas('success', '2 soal berhasil dihapus dari kuis.');

        $this->assertEquals([$this->questions[2]->id], $this->quiz->questions()->pluck('questions.id')->all());
        // Soal tetap ada di bank dan di kuis lain.
        $this->assertNotNull($this->questions[0]->fresh());
        $this->assertTrue($otherQuiz->questions()->whereKey($this->questions[0]->id)->exists());
    }

    public function test_empty_selection_is_rejected(): void
    {
        $this->asInstructor()
            ->delete(route('instructor.quizzes.detach_questions', $this->quiz), ['question_ids' => []])
            ->assertSessionHasErrors('question_ids');

        $this->assertSame(3, $this->quiz->questions()->count());
    }

    public function test_other_instructor_cannot_bulk_detach(): void
    {
        $other = $this->makeInstructor();

        $this->asInstructor($other)
            ->delete(route('instructor.quizzes.detach_questions', $this->quiz), [
                'question_ids' => [$this->questions[0]->id],
            ])
            ->assertForbidden();

        $this->assertSame(3, $this->quiz->questions()->count());
    }
}
