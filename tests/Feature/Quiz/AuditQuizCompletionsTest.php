<?php

namespace Tests\Feature\Quiz;

use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

class AuditQuizCompletionsTest extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    private function quizLesson($module): array
    {
        $quiz = Quiz::factory()->create();
        $lesson = Lesson::factory()->create([
            'module_id' => $module->id,
            'lessonable_type' => Quiz::class,
            'lessonable_id' => $quiz->id,
        ]);

        return [$quiz, $lesson];
    }

    public function test_report_lists_only_completions_without_a_passed_attempt_and_changes_nothing(): void
    {
        $course = $this->makeCourse($this->makeInstructor());
        $module = $this->makeModule($course);
        [$quizOk, $lessonOk] = $this->quizLesson($module);
        [, $lessonBad] = $this->quizLesson($module);
        $honest = $this->makeStudent(['email' => 'jujur@example.test']);
        $cheater = $this->makeStudent(['email' => 'curang@example.test']);

        QuizAttempt::factory()->passed()->create(['quiz_id' => $quizOk->id, 'student_id' => $honest->id]);
        $honest->completedLessons()->attach($lessonOk->id);
        $cheater->completedLessons()->attach($lessonBad->id);
        $before = DB::table('lesson_user')->count();

        $this->assertSame(0, Artisan::call('lms:audit-quiz-completions'));
        $output = Artisan::output();

        $this->assertStringContainsString('curang@example.test', $output);
        $this->assertStringNotContainsString('jujur@example.test', $output);
        $this->assertStringContainsString('Total completion tidak sah: 1', $output);
        $this->assertSame($before, DB::table('lesson_user')->count());
    }

    public function test_report_is_empty_when_all_completions_are_legit(): void
    {
        $this->assertSame(0, Artisan::call('lms:audit-quiz-completions'));
        $this->assertStringContainsString('Tidak ada completion', Artisan::output());
    }
}
