<?php

namespace Packages\Crm\Http\Controllers;

use App\Http\Controllers\Controller;
use Packages\Crm\Models\Contact;
use Packages\Crm\Models\Lead;
use Packages\Crm\Models\Task;
use Packages\Crm\Models\Pipeline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportsController extends Controller
{
    /**
     * Display analytics dashboard
     */
    public function index(Request $request)
    {
        // Get date range from request or default to last 30 days
        $dateFrom = $request->input('date_from', Carbon::now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->format('Y-m-d'));
        $userId = $request->input('user_id');
        $stage = $request->input('stage');

        // Overview Statistics
        $stats = [
            'total_contacts' => Contact::when($dateFrom && $dateTo, function($q) use ($dateFrom, $dateTo) {
                return $q->whereBetween('created_at', [$dateFrom, $dateTo]);
            })->count(),
            
            'total_leads' => Lead::when($dateFrom && $dateTo, function($q) use ($dateFrom, $dateTo) {
                return $q->whereBetween('created_at', [$dateFrom, $dateTo]);
            })->count(),
            
            'total_tasks' => Task::when($dateFrom && $dateTo, function($q) use ($dateFrom, $dateTo) {
                return $q->whereBetween('created_at', [$dateFrom, $dateTo]);
            })->count(),
            
            'total_deals' => Pipeline::when($dateFrom && $dateTo, function($q) use ($dateFrom, $dateTo) {
                return $q->whereBetween('created_at', [$dateFrom, $dateTo]);
            })->count(),
            
            'deals_won' => Pipeline::where('stage', 'closed_won')
                ->when($dateFrom && $dateTo, function($q) use ($dateFrom, $dateTo) {
                    return $q->whereBetween('created_at', [$dateFrom, $dateTo]);
                })->count(),
            
            'deals_lost' => Pipeline::where('stage', 'closed_lost')
                ->when($dateFrom && $dateTo, function($q) use ($dateFrom, $dateTo) {
                    return $q->whereBetween('created_at', [$dateFrom, $dateTo]);
                })->count(),
            
            'total_revenue' => Pipeline::where('stage', 'closed_won')
                ->when($dateFrom && $dateTo, function($q) use ($dateFrom, $dateTo) {
                    return $q->whereBetween('created_at', [$dateFrom, $dateTo]);
                })->sum('value'),
            
            'pending_revenue' => Pipeline::whereIn('stage', ['prospect', 'negotiation', 'proposal'])
                ->when($dateFrom && $dateTo, function($q) use ($dateFrom, $dateTo) {
                    return $q->whereBetween('created_at', [$dateFrom, $dateTo]);
                })->sum('value'),
        ];

        // Lead Conversion Rate
        $totalLeads = Lead::when($dateFrom && $dateTo, function($q) use ($dateFrom, $dateTo) {
            return $q->whereBetween('created_at', [$dateFrom, $dateTo]);
        })->count();
        
        $convertedLeads = Lead::where('stage', 'won')
            ->when($dateFrom && $dateTo, function($q) use ($dateFrom, $dateTo) {
                return $q->whereBetween('created_at', [$dateFrom, $dateTo]);
            })->count();
        
        $stats['conversion_rate'] = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 2) : 0;

        // Deals by Stage
        $dealsByStage = Pipeline::select('stage', DB::raw('count(*) as count'), DB::raw('sum(value) as total_value'))
            ->when($dateFrom && $dateTo, function($q) use ($dateFrom, $dateTo) {
                return $q->whereBetween('created_at', [$dateFrom, $dateTo]);
            })
            ->when($userId, function($q) use ($userId) {
                return $q->where('owner_user_id', $userId);
            })
            ->groupBy('stage')
            ->get();

        // Monthly Revenue Trend (last 6 months)
        $dbDriver = config('database.default');
        $connection = config("database.connections.{$dbDriver}.driver");
        
        if ($connection === 'sqlite') {
            $monthlyRevenue = Pipeline::where('stage', 'closed_won')
                ->where('created_at', '>=', Carbon::now()->subMonths(6))
                ->select(
                    DB::raw("cast(strftime('%m', created_at) as integer) as month"),
                    DB::raw("cast(strftime('%Y', created_at) as integer) as year"),
                    DB::raw('sum(value) as revenue')
                )
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get();
        } else {
            // MySQL/MariaDB
            $monthlyRevenue = Pipeline::where('stage', 'closed_won')
                ->where('created_at', '>=', Carbon::now()->subMonths(6))
                ->select(
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('sum(value) as revenue')
                )
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get();
        }

        // Lead Source Performance
        $leadsBySource = Lead::select('source', DB::raw('count(*) as count'))
            ->when($dateFrom && $dateTo, function($q) use ($dateFrom, $dateTo) {
                return $q->whereBetween('created_at', [$dateFrom, $dateTo]);
            })
            ->whereNotNull('source')
            ->groupBy('source')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        // User Performance
        $userPerformance = Pipeline::select(
                'owner_user_id',
                DB::raw('count(*) as total_deals'),
                DB::raw('sum(case when stage = "closed_won" then 1 else 0 end) as won_deals'),
                DB::raw('sum(case when stage = "closed_lost" then 1 else 0 end) as lost_deals'),
                DB::raw('sum(case when stage = "closed_won" then value else 0 end) as total_revenue')
            )
            ->when($dateFrom && $dateTo, function($q) use ($dateFrom, $dateTo) {
                return $q->whereBetween('created_at', [$dateFrom, $dateTo]);
            })
            ->whereNotNull('owner_user_id')
            ->groupBy('owner_user_id')
            ->orderBy('total_revenue', 'desc')
            ->get();

        // Tasks Status Distribution
        $tasksByStatus = Task::select('status', DB::raw('count(*) as count'))
            ->when($dateFrom && $dateTo, function($q) use ($dateFrom, $dateTo) {
                return $q->whereBetween('created_at', [$dateFrom, $dateTo]);
            })
            ->groupBy('status')
            ->get();

        return view('crm::reports.index', compact(
            'stats',
            'dealsByStage',
            'monthlyRevenue',
            'leadsBySource',
            'userPerformance',
            'tasksByStatus',
            'dateFrom',
            'dateTo',
            'userId',
            'stage'
        ));
    }

    /**
     * Export reports to CSV
     */
    public function export(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->format('Y-m-d'));

        $data = Pipeline::select(
                'deal_name',
                'stage',
                'value',
                'owner_user_id',
                'close_date',
                'probability',
                'company',
                'created_at'
            )
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->get();

        $filename = 'crm_report_' . date('Y-m-d_His') . '.csv';
        $handle = fopen('php://output', 'w');
        
        ob_start();
        
        // Headers
        fputcsv($handle, ['Deal Name', 'Stage', 'Value', 'Owner ID', 'Close Date', 'Probability %', 'Company', 'Created At']);
        
        // Data
        foreach ($data as $row) {
            fputcsv($handle, [
                $row->deal_name,
                ucfirst(str_replace('_', ' ', $row->stage)),
                '$' . number_format($row->value, 2),
                $row->owner_user_id ?? '-',
                $row->close_date ?? '-',
                $row->probability ?? '-',
                $row->company ?? '-',
                $row->created_at->format('Y-m-d H:i:s')
            ]);
        }
        
        fclose($handle);
        $csv = ob_get_clean();

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}

