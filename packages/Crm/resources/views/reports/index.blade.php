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
    <title>CRM Reports</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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
        .stat-card {
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
        #reportsTable td { 
            padding: 0.875rem 0.75rem !important; 
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.15);
            color: #1f2937;
            font-size: 0.875rem;
            background-color: rgba(255,255,255,0.05);
        }
        #reportsTable th { 
            padding: 1rem 0.75rem !important; 
            text-align: center;
            font-weight: 600;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: #374151;
        }
        
        #reportsTable tbody tr:hover {
            background-color: rgba(255,255,255,0.15);
        }
        
        #reportsTable tbody tr {
            transition: background-color 0.15s ease;
        }
        
        #reportsTable th:nth-child(3),
        #reportsTable td:nth-child(3),
        #reportsTable th:nth-child(4),
        #reportsTable td:nth-child(4),
        #reportsTable th:nth-child(5),
        #reportsTable td:nth-child(5),
        #reportsTable th:nth-child(6),
        #reportsTable td:nth-child(6),
        #reportsTable th:nth-child(7),
        #reportsTable td:nth-child(7) {
            padding-left: 0.25rem !important;
            padding-right: 0.25rem !important;
        }
        
        #reportsTable {
            width: 100% !important;
            table-layout: auto;
            min-width: 800px !important;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        #reportsTable th,
        #reportsTable td {
            white-space: nowrap;
        }
        
        .overflow-x-auto {
            overflow-x: auto !important;
            overflow-y: visible !important;
        }
        
        #reportsTable th .flex.items-center {
            justify-content: center;
        }
        
        #reportsTable th.sorting,
        #reportsTable th.sorting_asc,
        #reportsTable th.sorting_desc {
            position: relative;
            padding-right: 2rem !important;
        }
        
        #reportsTable th.sorting:before,
        #reportsTable th.sorting:after,
        #reportsTable th.sorting_asc:before,
        #reportsTable th.sorting_asc:after,
        #reportsTable th.sorting_desc:before,
        #reportsTable th.sorting_desc:after {
            display: none !important;
        }
        
        #reportsTable th.sorting::after {
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
        
        #reportsTable th.sorting::before {
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
        
        #reportsTable th.sorting_asc::after {
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
        
        #reportsTable th.sorting_asc::before {
            display: none;
        }
        
        #reportsTable th.sorting_desc::before {
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
        
        #reportsTable th.sorting_desc::after {
            display: none;
        }
        
        #reportsTable th.sorting,
        #reportsTable th.sorting_asc,
        #reportsTable th.sorting_desc {
            white-space: nowrap;
        }
        
        .chart-type-btn {
            color: #6b7280;
            background: transparent;
            border: none;
            cursor: pointer;
        }
        
        .chart-type-btn:hover {
            color: #2563eb;
            background: rgba(37, 99, 235, 0.1);
        }
        
        .chart-type-btn.active {
            color: #2563eb;
            background: rgba(37, 99, 235, 0.2);
            font-weight: 600;
        }
        
        .chart-type-btn svg {
            display: inline-block;
            vertical-align: middle;
        }
    </style>
</head>
<body>

<div x-data="{mobileMenu:false, open:true, showNotification:false, notificationMessage:'', notificationType:'success', dashboardData: {total_contacts: 0, total_leads: 0, total_deals: 0, won_deals: 0, lost_deals: 0, conversion_rate: 0, total_revenue: 0}}" 
     x-init="setTimeout(() => { if (typeof loadDashboardData === 'function') { const scrollY = window.scrollY; loadDashboardData(); setTimeout(() => window.scrollTo(0, scrollY), 10); } }, 100);" 
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
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <div>
                    <div class="text-gray-800 font-bold text-sm leading-tight">Welcome</div>
                    <div class="text-gray-600 font-medium text-xs">User</div>
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
        </nav>
    </aside>

    <div class="lg:transition-all lg:duration-300 pt-16 lg:pt-0" :class="{'lg:pl-[280px]': open, 'lg:pl-[88px]': !open}">
        <div class="min-h-screen flex flex-col justify-center items-center px-2 py-8">
            <div class="w-full max-w-[95%] mx-auto px-3 md:px-4 py-3">
                <div class="glass-card w-full rounded-2xl px-6 py-4 mb-6 flex items-center justify-between">
                    <div class="text-2xl md:text-3xl font-bold text-gray-800 tracking-tight">Reports & Analytics</div>
                    <div class="text-sm text-gray-500 font-medium">View your CRM performance metrics</div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                    <div class="stat-card rounded-2xl p-5">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Total Contacts</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        </div>
                        <div class="text-2xl font-bold text-gray-800" x-text="dashboardData.total_contacts || 0">0</div>
                    </div>
                    <div class="stat-card rounded-2xl p-5">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Total Leads</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                        </div>
                        <div class="text-2xl font-bold text-gray-800" x-text="dashboardData.total_leads || 0">0</div>
                    </div>
                    <div class="stat-card rounded-2xl p-5">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Total Deals</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                        </div>
                        <div class="text-2xl font-bold text-gray-800" x-text="dashboardData.total_deals || 0">0</div>
                    </div>
                    <div class="stat-card rounded-2xl p-5">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Conversion Rate</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                        </div>
                        <div class="text-2xl font-bold text-gray-800" x-text="(dashboardData.conversion_rate || 0) + '%'">0%</div>
                    </div>
                    <div class="stat-card rounded-2xl p-5">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Total Revenue</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div class="text-2xl font-bold text-gray-800">$<span x-text="dashboardData.total_revenue || '0.00'">0.00</span></div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <div class="glass-card rounded-2xl p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-gray-800">Deals Won vs Lost</h3>
                            <div class="flex items-center gap-2 bg-white/30 rounded-lg p-1">
                                <button type="button" class="chart-type-btn px-3 py-1.5 rounded-md text-xs font-semibold transition-all duration-200" data-chart="dealsChart" data-type="pie" title="Pie Chart">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H11V3.512A9.025 9.025 0 019.512 20.488z"/></svg>
                                </button>
                                <button type="button" class="chart-type-btn px-3 py-1.5 rounded-md text-xs font-semibold transition-all duration-200" data-chart="dealsChart" data-type="bar" title="Bar Chart">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                </button>
                                <button type="button" class="chart-type-btn px-3 py-1.5 rounded-md text-xs font-semibold transition-all duration-200" data-chart="dealsChart" data-type="line" title="Line Chart">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                                </button>
                            </div>
                        </div>
                        <div class="h-64">
                            <canvas id="dealsChart"></canvas>
                        </div>
                    </div>
                    <div class="glass-card rounded-2xl p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-gray-800">Revenue by Stage</h3>
                            <div class="flex items-center gap-2 bg-white/30 rounded-lg p-1">
                                <button type="button" class="chart-type-btn px-3 py-1.5 rounded-md text-xs font-semibold transition-all duration-200" data-chart="revenueChart" data-type="pie" title="Pie Chart">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H11V3.512A9.025 9.025 0 019.512 20.488z"/></svg>
                                </button>
                                <button type="button" class="chart-type-btn px-3 py-1.5 rounded-md text-xs font-semibold transition-all duration-200" data-chart="revenueChart" data-type="bar" title="Bar Chart">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                </button>
                                <button type="button" class="chart-type-btn px-3 py-1.5 rounded-md text-xs font-semibold transition-all duration-200" data-chart="revenueChart" data-type="line" title="Line Chart">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                                </button>
                            </div>
                        </div>
                        <div class="h-64">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-end gap-3 md:ml-auto w-full md:w-auto mb-6" id="datatableSearchContainer">
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
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">User</label>
                            <input type="number" id="filterUser" class="w-full border-2 border-white/30 rounded-xl px-4 py-2.5 bg-white/15 backdrop-blur-sm text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400 transition-all duration-200" autocomplete="off" placeholder="User ID" onkeydown="if(event.key==='Enter'){event.preventDefault();return false;}">
                        </div>
                        <div class="w-full md:flex-1">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Stage</label>
                            <select id="filterStage" class="w-full border-2 border-white/30 rounded-xl px-4 py-2.5 bg-white/15 backdrop-blur-sm text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" onkeydown="if(event.key==='Enter'){event.preventDefault();return false;}">
                                <option value="">All Stages</option>
                                <option value="prospect">Prospect</option>
                                <option value="negotiation">Negotiation</option>
                                <option value="proposal">Proposal</option>
                                <option value="closed_won">Closed Won</option>
                                <option value="closed_lost">Closed Lost</option>
                            </select>
                        </div>
                        <div class="w-full md:flex-1">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Date From</label>
                            <input type="date" id="filterDateFrom" class="w-full border-2 border-white/30 rounded-xl px-4 py-2.5 bg-white/15 backdrop-blur-sm text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" autocomplete="off" onkeydown="if(event.key==='Enter'){event.preventDefault();return false;}">
                        </div>
                        <div class="w-full md:flex-1">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Date To</label>
                            <input type="date" id="filterDateTo" class="w-full border-2 border-white/30 rounded-xl px-4 py-2.5 bg-white/15 backdrop-blur-sm text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" autocomplete="off" onkeydown="if(event.key==='Enter'){event.preventDefault();return false;}">
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto rounded-2xl shadow-2xl glass-card -mx-2 sm:mx-0 mb-6" style="overflow-x: auto; overflow-y: visible;">
                    <table id="reportsTable" class="w-full text-sm bg-white/15 backdrop-blur-sm rounded-2xl whitespace-nowrap" style="min-width: 800px;">
                        <thead class="uppercase bg-white/25 backdrop-blur-sm text-gray-700 rounded-t-2xl border-b-2 border-white/20">
                            <tr>
                                <th class="p-3 font-semibold tracking-widest text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <span>User ID</span>
                                    </div>
                                </th>
                                <th class="p-3 font-semibold tracking-widest text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <span>User Name</span>
                                    </div>
                                </th>
                                <th class="p-3 font-semibold tracking-widest text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <span>Total Deals</span>
                                    </div>
                                </th>
                                <th class="p-3 font-semibold tracking-widest text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <span>Won Deals</span>
                                    </div>
                                </th>
                                <th class="p-3 font-semibold tracking-widest text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <span>Lost Deals</span>
                                    </div>
                                </th>
                                <th class="p-3 font-semibold tracking-widest text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <span>Conversion Rate</span>
                                    </div>
                                </th>
                                <th class="p-3 font-semibold tracking-widest text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <span>Total Revenue</span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white/10 divide-y divide-white/20">
                        </tbody>
                    </table>
                </div>
                <div class="flex flex-col md:flex-row items-center md:justify-between gap-4 mt-6 p-5 glass-card rounded-2xl">
                    <div class="flex flex-wrap items-center gap-3" id="datatableLengthContainer">
                    </div>
                    <div class="flex flex-wrap items-center gap-2 justify-center" id="datatablePaginationContainer">
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <button type="button" id="exportBtn" class="px-4 py-2.5 rounded-xl border-2 border-green-200 bg-gradient-to-r from-green-50 to-green-100 hover:from-green-100 hover:to-green-200 text-green-700 font-semibold text-sm shadow-sm hover:shadow-md transition-all duration-200 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            Export CSV
                        </button>
                        <button type="button" id="resetFilters" class="px-4 py-2.5 rounded-xl border-2 border-gray-200 bg-white hover:bg-gray-50 text-gray-700 font-semibold text-sm shadow-sm hover:shadow-md transition-all duration-200 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            Reset
                        </button>
                    </div>
                </div>
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
    

</div>

<script>

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

let dealsChart = null;
let revenueChart = null;
let chartsLoaded = false;
let dealsChartType = 'pie';
let revenueChartType = 'pie';

// Initialize chart type button active states
function updateChartTypeButtons(chartName, activeType) {
    $('.chart-type-btn[data-chart="' + chartName + '"]').removeClass('active');
    $('.chart-type-btn[data-chart="' + chartName + '"][data-type="' + activeType + '"]').addClass('active');
}

function loadDashboardData() {
    const filterData = {
        date_from: $('#filterDateFrom').val() || '',
        date_to: $('#filterDateTo').val() || '',
        user_id: $('#filterUser').val() || '',
        stage: $('#filterStage').val() || ''
    };

    $.ajax({
        url: '{{ route('crm.reports.dashboard-data') }}',
        method: 'GET',
        data: filterData,
        success: function(response) {
            if (response.success && response.data) {
                const alpineData = getAlpineData();
                if (alpineData && alpineData.__x) {
                    const data = alpineData.__x.$data;
                    data.dashboardData = response.data;
                }
            }
        },
        error: function() {
            console.error('Error loading dashboard data');
        }
    });
}

// Function to create chart based on type
function createChart(ctx, type, labels, data, colors, isRevenue = false) {
    const baseOptions = {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
            duration: 1000
        },
        plugins: {
            legend: {
                position: 'bottom'
            }
        },
        interaction: {
            intersect: false
        }
    };

    // Pie chart specific options
    if (type === 'pie') {
        baseOptions.animation.animateRotate = true;
        baseOptions.animation.animateScale = false;
    }

    // Bar and Line chart specific options
    if (type === 'bar' || type === 'line') {
        baseOptions.scales = {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        if (isRevenue) {
                            return '$' + parseFloat(value).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});
                        }
                        return value;
                    }
                }
            }
        };
    }

    // Tooltip customization for revenue charts
    if (isRevenue) {
        baseOptions.plugins.tooltip = {
            callbacks: {
                label: function(context) {
                    const label = context.label || '';
                    let value;
                    if (type === 'pie') {
                        value = '$' + parseFloat(context.parsed).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    } else {
                        value = '$' + parseFloat(context.parsed.y || context.parsed).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    }
                    return label + ': ' + value;
                }
            }
        };
    }

    const chartData = {
        labels: labels,
        datasets: [{
            data: data,
            backgroundColor: colors,
            borderWidth: 2,
            borderColor: type === 'pie' ? '#fff' : colors.map(c => c.replace('0.6', '1')),
            ...(type === 'line' && {
                borderWidth: 3,
                fill: false,
                tension: 0.4
            }),
            ...(type === 'bar' && {
                borderRadius: 4
            })
        }]
    };

    return new Chart(ctx, {
        type: type,
        data: chartData,
        options: baseOptions
    });
}

function loadCharts() {
    const filterData = {
        date_from: $('#filterDateFrom').val() || '',
        date_to: $('#filterDateTo').val() || '',
        user_id: $('#filterUser').val() || '',
        stage: $('#filterStage').val() || ''
    };

    const dealsCtx = document.getElementById('dealsChart');
    const revenueCtx = document.getElementById('revenueChart');

    if (!dealsCtx || !revenueCtx) {
        return;
    }

    // Destroy existing charts
    if (dealsChart) {
        dealsChart.destroy();
        dealsChart = null;
    }

    if (revenueChart) {
        revenueChart.destroy();
        revenueChart = null;
    }

    let dealsChartCreated = false;
    let revenueChartCreated = false;
    const currentScrollY = window.scrollY;

    // Load Deals Chart
    $.ajax({
        url: '{{ route('crm.reports.chart-data') }}',
        method: 'GET',
        data: Object.assign({}, filterData, { chart_type: 'deals_won_lost' }),
        success: function(response) {
            if (response.success && response.data && dealsCtx) {
                try {
                    dealsChart = createChart(
                        dealsCtx,
                        dealsChartType,
                        response.data.labels,
                        response.data.values,
                        response.data.colors,
                        false
                    );
                    dealsChartCreated = true;
                    window.scrollTo(0, currentScrollY);
                    if (dealsChartCreated && revenueChartCreated) {
                        chartsLoaded = true;
                        window.scrollTo(0, currentScrollY);
                    }
                } catch (e) {
                    console.error('Error creating deals chart:', e);
                    window.scrollTo(0, currentScrollY);
                }
            }
        },
        error: function() {
            console.error('Error loading deals chart data');
            window.scrollTo(0, currentScrollY);
        }
    });

    // Load Revenue Chart
    $.ajax({
        url: '{{ route('crm.reports.chart-data') }}',
        method: 'GET',
        data: Object.assign({}, filterData, { chart_type: 'revenue_by_stage' }),
        success: function(response) {
            if (response.success && response.data && revenueCtx) {
                try {
                    const colors = ['rgba(37, 99, 235, 0.6)', 'rgba(59, 130, 246, 0.6)', 'rgba(251, 191, 36, 0.6)', 'rgba(16, 185, 129, 0.6)', 'rgba(239, 68, 68, 0.6)'];
                    revenueChart = createChart(
                        revenueCtx,
                        revenueChartType,
                        response.data.map(item => item.stage),
                        response.data.map(item => item.revenue),
                        colors.slice(0, response.data.length),
                        true
                    );
                    revenueChartCreated = true;
                    window.scrollTo(0, currentScrollY);
                    if (dealsChartCreated && revenueChartCreated) {
                        chartsLoaded = true;
                        window.scrollTo(0, currentScrollY);
                    }
                } catch (e) {
                    console.error('Error creating revenue chart:', e);
                    window.scrollTo(0, currentScrollY);
                }
            }
        },
        error: function() {
            console.error('Error loading revenue chart data');
            window.scrollTo(0, currentScrollY);
        }
    });
}

function reloadCharts() {
    const currentScrollY = window.scrollY;
    if (dealsChart) {
        dealsChart.destroy();
        dealsChart = null;
    }
    if (revenueChart) {
        revenueChart.destroy();
        revenueChart = null;
    }
    chartsLoaded = false;
    loadCharts();
    setTimeout(function() {
        window.scrollTo(0, currentScrollY);
    }, 100);
}

let chartInitDone = false;
function initCharts() {
    if (chartInitDone || chartsLoaded) {
        return;
    }

    const dealsCtx = document.getElementById('dealsChart');
    const revenueCtx = document.getElementById('revenueChart');

    if (dealsCtx && revenueCtx) {
        chartInitDone = true;
        const currentScrollY = window.scrollY;
        loadCharts();
        setTimeout(function() {
            // Initialize button states after charts load
            if (typeof updateChartTypeButtons === 'function') {
                updateChartTypeButtons('dealsChart', dealsChartType);
                updateChartTypeButtons('revenueChart', revenueChartType);
            }
            window.scrollTo(0, currentScrollY);
        }, 50);
    }
}

$(window).on('load', function() {
    setTimeout(function() {
        initCharts();
    }, 1500);
});

if (document.readyState === 'complete') {
    setTimeout(function() {
        initCharts();
    }, 1500);
} else {
    $(document).ready(function() {
        setTimeout(function() {
            initCharts();
        }, 1500);
    });
}

$(document).ready(function() {
    const initialScrollY = window.scrollY;

    let table = $('#reportsTable').DataTable({
        processing: true,
        serverSide: true,
        scrollX: false,
        scrollY: false,
        ajax: {
            url: '{{ route('crm.reports.datatable') }}',
            data: function(d) {
                d.date_from = $('#filterDateFrom').val() || '';
                d.date_to = $('#filterDateTo').val() || '';
                d.user_id = $('#filterUser').val() || '';
                d.stage = $('#filterStage').val() || '';
            }
        },
        columns: [
            { data: 'user_id', name: 'user_id', render: function(data) {
                return '<span class="font-medium text-gray-900">' + (data || '-') + '</span>';
            }},
            { data: 'user_name', name: 'user_name', render: function(data) {
                return '<span class="text-gray-700">' + (data || '-') + '</span>';
            }},
            { data: 'total_deals', name: 'total_deals', render: function(data) {
                return '<span class="text-gray-700">' + (data || '-') + '</span>';
            }},
            { data: 'won_deals', name: 'won_deals', render: function(data) {
                return '<span class="text-gray-700">' + (data || '-') + '</span>';
            }},
            { data: 'lost_deals', name: 'lost_deals', render: function(data) {
                return '<span class="text-gray-700">' + (data || '-') + '</span>';
            }},
            { data: 'conversion_rate', name: 'conversion_rate', render: function(data) {
                return '<span class="text-gray-700">' + (data || '-') + '</span>';
            }},
            { data: 'total_revenue', name: 'total_revenue', render: function(data) {
                return '<span class="text-gray-700 font-semibold">' + (data || '-') + '</span>';
            }}
        ],
        order: [[0, 'desc']],
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
    
    $('#applyFilters').on('click', function() {
        const currentScrollY = window.scrollY;
        reloadCharts();
        table.ajax.reload(function() {
            window.scrollTo(0, currentScrollY);
        }, false);
    });

    $('#resetFilters').on('click', function() {
        const currentScrollY = window.scrollY;
        $('#filterDateFrom').val('');
        $('#filterDateTo').val('');
        $('#filterUser').val('');
        $('#filterStage').val('');
        table.search('').draw();
        reloadCharts();
        table.ajax.reload(null, false);
        window.scrollTo(0, currentScrollY);
    });
    
    $('#filterDateFrom, #filterDateTo, #filterUser, #filterStage').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            e.stopPropagation();
            $('#applyFilters').click();
        }
    });

    // Set initial active states (will be updated after charts load)
    setTimeout(function() {
        updateChartTypeButtons('dealsChart', dealsChartType);
        updateChartTypeButtons('revenueChart', revenueChartType);
    }, 2000);

    // Chart type button click handlers
    $(document).on('click', '.chart-type-btn', function() {
        const btn = $(this);
        const chartName = btn.data('chart');
        const chartType = btn.data('type');

        if (chartName === 'dealsChart') {
            dealsChartType = chartType;
            updateChartTypeButtons('dealsChart', chartType);
        } else if (chartName === 'revenueChart') {
            revenueChartType = chartType;
            updateChartTypeButtons('revenueChart', chartType);
        }

        // Reload charts with new type
        reloadCharts();
    });

    $('#exportBtn').on('click', function() {
        const filterParams = new URLSearchParams({
            date_from: $('#filterDateFrom').val() || '',
            date_to: $('#filterDateTo').val() || '',
            user_id: $('#filterUser').val() || '',
            stage: $('#filterStage').val() || ''
        });
        
        window.location.href = '{{ route('crm.reports.export') }}?' + filterParams.toString();
    });
});
</script>
</body>
</html>


