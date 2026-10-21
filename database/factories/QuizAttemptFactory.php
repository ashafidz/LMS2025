<?php

namespace Database\Factories;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuizAttempt>
 */
class QuizAttemptFactory extends Factory
{
    protected $model = QuizAttempt::class;

    public function definition(): array
    {
        return [
            'quiz_id' => Quiz::factory(),
            'student_id' => User::factory(),
            'score' => null,
            'status' => 'in_progress',
            'start_time' => now(),
            'end_time' => null,
        ];
    }

    public function passed(float $score = 100): static
    {
        return $this->state(fn (array $attributes) => [
            'score' => $score,
            'scaled_score' => $score,
            'status' => 'passed',
            'end_time' => now(),
        ]);
    }

    public function failed(float $score = 40): static
    {
        return $this->state(fn (array $attributes) => [
            'score' => $score,
            'scaled_score' => $score,
            'status' => 'failed',
            'end_time' => now(),
        ]);
    }
}
