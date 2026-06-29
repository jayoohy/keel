<?php

use App\Models\BankConnection;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Http;

function fakeMonoApi(string $accountId = 'mono_acc_123'): void
{
    Http::fake([
        '*/v2/accounts/auth' => Http::response(['id' => $accountId]),
        "*/v2/accounts/{$accountId}" => Http::response([
            'data' => [
                'account' => [
                    'name' => 'Test Savings',
                    'accountNumber' => '0123456789',
                    'type' => 'SAVINGS_ACCOUNT',
                    'currency' => 'NGN',
                    'balance' => 500000,
                ],
                'institution' => ['name' => 'Test Bank', 'logo' => 'https://example.com/logo.png'],
            ],
        ]),
        "*/v2/accounts/{$accountId}/transactions*" => Http::response([
            'data' => [
                [
                    'id' => 'txn_1',
                    'amount' => 250000,
                    'type' => 'credit',
                    'narration' => 'Salary payment',
                    'currency' => 'NGN',
                    'balance' => 500000,
                    'date' => now()->toIso8601String(),
                ],
            ],
            'meta' => ['total_pages' => 1],
        ]),
    ]);
}

test('connecting a bank account exchanges the code and imports the account + transactions', function () {
    $user = User::factory()->create();
    fakeMonoApi();

    $response = $this->actingAs($user)->post('/bank-connections', ['code' => 'widget_code_abc']);

    $response->assertRedirect(route('bank-connections.index'));

    $connection = BankConnection::where('user_id', $user->id)->first();
    expect($connection)->not->toBeNull();
    expect($connection->mono_account_id)->toBe('mono_acc_123');
    expect($connection->institution_name)->toBe('Test Bank');
    expect($connection->status)->toBe('active');

    expect($connection->accounts()->count())->toBe(1);
    expect(Transaction::where('mono_transaction_id', 'txn_1')->exists())->toBeTrue();
});

test('syncing twice does not duplicate transactions', function () {
    $user = User::factory()->create();
    fakeMonoApi();

    $this->actingAs($user)->post('/bank-connections', ['code' => 'widget_code_abc']);
    $connection = BankConnection::where('user_id', $user->id)->first();

    app(App\Services\Mono\MonoSyncService::class)->sync($connection->refresh());

    expect(Transaction::where('mono_transaction_id', 'txn_1')->count())->toBe(1);
});

test('disconnecting a bank account marks it disconnected', function () {
    $user = User::factory()->create();
    fakeMonoApi();

    $this->actingAs($user)->post('/bank-connections', ['code' => 'widget_code_abc']);
    $connection = BankConnection::where('user_id', $user->id)->first();

    $this->actingAs($user)->delete("/bank-connections/{$connection->id}");

    expect($connection->refresh()->status)->toBe('disconnected');
});

test('a user cannot disconnect another user\'s bank connection', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    fakeMonoApi();

    $this->actingAs($owner)->post('/bank-connections', ['code' => 'widget_code_abc']);
    $connection = BankConnection::where('user_id', $owner->id)->first();

    $this->actingAs($intruder)->delete("/bank-connections/{$connection->id}")->assertForbidden();
});
