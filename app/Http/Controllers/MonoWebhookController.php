<?php

namespace App\Http\Controllers;

use App\Jobs\SyncBankConnection;
use App\Models\BankConnection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MonoWebhookController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $secret = (string) config('services.mono.webhook_secret');
        $provided = (string) $request->header('mono-webhook-secret', '');

        abort_unless($secret !== '' && hash_equals($secret, $provided), 401);

        $event = (string) $request->input('event');
        $accountId = $request->input('data.account.id') ?? $request->input('data.id');

        $connection = BankConnection::where('mono_account_id', $accountId)->first();

        if (! $connection) {
            return response()->noContent();
        }

        match ($event) {
            'issues.reauthorisation_required' => $connection->update(['status' => 'reauth_required']),
            'mono.events.account_updated' => SyncBankConnection::dispatch($connection),
            default => null,
        };

        return response()->noContent();
    }
}
