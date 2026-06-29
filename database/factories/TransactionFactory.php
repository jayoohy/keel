<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'user_id' => User::factory(),
            'category_id' => null,
            'mono_transaction_id' => 'txn_'.fake()->unique()->bothify('################'),
            'type' => fake()->randomElement(['debit', 'credit']),
            'amount' => fake()->randomFloat(2, 100, 100000),
            'currency' => 'NGN',
            'description' => fake()->words(3, true),
            'narration' => fake()->sentence(),
            'balance_after' => fake()->randomFloat(2, 1000, 500000),
            'transacted_at' => now(),
        ];
    }
}
