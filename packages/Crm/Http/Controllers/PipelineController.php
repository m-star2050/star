<?php

namespace Packages\Crm\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Packages\Crm\Models\Pipeline;
use Packages\Crm\Models\Contact;

class PipelineController extends Controller
{
    /**
     * Display list view with filters
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');

        $query = Pipeline::query()->with(['contact']);

        // Search by deal name or company
        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('deal_name', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
            });
        }

        // Filters
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
        if ($request->filled('probability_min')) {
            $query->where('probability', '>=', $request->input('probability_min'));
        }
        if ($request->filled('probability_max')) {
            $query->where('probability', '<=', $request->input('probability_max'));
        }
        if ($request->filled('close_date_from')) {
            $query->whereDate('close_date', '>=', $request->input('close_date_from'));
        }
        if ($request->filled('close_date_to')) {
            $query->whereDate('close_date', '<=', $request->input('close_date_to'));
        }

        // Sorting
        $allowedSorts = ['deal_name', 'stage', 'value', 'owner_user_id', 'close_date', 'probability', 'created_at'];
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }
        $direction = strtolower($direction) === 'asc' ? 'asc' : 'desc';

        $pipelines = $query->orderBy($sort, $direction)->paginate($perPage)->withQueryString();

        return view('crm::pipeline.index', [
            'pipelines' => $pipelines,
            'sort' => $sort,
            'direction' => $direction,
            'perPage' => $perPage,
        ]);
    }

    /**
     * Display Kanban board view
     */
    public function kanban(Request $request)
    {
        // Get all deals grouped by stage
        $stages = ['prospect', 'negotiation', 'proposal', 'closed_won', 'closed_lost'];
        
        $query = Pipeline::query()->with(['contact']);

        // Apply filters if any
        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('deal_name', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
            });
        }

        if ($request->filled('owner_user_id')) {
            $query->where('owner_user_id', $request->input('owner_user_id'));
        }

        $deals = $query->orderBy('created_at', 'desc')->get();

        // Group deals by stage
        $dealsByStage = [];
        foreach ($stages as $stage) {
            $dealsByStage[$stage] = $deals->where('stage', $stage)->values();
        }

        return view('crm::pipeline.kanban', [
            'stages' => $stages,
            'dealsByStage' => $dealsByStage,
        ]);
    }

    /**
     * Store a new deal
     */
    public function store(Request $request)
    {
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

        $pipeline = Pipeline::create($data);
        
        $returnView = $request->input('view_mode', 'list');
        $route = $returnView === 'kanban' ? 'crm.pipeline.kanban' : 'crm.pipeline.index';
        
        return redirect()->route($route)->with('status', 'Deal created successfully');
    }

    /**
     * Update an existing deal
     */
    public function update(Request $request, Pipeline $pipeline)
    {
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

        $pipeline->update($data);
        
        $returnView = $request->input('view_mode', 'list');
        $route = $returnView === 'kanban' ? 'crm.pipeline.kanban' : 'crm.pipeline.index';
        
        return redirect()->route($route)->with('status', 'Deal updated successfully');
    }

    /**
     * Delete a deal
     */
    public function destroy(Pipeline $pipeline)
    {
        $pipeline->delete();
        return redirect()->route('crm.pipeline.index');
    }

    /**
     * Restore deleted deal
     */
    public function restore($id)
    {
        $pipeline = Pipeline::withTrashed()->findOrFail($id);
        $pipeline->restore();
        return redirect()->route('crm.pipeline.index')->with('status', 'Deal restored successfully');
    }

    /**
     * Update deal stage via AJAX (for Kanban drag-drop)
     */
    public function updateStage(Request $request, Pipeline $pipeline)
    {
        $stage = $request->input('stage');
        
        if (! in_array($stage, ['prospect', 'negotiation', 'proposal', 'closed_won', 'closed_lost'], true)) {
            return response()->json(['success' => false, 'message' => 'Invalid stage'], 422);
        }

        $pipeline->stage = $stage;
        $pipeline->save();

        return response()->json(['success' => true, 'stage' => $stage]);
    }

    /**
     * Bulk delete deals
     */
    public function bulkDelete(Request $request)
    {
        $ids = (array) $request->input('ids', []);
        Pipeline::whereIn('id', $ids)->delete();
        return redirect()->route('crm.pipeline.index');
    }

    /**
     * Export deals to CSV
     */
    public function export(Request $request)
    {
        $query = Pipeline::query()->with(['contact']);

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('deal_name', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
            });
        }

        $rows = $query->orderBy('created_at', 'desc')->get();

        $filename = 'pipeline_export_'.now()->format('Ymd_His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Deal Name', 'Stage', 'Value', 'Owner', 'Close Date', 'Probability', 'Company', 'Contact', 'Created At']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->id,
                    $r->deal_name,
                    $r->stage,
                    $r->value,
                    $r->owner_user_id,
                    $r->close_date?->format('Y-m-d'),
                    $r->probability,
                    $r->company,
                    $r->contact?->name,
                    $r->created_at?->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}

