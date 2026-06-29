<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\BankConnection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    public function definition(): array
    {
        return [
            'bank_connection_id' => BankConnection::factory(),
            'user_id' => User::factory(),
            'mono_account_id' => 'mono_'.fake()->unique()->bothify('??########'),
            'name' => fake()->randomElement(['Savings', 'Current']).' Account',
            'account_number' => fake()->numerify('##########'),
            'account_type' => 'SAVINGS_ACCOUNT',
            'currency' => 'NGN',
            'balance' => fake()->randomFloat(2, 1000, 500000),
            'balance_synced_at' => now(),
        ];
    }
}
