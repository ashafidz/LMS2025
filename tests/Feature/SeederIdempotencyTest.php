<?php

namespace Tests\Feature;

use App\Models\CourseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SeederIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_is_idempotent(): void
    {
        // First run
        $this->seed();

        $rolesCount1 = Role::count();
        $categoriesCount1 = CourseCategory::count();
        $usersCount1 = User::count();

        $this->assertEquals(4, $rolesCount1);
        $this->assertGreaterThan(0, $categoriesCount1);
        $this->assertGreaterThan(0, $usersCount1);

        // Second run
        $this->seed();

        $rolesCount2 = Role::count();
        $categoriesCount2 = CourseCategory::count();
        $usersCount2 = User::count();

        $this->assertEquals($rolesCount1, $rolesCount2);
        $this->assertEquals($categoriesCount1, $categoriesCount2);
        $this->assertEquals($usersCount1, $usersCount2);
    }
}
