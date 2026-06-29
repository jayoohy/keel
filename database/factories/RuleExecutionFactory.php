<?php

namespace Database\Factories;

use App\Models\Rule;
use App\Models\RuleExecution;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RuleExecution>
 */
class RuleExecutionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'rule_id' => Rule::factory(),
            'transaction_id' => Transaction::factory(),
            'allocation_id' => null,
            'executed_at' => now(),
        ];
    }
}
