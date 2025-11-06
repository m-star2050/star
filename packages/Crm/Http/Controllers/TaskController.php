<?php

namespace Packages\Crm\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Packages\Crm\Models\Task;
use App\Models\User;
use Packages\Crm\Models\Contact;
use Packages\Crm\Models\Lead;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $users = User::select('id', 'name', 'email')->orderBy('name')->get();
        $contacts = Contact::select('id', 'name', 'email', 'company')->orderBy('name')->get();
        $leads = Lead::select('id', 'name', 'email', 'company')->orderBy('name')->get();
        
        return view('crm::tasks.index', [
            'users' => $users,
            'contacts' => $contacts,
            'leads' => $leads,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
            'due_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['pending', 'in_progress', 'completed'])],
            'assigned_user_id' => ['nullable', 'integer'],
            'contact_id' => ['nullable', 'integer', 'exists:crm_contacts,id'],
            'lead_id' => ['nullable', 'integer', 'exists:crm_leads,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $task = Task::create($data);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Task created successfully',
                'task' => $task
            ]);
        }
        
        return redirect()->route('crm.tasks.index')->with('status', 'Task created');
    }

    public function update(Request $request, Task $task)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
            'due_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['pending', 'in_progress', 'completed'])],
            'assigned_user_id' => ['nullable', 'integer'],
            'contact_id' => ['nullable', 'integer', 'exists:crm_contacts,id'],
            'lead_id' => ['nullable', 'integer', 'exists:crm_leads,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $task->update($data);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Task updated successfully',
                'task' => $task->fresh()
            ]);
        }
        
        return redirect()->route('crm.tasks.index')->with('status', 'Task updated');
    }

    public function destroy(Request $request, Task $task)
    {
        $task->delete();
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Task deleted successfully'
            ]);
        }
        
        return redirect()->route('crm.tasks.index');
    }

    public function restore($id)
    {
        $task = Task::withTrashed()->findOrFail($id);
        $task->restore();
        return redirect()->route('crm.tasks.index')->with('status', 'Task restored');
    }

    public function toggleStatus(Request $request, Task $task)
    {
        $status = $request->input('status');
        
        if (! in_array($status, ['pending', 'in_progress', 'completed'], true)) {
            return response()->json(['success' => false, 'message' => 'Invalid status'], 422);
        }

        $task->status = $status;
        $task->save();

        return response()->json(['success' => true, 'status' => $status]);
    }

    public function bulkDelete(Request $request)
    {
        $ids = (array) $request->input('ids', []);
        
        if (empty($ids)) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tasks selected for deletion'
                ], 400);
            }
            return redirect()->route('crm.tasks.index')->with('error', 'No tasks selected for deletion');
        }
        
        Task::whereIn('id', $ids)->delete();
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Selected tasks deleted successfully'
            ]);
        }
        
        return redirect()->route('crm.tasks.index');
    }

    public function export(Request $request)
    {
        $query = Task::query()->with(['contact', 'lead']);

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', 'like', '%'.$request->input('type').'%');
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('assigned_user_id')) {
            $query->where('assigned_user_id', $request->input('assigned_user_id'));
        }
        if ($request->filled('due_date_from')) {
            $query->whereDate('due_date', '>=', $request->input('due_date_from'));
        }
        if ($request->filled('due_date_to')) {
            $query->whereDate('due_date', '<=', $request->input('due_date_to'));
        }

        $rows = $query->orderBy('created_at', 'desc')->get();

        $filename = 'tasks_export_'.now()->format('Ymd_His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Title', 'Type', 'Priority', 'Due Date', 'Status', 'Assigned User', 'Contact', 'Lead', 'Created At']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->id,
                    $r->title,
                    $r->type,
                    $r->priority,
                    $r->due_date?->format('Y-m-d'),
                    $r->status,
                    $r->assigned_user_id,
                    $r->contact?->name,
                    $r->lead?->name,
                    $r->created_at?->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function datatable(Request $request)
    {
        $query = Task::query()->with(['contact', 'lead', 'assignedUser']);

        if ($search = trim((string) $request->input('search.value'))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhereHas('contact', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('lead', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('type')) {
            $query->where('type', 'like', '%'.$request->input('type').'%');
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('assigned_user_id')) {
            $query->where('assigned_user_id', $request->input('assigned_user_id'));
        }
        if ($request->filled('due_date_from')) {
            $query->whereDate('due_date', '>=', $request->input('due_date_from'));
        }
        if ($request->filled('due_date_to')) {
            $query->whereDate('due_date', '<=', $request->input('due_date_to'));
        }

        $totalRecords = Task::count();
        $filteredRecords = $query->count();

        $orderColumn = $request->input('order.0.column', 8);
        $orderDir = $request->input('order.0.dir', 'desc');
        
        $columns = ['id', 'title', 'type', 'priority', 'due_date', 'status', 'assigned_user_id', 'contact_id', 'created_at'];
        $sortColumn = $columns[$orderColumn] ?? 'created_at';
        
        $allowedSorts = ['title', 'type', 'priority', 'due_date', 'status', 'assigned_user_id', 'created_at'];
        if (!in_array($sortColumn, $allowedSorts, true)) {
            $sortColumn = 'created_at';
        }
        
        $orderDir = strtolower($orderDir) === 'asc' ? 'asc' : 'desc';

        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        
        $tasks = $query->orderBy($sortColumn, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $data = $tasks->map(function ($task) {
            $isArchived = !is_null($task->deleted_at ?? null);
            
            $priorityColors = [
                'low' => 'text-green-600',
                'medium' => 'text-yellow-600',
                'high' => 'text-red-600',
            ];
            $priorityColor = $priorityColors[$task->priority] ?? 'text-gray-600';
            
            $statusColors = [
                'pending' => 'text-gray-600',
                'in_progress' => 'text-blue-600',
                'completed' => 'text-green-600',
            ];
            $statusColor = $statusColors[$task->status] ?? 'text-gray-600';
            
            return [
                'id' => $task->id,
                'title' => $task->title,
                'type' => $task->type ?? '-',
                'priority' => ucfirst($task->priority),
                'priority_html' => '<span class="inline-flex items-center gap-1 '.$priorityColor.' font-semibold">'.ucfirst($task->priority).'</span>',
                'due_date' => $task->due_date?->format('Y-m-d') ?? '-',
                'status' => ucfirst(str_replace('_', ' ', $task->status)),
                'status_html' => '<span class="inline-flex items-center gap-1 '.$statusColor.' font-semibold">'.ucfirst(str_replace('_', ' ', $task->status)).'</span>',
                'assigned' => $task->assignedUser ? $task->assignedUser->name : ($task->assigned_user_id ? 'User ' . $task->assigned_user_id : '-'),
                'lead' => $task->lead?->name ?? '-',
                'created_at' => $task->created_at?->format('Y-m-d') ?? '-',
                'archive_html' => $isArchived 
                    ? '<span class="inline-flex items-center gap-1 text-red-600"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.54-10.46a.75.75 0 00-1.06-1.06L10 8.94 7.52 6.48a.75.75 0 00-1.06 1.06L8.94 10l-2.48 2.48a.75.75 0 101.06 1.06L10 11.06l2.48 2.48a.75.75 0 101.06-1.06L11.06 10l2.48-2.46z" clip-rule="evenodd"/></svg>Archived</span>'
                    : '<span class="inline-flex items-center gap-1 text-blue-600"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-7.364 7.364a1 1 0 01-1.414 0L3.293 10.435a1 1 0 011.414-1.414l3.221 3.221 6.657-6.657a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>Active</span>',
                'actions_html' => '<div class="flex flex-col sm:flex-row gap-1 justify-center">
                    <button type="button" class="inline-flex items-center gap-1 px-2 sm:px-3 py-1 rounded-lg border border-blue-400 text-blue-600 hover:bg-blue-50 shadow-sm text-xs edit-btn" data-id="'.$task->id.'" data-title="'.htmlspecialchars($task->title, ENT_QUOTES).'" data-type="'.htmlspecialchars($task->type ?? '', ENT_QUOTES).'" data-priority="'.($task->priority ?? 'medium').'" data-due-date="'.($task->due_date?->format('Y-m-d') ?? '').'" data-status="'.($task->status ?? 'pending').'" data-assigned="'.($task->assigned_user_id ?? '').'" data-contact="'.($task->contact_id ?? '').'" data-lead="'.($task->lead_id ?? '').'" data-notes="'.htmlspecialchars($task->notes ?? '', ENT_QUOTES).'"><svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-8.5 8.5a2 2 0 01-.878.515l-3.3.943a.5.5 0 01-.62-.62l.943-3.3a2 2 0 01.515-.878l8.5-8.5z"/></svg><span class="hidden sm:inline">Edit</span></button>
                    <button type="button" class="inline-flex items-center gap-1 px-2 sm:px-3 py-1 rounded-lg border border-red-400 text-red-600 hover:bg-red-50 shadow-sm text-xs delete-btn" data-id="'.$task->id.'"><svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 8a1 1 0 011 1v6a1 1 0 102 0V9a1 1 0 112 0v6a1 1 0 102 0V9a1 1 0 011-1h1a1 1 0 100-2h-1V5a2 2 0 00-2-2H9a2 2 0 00-2 2v1H6a1 1 0 100 2h1zm3-3h2v1H9V5z" clip-rule="evenodd"/></svg><span class="hidden sm:inline">Del</span></button>
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

