<?php

namespace Packages\Crm\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Packages\Crm\Models\Contact;
use Packages\Crm\Helpers\PermissionHelper;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        // Check permission
        if (!auth()->user()->can('view contacts')) {
            abort(403, 'Unauthorized. You do not have permission to view contacts.');
        }

        $users = collect([]);
        if (Schema::hasTable('users')) {
            try {
                $users = User::select('id', 'name', 'email')->orderBy('name')->get();
            } catch (\Exception $e) {
                $users = collect([]);
            }
        }
        $perPage = (int) $request->input('per_page', 10);
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');

        $query = Contact::query();
        
        // Filter by role (Executive sees only assigned records)
        $query = PermissionHelper::filterByRole($query, auth()->user(), 'assigned_user_id');

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

        $users = collect([]);
        if (Schema::hasTable('users')) {
            try {
                $users = User::select('id', 'name', 'email')->orderBy('name')->get();
            } catch (\Exception $e) {
                $users = collect([]);
            }
        }

        return view('crm::contacts.index', [
            'contacts' => $contacts,
            'sort' => $sort,
            'direction' => $direction,
            'perPage' => $perPage,
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('create contacts')) {
            abort(403, 'Unauthorized. You do not have permission to create contacts.');
        }

        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'company' => ['nullable','string','max:255'],
            'email' => ['nullable','email','max:255'],
            'phone' => ['nullable','string','max:50'],
            'assigned_user_id' => ['nullable','integer'],
            'status' => ['required', Rule::in(['active','archived'])],
            'tags' => ['nullable'],
            'notes' => ['nullable','string'],
        ]);

        // Handle tags input (can be array or comma-separated string)
        if (isset($data['tags'])) {
            if (is_array($data['tags']) && isset($data['tags'][0]) && is_string($data['tags'][0])) {
                // Handle comma-separated string in array
                $tagsInput = $data['tags'][0];
                $data['tags'] = array_filter(array_map('trim', explode(',', $tagsInput)));
            } elseif (is_string($data['tags'])) {
                // Handle direct string input
                $data['tags'] = array_filter(array_map('trim', explode(',', $data['tags'])));
            }
        }

        $contact = Contact::create($data);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Contact created successfully',
                'contact' => $contact
            ]);
        }
        
        return redirect()->route('crm.contacts.index')->with('status', 'Contact created');
    }

    public function update(Request $request, Contact $contact)
    {
        if (!auth()->user()->can('edit contacts')) {
            abort(403, 'Unauthorized. You do not have permission to edit contacts.');
        }

        // Check if user can access this record
        if (!PermissionHelper::canAccessRecord($contact, auth()->user())) {
            abort(403, 'Unauthorized. You do not have access to this contact.');
        }

        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'company' => ['nullable','string','max:255'],
            'email' => ['nullable','email','max:255'],
            'phone' => ['nullable','string','max:50'],
            'assigned_user_id' => ['nullable','integer'],
            'status' => ['required', Rule::in(['active','archived'])],
            'tags' => ['nullable'],
            'notes' => ['nullable','string'],
        ]);

        // Handle tags input (can be array or comma-separated string)
        if (isset($data['tags'])) {
            if (is_array($data['tags']) && isset($data['tags'][0]) && is_string($data['tags'][0])) {
                // Handle comma-separated string in array
                $tagsInput = $data['tags'][0];
                $data['tags'] = array_filter(array_map('trim', explode(',', $tagsInput)));
            } elseif (is_string($data['tags'])) {
                // Handle direct string input
                $data['tags'] = array_filter(array_map('trim', explode(',', $data['tags'])));
            }
        }

        $contact->update($data);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Contact updated successfully',
                'contact' => $contact->fresh()
            ]);
        }
        
        return redirect()->route('crm.contacts.index')->with('status', 'Contact updated');
    }

    public function destroy(Request $request, Contact $contact)
    {
        if (!auth()->user()->can('delete contacts')) {
            abort(403, 'Unauthorized. You do not have permission to delete contacts.');
        }

        // Check if user can access this record
        if (!PermissionHelper::canAccessRecord($contact, auth()->user())) {
            abort(403, 'Unauthorized. You do not have access to this contact.');
        }

        $contact->delete();
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Contact deleted successfully'
            ]);
        }
        
        return redirect()->route('crm.contacts.index')->with('status', 'Contact archived');
    }

    public function restore($id)
    {
        if (!auth()->user()->can('edit contacts')) {
            abort(403, 'Unauthorized. You do not have permission to restore contacts.');
        }

        $contact = Contact::withTrashed()->findOrFail($id);
        
        // Check if user can access this record
        if (!PermissionHelper::canAccessRecord($contact, auth()->user())) {
            abort(403, 'Unauthorized. You do not have access to this contact.');
        }

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
        if (!auth()->user()->can('delete contacts')) {
            abort(403, 'Unauthorized. You do not have permission to delete contacts.');
        }

        $ids = (array) $request->input('ids', []);
        
        // Return error if no IDs provided
        if (empty($ids)) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No contacts selected for deletion'
                ], 400);
            }
            
            return redirect()->route('crm.contacts.index')->with('error', 'No contacts selected for deletion');
        }
        
        Contact::whereIn('id', $ids)->delete();
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Selected contacts deleted successfully'
            ]);
        }
        
        return redirect()->route('crm.contacts.index')->with('status', 'Selected contacts deleted');
    }

    public function datatable(Request $request)
    {
        if (!auth()->user()->can('view contacts')) {
            abort(403, 'Unauthorized. You do not have permission to view contacts.');
        }

        if (Schema::hasTable('users')) {
            $query = Contact::with('assignedUser');
        } else {
            $query = Contact::query();
        }

        // Filter by role (Executive sees only assigned records)
        $query = PermissionHelper::filterByRole($query, auth()->user(), 'assigned_user_id');

        // Search from DataTables
        if ($search = trim((string) $request->input('search.value'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Additional filters from form
        if ($request->filled('company')) {
            $query->where('company', 'like', '%'.$request->input('company').'%');
        }
        if ($request->filled('assigned_user_id')) {
            $query->where('assigned_user_id', $request->input('assigned_user_id'));
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

        // Get total count before filtering
        $totalRecords = Contact::count();
        $filteredRecords = $query->count();

        // Ordering
        $orderColumn = $request->input('order.0.column', 7); // Default to created_at
        $orderDir = $request->input('order.0.dir', 'desc');
        
        $columns = ['id', 'name', 'company', 'email', 'phone', 'assigned_user_id', 'tags', 'created_at', 'status'];
        $sortColumn = $columns[$orderColumn] ?? 'created_at';
        
        $allowedSorts = ['name','company','email','phone','assigned_user_id','created_at'];
        if (!in_array($sortColumn, $allowedSorts, true)) {
            $sortColumn = 'created_at';
        }
        
        $orderDir = strtolower($orderDir) === 'asc' ? 'asc' : 'desc';

        // Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        
        $contacts = $query->orderBy($sortColumn, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $user = auth()->user();
        $canDelete = $user->can('delete contacts');
        $canEdit = $user->can('edit contacts');

        $data = $contacts->map(function ($contact) use ($canDelete, $canEdit, $user) {
            $isArchived = (($contact->status ?? null) === 'archived' || !is_null($contact->deleted_at ?? null));
            $tagsText = implode(',', (array) $contact->tags);
            $assigned = $contact->assignedUser ? $contact->assignedUser->name : ($contact->assigned_user_id ? 'User '.$contact->assigned_user_id : '-');
            
            // Check if user can access this record for edit/delete
            $canAccess = PermissionHelper::canAccessRecord($contact, $user);
            
            $actionsHtml = '<div class="flex flex-col sm:flex-row gap-1 justify-center">';
            
            if ($canEdit && $canAccess) {
                $actionsHtml .= '<button type="button" class="inline-flex items-center gap-1 px-2 sm:px-3 py-1 rounded-lg border border-blue-400 text-blue-600 hover:bg-blue-50 shadow-sm text-xs edit-btn" data-id="'.$contact->id.'" data-name="'.htmlspecialchars($contact->name, ENT_QUOTES).'" data-company="'.htmlspecialchars($contact->company ?? '', ENT_QUOTES).'" data-email="'.htmlspecialchars($contact->email ?? '', ENT_QUOTES).'" data-phone="'.htmlspecialchars($contact->phone ?? '', ENT_QUOTES).'" data-assigned="'.($contact->assigned_user_id ?? '').'" data-status="'.($contact->status ?? 'active').'" data-tags="'.htmlspecialchars($tagsText, ENT_QUOTES).'" data-notes="'.htmlspecialchars($contact->notes ?? '', ENT_QUOTES).'"><svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-8.5 8.5a2 2 0 01-.878.515l-3.3.943a.5.5 0 01-.62-.62l.943-3.3a2 2 0 01.515-.878l8.5-8.5z"/></svg><span class="hidden sm:inline">Edit</span></button>';
            }
            
            if ($canDelete && $canAccess) {
                $actionsHtml .= '<button type="button" class="inline-flex items-center gap-1 px-2 sm:px-3 py-1 rounded-lg border border-red-400 text-red-600 hover:bg-red-50 shadow-sm text-xs delete-btn" data-id="'.$contact->id.'"><svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 8a1 1 0 011 1v6a1 1 0 102 0V9a1 1 0 112 0v6a1 1 0 102 0V9a1 1 0 011-1h1a1 1 0 100-2h-1V5a2 2 0 00-2-2H9a2 2 0 00-2 2v1H6a1 1 0 100 2h1zm3-3h2v1H9V5z" clip-rule="evenodd"/></svg><span class="hidden sm:inline">Del</span></button>';
            }
            
            $actionsHtml .= '</div>';
            
            return [
                'id' => $contact->id,
                'name' => $contact->name,
                'company' => $contact->company ?? '-',
                'email' => $contact->email ?? '-',
                'phone' => $contact->phone ?? '-',
                'assigned' => $assigned,
                'tags' => $tagsText ?: '-',
                'created_at' => $contact->created_at?->format('Y-m-d') ?? '-',
                'status' => $isArchived ? 'archived' : 'active',
                'status_html' => $isArchived 
                    ? '<span class="inline-flex items-center gap-1 text-red-600" title="Archived"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.54-10.46a.75.75 0 00-1.06-1.06L10 8.94 7.52 6.48a.75.75 0 00-1.06 1.06L8.94 10l-2.48 2.48a.75.75 0 101.06 1.06L10 11.06l2.48 2.48a.75.75 0 101.06-1.06L11.06 10l2.48-2.46z" clip-rule="evenodd"/></svg>Archived</span>'
                    : '<span class="inline-flex items-center gap-1 text-blue-600" title="Active"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-7.364 7.364a1 1 0 01-1.414 0L3.293 10.435a1 1 0 011.414-1.414l3.221 3.221 6.657-6.657a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>Active</span>',
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

    public function export(Request $request)
    {
        if (!auth()->user()->can('export contacts')) {
            abort(403, 'Unauthorized. You do not have permission to export contacts.');
        }

        $query = Contact::query();
        
        // Filter by role (Executive sees only assigned records)
        $query = PermissionHelper::filterByRole($query, auth()->user(), 'assigned_user_id');

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


