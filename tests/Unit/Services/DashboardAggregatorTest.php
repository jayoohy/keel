<?php

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Dashboard\DashboardAggregator;

test('summary computes income, expenses, savings, and savings rate for the current month', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['balance' => 50000]);

    Transaction::factory()->for($user)->for($account, 'account')->create([
        'type' => 'salary',
        'amount' => 20000,
        'transacted_at' => now(),
    ]);

    Transaction::factory()->for($user)->for($account, 'account')->create([
        'type' => 'debit',
        'amount' => 5000,
        'transacted_at' => now(),
    ]);

    // Outside the current month — must not be counted.
    Transaction::factory()->for($user)->for($account, 'account')->create([
        'type' => 'salary',
        'amount' => 99999,
        'transacted_at' => now()->subMonths(2),
    ]);

    $summary = app(DashboardAggregator::class)->summary($user);

    expect($summary['totalBalance'])->toBe(50000.0);
    expect($summary['monthlyIncome'])->toBe(20000.0);
    expect($summary['monthlyExpenses'])->toBe(5000.0);
    expect($summary['monthlySavings'])->toBe(15000.0);
    expect($summary['savingsRate'])->toBe(75.0);
});

test('spending by category groups expense transactions by category name', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();
    $food = Category::factory()->create(['name' => 'Food', 'user_id' => null]);

    Transaction::factory()->for($user)->for($account, 'account')->create([
        'type' => 'debit',
        'amount' => 3000,
        'category_id' => $food->id,
        'transacted_at' => now(),
    ]);
    Transaction::factory()->for($user)->for($account, 'account')->create([
        'type' => 'debit',
        'amount' => 1000,
        'category_id' => null,
        'transacted_at' => now(),
    ]);

    $breakdown = app(DashboardAggregator::class)->spendingByCategory($user);

    expect($breakdown)->toContain(['category' => 'Food', 'total' => 3000.0]);
    expect($breakdown)->toContain(['category' => 'Uncategorized', 'total' => 1000.0]);
});

test('income/expense trend returns one entry per requested month', function () {
    $user = User::factory()->create();

    $trend = app(DashboardAggregator::class)->incomeExpenseTrend($user, 3);

    expect($trend)->toHaveCount(3);
    expect($trend[2]['month'])->toBe(now()->format('M Y'));
});
