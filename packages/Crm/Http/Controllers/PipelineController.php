<?php

namespace Packages\Crm\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Packages\Crm\Models\Pipeline;
use Packages\Crm\Models\Contact;
use Packages\Crm\Helpers\PermissionHelper;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class PipelineController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('view pipeline')) {
            abort(403, 'Unauthorized. You do not have permission to view pipeline.');
        }

        // Get users for dropdown, excluding admins for non-admin users
        $users = PermissionHelper::getUsersForSelection();
        return view('crm::pipeline.index', ['users' => $users]);
    }

    public function kanban(Request $request)
    {
        if (!auth()->user()->can('view pipeline')) {
            abort(403, 'Unauthorized. You do not have permission to view pipeline.');
        }

        // Get users for dropdown, excluding admins for non-admin users
        $users = PermissionHelper::getUsersForSelection();
        return view('crm::pipeline.index', ['view' => 'kanban', 'users' => $users]);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('create pipeline')) {
            abort(403, 'Unauthorized. You do not have permission to create pipeline.');
        }

        try {
            $data = $request->validate([
                'deal_name' => ['required', 'string', 'max:255'],
                'stage' => ['required', Rule::in(['prospect', 'negotiation', 'proposal', 'closed_won', 'closed_lost'])],
                'value' => ['required', 'numeric', 'min:0'],
                'owner_user_id' => ['nullable', 'integer'],
                'close_date' => ['nullable', 'date'],
                'probability' => ['nullable', 'integer', 'min:0', 'max:100'],
                'contact_id' => ['nullable', 'integer', 'exists:crm_contacts,id'],
                'company' => ['nullable', 'string', 'max:255'],
                'notes' => ['nullable', 'string'],
            ]);

            // Set user_id to current user's ID (users can only create their own records)
            $data['user_id'] = auth()->id();

            $pipeline = Pipeline::create($data);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Deal created successfully',
                    'deal' => $pipeline
                ]);
            }
            
            return redirect()->route('crm.pipeline.index')->with('status', 'Deal created');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            Log::error('Deal creation error: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Deal creation failed: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('crm.pipeline.index')->with('error', 'Deal creation failed: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Pipeline $pipeline)
    {
        if (!auth()->user()->can('edit pipeline')) {
            abort(403, 'Unauthorized. You do not have permission to edit pipeline.');
        }

        // Check if user can access this record
        if (!PermissionHelper::canAccessRecord($pipeline, auth()->user())) {
            abort(403, 'Unauthorized. You do not have access to this pipeline.');
        }

        $data = $request->validate([
            'deal_name' => ['required', 'string', 'max:255'],
            'stage' => ['required', Rule::in(['prospect', 'negotiation', 'proposal', 'closed_won', 'closed_lost'])],
            'value' => ['required', 'numeric', 'min:0'],
            'owner_user_id' => ['nullable', 'integer'],
            'close_date' => ['nullable', 'date'],
            'probability' => ['nullable', 'integer', 'min:0', 'max:100'],
            'contact_id' => ['nullable', 'integer', 'exists:crm_contacts,id'],
            'company' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        // Ensure user_id is not changed (users can only update their own records)
        // user_id represents the owner/creator and should remain unchanged
        unset($data['user_id']);

        $pipeline->update($data);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Deal updated successfully',
                'deal' => $pipeline->fresh()
            ]);
        }
        
        return redirect()->route('crm.pipeline.index')->with('status', 'Deal updated');
    }

    public function destroy(Request $request, Pipeline $pipeline)
    {
        if (!auth()->user()->can('delete pipeline')) {
            abort(403, 'Unauthorized. You do not have permission to delete pipeline.');
        }

        // Check if user can access this record
        if (!PermissionHelper::canAccessRecord($pipeline, auth()->user())) {
            abort(403, 'Unauthorized. You do not have access to this pipeline.');
        }

        $pipeline->delete();
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Deal deleted successfully'
            ]);
        }
        
        return redirect()->route('crm.pipeline.index');
    }

    public function restore($id)
    {
        if (!auth()->user()->can('edit pipeline')) {
            abort(403, 'Unauthorized. You do not have permission to restore pipeline.');
        }

        $pipeline = Pipeline::withTrashed()->findOrFail($id);
        
        // Check if user can access this record
        if (!PermissionHelper::canAccessRecord($pipeline, auth()->user())) {
            abort(403, 'Unauthorized. You do not have access to this pipeline.');
        }

        $pipeline->restore();
        return redirect()->route('crm.pipeline.index')->with('status', 'Deal restored');
    }

    public function updateStage(Request $request, Pipeline $pipeline)
    {
        if (!auth()->user()->can('edit pipeline')) {
            abort(403, 'Unauthorized. You do not have permission to edit pipeline.');
        }

        // Check if user can access this record
        if (!PermissionHelper::canAccessRecord($pipeline, auth()->user())) {
            abort(403, 'Unauthorized. You do not have access to this pipeline.');
        }

        $request->validate([
            'stage' => ['required', Rule::in(['prospect', 'negotiation', 'proposal', 'closed_won', 'closed_lost'])],
        ]);

        $pipeline->update(['stage' => $request->input('stage')]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Deal stage updated successfully',
                'deal' => $pipeline->fresh()
            ]);
        }
        return back()->with('status', 'Deal stage updated.');
    }

    public function bulkDelete(Request $request)
    {
        if (!auth()->user()->can('delete pipeline')) {
            abort(403, 'Unauthorized. You do not have permission to delete pipeline.');
        }

        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:crm_pipelines,id'],
        ]);

        Pipeline::whereIn('id', $request->input('ids'))->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Selected deals deleted successfully'
            ]);
        }

        return back()->with('status', 'Selected deals deleted.');
    }

    public function export(Request $request)
    {
        if (!auth()->user()->can('export pipeline')) {
            abort(403, 'Unauthorized. You do not have permission to export pipeline.');
        }

        $pipelines = Pipeline::query();
        
        // Filter by role (Executive sees only assigned records)
        $pipelines = PermissionHelper::filterByUserId($pipelines, auth()->user());

        if ($request->filled('search')) {
            $search = $request->input('search');
            $pipelines->where(function ($q) use ($search) {
                $q->where('deal_name', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
            });
        }

        if ($request->filled('stage')) {
            $pipelines->where('stage', $request->input('stage'));
        }
        if ($request->filled('owner_user_id')) {
            $pipelines->where('owner_user_id', $request->input('owner_user_id'));
        }
        if ($request->filled('value_min')) {
            $pipelines->where('value', '>=', $request->input('value_min'));
        }
        if ($request->filled('value_max')) {
            $pipelines->where('value', '<=', $request->input('value_max'));
        }
        if ($request->filled('close_date_from')) {
            $pipelines->whereDate('close_date', '>=', $request->input('close_date_from'));
        }
        if ($request->filled('close_date_to')) {
            $pipelines->whereDate('close_date', '<=', $request->input('close_date_to'));
        }
        if ($request->filled('probability')) {
            $pipelines->where('probability', '>=', $request->input('probability'));
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="pipeline.csv"',
        ];

        $callback = function() use ($pipelines) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Deal Name', 'Stage', 'Value', 'Owner User ID', 'Close Date', 'Probability', 'Contact ID', 'Company', 'Notes', 'Created At']);

            foreach ($pipelines->cursor() as $pipeline) {
                fputcsv($file, [
                    $pipeline->id,
                    $pipeline->deal_name,
                    $pipeline->stage,
                    $pipeline->value,
                    $pipeline->owner_user_id,
                    $pipeline->close_date?->format('Y-m-d'),
                    $pipeline->probability,
                    $pipeline->contact_id,
                    $pipeline->company,
                    $pipeline->notes,
                    $pipeline->created_at?->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function datatable(Request $request)
    {
        if (!auth()->user()->can('view pipeline')) {
            abort(403, 'Unauthorized. You do not have permission to view pipeline.');
        }

        if (Schema::hasTable('users')) {
            $query = Pipeline::query()->with(['user', 'contact', 'ownerUser']);
        } else {
            $query = Pipeline::query()->with(['contact']);
        }

        // Filter by user_id: Admins see all, others see only their own records
        $query = PermissionHelper::filterByUserId($query, auth()->user());

        if ($search = trim((string) $request->input('search.value'))) {
            $query->where(function ($q) use ($search) {
                $q->where('deal_name', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
            });
        }

        if ($request->filled('stage')) {
            $query->where('stage', $request->input('stage'));
        }
        if ($request->filled('owner_user_id')) {
            $query->where('owner_user_id', $request->input('owner_user_id'));
        }
        if ($request->filled('value_min')) {
            $query->where('value', '>=', $request->input('value_min'));
        }
        if ($request->filled('value_max')) {
            $query->where('value', '<=', $request->input('value_max'));
        }
        if ($request->filled('close_date_from')) {
            $query->whereDate('close_date', '>=', $request->input('close_date_from'));
        }
        if ($request->filled('close_date_to')) {
            $query->whereDate('close_date', '<=', $request->input('close_date_to'));
        }
        if ($request->filled('probability')) {
            $query->where('probability', '>=', $request->input('probability'));
        }

        // Get total records - must apply user filtering for accurate count
        $totalRecordsQuery = Pipeline::query();
        $totalRecordsQuery = PermissionHelper::filterByUserId($totalRecordsQuery, auth()->user());
        $totalRecords = $totalRecordsQuery->count();
        $filteredRecords = $query->count();

        $orderColumn = $request->input('order.0.column', 9);
        $orderDir = $request->input('order.0.dir', 'desc');
        
        $columns = ['id', 'deal_name', 'stage', 'value', 'owner_user_id', 'close_date', 'probability', 'company', 'contact_id', 'created_at'];
        $sortColumn = $columns[$orderColumn] ?? 'created_at';
        
        $allowedSorts = ['deal_name', 'stage', 'value', 'owner_user_id', 'close_date', 'probability', 'company', 'contact_id', 'created_at'];
        if (!in_array($sortColumn, $allowedSorts, true)) {
            $sortColumn = 'created_at';
        }
        
        $orderDir = strtolower($orderDir) === 'asc' ? 'asc' : 'desc';

        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        
        $pipelines = $query->orderBy($sortColumn, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $user = auth()->user();
        $canDelete = $user->can('delete pipeline');
        $canEdit = $user->can('edit pipeline');

        $data = $pipelines->map(function ($pipeline) use ($canDelete, $canEdit, $user) {
            $stageColors = [
                'prospect' => 'text-gray-700',
                'negotiation' => 'text-blue-700',
                'proposal' => 'text-yellow-700',
                'closed_won' => 'text-green-700',
                'closed_lost' => 'text-red-700',
            ];
            $stageColor = $stageColors[$pipeline->stage] ?? 'text-gray-700';
            
            // Check if user can access this record for edit/delete
            $canAccess = PermissionHelper::canAccessRecord($pipeline, $user);
            
            $actionsHtml = '<div class="flex flex-col sm:flex-row gap-1 justify-center">';
            
            if ($canEdit && $canAccess) {
                $actionsHtml .= '<button type="button" class="inline-flex items-center gap-1 px-2 sm:px-3 py-1 rounded-lg border border-blue-400 text-blue-600 hover:bg-blue-50 shadow-sm text-xs edit-btn" data-id="'.$pipeline->id.'" data-deal="'.htmlspecialchars($pipeline->deal_name, ENT_QUOTES).'" data-stage="'.($pipeline->stage ?? 'prospect').'" data-value="'.($pipeline->value ?? 0).'" data-owner="'.($pipeline->owner_user_id ?? '').'" data-close-date="'.($pipeline->close_date?->format('Y-m-d') ?? '').'" data-probability="'.($pipeline->probability ?? 0).'" data-contact="'.($pipeline->contact_id ?? '').'" data-company="'.htmlspecialchars($pipeline->company ?? '', ENT_QUOTES).'" data-notes="'.htmlspecialchars($pipeline->notes ?? '', ENT_QUOTES).'"><svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-8.5 8.5a2 2 0 01-.878.515l-3.3.943a.5.5 0 01-.62-.62l.943-3.3a2 2 0 01.515-.878l8.5-8.5z"/></svg><span class="hidden sm:inline">Edit</span></button>';
            }
            
            if ($canDelete && $canAccess) {
                $actionsHtml .= '<button type="button" class="inline-flex items-center gap-1 px-2 sm:px-3 py-1 rounded-lg border border-red-400 text-red-600 hover:bg-red-50 shadow-sm text-xs delete-btn" data-id="'.$pipeline->id.'"><svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 8a1 1 0 011 1v6a1 1 0 102 0V9a1 1 0 112 0v6a1 1 0 102 0V9a1 1 0 011-1h1a1 1 0 100-2h-1V5a2 2 0 00-2-2H9a2 2 0 00-2 2v1H6a1 1 0 100 2h1zm3-3h2v1H9V5z" clip-rule="evenodd"/></svg><span class="hidden sm:inline">Del</span></button>';
            }
            
            $actionsHtml .= '</div>';
            
            return [
                'id' => $pipeline->id,
                'deal_name' => $pipeline->deal_name,
                'stage' => $pipeline->stage,
                'stage_html' => '<button type="button" class="inline-flex items-center gap-1 '.$stageColor.' font-semibold toggle-stage-btn" data-id="'.$pipeline->id.'" data-stage="'.$pipeline->stage.'">'.$pipeline->getStageLabel().'</button>',
                'value' => '$' . number_format($pipeline->value, 2),
                'owner_user_id' => $pipeline->ownerUser ? $pipeline->ownerUser->name : ($pipeline->owner_user_id ? 'User ' . $pipeline->owner_user_id : '-'),
                'owner_user_id_raw' => $pipeline->owner_user_id,
                'close_date' => $pipeline->close_date?->format('Y-m-d') ?? '-',
                'probability' => ($pipeline->probability ?? '-') . '%',
                'company' => $pipeline->company ?? '-',
                'contact' => $pipeline->contact?->name ?? '-',
                'contact_id' => $pipeline->contact_id,
                'notes' => $pipeline->notes ?? '',
                'created_at' => $pipeline->created_at?->format('Y-m-d') ?? '-',
                'actions_html' => $actionsHtml,
            ];
        });

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }

    public function kanbanData(Request $request)
    {
        if (!auth()->user()->can('view pipeline')) {
            abort(403, 'Unauthorized. You do not have permission to view pipeline.');
        }

        $query = Pipeline::query()->with(['user', 'contact']);
        
        // Filter by user_id: Admins see all, others see only their own records
        $query = PermissionHelper::filterByUserId($query, auth()->user());

        if ($request->filled('stage')) {
            $query->where('stage', $request->input('stage'));
        }
        if ($request->filled('owner_user_id')) {
            $query->where('owner_user_id', $request->input('owner_user_id'));
        }
        if ($request->filled('value_min')) {
            $query->where('value', '>=', $request->input('value_min'));
        }
        if ($request->filled('value_max')) {
            $query->where('value', '<=', $request->input('value_max'));
        }
        if ($request->filled('close_date_from')) {
            $query->whereDate('close_date', '>=', $request->input('close_date_from'));
        }
        if ($request->filled('close_date_to')) {
            $query->whereDate('close_date', '<=', $request->input('close_date_to'));
        }
        if ($request->filled('probability')) {
            $query->where('probability', '>=', $request->input('probability'));
        }

        $pipelines = $query->orderBy('created_at', 'desc')->get();

        $stages = ['prospect', 'negotiation', 'proposal', 'closed_won', 'closed_lost'];
        $kanbanData = [];

        $user = auth()->user();
        $canDelete = $user->can('delete pipeline');
        $canEdit = $user->can('edit pipeline');

        foreach ($stages as $stage) {
            $stagePipelines = $pipelines->where('stage', $stage)->values();
            $kanbanData[$stage] = $stagePipelines->map(function ($pipeline) use ($canDelete, $canEdit, $user) {
                // Check if user can access this record for edit/delete
                $canAccess = PermissionHelper::canAccessRecord($pipeline, $user);
                
                return [
                    'id' => $pipeline->id,
                    'deal_name' => $pipeline->deal_name,
                    'value' => '$' . number_format($pipeline->value, 2),
                    'company' => $pipeline->company ?? '-',
                    'contact' => $pipeline->contact?->name ?? '-',
                    'probability' => $pipeline->probability ?? 0,
                    'close_date' => $pipeline->close_date?->format('Y-m-d') ?? '-',
                    'owner' => $pipeline->owner_user_id ? ('User ' . $pipeline->owner_user_id) : '-',
                    'canEdit' => $canEdit && $canAccess,
                    'canDelete' => $canDelete && $canAccess,
                ];
            })->toArray();
        }

        return response()->json([
            'success' => true,
            'data' => $kanbanData,
        ]);
    }
}

