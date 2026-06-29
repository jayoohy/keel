<?php

namespace App\Http\Controllers;

use App\Models\BankConnection;
use App\Services\Audit\AuditLogger;
use App\Services\Mono\MonoClient;
use App\Services\Mono\MonoSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BankConnectionController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('bank-connections/index', [
            'connections' => $request->user()->bankConnections()->latest()->get(),
            'monoPublicKey' => config('services.mono.public_key'),
        ]);
    }

    public function store(
        Request $request,
        MonoClient $mono,
        MonoSyncService $syncService,
        AuditLogger $auditLogger,
    ): RedirectResponse {
        $validated = $request->validate([
            'code' => ['required', 'string'],
        ]);

        $monoAccountId = $mono->exchangeToken($validated['code']);

        $connection = BankConnection::updateOrCreate(
            ['mono_account_id' => $monoAccountId, 'user_id' => $request->user()->id],
            [
                // Mono has no separate bearer token per account — the account id
                // itself, paired with the app's secret key, is the credential.
                'access_token' => $monoAccountId,
                'status' => 'active',
                'connected_at' => now(),
                'disconnected_at' => null,
            ]
        );

        $syncService->sync($connection);

        $auditLogger->log('bank_connected', $request->user(), $connection->institution_name);

        return to_route('bank-connections.index');
    }

    public function destroy(Request $request, BankConnection $bankConnection, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($bankConnection->user_id === $request->user()->id, 403);

        $bankConnection->update([
            'status' => 'disconnected',
            'disconnected_at' => now(),
        ]);

        $auditLogger->log('bank_disconnected', $request->user(), $bankConnection->institution_name);

        return back();
    }
}
