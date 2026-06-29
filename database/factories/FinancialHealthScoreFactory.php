<?php

namespace Database\Factories;

use App\Models\FinancialHealthScore;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinancialHealthScore>
 */
class FinancialHealthScoreFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'score' => fake()->numberBetween(0, 100),
            'savings_rate' => fake()->randomFloat(2, 0, 100),
            'emergency_fund_coverage' => fake()->randomFloat(2, 0, 100),
            'spending_stability' => fake()->randomFloat(2, 0, 100),
            'income_consistency' => fake()->randomFloat(2, 0, 100),
            'computed_at' => now(),
        ];
    }
}
