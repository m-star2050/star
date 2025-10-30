@php
    $qs = request()->query();
    function sort_link($key, $label) {
        $current = request('sort');
        $direction = request('direction','desc') === 'asc' ? 'desc' : 'asc';
        $params = array_merge(request()->query(), ['sort' => $key, 'direction' => ($current === $key ? $direction : 'asc')]);
        $url = request()->url().'?'.http_build_query($params);
        return '<a href="'.$url.'" class="text-blue-600 hover:underline">'.$label.'</a>';
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
    <style>body{background:#f3f4f6}</style>
    
</head>
<body>
<!-- Centered, large, stylish Contact heading with margin from top -->
<div class="mt-7 mb-6 flex justify-center items-center">
    <h1 style="font-family: 'Segoe Script', 'Brush Script MT', cursive; font-size: 3rem; font-weight: bold; color: #2563eb; transform: skew(-7deg, -3deg); letter-spacing: 0.09em; line-height: 1.1;">Contact</h1>
</div>
<div x-data="{showCreate:false, showEdit:false, showDelete:false,
    editId:null, editName:'', editCompany:'', editEmail:'', editPhone:'', editAssigned:'', editStatus:'active', editTags:'', editNotes:'', deleteId:null}"
    class="max-w-6xl mx-auto p-6 space-y-6">
    @if(session('status'))
        <div class="bg-green-100 text-green-700 px-4 py-2 rounded">{{ session('status') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow">
    <div class="px-4 pt-4">
        <div class="flex flex-wrap items-center">
            <button type="button" @click="showCreate=true" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"/></svg>
                New Contact
            </button>
            <div class="flex-grow"></div>
            <div class="flex items-center gap-3 ml-auto">
                <span class="mr-2 text-gray-700 font-medium text-base">Search</span>
                <form method="GET" class="relative flex items-center w-64">
                    <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 103 10.5a7.5 7.5 0 0013.15 6.15z" /></svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" class="pl-9 pr-3 py-2 border rounded w-full focus:ring-2 focus:ring-blue-300 outline-none" style="height:2.55rem;" autocomplete="off">
                </form>
            </div>
        </div>
        <p class="mt-2 text-sm text-gray-500"></p>
        <div class="mt-4 mb-2 ml-1">
            </div>
        </div>
    <form method="GET" class="relative grid grid-cols-1 md:grid-cols-5 gap-4 items-end p-4 border-t">
        <div>
            <label class="block text-sm">Company</label>
            <input type="text" name="company" value="{{ request('company') }}" class="w-full border rounded px-3 py-2" autocomplete="off">
        </div>
        <div>
            <label class="block text-sm">Assigned User</label>
            <input type="number" name="assigned_user_id" value="{{ request('assigned_user_id') }}" class="w-full border rounded px-3 py-2" autocomplete="off">
        </div>
        <div>
            <label class="block text-sm">Status</label>
            <select name="status" class="w-full border rounded px-3 py-2">
                <option value="">Any</option>
                <option value="active" @selected(request('status')==='active')>Active</option>
                <option value="archived" @selected(request('status')==='archived')>Archived</option>
            </select>
        </div>
        <div>
            <label class="block text-sm">Created From</label>
            <input type="date" name="created_from" value="{{ request('created_from') }}" class="w-full border rounded px-3 py-2" autocomplete="off">
        </div>
        <div>
            <label class="block text-sm">To</label>
            <input type="date" name="created_to" value="{{ request('created_to') }}" class="w-full border rounded px-3 py-2" autocomplete="off">
        </div>
    </form>
    </div>

    <form method="POST" action="{{ route('crm.contacts.bulk-delete') }}" class="bg-white rounded-lg shadow">
        @csrf
        <div class="p-3 flex flex-wrap items-end gap-2 border-b relative">
            <button class="px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded" onclick="return confirm('Delete selected?')">Archive Selected</button>
            <span class="ml-4 text-sm text-gray-500 self-end pb-0.5">Click the 'Export' button to download to Excel.</span>
            <button type="submit" formaction="#" class="ml-auto px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded">Filter</button>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 sticky top-0">
                    <tr>
                        <th class="p-2 text-center"><input type="checkbox" onclick="document.querySelectorAll('.row-check').forEach(cb=>cb.checked=this.checked)"></th>
                        <th class="p-2 text-center">{!! sort_link('name','Name') !!}</th>
                        <th class="p-2 text-center">{!! sort_link('company','Company') !!}</th>
                        <th class="p-2 text-center">{!! sort_link('email','Email') !!}</th>
                        <th class="p-2 text-center">{!! sort_link('phone','Phone') !!}</th>
                        <th class="p-2 text-center">Assigned</th>
                        <th class="p-2 text-center">Tags</th>
                        <th class="p-2 text-center">{!! sort_link('created_at','Created') !!}</th>
                        <th class="p-2 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contacts as $c)
                        @php
                            $payload = [
                                'id' => $c->id,
                                'name' => $c->name,
                                'company' => $c->company,
                                'email' => $c->email,
                                'phone' => $c->phone,
                                'assigned_user_id' => $c->assigned_user_id,
                                'status' => $c->status,
                                'tags' => implode(',', (array) $c->tags),
                                'notes' => $c->notes,
                            ];
                        @endphp
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-2 text-center"><input class="row-check" type="checkbox" name="ids[]" value="{{ $c->id }}"></td>
                            <td class="p-2 text-center">{{ $c->name }}</td>
                            <td class="p-2 text-center">{{ $c->company }}</td>
                            <td class="p-2 text-center">{{ $c->email }}</td>
                            <td class="p-2 text-center">{{ $c->phone }}</td>
                            <td class="p-2 text-center">
                                @php $assigned = $c->assigned_user_id ? ('User '.$c->assigned_user_id) : '-'; @endphp
                                <span class="inline-block px-2 py-1">{{ $assigned }}</span>
                            </td>
                            <td class="p-2 text-center">
                                @php $tagsText = implode(',', (array) $c->tags); @endphp
                                <span class="inline-block px-2 py-1">{{ $tagsText ?: '-' }}</span>
                            </td>
                            <td class="p-2 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-700">{{ $c->created_at?->format('Y-m-d') }}</span>
                            </td>
                            <td class="p-2 text-center">
                                <div class="inline-flex gap-2 items-center">
                                    <button type="button"
                                        class="px-3 py-1 rounded border border-blue-600 text-blue-600 hover:bg-blue-50"
                                        title="Edit"
                                        data-id="{{ $c->id }}"
                                        data-name="{{ $c->name }}"
                                        data-company="{{ $c->company }}"
                                        data-email="{{ $c->email }}"
                                        data-phone="{{ $c->phone }}"
                                        data-assigned="{{ $c->assigned_user_id }}"
                                        data-status="{{ $c->status }}"
                                        data-tags="{{ implode(',', (array) $c->tags) }}"
                                        data-notes="{{ $c->notes }}"
                                        @click.prevent="editId=$el.dataset.id; editName=$el.dataset.name; editCompany=$el.dataset.company; editEmail=$el.dataset.email; editPhone=$el.dataset.phone; editAssigned=$el.dataset.assigned; editStatus=$el.dataset.status||'active'; editTags=$el.dataset.tags||''; editNotes=$el.dataset.notes||''; showEdit=true;">
                                        Edit
                                    </button>
                                    <button type="button"
                                        class="px-3 py-1 rounded border border-red-600 text-red-600 hover:bg-red-50"
                                        title="Delete"
                                        data-id="{{ $c->id }}"
                                        @click.prevent="deleteId=$el.dataset.id; showDelete=true;">
                                        Del
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="p-4 text-center text-gray-500">No contacts found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </form>

    <div class="p-4 border-t bg-white rounded-lg shadow">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-600">
                    @php
                        $from = $contacts->firstItem() ?? 0;
                        $to = $contacts->lastItem() ?? 0;
                        $total = $contacts->total();
                    @endphp
                    Showing <span class="font-medium">{{ $from }}</span>–<span class="font-medium">{{ $to }}</span> of <span class="font-medium">{{ $total }}</span>
                </div>

                <nav class="inline-flex items-center gap-1" aria-label="Pagination">
                    @php
                        $current = $contacts->currentPage();
                        $last = $contacts->lastPage();
                        $window = 2; // how many pages around current
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

                    @if ($contacts->onFirstPage())
                        <span class="px-3 py-2 rounded border text-gray-400 cursor-not-allowed">&laquo; Prev</span>
                    @else
                        <a href="{{ $contacts->previousPageUrl() }}" class="px-3 py-2 rounded border hover:bg-gray-50">&laquo; Prev</a>
                    @endif

                    @foreach ($pages as $p)
                        @if ($p === '...')
                            <span class="px-3 py-2 text-gray-500 select-none">...</span>
                        @elseif ($p == $current)
                            <span class="px-3 py-2 rounded border bg-blue-600 text-white">{{ $p }}</span>
                        @else
                            <a href="{{ $contacts->url($p) }}" class="px-3 py-2 rounded border hover:bg-gray-50">{{ $p }}</a>
                        @endif
                    @endforeach

                    @if ($contacts->hasMorePages())
                        <a href="{{ $contacts->nextPageUrl() }}" class="px-3 py-2 rounded border hover:bg-gray-50">Next &raquo;</a>
                    @else
                        <span class="px-3 py-2 rounded border text-gray-400 cursor-not-allowed">Next &raquo;</span>
                    @endif
                </nav>

                <form method="GET" class="flex items-center gap-2">
                    <label class="text-sm text-gray-600">Items per page</label>
                    <input type="number" name="per_page" min="1" max="100" value="{{ request('per_page', 10) }}" class="w-24 border rounded px-3 py-2">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="company" value="{{ request('company') }}">
                    <input type="hidden" name="assigned_user_id" value="{{ request('assigned_user_id') }}">
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    <input type="hidden" name="created_from" value="{{ request('created_from') }}">
                    <input type="hidden" name="created_to" value="{{ request('created_to') }}">
                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                    <input type="hidden" name="direction" value="{{ request('direction') }}">
                    <button class="px-3 py-2 rounded border hover:bg-gray-50">Apply</button>
                    <a href="{{ route('crm.contacts.export', request()->query()) }}" class="px-3 py-2 rounded border hover:bg-gray-50">Export</a>
                    <a href="{{ route('crm.contacts.index') }}" class="px-3 py-2 rounded border hover:bg-gray-50">Reset</a>
                </form>
            </div>
        </div>

    <!-- Create Contact Modal -->
    <div x-show="showCreate" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center">
        <div @click="showCreate=false" class="absolute inset-0 bg-black/40"></div>
        <div @keydown.escape.window="showCreate=false" x-trap.noscroll.inert="showCreate" class="relative bg-white w-full max-w-3xl rounded-lg shadow-xl">
            <div class="flex items-center justify-between border-b px-5 py-3">
                <h3 class="font-semibold text-lg">Add Contact</h3>
                <button class="text-gray-500 hover:text-gray-700" @click="showCreate=false" aria-label="Close">✕</button>
            </div>
            <form method="POST" action="{{ route('crm.contacts.store') }}" class="p-5 grid md:grid-cols-3 gap-3">
                @csrf
                <input name="name" class="border rounded px-3 py-2" placeholder="Name" required>
                <input name="company" class="border rounded px-3 py-2" placeholder="Company">
                <input name="email" type="email" class="border rounded px-3 py-2" placeholder="Email">
                <input name="phone" class="border rounded px-3 py-2" placeholder="Phone">
                <input name="assigned_user_id" type="number" class="border rounded px-3 py-2" placeholder="Assigned User ID">
                <select name="status" class="border rounded px-3 py-2">
                    <option value="active">Active</option>
                    <option value="archived">Archived</option>
                </select>
                <input name="tags[]" class="border rounded px-3 py-2" placeholder="Tag (optional)">
                <textarea name="notes" class="border rounded px-3 py-2 md:col-span-3" placeholder="Notes"></textarea>
                <div class="md:col-span-3 flex justify-end gap-2 pt-2">
                    <button type="button" class="px-4 py-2 rounded border" @click="showCreate=false">Cancel</button>
                    <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Create</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Contact Modal -->
    <div x-show="showEdit" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center">
        <div @click="showEdit=false" class="absolute inset-0 bg-black/40"></div>
        <div @keydown.escape.window="showEdit=false" x-trap.noscroll.inert="showEdit" class="relative bg-white w-full max-w-3xl rounded-lg shadow-xl">
            <div class="flex items-center justify-between border-b px-5 py-3">
                <h3 class="font-semibold text-lg">Edit Contact</h3>
                <button class="text-gray-500 hover:text-gray-700" @click="showEdit=false" aria-label="Close">✕</button>
            </div>
            <form x-bind:action="'{{ route('crm.contacts.update','__ID__') }}'.replace('__ID__', editId)" method="POST" class="p-5 grid md:grid-cols-3 gap-3">
                @csrf
                @method('PUT')
                <input x-model="editName" name="name" class="border rounded px-3 py-2" placeholder="Name" required>
                <input x-model="editCompany" name="company" class="border rounded px-3 py-2" placeholder="Company">
                <input x-model="editEmail" name="email" type="email" class="border rounded px-3 py-2" placeholder="Email">
                <input x-model="editPhone" name="phone" class="border rounded px-3 py-2" placeholder="Phone">
                <input x-model="editAssigned" name="assigned_user_id" type="number" class="border rounded px-3 py-2" placeholder="Assigned User ID">
                <select x-model="editStatus" name="status" class="border rounded px-3 py-2">
                    <option value="active">Active</option>
                    <option value="archived">Archived</option>
                </select>
                <input x-model="editTags" name="tags[]" class="border rounded px-3 py-2" placeholder="Tag (optional)">
                <textarea x-model="editNotes" name="notes" class="border rounded px-3 py-2 md:col-span-3" placeholder="Notes"></textarea>
                <div class="md:col-span-3 flex justify-end gap-2 pt-2">
                    <button type="button" class="px-4 py-2 rounded border" @click="showEdit=false">Cancel</button>
                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Contact Modal -->
    <div x-show="showDelete" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center">
        <div @click="showDelete=false" class="absolute inset-0 bg-black/40"></div>
        <div @keydown.escape.window="showDelete=false" x-trap.noscroll.inert="showDelete" class="relative bg-white w-full max-w-md rounded-lg shadow-xl">
            <div class="px-5 py-4 border-b">
                <h3 class="font-semibold">Delete Contact</h3>
            </div>
            <div class="px-5 py-4 text-sm text-gray-600">
                Are you sure you want to delete this contact? This action can be reverted if soft delete is enabled.
            </div>
            <div class="px-5 py-4 border-t flex justify-end gap-2">
                <button type="button" class="px-4 py-2 rounded border" @click="showDelete=false">Cancel</button>
                <form x-bind:action="'{{ route('crm.contacts.destroy','__ID__') }}'.replace('__ID__', deleteId)" method="POST">
                    @csrf
                    @method('DELETE')
                    <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">Delete</button>
                </form>
            </div>
        </div>
    </div>
    
</div>
</body>
</html>

