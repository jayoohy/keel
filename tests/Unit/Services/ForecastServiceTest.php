<?php

use App\Models\Account;
use App\Models\Goal;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Forecasting\ForecastService;

test('average monthly saving is net income minus expenses over the trailing months', function () {
    $user = User::factory()->create();

    Transaction::factory()->for($user)->create(['type' => 'salary', 'amount' => 90000, 'transacted_at' => now()]);
    Transaction::factory()->for($user)->create(['type' => 'debit', 'amount' => 30000, 'transacted_at' => now()]);

    $average = app(ForecastService::class)->averageMonthlySaving($user, 3);

    expect($average)->toBe(20000.0);
});

test('a goal forecast projects a completion date based on the average saving rate', function () {
    $user = User::factory()->create();
    Transaction::factory()->for($user)->create(['type' => 'salary', 'amount' => 90000, 'transacted_at' => now()]);
    $goal = Goal::factory()->for($user)->create(['target_amount' => 60000, 'current_amount' => 0]);

    $forecast = app(ForecastService::class)->forecastGoal($goal);

    expect($forecast->average_monthly_saving)->toBe('30000.00');
    expect($forecast->projected_completion_date)->not->toBeNull();
});

test('a goal forecast has no projected date when there is no positive savings rate', function () {
    $user = User::factory()->create();
    Transaction::factory()->for($user)->create(['type' => 'debit', 'amount' => 50000, 'transacted_at' => now()]);
    $goal = Goal::factory()->for($user)->create(['target_amount' => 60000, 'current_amount' => 0]);

    $forecast = app(ForecastService::class)->forecastGoal($goal);

    expect($forecast->projected_completion_date)->toBeNull();
});

test('a fully funded goal forecast projects completion today', function () {
    $user = User::factory()->create();
    $goal = Goal::factory()->for($user)->create(['target_amount' => 60000, 'current_amount' => 60000]);

    $forecast = app(ForecastService::class)->forecastGoal($goal);

    expect($forecast->projected_completion_date->isToday())->toBeTrue();
});

test('forecast balance projects the current balance plus average saving times months ahead', function () {
    $user = User::factory()->create();
    Account::factory()->for($user)->create(['balance' => 10000]);
    Transaction::factory()->for($user)->create(['type' => 'salary', 'amount' => 90000, 'transacted_at' => now()]);

    $forecast = app(ForecastService::class)->forecastBalance($user, 2);

    expect($forecast)->toBe(70000.0);
});
