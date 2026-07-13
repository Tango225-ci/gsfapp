<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckUserStatus;
use App\Http\Middleware\ForcePasswordChange;
use App\Http\Middleware\ForceClientPasswordChange;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SetPortailSessionCookie;
use App\Http\Middleware\Api\EnsureClientPasswordNotForced;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(prepend: [
            SetPortailSessionCookie::class,
        ]);

        $middleware->alias([
            'check.status'          => CheckUserStatus::class,
            'role'                  => RoleMiddleware::class,
            'force.password.change' => ForcePasswordChange::class,
            'force.client.password.change' => ForceClientPasswordChange::class,
            'client.password.not.forced' => EnsureClientPasswordNotForced::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
