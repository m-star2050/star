<?php

namespace Packages\Crm\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Packages\Crm\Models\Lead;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);
        $sort = $request->input('sort', 'created_at');
        $direction = strtolower($request->input('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $q = Lead::query();

        if ($search = trim((string) $request->input('search'))) {
            $q->where(function ($qq) use ($search) {
                $qq->where('name','like',"%{$search}%")
                   ->orWhere('email','like',"%{$search}%")
                   ->orWhere('company','like',"%{$search}%");
            });
        }
        if ($request->filled('company')) {
            $q->where('company','like','%'.$request->input('company').'%');
        }
        if ($request->filled('source')) {
            $q->where('source','like','%'.$request->input('source').'%');
        }
        if ($request->filled('assigned_user_id')) {
            $q->where('assigned_user_id',$request->input('assigned_user_id'));
        }
        if ($request->filled('lead_score')) {
            $q->where('lead_score',$request->input('lead_score'));
        }
        if ($request->filled('status')) { // map status to stage/archived
            $status = $request->input('status');
            if ($status === 'archived') {
                $q->onlyTrashed();
            } elseif ($status === 'active') {
                // active means not deleted
            }
        }
        if ($request->filled('stage')) {
            $q->where('stage',$request->input('stage'));
        }
        if ($request->filled('created_from')) {
            $q->whereDate('created_at','>=',$request->input('created_from'));
        }
        if ($request->filled('created_to')) {
            $q->whereDate('created_at','<=',$request->input('created_to'));
        }

        $allowedSorts = ['name','company','email','stage','assigned_user_id','created_at'];
        if (! in_array($sort,$allowedSorts,true)) $sort='created_at';

        $leads = $q->orderBy($sort,$direction)->paginate($perPage)->withQueryString();

        return view('crm::leads.index', compact('leads','sort','direction','perPage'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'=>['required','string','max:255'],
            'email'=>['nullable','email','max:255'],
            'company'=>['nullable','string','max:255'],
            'source'=>['nullable','string','max:255'],
            'stage'=>['required', Rule::in(['new','contacted','qualified','won','lost'])],
            'assigned_user_id'=>['nullable','integer'],
            'lead_score'=>['nullable','integer'],
            'tags'=>['nullable'],
            'notes'=>['nullable','string'],
        ]);

        if (isset($data['tags'])) {
            if (is_array($data['tags']) && isset($data['tags'][0]) && is_string($data['tags'][0])) {
                $tagsInput = $data['tags'][0];
                $data['tags'] = array_filter(array_map('trim', explode(',', $tagsInput)));
            } elseif (is_string($data['tags'])) {
                $data['tags'] = array_filter(array_map('trim', explode(',', $data['tags'])));
            }
        }

        $lead = Lead::create($data);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Lead created successfully',
                'lead' => $lead
            ]);
        }
        
        return redirect()->route('crm.leads.index')->with('status','Lead created');
    }

    public function update(Request $request, Lead $lead)
    {
        $data = $request->validate([
            'name'=>['required','string','max:255'],
            'email'=>['nullable','email','max:255'],
            'company'=>['nullable','string','max:255'],
            'source'=>['nullable','string','max:255'],
            'stage'=>['required', Rule::in(['new','contacted','qualified','won','lost'])],
            'assigned_user_id'=>['nullable','integer'],
            'lead_score'=>['nullable','integer'],
            'tags'=>['nullable'],
            'notes'=>['nullable','string'],
        ]);

        if (isset($data['tags'])) {
            if (is_array($data['tags']) && isset($data['tags'][0]) && is_string($data['tags'][0])) {
                $tagsInput = $data['tags'][0];
                $data['tags'] = array_filter(array_map('trim', explode(',', $tagsInput)));
            } elseif (is_string($data['tags'])) {
                $data['tags'] = array_filter(array_map('trim', explode(',', $data['tags'])));
            }
        }

        $lead->update($data);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Lead updated successfully',
                'lead' => $lead->fresh()
            ]);
        }
        
        return redirect()->route('crm.leads.index')->with('status','Lead updated');
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
        
        return redirect()->route('crm.leads.index')->with('status','Lead deleted');
    }

    public function restore($id)
    {
        $lead = Lead::withTrashed()->findOrFail($id);
        $lead->restore();
        return redirect()->route('crm.leads.index')->with('status','Lead restored');
    }

    public function inlineStage(Request $request, Lead $lead)
    {
        $request->validate(['stage'=>['required', Rule::in(['new','contacted','qualified','won','lost'])]]);
        $lead->stage = $request->input('stage');
        $lead->save();
        return response()->json(['success'=>true]);
    }

    public function bulkDelete(Request $request)
    {
        $ids = (array) $request->input('ids', []);
        
        if (empty($ids)) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No leads selected for deletion'
                ], 400);
            }
            return redirect()->route('crm.leads.index')->with('error', 'No leads selected for deletion');
        }
        
        Lead::whereIn('id', $ids)->delete();
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Selected leads deleted successfully'
            ]);
        }
        
        return redirect()->route('crm.leads.index')->with('status','Selected leads deleted');
    }

    public function export(Request $request)
    {
        $q = Lead::query();
        if ($search = trim((string) $request->input('search'))) {
            $q->where(function ($qq) use ($search) {
                $qq->where('name','like',"%{$search}%")
                   ->orWhere('email','like',"%{$search}%")
                   ->orWhere('company','like',"%{$search}%");
            });
        }
        if ($request->filled('company')) {
            $q->where('company','like','%'.$request->input('company').'%');
        }
        if ($request->filled('source')) {
            $q->where('source','like','%'.$request->input('source').'%');
        }
        if ($request->filled('stage')) {
            $q->where('stage',$request->input('stage'));
        }
        if ($request->filled('assigned_user_id')) {
            $q->where('assigned_user_id',$request->input('assigned_user_id'));
        }
        if ($request->filled('lead_score')) {
            $q->where('lead_score',$request->input('lead_score'));
        }
        $rows = $q->orderBy('created_at','desc')->get(['id','name','email','company','source','stage','lead_score','assigned_user_id','created_at']);
        $filename = 'leads_export_'.now()->format('Ymd_His').'.csv';
        $headers = [
            'Content-Type'=>'text/csv',
            'Content-Disposition'=>'attachment; filename="'.$filename.'"',
        ];
        $callback = function() use ($rows) {
            $out = fopen('php://output','w');
            fputcsv($out,['ID','Name','Email','Company','Source','Stage','Lead Score','Assigned User','Created At']);
            foreach($rows as $r){
                fputcsv($out,[$r->id,$r->name,$r->email,$r->company,$r->source,$r->stage,$r->lead_score,$r->assigned_user_id,$r->created_at]);
            }
            fclose($out);
        };
        return response()->stream($callback,200,$headers);
    }

    public function convertToContact(Lead $lead)
    {
        $contactData = [
            'name' => $lead->name,
            'email' => $lead->email,
            'phone' => null,
            'company' => $lead->company,
            'tags' => $lead->tags,
            'notes' => $lead->notes . "\n\nConverted from Lead (ID: {$lead->id}) on " . now()->format('Y-m-d H:i:s'),
        ];

        $contact = \Packages\Crm\Models\Contact::create($contactData);

        $lead->update(['stage' => 'won']);
        $lead->delete();

        return redirect()->route('crm.contacts.index')
            ->with('status', "Lead '{$lead->name}' successfully converted to Contact");
    }

    public function convertToDeal(Lead $lead)
    {
        $dealData = [
            'deal_name' => $lead->company ? "{$lead->name} - {$lead->company}" : $lead->name,
            'stage' => 'prospect',
            'value' => $lead->lead_score ? $lead->lead_score * 100 : 0,
            'owner_user_id' => $lead->assigned_user_id,
            'close_date' => null,
            'probability' => $lead->lead_score ?? 50,
            'contact_id' => null,
            'company' => $lead->company,
            'notes' => $lead->notes . "\n\nConverted from Lead (ID: {$lead->id}) on " . now()->format('Y-m-d H:i:s'),
        ];

        $pipeline = \Packages\Crm\Models\Pipeline::create($dealData);
        $lead->update(['stage' => 'won']);
        $lead->delete();

        return redirect()->route('crm.pipeline.index')
            ->with('status', "Lead '{$lead->name}' successfully converted to Deal");
    }

    public function datatable(Request $request)
    {
        $query = Lead::query();

        if ($search = trim((string) $request->input('search.value'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%")
                  ->orWhere('source', 'like', "%{$search}%");
            });
        }

        if ($request->filled('company')) {
            $query->where('company', 'like', '%'.$request->input('company').'%');
        }
        if ($request->filled('source')) {
            $query->where('source', 'like', '%'.$request->input('source').'%');
        }
        if ($request->filled('assigned_user_id')) {
            $query->where('assigned_user_id', $request->input('assigned_user_id'));
        }
        if ($request->filled('stage')) {
            $query->where('stage', $request->input('stage'));
        }
        if ($request->filled('lead_score')) {
            $query->where('lead_score', $request->input('lead_score'));
        }
        if ($request->filled('created_from')) {
            $query->whereDate('created_at', '>=', $request->input('created_from'));
        }
        if ($request->filled('created_to')) {
            $query->whereDate('created_at', '<=', $request->input('created_to'));
        }

        $totalRecords = Lead::count();
        $filteredRecords = $query->count();

        $orderColumn = $request->input('order.0.column', 8);
        $orderDir = $request->input('order.0.dir', 'desc');
        
        $columns = ['id', 'name', 'email', 'company', 'source', 'stage', 'lead_score', 'assigned_user_id', 'created_at'];
        $sortColumn = $columns[$orderColumn] ?? 'created_at';
        
        $allowedSorts = ['name','email','company','source','stage','lead_score','assigned_user_id','created_at'];
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

        $data = $leads->map(function ($lead) {
            $isArchived = !is_null($lead->deleted_at ?? null);
            $tagsText = implode(',', (array) $lead->tags);
            $assigned = $lead->assigned_user_id ? ('User '.$lead->assigned_user_id) : '-';
            
            $stageColors = [
                'new' => 'text-blue-600',
                'contacted' => 'text-amber-600',
                'qualified' => 'text-emerald-600',
                'won' => 'text-green-600',
                'lost' => 'text-red-600',
            ];
            $stageColor = $stageColors[$lead->stage] ?? 'text-gray-600';
            
            return [
                'id' => $lead->id,
                'name' => $lead->name,
                'email' => $lead->email ?? '-',
                'company' => $lead->company ?? '-',
                'source' => $lead->source ?? '-',
                'stage' => ucfirst($lead->stage),
                'stage_html' => '<span class="inline-flex items-center gap-1 '.$stageColor.' font-semibold">'.ucfirst($lead->stage).'</span>',
                'lead_score' => $lead->lead_score ?? '-',
                'assigned' => $assigned,
                'created_at' => $lead->created_at?->format('Y-m-d') ?? '-',
                'status_html' => $isArchived 
                    ? '<span class="inline-flex items-center gap-1 text-red-600"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.54-10.46a.75.75 0 00-1.06-1.06L10 8.94 7.52 6.48a.75.75 0 00-1.06 1.06L8.94 10l-2.48 2.48a.75.75 0 101.06 1.06L10 11.06l2.48 2.48a.75.75 0 101.06-1.06L11.06 10l2.48-2.46z" clip-rule="evenodd"/></svg>Archived</span>'
                    : '<span class="inline-flex items-center gap-1 text-blue-600"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-7.364 7.364a1 1 0 01-1.414 0L3.293 10.435a1 1 0 011.414-1.414l3.221 3.221 6.657-6.657a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>Active</span>',
                'actions_html' => '<div class="flex flex-col sm:flex-row gap-1 justify-center">
                    <button type="button" class="inline-flex items-center gap-1 px-2 sm:px-3 py-1 rounded-lg border border-blue-400 text-blue-600 hover:bg-blue-50 shadow-sm text-xs edit-btn" data-id="'.$lead->id.'" data-name="'.htmlspecialchars($lead->name, ENT_QUOTES).'" data-company="'.htmlspecialchars($lead->company ?? '', ENT_QUOTES).'" data-email="'.htmlspecialchars($lead->email ?? '', ENT_QUOTES).'" data-source="'.htmlspecialchars($lead->source ?? '', ENT_QUOTES).'" data-stage="'.($lead->stage ?? 'new').'" data-assigned="'.($lead->assigned_user_id ?? '').'" data-score="'.($lead->lead_score ?? '').'" data-tags="'.htmlspecialchars($tagsText, ENT_QUOTES).'" data-notes="'.htmlspecialchars($lead->notes ?? '', ENT_QUOTES).'"><svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-8.5 8.5a2 2 0 01-.878.515l-3.3.943a.5.5 0 01-.62-.62l.943-3.3a2 2 0 01.515-.878l8.5-8.5z"/></svg><span class="hidden sm:inline">Edit</span></button>
                    <button type="button" class="inline-flex items-center gap-1 px-2 sm:px-3 py-1 rounded-lg border border-red-400 text-red-600 hover:bg-red-50 shadow-sm text-xs delete-btn" data-id="'.$lead->id.'"><svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 8a1 1 0 011 1v6a1 1 0 102 0V9a1 1 0 112 0v6a1 1 0 102 0V9a1 1 0 011-1h1a1 1 0 100-2h-1V5a2 2 0 00-2-2H9a2 2 0 00-2 2v1H6a1 1 0 100 2h1zm3-3h2v1H9V5z" clip-rule="evenodd"/></svg><span class="hidden sm:inline">Del</span></button>
                    '.(in_array($lead->stage, ['won']) ? '<button type="button" class="inline-flex items-center gap-1 px-2 sm:px-3 py-1 rounded-lg border border-green-400 text-green-600 hover:bg-green-50 shadow-sm text-xs convert-btn" data-id="'.$lead->id.'" data-name="'.htmlspecialchars($lead->name, ENT_QUOTES).'"><svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span class="hidden sm:inline">Convert</span></button>' : '').'
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
