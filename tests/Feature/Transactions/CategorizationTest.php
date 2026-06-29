<?php

use App\Models\Category;
use App\Models\CategoryOverride;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Transactions\TransactionCategorizer;

test('the categorizer matches a default keyword', function () {
    Category::factory()->create(['name' => 'Food', 'user_id' => null, 'is_default' => true]);

    $transaction = Transaction::factory()->create(['description' => 'Payment to Bukkateria Express', 'narration' => null]);

    $category = app(TransactionCategorizer::class)->categorize($transaction);

    expect($category?->name)->toBe('Food');
});

test('a user can manually re-categorize a transaction via the API', function () {
    $user = User::factory()->create();
    $transaction = Transaction::factory()->for($user)->create(['category_id' => null]);
    $category = Category::factory()->create(['user_id' => null, 'name' => 'Shopping']);

    $response = $this->actingAs($user)->patch("/transactions/{$transaction->id}", [
        'category_id' => $category->id,
    ]);

    $response->assertRedirect();
    expect($transaction->refresh()->category_id)->toBe($category->id);
});

test('manually re-categorizing remembers the override for similar future transactions', function () {
    $user = User::factory()->create();
    $transaction = Transaction::factory()->for($user)->create([
        'description' => 'POS Purchase XYZ Store',
        'category_id' => null,
    ]);
    $category = Category::factory()->create(['user_id' => null, 'name' => 'Shopping']);

    $this->actingAs($user)->patch("/transactions/{$transaction->id}", ['category_id' => $category->id]);

    $override = CategoryOverride::where('user_id', $user->id)->first();
    expect($override)->not->toBeNull();
    expect($override->match_pattern)->toBe('pos purchase xyz store');

    $nextTransaction = Transaction::factory()->for($user)->create([
        'description' => 'POS Purchase XYZ Store',
        'category_id' => null,
    ]);

    $suggested = app(TransactionCategorizer::class)->categorize($nextTransaction);

    expect($suggested?->id)->toBe($category->id);
});

test('a user cannot re-categorize another user\'s transaction', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $transaction = Transaction::factory()->for($owner)->create();
    $category = Category::factory()->create(['user_id' => null]);

    $this->actingAs($intruder)
        ->patch("/transactions/{$transaction->id}", ['category_id' => $category->id])
        ->assertForbidden();
});
