<?php

namespace Database\Factories;

use App\Models\BankConnection;
use App\Models\SyncLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SyncLog>
 */
class SyncLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'bank_connection_id' => BankConnection::factory(),
            'status' => 'success',
            'message' => null,
            'transactions_synced' => fake()->numberBetween(0, 20),
            'started_at' => now()->subMinute(),
            'finished_at' => now(),
        ];
    }
}
