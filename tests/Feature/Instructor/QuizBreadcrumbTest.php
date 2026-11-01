<?php

namespace Tests\Feature\Instructor;

use App\Models\Lesson;
use App\Models\Quiz;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

class QuizBreadcrumbTest extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    private function setUpQuiz(): array
    {
        $instructor = $this->makeInstructor();
        $course = $this->makeCourse($instructor);
        $module = $this->makeModule($course);
        $quiz = Quiz::factory()->create();
        Lesson::factory()->create([
            'module_id' => $module->id,
            'lessonable_type' => Quiz::class,
            'lessonable_id' => $quiz->id,
        ]);

        return [$instructor, $course, $quiz];
    }

    private function assertBreadcrumbLinks($response, $course): void
    {
        $response->assertOk();
        $response->assertSee('href="'.route('instructor.dashboard').'"', false);
        $response->assertSee('href="'.route('instructor.courses.index').'"', false);
        $response->assertSee('href="'.route('instructor.courses.modules.index', $course).'"', false);
    }

    public function test_manage_questions_breadcrumb_links_to_courses_and_modules(): void
    {
        [$instructor, $course, $quiz] = $this->setUpQuiz();

        $response = $this->actingAs($instructor)->withSession(['active_role' => 'instructor'])
            ->get(route('instructor.quizzes.manage_questions', $quiz));

        $this->assertBreadcrumbLinks($response, $course);
    }

    public function test_quiz_results_breadcrumb_links_to_courses_and_modules(): void
    {
        [$instructor, $course, $quiz] = $this->setUpQuiz();

        $response = $this->actingAs($instructor)->withSession(['active_role' => 'instructor'])
            ->get(route('instructor.quiz.results', $quiz));

        $this->assertBreadcrumbLinks($response, $course);
    }
}
