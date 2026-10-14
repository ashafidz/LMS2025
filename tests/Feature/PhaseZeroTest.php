<?php

namespace Tests\Feature;

use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseZeroTest extends TestCase
{
    use RefreshDatabase;

    public function test_helpers_can_create_student_instructor_admin_superadmin_and_enroll(): void
    {
        $student = $this->makeStudent();
        $this->assertTrue($student->hasRole('student'));
        $this->assertNotNull($student->studentProfile);

        $instructor = $this->makeInstructor();
        $this->assertTrue($instructor->hasRole('instructor'));
        $this->assertNotNull($instructor->instructorProfile);
        $this->assertEquals('approved', $instructor->instructorProfile->application_status);

        $admin = $this->makeAdmin();
        $this->assertTrue($admin->hasRole('admin'));

        $superadmin = $this->makeSuperadmin();
        $this->assertTrue($superadmin->hasRole('superadmin'));

        $course = Course::factory()->create(['instructor_id' => $instructor->id]);
        $this->enroll($student, $course);

        $this->assertTrue($student->enrollments->contains($course));
    }
}
