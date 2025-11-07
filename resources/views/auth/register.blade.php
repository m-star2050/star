<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body style="background: url('{{ asset('image/Screenshot_16.png') }}') center center / cover no-repeat fixed; min-height: 100vh; font-family: 'Inter', sans-serif;" class="flex items-center justify-center">
    <div class="w-full max-w-md mx-4">
        <div class="glass rounded-2xl p-8 shadow-xl" style="background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.18);">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Create Account</h1>
            <p class="text-gray-700 mb-6">Sign up to access the CRM system</p>

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-500/20 border border-red-500/50 rounded-lg text-red-100">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ request()->routeIs('crm.register') ? route('crm.register') : route('register') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-800 mb-1">Full Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required autofocus
                               class="w-full border rounded-lg px-4 py-3 bg-white/60 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-800 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                               class="w-full border rounded-lg px-4 py-3 bg-white/60 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-800 mb-1">Password</label>
                        <input type="password" name="password" required minlength="8"
                               class="w-full border rounded-lg px-4 py-3 bg-white/60 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-600 mt-1">Minimum 8 characters</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-800 mb-1">Confirm Password</label>
                        <input type="password" name="password_confirmation" required minlength="8"
                               class="w-full border rounded-lg px-4 py-3 bg-white/60 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <button type="submit" class="w-full px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition">
                        Create Account
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center">
                <p class="text-gray-700 text-sm">
                    Already have an account? 
                    <a href="{{ request()->routeIs('crm.register') ? route('crm.login') : route('login') }}" class="text-blue-600 hover:text-blue-700 underline font-medium">Login here</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>

