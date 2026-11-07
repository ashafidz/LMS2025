<?php

namespace Tests\Feature\Student;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * A student who is not enrolled and opens /student/courses/{slug} used to get HTTP 500
 * (details-course needs $reviewCount, $reviews and $averageRating, which only the catalog page prepares).
 */
class NotEnrolledCoursePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_not_enrolled_student_is_sent_to_the_public_course_page(): void
    {
        Role::findOrCreate('student');
        $student = User::factory()->create();
        $student->assignRole('student');
        $course = Course::factory()->create(['status' => 'published']);

        $this->actingAs($student)->withSession(['active_role' => 'student'])
            ->get(route('student.courses.show', $course))
            ->assertRedirect(route('courses.show', $course));

        $this->actingAs($student)->get(route('courses.show', $course))->assertOk();
    }
}
