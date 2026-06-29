<?php

use App\Models\Account;
use App\Models\Goal;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Forecasting\HealthScoreService;

test('a user with no transactions or goals gets a neutral-leaning score, not zero', function () {
    $user = User::factory()->create();

    $result = app(HealthScoreService::class)->compute($user);

    // No income this month -> savings rate 0; no goals/expense history -> neutral defaults elsewhere.
    expect($result->score)->toBeGreaterThan(0);
    expect($result->savings_rate)->toBe('0.00');
});

test('a healthy saver with a well-funded emergency fund scores highly', function () {
    $user = User::factory()->create();
    Account::factory()->for($user)->create(['balance' => 600000]);

    foreach (range(0, 2) as $monthsAgo) {
        Transaction::factory()->for($user)->create([
            'type' => 'salary',
            'amount' => 100000,
            'transacted_at' => now()->subMonths($monthsAgo),
        ]);
        Transaction::factory()->for($user)->create([
            'type' => 'debit',
            'amount' => 20000,
            'transacted_at' => now()->subMonths($monthsAgo),
        ]);
    }

    $goal = Goal::factory()->for($user)->create(['target_amount' => 10000, 'current_amount' => 10000, 'status' => 'active']);

    $result = app(HealthScoreService::class)->compute($user);

    expect($result->score)->toBeGreaterThanOrEqual(80);
    expect((float) $result->savings_rate)->toBe(80.0);
});

test('a user with no income and high spending scores poorly', function () {
    $user = User::factory()->create();

    foreach (range(0, 2) as $monthsAgo) {
        Transaction::factory()->for($user)->create([
            'type' => 'debit',
            'amount' => 50000,
            'transacted_at' => now()->subMonths($monthsAgo),
        ]);
    }

    $result = app(HealthScoreService::class)->compute($user);

    expect($result->score)->toBeLessThan(50);
});
