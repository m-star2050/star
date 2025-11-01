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
            'tags'=>['nullable','array'],
            'notes'=>['nullable','string'],
        ]);
        Lead::create($data);
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
            'tags'=>['nullable','array'],
            'notes'=>['nullable','string'],
        ]);
        $lead->update($data);
        return redirect()->route('crm.leads.index')->with('status','Lead updated');
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();
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
        if ($ids) Lead::whereIn('id',$ids)->delete();
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
        // Create Contact from Lead data
        $contactData = [
            'name' => $lead->name,
            'email' => $lead->email,
            'phone' => null,
            'company' => $lead->company,
            'tags' => $lead->tags,
            'notes' => $lead->notes . "\n\nConverted from Lead (ID: {$lead->id}) on " . now()->format('Y-m-d H:i:s'),
        ];

        $contact = \Packages\Crm\Models\Contact::create($contactData);

        // Update lead stage to 'won' and soft delete
        $lead->update(['stage' => 'won']);
        $lead->delete();

        return redirect()->route('crm.contacts.index')
            ->with('status', "Lead '{$lead->name}' successfully converted to Contact");
    }

    public function convertToDeal(Lead $lead)
    {
        // Create Deal/Pipeline from Lead data
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

        // Update lead stage to 'won' and soft delete
        $lead->update(['stage' => 'won']);
        $lead->delete();

        return redirect()->route('crm.pipeline.index')
            ->with('status', "Lead '{$lead->name}' successfully converted to Deal");
    }
}
