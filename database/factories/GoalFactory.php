<?php

namespace Database\Factories;

use App\Models\Goal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Goal>
 */
class GoalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->randomElement(['House Fund', 'Wedding Fund', 'Emergency Fund', 'Relocation Fund']),
            'description' => fake()->sentence(),
            'target_amount' => fake()->randomFloat(2, 100000, 5000000),
            'current_amount' => 0,
            'deadline' => fake()->dateTimeBetween('+3 months', '+2 years'),
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'status' => 'active',
        ];
    }
}
