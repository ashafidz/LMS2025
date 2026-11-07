<?php

namespace Tests\Feature\Student;

use App\Models\CourseReview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

/**
 * The certificate and point conversion buttons are always printed with their ids, so the page can unlock
 * them in place right after the feedback is sent (they used to be id-less placeholders that only a reload fixed).
 */
class CourseActionButtonsTest extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    private function buttons(bool $reviewed): array
    {
        $student = $this->makeStudent();
        $course = $this->makeCourse($this->makeInstructor());
        $lesson = $this->makeArticleLesson($this->makeModule($course));
        $this->enroll($student, $course);
        $student->completedLessons()->attach($lesson->id, ['completed_at' => now()]);
        if ($reviewed) {
            CourseReview::create(['course_id' => $course->id, 'user_id' => $student->id, 'rating' => 5, 'comment' => 'Bagus']);
        }

        $html = $this->actingAs($student)->withSession(['active_role' => 'student'])
            ->get(route('student.courses.show', $course))->assertOk()->getContent();

        $tag = function (string $id) use ($html) {
            $this->assertSame(1, preg_match('/<button[^>]*id="'.$id.'"[^>]*>/s', $html, $m), $id.' is not printed');

            return $m[0];
        };

        return [$tag('load-certificate-preview'), $tag('convert-points-btn')];
    }

    public function test_buttons_are_printed_locked_until_the_feedback_is_sent(): void
    {
        foreach ($this->buttons(false) as $button) {
            $this->assertStringContainsString('data-needs-feedback', $button);
            $this->assertMatchesRegularExpression('/\sdisabled\s/', $button);
        }
    }

    public function test_buttons_are_open_after_the_feedback_is_sent(): void
    {
        foreach ($this->buttons(true) as $button) {
            $this->assertDoesNotMatchRegularExpression('/\sdisabled\s/', $button);
        }
    }
}
