<?php

namespace Packages\Crm\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Packages\Crm\Models\Contact;
use Packages\Crm\Models\Lead;
use Packages\Crm\Models\Pipeline;

class ReportsController extends Controller
{
    public function index()
    {
        return view('crm::reports.index');
    }

    public function dashboardData(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $userId = $request->input('user_id');
        $stage = $request->input('stage');

        $contactsQuery = Contact::query();
        $leadsQuery = Lead::query();
        $dealsQuery = Pipeline::query();

        if ($dateFrom) {
            $contactsQuery->whereDate('created_at', '>=', $dateFrom);
            $leadsQuery->whereDate('created_at', '>=', $dateFrom);
            $dealsQuery->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $contactsQuery->whereDate('created_at', '<=', $dateTo);
            $leadsQuery->whereDate('created_at', '<=', $dateTo);
            $dealsQuery->whereDate('created_at', '<=', $dateTo);
        }

        if ($userId) {
            $contactsQuery->where('assigned_user_id', $userId);
            $leadsQuery->where('assigned_user_id', $userId);
            $dealsQuery->where('owner_user_id', $userId);
        }

        $totalContacts = $contactsQuery->count();
        $totalLeads = $leadsQuery->count();
        $totalDeals = $dealsQuery->count();

        $wonDealsQuery = clone $dealsQuery;
        $wonDeals = $wonDealsQuery->where('stage', 'closed_won')->count();

        $lostDealsQuery = clone $dealsQuery;
        $lostDeals = $lostDealsQuery->where('stage', 'closed_lost')->count();

        $conversionRate = $totalDeals > 0 ? round(($wonDeals / $totalDeals) * 100, 2) : 0;

        $revenueQuery = clone $dealsQuery;
        $totalRevenue = $revenueQuery->where('stage', 'closed_won')->sum('value') ?? 0;

        return response()->json([
            'success' => true,
            'data' => [
                'total_contacts' => $totalContacts,
                'total_leads' => $totalLeads,
                'total_deals' => $totalDeals,
                'won_deals' => $wonDeals,
                'lost_deals' => $lostDeals,
                'conversion_rate' => $conversionRate,
                'total_revenue' => number_format($totalRevenue, 2, '.', ''),
            ]
        ]);
    }

    public function chartData(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $userId = $request->input('user_id');
        $stage = $request->input('stage');
        $chartType = $request->input('chart_type');

        $dealsQuery = Pipeline::query();

        if ($dateFrom) {
            $dealsQuery->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $dealsQuery->whereDate('created_at', '<=', $dateTo);
        }

        if ($userId) {
            $dealsQuery->where('owner_user_id', $userId);
        }

        if ($chartType === 'deals_won_lost') {
            $wonCount = (clone $dealsQuery)->where('stage', 'closed_won')->count();
            $lostCount = (clone $dealsQuery)->where('stage', 'closed_lost')->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => ['Won', 'Lost'],
                    'values' => [$wonCount, $lostCount],
                    'colors' => ['rgba(16, 185, 129, 0.6)', 'rgba(239, 68, 68, 0.6)']
                ]
            ]);
        }

        if ($chartType === 'revenue_by_stage') {
            $stages = ['prospect', 'negotiation', 'proposal', 'closed_won', 'closed_lost'];
            $data = [];

            foreach ($stages as $stageName) {
                $stageQuery = clone $dealsQuery;
                $revenue = $stageQuery->where('stage', $stageName)->sum('value') ?? 0;
                
                $label = match($stageName) {
                    'prospect' => 'Prospect',
                    'negotiation' => 'Negotiation',
                    'proposal' => 'Proposal',
                    'closed_won' => 'Closed Won',
                    'closed_lost' => 'Closed Lost',
                    default => ucfirst($stageName),
                };

                $data[] = [
                    'stage' => $label,
                    'revenue' => (float) $revenue
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid chart type'
        ], 400);
    }

    public function datatable(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $userId = $request->input('user_id');
        $stage = $request->input('stage');

        $userIds = Pipeline::query()
            ->when($dateFrom, function($q) use ($dateFrom) {
                $q->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($dateTo, function($q) use ($dateTo) {
                $q->whereDate('created_at', '<=', $dateTo);
            })
            ->when($userId, function($q) use ($userId) {
                $q->where('owner_user_id', $userId);
            })
            ->when($stage, function($q) use ($stage) {
                $q->where('stage', $stage);
            })
            ->distinct()
            ->pluck('owner_user_id')
            ->filter()
            ->unique();

        $data = [];
        foreach ($userIds as $uid) {
            $userDealsQuery = Pipeline::query()->where('owner_user_id', $uid);
            
            if ($dateFrom) {
                $userDealsQuery->whereDate('created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $userDealsQuery->whereDate('created_at', '<=', $dateTo);
            }
            if ($stage) {
                $userDealsQuery->where('stage', $stage);
            }

            $totalDeals = $userDealsQuery->count();
            $wonDeals = (clone $userDealsQuery)->where('stage', 'closed_won')->count();
            $lostDeals = (clone $userDealsQuery)->where('stage', 'closed_lost')->count();
            $conversionRate = $totalDeals > 0 ? round(($wonDeals / $totalDeals) * 100, 2) : 0;
            
            $revenueQuery = clone $userDealsQuery;
            $totalRevenue = $revenueQuery->where('stage', 'closed_won')->sum('value') ?? 0;

            $data[] = [
                'user_id' => $uid,
                'user_name' => 'User ' . $uid,
                'total_deals' => $totalDeals,
                'won_deals' => $wonDeals,
                'lost_deals' => $lostDeals,
                'conversion_rate' => $conversionRate . '%',
                'total_revenue' => '$' . number_format($totalRevenue, 2),
            ];
        }

        if ($search = trim((string) $request->input('search.value'))) {
            $data = array_filter($data, function($item) use ($search) {
                return stripos($item['user_name'], $search) !== false ||
                       stripos((string)$item['user_id'], $search) !== false;
            });
        }

        $totalRecords = count($data);
        $filteredRecords = count($data);

        $orderColumn = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');

        $columns = ['user_id', 'user_name', 'total_deals', 'won_deals', 'lost_deals', 'conversion_rate', 'total_revenue'];
        $sortColumn = $columns[$orderColumn] ?? 'user_id';

        usort($data, function($a, $b) use ($sortColumn, $orderDir) {
            $aVal = $a[$sortColumn];
            $bVal = $b[$sortColumn];
            
            if (is_numeric($aVal)) {
                $aVal = (float) $aVal;
            }
            if (is_numeric($bVal)) {
                $bVal = (float) $bVal;
            }
            
            if ($orderDir === 'asc') {
                return $aVal <=> $bVal;
            }
            return $bVal <=> $aVal;
        });

        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $data = array_slice($data, $start, $length);

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => array_values($data),
        ]);
    }

    public function export(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $userId = $request->input('user_id');
        $stage = $request->input('stage');

        $userIds = Pipeline::query()
            ->when($dateFrom, function($q) use ($dateFrom) {
                $q->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($dateTo, function($q) use ($dateTo) {
                $q->whereDate('created_at', '<=', $dateTo);
            })
            ->when($userId, function($q) use ($userId) {
                $q->where('owner_user_id', $userId);
            })
            ->when($stage, function($q) use ($stage) {
                $q->where('stage', $stage);
            })
            ->distinct()
            ->pluck('owner_user_id')
            ->filter()
            ->unique();

        $rows = [];
        foreach ($userIds as $uid) {
            $userDealsQuery = Pipeline::query()->where('owner_user_id', $uid);
            
            if ($dateFrom) {
                $userDealsQuery->whereDate('created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $userDealsQuery->whereDate('created_at', '<=', $dateTo);
            }
            if ($stage) {
                $userDealsQuery->where('stage', $stage);
            }

            $totalDeals = $userDealsQuery->count();
            $wonDeals = (clone $userDealsQuery)->where('stage', 'closed_won')->count();
            $lostDeals = (clone $userDealsQuery)->where('stage', 'closed_lost')->count();
            $conversionRate = $totalDeals > 0 ? round(($wonDeals / $totalDeals) * 100, 2) : 0;
            
            $revenueQuery = clone $userDealsQuery;
            $totalRevenue = $revenueQuery->where('stage', 'closed_won')->sum('value') ?? 0;

            $rows[] = [
                'user_id' => $uid,
                'user_name' => 'User ' . $uid,
                'total_deals' => $totalDeals,
                'won_deals' => $wonDeals,
                'lost_deals' => $lostDeals,
                'conversion_rate' => $conversionRate . '%',
                'total_revenue' => number_format($totalRevenue, 2),
            ];
        }

        $filename = 'reports_export_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['User ID', 'User Name', 'Total Deals', 'Won Deals', 'Lost Deals', 'Conversion Rate', 'Total Revenue']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r['user_id'],
                    $r['user_name'],
                    $r['total_deals'],
                    $r['won_deals'],
                    $r['lost_deals'],
                    $r['conversion_rate'],
                    $r['total_revenue'],
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}

