<?php

namespace App\Http\Controllers\RealEstate\Global;

use App\Models\SaasPlan;
use App\Models\SaasTenant;
use Illuminate\Support\Facades\DB;

class DashboardController
{
    public function __invoke()
    {
        $totalTenants = SaasTenant::count();
        $activeTenants = SaasTenant::where('status', 'active')->count();
        $suspendedTenants = SaasTenant::where('status', 'suspended')->count();
        $expiredTenants = SaasTenant::whereNotNull('expires_at')->where('expires_at', '<', now())->count();

        $planBreakdown = SaasTenant::select('plan_id', DB::raw('count(*) as total'))
            ->groupBy('plan_id')
            ->get()
            ->mapWithKeys(function ($row) {
                $label = $row->plan?->name ?? 'Unassigned';
                return [$label => $row->total];
            });

        $recentTenants = SaasTenant::latest()->limit(5)->get();
        $plans = SaasPlan::active()->get();

        return view('realestate.global.admin.dashboard', [
            'metrics' => [
                'total' => $totalTenants,
                'active' => $activeTenants,
                'suspended' => $suspendedTenants,
                'expired' => $expiredTenants,
            ],
            'planBreakdown' => $planBreakdown,
            'recentTenants' => $recentTenants,
            'plans' => $plans,
        ]);
    }
}

