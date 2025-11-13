<?php

namespace App\Http\Controllers\RealEstate\Global;

use App\Http\Controllers\Controller;
use App\Models\SaasPlan;
use App\Models\SaasTenant;
use App\Services\Tenancy\TenantProvisioner;
use Carbon\Carbon;
use Illuminate\Database\DatabaseManager;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    public function __construct(
        protected TenantProvisioner $provisioner,
        protected DatabaseManager $databaseManager,
        protected Filesystem $filesystem
    ) {
    }

    public function list(): JsonResponse
    {
        $tenants = SaasTenant::with('plan')->latest()->get()->map(function (SaasTenant $tenant) {
            return [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'plan' => $tenant->plan?->name,
                'plan_id' => $tenant->plan_id,
                'status' => $tenant->status,
                'database' => $tenant->database,
                'activated_at' => optional($tenant->activated_at)->toDateTimeString(),
                'expires_at' => optional($tenant->expires_at)->toDateString(),
                'created_at' => $tenant->created_at->toDateTimeString(),
                'updated_at' => $tenant->updated_at->toDateTimeString(),
            ];
        });

        return response()->json(['data' => $tenants]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'email' => ['nullable', 'email'],
            'plan_id' => ['nullable', 'exists:saas_plans,id'],
            'slug' => ['nullable', 'alpha_dash', 'max:190'],
            'database' => ['nullable', 'string', 'max:190'],
        ]);

        $slugBase = $validated['slug'] ?? $validated['name'];
        $slug = Str::slug($slugBase);
        if ($slug === '') {
            $slug = Str::slug(Str::random(10));
        }
        if (SaasTenant::where('slug', $slug)->exists()) {
            $slug .= '-' . Str::lower(Str::random(4));
        }

        $code = Str::upper(Str::random(8));
        while (SaasTenant::where('code', $code)->exists()) {
            $code = Str::upper(Str::random(8));
        }

        $databaseName = $validated['database'] ?: config('tenancy.database.prefix') . $slug;
        $databaseName = preg_replace('/[^A-Za-z0-9_]/', '_', strtolower($databaseName));
        if (SaasTenant::where('database', $databaseName)->exists()) {
            $databaseName .= '_' . Str::lower(Str::random(3));
        }

        $plan = null;
        if (!empty($validated['plan_id'])) {
            $plan = SaasPlan::find($validated['plan_id']);
        }

        $tenant = SaasTenant::create([
            'plan_id' => $plan?->id,
            'name' => $validated['name'],
            'slug' => $slug,
            'code' => $code,
            'database' => $databaseName,
            'path' => 'realestate/' . $slug,
            'status' => 'pending',
            'limits' => $plan?->features,
        ]);

        $this->provisioner->provision($tenant, [
            'admin' => [
                'email' => $validated['email'] ?? null,
                'name' => $validated['name'],
            ],
            'plan' => $plan?->toArray(),
        ]);

        $tenant->status = 'active';
        $tenant->activated_at = now();
        $tenant->save();

        return response()->json([
            'message' => 'Tenant created',
            'tenant' => $tenant->fresh('plan'),
        ]);
    }

    public function update(Request $request, SaasTenant $tenant): JsonResponse
    {
        $validated = $request->validate([
            'plan_id' => ['nullable', 'exists:saas_plans,id'],
            'expires_at' => ['nullable', 'date'],
        ]);

        if (array_key_exists('plan_id', $validated)) {
            $plan = $validated['plan_id'] ? SaasPlan::find($validated['plan_id']) : null;
            $tenant->plan_id = $plan?->id;
            $tenant->limits = $plan?->features;
        }

        if (array_key_exists('expires_at', $validated)) {
            $tenant->expires_at = $validated['expires_at'] ? Carbon::parse($validated['expires_at']) : null;
        }

        $tenant->save();

        return response()->json([
            'message' => 'Tenant updated',
            'tenant' => $tenant->fresh('plan'),
        ]);
    }

    public function status(Request $request, SaasTenant $tenant): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:active,suspended,pending'],
        ]);

        $tenant->status = $validated['status'];
        if ($validated['status'] === 'suspended') {
            $tenant->suspended_at = now();
        } else {
            $tenant->suspended_at = null;
        }
        if ($validated['status'] === 'active' && !$tenant->activated_at) {
            $tenant->activated_at = now();
        }
        $tenant->save();

        return response()->json([
            'message' => 'Status updated',
            'tenant' => $tenant,
        ]);
    }

    public function destroy(SaasTenant $tenant): JsonResponse
    {
        $managerConnection = config('tenancy.database.manager_connection', config('database.default'));
        $connection = $this->databaseManager->connection($managerConnection);
        if ($tenant->database) {
            $connection->statement('DROP DATABASE IF EXISTS `' . $tenant->database . '`');
        }

        $path = config('tenancy.paths.clients_root') . '/' . $tenant->slug;
        if ($this->filesystem->exists($path)) {
            $this->filesystem->deleteDirectory($path);
        }

        $tenant->delete();

        return response()->json(['message' => 'Tenant deleted']);
    }
}

