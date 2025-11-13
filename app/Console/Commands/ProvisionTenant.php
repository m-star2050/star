<?php

namespace App\Console\Commands;

use App\Models\SaasPlan;
use App\Models\SaasTenant;
use App\Services\Tenancy\TenantProvisioner;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ProvisionTenant extends Command
{
    protected $signature = 'tenant:provision {name} {--plan=} {--email=} {--code=} {--database=} {--path=}';

    protected $description = 'Provision a new real estate tenant instance';

    public function handle(TenantProvisioner $provisioner): int
    {
        $name = trim($this->argument('name'));
        if ($name === '') {
            $this->error('Name is required.');
            return self::FAILURE;
        }

        $slugCandidate = $this->option('path') ?: $name;
        $slug = Str::slug($slugCandidate);
        if ($slug === '') {
            $slug = Str::slug(Str::random(8));
        }
        if (SaasTenant::where('slug', $slug)->exists()) {
            $slug .= '-' . Str::lower(Str::random(4));
        }

        $code = $this->option('code') ?: Str::upper(Str::random(8));
        if (SaasTenant::where('code', $code)->exists()) {
            $code .= Str::upper(Str::random(2));
        }

        $database = $this->sanitizeDatabaseName($this->option('database') ?: config('tenancy.database.prefix') . $slug);
        if (SaasTenant::where('database', $database)->exists()) {
            $database .= '_' . Str::lower(Str::random(3));
        }

        $plan = null;
        if ($planOption = $this->option('plan')) {
            $plan = SaasPlan::where('slug', $planOption)
                ->orWhere('code', $planOption)
                ->orWhere('id', $planOption)
                ->first();
            if (!$plan) {
                $this->error('Plan not found.');
                return self::FAILURE;
            }
        }

        $email = $this->option('email');

        $tenant = SaasTenant::create([
            'plan_id' => $plan?->id,
            'name' => $name,
            'slug' => $slug,
            'code' => $code,
            'database' => $database,
            'path' => 'realestate/' . $slug,
            'status' => 'pending',
            'limits' => $plan?->features,
        ]);

        $provisioner->provision($tenant, [
            'admin' => [
                'email' => $email,
                'name' => $name,
            ],
            'plan' => $plan?->toArray(),
        ]);

        $tenant->status = 'active';
        $tenant->activated_at = now();
        $tenant->save();

        $this->info('Tenant provisioned: ' . $tenant->slug);

        return self::SUCCESS;
    }

    protected function sanitizeDatabaseName(string $name): string
    {
        $normalized = preg_replace('/[^A-Za-z0-9_]/', '_', $name);
        if (is_numeric(substr($normalized, 0, 1))) {
            $normalized = 't_' . $normalized;
        }
        return strtolower($normalized);
    }
}

