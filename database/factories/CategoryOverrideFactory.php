<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\CategoryOverride;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CategoryOverride>
 */
class CategoryOverrideFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'match_pattern' => fake()->word(),
        ];
    }
}
