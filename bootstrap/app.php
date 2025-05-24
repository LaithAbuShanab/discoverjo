<?php

use App\Http\Middleware\ApiKeyMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php', // ✅ Added channels route
        health: '/up',
        then: function () {
            Route::middleware(['api', 'guest', 'apiKey', 'throttle:30,1'])
                ->prefix('api')
                ->name('api.')
                ->group(base_path('routes/api/user/without_authentication.php'));

            Route::middleware(['api', 'auth:api', 'verifiedEmail', 'inactiveUser', 'apiKey', 'throttle:30,1'])
                ->prefix('api')
                ->name('api.')
                ->group(base_path('routes/api/user/with_authentication.php'));
        },

    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->web(append: [
            \App\Http\Middleware\language::class,
            \App\Http\Middleware\RedirectIfAuthenticated::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
        ]);


        $middleware->api(append: [
            \App\Http\Middleware\languageApi::class,
            \App\Http\Middleware\ApiKeyMiddleware::class,
            \App\Http\Middleware\TrackVisits::class,
        ]);

        $middleware->alias([
            'verifiedEmail' => \App\Http\Middleware\EnsureEmailVerified::class,
            'lang' => \App\Http\Middleware\language::class,
            'firstLogin' => \App\Http\Middleware\FirstLoginMiddleware::class,
            'inactiveUser' => \App\Http\Middleware\ActiveUserMiddleware::class,
            'CheckTripStatus' => \App\Http\Middleware\CheckTripStatus::class,
            'apiKey' => ApiKeyMiddleware::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'langApi' => \App\Http\Middleware\languageApi::class,
            'signed' => Illuminate\Routing\Middleware\ValidateSignature::class,
            'enforcePasswordReset' => \App\Http\Middleware\EnforcePasswordReset::class,
            'sanitize'=>App\Http\Middleware\SanitizeInputMiddleware::class,
//            'csp'=>\App\Http\Middleware\AddCspHeaderToApiMiddleware::class,
        ]);
    })
    ->withProviders([
        App\Providers\ScheduleServiceProvider::class,
    ])

    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
