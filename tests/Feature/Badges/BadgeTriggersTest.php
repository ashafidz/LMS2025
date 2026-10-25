<?php

namespace Tests\Feature\Badges;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Lesson;
use App\Models\LessonLinkCollection;
use App\Models\LikertQuestion;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionTopic;
use App\Models\SiteSetting;
use App\Services\DiamondService;
use App\Services\OrderSettlementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsBadgeFixtures;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

/**
 * Setiap titik yang mengubah progres siswa harus memicu pemeriksaan badge.
 * Antrean memakai driver sync di test, jadi badge langsung terlihat setelah aksi.
 */
class BadgeTriggersTest extends TestCase
{
    use BuildsBadgeFixtures;
    use BuildsLessonFixtures {
        BuildsLessonFixtures::enroll insteadof BuildsBadgeFixtures;
    }
    use RefreshDatabase;

    private function badgeFor(string $metric, int $target = 1, string $scope = 'all', ?int $scopeId = null)
    {
        return $this->makeBadge([['metric' => $metric, 'target' => $target, 'scope' => $scope, 'scope_id' => $scopeId]]);
    }

    private function assertHasBadge($student, $badge): void
    {
        $this->assertDatabaseHas('badge_user', ['user_id' => $student->id, 'badge_id' => $badge->id]);
    }

    public function test_completing_a_lesson_awards_badge_even_when_it_gives_no_points(): void
    {
        $student = $this->makeStudent();
        $course = $this->makeCourse($this->makeInstructor());
        $module = $this->makeModule($course);
        $lesson = $this->makeArticleLesson($module);
        $this->enroll($student, $course);
        $badge = $this->badgeFor('lessons_completed');

        $this->actingAs($student)->postJson(route('student.lessons.complete', $lesson))->assertOk();

        $this->assertHasBadge($student, $badge);
    }

    public function test_completing_a_link_collection_lesson_awards_badge(): void
    {
        $student = $this->makeStudent();
        $course = $this->makeCourse($this->makeInstructor());
        $module = $this->makeModule($course);
        $links = LessonLinkCollection::create(['links' => []]);
        $lesson = Lesson::factory()->create([
            'module_id' => $module->id,
            'lessonable_type' => LessonLinkCollection::class,
            'lessonable_id' => $links->id,
        ]);
        $this->enroll($student, $course);
        $badge = $this->badgeFor('lessons_completed');

        $this->actingAs($student)->postJson(route('student.lessons.complete', $lesson))->assertOk();

        $this->assertHasBadge($student, $badge);
    }

    public function test_passing_a_quiz_awards_badge(): void
    {
        $student = $this->makeStudent();
        $course = $this->makeCourse($this->makeInstructor());
        $module = $this->makeModule($course);
        $this->enroll($student, $course);
        $quiz = $this->makeQuizIn($module);
        $topic = QuestionTopic::create(['instructor_id' => $course->instructor_id, 'name' => 'T', 'available_for_all_courses' => true]);
        $question = Question::create(['topic_id' => $topic->id, 'question_text' => '1 + 1 = ?', 'score' => 10, 'question_type' => 'multiple_choice_single']);
        $right = QuestionOption::create(['question_id' => $question->id, 'option_text' => '2', 'is_correct' => true]);
        $quiz->questions()->attach($question->id, ['order' => 1]);
        $quiz->update(['pass_mark' => 50, 'time_limit' => 10, 'max_attempts' => null]);
        $badge = $this->badgeFor('quiz_perfect');

        $this->actingAs($student)->post(route('student.quiz.begin', $quiz));
        $attempt = $student->quizAttempts()->firstOrFail();
        $this->actingAs($student)->post(route('student.quiz.submit', $attempt), ['answers' => [$question->id => $right->id]]);

        $this->assertSame('passed', $attempt->fresh()->status);
        $this->assertHasBadge($student, $badge);
    }

    public function test_instructor_grading_a_passing_assignment_awards_badge(): void
    {
        $instructor = $this->makeInstructor();
        $student = $this->makeStudent();
        $course = $this->makeCourse($instructor);
        $module = $this->makeModule($course);
        $assignment = $this->makeAssignmentIn($module);
        $this->enroll($student, $course);
        $submission = $this->submitAssignment($student, $assignment, 'submitted');
        $submission->update(['grade' => null]);
        $badge = $this->badgeFor('assignments_passed');

        $this->actingAs($instructor)->withSession(['active_role' => 'instructor'])
            ->post(route('instructor.submission.grade', $submission), ['grade' => 95, 'feedback' => 'bagus']);

        $this->assertSame('passed', $submission->fresh()->status);
        $this->assertHasBadge($student, $badge);
    }

    public function test_submitting_a_course_review_awards_badge(): void
    {
        $student = $this->makeStudent();
        $course = $this->makeCourse($this->makeInstructor());
        $this->enroll($student, $course);
        $question = LikertQuestion::create(['question_text' => 'Materi jelas', 'category' => 'course', 'is_active' => true]);
        $badge = $this->badgeFor('reviews_written');

        $this->actingAs($student)->postJson(route('student.course.review.store', $course), [
            'course_rating' => 5,
            'course_comment' => 'Bagus',
            'instructor_rating' => 4,
            'instructor_comment' => 'Ramah',
            'likert_answers' => [$question->id => 3],
        ])->assertOk();

        $this->assertHasBadge($student, $badge);
    }

    public function test_posting_a_discussion_comment_awards_badge(): void
    {
        $student = $this->makeStudent();
        $course = $this->makeCourse($this->makeInstructor());
        $module = $this->makeModule($course);
        $lesson = $this->makeArticleLesson($module);
        $this->enroll($student, $course);
        $badge = $this->badgeFor('discussions_posted');

        $this->actingAs($student)->post(route('student.lessons.discussions.store', $lesson), ['content' => 'Halo semua']);

        $this->assertHasBadge($student, $badge);
    }

    public function test_opening_the_certificate_awards_certificate_badge(): void
    {
        $student = $this->makeStudent();
        $course = $this->makeCourse($this->makeInstructor());
        $module = $this->makeModule($course);
        $lesson = $this->makeArticleLesson($module);
        $this->enroll($student, $course);
        $student->completedLessons()->attach($lesson->id);
        $course->reviews()->create(['user_id' => $student->id, 'rating' => 5, 'comment' => 'ok']);
        $badge = $this->badgeFor('certificates_earned');

        $this->actingAs($student)->getJson(route('student.certificate.preview', $course))->assertOk();

        $this->assertDatabaseHas('certificates', ['user_id' => $student->id, 'course_id' => $course->id]);
        $this->assertHasBadge($student, $badge);
    }

    public function test_paid_order_awards_enrollment_badge(): void
    {
        SiteSetting::create(['site_name' => 'ruangstudi.id', 'points_for_purchase' => 0]);
        cache()->forget('site_settings');
        $student = $this->makeStudent();
        $course = Course::factory()->create();
        $order = Order::factory()->create(['user_id' => $student->id, 'status' => 'pending', 'final_amount' => 1000]);
        OrderItem::create(['order_id' => $order->id, 'course_id' => $course->id, 'price_at_purchase' => 1000]);
        $badge = $this->badgeFor('courses_enrolled');

        app(OrderSettlementService::class)->markPaid($order);

        $this->assertHasBadge($student, $badge);
    }

    public function test_diamond_purchase_awards_enrollment_badge(): void
    {
        $student = $this->makeStudent();
        $student->update(['diamond_balance' => 100]);
        $course = Course::factory()->create(['category_id' => CourseCategory::factory()->create()->id, 'diamond_price' => 50]);
        $badge = $this->badgeFor('courses_enrolled');

        $this->assertTrue(DiamondService::useForPurchase($student, $course));

        $this->assertHasBadge($student, $badge);
    }

    public function test_admin_enrolling_students_awards_enrollment_badge(): void
    {
        $admin = $this->makeAdmin();
        $student = $this->makeStudent();
        $course = Course::factory()->create();
        $badge = $this->badgeFor('courses_enrolled');

        $this->actingAs($admin)->withSession(['active_role' => 'admin'])
            ->post(route('admin.course-enrollments.store', $course), ['user_ids' => [$student->id]]);

        $this->assertHasBadge($student, $badge);
    }
}
