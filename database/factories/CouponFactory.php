<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(Str::random(8)),
            'description' => fake()->sentence(),
            'type' => 'fixed',
            'value' => 10000,
            'course_id' => null,
            'max_uses' => null,
            'uses_count' => 0,
            'is_public' => false,
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'max_uses_per_user' => null,
        ];
    }

    public function percent(int $value = 20): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'percent',
            'value' => $value,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDay(),
        ]);
    }
}
