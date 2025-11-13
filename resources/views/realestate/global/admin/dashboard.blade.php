@extends('realestate.global.admin.layout', ['title' => 'Control Center'])

@section('admin-content')
<main class="max-w-7xl mx-auto px-6 py-10 space-y-10">
    <section class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
        <div class="bg-white rounded-3xl p-6 shadow-sm">
            <p class="text-sm text-slate-500">Total Tenants</p>
            <p class="text-3xl font-semibold text-slate-900 mt-2">{{ $metrics['total'] }}</p>
        </div>
        <div class="bg-white rounded-3xl p-6 shadow-sm">
            <p class="text-sm text-slate-500">Active</p>
            <p class="text-3xl font-semibold text-emerald-600 mt-2">{{ $metrics['active'] }}</p>
        </div>
        <div class="bg-white rounded-3xl p-6 shadow-sm">
            <p class="text-sm text-slate-500">Suspended</p>
            <p class="text-3xl font-semibold text-amber-500 mt-2">{{ $metrics['suspended'] }}</p>
        </div>
        <div class="bg-white rounded-3xl p-6 shadow-sm">
            <p class="text-sm text-slate-500">Expired</p>
            <p class="text-3xl font-semibold text-rose-500 mt-2">{{ $metrics['expired'] }}</p>
        </div>
    </section>

    <section class="bg-white rounded-3xl shadow-sm p-6 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Tenant Directory</h2>
                <p class="text-sm text-slate-500">Manage onboarding, plans, billing windows, and status.</p>
            </div>
            <button class="btn-primary" id="open-create-tenant">New Tenant</button>
        </div>
        <table id="tenant-table" class="stripe hover w-full text-sm"></table>
    </section>

    <section class="grid gap-6 xl:grid-cols-2">
        <div class="bg-white rounded-3xl shadow-sm p-6 space-y-4">
            <h3 class="text-lg font-semibold text-slate-900">Plan Distribution</h3>
            <ul class="space-y-3">
                @forelse ($planBreakdown as $plan => $total)
                    <li class="flex items-center justify-between text-sm">
                        <span class="text-slate-500">{{ $plan }}</span>
                        <span class="font-semibold text-slate-900">{{ $total }}</span>
                    </li>
                @empty
                    <li class="text-sm text-slate-500">No tenants yet.</li>
                @endforelse
            </ul>
        </div>
        <div class="bg-white rounded-3xl shadow-sm p-6 space-y-4">
            <h3 class="text-lg font-semibold text-slate-900">Recent Activity</h3>
            <ul class="space-y-3">
                @forelse ($recentTenants as $tenant)
                    <li class="flex items-center justify-between text-sm">
                        <div class="space-y-0.5">
                            <p class="font-medium text-slate-900">{{ $tenant->name }}</p>
                            <p class="text-slate-500">{{ $tenant->created_at->diffForHumans() }}</p>
                        </div>
                        <span class="badge {{ $tenant->status === 'active' ? 'badge-success' : ($tenant->status === 'suspended' ? 'badge-warning' : 'badge-muted') }}">{{ ucfirst($tenant->status) }}</span>
                    </li>
                @empty
                    <li class="text-sm text-slate-500">No activity recorded.</li>
                @endforelse
            </ul>
        </div>
    </section>
</main>

<dialog id="tenant-create-modal">
    <form class="space-y-4" id="tenant-create-form">
        <h2 class="text-xl font-semibold text-slate-900">Create Tenant</h2>
        <div class="space-y-2">
            <label class="text-sm font-medium text-slate-600">Company Name</label>
            <input name="name" type="text" required class="w-full rounded-2xl border border-slate-200 px-4 py-3 focus:border-slate-400 focus:outline-none">
        </div>
        <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-2">
                <label class="text-sm font-medium text-slate-600">Plan</label>
                <select name="plan_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3 focus:border-slate-400 focus:outline-none">
                    <option value="">Unassigned</option>
                    @foreach ($plans as $plan)
                        <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-2">
                <label class="text-sm font-medium text-slate-600">Admin Email</label>
                <input name="email" type="email" class="w-full rounded-2xl border border-slate-200 px-4 py-3 focus:border-slate-400 focus:outline-none">
            </div>
        </div>
        <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-2">
                <label class="text-sm font-medium text-slate-600">Custom Slug</label>
                <input name="slug" type="text" class="w-full rounded-2xl border border-slate-200 px-4 py-3 focus:border-slate-400 focus:outline-none" placeholder="Optional">
            </div>
            <div class="space-y-2">
                <label class="text-sm font-medium text-slate-600">Custom Database</label>
                <input name="database" type="text" class="w-full rounded-2xl border border-slate-200 px-4 py-3 focus:border-slate-400 focus:outline-none" placeholder="Optional">
            </div>
        </div>
        <div class="flex items-center gap-3 justify-end pt-3">
            <button type="button" class="btn-secondary" data-close>Cancel</button>
            <button type="submit" class="btn-primary">Create</button>
        </div>
    </form>
</dialog>

<dialog id="tenant-edit-modal">
    <form class="space-y-4" id="tenant-edit-form">
        <h2 class="text-xl font-semibold text-slate-900">Update Tenant</h2>
        <input type="hidden" name="tenant_id">
        <div class="space-y-2">
            <label class="text-sm font-medium text-slate-600">Plan</label>
            <select name="plan_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3 focus:border-slate-400 focus:outline-none">
                <option value="">Unassigned</option>
                @foreach ($plans as $plan)
                    <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="space-y-2">
            <label class="text-sm font-medium text-slate-600">Expiry Date</label>
            <input name="expires_at" type="date" class="w-full rounded-2xl border border-slate-200 px-4 py-3 focus:border-slate-400 focus:outline-none">
        </div>
        <div class="flex items-center gap-3 justify-end pt-3">
            <button type="button" class="btn-secondary" data-close>Cancel</button>
            <button type="submit" class="btn-primary">Save</button>
        </div>
    </form>
</dialog>

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const createModal = document.getElementById('tenant-create-modal');
    const editModal = document.getElementById('tenant-edit-modal');
    const tenantTable = $('#tenant-table').DataTable({
        ajax: {
            url: '{{ route('global.tenants.list') }}',
            dataSrc: 'data'
        },
        columns: [
            { title: 'Company', data: 'name' },
            { title: 'Slug', data: 'slug' },
            { title: 'Plan', data: 'plan', defaultContent: 'Unassigned' },
            { title: 'Status', data: 'status', render: data => {
                if (data === 'active') return '<span class="badge badge-success">Active</span>';
                if (data === 'suspended') return '<span class="badge badge-warning">Suspended</span>';
                return '<span class="badge badge-muted">' + data.charAt(0).toUpperCase() + data.slice(1) + '</span>';
            }},
            { title: 'Expires', data: 'expires_at', defaultContent: '' },
            { title: 'Created', data: 'created_at' },
            { title: '', data: null, orderable: false, render: row => {
                return `
                    <div class="flex items-center gap-2 justify-end">
                        <button class="btn-secondary text-xs" data-action="edit" data-id="${row.id}">Edit</button>
                        <button class="btn-secondary text-xs" data-action="status" data-status="${row.status === 'suspended' ? 'active' : 'suspended'}" data-id="${row.id}">${row.status === 'suspended' ? 'Activate' : 'Suspend'}</button>
                        <button class="btn-secondary text-xs text-rose-600" data-action="delete" data-id="${row.id}">Delete</button>
                    </div>
                `;
            }}
        ],
        order: [[5, 'desc']]
    });

    function closeDialog(dialog) {
        dialog.close();
    }

    function openDialog(dialog) {
        dialog.showModal();
    }

    $('#open-create-tenant').on('click', () => openDialog(createModal));

    $('[data-close]').on('click', function () {
        closeDialog(this.closest('dialog'));
    });

    $('#tenant-create-form').on('submit', async function (event) {
        event.preventDefault();
        const formData = new FormData(this);
        const response = await fetch('{{ route('global.tenants.store') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: formData
        });
        if (response.ok) {
            tenantTable.ajax.reload(null, false);
            this.reset();
            closeDialog(createModal);
        } else {
            alert('Unable to create tenant. Check input fields.');
        }
    });

    $('#tenant-table').on('click', 'button[data-action="edit"]', function () {
        const id = this.getAttribute('data-id');
        const row = tenantTable.rows().data().toArray().find(item => String(item.id) === String(id));
        if (!row) return;
        const form = document.getElementById('tenant-edit-form');
        form.tenant_id.value = row.id;
        form.plan_id.value = row.plan_id || '';
        form.expires_at.value = row.expires_at || '';
        openDialog(editModal);
    });

    $('#tenant-edit-form').on('submit', async function (event) {
        event.preventDefault();
        const formData = new FormData(this);
        const id = formData.get('tenant_id');
        const payload = new URLSearchParams();
        payload.append('plan_id', formData.get('plan_id') || '');
        payload.append('expires_at', formData.get('expires_at') || '');
        const response = await fetch(`{{ url('/realestate/global/admin/api/tenants') }}/${id}`, {
            method: 'PUT',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded' },
            body: payload
        });
        if (response.ok) {
            tenantTable.ajax.reload(null, false);
            closeDialog(editModal);
        } else {
            alert('Unable to update tenant.');
        }
    });

    $('#tenant-table').on('click', 'button[data-action="status"]', async function () {
        const id = this.getAttribute('data-id');
        const status = this.getAttribute('data-status');
        const payload = new URLSearchParams();
        payload.append('status', status);
        const response = await fetch(`{{ url('/realestate/global/admin/api/tenants') }}/${id}/status`, {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded' },
            body: payload
        });
        if (response.ok) {
            tenantTable.ajax.reload(null, false);
        } else {
            alert('Unable to update status.');
        }
    });

    $('#tenant-table').on('click', 'button[data-action="delete"]', async function () {
        const id = this.getAttribute('data-id');
        if (!confirm('Delete tenant and remove its data?')) return;
        const response = await fetch(`{{ url('/realestate/global/admin/api/tenants') }}/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        });
        if (response.ok) {
            tenantTable.ajax.reload(null, false);
        } else {
            alert('Unable to delete tenant.');
        }
    });
</script>
@endpush
@endsection

