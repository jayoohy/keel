<?php

use App\Models\Account;
use App\Models\Goal;
use App\Models\User;

test('a user can create a goal', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/goals', [
        'name' => 'House Fund',
        'description' => 'Save for a down payment',
        'target_amount' => 5000000,
        'deadline' => now()->addYear()->toDateString(),
        'priority' => 'high',
    ]);

    $response->assertRedirect(route('goals.index'));
    expect(Goal::where('user_id', $user->id)->where('name', 'House Fund')->exists())->toBeTrue();
});

test('a user can update their goal, including its status', function () {
    $user = User::factory()->create();
    $goal = Goal::factory()->for($user)->create(['status' => 'active']);

    $response = $this->actingAs($user)->put("/goals/{$goal->id}", [
        'name' => $goal->name,
        'target_amount' => $goal->target_amount,
        'priority' => $goal->priority,
        'status' => 'paused',
    ]);

    $response->assertRedirect(route('goals.index'));
    expect($goal->refresh()->status)->toBe('paused');
});

test('a user cannot view or edit another user\'s goal', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $goal = Goal::factory()->for($owner)->create();

    $this->actingAs($intruder)->get("/goals/{$goal->id}")->assertForbidden();
    $this->actingAs($intruder)->get("/goals/{$goal->id}/edit")->assertForbidden();
});

test('a user can manually allocate available balance to a goal', function () {
    $user = User::factory()->create();
    Account::factory()->for($user)->create(['balance' => 100000]);
    $goal = Goal::factory()->for($user)->create();

    $response = $this->actingAs($user)->post("/goals/{$goal->id}/allocations", ['amount' => 25000]);

    $response->assertRedirect();
    expect($goal->refresh()->current_amount)->toBe('25000.00');
});

test('allocating more than the available balance fails validation', function () {
    $user = User::factory()->create();
    Account::factory()->for($user)->create(['balance' => 10000]);
    $goal = Goal::factory()->for($user)->create();

    $response = $this->actingAs($user)->post("/goals/{$goal->id}/allocations", ['amount' => 50000]);

    $response->assertSessionHasErrors('amount');
    expect($goal->refresh()->current_amount)->toBe('0.00');
});
