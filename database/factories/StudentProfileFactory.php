<?php

namespace Database\Factories;

use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentProfile>
 */
class StudentProfileFactory extends Factory
{
    protected $model = StudentProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'headline' => fake()->sentence(3),
            'bio' => fake()->paragraph(),
            'website_url' => fake()->url(),
            'student_status' => 'active',
        ];
    }
}
