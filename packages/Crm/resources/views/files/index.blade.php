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
    <title>CRM Files</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <style>
        body {
            background:
                url('{{ asset('image/Screenshot_16.png') }}') center center/cover no-repeat fixed !important;
            min-height: 100vh;
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
        }
        .glass { 
            backdrop-filter: blur(16px); 
            -webkit-backdrop-filter: blur(16px); 
            background: rgba(255,255,255,0.2); 
            border: 1px solid rgba(255,255,255,0.2); 
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.1), inset 0 1px 0 0 rgba(255,255,255,0.2); 
        }
        .glass-card {
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            background: rgba(255,255,255,0.25);
            border: 1px solid rgba(255,255,255,0.25);
            box-shadow: 0 10px 40px rgba(0,0,0,0.08), 0 2px 8px rgba(0,0,0,0.05);
        }
        .sidebar-link{ 
            display:flex; 
            align-items:center; 
            gap:.875rem; 
            color:#1f2937; 
            text-decoration:none; 
            padding:.75rem 1rem; 
            border-radius:.75rem; 
            line-height:1; 
            font-weight:500;
            font-size:0.875rem;
            transition: all 0.2s ease;
            position: relative;
            overflow: visible;
            white-space: nowrap;
        }
        .sidebar-link:hover{ 
            background: rgba(255,255,255,0.25);
            color: #111827;
        }
        .sidebar-link:not(.justify-center):hover {
            transform: translateX(4px);
        }
        .sidebar-link svg{ 
            width:20px; 
            height:20px; 
            min-width:20px; 
            min-height:20px; 
            max-width:20px;
            max-height:20px;
            flex-shrink:0; 
            display:block;
            transition: all 0.2s ease;
            overflow: visible;
        }
        .sidebar-link:hover svg {
            transform: scale(1.1);
        }
        .sidebar-link span{ 
            line-height:1.2; 
            display:flex; 
            align-items:center;
            font-weight: 500;
        }
        .sidebar-link.active {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.15), rgba(59, 130, 246, 0.1));
            color: #2563eb;
            font-weight: 600;
        }
        .sidebar-link.active:not(.justify-center) {
            border-left: 3px solid #2563eb;
            padding-left: calc(1rem - 3px);
        }
        .sidebar-link.active svg {
            color: #2563eb;
        }
        .sidebar-section-title {
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #6b7280;
            padding: 0.5rem 1rem;
            margin-top: 0.5rem;
            margin-bottom: 0.25rem;
        }
        .hdr-wrap{max-width:1120px}
        
        .dataTables_wrapper { font-family: inherit; }
        .dataTables_filter { margin: 0; }
        .dataTables_length { margin: 0; }
        .dataTables_length label { 
            font-size: 0.875rem !important;
            color: #374151 !important;
        }
        .dataTables_length select { 
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 0.75rem;
            padding: 0.5rem 2.5rem 0.5rem 0.875rem;
            background: rgba(255,255,255,0.6);
            color: #374151;
            font-size: 0.875rem;
            font-weight: 500;
            appearance: none;
            min-height: 2.25rem;
        }
        .dataTables_info { 
            margin: 0; 
            color: #6b7280;
            font-size: 0.875rem;
            font-weight: 500;
            margin-left: 1rem;
        }
        @media (max-width: 485px) {
            #datatableLengthContainer {
                justify-content: center !important;
                width: 100%;
            }
            #datatableLengthContainer .dataTables_length,
            #datatableLengthContainer .dataTables_info {
                margin-left: 0 !important;
            }
        }
        .dataTables_paginate { margin: 0; }
        .dataTables_paginate .paginate_button {
            padding: 0.5rem 0.875rem;
            border-radius: 0.75rem;
            border: 1px solid rgba(0,0,0,0.1);
            margin: 0 0.125rem;
            cursor: pointer;
            background: rgba(255,255,255,0.6);
            color: #374151;
            font-size: 0.875rem;
            font-weight: 500;
            min-height: 2.25rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .dataTables_paginate .paginate_button:hover:not(.disabled):not(.current) {
            background: rgba(255,255,255,0.8);
        }
        .dataTables_paginate .paginate_button.current {
            background: #2563eb;
            color: white;
            border-color: #2563eb;
            font-weight: 600;
        }
        .dataTables_paginate .paginate_button.disabled {
            color: #9ca3af;
            cursor: not-allowed;
            opacity: 0.5;
        }
        #filesTable td { 
            padding: 0.875rem 0.75rem !important; 
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.15);
            color: #1f2937;
            font-size: 0.875rem;
            background-color: rgba(255,255,255,0.05);
        }
        #filesTable th { 
            padding: 1rem 0.75rem !important; 
            text-align: center;
            font-weight: 600;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: #374151;
        }
        
        #filesTable tbody tr:hover {
            background-color: rgba(255,255,255,0.15);
        }
        
        #filesTable tbody tr {
            transition: background-color 0.15s ease;
        }
        
        #filesTable th:nth-child(3),
        #filesTable td:nth-child(3),
        #filesTable th:nth-child(4),
        #filesTable td:nth-child(4),
        #filesTable th:nth-child(5),
        #filesTable td:nth-child(5),
        #filesTable th:nth-child(6),
        #filesTable td:nth-child(6),
        #filesTable th:nth-child(7),
        #filesTable td:nth-child(7) {
            padding-left: 0.25rem !important;
            padding-right: 0.25rem !important;
        }
        
        #filesTable {
            width: 100% !important;
            table-layout: auto;
            min-width: 1000px !important;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        #filesTable th,
        #filesTable td {
            white-space: nowrap;
        }
        
        .overflow-x-auto {
            overflow-x: auto !important;
            overflow-y: visible !important;
        }
        
        #filesTable th .flex.items-center {
            justify-content: center;
        }
        
        #filesTable th.sorting,
        #filesTable th.sorting_asc,
        #filesTable th.sorting_desc {
            position: relative;
            padding-right: 2rem !important;
        }
        
        #filesTable th.sorting:before,
        #filesTable th.sorting:after,
        #filesTable th.sorting_asc:before,
        #filesTable th.sorting_asc:after,
        #filesTable th.sorting_desc:before,
        #filesTable th.sorting_desc:after {
            display: none !important;
        }
        
        #filesTable th.sorting::after {
            content: '';
            position: absolute;
            right: 0.5rem;
            top: 50%;
            transform: translateY(-50%);
            width: 14px;
            height: 14px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%239ca3af' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M5 15l7-7 7 7'/%3E%3C/svg%3E");
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            opacity: 0.5;
        }
        
        #filesTable th.sorting::before {
            content: '';
            position: absolute;
            right: 0.5rem;
            top: 50%;
            transform: translateY(-50%) translateY(8px);
            width: 14px;
            height: 14px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%239ca3af' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            opacity: 0.5;
        }
        
        #filesTable th.sorting_asc::after {
            content: '';
            position: absolute;
            right: 0.5rem;
            top: 50%;
            transform: translateY(-50%);
            width: 14px;
            height: 14px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%232563eb' stroke-width='2.5'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M5 15l7-7 7 7'/%3E%3C/svg%3E");
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            opacity: 1;
        }
        
        #filesTable th.sorting_asc::before {
            display: none;
        }
        
        #filesTable th.sorting_desc::before {
            content: '';
            position: absolute;
            right: 0.5rem;
            top: 50%;
            transform: translateY(-50%) translateY(8px);
            width: 14px;
            height: 14px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%232563eb' stroke-width='2.5'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            opacity: 1;
        }
        
        #filesTable th.sorting_desc::after {
            display: none;
        }
        
        #filesTable th.sorting,
        #filesTable th.sorting_asc,
        #filesTable th.sorting_desc {
            white-space: nowrap;
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body>

<div x-data="{mobileMenu:false, open:true, showUpload:false, showDelete:false, showBulkDelete:false, editId:null, editFileName:'', showNotification:false, notificationMessage:'', notificationType:'success', showRoleChangeNotification:false, roleChangeMessage:''}" 
     class="relative">
    <div class="lg:hidden fixed top-0 left-0 right-0 z-50 glass-card rounded-b-2xl p-4 shadow-xl">
        <div class="flex items-center justify-between pt-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <div>
                    <div class="text-gray-800 font-bold text-sm leading-tight">Welcome</div>
                    <div class="text-gray-600 font-medium text-xs">User</div>
                </div>
            </div>
            <button @click="mobileMenu=!mobileMenu" class="text-gray-700 bg-white/20 hover:bg-white/30 border border-white/30 rounded-lg w-10 h-10 flex items-center justify-center transition-all duration-200 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>
        <div x-show="mobileMenu" x-transition class="mt-4 pt-4 border-t border-white/30">
            <div class="sidebar-section-title mb-2">Navigation</div>
            <nav class="space-y-1.5">
                <a href="{{ route('crm.contacts.index') }}" class="sidebar-link {{ request()->routeIs('crm.contacts.*') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10.707 1.293a1 1 0 00-1.414 0l-7 7A1 1 0 003 10h1v7a1 1 0 001 1h4v-4h2v4h4a1 1 0 001-1v-7h1a1 1 0 00.707-1.707l-7-7z"/></svg>
                    <span>Contacts</span>
                </a>
                <a href="{{ route('crm.leads.index') }}" class="sidebar-link {{ request()->routeIs('crm.leads.*') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M3 3h2l.4 2M7 13h8l2-8H5.4M7 13L6 6m1 7l-1 4m8-4l1 4m-5-4v4"/></svg>
                    <span>Leads</span>
                </a>
                <a href="{{ route('crm.tasks.index') }}" class="sidebar-link {{ request()->routeIs('crm.tasks.*') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/></svg>
                    <span>Tasks</span>
                </a>
                <a href="{{ route('crm.pipeline.index') }}" class="sidebar-link {{ request()->routeIs('crm.pipeline.*') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M3 12v3c0 1.657 3.134 3 7 3s7-1.343 7-3v-3c0 1.657-3.134 3-7 3s-7-1.343-7-3z"/><path d="M3 7v3c0 1.657 3.134 3 7 3s7-1.343 7-3V7c0 1.657-3.134 3-7 3S3 8.657 3 7z"/><path d="M17 5c0 1.657-3.134 3-7 3S3 6.657 3 5s3.134-3 7-3 7 1.343 7 3z"/></svg>
                    <span>Pipeline</span>
                </a>
                <a href="{{ route('crm.reports.index') }}" class="sidebar-link {{ request()->routeIs('crm.reports.*') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/></svg>
                    <span>Reports</span>
                </a>
                <a href="{{ route('crm.files.index') }}" class="sidebar-link {{ request()->routeIs('crm.files.*') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg>
                    <span>Files</span>
                </a>
            </nav>
        </div>
    </div>

    <aside class="hidden lg:flex fixed top-3 left-3 h-[calc(100vh-24px)] glass-card rounded-2xl transition-all duration-300 z-40 flex-col shadow-2xl overflow-hidden" :class="open ? 'w-64 p-4' : 'w-16 p-3'">
        <div class="flex items-center mb-6 pb-4 border-b border-white/20" :class="open ? 'justify-between' : 'justify-center'">
            <div class="flex items-center gap-3" :class="open ? 'opacity-100' : 'opacity-0 pointer-events-none absolute'">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg">
                    @if(auth()->check() && auth()->user()->name)
                        <span class="text-white font-bold text-sm">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-gray-800 font-bold text-sm leading-tight truncate">{{ auth()->check() ? auth()->user()->name : 'User' }}</div>
                    <div class="text-gray-600 font-medium text-xs truncate">{{ auth()->check() ? auth()->user()->email : 'user@example.com' }}</div>
                    @if(auth()->check() && method_exists(auth()->user(), 'hasRole'))
                        @php
                            $user = auth()->user();
                            $isAdmin = method_exists($user, 'hasRole') && $user->hasRole('Admin');
                            $isManager = method_exists($user, 'hasRole') && $user->hasRole('Manager');
                            $isExecutive = method_exists($user, 'hasRole') && $user->hasRole('Executive');
                        @endphp
                        @if($isAdmin)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 mt-1">Admin</span>
                        @elseif($isManager)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mt-1">Manager</span>
                        @elseif($isExecutive)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mt-1">Executive</span>
                        @endif
                    @endif
                </div>
            </div>
            <button @click="open=!open" class="text-gray-600 bg-white/20 hover:bg-white/30 border border-white/30 rounded-lg w-8 h-8 flex items-center justify-center hover:scale-110 transition-all duration-200 flex-shrink-0 shadow-sm" :aria-expanded="open">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" :d="open ? 'M15 19l-7-7 7-7' : 'M9 5l7 7-7 7'"/></svg>
            </button>
        </div>
        <div class="sidebar-section-title" :class="open ? 'opacity-100' : 'opacity-0 pointer-events-none'">
            <span x-show="open" x-transition>Navigation</span>
        </div>
        <nav class="space-y-1.5 mt-2 flex-1 overflow-y-auto overflow-x-hidden">
            <a href="{{ route('crm.contacts.index') }}" class="sidebar-link {{ request()->routeIs('crm.contacts.*') ? 'active' : '' }}" :class="!open ? 'justify-center px-0' : ''">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10.707 1.293a1 1 0 00-1.414 0l-7 7A1 1 0 003 10h1v7a1 1 0 001 1h4v-4h2v4h4a1 1 0 001-1v-7h1a1 1 0 00.707-1.707l-7-7z"/></svg>
                <span x-show="open" x-transition>Contacts</span>
            </a>
            <a href="{{ route('crm.leads.index') }}" class="sidebar-link {{ request()->routeIs('crm.leads.*') ? 'active' : '' }}" :class="!open ? 'justify-center px-0' : ''">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M3 3h2l.4 2M7 13h8l2-8H5.4M7 13L6 6m1 7l-1 4m8-4l1 4m-5-4v4"/></svg>
                <span x-show="open" x-transition>Leads</span>
            </a>
            <a href="{{ route('crm.tasks.index') }}" class="sidebar-link {{ request()->routeIs('crm.tasks.*') ? 'active' : '' }}" :class="!open ? 'justify-center px-0' : ''">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/></svg>
                <span x-show="open" x-transition>Tasks</span>
            </a>
            <a href="{{ route('crm.pipeline.index') }}" class="sidebar-link {{ request()->routeIs('crm.pipeline.*') ? 'active' : '' }}" :class="!open ? 'justify-center px-0' : ''">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M3 12v3c0 1.657 3.134 3 7 3s7-1.343 7-3v-3c0 1.657-3.134 3-7 3s-7-1.343-7-3z"/><path d="M3 7v3c0 1.657 3.134 3 7 3s7-1.343 7-3V7c0 1.657-3.134 3-7 3S3 8.657 3 7z"/><path d="M17 5c0 1.657-3.134 3-7 3S3 6.657 3 5s3.134-3 7-3 7 1.343 7 3z"/></svg>
                <span x-show="open" x-transition>Pipeline</span>
            </a>
            <a href="{{ route('crm.reports.index') }}" class="sidebar-link {{ request()->routeIs('crm.reports.*') ? 'active' : '' }}" :class="!open ? 'justify-center px-0' : ''">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/></svg>
                <span x-show="open" x-transition>Reports</span>
            </a>
            <a href="{{ route('crm.files.index') }}" class="sidebar-link {{ request()->routeIs('crm.files.*') ? 'active' : '' }}" :class="!open ? 'justify-center px-0' : ''">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg>
                <span x-show="open" x-transition>Files</span>
            </a>
            @php
                $showUserRoles = false;
                if (auth()->check()) {
                    $user = auth()->user();
                    // Always show for first user (by ID) - allows them to assign Admin role
                    $firstUser = \App\Models\User::orderBy('id', 'asc')->first();
                    $isFirstUser = $firstUser && $firstUser->id === $user->id;
                    
                    if (method_exists($user, 'hasRole')) {
                        try {
                            $showUserRoles = $user->hasRole('Admin') || $isFirstUser;
                        } catch (\Exception $e) {
                            $showUserRoles = $isFirstUser;
                        }
                    } else {
                        $showUserRoles = $isFirstUser;
                    }
                }
            @endphp
            @if($showUserRoles)
            <div class="pt-4 mt-4 border-t border-white/20">
                <div class="sidebar-section-title mb-2" :class="open ? 'opacity-100' : 'opacity-0 pointer-events-none'">
                    <span x-show="open" x-transition>Administration</span>
                </div>
                <a href="{{ route('crm.user-roles.index') }}" class="sidebar-link {{ request()->routeIs('crm.user-roles.*') ? 'active' : '' }}" :class="!open ? 'justify-center px-0' : ''">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg>
                    <span x-show="open" x-transition>User Roles</span>
                </a>
            </div>
            @endif
        </nav>
    </aside>

    <div class="lg:transition-all lg:duration-300 pt-16 lg:pt-0" :class="{'lg:pl-[280px]': open, 'lg:pl-[88px]': !open}">
        <div class="min-h-screen flex flex-col justify-center items-center px-2 py-8">
            <div class="w-full max-w-[95%] mx-auto px-3 md:px-4 py-3">
                <div class="glass-card w-full rounded-2xl px-6 py-4 mb-6 flex items-center justify-between">
                    <div class="text-2xl md:text-3xl font-bold text-gray-800 tracking-tight">Files</div>
                    <div class="text-sm text-gray-500 font-medium">Manage your documents and files</div>
                </div>
                <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-6">
                    <div class="flex items-end gap-6 w-full md:max-w-xl">
                        <button type="button" id="newFileBtn" @click="showUpload=true" class="flex-shrink-0 flex items-center gap-2.5 px-6 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-[1.02]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 20 20" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            Upload File
                        </button>
                    </div>
                    <div class="flex items-end gap-3 md:ml-auto w-full md:w-auto" id="datatableSearchContainer">
                    </div>
                </div>
                <div class="mb-6 glass-card rounded-2xl p-5">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-3 md:gap-0">
                        <div class="font-bold text-lg text-gray-800 tracking-tight flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                            Filters
                        </div>
                        <button type="button" id="applyFilters" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold shadow-md hover:shadow-lg transition-all duration-200 hover:scale-[1.02] w-full md:w-auto">Apply Filters</button>
                    </div>
                    <div class="flex flex-col md:flex-row items-end gap-3">
                        <div class="w-full md:flex-1">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">File Type</label>
                            <input type="text" id="filterFileType" class="w-full border-2 border-white/30 rounded-xl px-4 py-2.5 bg-white/15 backdrop-blur-sm text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400 transition-all duration-200" autocomplete="off" placeholder="Filter by type" onkeydown="if(event.key==='Enter'){event.preventDefault();return false;}">
                        </div>
                        <div class="w-full md:flex-1">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Linked To</label>
                            <select id="filterLinkedType" class="w-full border-2 border-white/30 rounded-xl px-4 py-2.5 bg-white/15 backdrop-blur-sm text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" onkeydown="if(event.key==='Enter'){event.preventDefault();return false;}">
                                <option value="">All Types</option>
                                <option value="contact">Contact</option>
                                <option value="lead">Lead</option>
                                <option value="deal">Deal</option>
                            </select>
                        </div>
                        <div class="w-full md:flex-1">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Uploaded From</label>
                            <input type="date" id="filterUploadedFrom" class="w-full border-2 border-white/30 rounded-xl px-4 py-2.5 bg-white/15 backdrop-blur-sm text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" autocomplete="off" onkeydown="if(event.key==='Enter'){event.preventDefault();return false;}">
                        </div>
                        <div class="w-full md:flex-1">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Uploaded To</label>
                            <input type="date" id="filterUploadedTo" class="w-full border-2 border-white/30 rounded-xl px-4 py-2.5 bg-white/15 backdrop-blur-sm text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" autocomplete="off" onkeydown="if(event.key==='Enter'){event.preventDefault();return false;}">
                        </div>
                    </div>
                </div>
                <div class="mb-4 flex flex-col sm:flex-row sm:items-center gap-3">
                    @if(auth()->check() && auth()->user()->can('delete files'))
                    <button type="button" id="bulkDeleteBtn" class="px-4 py-2.5 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white rounded-xl shadow-md hover:shadow-lg transition-all duration-200 text-sm font-semibold flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        Delete Selected
                    </button>
                    @endif
                </div>
                <div class="overflow-x-auto rounded-2xl shadow-2xl glass-card -mx-2 sm:mx-0" style="overflow-x: auto; overflow-y: visible;">
                    <table id="filesTable" class="w-full text-sm bg-white/15 backdrop-blur-sm rounded-2xl whitespace-nowrap" style="min-width: 1000px;">
                        <thead class="uppercase bg-white/25 backdrop-blur-sm text-gray-700 rounded-t-2xl border-b-2 border-white/20">
                            <tr>
                                <th class="p-3 text-center"><input type="checkbox" id="selectAll"></th>
                                <th class="p-3 font-semibold tracking-widest text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <span>File Name</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </th>
                                <th class="p-3 font-semibold tracking-widest text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <span>Type</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </th>
                                <th class="p-3 font-semibold tracking-widest text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <span>Size</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                        </svg>
                                    </div>
                                </th>
                                <th class="p-3 font-semibold tracking-widest text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <span>Linked To</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </th>
                                <th class="p-3 font-semibold tracking-widest text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <span>Uploaded</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </th>
                                <th class="p-3 font-semibold tracking-widest text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white/10 divide-y divide-white/20">
                        </tbody>
                    </table>
                </div>
    <div x-show="showBulkDelete" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showBulkDelete=false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 sm:p-8" @click.stop>
            <div class="flex items-center gap-4 mb-4">
                <div class="flex-shrink-0 w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Delete Selected Files</h2>
                    <p class="text-sm text-gray-600 mt-1">This action cannot be undone</p>
                </div>
            </div>
            <p class="text-sm text-gray-700 mb-6 ml-16">Are you sure you want to delete the selected files? All files will be permanently removed.</p>
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" class="px-6 py-3 rounded-xl border-2 border-gray-200 bg-white hover:bg-gray-50 text-gray-700 font-semibold transition-all duration-200 cancel-bulk-delete-btn" @click="showBulkDelete=false">Cancel</button>
                <button type="button" id="confirmBulkDelete" class="px-6 py-3 rounded-xl bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-semibold shadow-lg hover:shadow-xl transition-all duration-200">Delete Selected</button>
            </div>
        </div>
    </div>
                <div class="flex flex-col md:flex-row items-center md:justify-between gap-4 mt-6 p-5 glass-card rounded-2xl">
                    <div class="flex flex-wrap items-center gap-3" id="datatableLengthContainer">
                    </div>
                    <div class="flex flex-wrap items-center gap-2 justify-center" id="datatablePaginationContainer">
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <button type="button" id="resetFilters" class="px-4 py-2.5 rounded-xl border-2 border-gray-200 bg-white hover:bg-gray-50 text-gray-700 font-semibold text-sm shadow-sm hover:shadow-md transition-all duration-200 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div x-show="showUpload" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showUpload=false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl p-6 sm:p-8 max-h-[90vh] overflow-y-auto" @click.stop>
            <div class="flex items-center justify-between mb-6 pb-4 border-b-2 border-gray-100">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Upload File</h2>
                    <p class="text-sm text-gray-500 mt-1">Upload a new file to your database</p>
                </div>
                <button type="button" @click="showUpload=false" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form id="uploadForm" class="grid grid-cols-1 md:grid-cols-2 gap-5" enctype="multipart/form-data">
                @csrf
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">File <span class="text-red-500">*</span></label>
                    <input type="file" name="file" id="uploadFile" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 bg-white text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" required>
                    <p class="text-xs text-gray-500 mt-1">Supported formats: PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG (Max 10MB)</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Link To</label>
                    <select name="linked_type" id="uploadLinkedType" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 bg-white text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                        <option value="">None</option>
                        <option value="contact">Contact</option>
                        <option value="lead">Lead</option>
                        <option value="deal">Deal</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Linked ID</label>
                    <input type="number" name="linked_id" id="uploadLinkedId" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 bg-white text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400 transition-all duration-200" placeholder="ID">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                    <textarea name="description" id="uploadDescription" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 bg-white text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400 transition-all duration-200" rows="4" placeholder="File description..."></textarea>
                </div>
                <div class="md:col-span-2 flex justify-end gap-3 mt-4 pt-4 border-t border-gray-100">
                    <button type="button" class="px-6 py-3 rounded-xl border-2 border-gray-200 bg-white hover:bg-gray-50 text-gray-700 font-semibold transition-all duration-200 cancel-upload-btn" @click="showUpload=false">Cancel</button>
                    <button type="submit" id="uploadSubmitBtn" class="px-6 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold shadow-lg hover:shadow-xl transition-all duration-200">Upload File</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="showDelete" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showDelete=false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 sm:p-8" @click.stop>
            <div class="flex items-center gap-4 mb-4">
                <div class="flex-shrink-0 w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Delete File</h2>
                    <p class="text-sm text-gray-600 mt-1">This action cannot be undone</p>
                </div>
            </div>
            <p class="text-sm text-gray-700 mb-6 ml-16">Are you sure you want to delete <span class="font-semibold" x-text="editFileName"></span>? The file will be permanently removed.</p>
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" class="px-6 py-3 rounded-xl border-2 border-gray-200 bg-white hover:bg-gray-50 text-gray-700 font-semibold transition-all duration-200 cancel-delete-btn" @click="showDelete=false">Cancel</button>
                <button type="button" id="confirmDelete" class="px-6 py-3 rounded-xl bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-semibold shadow-lg hover:shadow-xl transition-all duration-200">Delete File</button>
            </div>
        </div>
    </div>

    <div x-show="showNotification" x-transition.opacity class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" @click="showNotification=false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <div class="flex items-start gap-4">
                <div :class="notificationType === 'success' ? 'bg-green-100 text-green-600' : notificationType === 'error' ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-600'" class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center">
                    <svg x-show="notificationType === 'success'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <svg x-show="notificationType === 'error'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <svg x-show="notificationType !== 'success' && notificationType !== 'error'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1" x-text="notificationType === 'success' ? 'Success' : notificationType === 'error' ? 'Error' : 'Notice'"></h3>
                    <p class="text-sm text-gray-600 mb-4 whitespace-pre-line" x-text="notificationMessage"></p>
                    <div class="flex justify-end">
                        <button type="button" @click="showNotification=false" class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors">OK</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Role Change Notification Modal -->
    <div x-show="showRoleChangeNotification" x-transition.opacity class="fixed inset-0 z-[70] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 sm:p-8">
            <div class="flex items-center gap-4 mb-4">
                <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Role Changed</h2>
                </div>
            </div>
            <p class="text-sm text-gray-700 mb-6 ml-16" x-text="roleChangeMessage"></p>
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 ml-16">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-semibold text-yellow-800">Please refresh the page</p>
                        <p class="text-sm text-yellow-700 mt-1">Press <kbd class="px-2 py-1 bg-yellow-100 border border-yellow-300 rounded text-xs font-mono font-bold">F5</kbd> or click the button below to refresh and see your updated permissions.</p>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" @click="location.reload()" class="px-6 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold shadow-lg hover:shadow-xl transition-all duration-200">Refresh Page</button>
            </div>
        </div>
    </div>
    

</div>

<script>

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// Check for role change notifications on page load
$(document).ready(function() {
    // Check for role change notification
    $.ajax({
        url: '{{ route('crm.user-roles.check-notification') }}',
        method: 'GET',
        success: function(response) {
            if (response.success && response.has_notification && response.notification) {
                const alpineData = document.querySelector('[x-data]');
                if (alpineData && alpineData.__x) {
                    alpineData.__x.$data.showRoleChangeNotification = true;
                    alpineData.__x.$data.roleChangeMessage = response.notification.message;
                }
            }
        },
        error: function() {
            // Silently fail - notification check is not critical
        }
    });
    
    let table = $('#filesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('crm.files.datatable') }}',
            data: function(d) {
                d.file_type = $('#filterFileType').val() || '';
                d.linked_type = $('#filterLinkedType').val() || '';
                d.uploaded_from = $('#filterUploadedFrom').val() || '';
                d.uploaded_to = $('#filterUploadedTo').val() || '';
            }
        },
        columns: [
            { data: 'id', name: 'id', orderable: false, searchable: false, render: function(data) {
                return '<input type="checkbox" class="row-check rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" value="' + data + '">';
            }},
            { data: 'original_name', name: 'original_name', render: function(data, type, row) {
                return '<div class="flex items-center gap-2"><span>' + (row.icon_html || '') + '</span><span class="font-medium text-gray-900">' + (data || '-') + '</span></div>';
            }},
            { data: 'file_type', name: 'file_type', render: function(data) {
                return '<span class="text-gray-700">' + (data || '-') + '</span>';
            }},
            { data: 'file_size', name: 'file_size', render: function(data) {
                return '<span class="text-gray-700">' + (data || '-') + '</span>';
            }},
            { data: 'linked_type', name: 'linked_type', render: function(data) {
                return '<span class="text-gray-700">' + (data || '-') + '</span>';
            }},
            { data: 'created_at', name: 'created_at', render: function(data) {
                return '<span class="text-gray-700">' + (data || '-') + '</span>';
            }},
            { data: 'actions_html', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[5, 'desc']],
        pageLength: 10,
        dom: '<"top"f>rt<"bottom"lip><"clear">',
        pagingType: 'simple_numbers',
        language: {
            search: "",
            searchPlaceholder: "Search...",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        },
        initComplete: function() {
            const searchContainer = $('#datatableSearchContainer');
            const searchInput = $('.dataTables_filter');
            if (searchContainer.length && searchInput.length) {
                const input = searchInput.find('input').first();
                
                const wrapper = $('<div class="flex items-center gap-3 bg-white/15 backdrop-blur-sm border-2 border-white/30 rounded-xl px-4 py-2.5 shadow-sm w-full md:w-96 focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-200 transition-all duration-200"></div>');
                wrapper.append('<label class="text-gray-600 px-1 text-sm font-semibold">Search</label>');
                wrapper.append('<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 103 10.5a7.5 7.5 0 0013.15 6.15z" /></svg>');
                
                input.addClass('bg-transparent border-0 outline-none ring-0 focus:ring-0 w-full text-gray-800 placeholder-gray-400 text-sm px-2 py-1');
                input.attr('placeholder', '');
                
                searchInput.find('label').remove();
                wrapper.append(input);
                searchInput.html(wrapper);
                searchInput.appendTo(searchContainer);
                
                let searchTimeout;
                input.off('keyup.search input.search').on('keyup.search input.search', function() {
                    clearTimeout(searchTimeout);
                    const self = this;
                    searchTimeout = setTimeout(function() {
                        table.search(self.value).draw();
                    }, 300); 
                });
                
                input.on('input', function() {
                    if (this.value === '') {
                        table.search('').draw();
                    }
                });
            }
            
            const lengthContainer = $('#datatableLengthContainer');
            const lengthSelect = $('.dataTables_length');
            const info = $('.dataTables_info');
            if (lengthContainer.length) {
                if (lengthSelect.length) {
                    lengthSelect.appendTo(lengthContainer);
                    lengthSelect.css('margin', '0');
                    lengthSelect.find('label').css({
                        'font-size': '0.875rem',
                        'font-weight': '500',
                        'color': '#374151'
                    });
                }
                if (info.length) {
                    info.appendTo(lengthContainer);
                    info.css({
                        'margin': '0',
                        'font-size': '0.875rem',
                        'font-weight': '500'
                    });
                }
            }
            
            const paginationContainer = $('#datatablePaginationContainer');
            const pagination = $('.dataTables_paginate');
            if (paginationContainer.length && pagination.length) {
                pagination.appendTo(paginationContainer);
                pagination.css({
                    'margin': '0',
                    'display': 'flex',
                    'align-items': 'center',
                    'gap': '0.25rem'
                });
                pagination.find('.paginate_button').css({
                    'font-size': '0.875rem',
                    'font-weight': '500',
                    'padding': '0.5rem 0.875rem',
                    'min-height': '2.25rem'
                });
            }
            
            $('.dataTables_wrapper .top').hide();
        },
        drawCallback: function() {
            const pagination = $('.dataTables_paginate');
            if (pagination.length) {
                const paginationContainer = $('#datatablePaginationContainer');
                if (paginationContainer.length && pagination.parent()[0] !== paginationContainer[0]) {
                    pagination.appendTo(paginationContainer);
                }
                
                pagination.find('.paginate_button').css({
                    'font-size': '0.875rem',
                    'font-weight': '500',
                    'padding': '0.5rem 0.875rem',
                    'min-height': '2.25rem'
                });
            }
            
            const lengthContainer = $('#datatableLengthContainer');
            const lengthSelect = $('.dataTables_length');
            const info = $('.dataTables_info');
            if (lengthContainer.length) {
                if (lengthSelect.length && lengthSelect.parent()[0] !== lengthContainer[0]) {
                    lengthSelect.appendTo(lengthContainer);
                    lengthSelect.css('margin', '0');
                    lengthSelect.find('label').css({
                        'font-size': '0.875rem',
                        'font-weight': '500',
                        'color': '#374151'
                    });
                }
                if (info.length && info.parent()[0] !== lengthContainer[0]) {
                    info.appendTo(lengthContainer);
                    info.css({
                        'margin': '0',
                        'font-size': '0.875rem',
                        'font-weight': '500'
                    });
                }
            }
        }
    });

    function showNotification(message, type = 'success') {
        // For success notifications, auto-hide quickly (2 seconds) or don't show at all
        if (type === 'success') {
            // Just reload the table silently, don't show notification
            return;
        }
        
        const alpineData = getAlpineData();
        if (alpineData && alpineData.__x) {
            const data = alpineData.__x.$data;
            data.notificationMessage = message;
            data.notificationType = type;
            data.showNotification = true;
        }
        const notificationModal = $('[x-show="showNotification"]');
        if (notificationModal.length) {
            notificationModal[0].style.display = 'flex';
        }
        
        setTimeout(function() {
            if (alpineData && alpineData.__x) {
                alpineData.__x.$data.showNotification = false;
            }
            if (notificationModal.length) {
                notificationModal[0].style.display = 'none';
            }
        }, 5000);
    }

    function getAlpineData() {
        return document.querySelector('[x-data]');
    }
    
    function closeModal(modalName) {
        const alpineData = getAlpineData();
        if (alpineData && alpineData.__x) {
            alpineData.__x.$data[modalName] = false;
        }
        
        const modal = $('[x-show="' + modalName + '"]');
        if (modal.length) {
            modal.hide();
            modal.css('display', 'none');
        }
        
        setTimeout(function() {
            const alpineDataAfter = getAlpineData();
            if (alpineDataAfter && alpineDataAfter.__x) {
                alpineDataAfter.__x.$data[modalName] = false;
            }
            const modalAfter = $('[x-show="' + modalName + '"]');
            if (modalAfter.length && modalAfter.is(':visible')) {
                modalAfter.hide();
                modalAfter.css('display', 'none');
            }
        }, 50);
    }
    
    let isUploading = false;
    
    // Reset upload form - matching pattern from Contacts/Leads/Tasks
    function resetUploadForm() {
        isUploading = false;
        
        // Reset form
        const form = $('#uploadForm')[0];
        if (form) {
            form.reset();
        }
        
        // Replace file input (browsers don't allow clearing file inputs via reset)
        const oldInput = $('#uploadFile')[0];
        if (oldInput && oldInput.parentNode) {
            const newInput = document.createElement('input');
            newInput.type = 'file';
            newInput.id = 'uploadFile';
            newInput.name = 'file';
            newInput.className = 'w-full border-2 border-gray-200 rounded-xl px-4 py-3 bg-white text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200';
            newInput.setAttribute('accept', '.pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png');
            newInput.setAttribute('required', 'required');
            
            oldInput.parentNode.replaceChild(newInput, oldInput);
        }
        
        // Reset other fields
        $('#uploadLinkedType').val('');
        $('#uploadLinkedId').val('');
        $('#uploadDescription').val('');
        
        // Reset button state
        const submitBtn = $('#uploadSubmitBtn');
        if (submitBtn.length) {
            submitBtn.prop('disabled', false).text('Upload File');
        }
    }
    
    window.resetFileInput = resetUploadForm;
    window.resetUploadForm = resetUploadForm;
    
    function handleFileUpload() {
        console.log('handleFileUpload called, isUploading:', isUploading);
        
        if (isUploading) {
            console.log('Upload already in progress, aborting');
            return false;
        }
        
        const fileInput = $('#uploadFile')[0];
        console.log('File input:', fileInput);
        console.log('File input files:', fileInput ? fileInput.files : 'null');
        if (!fileInput || !fileInput.files || !fileInput.files[0]) {
            console.log('No file selected');
            showNotification('Please select a file to upload.', 'error');
            return false;
        }
        
        const submitBtn = $('#uploadSubmitBtn');
        console.log('Submit button disabled:', submitBtn.prop('disabled'));
        if (submitBtn.prop('disabled')) {
            console.log('Button is disabled, aborting');
            return false;
        }
        
        isUploading = true;
        const originalText = submitBtn.text();
        submitBtn.prop('disabled', true).text('Uploading...');
        
        // Get CSRF token from the form (it's recreated each time, so get it fresh)
        const csrfToken = $('input[name="_token"]', '#uploadForm').val() || $('meta[name="csrf-token"]').attr('content');
        
        const formData = new FormData();
        formData.append('_token', csrfToken);
        formData.append('file', fileInput.files[0]);
        formData.append('linked_type', $('#uploadLinkedType').val() || '');
        formData.append('linked_id', $('#uploadLinkedId').val() || '');
        formData.append('description', $('#uploadDescription').val() || '');
        
        $.ajax({
            url: '{{ route('crm.files.store') }}',
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            processData: false,
            contentType: false,
            data: formData,
            success: function(response) {
                console.log('Upload response:', response);
                
                if (!response || !response.success) {
                    showNotification('File upload failed. Please try again.', 'error');
                    isUploading = false;
                    submitBtn.prop('disabled', false).text(originalText);
                    return;
                }
                
                // Reset button state first
                submitBtn.prop('disabled', false).text(originalText);
                
                // Reload table
                table.ajax.reload();
                
                // Reset form (matching Contacts pattern)
                resetUploadForm();
                
                // Close modal - matching Contacts pattern exactly
                const alpineData = getAlpineData();
                if (alpineData && alpineData.__x) {
                    alpineData.__x.$data.showUpload = false;
                }
                
                const modal = $('[x-show="showUpload"]');
                if (modal.length) {
                    modal.hide();
                    modal.css('display', 'none');
                }
                
                // Ensure modal stays closed
                setTimeout(function() {
                    const alpineDataAfter = getAlpineData();
                    if (alpineDataAfter && alpineDataAfter.__x) {
                        alpineDataAfter.__x.$data.showUpload = false;
                    }
                    const modalAfter = $('[x-show="showUpload"]');
                    if (modalAfter.length && modalAfter.is(':visible')) {
                        modalAfter.hide();
                        modalAfter.css('display', 'none');
                    }
                }, 50);
            },
            error: function(xhr, status, error) {
                isUploading = false;
                submitBtn.prop('disabled', false).text(originalText);
                console.error('Error uploading file:', xhr, status, error);
                console.error('Response text:', xhr.responseText);
                
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    let errors = Object.values(xhr.responseJSON.errors).flat();
                    showNotification('Validation errors:\n' + errors.join('\n'), 'error');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    showNotification('Error: ' + xhr.responseJSON.message, 'error');
                } else if (xhr.status === 0) {
                    showNotification('Network error. Please check your connection and try again.', 'error');
                } else {
                    showNotification('Error uploading file: ' + (error || 'Unknown error'), 'error');
                }
            }
        });
        
        return false;
    }

    let currentFileId = null;

    $(document).on('click', '.delete-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const btn = $(this);
        currentFileId = btn.data('id');
        
        if (!currentFileId) {
            showNotification('File ID not found.', 'error');
            return;
        }
        
        const alpineData = getAlpineData();
        if (alpineData && alpineData.__x) {
            const data = alpineData.__x.$data;
            data.editId = currentFileId;
            data.editFileName = btn.data('name') || '';
            data.showDelete = true;
        }
        
        const deleteModal = $('[x-show="showDelete"]');
        if (deleteModal.length) {
            deleteModal.removeAttr('style'); 
            deleteModal.show();
            deleteModal.css('display', 'flex');
        }
    });

    // Form submit handler - using event delegation to handle form recreation
    $(document).on('submit', '#uploadForm', function(e) {
        e.preventDefault();
        e.stopPropagation();
        return handleFileUpload();
    });

    $('#confirmDelete').on('click', function() {
        const alpineData = getAlpineData();
        if (alpineData && alpineData.__x) {
            currentFileId = alpineData.__x.$data.editId || currentFileId;
        }
        if (!currentFileId) {
            showNotification('File ID not found.', 'error');
            return;
        }
        
        $.ajax({
            url: '{{ route('crm.files.destroy', '__ID__') }}'.replace('__ID__', currentFileId),
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                _method: 'DELETE'
            },
            success: function(response) {
                table.ajax.reload();
                closeModal('showDelete');
                currentFileId = null;
                
                // Also ensure modal is hidden
                const deleteModal = $('[x-show="showDelete"]');
                if (deleteModal.length) {
                    deleteModal.hide();
                    deleteModal.css('display', 'none');
                }
            },
            error: function() {
                showNotification('Error deleting file.', 'error');
            }
        });
    });

    let currentBulkDeleteIds = [];
    $('#bulkDeleteBtn').on('click', function() {
        const selectedIds = $('.row-check:checked').map(function() {
            return $(this).val();
        }).get();
        
        if (selectedIds.length === 0) {
            return;
        }
        
        currentBulkDeleteIds = selectedIds;
        const alpineData = getAlpineData();
        if (alpineData && alpineData.__x) {
            alpineData.__x.$data.showBulkDelete = true;
        }
        
        const bulkDeleteModal = $('[x-show="showBulkDelete"]');
        if (bulkDeleteModal.length) {
            bulkDeleteModal.removeAttr('style'); // Remove inline styles so Alpine.js can control visibility
            bulkDeleteModal.show();
            bulkDeleteModal.css('display', 'flex');
        }
    });

    $('#confirmBulkDelete').on('click', function() {
        if (!currentBulkDeleteIds || currentBulkDeleteIds.length === 0) {
            closeModal('showBulkDelete');
            return;
        }
        
        $.ajax({
            url: '{{ route('crm.files.bulk-delete') }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                ids: currentBulkDeleteIds
            },
            success: function(response) {
                table.ajax.reload();
                closeModal('showBulkDelete');
                
                const bulkDeleteModal = $('[x-show="showBulkDelete"]');
                if (bulkDeleteModal.length) {
                    bulkDeleteModal.hide();
                    bulkDeleteModal.css('display', 'none');
                }
            },
            error: function() {
                showNotification('Error deleting files.', 'error');
            }
        });
    });

    $('#selectAll').on('click', function() {
        $('.row-check').prop('checked', $(this).prop('checked'));
    });
    
    // Use event delegation for cancel buttons to handle dynamically created elements
    $(document).off('click', '.cancel-upload-btn').on('click', '.cancel-upload-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        closeModal('showUpload');
        return false;
    });
    
    setTimeout(function() {
        $('.cancel-delete-btn').off('click').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            closeModal('showDelete');
            currentFileId = null;
            return false;
        });
        
        $('.cancel-bulk-delete-btn').off('click').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            closeModal('showBulkDelete');
            return false;
        });
    }, 100);
    
    $(document).off('click', '.cancel-delete-btn').on('click', '.cancel-delete-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        closeModal('showDelete');
        currentFileId = null;
        return false;
    });
    
    $(document).off('click', '.cancel-bulk-delete-btn').on('click', '.cancel-bulk-delete-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        closeModal('showBulkDelete');
        return false;
    });

    // Auto-refresh table every 30 seconds to show new data added by other users
    let autoRefreshInterval = null;
    let isModalOpen = false;
    
    function startAutoRefresh() {
        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
        }
        
        autoRefreshInterval = setInterval(function() {
            if (!document.hidden && !isModalOpen && typeof table !== 'undefined' && table) {
                table.ajax.reload(null, false);
            }
        }, 30000); // Refresh every 30 seconds
    }
    
    function stopAutoRefresh() {
        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
            autoRefreshInterval = null;
        }
    }
    
    startAutoRefresh();
    
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopAutoRefresh();
        } else {
            startAutoRefresh();
        }
    });
    
    // Monitor modal state
    const alpineData = getAlpineData();
    if (alpineData && alpineData.__x) {
        setInterval(function() {
            if (alpineData && alpineData.__x) {
                const data = alpineData.__x.$data;
                const newModalState = data.showUpload || data.showDelete || data.showBulkDelete || data.showNotification;
                if (newModalState !== isModalOpen) {
                    isModalOpen = newModalState;
                }
            }
        }, 500);
    }

    $('#applyFilters').on('click', function() {
        table.ajax.reload(function() {
        }, false);
    });

    $('#resetFilters').on('click', function() {
        $('#filterFileType').val('');
        $('#filterLinkedType').val('');
        $('#filterUploadedFrom').val('');
        $('#filterUploadedTo').val('');
        table.search('').draw();
        table.ajax.reload(null, false);
    });
    
    $('#filterFileType, #filterLinkedType, #filterUploadedFrom, #filterUploadedTo').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#applyFilters').click();
        }
    });
    
    // Handle button click - matching Contacts pattern exactly
    $(document).on('click', '#newFileBtn', function(e) {
        // Remove inline styles immediately (before Alpine.js processes)
        const modal = $('[x-show="showUpload"]');
        if (modal.length) {
            modal.removeAttr('style');
        }
        
        // Reset form when modal opens
        setTimeout(function() {
            resetUploadForm();
        }, 150);
    });
});
</script>
</body>
</html>

