<?php

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AI\AiTransactionCategorizer;
use Illuminate\Support\Facades\Http;

function fakeOpenAi(string $reply): void
{
    Http::fake([
        'api.openai.com/*' => Http::response([
            'choices' => [
                ['message' => ['content' => $reply]],
            ],
        ]),
    ]);
}

test('it assigns the category suggested by OpenAI when it matches an available category', function () {
    $user = User::factory()->create();
    Category::factory()->create(['name' => 'Transport', 'user_id' => null, 'is_default' => true]);
    $transaction = Transaction::factory()->for($user)->create(['category_id' => null, 'description' => 'Trip downtown']);

    fakeOpenAi('Transport');

    $updated = app(AiTransactionCategorizer::class)->categorizeUncategorized($user);

    expect($updated)->toBe(1);
    expect($transaction->refresh()->category->name)->toBe('Transport');
});

test('it leaves the transaction uncategorized when OpenAI suggests an unknown category', function () {
    $user = User::factory()->create();
    Category::factory()->create(['name' => 'Transport', 'user_id' => null, 'is_default' => true]);
    Transaction::factory()->for($user)->create(['category_id' => null, 'description' => 'Mystery charge']);

    fakeOpenAi('NotARealCategory');

    $updated = app(AiTransactionCategorizer::class)->categorizeUncategorized($user);

    expect($updated)->toBe(0);
});

test('it skips transactions with no description or narration without calling OpenAI', function () {
    $user = User::factory()->create();
    Category::factory()->create(['name' => 'Transport', 'user_id' => null, 'is_default' => true]);
    Transaction::factory()->for($user)->create(['category_id' => null, 'description' => null, 'narration' => null]);

    Http::fake();

    app(AiTransactionCategorizer::class)->categorizeUncategorized($user);

    Http::assertNothingSent();
});
