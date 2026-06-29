<?php

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;

test('transactions can be filtered by account, category, type, date range, and amount range', function () {
    $user = User::factory()->create();
    $accountA = Account::factory()->for($user)->create();
    $accountB = Account::factory()->for($user)->create();
    $foodCategory = Category::factory()->create(['name' => 'Food', 'user_id' => null]);

    $matching = Transaction::factory()->for($user)->for($accountA, 'account')->create([
        'category_id' => $foodCategory->id,
        'type' => 'debit',
        'amount' => 5000,
        'transacted_at' => '2026-01-15',
    ]);

    Transaction::factory()->for($user)->for($accountB, 'account')->create([
        'type' => 'credit',
        'amount' => 9000,
        'transacted_at' => '2026-02-01',
    ]);

    $response = $this->actingAs($user)->get('/transactions?'.http_build_query([
        'account_id' => $accountA->id,
        'category_id' => $foodCategory->id,
        'type' => 'debit',
        'date_from' => '2026-01-01',
        'date_to' => '2026-01-31',
        'amount_min' => 1000,
        'amount_max' => 10000,
    ]));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->where('transactions.data.0.id', $matching->id)
        ->has('transactions.data', 1)
    );
});

test('a user only sees their own transactions', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Transaction::factory()->for($otherUser)->create();

    $response = $this->actingAs($user)->get('/transactions');

    $response->assertInertia(fn ($page) => $page->has('transactions.data', 0));
});
