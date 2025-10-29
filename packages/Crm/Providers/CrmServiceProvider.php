<?php

namespace Packages\Crm\Providers;

use Illuminate\Support\ServiceProvider;

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
    }
}
