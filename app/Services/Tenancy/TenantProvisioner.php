<?php

namespace App\Services\Tenancy;

use App\Models\SaasTenant;
use App\Support\Tenancy\TenantManager;
use Illuminate\Database\DatabaseManager;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class TenantProvisioner
{
    public function __construct(
        protected DatabaseManager $databaseManager,
        protected Filesystem $filesystem,
        protected TenantManager $tenantManager
    ) {
    }

    public function provision(SaasTenant $tenant, array $payload = []): void
    {
        $this->createDatabase($tenant);
        $this->tenantManager->setTenant($tenant);
        $this->runMigrations();
        $this->runSeeder($payload);
        $this->ensureClientDirectory($tenant);
    }

    protected function createDatabase(SaasTenant $tenant): void
    {
        $managerConnection = config('tenancy.database.manager_connection', config('database.default'));
        $connection = $this->databaseManager->connection($managerConnection);
        $name = $tenant->database ?: config('tenancy.database.prefix', 'real_estate_') . Str::slug($tenant->slug, '_');
        $connection->statement('CREATE DATABASE IF NOT EXISTS `' . $name . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $tenant->database = $name;
        $tenant->save();
    }

    protected function runMigrations(): void
    {
        $path = config('tenancy.database.template_path');
        Artisan::call('migrate', [
            '--database' => $this->tenantManager->connectionName(),
            '--path' => $path,
            '--force' => true,
        ]);
    }

    protected function runSeeder(array $payload): void
    {
        $seeder = config('tenancy.database.seeder');
        if (!$seeder) {
            return;
        }

        Config::set('tenant.provisioning', $payload);
        Artisan::call('db:seed', [
            '--database' => $this->tenantManager->connectionName(),
            '--class' => $seeder,
            '--force' => true,
        ]);
        Config::set('tenant.provisioning', null);
    }

    protected function ensureClientDirectory(SaasTenant $tenant): void
    {
        $root = config('tenancy.paths.clients_root');
        $path = $root . '/' . $tenant->slug;
        if (!$this->filesystem->exists($path)) {
            $this->filesystem->makeDirectory($path, 0755, true);
        }
    }
}

