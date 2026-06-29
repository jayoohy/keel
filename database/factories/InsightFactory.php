<?php

namespace Database\Factories;

use App\Models\Insight;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Insight>
 */
class InsightFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => 'spending',
            'title' => 'Spending update',
            'message' => fake()->sentence(),
            'related_type' => null,
            'related_id' => null,
            'is_read' => false,
            'dismissed_at' => null,
        ];
    }
}
