<?php

namespace Tests\Concerns;

use App\Models\AssignmentSubmission;
use App\Models\Badge;
use App\Models\BadgeRule;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonAssignment;
use App\Models\Module;
use App\Models\Question;
use App\Models\QuestionTopic;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;

trait BuildsBadgeFixtures
{
    protected function makeCourseWithModule(array $courseAttributes = []): array
    {
        $instructor = $this->makeInstructor();
        $course = Course::factory()->create(array_merge([
            'instructor_id' => $instructor->id,
            'status' => 'published',
        ], $courseAttributes));
        $module = Module::factory()->create(['course_id' => $course->id, 'points_required' => 0]);

        return [$course, $module];
    }

    protected function makeQuizIn(Module $module): Quiz
    {
        $quiz = Quiz::factory()->create();
        Lesson::factory()->create([
            'module_id' => $module->id,
            'lessonable_id' => $quiz->id,
            'lessonable_type' => Quiz::class,
        ]);

        return $quiz;
    }

    /**
     * Beri kuis soal dengan total skor $maxScore. revised_score adalah skor mentah dan dibandingkan dengan nilai ini.
     */
    protected function giveQuizMaxScore(Quiz $quiz, int $maxScore): Quiz
    {
        $instructorId = $quiz->lesson?->module?->course?->instructor_id ?? $this->makeInstructor()->id;
        $topic = QuestionTopic::create(['instructor_id' => $instructorId, 'name' => 'Topik', 'available_for_all_courses' => true]);
        $question = Question::create(['topic_id' => $topic->id, 'question_text' => 'Soal', 'score' => $maxScore, 'question_type' => 'multiple_choice_single']);
        $quiz->questions()->attach($question->id, ['order' => 1]);

        return $quiz;
    }

    protected function passQuiz(User $student, Quiz $quiz, float $score = 80): QuizAttempt
    {
        return QuizAttempt::factory()->passed($score)->create([
            'quiz_id' => $quiz->id,
            'student_id' => $student->id,
        ]);
    }

    protected function makeAssignmentIn(Module $module): LessonAssignment
    {
        $assignment = LessonAssignment::create(['instructions' => 'Kerjakan', 'pass_mark' => 70]);
        Lesson::factory()->create([
            'module_id' => $module->id,
            'lessonable_id' => $assignment->id,
            'lessonable_type' => LessonAssignment::class,
        ]);

        return $assignment;
    }

    protected function submitAssignment(User $student, LessonAssignment $assignment, string $status = 'passed'): AssignmentSubmission
    {
        return AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'file_path' => 'assignment_submissions/x.pdf',
            'submitted_at' => now(),
            'grade' => $status === 'passed' ? 90 : 40,
            'status' => $status,
        ]);
    }

    protected function makeBadge(array $rules = [], array $attributes = []): Badge
    {
        $badge = Badge::factory()->create($attributes);

        foreach ($rules as $position => $rule) {
            BadgeRule::factory()->create(array_merge([
                'badge_id' => $badge->id,
                'position' => $position,
            ], $rule));
        }

        return $badge->load('rules');
    }
}
