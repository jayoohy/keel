<?php

use App\Exceptions\InsufficientBalanceException;
use App\Models\Account;
use App\Models\Goal;
use App\Models\User;
use App\Services\Allocation\AllocationEngine;

test('unallocated balance is the account balance minus existing allocations', function () {
    $user = User::factory()->create();
    Account::factory()->for($user)->create(['balance' => 100000]);
    $goal = Goal::factory()->for($user)->create();

    $engine = app(AllocationEngine::class);

    expect($engine->unallocatedBalance($user))->toBe(100000.0);

    $engine->allocate($user, $goal, 40000);

    expect($engine->unallocatedBalance($user))->toBe(60000.0);
    expect($goal->refresh()->current_amount)->toBe('40000.00');
});

test('allocating more than the unallocated balance throws', function () {
    $user = User::factory()->create();
    Account::factory()->for($user)->create(['balance' => 10000]);
    $goal = Goal::factory()->for($user)->create();

    app(AllocationEngine::class)->allocate($user, $goal, 15000);
})->throws(InsufficientBalanceException::class);

test('deallocating removes the allocation and restores the goal amount', function () {
    $user = User::factory()->create();
    Account::factory()->for($user)->create(['balance' => 100000]);
    $goal = Goal::factory()->for($user)->create();

    $engine = app(AllocationEngine::class);
    $allocation = $engine->allocate($user, $goal, 30000);

    $engine->deallocate($allocation);

    expect($goal->refresh()->current_amount)->toBe('0.00');
    expect($engine->unallocatedBalance($user))->toBe(100000.0);
});
