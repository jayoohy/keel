<?php

use App\Models\User;

test('preferences page is displayed', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/settings/preferences');

    $response->assertStatus(200);
});

test('notification preferences can be updated', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->put('/settings/preferences', [
        'notification_preferences' => [
            'goal_updates' => false,
            'large_spending_alerts' => true,
            'goal_completion' => true,
            'sync_errors' => false,
            'rule_executions' => true,
        ],
    ]);

    $response->assertSessionHasNoErrors();

    expect($user->refresh()->notification_preferences)->toBe([
        'goal_updates' => false,
        'large_spending_alerts' => true,
        'goal_completion' => true,
        'sync_errors' => false,
        'rule_executions' => true,
    ]);
});
