<?php

namespace Tests\Feature\Student;

use App\Models\LikertQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

class CourseReviewAccessTest extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    private function payload(): array
    {
        $question = LikertQuestion::create(['question_text' => 'Materi jelas', 'category' => 'course', 'is_active' => true]);

        return [
            'course_rating' => 5,
            'course_comment' => 'Bagus',
            'instructor_rating' => 4,
            'instructor_comment' => 'Ramah',
            'likert_answers' => [$question->id => 3],
        ];
    }

    public function test_non_enrolled_student_cannot_open_or_submit_a_review(): void
    {
        $course = $this->makeCourse($this->makeInstructor());
        $student = $this->makeStudent();

        $this->actingAs($student)->getJson(route('student.course.review.create', $course))->assertForbidden();
        $this->actingAs($student)->postJson(route('student.course.review.store', $course), $this->payload())->assertForbidden();

        $this->assertDatabaseMissing('course_reviews', ['user_id' => $student->id]);
        $this->assertDatabaseMissing('instructor_reviews', ['user_id' => $student->id]);
    }

    public function test_enrolled_student_can_submit_a_review(): void
    {
        $course = $this->makeCourse($this->makeInstructor());
        $student = $this->makeStudent();
        $this->enroll($student, $course);

        $this->actingAs($student)->postJson(route('student.course.review.store', $course), $this->payload())
            ->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseHas('course_reviews', ['user_id' => $student->id, 'course_id' => $course->id, 'rating' => 5]);
    }

    public function test_review_form_is_closed_until_admin_adds_likert_questions(): void
    {
        $course = $this->makeCourse($this->makeInstructor());
        $student = $this->makeStudent();
        $this->enroll($student, $course);

        $this->actingAs($student)->getJson(route('student.course.review.create', $course))
            ->assertOk()->assertSee('Ulasan Belum Bisa Diisi');

        $this->actingAs($student)->postJson(route('student.course.review.store', $course), [
            'course_rating' => 5,
            'instructor_rating' => 4,
        ])->assertStatus(422)->assertJson(['success' => false]);

        $this->assertDatabaseMissing('course_reviews', ['user_id' => $student->id]);
    }

    public function test_every_active_likert_question_must_be_answered(): void
    {
        $course = $this->makeCourse($this->makeInstructor());
        $student = $this->makeStudent();
        $this->enroll($student, $course);
        $payload = $this->payload();
        LikertQuestion::create(['question_text' => 'Instruktur ramah', 'category' => 'instructor', 'is_active' => true]);

        $this->actingAs($student)->postJson(route('student.course.review.store', $course), $payload)
            ->assertStatus(422)->assertJsonFragment(['message' => 'Jawab semua pertanyaan penilaian.']);

        $this->assertDatabaseMissing('course_reviews', ['user_id' => $student->id]);
    }
}
