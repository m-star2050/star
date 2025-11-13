<?php

namespace App\Http\Middleware;

use App\Models\SaasTenant;
use App\Support\Tenancy\TenantManager;
use Closure;
use Illuminate\Http\Request;

class IdentifyTenant
{
    public function __construct(protected TenantManager $tenantManager)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        if ($request->segment(1) === 'realestate' && $request->segment(2) && $request->segment(2) !== 'global') {
            $tenant = SaasTenant::where('slug', $request->segment(2))->first();
            if (!$tenant) {
                abort(404);
            }
            $this->tenantManager->setTenant($tenant);
        } else {
            $this->tenantManager->setTenant(null);
        }

        return $next($request);
    }
}

