<?php

namespace Tests\Feature\Student;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * One enrolled student without a student profile made the leaderboard partial throw
 * ("Attempt to read property unique_id_number on null"), and the empty response broke the leaderboard for everyone.
 */
class LeaderboardWithoutProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_course_leaderboard_loads_when_a_student_has_no_profile(): void
    {
        Role::findOrCreate('student');
        $course = Course::factory()->create(['status' => 'published']);
        $student = User::factory()->create(['name' => 'Siswa Tanpa Profil']);
        $student->assignRole('student');
        $student->enrollments()->attach($course->id);
        $student->studentProfile()->delete();
        DB::table('course_user')->insert(['user_id' => $student->id, 'course_id' => $course->id, 'points_earned' => 25]);

        $response = $this->actingAs($student)->withSession(['active_role' => 'student'])
            ->getJson(route('student.course.leaderboard', $course));

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertStringContainsString('Siswa Tanpa Profil', $response->json('html'));
        $this->assertStringContainsString('rs-rank--top', $response->json('html'));
    }
}
