<?php

namespace Database\Factories;

use App\Models\Allocation;
use App\Models\Goal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Allocation>
 */
class AllocationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'goal_id' => Goal::factory(),
            'transaction_id' => null,
            'amount' => fake()->randomFloat(2, 1000, 50000),
            'type' => 'manual',
            'source' => 'user',
        ];
    }
}
