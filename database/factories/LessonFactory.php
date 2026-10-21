<?php

namespace Database\Factories;

use App\Models\Lesson;
use App\Models\LessonArticle;
use App\Models\Module;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lesson>
 */
class LessonFactory extends Factory
{
    protected $model = Lesson::class;

    public function definition(): array
    {
        $article = LessonArticle::create([
            'content' => fake()->paragraphs(3, true),
        ]);

        return [
            'module_id' => Module::factory(),
            'title' => fake()->sentence(3),
            'order' => 1,
            'lessonable_type' => LessonArticle::class,
            'lessonable_id' => $article->id,
        ];
    }

    public function forQuiz(?Quiz $quiz = null): static
    {
        return $this->state(function (array $attributes) use ($quiz) {
            $quizModel = $quiz ?? Quiz::factory()->create();

            return [
                'lessonable_type' => Quiz::class,
                'lessonable_id' => $quizModel->id,
            ];
        });
    }
}
