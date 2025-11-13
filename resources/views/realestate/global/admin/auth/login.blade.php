@extends('realestate.global.layout', ['title' => 'Super Admin Sign In'])

@section('content')
<main class="flex-1 flex items-center justify-center px-6 py-24">
    <section class="w-full max-w-md bg-white shadow-xl rounded-3xl p-8 space-y-6">
        <div class="space-y-1 text-center">
            <h1 class="text-2xl font-semibold text-slate-900">Super Admin Access</h1>
            <p class="text-sm text-slate-500">Enter the credentials configured for the SaaS control center.</p>
        </div>
        <form method="POST" action="{{ route('global.login.attempt') }}" class="space-y-5" id="global-login-form">
            @csrf
            <div class="space-y-2">
                <label for="email" class="text-sm font-medium text-slate-600">Email</label>
                <input id="email" name="email" type="email" required class="w-full rounded-2xl border border-slate-200 px-4 py-3 focus:border-slate-400 focus:outline-none" autofocus>
            </div>
            <div class="space-y-2">
                <label for="password" class="text-sm font-medium text-slate-600">Password</label>
                <input id="password" name="password" type="password" required class="w-full rounded-2xl border border-slate-200 px-4 py-3 focus:border-slate-400 focus:outline-none">
            </div>
            @if ($errors->any())
                <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
                    {{ $errors->first() }}
                </div>
            @endif
            <button type="submit" class="btn-primary w-full justify-center">Sign in</button>
        </form>
    </section>
</main>
@endsection

