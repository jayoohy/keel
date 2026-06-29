<?php

use App\Models\Account;
use App\Models\Goal;
use App\Models\User;
use App\Notifications\GoalCompleted;
use App\Services\Allocation\AllocationEngine;

test('a user can view their notifications', function () {
    $user = User::factory()->create();
    $goal = Goal::factory()->for($user)->create();
    $user->notify(new GoalCompleted($goal));

    $response = $this->actingAs($user)->get('/notifications');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->has('notifications.data', 1));
});

test('a user can mark a single notification as read', function () {
    $user = User::factory()->create();
    $goal = Goal::factory()->for($user)->create();
    $user->notify(new GoalCompleted($goal));
    $notification = $user->notifications()->first();

    $this->actingAs($user)->patch("/notifications/{$notification->id}")->assertRedirect();

    expect($notification->refresh()->read_at)->not->toBeNull();
});

test('a user can mark all notifications as read', function () {
    $user = User::factory()->create();
    $goal = Goal::factory()->for($user)->create();
    $user->notify(new GoalCompleted($goal));
    $user->notify(new GoalCompleted($goal));

    $this->actingAs($user)->post('/notifications/mark-all-read')->assertRedirect();

    expect($user->unreadNotifications()->count())->toBe(0);
});

test('the unread notifications count is shared with every Inertia response', function () {
    $user = User::factory()->create();
    $goal = Goal::factory()->for($user)->create();
    $user->notify(new GoalCompleted($goal));

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertInertia(fn ($page) => $page->where('unreadNotificationsCount', 1));
});

test('allocating funds to a goal records a notification for the user', function () {
    $user = User::factory()->create();
    Account::factory()->for($user)->create(['balance' => 100000]);
    $goal = Goal::factory()->for($user)->create(['target_amount' => 50000]);

    app(AllocationEngine::class)->allocate($user, $goal, 10000);

    expect($user->notifications()->count())->toBe(1);
});
