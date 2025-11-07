<?php

namespace Packages\Crm\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Packages\Crm\Http\Middleware\CrmRoleMiddleware;
use Packages\Crm\Http\Middleware\CrmRoleAccessMiddleware;

class CrmServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'crm');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        
        // Register middleware
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('crm.role', CrmRoleMiddleware::class);
        $router->aliasMiddleware('crm.access', CrmRoleAccessMiddleware::class);
    }
}
