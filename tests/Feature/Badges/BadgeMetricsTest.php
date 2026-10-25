<?php

namespace Tests\Feature\Badges;

use App\Models\Certificate;
use App\Models\CourseCategory;
use App\Models\CourseReview;
use App\Models\CourseUser;
use App\Models\Lesson;
use App\Models\LessonDiscussion;
use App\Models\PointHistory;
use App\Support\Badges\BadgeMetrics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsBadgeFixtures;
use Tests\TestCase;

class BadgeMetricsTest extends TestCase
{
    use BuildsBadgeFixtures;
    use RefreshDatabase;

    public function test_every_metric_has_label_unit_and_scopes(): void
    {
        foreach (BadgeMetrics::keys() as $key) {
            $this->assertNotSame('', BadgeMetrics::label($key));
            $this->assertNotSame('', BadgeMetrics::unit($key));
            $this->assertContains('all', BadgeMetrics::scopesFor($key));
        }
        $this->assertSame([], BadgeMetrics::scopesFor('tidak_ada'));
    }

    public function test_quiz_passed_counts_distinct_quizzes_not_attempts(): void
    {
        $student = $this->makeStudent();
        [, $module] = $this->makeCourseWithModule();
        $quizA = $this->makeQuizIn($module);
        $quizB = $this->makeQuizIn($module);

        $this->passQuiz($student, $quizA);
        $this->passQuiz($student, $quizA);
        $this->passQuiz($student, $quizA);
        $this->assertSame(1, BadgeMetrics::count('quiz_passed', $student, 'all', null));

        $this->passQuiz($student, $quizB);
        $this->assertSame(2, BadgeMetrics::count('quiz_passed', $student, 'all', null));
    }

    public function test_quiz_passed_ignores_failed_and_in_progress_attempts(): void
    {
        $student = $this->makeStudent();
        [, $module] = $this->makeCourseWithModule();
        $quiz = $this->makeQuizIn($module);

        \App\Models\QuizAttempt::factory()->failed()->create(['quiz_id' => $quiz->id, 'student_id' => $student->id]);
        \App\Models\QuizAttempt::factory()->create(['quiz_id' => $quiz->id, 'student_id' => $student->id]);

        $this->assertSame(0, BadgeMetrics::count('quiz_passed', $student, 'all', null));
    }

    public function test_quiz_passed_respects_course_and_category_scope(): void
    {
        $student = $this->makeStudent();
        $category = CourseCategory::factory()->create();
        [$courseA, $moduleA] = $this->makeCourseWithModule(['category_id' => $category->id]);
        [$courseB, $moduleB] = $this->makeCourseWithModule();

        $this->passQuiz($student, $this->makeQuizIn($moduleA));
        $this->passQuiz($student, $this->makeQuizIn($moduleB));

        $this->assertSame(2, BadgeMetrics::count('quiz_passed', $student, 'all', null));
        $this->assertSame(1, BadgeMetrics::count('quiz_passed', $student, 'course', $courseA->id));
        $this->assertSame(1, BadgeMetrics::count('quiz_passed', $student, 'course', $courseB->id));
        $this->assertSame(1, BadgeMetrics::count('quiz_passed', $student, 'category', $category->id));
    }

    public function test_quiz_perfect_counts_only_full_scores(): void
    {
        $student = $this->makeStudent();
        [, $module] = $this->makeCourseWithModule();
        $this->passQuiz($student, $this->makeQuizIn($module), 100);
        $this->passQuiz($student, $this->makeQuizIn($module), 90);

        $this->assertSame(1, BadgeMetrics::count('quiz_perfect', $student, 'all', null));
    }

    public function test_quiz_perfect_uses_scaled_score_and_raw_instructor_revision_against_quiz_max(): void
    {
        $student = $this->makeStudent();
        [, $module] = $this->makeCourseWithModule();

        // Tanpa revisi: skala 100 dari nilai mentah 10/10
        $perfect = $this->passQuiz($student, $this->giveQuizMaxScore($this->makeQuizIn($module), 10), 100);
        $perfect->update(['score' => 10, 'scaled_score' => 100]);

        // Skala 85, instruktur merevisi skor mentah menjadi 10 dari 10 -> sempurna
        $revisedUp = $this->passQuiz($student, $this->giveQuizMaxScore($this->makeQuizIn($module), 10), 85);
        $revisedUp->update(['score' => 8.5, 'scaled_score' => 85, 'revised_score' => 10]);

        // Skala 100, instruktur menurunkan skor mentah menjadi 9 dari 10 -> tidak sempurna
        $revisedDown = $this->passQuiz($student, $this->giveQuizMaxScore($this->makeQuizIn($module), 10), 100);
        $revisedDown->update(['score' => 10, 'scaled_score' => 100, 'revised_score' => 9]);

        $this->assertSame(2, BadgeMetrics::count('quiz_perfect', $student, 'all', null));

        // Kuis bermaksimum 200: revisi 150 (75%) bukan sempurna walau angkanya >= 100
        $bigQuiz = $this->passQuiz($student, $this->giveQuizMaxScore($this->makeQuizIn($module), 200), 75);
        $bigQuiz->update(['score' => 150, 'scaled_score' => 75, 'revised_score' => 150]);

        $this->assertSame(2, BadgeMetrics::count('quiz_perfect', $student, 'all', null));
    }

    public function test_lessons_completed_with_scope(): void
    {
        $student = $this->makeStudent();
        [$courseA, $moduleA] = $this->makeCourseWithModule();
        [, $moduleB] = $this->makeCourseWithModule();
        $lessonA = $this->makeArticleLessonFor($moduleA);
        $lessonB = $this->makeArticleLessonFor($moduleB);
        $student->completedLessons()->attach([$lessonA->id, $lessonB->id]);

        $this->assertSame(2, BadgeMetrics::count('lessons_completed', $student, 'all', null));
        $this->assertSame(1, BadgeMetrics::count('lessons_completed', $student, 'course', $courseA->id));
    }

    public function test_certificates_earned_with_category_scope(): void
    {
        $student = $this->makeStudent();
        $category = CourseCategory::factory()->create();
        [$courseA] = $this->makeCourseWithModule(['category_id' => $category->id]);
        [$courseB] = $this->makeCourseWithModule();
        foreach ([$courseA, $courseB] as $course) {
            Certificate::create([
                'user_id' => $student->id,
                'course_id' => $course->id,
                'certificate_code' => 'LMS-'.$course->id,
                'issued_at' => now(),
            ]);
        }

        $this->assertSame(2, BadgeMetrics::count('certificates_earned', $student, 'all', null));
        $this->assertSame(1, BadgeMetrics::count('certificates_earned', $student, 'category', $category->id));
        $this->assertNotContains('course', BadgeMetrics::scopesFor('certificates_earned'));
    }

    public function test_assignments_passed_counts_only_passed(): void
    {
        $student = $this->makeStudent();
        [$course, $module] = $this->makeCourseWithModule();
        $this->submitAssignment($student, $this->makeAssignmentIn($module), 'passed');
        $this->submitAssignment($student, $this->makeAssignmentIn($module), 'revision_required');

        $this->assertSame(1, BadgeMetrics::count('assignments_passed', $student, 'all', null));
        $this->assertSame(1, BadgeMetrics::count('assignments_passed', $student, 'course', $course->id));
    }

    public function test_points_total_all_sums_only_earned_points_and_course_uses_course_total(): void
    {
        $student = $this->makeStudent();
        [$course] = $this->makeCourseWithModule();

        PointHistory::create(['user_id' => $student->id, 'course_id' => $course->id, 'points' => 30, 'description' => 'a']);
        PointHistory::create(['user_id' => $student->id, 'course_id' => $course->id, 'points' => 20, 'description' => 'b']);
        PointHistory::create(['user_id' => $student->id, 'course_id' => null, 'points' => -10, 'description' => 'dipakai']);
        CourseUser::create(['user_id' => $student->id, 'course_id' => $course->id, 'points_earned' => 50]);

        $this->assertSame(50, BadgeMetrics::count('points_total', $student, 'all', null));
        $this->assertSame(50, BadgeMetrics::count('points_total', $student, 'course', $course->id));
        $this->assertNotContains('category', BadgeMetrics::scopesFor('points_total'));
    }

    public function test_reviews_written_with_category_scope(): void
    {
        $student = $this->makeStudent();
        $category = CourseCategory::factory()->create();
        [$courseA] = $this->makeCourseWithModule(['category_id' => $category->id]);
        [$courseB] = $this->makeCourseWithModule();
        CourseReview::create(['course_id' => $courseA->id, 'user_id' => $student->id, 'rating' => 5, 'comment' => 'bagus']);
        CourseReview::create(['course_id' => $courseB->id, 'user_id' => $student->id, 'rating' => 4, 'comment' => 'ok']);

        $this->assertSame(2, BadgeMetrics::count('reviews_written', $student, 'all', null));
        $this->assertSame(1, BadgeMetrics::count('reviews_written', $student, 'category', $category->id));
    }

    public function test_discussions_posted_skips_deleted_comments(): void
    {
        $student = $this->makeStudent();
        [$course, $module] = $this->makeCourseWithModule();
        $lesson = $this->makeArticleLessonFor($module);
        LessonDiscussion::create(['lesson_id' => $lesson->id, 'user_id' => $student->id, 'content' => 'halo']);
        LessonDiscussion::create(['lesson_id' => $lesson->id, 'user_id' => $student->id, 'content' => 'dihapus', 'is_deleted' => true]);

        $this->assertSame(1, BadgeMetrics::count('discussions_posted', $student, 'all', null));
        $this->assertSame(1, BadgeMetrics::count('discussions_posted', $student, 'course', $course->id));
    }

    public function test_courses_enrolled_uses_enrollment_table(): void
    {
        $student = $this->makeStudent();
        $category = CourseCategory::factory()->create();
        [$courseA] = $this->makeCourseWithModule(['category_id' => $category->id]);
        [$courseB] = $this->makeCourseWithModule();
        $this->enroll($student, $courseA);
        $this->enroll($student, $courseB);

        $this->assertSame(2, BadgeMetrics::count('courses_enrolled', $student, 'all', null));
        $this->assertSame(1, BadgeMetrics::count('courses_enrolled', $student, 'category', $category->id));
    }

    public function test_deleted_course_or_category_scope_returns_zero_without_error(): void
    {
        $student = $this->makeStudent();

        foreach (BadgeMetrics::keys() as $key) {
            foreach (['course', 'category'] as $scope) {
                if (in_array($scope, BadgeMetrics::scopesFor($key), true)) {
                    $this->assertSame(0, BadgeMetrics::count($key, $student, $scope, 999999), "$key/$scope");
                }
            }
        }
    }

    public function test_sentence_is_plain_indonesian_with_names_not_ids(): void
    {
        [$course] = $this->makeCourseWithModule(['title' => 'Dasar Laravel']);
        $category = CourseCategory::factory()->create(['name' => 'Pemrograman']);

        $this->assertSame('Lulus kuis minimal 10 kuis di semua kursus', BadgeMetrics::sentence('quiz_passed', 10, 'all', null));
        $this->assertSame('Lulus kuis minimal 3 kuis di kursus Dasar Laravel', BadgeMetrics::sentence('quiz_passed', 3, 'course', $course->id));
        $this->assertSame('Menulis ulasan kursus minimal 2 ulasan di kategori Pemrograman', BadgeMetrics::sentence('reviews_written', 2, 'category', $category->id));
        $this->assertStringContainsString('yang sudah dihapus', BadgeMetrics::sentence('quiz_passed', 3, 'course', 999999));
    }

    private function makeArticleLessonFor($module): Lesson
    {
        return Lesson::factory()->create(['module_id' => $module->id]);
    }
}
