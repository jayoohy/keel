<?php

namespace Database\Factories;

use App\Models\BankConnection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BankConnection>
 */
class BankConnectionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'mono_account_id' => 'mono_'.fake()->unique()->bothify('??########'),
            'access_token' => fake()->uuid(),
            'institution_name' => fake()->company().' Bank',
            'institution_logo' => null,
            'status' => 'active',
            'connected_at' => now(),
            'disconnected_at' => null,
        ];
    }
}
