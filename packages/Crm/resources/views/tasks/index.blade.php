@php
    $qs = request()->query();
    function sort_link($key, $label) {
        $current = request('sort');
        $direction = request('direction','asc') === 'asc' ? 'desc' : 'asc';
        $params = array_merge(request()->query(), ['sort' => $key, 'direction' => ($current === $key ? $direction : 'asc')]);
        $url = request()->url().'?'.http_build_query($params);
        return '<a href="'.$url.'" class="text-blue-400 hover:text-blue-600 font-semibold uppercase tracking-wider">'.$label.'</a>';
    }
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CRM Tasks</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            background:
                url('{{ asset('image/Screenshot_16.png') }}') center center/cover no-repeat fixed !important;
            min-height: 100vh;
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
        }
        .glass { backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.35); box-shadow: inset 0 0 0 1px rgba(255,255,255,.15); }
        .sidebar-link{ display:flex; align-items:center; gap:.75rem; color:#0f172a; text-decoration:none; padding:.6rem .9rem; border-radius:.6rem; }
        .sidebar-link:hover{ background: rgba(0,0,0,.06); }
        .sidebar-link svg{ width:20px; height:20px; min-width:20px; min-height:20px; }
        .priority-badge {
            display: inline-flex;
            align-items: center;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
            color: #374151;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
            color: #374151;
        }
    </style>
</head>
<body>

<div x-data="{open:true, showCreate:false, showEdit:false, showDelete:false, showBulkDelete:false, editId:null, editTitle:'', editType:'', editPriority:'medium', editDueDate:'', editStatus:'pending', editAssigned:'', editContactId:'', editLeadId:'', editNotes:''}" class="relative">
    <aside class="fixed top-3 left-3 h-[calc(100vh-24px)] glass rounded-2xl p-3 transition-all duration-300" :class="open ? 'w-64' : 'w-16'">
        <div class="flex items-center justify-between mb-4">
            <div class="text-gray-900 font-extrabold tracking-wide mt-5" :class="open ? 'opacity-100' : 'opacity-0 pointer-events-none'">WELCOME USER</div>
            <button @click="open=!open" class="text-white bg-white/20 border border-white/40 rounded-full w-7 h-7 flex items-center justify-center hover:bg-white/30 mt-5" :aria-expanded="open">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="open ? 'M15 19l-7-7 7-7' : 'M9 5l7 7-7 7'"/></svg>
            </button>
        </div>
        <div class="text-gray-900/80 text-xs uppercase tracking-wider mb-2 mt-5" :class="open ? 'opacity-100' : 'opacity-0 pointer-events-none'">General</div>
        <nav class="space-y-1 mt-4">
            <a href="{{ route('crm.contacts.index') }}" class="sidebar-link">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10.707 1.293a1 1 0 00-1.414 0l-7 7A1 1 0 003 10h1v7a1 1 0 001 1h4v-4h2v4h4a1 1 0 001-1v-7h1a1 1 0 00.707-1.707l-7-7z"/></svg>
                <span x-show="open" x-transition>Contacts</span>
            </a>
            <a href="{{ route('crm.leads.index') }}" class="sidebar-link">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M3 3h2l.4 2M7 13h8l2-8H5.4M7 13L6 6m1 7l-1 4m8-4l1 4m-5-4v4"/></svg>
                <span x-show="open" x-transition>Leads</span>
            </a>
            <a href="{{ route('crm.tasks.index') }}" class="sidebar-link bg-white/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/></svg>
                <span x-show="open" x-transition>Tasks</span>
            </a>
            <a href="{{ route('crm.pipeline.index') }}" class="sidebar-link">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M3 12v3c0 1.657 3.134 3 7 3s7-1.343 7-3v-3c0 1.657-3.134 3-7 3s-7-1.343-7-3z"/><path d="M3 7v3c0 1.657 3.134 3 7 3s7-1.343 7-3V7c0 1.657-3.134 3-7 3S3 8.657 3 7z"/><path d="M17 5c0 1.657-3.134 3-7 3S3 6.657 3 5s3.134-3 7-3 7 1.343 7 3z"/></svg>
                <span x-show="open" x-transition>Pipeline</span>
            </a>
        </nav>
    </aside>

    <div class="transition-all duration-300" :style="open ? 'padding-left:280px' : 'padding-left:88px'">
        <div class="min-h-screen flex flex-col justify-center items-center px-2 py-8">
            <div class="w-full max-w-6xl mx-auto px-3 md:px-4 py-3">
                <div class="glass w-full rounded-xl px-6 py-3 mb-4 flex items-center justify-between text-white">
                    <div class="text-lg md:text-xl font-semibold tracking-wide">TASKS</div>
                </div>
                <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-6">
                    <div class="flex items-end gap-6 w-full md:max-w-xl">
                        <button type="button" @click="showCreate=true" class="flex-shrink-0 flex items-center gap-2 px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow-md">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 20 20" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"/></svg>
                            New Task
                        </button>
                    </div>
                    <div class="flex items-end gap-3 md:ml-auto w-full md:w-auto">
                        <form method="GET" class="flex items-center gap-0 bg-white/30 backdrop-blur-sm rounded-xl px-3 py-2 shadow-inner w-full md:w-80">
                            <label for="mainsearch" class="text-gray-600 px-2 text-base font-medium">Search</label>
                            <span class="inline-flex items-center justify-center pl-2 pr-1"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 103 10.5a7.5 7.5 0 0013.15 6.15z" /></svg></span>
                            <input id="mainsearch" type="text" name="search" value="{{ request('search') }}" class="bg-transparent border-0 outline-none ring-0 focus:ring-0 w-full text-gray-900 placeholder-gray-400 text-base px-2 py-1" placeholder="" autocomplete="off">
                        </form>
                        <button type="submit" formaction="#" class="hidden" aria-hidden="true"></button>
                    </div>
                </div>
                <div class="mb-4">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                    <div class="md:col-span-5 flex items-center justify-between">
                        <div class="font-semibold text-xl text-gray-700 ml-1 tracking-wide">Filter</div>
                        <button type="submit"class="px-5 py-2 rounded-xl bg-blue-600 text-white shadow hover:bg-blue-700">Filter
                        </button>
                    </div>

                        <div>
                            <input type="text" name="type" value="{{ request('type') }}" class="w-full border rounded-xl px-3 py-2 bg-transparent text-gray-800 focus:ring-2 focus:ring-blue-400 placeholder-gray-500" autocomplete="off" placeholder="Type">
                        </div>
                        <div>
                            <select name="priority" class="w-full border rounded-xl px-3 py-2 bg-transparent text-gray-800 focus:ring-2 focus:ring-blue-400">
                                <option value="">All Priority</option>
                                <option value="low" @selected(request('priority')==='low')>Low</option>
                                <option value="medium" @selected(request('priority')==='medium')>Medium</option>
                                <option value="high" @selected(request('priority')==='high')>High</option>
                            </select>
                        </div>
                        <div>
                            <select name="status" class="w-full border rounded-xl px-3 py-2 bg-transparent text-gray-800 focus:ring-2 focus:ring-blue-400">
                                <option value="">All Status</option>
                                <option value="pending" @selected(request('status')==='pending')>Pending</option>
                                <option value="in_progress" @selected(request('status')==='in_progress')>In Progress</option>
                                <option value="completed" @selected(request('status')==='completed')>Completed</option>
                            </select>
                        </div>
                        <div>
                            <input type="date" name="due_date_from" value="{{ request('due_date_from') }}" class="w-full border rounded-xl px-3 py-2 bg-transparent text-gray-800 focus:ring-2 focus:ring-blue-400" autocomplete="off" placeholder="Due From">
                        </div>
                        <div>
                            <input type="date" name="due_date_to" value="{{ request('due_date_to') }}" class="w-full border rounded-xl px-3 py-2 bg-transparent text-gray-800 focus:ring-2 focus:ring-blue-400" autocomplete="off" placeholder="Due To">
                        </div>
                    </form>
                </div>
                <form method="POST" action="{{ route('crm.tasks.bulk-delete') }}" x-ref="bulkForm">
                    @csrf
                    <div class="mb-2 flex items-end gap-2 px-2">
                        <button type="button" class="px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl shadow" @click="showBulkDelete=true">Delete Selected</button>
                        <span class="text-sm text-gray-500 pb-1 ml-4">Click the 'Export' button to download to Excel.</span>
                    </div>
                    <div class="overflow-x-auto rounded-xl shadow-xl glass">
                        <table class="min-w-full text-sm bg-white/20 backdrop-blur-sm border border-white/30 rounded-xl">
                            <thead class="uppercase bg-white/40 text-gray-800 rounded-xl">
                                <tr>
                                    <th class="p-3 text-center"><input type="checkbox" @click="$el.closest('table').querySelectorAll('.row-check').forEach(cb=>cb.checked=$event.target.checked)"></th>
                                    <th class="p-3 font-semibold tracking-widest text-center">Title</th>
                                    <th class="p-3 font-semibold tracking-widest text-center">Type</th>
                                    <th class="p-3 font-semibold tracking-widest text-center">Priority</th>
                                    <th class="p-3 font-semibold tracking-widest text-center">Due Date</th>
                                    <th class="p-3 font-semibold tracking-widest text-center">Status</th>
                                    <th class="p-3 font-semibold tracking-widest text-center">Assigned</th>
                                    <th class="p-3 font-semibold tracking-widest text-center">Linked To</th>
                                    <th class="p-3 font-semibold tracking-widest text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                        @forelse($tasks as $t)
                            <tr class="border-b border-white/30 bg-white/20 hover:bg-white/40 transition">
                                <td class="p-2 text-center"><input type="checkbox" class="row-check" name="ids[]" value="{{ $t->id }}"></td>
                                <td class="p-2 text-center font-medium">{{ $t->title }}</td>
                                <td class="p-2 text-center">{{ $t->type ?? '-' }}</td>
                                <td class="p-2 text-center">
                                    <span class="priority-badge font-semibold
                                        @if($t->priority === 'high') text-red-600 
                                        @elseif($t->priority === 'medium') text-amber-600 
                                        @else text-green-600 
                                        @endif">
                                        {{ ucfirst($t->priority) }}
                                    </span>
                                </td>
                                <td class="p-2 text-center">{{ $t->due_date?->format('Y-m-d') ?? '-' }}</td>
                                <td class="p-2 text-center">
                                    <span class="status-badge font-semibold
                                        @if($t->status === 'completed') text-green-600 
                                        @elseif($t->status === 'in_progress') text-blue-600 
                                        @else text-gray-600 
                                        @endif">
                                        {{ str_replace('_', ' ', ucfirst($t->status)) }}
                                    </span>
                                </td>
                                <td class="p-2 text-center">{{ $t->assigned_user_id ? 'User '.$t->assigned_user_id : '-' }}</td>
                                <td class="p-2 text-center">
                                    @if($t->contact)
                                        <span class="text-xs text-gray-700">Contact: {{ $t->contact->name }}</span>
                                    @elseif($t->lead)
                                        <span class="text-xs text-gray-700">Lead: {{ $t->lead->name }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="p-2 text-center">
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-1 px-3 py-1 rounded-lg border border-blue-400 text-blue-600 hover:bg-blue-50 shadow-sm"
                                        data-id="{{ $t->id }}" data-title="{{ $t->title }}" data-type="{{ $t->type }}"
                                        data-priority="{{ $t->priority }}" data-due-date="{{ $t->due_date?->format('Y-m-d') }}" 
                                        data-status="{{ $t->status }}" data-assigned="{{ $t->assigned_user_id }}"
                                        data-contact-id="{{ $t->contact_id }}" data-lead-id="{{ $t->lead_id }}" data-notes="{{ $t->notes }}"
                                        @click.prevent="
                                        editId=$el.dataset.id;
                                        editTitle=$el.dataset.title;
                                        editType=$el.dataset.type;
                                        editPriority=$el.dataset.priority;
                                        editDueDate=$el.dataset.dueDate;
                                        editStatus=$el.dataset.status;
                                        editAssigned=$el.dataset.assigned;
                                        editContactId=$el.dataset.contactId;
                                        editLeadId=$el.dataset.leadId;
                                        editNotes=$el.dataset.notes||'';
                                        showEdit=true;
                                        ">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-8.5 8.5a2 2 0 01-.878.515l-3.3.943a.5.5 0 01-.62-.62l.943-3.3a2 2 0 01.515-.878l8.5-8.5z"/></svg>Edit
                                    </button>
                                    <button type="button"class="inline-flex items-center gap-1 px-3 py-1 rounded-lg border border-red-400 text-red-600 hover:bg-red-50 shadow-sm"data-id="{{ $t->id }}" @click.prevent="editId=$el.dataset.id; showDelete=true;">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M6 8a1 1 0 011 1v6a1 1 0 102 0V9a1 1 0 112 0v6a1 1 0 102 0V9a1 1 0 011-1h1a1 1 0 100-2h-1V5a2 2 0 00-2-2H9a2 2 0 00-2 2v1H6a1 1 0 100 2h1zm3-3h2v1H9V5z" clip-rule="evenodd"/></svg>Del
                                    </button>
                                </td>

                            </tr>
                        @empty
                            <tr><td colspan="9" class="p-4 text-center text-gray-400">No tasks found</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div x-show="showBulkDelete" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center">
                    <div class="absolute inset-0 bg-black/50" @click="showBulkDelete=false"></div>
                    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                        <div class="text-lg font-semibold mb-3">Delete Selected</div>
                        <p class="text-sm text-gray-600 mb-4">Are you sure you want to delete the selected tasks?</p>
                        <div class="flex justify-end gap-2">
                            <button type="button" class="px-4 py-2 rounded-lg border" @click="showBulkDelete=false">Cancel</button>
                            <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 text-white">Delete</button>
                        </div>
                    </div>
                </div>
                </form>
                <div class="flex flex-col md:flex-row items-center md:justify-between gap-3 p-3">
                    <div class="text-sm text-gray-600">
                        Showing <span class="font-medium">{{ $tasks->firstItem() ?? 0 }}</span>–<span class="font-medium">{{ $tasks->lastItem() ?? 0 }}</span> of <span class="font-medium">{{ $tasks->total() }}</span>
                    </div>
                    <div class="flex gap-1 items-center">
                        @if ($tasks->onFirstPage())
                            <span class="px-3 py-2 rounded-xl border text-gray-400 cursor-not-allowed">&laquo; Prev</span>
                        @else
                            <a href="{{ $tasks->previousPageUrl() }}" class="px-3 py-2 rounded-xl border hover:bg-white/40">&laquo; Prev</a>
                        @endif
                        @php
                            $current = $tasks->currentPage();
                            $last = $tasks->lastPage();
                            $window = 2;
                            $pages = [];
                            if ($last <= 7) {
                                $pages = range(1, $last);
                            } else {
                                $pages = [1, 2];
                                $start = max(3, $current - $window);
                                $end = min($last - 2, $current + $window);
                                if ($start > 3) $pages[] = '...';
                                foreach (range($start, $end) as $p) { $pages[] = $p; }
                                if ($end < $last - 2) $pages[] = '...';
                                $pages[] = $last - 1; $pages[] = $last;
                            }
                        @endphp
                        @foreach ($pages as $p)
                            @if ($p === '...')
                                <span class="px-3 py-2 text-gray-400 select-none">...</span>
                            @elseif ($p == $current)
                                <span class="px-3 py-2 rounded-xl border bg-blue-600 text-white">{{ $p }}</span>
                            @else
                                <a href="{{ $tasks->url($p) }}" class="px-3 py-2 rounded-xl border hover:bg-white/60">{{ $p }}</a>
                            @endif
                        @endforeach
                        @if ($tasks->hasMorePages())
                            <a href="{{ $tasks->nextPageUrl() }}" class="px-3 py-2 rounded-xl border hover:bg-white/40">Next &raquo;</a>
                        @else
                            <span class="px-3 py-2 rounded-xl border text-gray-400 cursor-not-allowed">Next &raquo;</span>
                        @endif
                    </div>
                    <form method="GET" class="flex items-center gap-2">
                        <label class="text-sm text-gray-600">Items per page</label>
                        <input type="number" name="per_page" min="1" max="100" value="{{ request('per_page', 10) }}" class="w-20 border rounded-xl px-3 py-2 bg-white/60 text-gray-700 shadow-inner">
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        <input type="hidden" name="type" value="{{ request('type') }}">
                        <input type="hidden" name="priority" value="{{ request('priority') }}">
                        <input type="hidden" name="status" value="{{ request('status') }}">
                        <input type="hidden" name="due_date_from" value="{{ request('due_date_from') }}">
                        <input type="hidden" name="due_date_to" value="{{ request('due_date_to') }}">
                        <input type="hidden" name="sort" value="{{ request('sort') }}">
                        <input type="hidden" name="direction" value="{{ request('direction') }}">
                        <button class="px-4 py-2 rounded-xl border hover:bg-white/70 bg-white/40 text-gray-700">Apply</button>
                        <a href="{{ route('crm.tasks.export', request()->query()) }}" class="px-4 py-2 rounded-xl border bg-green-100 hover:bg-green-200 text-green-700 ml-2">Export</a>
                        <a href="{{ route('crm.tasks.index') }}" class="px-4 py-2 rounded-xl border bg-gray-100 hover:bg-gray-200 text-gray-600 ml-2">Reset</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Task Modal -->
    <div x-show="showCreate" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/50" @click="showCreate=false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-xl p-6">
            <div class="text-lg font-semibold mb-4">Create Task</div>
            <form method="POST" action="{{ route('crm.tasks.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf
                <input name="title" class="border rounded-lg px-3 py-2 md:col-span-2" placeholder="Task Title" required>
                <input name="type" class="border rounded-lg px-3 py-2" placeholder="Type (e.g., Call, Email, Meeting)">
                <select name="priority" class="border rounded-lg px-3 py-2">
                    <option value="low">Low Priority</option>
                    <option value="medium" selected>Medium Priority</option>
                    <option value="high">High Priority</option>
                </select>
                <input name="due_date" type="date" class="border rounded-lg px-3 py-2" placeholder="Due Date">
                <select name="status" class="border rounded-lg px-3 py-2">
                    <option value="pending" selected>Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                </select>
                <input name="assigned_user_id" type="number" class="border rounded-lg px-3 py-2" placeholder="Assigned User ID">
                <input name="contact_id" type="number" class="border rounded-lg px-3 py-2" placeholder="Contact ID (optional)">
                <input name="lead_id" type="number" class="border rounded-lg px-3 py-2" placeholder="Lead ID (optional)">
                <textarea name="notes" class="border rounded-lg px-3 py-2 md:col-span-2" rows="3" placeholder="Notes"></textarea>
                <div class="md:col-span-2 flex justify-end gap-2 mt-2">
                    <button type="button" class="px-4 py-2 rounded-lg border" @click="showCreate=false">Cancel</button>
                    <button class="px-4 py-2 rounded-lg bg-blue-600 text-white">Create</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Task Modal -->
    <div x-show="showEdit" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/50" @click="showEdit=false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-xl p-6">
            <div class="text-lg font-semibold mb-4">Edit Task</div>
            <form :action="'{{ route('crm.tasks.update','__ID__') }}'.replace('__ID__', editId)" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf
                @method('PUT')
                <input name="title" class="border rounded-lg px-3 py-2 md:col-span-2" placeholder="Task Title" x-model="editTitle" required>
                <input name="type" class="border rounded-lg px-3 py-2" placeholder="Type" x-model="editType">
                <select name="priority" class="border rounded-lg px-3 py-2" x-model="editPriority">
                    <option value="low">Low Priority</option>
                    <option value="medium">Medium Priority</option>
                    <option value="high">High Priority</option>
                </select>
                <input name="due_date" type="date" class="border rounded-lg px-3 py-2" x-model="editDueDate">
                <select name="status" class="border rounded-lg px-3 py-2" x-model="editStatus">
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                </select>
                <input name="assigned_user_id" type="number" class="border rounded-lg px-3 py-2" placeholder="Assigned User ID" x-model="editAssigned">
                <input name="contact_id" type="number" class="border rounded-lg px-3 py-2" placeholder="Contact ID" x-model="editContactId">
                <input name="lead_id" type="number" class="border rounded-lg px-3 py-2" placeholder="Lead ID" x-model="editLeadId">
                <textarea name="notes" class="border rounded-lg px-3 py-2 md:col-span-2" rows="3" placeholder="Notes" x-model="editNotes"></textarea>
                <div class="md:col-span-2 flex justify-end gap-2 mt-2">
                    <button type="button" class="px-4 py-2 rounded-lg border" @click="showEdit=false">Cancel</button>
                    <button class="px-4 py-2 rounded-lg bg-blue-600 text-white">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Task Modal -->
    <div x-show="showDelete" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/50" @click="showDelete=false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <div class="text-lg font-semibold mb-3">Delete Task</div>
            <p class="text-sm text-gray-600 mb-4">Are you sure you want to delete this task?</p>
            <form :action="'{{ route('crm.tasks.destroy','__ID__') }}'.replace('__ID__', editId)" method="POST" class="flex justify-end gap-2">
                @csrf
                @method('DELETE')
                <button type="button" class="px-4 py-2 rounded-lg border" @click="showDelete=false">Cancel</button>
                <button class="px-4 py-2 rounded-lg bg-red-600 text-white">Delete</button>
            </form>
        </div>
    </div>

    
</div>
</body>
</html>

