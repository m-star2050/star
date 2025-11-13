@extends('realestate.global.layout', ['title' => $tenant->name . ' Portal'])

@section('content')
<main class="min-h-screen flex items-center justify-center px-6 py-16">
    <section class="max-w-2xl w-full text-center space-y-6">
        <h1 class="text-3xl font-semibold tracking-tight">{{ $tenant->name }} Real Estate Portal</h1>
        <p class="text-slate-600">Tenant environment is active. Continue building dashboards and modules for this company.</p>
        <dl class="grid grid-cols-1 gap-4 text-left sm:grid-cols-3">
            <div class="rounded-xl border border-slate-200 p-4">
                <dt class="text-xs uppercase text-slate-400">Tenant Code</dt>
                <dd class="text-base font-medium text-slate-800">{{ $tenant->code }}</dd>
            </div>
            <div class="rounded-xl border border-slate-200 p-4">
                <dt class="text-xs uppercase text-slate-400">Database</dt>
                <dd class="text-base font-medium text-slate-800 break-all">{{ $tenant->database }}</dd>
            </div>
            <div class="rounded-xl border border-slate-200 p-4">
                <dt class="text-xs uppercase text-slate-400">Status</dt>
                <dd class="text-base font-medium text-emerald-600">{{ ucfirst($tenant->status) }}</dd>
            </div>
        </dl>
    </section>
</main>
@endsection

