<?php

use App\Providers\TenancyServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withProviders([
        TenancyServiceProvider::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'tenant' => \App\Http\Middleware\IdentifyTenant::class,
            'global.admin' => \App\Http\Middleware\EnsureGlobalAdmin::class,
        ]);
        $middleware->appendToGroup('web', \App\Http\Middleware\IdentifyTenant::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
