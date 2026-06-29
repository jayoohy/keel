<?php

use App\Models\Account;
use App\Models\User;

test('accounts index shows the user\'s accounts and aggregate balance', function () {
    $user = User::factory()->create();
    Account::factory()->for($user)->create(['balance' => 10000]);
    Account::factory()->for($user)->create(['balance' => 25000]);
    Account::factory()->create(['balance' => 99999]); // another user's account

    $response = $this->actingAs($user)->get('/accounts');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->where('accountCount', 2)
        ->where('totalBalance', 35000)
    );
});
