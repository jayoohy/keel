<?php

use App\Models\Insight;
use App\Models\User;

test('a user sees only their own, non-dismissed insights', function () {
    $user = User::factory()->create();
    $visible = Insight::factory()->for($user)->create();
    Insight::factory()->for($user)->create(['dismissed_at' => now()]);
    Insight::factory()->create(); // another user's insight

    $response = $this->actingAs($user)->get('/insights');

    $response->assertInertia(fn ($page) => $page
        ->has('insights.data', 1)
        ->where('insights.data.0.id', $visible->id)
    );
});

test('a user can mark an insight as read', function () {
    $user = User::factory()->create();
    $insight = Insight::factory()->for($user)->create(['is_read' => false]);

    $this->actingAs($user)->patch("/insights/{$insight->id}")->assertRedirect();

    expect($insight->refresh()->is_read)->toBeTrue();
});

test('a user can dismiss an insight', function () {
    $user = User::factory()->create();
    $insight = Insight::factory()->for($user)->create();

    $this->actingAs($user)->delete("/insights/{$insight->id}")->assertRedirect();

    expect($insight->refresh()->dismissed_at)->not->toBeNull();
});

test('a user cannot manage another user\'s insight', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $insight = Insight::factory()->for($owner)->create();

    $this->actingAs($intruder)->patch("/insights/{$insight->id}")->assertForbidden();
    $this->actingAs($intruder)->delete("/insights/{$insight->id}")->assertForbidden();
});
