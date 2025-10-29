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

<div x-data class="p-6 space-y-4">
    @if(session('status'))
        <div class="bg-green-100 text-green-700 px-4 py-2 rounded">{{ session('status') }}</div>
    @endif

    <form method="GET" class="grid md:grid-cols-6 gap-3 items-end bg-white p-4 rounded shadow">
        <div class="col-span-2">
            <label class="block text-sm">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" class="w-full border rounded px-3 py-2" placeholder="name, email, phone">
        </div>
        <div>
            <label class="block text-sm">Company</label>
            <input type="text" name="company" value="{{ request('company') }}" class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm">Assigned User</label>
            <input type="number" name="assigned_user_id" value="{{ request('assigned_user_id') }}" class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm">Status</label>
            <select name="status" class="w-full border rounded px-3 py-2">
                <option value="">Any</option>
                <option value="active" @selected(request('status')==='active')>Active</option>
                <option value="archived" @selected(request('status')==='archived')>Archived</option>
            </select>
        </div>
        <div class="flex gap-2">
            <div>
                <label class="block text-sm">Created From</label>
                <input type="date" name="created_from" value="{{ request('created_from') }}" class="border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm">To</label>
                <input type="date" name="created_to" value="{{ request('created_to') }}" class="border rounded px-3 py-2">
            </div>
        </div>
        <div>
            <label class="block text-sm">Per Page</label>
            <select name="per_page" class="w-full border rounded px-3 py-2" onchange="this.form.submit()">
                @foreach([10,25,50,100] as $n)
                    <option value="{{ $n }}" @selected(request('per_page',10)==$n)>{{ $n }}</option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-6 flex gap-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button>
            <a href="{{ route('crm.contacts.index') }}" class="px-4 py-2 rounded border">Reset</a>
            <a href="{{ route('crm.contacts.export', request()->query()) }}" class="px-4 py-2 rounded border">Export</a>
        </div>
    </form>

    <form method="POST" action="{{ route('crm.contacts.bulk-delete') }}" class="bg-white rounded shadow">
        @csrf
        <div class="p-2 flex gap-2">
            <button class="px-3 py-2 bg-red-600 text-white rounded" onclick="return confirm('Delete selected?')">Delete Selected</button>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-2"><input type="checkbox" onclick="document.querySelectorAll('.row-check').forEach(cb=>cb.checked=this.checked)"></th>
                        <th class="p-2">{!! sort_link('name','Name') !!}</th>
                        <th class="p-2">{!! sort_link('company','Company') !!}</th>
                        <th class="p-2">{!! sort_link('email','Email') !!}</th>
                        <th class="p-2">{!! sort_link('phone','Phone') !!}</th>
                        <th class="p-2">Assigned User</th>
                        <th class="p-2">Tags</th>
                        <th class="p-2">{!! sort_link('created_at','Created') !!}</th>
                        <th class="p-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contacts as $c)
                        <tr class="border-b">
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
                            <td class="p-2">{{ $c->created_at?->format('Y-m-d') }}</td>
                            <td class="p-2">
                                <form method="POST" action="{{ route('crm.contacts.destroy', $c) }}" onsubmit="return confirm('Delete this contact?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="p-4 text-center text-gray-500">No contacts found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $contacts->links() }}</div>
    </form>

    <div class="bg-white rounded shadow p-4">
        <h3 class="font-semibold mb-2">Add Contact</h3>
        <form method="POST" action="{{ route('crm.contacts.store') }}" class="grid md:grid-cols-3 gap-3">
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
            <div class="md:col-span-3">
                <button class="bg-green-600 text-white px-4 py-2 rounded">Create</button>
            </div>
        </form>
    </div>
</div>

<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

