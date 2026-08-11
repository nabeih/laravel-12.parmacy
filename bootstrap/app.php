<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureRole::class,
            'approved.pharmacy' => \App\Http\Middleware\EnsureApprovedPharmacy::class,
            'prevent-back-history' => \App\Http\Middleware\EnsureRole::class,
        ]);

        // These OTP endpoints take email/otp explicitly rather than relying on
        // the session, so they're safe to exempt for JSON/API-style clients (e.g. Postman).
        $middleware->validateCsrfTokens(except: [
            'verify-email',
            'resend-verification-code',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
