<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))

    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware): void {

        /*
        |--------------------------------------------------------------------------
        | CSRF Exceptions
        |--------------------------------------------------------------------------
        |
        | Webhooks son llamadas server-to-server.
        | No usan sesión ni cookies.
        | Deben estar fuera de protección CSRF.
        |
        */

        $middleware->validateCsrfTokens(except: [
            'webhooks/fake',
            'payments/webpay/return',   // Transbank: browser redirect GET
            'webhooks/mercadopago',     // MercadoPago: webhook server-to-server
        ]);

    })

    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })

    ->create();