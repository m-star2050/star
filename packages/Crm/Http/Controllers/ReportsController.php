<?php

namespace Packages\Crm\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Packages\Crm\Helpers\PermissionHelper;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Packages\Crm\Models\Contact;
use Packages\Crm\Models\Lead;
use Packages\Crm\Models\Pipeline;

class ReportsController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('view reports')) {
            abort(403, 'Unauthorized. You do not have permission to view reports.');
        }

        $users = collect([]);
        if (Schema::hasTable('users')) {
            try {
                $users = User::select('id', 'name', 'email')->orderBy('name')->get();
            } catch (\Exception $e) {
                $users = collect([]);
            }
        }
        return view('crm::reports.index', ['users' => $users]);
    }

    public function dashboardData(Request $request)
    {
        if (!auth()->user()->can('view reports')) {
            abort(403, 'Unauthorized. You do not have permission to view reports.');
        }

        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $userId = $request->input('user_id');
        $stage = $request->input('stage');

        $contactsQuery = Contact::query();
        $leadsQuery = Lead::query();
        $dealsQuery = Pipeline::query();

        // Filter by role (Executive sees only assigned records)
        $contactsQuery = PermissionHelper::filterByRole($contactsQuery, auth()->user(), 'assigned_user_id');
        $leadsQuery = PermissionHelper::filterByRole($leadsQuery, auth()->user(), 'assigned_user_id');
        $dealsQuery = PermissionHelper::filterByRole($dealsQuery, auth()->user(), null, 'owner_user_id');

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

        $data = [
            'total_contacts' => (int) $totalContacts,
            'total_leads' => (int) $totalLeads,
            'total_deals' => (int) $totalDeals,
            'won_deals' => (int) $wonDeals,
            'lost_deals' => (int) $lostDeals,
            'conversion_rate' => (float) $conversionRate,
            'total_revenue' => number_format((float) $totalRevenue, 2, '.', ''),
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function chartData(Request $request)
    {
        if (!auth()->user()->can('view reports')) {
            abort(403, 'Unauthorized. You do not have permission to view reports.');
        }

        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $userId = $request->input('user_id');
        $stage = $request->input('stage');
        $chartType = $request->input('chart_type');

        $dealsQuery = Pipeline::query();
        
        // Filter by role (Executive sees only assigned records)
        $dealsQuery = PermissionHelper::filterByRole($dealsQuery, auth()->user(), null, 'owner_user_id');

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
        try {
            // Defensive permission check
            $user = auth()->user();
            if (!$user) {
                abort(403, 'Unauthorized. Please login to access reports.');
            }
            
            // Check permission with error handling
            $hasPermission = false;
            try {
                $hasPermission = $user->can('view reports');
            } catch (\Exception $e) {
                \Log::error('Permission check exception in reports datatable: ' . $e->getMessage());
                // If permission system is broken, deny access for security
                abort(500, 'Permission system error. Please contact administrator.');
            }
            
            if (!$hasPermission) {
                abort(403, 'Unauthorized. You do not have permission to view reports.');
            }

            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');
            $userId = $request->input('user_id');
            $stage = $request->input('stage');

        // Get all user IDs that have deals, contacts, or leads
        $userIdsFromDeals = collect([]);
        try {
            $dealsQuery = Pipeline::query();
            $dealsQuery = PermissionHelper::filterByRole($dealsQuery, auth()->user(), null, 'owner_user_id');
            
            $userIdsFromDeals = $dealsQuery
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
                ->whereNotNull('owner_user_id')
                ->distinct()
                ->pluck('owner_user_id')
                ->filter()
                ->unique();
        } catch (\Exception $e) {
            \Log::warning('Error getting user IDs from deals: ' . $e->getMessage());
            $userIdsFromDeals = collect([]);
        }

        $userIdsFromContacts = collect([]);
        try {
            $contactsQuery = Contact::query();
            $contactsQuery = PermissionHelper::filterByRole($contactsQuery, auth()->user(), 'assigned_user_id');
            
            $userIdsFromContacts = $contactsQuery
                ->when($dateFrom, function($q) use ($dateFrom) {
                    $q->whereDate('created_at', '>=', $dateFrom);
                })
                ->when($dateTo, function($q) use ($dateTo) {
                    $q->whereDate('created_at', '<=', $dateTo);
                })
                ->when($userId, function($q) use ($userId) {
                    $q->where('assigned_user_id', $userId);
                })
                ->whereNotNull('assigned_user_id')
                ->distinct()
                ->pluck('assigned_user_id')
                ->filter()
                ->unique();
        } catch (\Exception $e) {
            \Log::warning('Error getting user IDs from contacts: ' . $e->getMessage());
            $userIdsFromContacts = collect([]);
        }

        $userIdsFromLeads = collect([]);
        try {
            $leadsQuery = Lead::query();
            $leadsQuery = PermissionHelper::filterByRole($leadsQuery, auth()->user(), 'assigned_user_id');
            
            $userIdsFromLeads = $leadsQuery
                ->when($dateFrom, function($q) use ($dateFrom) {
                    $q->whereDate('created_at', '>=', $dateFrom);
                })
                ->when($dateTo, function($q) use ($dateTo) {
                    $q->whereDate('created_at', '<=', $dateTo);
                })
                ->when($userId, function($q) use ($userId) {
                    $q->where('assigned_user_id', $userId);
                })
                ->whereNotNull('assigned_user_id')
                ->distinct()
                ->pluck('assigned_user_id')
                ->filter()
                ->unique();
        } catch (\Exception $e) {
            \Log::warning('Error getting user IDs from leads: ' . $e->getMessage());
            $userIdsFromLeads = collect([]);
        }

        // Combine all user IDs
        $allUserIds = $userIdsFromDeals
            ->merge($userIdsFromContacts)
            ->merge($userIdsFromLeads)
            ->unique()
            ->values();

        // If no user IDs found and no specific user filter, show a summary row
        if ($allUserIds->isEmpty() && !$userId) {
            try {
                // Show overall summary - but apply role filtering for Executive users
                $dealsQuery = Pipeline::query();
                $dealsQuery = PermissionHelper::filterByRole($dealsQuery, auth()->user(), null, 'owner_user_id');
                
                if ($dateFrom) {
                    $dealsQuery->whereDate('created_at', '>=', $dateFrom);
                }
                if ($dateTo) {
                    $dealsQuery->whereDate('created_at', '<=', $dateTo);
                }
                if ($stage) {
                    $dealsQuery->where('stage', $stage);
                }

                $totalDeals = $dealsQuery->count();
                $wonDeals = (clone $dealsQuery)->where('stage', 'closed_won')->count();
                $lostDeals = (clone $dealsQuery)->where('stage', 'closed_lost')->count();
                $conversionRate = $totalDeals > 0 ? round(($wonDeals / $totalDeals) * 100, 2) : 0;
                $totalRevenue = (clone $dealsQuery)->where('stage', 'closed_won')->sum('value') ?? 0;

                // For Executive users, show their own name instead of "All Users"
                $summaryLabel = 'All Users';
                try {
                    if (PermissionHelper::isExecutive(auth()->user())) {
                        $summaryLabel = auth()->user()->name ?? 'My Summary';
                    }
                } catch (\Exception $e) {
                    // Keep default label
                }

                $data = [[
                    'user_id' => auth()->user()->id ?? '-',
                    'user_name' => $summaryLabel,
                    'total_deals' => $totalDeals,
                    'won_deals' => $wonDeals,
                    'lost_deals' => $lostDeals,
                    'conversion_rate' => $conversionRate . '%',
                    'total_revenue' => '$' . number_format($totalRevenue, 2),
                ]];
            } catch (\Exception $e) {
                \Log::warning('Error building summary data: ' . $e->getMessage());
                $data = [];
            }
        } else {
            $data = [];
            foreach ($allUserIds as $uid) {
                try {
                // Apply role filtering to user-specific queries as well
                $userDealsQuery = Pipeline::query();
                $userDealsQuery = PermissionHelper::filterByRole($userDealsQuery, auth()->user(), null, 'owner_user_id');
                $userDealsQuery->where('owner_user_id', $uid);
                
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

                // Try to get user name if users table exists
                $userName = 'User ' . $uid;
                if (Schema::hasTable('users')) {
                    try {
                        $user = \App\Models\User::find($uid);
                        if ($user && $user->name) {
                            $userName = $user->name;
                        }
                    } catch (\Exception $e) {
                        // Keep default name
                    }
                }

                    $data[] = [
                        'user_id' => $uid,
                        'user_name' => $userName,
                        'total_deals' => $totalDeals,
                        'won_deals' => $wonDeals,
                        'lost_deals' => $lostDeals,
                        'conversion_rate' => $conversionRate . '%',
                        'total_revenue' => '$' . number_format($totalRevenue, 2),
                    ];
                } catch (\Exception $e) {
                    \Log::warning('Error processing user data for user ID ' . $uid . ': ' . $e->getMessage());
                    // Continue to next user
                }
            }
        }

        // Ensure data is an array
        if (!is_array($data)) {
            $data = [];
        }

        if ($search = trim((string) $request->input('search.value'))) {
            $data = array_filter($data, function($item) use ($search) {
                if (!is_array($item)) {
                    return false;
                }
                $userName = $item['user_name'] ?? '';
                $userId = $item['user_id'] ?? '';
                return stripos($userName, $search) !== false ||
                       stripos((string)$userId, $search) !== false;
            });
        }

        $totalRecords = count($data);
        $filteredRecords = count($data);

        $orderColumn = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');

        $columns = ['user_id', 'user_name', 'total_deals', 'won_deals', 'lost_deals', 'conversion_rate', 'total_revenue'];
        $sortColumn = $columns[$orderColumn] ?? 'user_id';

        // Only sort if we have data
        if (count($data) > 0) {
            usort($data, function($a, $b) use ($sortColumn, $orderDir) {
                if (!is_array($a) || !is_array($b)) {
                    return 0;
                }
                
                $aVal = $a[$sortColumn] ?? '';
                $bVal = $b[$sortColumn] ?? '';
                
                // Handle percentage strings (e.g., "50%")
                if (is_string($aVal) && strpos($aVal, '%') !== false) {
                    $aVal = (float) str_replace('%', '', $aVal);
                }
                if (is_string($bVal) && strpos($bVal, '%') !== false) {
                    $bVal = (float) str_replace('%', '', $bVal);
                }
                
                // Handle currency strings (e.g., "$1,234.56")
                if (is_string($aVal) && strpos($aVal, '$') !== false) {
                    $aVal = (float) str_replace(['$', ','], '', $aVal);
                }
                if (is_string($bVal) && strpos($bVal, '$') !== false) {
                    $bVal = (float) str_replace(['$', ','], '', $bVal);
                }
                
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
        }

        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $data = array_slice($data, $start, $length);

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => array_values($data),
        ]);
        } catch (\Exception $e) {
            // Safely get user role for logging
            $userRole = 'unknown';
            try {
                if (auth()->check() && method_exists(auth()->user(), 'roles')) {
                    $userRole = auth()->user()->roles->pluck('name')->first() ?? 'none';
                }
            } catch (\Exception $roleEx) {
                // Ignore role lookup errors
            }
            
            \Log::error('Reports datatable error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id(),
                'user_role' => $userRole,
                'request' => $request->all()
            ]);
            
            // Return empty DataTables response on error
            // Don't include 'error' field as DataTables will show it as a warning
            return response()->json([
                'draw' => (int) $request->input('draw', 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ]);
        }
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

