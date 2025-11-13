<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Afli Real Estate' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=inter:400,500,600,700">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.1/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <style>
        body{font-family:"Inter",sans-serif;}
        .btn-primary{background:#2563eb;color:#fff;border-radius:0.75rem;padding:0.65rem 1.25rem;font-weight:600;transition:all .2s;}
        .btn-primary:hover{background:#1d4ed8;}
        .btn-secondary{background:#f1f5f9;color:#0f172a;border-radius:0.75rem;padding:0.6rem 1.2rem;font-weight:600;transition:all .2s;}
        .btn-secondary:hover{background:#e2e8f0;}
        .badge{display:inline-flex;align-items:center;border-radius:9999px;padding:0.35rem 0.75rem;font-size:0.75rem;font-weight:600;}
        .badge-success{background:#dcfce7;color:#15803d;}
        .badge-warning{background:#fef3c7;color:#b45309;}
        .badge-muted{background:#e2e8f0;color:#1e293b;}
        dialog::backdrop{background:rgba(15,23,42,0.45);}
        dialog{border:none;border-radius:1rem;padding:2rem;max-width:480px;width:100%;}
    </style>
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-900 antialiased min-h-screen">
    <div id="app" class="min-h-screen flex flex-col">
        @yield('content')
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    @stack('scripts')
</body>
</html>

