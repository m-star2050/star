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
<div x-data="{showCreate:false}" class="max-w-6xl mx-auto p-6 space-y-6">
    @if(session('status'))
        <div class="bg-green-100 text-green-700 px-4 py-2 rounded">{{ session('status') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow">
    <div class="px-4 pt-4 flex items-end justify-between">
        <div>
            <h2 class="text-lg font-semibold">Contacts</h2>
            <p class="text-sm text-gray-500">Search, filter and manage your contacts</p>
        </div>
        <button type="button" @click="showCreate=true" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"/></svg>
            New Contact
        </button>
    </div>
    <form method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end p-4 border-t">
        <div class="md:col-span-3">
            <label class="block text-sm">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" class="w-full border rounded px-3 py-2" placeholder="name, email, phone">
        </div>
        <div class="md:col-span-3">
            <label class="block text-sm">Company</label>
            <input type="text" name="company" value="{{ request('company') }}" class="w-full border rounded px-3 py-2">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm">Assigned User</label>
            <input type="number" name="assigned_user_id" value="{{ request('assigned_user_id') }}" class="w-full border rounded px-3 py-2">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm">Status</label>
            <select name="status" class="w-full border rounded px-3 py-2">
                <option value="">Any</option>
                <option value="active" @selected(request('status')==='active')>Active</option>
                <option value="archived" @selected(request('status')==='archived')>Archived</option>
            </select>
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm">Per Page</label>
            <select name="per_page" class="w-full border rounded px-3 py-2" onchange="this.form.submit()">
                @foreach([10,25,50,100] as $n)
                    <option value="{{ $n }}" @selected(request('per_page',10)==$n)>{{ $n }}</option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-3">
            <label class="block text-sm">Created From</label>
            <input type="date" name="created_from" value="{{ request('created_from') }}" class="w-full border rounded px-3 py-2">
        </div>
        <div class="md:col-span-3">
            <label class="block text-sm">To</label>
            <input type="date" name="created_to" value="{{ request('created_to') }}" class="w-full border rounded px-3 py-2">
        </div>
        <div class="md:col-span-12 flex flex-wrap gap-2">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Filter</button>
            <a href="{{ route('crm.contacts.index') }}" class="px-4 py-2 rounded border hover:bg-gray-50">Reset</a>
            <a href="{{ route('crm.contacts.export', request()->query()) }}" class="px-4 py-2 rounded border hover:bg-gray-50">Export</a>
        </div>
    </form>
    </div>

    <form method="POST" action="{{ route('crm.contacts.bulk-delete') }}" class="bg-white rounded-lg shadow">
        @csrf
        <div class="p-3 flex flex-wrap items-center gap-2 border-b">
            <button class="px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded" onclick="return confirm('Delete selected?')">Archive Selected</button>
            <div class="text-sm text-gray-500">Tip: use the header checkbox to select all on page</div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 sticky top-0">
                    <tr>
                        <th class="p-2"><input type="checkbox" onclick="document.querySelectorAll('.row-check').forEach(cb=>cb.checked=this.checked)"></th>
                        <th class="p-2">{!! sort_link('name','Name') !!}</th>
                        <th class="p-2">{!! sort_link('company','Company') !!}</th>
                        <th class="p-2">{!! sort_link('email','Email') !!}</th>
                        <th class="p-2">{!! sort_link('phone','Phone') !!}</th>
                        <th class="p-2">Assigned</th>
                        <th class="p-2">Tags</th>
                        <th class="p-2">{!! sort_link('created_at','Created') !!}</th>
                        <th class="p-2 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contacts as $c)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-2"><input class="row-check" type="checkbox" name="ids[]" value="{{ $c->id }}"></td>
                            <td class="p-2">{{ $c->name }}</td>
                            <td class="p-2">{{ $c->company }}</td>
                            <td class="p-2">{{ $c->email }}</td>
                            <td class="p-2">{{ $c->phone }}</td>
                            <td class="p-2">
                                <form x-data @change="fetch('{{ route('crm.contacts.inline', $c) }}', {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}, body: new URLSearchParams({field:'assigned_user_id', value: $event.target.value})})">
                                    <select class="border rounded px-2 py-1">
                                        <option value="">-</option>
                                        @for($i=1;$i<=5;$i++)
                                            <option value="{{ $i }}" @selected($c->assigned_user_id==$i)>User {{ $i }}</option>
                                        @endfor
                                    </select>
                                </form>
                            </td>
                            <td class="p-2">
                                <form x-data @submit.prevent="fetch('{{ route('crm.contacts.inline', $c) }}', {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}, body: new URLSearchParams({field:'tags', value: $refs.t.value})}).then(()=>$refs.t.blur())">
                                    <input x-ref="t" class="border rounded px-2 py-1 w-40" value="{{ implode(',', (array) $c->tags) }}" placeholder="tag1,tag2" @change="$el.form.dispatchEvent(new Event('submit'))">
                                </form>
                            </td>
                            <td class="p-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-700">{{ $c->created_at?->format('Y-m-d') }}</span>
                            </td>
                            <td class="p-2 text-right">
                                <div class="inline-flex gap-2">
                                    @if($c->deleted_at)
                                        <form method="POST" action="{{ route('crm.contacts.restore', $c->id) }}">
                                            @csrf
                                            <button class="text-green-600 hover:underline">Restore</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('crm.contacts.destroy', $c) }}" onsubmit="return confirm('Archive this contact?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-red-600 hover:underline">Archive</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="p-4 text-center text-gray-500">No contacts found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t">
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
                </form>
            </div>
        </div>
    </form>

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
    
</div>
</body>
</html>

