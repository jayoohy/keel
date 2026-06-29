<?php

namespace App\Providers;

use App\Listeners\AuditLogSubscriber;
use App\Services\AI\OpenAiClient;
use App\Services\Mono\MonoClient;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(MonoClient::class, fn () => new MonoClient(
            config('services.mono.secret_key'),
            config('services.mono.base_url'),
        ));

        $this->app->singleton(OpenAiClient::class, fn () => new OpenAiClient(
            config('services.openai.api_key'),
            config('services.openai.model'),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::subscribe(AuditLogSubscriber::class);
    }
}
