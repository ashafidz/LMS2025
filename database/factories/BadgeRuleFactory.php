<?php

namespace Database\Factories;

use App\Models\Badge;
use App\Models\BadgeRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BadgeRule>
 */
class BadgeRuleFactory extends Factory
{
    protected $model = BadgeRule::class;

    public function definition(): array
    {
        return [
            'badge_id' => Badge::factory(),
            'metric' => 'quiz_passed',
            'target' => 1,
            'scope' => 'all',
            'scope_id' => null,
            'position' => 0,
        ];
    }
}
