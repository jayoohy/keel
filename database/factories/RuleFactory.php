<?php

namespace Database\Factories;

use App\Models\Goal;
use App\Models\Rule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rule>
 */
class RuleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'goal_id' => Goal::factory(),
            'name' => 'Save from salary',
            'trigger_type' => 'transaction_type',
            'trigger_value' => 'salary',
            'action_basis' => 'percentage',
            'action_value' => 20,
            'is_active' => true,
        ];
    }
}
