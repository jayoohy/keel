<?php

namespace Database\Factories;

use App\Models\Goal;
use App\Models\GoalForecast;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GoalForecast>
 */
class GoalForecastFactory extends Factory
{
    public function definition(): array
    {
        return [
            'goal_id' => Goal::factory(),
            'projected_completion_date' => fake()->dateTimeBetween('+1 month', '+3 years'),
            'average_monthly_saving' => fake()->randomFloat(2, 5000, 200000),
            'computed_at' => now(),
        ];
    }
}
