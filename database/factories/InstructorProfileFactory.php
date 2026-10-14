<?php

namespace Database\Factories;

use App\Models\InstructorProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InstructorProfile>
 */
class InstructorProfileFactory extends Factory
{
    protected $model = InstructorProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'headline' => fake()->jobTitle(),
            'bio' => fake()->paragraph(),
            'website_url' => fake()->url(),
            'application_status' => 'approved',
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'application_status' => 'pending',
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'application_status' => 'rejected',
        ]);
    }
}
