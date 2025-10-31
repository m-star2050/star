@php
    $qs = request()->query();
    function sort_link($key, $label) {
        $current = request('sort');
        $direction = request('direction','desc') === 'asc' ? 'desc' : 'asc';
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
    <title>CRM Contacts</title>
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
        .hdr-wrap{max-width:1120px}
    </style>
</head>
<body>

<div x-data="{open:true, showCreate:false, showEdit:false, showDelete:false, showBulkDelete:false, editId:null, editName:'', editCompany:'', editEmail:'', editPhone:'', editAssigned:'', editStatus:'active', editTags:'', editNotes:''}" class="relative">
    <aside class="fixed top-3 left-3 h-[calc(100vh-24px)] glass rounded-2xl p-3 transition-all duration-300" :class="open ? 'w-64' : 'w-16'">
        <div class="flex items-center justify-between mb-4">
            <div class="text-gray-900 font-extrabold tracking-wide" :class="open ? 'opacity-100' : 'opacity-0 pointer-events-none'">WELCOME USER</div>
            <button @click="open=!open" class="text-white bg-white/20 border border-white/40 rounded-full w-7 h-7 flex items-center justify-center hover:bg-white/30" :aria-expanded="open">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="open ? 'M15 19l-7-7 7-7' : 'M9 5l7 7-7 7'"/></svg>
            </button>
        </div>
        <div class="text-gray-900/80 text-xs uppercase tracking-wider mb-2 mt-16" :class="open ? 'opacity-100' : 'opacity-0 pointer-events-none'">General</div>
        <nav class="space-y-1 mt-4">
            <a href="#" class="sidebar-link">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10.707 1.293a1 1 0 00-1.414 0l-7 7A1 1 0 003 10h1v7a1 1 0 001 1h4v-4h2v4h4a1 1 0 001-1v-7h1a1 1 0 00.707-1.707l-7-7z"/></svg>
                <span x-show="open" x-transition>Contacts</span>
            </a>
            <a href="#" class="sidebar-link">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M3 3h2l.4 2M7 13h8l2-8H5.4M7 13L6 6m1 7l-1 4m8-4l1 4m-5-4v4"/></svg>
                <span x-show="open" x-transition>Leads</span>
            </a>
 
        </nav>
    </aside>

    <div class="transition-all duration-300" :style="open ? 'padding-left:280px' : 'padding-left:88px'">
        <div class="min-h-screen flex flex-col justify-center items-center px-2 py-8">
            <div class="w-full max-w-6xl mx-auto px-3 md:px-4 py-3">
                <div class="glass w-full rounded-xl px-6 py-3 mb-4 flex items-center justify-between text-white">
                    <div class="text-lg md:text-xl font-semibold tracking-wide">CONTACTS</div>
                </div>
                <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-6">
                    <div class="flex items-end gap-6 w-full md:max-w-xl">
                        <button type="button" @click="showCreate=true" class="flex-shrink-0 flex items-center gap-2 px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow-md">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 20 20" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"/></svg>
                            New Contact
                        </button>
                    </div>
                    <div class="flex items-end gap-3 md:ml-auto w-full md:w-auto">
                        <form method="GET" class="flex items-center gap-0 bg-white/30 backdrop-blur-sm rounded-xl px-3 py-2 shadow-inner w-full md:w-80">
                            <label for="mainsearch" class="text-gray-400 px-2 text-base font-medium">Search</label>
                            <span class="inline-flex items-center justify-center pl-2 pr-1"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 103 10.5a7.5 7.5 0 0013.15 6.15z" /></svg></span>
                            <input id="mainsearch" type="text" name="search" value="{{ request('search') }}" class="bg-transparent border-0 outline-none ring-0 focus:ring-0 w-full text-gray-900 placeholder-gray-400 text-base px-2 py-1" placeholder="" autocomplete="off">
                        </form>
                        <button type="submit" formaction="#" class="hidden" aria-hidden="true"></button>
                    </div>
                </div>
                <div class="mb-4">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                    <div class="md:col-span-5 flex items-center justify-between">
  <div class="font-semibold text-md text-gray-700 ml-1 tracking-wide">Filter</div>
  <button
    type="submit"
    class="px-5 py-2 rounded-xl bg-blue-600 text-white shadow hover:bg-blue-700"
  >
    Filter
  </button>
</div>

                        <div>
                            <input type="text" name="company" value="{{ request('company') }}" class="w-full border rounded-xl px-3 py-2 bg-transparent text-gray-800 focus:ring-2 focus:ring-blue-400 placeholder-gray-500" autocomplete="off" placeholder="Company">
                        </div>
                        <div>
                            <input type="number" name="assigned_user_id" value="{{ request('assigned_user_id') }}" class="w-full border rounded-xl px-3 py-2 bg-transparent text-gray-800 focus:ring-2 focus:ring-blue-400 placeholder-gray-500" autocomplete="off" placeholder="Assigned User">
                        </div>
                        <div>
                            <select name="status" class="w-full border rounded-xl px-3 py-2 bg-transparent text-gray-800 focus:ring-2 focus:ring-blue-400">
                                <option value="">All Status</option>
                                <option value="active" @selected(request('status')==='active')>Active</option>
                                <option value="archived" @selected(request('status')==='archived')>Archived</option>
                            </select>
                        </div>
                        <div>
                            <input type="date" name="created_from" value="{{ request('created_from') }}" class="w-full border rounded-xl px-3 py-2 bg-transparent text-gray-800 focus:ring-2 focus:ring-blue-400" autocomplete="off">
                        </div>
                        <div>
                            <input type="date" name="created_to" value="{{ request('created_to') }}" class="w-full border rounded-xl px-3 py-2 bg-transparent text-gray-800 focus:ring-2 focus:ring-blue-400" autocomplete="off">
                        </div>
                    </form>
                </div>
                <form method="POST" action="{{ route('crm.contacts.bulk-delete') }}" x-ref="bulkForm">
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
                                    <th class="p-3 font-semibold tracking-widest text-center">Name</th>
                                    <th class="p-3 font-semibold tracking-widest text-center">Company</th>
                                    <th class="p-3 font-semibold tracking-widest text-center">Email</th>
                                    <th class="p-3 font-semibold tracking-widest text-center">Phone</th>
                                    <th class="p-3 font-semibold tracking-widest text-center">Assigned</th>
                                    <th class="p-3 font-semibold tracking-widest text-center">Tags</th>
                                    <th class="p-3 font-semibold tracking-widest text-center">Created</th>
                                    <th class="p-3 font-semibold tracking-widest text-center">Actions</th>
                                    <th class="p-3 font-semibold tracking-widest text-center"></th>
                                </tr>
                            </thead>
                            <tbody>
                        @forelse($contacts as $c)
                            <tr class="border-b border-white/30 bg-white/20 hover:bg-white/40 transition">
                                <td class="p-2 text-center"><input type="checkbox" class="row-check" name="ids[]" value="{{ $c->id }}"></td>
                                <td class="p-2 text-center font-medium">{{ $c->name }}</td>
                                <td class="p-2 text-center">{{ $c->company }}</td>
                                <td class="p-2 text-center">{{ $c->email }}</td>
                                <td class="p-2 text-center">{{ $c->phone }}</td>
                                <td class="p-2 text-center">@php $assigned = $c->assigned_user_id ? ('User '.$c->assigned_user_id) : '-'; @endphp <span>{{ $assigned }}</span></td>
                                <td class="p-2 text-center">@php $tagsText = implode(',', (array) $c->tags); @endphp <span>{{ $tagsText ?: '-' }}</span></td>
                                <td class="p-2 text-center">{{ $c->created_at?->format('Y-m-d') }}</td>
                                <td class="p-2 text-center">
                                    @php $isArchived = (($c->status ?? null) === 'archived' || !is_null($c->deleted_at ?? null)); @endphp
                                    @if($isArchived)
                                        <span class="inline-flex items-center gap-1 text-red-600" title="Archived">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.54-10.46a.75.75 0 00-1.06-1.06L10 8.94 7.52 6.48a.75.75 0 00-1.06 1.06L8.94 10l-2.48 2.48a.75.75 0 101.06 1.06L10 11.06l2.48 2.48a.75.75 0 101.06-1.06L11.06 10l2.48-2.46z" clip-rule="evenodd"/></svg>
                                            Archived
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-blue-600" title="Active">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-7.364 7.364a1 1 0 01-1.414 0L3.293 10.435a1 1 0 011.414-1.414l3.221 3.221 6.657-6.657a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            Active
                                        </span>
                                    @endif
                                </td>
                                <td class="p-2 text-center">
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-1 px-3 py-1 rounded-lg border border-blue-400 text-blue-600 hover:bg-blue-50 shadow-sm"
                                        data-id="{{ $c->id }}" data-name="{{ $c->name }}" data-company="{{ $c->company }}"
                                        data-email="{{ $c->email }}" data-phone="{{ $c->phone }}" data-assigned="{{ $c->assigned_user_id }}"
                                        data-status="{{ $c->status }}" data-tags="{{ implode(',', (array) $c->tags) }}" data-notes="{{ $c->notes }}"
                                        @click.prevent="
                                        editId=$el.dataset.id;
                                        editName=$el.dataset.name;
                                        editCompany=$el.dataset.company;
                                        editEmail=$el.dataset.email;
                                        editPhone=$el.dataset.phone;
                                        editAssigned=$el.dataset.assigned;
                                        editStatus=$el.dataset.status||'active';
                                        editTags=$el.dataset.tags||'';
                                        editNotes=$el.dataset.notes||'';
                                        showEdit=true;
                                        ">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-8.5 8.5a2 2 0 01-.878.515l-3.3.943a.5.5 0 01-.62-.62l.943-3.3a2 2 0 01.515-.878l8.5-8.5z"/></svg>Edit
                                    </button>
                                    <button type="button"class="inline-flex items-center gap-1 px-3 py-1 rounded-lg border border-red-400 text-red-600 hover:bg-red-50 shadow-sm"data-id="{{ $c->id }}" @click.prevent="editId=$el.dataset.id; showDelete=true;">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M6 8a1 1 0 011 1v6a1 1 0 102 0V9a1 1 0 112 0v6a1 1 0 102 0V9a1 1 0 011-1h1a1 1 0 100-2h-1V5a2 2 0 00-2-2H9a2 2 0 00-2 2v1H6a1 1 0 100 2h1zm3-3h2v1H9V5z" clip-rule="evenodd"/></svg>Del
                                    </button>
                                </td>

                            </tr>
                        @empty
                            <tr><td colspan="8" class="p-4 text-center text-gray-400">No contacts found</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- Bulk Delete Confirm Modal moved inside the form so selected checkboxes submit correctly -->
                <div x-show="showBulkDelete" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center">
                    <div class="absolute inset-0 bg-black/50" @click="showBulkDelete=false"></div>
                    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                        <div class="text-lg font-semibold mb-3">Delete Selected</div>
                        <p class="text-sm text-gray-600 mb-4">Are you sure you want to delete the selected contacts?</p>
                        <div class="flex justify-end gap-2">
                            <button type="button" class="px-4 py-2 rounded-lg border" @click="showBulkDelete=false">Cancel</button>
                            <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 text-white">Delete</button>
                        </div>
                    </div>
                </div>
                </form>
                <div class="flex flex-col md:flex-row items-center md:justify-between gap-3 p-3">
                    <div class="text-sm text-gray-600">
                        Showing <span class="font-medium">{{ $contacts->firstItem() ?? 0 }}</span>–<span class="font-medium">{{ $contacts->lastItem() ?? 0 }}</span> of <span class="font-medium">{{ $contacts->total() }}</span>
                    </div>
                    <div class="flex gap-1 items-center">
                        @if ($contacts->onFirstPage())
                            <span class="px-3 py-2 rounded-xl border text-gray-400 cursor-not-allowed">&laquo; Prev</span>
                        @else
                            <a href="{{ $contacts->previousPageUrl() }}" class="px-3 py-2 rounded-xl border hover:bg-white/40">&laquo; Prev</a>
                        @endif
                        @php
                            $current = $contacts->currentPage();
                            $last = $contacts->lastPage();
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
                                <a href="{{ $contacts->url($p) }}" class="px-3 py-2 rounded-xl border hover:bg-white/60">{{ $p }}</a>
                            @endif
                        @endforeach
                        @if ($contacts->hasMorePages())
                            <a href="{{ $contacts->nextPageUrl() }}" class="px-3 py-2 rounded-xl border hover:bg-white/40">Next &raquo;</a>
                        @else
                            <span class="px-3 py-2 rounded-xl border text-gray-400 cursor-not-allowed">Next &raquo;</span>
                        @endif
                    </div>
                    <form method="GET" class="flex items-center gap-2">
                        <label class="text-sm text-gray-600">Items per page</label>
                        <input type="number" name="per_page" min="1" max="100" value="{{ request('per_page', 10) }}" class="w-20 border rounded-xl px-3 py-2 bg-white/60 text-gray-700 shadow-inner">
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        <input type="hidden" name="company" value="{{ request('company') }}">
                        <input type="hidden" name="assigned_user_id" value="{{ request('assigned_user_id') }}">
                        <input type="hidden" name="status" value="{{ request('status') }}">
                        <input type="hidden" name="created_from" value="{{ request('created_from') }}">
                        <input type="hidden" name="created_to" value="{{ request('created_to') }}">
                        <input type="hidden" name="sort" value="{{ request('sort') }}">
                        <input type="hidden" name="direction" value="{{ request('direction') }}">
                        <button class="px-4 py-2 rounded-xl border hover:bg-white/70 bg-white/40 text-gray-700">Apply</button>
                        <a href="{{ route('crm.contacts.export', request()->query()) }}" class="px-4 py-2 rounded-xl border bg-green-100 hover:bg-green-200 text-green-700 ml-2">Export</a>
                        <a href="{{ route('crm.contacts.index') }}" class="px-4 py-2 rounded-xl border bg-gray-100 hover:bg-gray-200 text-gray-600 ml-2">Reset</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div x-show="showCreate" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/50" @click="showCreate=false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-xl p-6">
            <div class="text-lg font-semibold mb-4">Create Contact</div>
            <form method="POST" action="{{ route('crm.contacts.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf
                <input name="name" class="border rounded-lg px-3 py-2" placeholder="Name" required>
                <input name="company" class="border rounded-lg px-3 py-2" placeholder="Company">
                <input name="email" type="email" class="border rounded-lg px-3 py-2" placeholder="Email">
                <input name="phone" class="border rounded-lg px-3 py-2" placeholder="Phone">
                <input name="assigned_user_id" type="number" class="border rounded-lg px-3 py-2" placeholder="Assigned User ID">
                <select name="status" class="border rounded-lg px-3 py-2">
                    <option value="active">Active</option>
                    <option value="archived">Archived</option>
                </select>
                <input name="tags[]" class="border rounded-lg px-3 py-2 md:col-span-2" placeholder="Tags (comma separated)">
                <textarea name="notes" class="border rounded-lg px-3 py-2 md:col-span-2" rows="3" placeholder="Notes"></textarea>
                <div class="md:col-span-2 flex justify-end gap-2 mt-2">
                    <button type="button" class="px-4 py-2 rounded-lg border" @click="showCreate=false">Cancel</button>
                    <button class="px-4 py-2 rounded-lg bg-blue-600 text-white">Create</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="showEdit" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/50" @click="showEdit=false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-xl p-6">
            <div class="text-lg font-semibold mb-4">Edit Contact</div>
            <form :action="'{{ route('crm.contacts.update','__ID__') }}'.replace('__ID__', editId)" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf
                @method('PUT')
                <input name="name" class="border rounded-lg px-3 py-2" placeholder="Name" x-model="editName" required>
                <input name="company" class="border rounded-lg px-3 py-2" placeholder="Company" x-model="editCompany">
                <input name="email" type="email" class="border rounded-lg px-3 py-2" placeholder="Email" x-model="editEmail">
                <input name="phone" class="border rounded-lg px-3 py-2" placeholder="Phone" x-model="editPhone">
                <input name="assigned_user_id" type="number" class="border rounded-lg px-3 py-2" placeholder="Assigned User ID" x-model="editAssigned">
                <select name="status" class="border rounded-lg px-3 py-2" x-model="editStatus">
                    <option value="active">Active</option>
                    <option value="archived">Archived</option>
                </select>
                <input name="tags[]" class="border rounded-lg px-3 py-2 md:col-span-2" placeholder="Tags (comma separated)" x-model="editTags">
                <textarea name="notes" class="border rounded-lg px-3 py-2 md:col-span-2" rows="3" placeholder="Notes" x-model="editNotes"></textarea>
                <div class="md:col-span-2 flex justify-end gap-2 mt-2">
                    <button type="button" class="px-4 py-2 rounded-lg border" @click="showEdit=false">Cancel</button>
                    <button class="px-4 py-2 rounded-lg bg-blue-600 text-white">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirm Modal -->
    <div x-show="showDelete" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/50" @click="showDelete=false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <div class="text-lg font-semibold mb-3">Delete Contact</div>
            <p class="text-sm text-gray-600 mb-4">Are you sure you want to delete this contact?</p>
            <form :action="'{{ route('crm.contacts.destroy','__ID__') }}'.replace('__ID__', editId)" method="POST" class="flex justify-end gap-2">
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

