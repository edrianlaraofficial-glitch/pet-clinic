<?php

use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureVerifiedEmail;
use App\Http\Middleware\SetSecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(SetSecurityHeaders::class);
        $middleware->alias([
            'admin' => EnsureAdmin::class,
            'email.verified' => EnsureVerifiedEmail::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
