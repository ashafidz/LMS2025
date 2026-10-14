<?php

namespace Database\Factories;

use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quiz>
 */
class QuizFactory extends Factory
{
    protected $model = Quiz::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'pass_mark' => 70,
            'time_limit' => 60,
            'allow_exceed_time_limit' => false,
            'reveal_answers' => false,
            'max_attempts' => null,
        ];
    }
}
