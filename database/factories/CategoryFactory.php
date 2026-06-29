<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->randomElement(['Food', 'Transport', 'Utilities', 'Housing', 'Shopping']),
            'icon' => null,
            'is_default' => false,
        ];
    }

    public function default(): static
    {
        return $this->state(fn () => ['user_id' => null, 'is_default' => true]);
    }
}
