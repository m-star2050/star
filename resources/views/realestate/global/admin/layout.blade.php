@extends('realestate.global.layout', ['title' => $title ?? 'Afli Super Admin'])

@section('content')
<header class="bg-white shadow-sm">
    <div class="max-w-7xl mx-auto px-6 py-5 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-slate-900">Afli Real Estate Control Center</h1>
            <p class="text-sm text-slate-500">Manage tenants, plans, and global settings.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-sm text-slate-500">{{ session('global_admin.email') }}</span>
            <form action="{{ route('global.logout') }}" method="POST" class="ml-2">
                @csrf
                <button type="submit" class="btn-secondary">Sign out</button>
            </form>
        </div>
    </div>
</header>
<div class="flex-1">
    @yield('admin-content')
</div>
@endsection

