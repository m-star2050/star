<?php

namespace Packages\Crm\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Packages\Crm\Models\Contact;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');

        $query = Contact::query();

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('assigned_user_id')) {
            $query->where('assigned_user_id', $request->input('assigned_user_id'));
        }
        if ($request->filled('company')) {
            $query->where('company', 'like', '%'.$request->input('company').'%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('created_from')) {
            $query->whereDate('created_at', '>=', $request->input('created_from'));
        }
        if ($request->filled('created_to')) {
            $query->whereDate('created_at', '<=', $request->input('created_to'));
        }
        if ($request->filled('tag')) {
            $query->whereJsonContains('tags', $request->input('tag'));
        }

        $allowedSorts = ['name','company','email','phone','assigned_user_id','created_at'];
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }
        $direction = strtolower($direction) === 'asc' ? 'asc' : 'desc';

        $contacts = $query->orderBy($sort, $direction)->paginate($perPage)->withQueryString();

        return view('crm::contacts.index', [
            'contacts' => $contacts,
            'sort' => $sort,
            'direction' => $direction,
            'perPage' => $perPage,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'company' => ['nullable','string','max:255'],
            'email' => ['nullable','email','max:255'],
            'phone' => ['nullable','string','max:50'],
            'assigned_user_id' => ['nullable','integer'],
            'status' => ['required', Rule::in(['active','archived'])],
            'tags' => ['nullable','array'],
            'tags.*' => ['string','max:50'],
            'notes' => ['nullable','string'],
        ]);

        $contact = Contact::create($data);
        return redirect()->route('crm.contacts.index')->with('status', 'Contact created');
    }

    public function update(Request $request, Contact $contact)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'company' => ['nullable','string','max:255'],
            'email' => ['nullable','email','max:255'],
            'phone' => ['nullable','string','max:50'],
            'assigned_user_id' => ['nullable','integer'],
            'status' => ['required', Rule::in(['active','archived'])],
            'tags' => ['nullable','array'],
            'tags.*' => ['string','max:50'],
            'notes' => ['nullable','string'],
        ]);

        $contact->update($data);
        return redirect()->route('crm.contacts.index')->with('status', 'Contact updated');
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();
        return redirect()->route('crm.contacts.index')->with('status', 'Contact archived');
    }

    public function restore($id)
    {
        $contact = Contact::withTrashed()->findOrFail($id);
        $contact->restore();
        return redirect()->route('crm.contacts.index')->with('status', 'Contact restored');
    }

    public function inline(Request $request, Contact $contact)
    {
        $field = $request->input('field');
        $value = $request->input('value');

        if (! in_array($field, ['assigned_user_id','tags'], true)) {
            abort(422, 'Invalid field');
        }

        if ($field === 'tags') {
            $contact->tags = is_array($value) ? $value : array_filter(array_map('trim', explode(',', (string) $value)));
        } else {
            $contact->assigned_user_id = $value ?: null;
        }
        $contact->save();

        return response()->json(['success' => true]);
    }

    public function bulkDelete(Request $request)
    {
        $ids = (array) $request->input('ids', []);
        Contact::whereIn('id', $ids)->delete();
        return redirect()->route('crm.contacts.index')->with('status', 'Selected contacts deleted');
    }

    public function export(Request $request)
    {
        $query = Contact::query();

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $rows = $query->orderBy('created_at','desc')->get([
            'id','name','company','email','phone','assigned_user_id','status','created_at'
        ]);

        $filename = 'contacts_export_'.now()->format('Ymd_His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID','Name','Company','Email','Phone','Assigned User','Status','Created At']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->id,
                    $r->name,
                    $r->company,
                    $r->email,
                    $r->phone,
                    $r->assigned_user_id,
                    $r->status,
                    $r->created_at,
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}


