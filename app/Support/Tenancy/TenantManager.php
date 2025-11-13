<?php

namespace App\Support\Tenancy;

use App\Models\SaasTenant;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class TenantManager
{
    protected ?SaasTenant $tenant = null;

    public function __construct(protected DatabaseManager $databaseManager)
    {
    }

    public function setTenant(?SaasTenant $tenant): void
    {
        $this->tenant = $tenant;
        if ($tenant) {
            $this->configureConnection($tenant);
        }
    }

    public function getTenant(): ?SaasTenant
    {
        return $this->tenant;
    }

    public function connectionName(): string
    {
        return config('tenancy.database.connection', 'tenant');
    }

    protected function configureConnection(SaasTenant $tenant): void
    {
        $managerConnection = config('tenancy.database.manager_connection', config('database.default'));
        $base = Config::get('database.connections.' . $managerConnection);
        $databaseName = $tenant->database;
        if (!$databaseName) {
            $databaseName = config('tenancy.database.prefix', 'real_estate_') . Str::slug($tenant->slug, '_');
        }
        $base['database'] = $databaseName;
        Config::set('database.connections.' . $this->connectionName(), $base);
        $this->databaseManager->purge($this->connectionName());
    }
}

