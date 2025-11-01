<?php

namespace Packages\Crm\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Packages\Crm\Models\Task;
use Packages\Crm\Models\Contact;
use Packages\Crm\Models\Lead;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);
        $sort = $request->input('sort', 'due_date');
        $direction = $request->input('direction', 'asc');

        $query = Task::query()->with(['contact', 'lead']);

        // Search by task title, contact name, or lead name
        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('contact', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('lead', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filters
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
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

        // Sorting
        $allowedSorts = ['title', 'type', 'priority', 'due_date', 'status', 'assigned_user_id', 'created_at'];
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'due_date';
        }
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        $tasks = $query->orderBy($sort, $direction)->paginate($perPage)->withQueryString();

        return view('crm::tasks.index', [
            'tasks' => $tasks,
            'sort' => $sort,
            'direction' => $direction,
            'perPage' => $perPage,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:100'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
            'due_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['pending', 'in_progress', 'completed'])],
            'assigned_user_id' => ['nullable', 'integer'],
            'contact_id' => ['nullable', 'integer', 'exists:crm_contacts,id'],
            'lead_id' => ['nullable', 'integer', 'exists:crm_leads,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $task = Task::create($data);
        return redirect()->route('crm.tasks.index')->with('status', 'Task created');
    }

    public function update(Request $request, Task $task)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:100'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
            'due_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['pending', 'in_progress', 'completed'])],
            'assigned_user_id' => ['nullable', 'integer'],
            'contact_id' => ['nullable', 'integer', 'exists:crm_contacts,id'],
            'lead_id' => ['nullable', 'integer', 'exists:crm_leads,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $task->update($data);
        return redirect()->route('crm.tasks.index')->with('status', 'Task updated');
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return redirect()->route('crm.tasks.index')->with('status', 'Task deleted');
    }

    public function restore($id)
    {
        $task = Task::withTrashed()->findOrFail($id);
        $task->restore();
        return redirect()->route('crm.tasks.index')->with('status', 'Task restored');
    }

    // Inline status toggle
    public function toggleStatus(Request $request, Task $task)
    {
        $status = $request->input('status');
        
        if (! in_array($status, ['pending', 'in_progress', 'completed'], true)) {
            abort(422, 'Invalid status');
        }

        $task->status = $status;
        $task->save();

        return response()->json(['success' => true, 'status' => $status]);
    }

    public function bulkDelete(Request $request)
    {
        $ids = (array) $request->input('ids', []);
        Task::whereIn('id', $ids)->delete();
        return redirect()->route('crm.tasks.index')->with('status', 'Selected tasks deleted');
    }

    public function export(Request $request)
    {
        $query = Task::query()->with(['contact', 'lead']);

        if ($search = trim((string) $request->input('search'))) {
            $query->where('title', 'like', "%{$search}%");
        }

        $rows = $query->orderBy('due_date', 'asc')->get();

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
}

