<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around OpenAI's Chat Completions API. Every call site in
 * this app must run inside a scheduled/queued job, never synchronously on a
 * user request — Hostinger shared hosting's PHP execution limit can't
 * absorb a slow third-party API call on the request/response cycle.
 */
class OpenAiClient
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $model,
    ) {}

    public function chat(array $messages, array $options = []): string
    {
        $response = Http::withToken($this->apiKey)
            ->baseUrl('https://api.openai.com/v1')
            ->post('/chat/completions', [
                'model' => $options['model'] ?? $this->model,
                'messages' => $messages,
                'temperature' => $options['temperature'] ?? 0.3,
            ])
            ->throw();

        return trim((string) $response->json('choices.0.message.content', ''));
    }
}
