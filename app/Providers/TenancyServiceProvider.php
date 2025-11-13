<?php

namespace App\Providers;

use App\Support\Tenancy\TenantManager;
use Illuminate\Database\DatabaseManager;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

class TenancyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(base_path('config/tenancy.php'), 'tenancy');

        $this->app->singleton(TenantManager::class, function ($app) {
            return new TenantManager($app->make(DatabaseManager::class));
        });
    }

    public function boot(): void
    {
        $filesystem = $this->app->make(Filesystem::class);
        $directories = [
            config('tenancy.paths.clients_root'),
            config('tenancy.paths.global_root'),
            config('tenancy.paths.storage_root'),
        ];

        foreach ($directories as $directory) {
            if ($directory && !$filesystem->exists($directory)) {
                $filesystem->makeDirectory($directory, 0755, true);
            }
        }
    }
}

