<?php

namespace Tests\Feature\Authorization;

use App\Models\InstructorProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleSwitchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('role.switch', 'student'));
        $response->assertRedirect(route('login'));
    }

    public function test_student_cannot_switch_to_admin_or_superadmin(): void
    {
        $student = $this->makeStudent();

        $responseAdmin = $this->actingAs($student)
            ->withSession(['active_role' => 'student'])
            ->get(route('role.switch', 'admin'));

        $responseAdmin->assertStatus(403);
        $this->assertEquals('student', session('active_role'));

        $responseSuperadmin = $this->actingAs($student)
            ->withSession(['active_role' => 'student'])
            ->get(route('role.switch', 'superadmin'));

        $responseSuperadmin->assertStatus(403);
        $this->assertEquals('student', session('active_role'));
    }

    public function test_student_cannot_switch_to_invalid_role(): void
    {
        $student = $this->makeStudent();

        $response = $this->actingAs($student)
            ->withSession(['active_role' => 'student'])
            ->get(route('role.switch', 'hacker'));

        $response->assertStatus(403);
        $this->assertEquals('student', session('active_role'));
    }

    public function test_instructor_can_switch_to_instructor(): void
    {
        $instructor = $this->makeInstructor();
        InstructorProfile::factory()->create([
            'user_id' => $instructor->id,
            'application_status' => 'approved',
        ]);

        $response = $this->actingAs($instructor)
            ->get(route('role.switch', 'instructor'));

        $response->assertRedirect(route('instructor.dashboard'));
        $this->assertEquals('instructor', session('active_role'));
    }

    public function test_student_switching_to_instructor_redirects_to_application_without_setting_active_role(): void
    {
        $student = $this->makeStudent();

        $response = $this->actingAs($student)
            ->withSession(['active_role' => 'student'])
            ->get(route('role.switch', 'instructor'));

        $response->assertRedirect(route('student.apply_instructor.create'));
        $this->assertEquals('student', session('active_role'));
    }

    public function test_student_with_pending_instructor_profile_redirects_to_pending_without_setting_active_role(): void
    {
        $student = $this->makeStudent();
        InstructorProfile::factory()->create([
            'user_id' => $student->id,
            'application_status' => 'pending',
        ]);

        $response = $this->actingAs($student)
            ->withSession(['active_role' => 'student'])
            ->get(route('role.switch', 'instructor'));

        $response->assertRedirect(route('instructor.pending'));
        $this->assertEquals('student', session('active_role'));
    }
}
