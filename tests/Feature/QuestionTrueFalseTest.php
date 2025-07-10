<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\QuestionTopic;
use App\Models\Question;

class QuestionTrueFalseTest extends TestCase
{
    use RefreshDatabase;

    protected $instructor;
    protected $topic;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an instructor user
        $this->instructor = User::factory()->create([
            'is_instructor' => true,
        ]);

        // Create a question topic for the instructor
        $this->topic = QuestionTopic::factory()->create([
            'instructor_id' => $this->instructor->id,
        ]);

        // Authenticate as the instructor
        $this->actingAs($this->instructor);
    }

    /** @test */
    public function instructor_can_create_true_false_question_with_correct_answer(){
        $response = $this->post(route('instructor.question-bank.questions.store', $this->topic->id), [
            'question_text' => 'This is a true/false question.',
            'score' => 10,
            'explanation' => 'This is the explanation for the true/false question.',
            'question_type' => 'true_false',
            'true_false_answer' => 'true',
            'options' => [
                ['text' => 'true', 'is_correct' => true],
                ['text' => 'false', 'is_correct' => false],
            ],
        ]);

        $response->assertRedirect(route('instructor.question-bank.questions.index', $this->topic->id));
        $response->assertSessionHas('success', 'Question created successfully.');

        $this->assertDatabaseHas('questions', [
            'question_text' => 'This is a true/false question.',
            'score' => 10,
            'explanation' => 'This is the explanation for the true/false question.',
            'question_type' => 'true_false',
            'topic_id' => $this->topic->id,
        ]);

        $question = Question::where('question_text', 'This is a true/false question.')->first();
        $this->assertNotNull($question);

        $this->assertDatabaseHas('question_options', [
            'question_id' => $question->id,
            'option_text' => 'true',
            'is_correct' => true,
        ]);

        $this->assertDatabaseHas('question_options', [
            'question_id' => $question->id,
            'option_text' => 'false',
            'is_correct' => false,
        ]);
    }

    /** @test */
    public function instructor_can_update_true_false_question_with_correct_answer()
    {
        $question = Question::factory()->create([
            'topic_id' => $this->topic->id,
            'question_type' => 'true_false',
            'question_text' => 'Original true/false question.',
            'score' => 5,
            'explanation' => 'Original explanation.',
        ]);

        $question->options()->create(['option_text' => 'true', 'is_correct' => true]);
        $question->options()->create(['option_text' => 'false', 'is_correct' => false]);

        $response = $this->put(route('instructor.question-bank.questions.update', $question->id), [
            'question_text' => 'Updated true/false question.',
            'score' => 15,
            'explanation' => 'Updated explanation.',
            'question_type' => 'true_false',
            'true_false_answer' => 'false',
            'options' => [
                ['text' => 'true', 'is_correct' => false],
                ['text' => 'false', 'is_correct' => true],
            ],
        ]);

        $response->assertRedirect(route('instructor.question-bank.questions.index', $this->topic->id));
        $response->assertSessionHas('success', 'Question updated successfully.');

        $this->assertDatabaseHas('questions', [
            'id' => $question->id,
            'question_text' => 'Updated true/false question.',
            'score' => 15,
            'explanation' => 'Updated explanation.',
            'question_type' => 'true_false',
        ]);

        $this->assertDatabaseHas('question_options', [
            'question_id' => $question->id,
            'option_text' => 'true',
            'is_correct' => false,
        ]);

        $this->assertDatabaseHas('question_options', [
            'question_id' => $question->id,
            'option_text' => 'false',
            'is_correct' => true,
        ]);
    }
}
