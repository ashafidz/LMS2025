<?php

namespace Tests;

use App\Models\Course;
use App\Models\InstructorProfile;
use App\Models\StudentProfile;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Models\Role;

abstract class TestCase extends BaseTestCase
{
    /**
     * Seed Spatie roles.
     */
    protected function seedRoles(): void
    {
        $this->seed(RoleSeeder::class);
    }

    /**
     * Create a student user with verified email and student profile.
     */
    protected function makeStudent(array $attributes = []): User
    {
        if (Role::count() === 0) {
            $this->seedRoles();
        }

        $user = User::factory()->create(array_merge([
            'email_verified_at' => now(),
        ], $attributes));

        $user->assignRole('student');

        StudentProfile::factory()->create([
            'user_id' => $user->id,
            'student_status' => 'active',
        ]);

        return $user;
    }

    /**
     * Create an instructor user with verified email and approved profile.
     */
    protected function makeInstructor(array $attributes = []): User
    {
        if (Role::count() === 0) {
            $this->seedRoles();
        }

        $user = User::factory()->create(array_merge([
            'email_verified_at' => now(),
        ], $attributes));

        $user->assignRole('instructor');

        InstructorProfile::factory()->create([
            'user_id' => $user->id,
            'application_status' => 'approved',
        ]);

        return $user;
    }

    /**
     * Create an admin user.
     */
    protected function makeAdmin(array $attributes = []): User
    {
        if (Role::count() === 0) {
            $this->seedRoles();
        }

        $user = User::factory()->create(array_merge([
            'email_verified_at' => now(),
        ], $attributes));

        $user->assignRole('admin');

        return $user;
    }

    /**
     * Create a superadmin user.
     */
    protected function makeSuperadmin(array $attributes = []): User
    {
        if (Role::count() === 0) {
            $this->seedRoles();
        }

        $user = User::factory()->create(array_merge([
            'email_verified_at' => now(),
        ], $attributes));

        $user->assignRole('superadmin');

        return $user;
    }

    /**
     * Enroll a student user into a course.
     */
    protected function enroll(User $u, Course $c): void
    {
        $u->enrollments()->syncWithoutDetaching([
            $c->id => ['enrolled_at' => now()],
        ]);
    }
}
