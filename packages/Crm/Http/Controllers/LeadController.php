<?php

namespace Packages\Crm\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Packages\Crm\Models\Lead;
use Packages\Crm\Models\Contact;
use Packages\Crm\Models\Pipeline;
use App\Models\User;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $users = User::select('id', 'name', 'email')->orderBy('name')->get();
        return view('crm::leads.index', ['users' => $users]);
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['nullable', 'email', 'max:255'],
                'company' => ['nullable', 'string', 'max:255'],
                'source' => ['nullable', 'string', 'max:255'],
                'stage' => ['required', Rule::in(['new', 'contacted', 'qualified', 'won', 'lost'])],
                'assigned_user_id' => ['nullable', 'integer'],
                'lead_score' => ['nullable', 'integer', 'min:0', 'max:100'],
                'tags' => ['nullable'],
                'notes' => ['nullable', 'string'],
            ]);

            $hasTagsColumn = Schema::hasColumn('crm_leads', 'tags');
            
            if ($hasTagsColumn) {
                if (isset($data['tags'])) {
                    if (is_array($data['tags']) && !empty($data['tags']) && isset($data['tags'][0]) && is_string($data['tags'][0])) {
                        $tagsInput = $data['tags'][0];
                        $data['tags'] = array_filter(array_map('trim', explode(',', $tagsInput)));
                    } elseif (is_string($data['tags'])) {
                        $data['tags'] = array_filter(array_map('trim', explode(',', $data['tags'])));
                    } else {
                        $data['tags'] = null;
                    }
                } else {
                    $data['tags'] = null;
                }
                
                if (empty($data['tags'])) {
                    $data['tags'] = null;
                }
            } else {
                unset($data['tags']);
            }

            $lead = Lead::create($data);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Lead created successfully',
                    'lead' => $lead
                ]);
            }
            
            return redirect()->route('crm.leads.index')->with('status', 'Lead created');
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
            Log::error('Lead creation error: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lead creation failed: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('crm.leads.index')->with('error', 'Lead creation failed: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Lead $lead)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:255'],
            'stage' => ['required', Rule::in(['new', 'contacted', 'qualified', 'won', 'lost'])],
            'assigned_user_id' => ['nullable', 'integer'],
            'lead_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'tags' => ['nullable'],
            'notes' => ['nullable', 'string'],
        ]);

        $hasTagsColumn = Schema::hasColumn('crm_leads', 'tags');
        
        if ($hasTagsColumn) {
            if (isset($data['tags'])) {
                if (is_array($data['tags']) && !empty($data['tags']) && isset($data['tags'][0]) && is_string($data['tags'][0])) {
                    $tagsInput = $data['tags'][0];
                    $data['tags'] = array_filter(array_map('trim', explode(',', $tagsInput)));
                } elseif (is_string($data['tags'])) {
                    $data['tags'] = array_filter(array_map('trim', explode(',', $data['tags'])));
                } else {
                    $data['tags'] = null;
                }
            } else {
                $data['tags'] = null;
            }
            
            if (empty($data['tags'])) {
                $data['tags'] = null;
            }
        } else {
            unset($data['tags']);
        }

        $lead->update($data);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Lead updated successfully',
                'lead' => $lead->fresh()
            ]);
        }
        
        return redirect()->route('crm.leads.index')->with('status', 'Lead updated');
    }

    public function destroy(Request $request, Lead $lead)
    {
        $lead->delete();
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Lead deleted successfully'
            ]);
        }
        
        return redirect()->route('crm.leads.index');
    }

    public function restore($id)
    {
        $lead = Lead::withTrashed()->findOrFail($id);
        $lead->restore();
        return redirect()->route('crm.leads.index')->with('status', 'Lead restored');
    }

    public function inlineStage(Request $request, Lead $lead)
    {
        $request->validate([
            'stage' => ['required', Rule::in(['new', 'contacted', 'qualified', 'won', 'lost'])],
        ]);

        $lead->update(['stage' => $request->input('stage')]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Lead stage updated successfully',
                'lead' => $lead->fresh()
            ]);
        }
        return back()->with('status', 'Lead stage updated.');
    }

    public function convertToContact(Request $request, Lead $lead)
    {
        $hasTagsColumn = Schema::hasColumn('crm_leads', 'tags');
        
        $contactData = [
            'name' => $lead->name,
            'email' => $lead->email,
            'company' => $lead->company,
            'assigned_user_id' => $lead->assigned_user_id,
            'notes' => $lead->notes,
            'status' => 'active',
        ];
        
        if ($hasTagsColumn && isset($lead->tags)) {
            $contactData['tags'] = $lead->tags;
        }
        
        $contact = Contact::create($contactData);

        $lead->update(['stage' => 'won']);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Lead converted to contact successfully',
                'contact' => $contact
            ]);
        }
        return back()->with('status', 'Lead converted to contact.');
    }

    public function convertToDeal(Request $request, Lead $lead)
    {
        $pipeline = Pipeline::create([
            'deal_name' => $lead->name . ' - Deal',
            'stage' => 'prospect',
            'value' => 0,
            'owner_user_id' => $lead->assigned_user_id,
            'contact_id' => null,
            'company' => $lead->company,
            'notes' => $lead->notes,
        ]);

        $lead->update(['stage' => 'won']);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Lead converted to deal successfully',
                'pipeline' => $pipeline
            ]);
        }
        return back()->with('status', 'Lead converted to deal.');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:crm_leads,id'],
        ]);

        Lead::whereIn('id', $request->input('ids'))->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Selected leads deleted successfully'
            ]);
        }

        return back()->with('status', 'Selected leads deleted.');
    }

    public function export(Request $request)
    {
        $leads = Lead::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $leads->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
            });
        }

        if ($request->filled('stage')) {
            $leads->where('stage', $request->input('stage'));
        }
        if ($request->filled('source')) {
            $leads->where('source', $request->input('source'));
        }
        if ($request->filled('assigned_user_id')) {
            $leads->where('assigned_user_id', $request->input('assigned_user_id'));
        }
        if ($request->filled('date_from')) {
            $leads->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $leads->whereDate('created_at', '<=', $request->input('date_to'));
        }
        if ($request->filled('lead_score')) {
            $leads->where('lead_score', '>=', $request->input('lead_score'));
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="leads.csv"',
        ];

        $hasTagsColumn = Schema::hasColumn('crm_leads', 'tags');
        
        $callback = function() use ($leads, $hasTagsColumn) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Email', 'Company', 'Source', 'Stage', 'Assigned User ID', 'Lead Score', 'Tags', 'Notes', 'Created At']);

            foreach ($leads->cursor() as $lead) {
                $tagsValue = '';
                if ($hasTagsColumn) {
                    $tagsValue = is_array($lead->tags) ? implode(',', $lead->tags) : '';
                }
                
                fputcsv($file, [
                    $lead->id,
                    $lead->name,
                    $lead->email,
                    $lead->company,
                    $lead->source,
                    $lead->stage,
                    $lead->assigned_user_id,
                    $lead->lead_score,
                    $tagsValue,
                    $lead->notes,
                    $lead->created_at?->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function datatable(Request $request)
    {
        $query = Lead::with('assignedUser');

        if ($search = trim((string) $request->input('search.value'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
            });
        }

        if ($request->filled('stage')) {
            $query->where('stage', $request->input('stage'));
        }
        if ($request->filled('source')) {
            $query->where('source', $request->input('source'));
        }
        if ($request->filled('assigned_user_id')) {
            $query->where('assigned_user_id', $request->input('assigned_user_id'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }
        if ($request->filled('lead_score')) {
            $query->where('lead_score', '>=', $request->input('lead_score'));
        }

        $totalRecords = Lead::count();
        $filteredRecords = $query->count();

        $orderColumn = $request->input('order.0.column', 6);
        $orderDir = $request->input('order.0.dir', 'desc');
        
        $columns = ['id', 'name', 'email', 'source', 'stage', 'assigned_user_id', 'created_at'];
        $sortColumn = $columns[$orderColumn] ?? 'created_at';
        
        $allowedSorts = ['name', 'email', 'source', 'stage', 'assigned_user_id', 'created_at'];
        if (!in_array($sortColumn, $allowedSorts, true)) {
            $sortColumn = 'created_at';
        }
        
        $orderDir = strtolower($orderDir) === 'asc' ? 'asc' : 'desc';

        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        
        $leads = $query->orderBy($sortColumn, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $hasTagsColumn = Schema::hasColumn('crm_leads', 'tags');
        
        $data = $leads->map(function ($lead) use ($hasTagsColumn) {
            $stageColors = [
                'new' => 'text-gray-600',
                'contacted' => 'text-blue-600',
                'qualified' => 'text-yellow-600',
                'won' => 'text-green-600',
                'lost' => 'text-red-600',
            ];
            $stageColor = $stageColors[$lead->stage] ?? 'text-gray-600';
            
            $tagsValue = '';
            if ($hasTagsColumn) {
                $tagsValue = is_array($lead->tags) ? implode(',', $lead->tags) : '';
            }
            
            return [
                'id' => $lead->id,
                'name' => $lead->name,
                'email' => $lead->email ?? '-',
                'source' => $lead->source ?? '-',
                'stage' => ucfirst($lead->stage),
                'stage_html' => '<button type="button" class="inline-flex items-center gap-1 '.$stageColor.' font-semibold toggle-stage-btn" data-id="'.$lead->id.'" data-stage="'.$lead->stage.'">'.ucfirst($lead->stage).'</button>',
                'assigned' => $lead->assignedUser ? $lead->assignedUser->name : ($lead->assigned_user_id ? 'User ' . $lead->assigned_user_id : '-'),
                'created_at' => $lead->created_at?->format('Y-m-d') ?? '-',
                'actions_html' => '<div class="flex flex-col sm:flex-row gap-1 justify-center">
                    <button type="button" class="inline-flex items-center gap-1 px-2 sm:px-3 py-1 rounded-lg border border-blue-400 text-blue-600 hover:bg-blue-50 shadow-sm text-xs edit-btn" data-id="'.$lead->id.'" data-name="'.htmlspecialchars($lead->name, ENT_QUOTES).'" data-email="'.htmlspecialchars($lead->email ?? '', ENT_QUOTES).'" data-company="'.htmlspecialchars($lead->company ?? '', ENT_QUOTES).'" data-source="'.htmlspecialchars($lead->source ?? '', ENT_QUOTES).'" data-stage="'.($lead->stage ?? 'new').'" data-assigned="'.($lead->assigned_user_id ?? '').'" data-lead-score="'.($lead->lead_score ?? '').'" data-tags="'.htmlspecialchars($tagsValue, ENT_QUOTES).'" data-notes="'.htmlspecialchars($lead->notes ?? '', ENT_QUOTES).'"><svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-8.5 8.5a2 2 0 01-.878.515l-3.3.943a.5.5 0 01-.62-.62l.943-3.3a2 2 0 01.515-.878l8.5-8.5z"/></svg><span class="hidden sm:inline">Edit</span></button>
                    <button type="button" class="inline-flex items-center gap-1 px-2 sm:px-3 py-1 rounded-lg border border-red-400 text-red-600 hover:bg-red-50 shadow-sm text-xs delete-btn" data-id="'.$lead->id.'"><svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 8a1 1 0 011 1v6a1 1 0 102 0V9a1 1 0 112 0v6a1 1 0 102 0V9a1 1 0 011-1h1a1 1 0 100-2h-1V5a2 2 0 00-2-2H9a2 2 0 00-2 2v1H6a1 1 0 100 2h1zm3-3h2v1H9V5z" clip-rule="evenodd"/></svg><span class="hidden sm:inline">Del</span></button>
                </div>',
            ];
        });

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }
}

