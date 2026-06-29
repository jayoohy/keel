<?php

use App\Models\Account;
use App\Models\BankConnection;
use App\Models\Goal;
use App\Models\Rule;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\GoalCompleted;
use App\Notifications\GoalUpdated;
use App\Notifications\RuleExecuted;
use App\Notifications\SyncErrorAlert;
use App\Services\Allocation\AllocationEngine;
use App\Services\Mono\MonoSyncService;
use App\Services\Rules\RuleEvaluator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

test('allocating to a goal sends a GoalUpdated notification', function () {
    Notification::fake();

    $user = User::factory()->create();
    Account::factory()->for($user)->create(['balance' => 100000]);
    $goal = Goal::factory()->for($user)->create(['target_amount' => 50000]);

    app(AllocationEngine::class)->allocate($user, $goal, 10000);

    Notification::assertSentTo($user, GoalUpdated::class);
    Notification::assertNotSentTo($user, GoalCompleted::class);
});

test('fully allocating a goal also sends a GoalCompleted notification and marks it complete', function () {
    Notification::fake();

    $user = User::factory()->create();
    Account::factory()->for($user)->create(['balance' => 100000]);
    $goal = Goal::factory()->for($user)->create(['target_amount' => 10000, 'current_amount' => 0]);

    app(AllocationEngine::class)->allocate($user, $goal, 10000);

    Notification::assertSentTo($user, GoalCompleted::class);
    expect($goal->refresh()->status)->toBe('completed');
});

test('a failed sync sends a SyncErrorAlert notification', function () {
    Notification::fake();

    $user = User::factory()->create();
    $connection = BankConnection::factory()->for($user)->create();

    Http::fake(['*' => Http::response('error', 500)]);

    app(MonoSyncService::class)->sync($connection);

    Notification::assertSentTo($user, SyncErrorAlert::class);
});

test('a fired rule sends a RuleExecuted notification', function () {
    Notification::fake();

    $user = User::factory()->create();
    Account::factory()->for($user)->create(['balance' => 100000]);
    $goal = Goal::factory()->for($user)->create();
    Rule::factory()->for($user)->for($goal)->create([
        'trigger_type' => 'transaction_type',
        'trigger_value' => 'salary',
    ]);
    $transaction = Transaction::factory()->for($user)->create(['type' => 'salary', 'amount' => 10000]);

    app(RuleEvaluator::class)->evaluate($transaction);

    Notification::assertSentTo($user, RuleExecuted::class);
});

test('disabling a notification preference suppresses that notification entirely', function () {
    $user = User::factory()->create(['notification_preferences' => ['goal_updates' => false]]);
    Account::factory()->for($user)->create(['balance' => 100000]);
    $goal = Goal::factory()->for($user)->create(['target_amount' => 50000]);

    app(AllocationEngine::class)->allocate($user, $goal, 10000);

    expect($user->notifications()->where('type', GoalUpdated::class)->count())->toBe(0);
});

test('a notification preference left unset defaults to enabled', function () {
    $user = User::factory()->create(['notification_preferences' => null]);
    Account::factory()->for($user)->create(['balance' => 100000]);
    $goal = Goal::factory()->for($user)->create(['target_amount' => 50000]);

    app(AllocationEngine::class)->allocate($user, $goal, 10000);

    expect($user->notifications()->where('type', GoalUpdated::class)->count())->toBe(1);
});
