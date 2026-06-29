<?php

use App\Models\Account;
use App\Models\Goal;
use App\Models\Rule;
use App\Models\RuleExecution;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Rules\RuleEvaluator;

test('a matching transaction_type rule allocates to the goal and logs an execution', function () {
    $user = User::factory()->create();
    Account::factory()->for($user)->create(['balance' => 100000]);
    $goal = Goal::factory()->for($user)->create();
    $rule = Rule::factory()->for($user)->for($goal)->create([
        'trigger_type' => 'transaction_type',
        'trigger_value' => 'salary',
        'action_basis' => 'percentage',
        'action_value' => 20,
    ]);
    $transaction = Transaction::factory()->for($user)->create(['type' => 'salary', 'amount' => 10000]);

    app(RuleEvaluator::class)->evaluate($transaction);

    expect($goal->refresh()->current_amount)->toBe('2000.00');
    expect(RuleExecution::where('rule_id', $rule->id)->where('transaction_id', $transaction->id)->exists())->toBeTrue();
});

test('a non-matching transaction does not trigger the rule', function () {
    $user = User::factory()->create();
    Account::factory()->for($user)->create(['balance' => 100000]);
    $goal = Goal::factory()->for($user)->create();
    Rule::factory()->for($user)->for($goal)->create([
        'trigger_type' => 'transaction_type',
        'trigger_value' => 'salary',
    ]);
    $transaction = Transaction::factory()->for($user)->create(['type' => 'debit', 'amount' => 10000]);

    app(RuleEvaluator::class)->evaluate($transaction);

    expect($goal->refresh()->current_amount)->toBe('0.00');
    expect(RuleExecution::count())->toBe(0);
});

test('an inactive rule never fires', function () {
    $user = User::factory()->create();
    Account::factory()->for($user)->create(['balance' => 100000]);
    $goal = Goal::factory()->for($user)->create();
    Rule::factory()->for($user)->for($goal)->create([
        'trigger_type' => 'transaction_type',
        'trigger_value' => 'salary',
        'is_active' => false,
    ]);
    $transaction = Transaction::factory()->for($user)->create(['type' => 'salary', 'amount' => 10000]);

    app(RuleEvaluator::class)->evaluate($transaction);

    expect($goal->refresh()->current_amount)->toBe('0.00');
});

test('a rule is skipped (not logged) when the allocation would exceed the unallocated balance', function () {
    $user = User::factory()->create();
    Account::factory()->for($user)->create(['balance' => 100]);
    $goal = Goal::factory()->for($user)->create();
    Rule::factory()->for($user)->for($goal)->create([
        'trigger_type' => 'transaction_type',
        'trigger_value' => 'salary',
        'action_basis' => 'fixed',
        'action_value' => 5000,
    ]);
    $transaction = Transaction::factory()->for($user)->create(['type' => 'salary', 'amount' => 10000]);

    app(RuleEvaluator::class)->evaluate($transaction);

    expect($goal->refresh()->current_amount)->toBe('0.00');
    expect(RuleExecution::count())->toBe(0);
});

test('a merchant trigger matches on transaction description', function () {
    $user = User::factory()->create();
    Account::factory()->for($user)->create(['balance' => 100000]);
    $goal = Goal::factory()->for($user)->create();
    Rule::factory()->for($user)->for($goal)->create([
        'trigger_type' => 'merchant',
        'trigger_value' => 'uber',
        'action_basis' => 'fixed',
        'action_value' => 100,
    ]);
    $transaction = Transaction::factory()->for($user)->create(['description' => 'Uber Trip Payment', 'amount' => 3000]);

    app(RuleEvaluator::class)->evaluate($transaction);

    expect($goal->refresh()->current_amount)->toBe('100.00');
});
