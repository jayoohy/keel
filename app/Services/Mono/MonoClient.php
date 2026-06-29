<?php

namespace App\Services\Mono;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around the Mono (withmono.com) open banking API. Mono has no
 * separate per-account "access token" — the account id returned from the
 * Connect widget exchange is the identifier used on every subsequent call,
 * authenticated by the app's own secret key.
 */
class MonoClient
{
    public function __construct(
        private readonly string $secretKey,
        private readonly string $baseUrl,
    ) {}

    /**
     * Exchange a Mono Connect widget code for the linked account's id.
     */
    public function exchangeToken(string $code): string
    {
        return $this->client()
            ->post('/v2/accounts/auth', ['code' => $code])
            ->throw()
            ->json('id');
    }

    public function getAccount(string $accountId): array
    {
        return $this->client()
            ->get("/v2/accounts/{$accountId}")
            ->throw()
            ->json();
    }

    public function getTransactions(string $accountId, int $page = 1): array
    {
        return $this->client()
            ->get("/v2/accounts/{$accountId}/transactions", ['page' => $page, 'paginate' => 'true'])
            ->throw()
            ->json();
    }

    public function sync(string $accountId): array
    {
        return $this->client()
            ->post("/v2/accounts/{$accountId}/sync")
            ->throw()
            ->json();
    }

    public function unlink(string $accountId): array
    {
        return $this->client()
            ->post("/v2/accounts/{$accountId}/unlink")
            ->throw()
            ->json();
    }

    protected function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->withHeaders(['mono-sec-key' => $this->secretKey])
            ->acceptJson();
    }
}
