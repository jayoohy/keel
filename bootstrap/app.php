<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Mono's webhook POSTs originate from their servers, not a browser
        // session, so they can't carry a CSRF token. Verified via a shared
        // secret header instead (see MonoWebhookController).
        $middleware->validateCsrfTokens(except: [
            'webhooks/mono',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
