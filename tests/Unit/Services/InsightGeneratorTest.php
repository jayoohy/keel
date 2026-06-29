<?php

use App\Models\Goal;
use App\Models\GoalForecast;
use App\Models\Insight;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AI\InsightGenerator;
use Illuminate\Support\Facades\Http;

test('a significant spending change generates a spending insight via OpenAI', function () {
    $user = User::factory()->create();

    Transaction::factory()->for($user)->create([
        'type' => 'debit',
        'amount' => 20000,
        'transacted_at' => now(),
    ]);
    Transaction::factory()->for($user)->create([
        'type' => 'debit',
        'amount' => 10000,
        'transacted_at' => now()->subMonth(),
    ]);

    Http::fake([
        'api.openai.com/*' => Http::response([
            'choices' => [['message' => ['content' => 'You spent 100% more this month.']]],
        ]),
    ]);

    $insight = app(InsightGenerator::class)->generateSpendingInsight($user);

    expect($insight)->not->toBeNull();
    expect($insight->type)->toBe('spending');
    expect($insight->message)->toBe('You spent 100% more this month.');
});

test('no spending insight is generated when the change is not significant', function () {
    $user = User::factory()->create();

    Transaction::factory()->for($user)->create(['type' => 'debit', 'amount' => 10500, 'transacted_at' => now()]);
    Transaction::factory()->for($user)->create(['type' => 'debit', 'amount' => 10000, 'transacted_at' => now()->subMonth()]);

    Http::fake();

    $insight = app(InsightGenerator::class)->generateSpendingInsight($user);

    expect($insight)->toBeNull();
    Http::assertNothingSent();
});

test('only one spending insight is generated per user per month', function () {
    $user = User::factory()->create();
    Insight::factory()->for($user)->create(['type' => 'spending', 'created_at' => now()]);

    Http::fake();

    $insight = app(InsightGenerator::class)->generateSpendingInsight($user);

    expect($insight)->toBeNull();
    Http::assertNothingSent();
});

test('a goal with a projected completion date generates a goal progress insight', function () {
    $user = User::factory()->create();
    $goal = Goal::factory()->for($user)->create();
    GoalForecast::factory()->for($goal)->create(['projected_completion_date' => now()->addMonths(4)]);

    Http::fake([
        'api.openai.com/*' => Http::response([
            'choices' => [['message' => ['content' => 'Great progress toward your goal!']]],
        ]),
    ]);

    $insight = app(InsightGenerator::class)->generateGoalProgressInsight($goal->refresh());

    expect($insight)->not->toBeNull();
    expect($insight->type)->toBe('goal_progress');
});

test('a goal with no forecast yet does not generate an insight', function () {
    $user = User::factory()->create();
    $goal = Goal::factory()->for($user)->create();

    Http::fake();

    $insight = app(InsightGenerator::class)->generateGoalProgressInsight($goal);

    expect($insight)->toBeNull();
    Http::assertNothingSent();
});

test('an anomaly insight is created with a deterministic message and no OpenAI call', function () {
    $user = User::factory()->create();
    $transaction = Transaction::factory()->for($user)->create(['type' => 'debit', 'amount' => 50000]);

    Http::fake();

    $insight = app(InsightGenerator::class)->generateAnomalyInsight($transaction);

    expect($insight)->not->toBeNull();
    expect($insight->type)->toBe('anomaly');
    Http::assertNothingSent();
});

test('the same transaction is not flagged as an anomaly twice', function () {
    $user = User::factory()->create();
    $transaction = Transaction::factory()->for($user)->create();

    app(InsightGenerator::class)->generateAnomalyInsight($transaction);
    $second = app(InsightGenerator::class)->generateAnomalyInsight($transaction);

    expect($second)->toBeNull();
    expect(Insight::where('type', 'anomaly')->count())->toBe(1);
});
