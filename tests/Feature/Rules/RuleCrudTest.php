<?php

use App\Models\Goal;
use App\Models\Rule;
use App\Models\User;

test('a user can create a rule', function () {
    $user = User::factory()->create();
    $goal = Goal::factory()->for($user)->create();

    $response = $this->actingAs($user)->post('/rules', [
        'name' => 'Save from salary',
        'goal_id' => $goal->id,
        'trigger_type' => 'transaction_type',
        'trigger_value' => 'salary',
        'action_basis' => 'percentage',
        'action_value' => 20,
        'is_active' => true,
    ]);

    $response->assertRedirect(route('rules.index'));
    expect(Rule::where('user_id', $user->id)->where('name', 'Save from salary')->exists())->toBeTrue();
});

test('a user can deactivate a rule', function () {
    $user = User::factory()->create();
    $goal = Goal::factory()->for($user)->create();
    $rule = Rule::factory()->for($user)->for($goal)->create(['is_active' => true]);

    $response = $this->actingAs($user)->put("/rules/{$rule->id}", [
        'name' => $rule->name,
        'goal_id' => $goal->id,
        'trigger_type' => $rule->trigger_type,
        'trigger_value' => $rule->trigger_value,
        'action_basis' => $rule->action_basis,
        'action_value' => $rule->action_value,
        'is_active' => false,
    ]);

    $response->assertRedirect(route('rules.index'));
    expect($rule->refresh()->is_active)->toBeFalse();
});

test('a user can delete their rule', function () {
    $user = User::factory()->create();
    $rule = Rule::factory()->for($user)->create();

    $this->actingAs($user)->delete("/rules/{$rule->id}")->assertRedirect(route('rules.index'));

    expect(Rule::find($rule->id))->toBeNull();
});

test('a user cannot manage another user\'s rule', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $rule = Rule::factory()->for($owner)->create();

    $this->actingAs($intruder)->get("/rules/{$rule->id}/edit")->assertForbidden();
    $this->actingAs($intruder)->delete("/rules/{$rule->id}")->assertForbidden();
});
