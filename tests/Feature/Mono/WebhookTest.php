<?php

use App\Jobs\SyncBankConnection;
use App\Models\BankConnection;
use App\Models\User;
use Illuminate\Support\Facades\Bus;

beforeEach(function () {
    config(['services.mono.webhook_secret' => 'test-webhook-secret']);
});

test('webhook requests without the correct secret are rejected', function () {
    $response = $this->postJson('/webhooks/mono', ['event' => 'mono.events.account_updated']);

    $response->assertStatus(401);
});

test('webhook requests with the correct secret are accepted', function () {
    $response = $this->withHeaders(['mono-webhook-secret' => 'test-webhook-secret'])
        ->postJson('/webhooks/mono', ['event' => 'mono.events.account_updated', 'data' => ['id' => 'unknown']]);

    $response->assertNoContent();
});

test('an account_updated event queues a sync job for the matching connection', function () {
    Bus::fake();

    $user = User::factory()->create();
    $connection = BankConnection::factory()->for($user)->create(['mono_account_id' => 'mono_acc_123']);

    $this->withHeaders(['mono-webhook-secret' => 'test-webhook-secret'])
        ->postJson('/webhooks/mono', [
            'event' => 'mono.events.account_updated',
            'data' => ['id' => 'mono_acc_123'],
        ])
        ->assertNoContent();

    Bus::assertDispatched(SyncBankConnection::class, fn ($job) => $job->bankConnection->is($connection));
});

test('a reauthorisation_required event marks the connection accordingly', function () {
    $user = User::factory()->create();
    $connection = BankConnection::factory()->for($user)->create(['mono_account_id' => 'mono_acc_123']);

    $this->withHeaders(['mono-webhook-secret' => 'test-webhook-secret'])
        ->postJson('/webhooks/mono', [
            'event' => 'issues.reauthorisation_required',
            'data' => ['id' => 'mono_acc_123'],
        ])
        ->assertNoContent();

    expect($connection->refresh()->status)->toBe('reauth_required');
});
