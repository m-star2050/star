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
    <title>CRM Pipeline</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            background:
                url('{{ asset('image/Screenshot_16.png') }}') center center/cover no-repeat fixed !important;
            min-height: 100vh;
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
        }
        .glass { backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.35); box-shadow: inset 0 0 0 1px rgba(255,255,255,.15); }
        .sidebar-link{ display:flex; align-items:center; gap:.75rem; color:#0f172a; text-decoration:none; padding:.6rem .9rem; border-radius:.6rem; line-height:1; }
        .sidebar-link:hover{ background: rgba(0,0,0,.06); }
        .sidebar-link svg{ width:20px; height:20px; min-width:20px; min-height:20px; flex-shrink:0; display:block; }
        .sidebar-link span{ line-height:1.2; display:flex; align-items:center; }
        .hdr-wrap{max-width:1120px}
    </style>
</head>
<body>

<div x-data="{mobileMenu:false, open:true, showCreate:false, showEdit:false, showDelete:false, showBulkDelete:false, editId:null, editDeal:'', editStage:'prospect', editValue:0, editOwner:'', editCloseDate:'', editProbability:0, editContact:'', editCompany:'', editNotes:''}" class="relative">
    <!-- Mobile Top Navigation -->
    <div class="lg:hidden fixed top-0 left-0 right-0 z-50 glass rounded-b-2xl p-3 shadow-lg">
        <div class="flex items-center justify-between mb-4 mt-7">
            <div class="text-gray-900 font-extrabold tracking-wide text-sm">WELCOME USER</div>
            <button @click="mobileMenu=!mobileMenu" class="text-gray-900 bg-white/20 border border-white/40 rounded-lg w-10 h-10 flex items-center justify-center hover:bg-white/30">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>
        <!-- Mobile Menu Dropdown -->
        <div x-show="mobileMenu" x-transition class="mt-4 pt-4 border-t border-white/30">
            <nav class="space-y-2">
                <a href="{{ route('crm.contacts.index') }}" class="sidebar-link {{ request()->routeIs('crm.contacts.*') ? 'bg-white/20' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10.707 1.293a1 1 0 00-1.414 0l-7 7A1 1 0 003 10h1v7a1 1 0 001 1h4v-4h2v4h4a1 1 0 001-1v-7h1a1 1 0 00.707-1.707l-7-7z"/></svg>
                    <span>Contacts</span>
                </a>
                <a href="{{ route('crm.leads.index') }}" class="sidebar-link {{ request()->routeIs('crm.leads.*') ? 'bg-white/20' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M3 3h2l.4 2M7 13h8l2-8H5.4M7 13L6 6m1 7l-1 4m8-4l1 4m-5-4v4"/></svg>
                    <span>Leads</span>
                </a>
                <a href="{{ route('crm.tasks.index') }}" class="sidebar-link {{ request()->routeIs('crm.tasks.*') ? 'bg-white/20' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/></svg>
                    <span>Tasks</span>
                </a>
                <a href="{{ route('crm.pipeline.index') }}" class="sidebar-link {{ request()->routeIs('crm.pipeline.*') ? 'bg-white/20' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M3 12v3c0 1.657 3.134 3 7 3s7-1.343 7-3v-3c0 1.657-3.134 3-7 3s-7-1.343-7-3z"/><path d="M3 7v3c0 1.657 3.134 3 7 3s7-1.343 7-3V7c0 1.657-3.134 3-7 3S3 8.657 3 7z"/><path d="M17 5c0 1.657-3.134 3-7 3S3 6.657 3 5s3.134-3 7-3 7 1.343 7 3z"/></svg>
                    <span>Pipeline</span>
                </a>
                <a href="{{ route('crm.reports.index') }}" class="sidebar-link {{ request()->routeIs('crm.reports.*') ? 'bg-white/20' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/></svg>
                    <span>Reports</span>
                </a>
                <a href="{{ route('crm.files.index') }}" class="sidebar-link {{ request()->routeIs('crm.files.*') ? 'bg-white/20' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg>
                    <span>Files</span>
                </a>
            </nav>
        </div>
    </div>

    <!-- Desktop Sidebar -->
    <aside class="hidden lg:flex fixed top-3 left-3 h-[calc(100vh-24px)] glass rounded-2xl p-3 transition-all duration-300 z-40 flex-col" :class="open ? 'w-64' : 'w-16'">
        <div class="flex items-center justify-between mb-4">
            <div class="text-gray-900 font-extrabold tracking-wide flex items-center" :class="open ? 'opacity-100' : 'opacity-0 pointer-events-none'">
                <span class="leading-none">WELCOME USER</span>
            </div>
            <button @click="open=!open" class="text-white bg-white/20 border border-white/40 rounded-full w-7 h-7 flex items-center justify-center hover:bg-white/30 flex-shrink-0" :aria-expanded="open">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="open ? 'M15 19l-7-7 7-7' : 'M9 5l7 7-7 7'"/></svg>
            </button>
        </div>
        <div class="text-gray-900/80 text-xs uppercase tracking-wider mb-2 leading-none" :class="open ? 'opacity-100' : 'opacity-0 pointer-events-none'">General</div>
        <nav class="space-y-1 mt-4">
            <a href="{{ route('crm.contacts.index') }}" class="sidebar-link {{ request()->routeIs('crm.contacts.*') ? 'bg-white/20' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10.707 1.293a1 1 0 00-1.414 0l-7 7A1 1 0 003 10h1v7a1 1 0 001 1h4v-4h2v4h4a1 1 0 001-1v-7h1a1 1 0 00.707-1.707l-7-7z"/></svg>
                <span x-show="open" x-transition>Contacts</span>
            </a>
            <a href="{{ route('crm.leads.index') }}" class="sidebar-link {{ request()->routeIs('crm.leads.*') ? 'bg-white/20' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M3 3h2l.4 2M7 13h8l2-8H5.4M7 13L6 6m1 7l-1 4m8-4l1 4m-5-4v4"/></svg>
                <span x-show="open" x-transition>Leads</span>
            </a>
            <a href="{{ route('crm.tasks.index') }}" class="sidebar-link {{ request()->routeIs('crm.tasks.*') ? 'bg-white/20' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/></svg>
                <span x-show="open" x-transition>Tasks</span>
            </a>
            <a href="{{ route('crm.pipeline.index') }}" class="sidebar-link {{ request()->routeIs('crm.pipeline.*') ? 'bg-white/20' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M3 12v3c0 1.657 3.134 3 7 3s7-1.343 7-3v-3c0 1.657-3.134 3-7 3s-7-1.343-7-3z"/><path d="M3 7v3c0 1.657 3.134 3 7 3s7-1.343 7-3V7c0 1.657-3.134 3-7 3S3 8.657 3 7z"/><path d="M17 5c0 1.657-3.134 3-7 3S3 6.657 3 5s3.134-3 7-3 7 1.343 7 3z"/></svg>
                <span x-show="open" x-transition>Pipeline</span>
            </a>
            <a href="{{ route('crm.reports.index') }}" class="sidebar-link {{ request()->routeIs('crm.reports.*') ? 'bg-white/20' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/></svg>
                <span x-show="open" x-transition>Reports</span>
            </a>
            <a href="{{ route('crm.files.index') }}" class="sidebar-link {{ request()->routeIs('crm.files.*') ? 'bg-white/20' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg>
                <span x-show="open" x-transition>Files</span>
            </a>
        </nav>
    </aside>

    <div class="lg:transition-all lg:duration-300 pt-16 lg:pt-0" :class="{'lg:pl-[280px]': open, 'lg:pl-[88px]': !open}">
        <div class="min-h-screen flex flex-col justify-center items-center px-2 py-8">
            <div class="w-full max-w-6xl mx-auto px-3 md:px-4 py-3">
                <div class="glass w-full rounded-xl px-6 py-3 mb-4 flex items-center justify-between text-white">
                    <div class="text-lg md:text-xl font-semibold tracking-wide">SALES PIPELINE</div>
                    <a href="{{ route('crm.pipeline.kanban') }}" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 rounded-lg text-sm font-semibold transition">
                        Switch to Kanban View
                    </a>
                </div>

                <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-6">
                    <div class="flex items-end gap-3 w-full md:max-w-xl">
                        <button type="button" @click="showCreate=true" class="flex-shrink-0 flex items-center gap-2 px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow-md">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 20 20" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"/></svg>
                            New Deal
                        </button>
                    </div>
                    <div class="flex items-end gap-3 md:ml-auto w-full md:w-auto">
                        <form method="GET" class="flex items-center gap-0 bg-white/30 backdrop-blur-sm rounded-xl px-3 py-2 shadow-inner w-full md:w-80">
                            <label for="mainsearch" class="text-gray-600 px-2 text-base font-medium">Search</label>
                            <span class="inline-flex items-center justify-center pl-2 pr-1"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 103 10.5a7.5 7.5 0 0013.15 6.15z" /></svg></span>
                            <input id="mainsearch" type="text" name="search" value="{{ request('search') }}" class="bg-transparent border-0 outline-none ring-0 focus:ring-0 w-full text-gray-900 placeholder-gray-400 text-base px-2 py-1" placeholder="Deal or Company..." autocomplete="off">
                        </form>
                    </div>
                </div>

                <div class="mb-4">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-4 items-end">
                        <div class="md:col-span-6 flex items-center justify-between">
                            <div class="font-semibold text-xl text-gray-700 ml-1 tracking-wide">Filters</div>
                            <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 text-white shadow hover:bg-blue-700">Apply Filters</button>
                        </div>

                        <div>
                            <select name="stage" class="w-full border rounded-xl px-3 py-2 bg-white/80 text-gray-800 focus:ring-2 focus:ring-blue-400">
                                <option value="">All Stages</option>
                                <option value="prospect" @selected(request('stage')==='prospect')>Prospect</option>
                                <option value="negotiation" @selected(request('stage')==='negotiation')>Negotiation</option>
                                <option value="proposal" @selected(request('stage')==='proposal')>Proposal</option>
                                <option value="closed_won" @selected(request('stage')==='closed_won')>Closed Won</option>
                                <option value="closed_lost" @selected(request('stage')==='closed_lost')>Closed Lost</option>
                            </select>
                        </div>

                        <div>
                            <input type="number" name="owner_user_id" value="{{ request('owner_user_id') }}" class="w-full border rounded-xl px-3 py-2 bg-white/80 text-gray-800 focus:ring-2 focus:ring-blue-400 placeholder-gray-500" placeholder="Owner ID" autocomplete="off">
                        </div>

                        <div>
                            <input type="number" name="value_min" value="{{ request('value_min') }}" class="w-full border rounded-xl px-3 py-2 bg-white/80 text-gray-800 focus:ring-2 focus:ring-blue-400 placeholder-gray-500" placeholder="Min Value" step="0.01" autocomplete="off">
                        </div>

                        <div>
                            <input type="number" name="value_max" value="{{ request('value_max') }}" class="w-full border rounded-xl px-3 py-2 bg-white/80 text-gray-800 focus:ring-2 focus:ring-blue-400 placeholder-gray-500" placeholder="Max Value" step="0.01" autocomplete="off">
                        </div>

                        <div>
                            <input type="date" name="close_date_from" value="{{ request('close_date_from') }}" class="w-full border rounded-xl px-3 py-2 bg-white/80 text-gray-800 focus:ring-2 focus:ring-blue-400" autocomplete="off">
                        </div>

                        <div>
                            <input type="date" name="close_date_to" value="{{ request('close_date_to') }}" class="w-full border rounded-xl px-3 py-2 bg-white/80 text-gray-800 focus:ring-2 focus:ring-blue-400" autocomplete="off">
                        </div>
                    </form>
                </div>

                <form method="POST" action="{{ route('crm.pipeline.bulk-delete') }}" x-ref="bulkForm">
                    @csrf
                    <div class="mb-2 flex items-end gap-2 px-2">
                        <button type="button" class="px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl shadow" @click="showBulkDelete=true">Delete Selected</button>
                        <span class="text-sm text-gray-600 pb-1 ml-4">Click the 'Export' button to download to Excel.
                        </span>
                    </div>

                    <div class="overflow-x-auto rounded-xl shadow-xl glass">
                        <table class="min-w-full text-sm bg-white/20 backdrop-blur-sm border border-white/30 rounded-xl">
                            <thead class="uppercase bg-white/40 text-gray-800 rounded-xl">
                                <tr>
                                    <th class="p-3 text-center"><input type="checkbox" @click="$el.closest('table').querySelectorAll('.row-check').forEach(cb=>cb.checked=$event.target.checked)"></th>
                                    <th class="p-3 font-semibold tracking-widest text-center">{!! sort_link('deal_name', 'Deal Name') !!}</th>
                                    <th class="p-3 font-semibold tracking-widest text-center">{!! sort_link('stage', 'Stage') !!}</th>
                                    <th class="p-3 font-semibold tracking-widest text-center">{!! sort_link('value', 'Value') !!}</th>
                                    <th class="p-3 font-semibold tracking-widest text-center">{!! sort_link('owner_user_id', 'Owner') !!}</th>
                                    <th class="p-3 font-semibold tracking-widest text-center">{!! sort_link('close_date', 'Close Date') !!}</th>
                                    <th class="p-3 font-semibold tracking-widest text-center">{!! sort_link('probability', 'Prob %') !!}</th>
                                    <th class="p-3 font-semibold tracking-widest text-center">Company</th>
                                    <th class="p-3 font-semibold tracking-widest text-center">Contact</th>
                                    <th class="p-3 font-semibold tracking-widest text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($pipelines as $p)
                                <tr class="border-b border-white/30 bg-white/20 hover:bg-white/40 transition">
                                    <td class="p-2 text-center"><input type="checkbox" class="row-check" name="ids[]" value="{{ $p->id }}"></td>
                                    <td class="p-2 text-center font-medium">{{ $p->deal_name }}</td>
                                    <td class="p-2 text-center">
                                        <span class="font-semibold {{ $p->getStageColorClass() }}">
                                            {{ $p->getStageLabel() }}
                                        </span>
                                    </td>
                                    <td class="p-2 text-center font-semibold">${{ number_format($p->value, 2) }}</td>
                                    <td class="p-2 text-center">{{ $p->owner_user_id ? 'User '.$p->owner_user_id : '-' }}</td>
                                    <td class="p-2 text-center">{{ $p->close_date?->format('Y-m-d') ?? '-' }}</td>
                                    <td class="p-2 text-center">{{ $p->probability ?? '-' }}%</td>
                                    <td class="p-2 text-center">{{ $p->company ?? '-' }}</td>
                                    <td class="p-2 text-center">{{ $p->contact?->name ?? '-' }}</td>
                                    <td class="p-2 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <button type="button" @click="editId={{$p->id}}; editDeal='{{$p->deal_name}}'; editStage='{{$p->stage}}'; editValue={{$p->value}}; editOwner='{{$p->owner_user_id}}'; editCloseDate='{{$p->close_date}}'; editProbability={{$p->probability??0}}; editContact='{{$p->contact_id}}'; editCompany='{{$p->company}}'; editNotes='{{addslashes($p->notes)}}'; showEdit=true;" class="inline-flex items-center gap-1 px-3 py-1 rounded-lg border border-blue-400 text-blue-600 hover:bg-blue-50 shadow-sm">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-8.5 8.5a2 2 0 01-.878.515l-3.3.943a.5.5 0 01-.62-.62l.943-3.3a2 2 0 01.515-.878l8.5-8.5z"/></svg>Edit
                                            </button>
                                            <button type="button" @click="editId={{$p->id}}; editDeal='{{$p->deal_name}}'; showDelete=true;" class="inline-flex items-center gap-1 px-3 py-1 rounded-lg border border-red-400 text-red-600 hover:bg-red-50 shadow-sm">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 8a1 1 0 011 1v6a1 1 0 102 0V9a1 1 0 112 0v6a1 1 0 102 0V9a1 1 0 011-1h1a1 1 0 100-2h-1V5a2 2 0 00-2-2H9a2 2 0 00-2 2v1H6a1 1 0 100 2h1zm3-3h2v1H9V5z" clip-rule="evenodd"/></svg>Del
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="p-8 text-center text-gray-600">No deals found.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div x-show="showBulkDelete" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center">
                        <div class="absolute inset-0 bg-black/50" @click="showBulkDelete=false"></div>
                        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                            <div class="text-lg font-semibold mb-3">Delete Selected</div>
                            <p class="text-sm text-gray-600 mb-4">Are you sure you want to delete the selected deals?</p>
                            <div class="flex justify-end gap-2">
                                <button type="button" class="px-4 py-2 rounded-lg border" @click="showBulkDelete=false">Cancel</button>
                                <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 text-white">Delete</button>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="flex flex-col md:flex-row items-center md:justify-between gap-3 p-3">
                    <div class="text-sm text-gray-600">
                        Showing <span class="font-medium">{{ $pipelines->firstItem() ?? 0 }}</span>–<span class="font-medium">{{ $pipelines->lastItem() ?? 0 }}</span> of <span class="font-medium">{{ $pipelines->total() }}</span>
                    </div>
                    <div class="flex gap-1 items-center">
                        @if ($pipelines->onFirstPage())
                            <span class="px-3 py-2 rounded-xl border text-gray-400 cursor-not-allowed">&laquo; Prev</span>
                        @else
                            <a href="{{ $pipelines->previousPageUrl() }}" class="px-3 py-2 rounded-xl border hover:bg-white/40">&laquo; Prev</a>
                        @endif
                        @php
                            $current = $pipelines->currentPage();
                            $last = $pipelines->lastPage();
                            $window = 2;
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
                        @foreach ($pages as $p)
                            @if ($p === '...')
                                <span class="px-3 py-2 text-gray-400 select-none">...</span>
                            @elseif ($p == $current)
                                <span class="px-3 py-2 rounded-xl border bg-blue-600 text-white">{{ $p }}</span>
                            @else
                                <a href="{{ $pipelines->url($p) }}" class="px-3 py-2 rounded-xl border hover:bg-white/60">{{ $p }}</a>
                            @endif
                        @endforeach
                        @if ($pipelines->hasMorePages())
                            <a href="{{ $pipelines->nextPageUrl() }}" class="px-3 py-2 rounded-xl border hover:bg-white/40">Next &raquo;</a>
                        @else
                            <span class="px-3 py-2 rounded-xl border text-gray-400 cursor-not-allowed">Next &raquo;</span>
                        @endif
                    </div>
                    <form method="GET" class="flex items-center gap-2">
                        <label class="text-sm text-gray-600">Items per page</label>
                        <input type="number" name="per_page" min="1" max="100" value="{{ request('per_page', 10) }}" class="w-20 border rounded-xl px-3 py-2 bg-white/60 text-gray-700 shadow-inner">
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        <input type="hidden" name="stage" value="{{ request('stage') }}">
                        <input type="hidden" name="owner_user_id" value="{{ request('owner_user_id') }}">
                        <input type="hidden" name="value_min" value="{{ request('value_min') }}">
                        <input type="hidden" name="value_max" value="{{ request('value_max') }}">
                        <input type="hidden" name="close_date_from" value="{{ request('close_date_from') }}">
                        <input type="hidden" name="close_date_to" value="{{ request('close_date_to') }}">
                        <input type="hidden" name="sort" value="{{ request('sort') }}">
                        <input type="hidden" name="direction" value="{{ request('direction') }}">
                        <button class="px-4 py-2 rounded-xl border hover:bg-white/70 bg-white/40 text-gray-700">Apply</button>
                        <a href="{{ route('crm.pipeline.export', request()->query()) }}" class="px-4 py-2 rounded-xl border bg-green-100 hover:bg-green-200 text-green-700 ml-2">Export</a>
                        <a href="{{ route('crm.pipeline.index') }}" class="px-4 py-2 rounded-xl border bg-gray-100 hover:bg-gray-200 text-gray-600 ml-2">Reset</a>
                    </form>
                </div>

                <!-- Create Deal Modal -->
                <div x-show="showCreate" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 overflow-y-auto" @click.self="showCreate=false">
                    <div class="glass rounded-2xl p-6 w-full max-w-2xl m-4">
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Create New Deal</h3>
                        <form method="POST" action="{{ route('crm.pipeline.store') }}">
                            @csrf
                            <input type="hidden" name="view_mode" value="list">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Deal Name*</label>
                                    <input type="text" name="deal_name" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Stage*</label>
                                    <select name="stage" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
                                        <option value="prospect">Prospect</option>
                                        <option value="negotiation">Negotiation</option>
                                        <option value="proposal">Proposal</option>
                                        <option value="closed_won">Closed Won</option>
                                        <option value="closed_lost">Closed Lost</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Value*</label>
                                    <input type="number" name="value" step="0.01" min="0" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Owner User ID</label>
                                    <input type="number" name="owner_user_id" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Close Date</label>
                                    <input type="date" name="close_date" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Probability (%)</label>
                                    <input type="number" name="probability" min="0" max="100" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Contact ID</label>
                                    <input type="number" name="contact_id" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Company</label>
                                    <input type="text" name="company" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                    <textarea name="notes" rows="3" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400"></textarea>
                                </div>
                            </div>
                            <div class="flex justify-end gap-3 mt-6">
                                <button type="button" @click="showCreate=false" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg">Cancel</button>
                                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">Create Deal</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Edit Deal Modal -->
                <div x-show="showEdit" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 overflow-y-auto" @click.self="showEdit=false">
                    <div class="glass rounded-2xl p-6 w-full max-w-2xl m-4">
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Edit Deal</h3>
                        <form method="POST" :action="`{{ route('crm.pipeline.index') }}/${editId}`">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="view_mode" value="list">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Deal Name*</label>
                                    <input type="text" name="deal_name" x-model="editDeal" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Stage*</label>
                                    <select name="stage" x-model="editStage" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
                                        <option value="prospect">Prospect</option>
                                        <option value="negotiation">Negotiation</option>
                                        <option value="proposal">Proposal</option>
                                        <option value="closed_won">Closed Won</option>
                                        <option value="closed_lost">Closed Lost</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Value*</label>
                                    <input type="number" name="value" x-model="editValue" step="0.01" min="0" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Owner User ID</label>
                                    <input type="number" name="owner_user_id" x-model="editOwner" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Close Date</label>
                                    <input type="date" name="close_date" x-model="editCloseDate" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Probability (%)</label>
                                    <input type="number" name="probability" x-model="editProbability" min="0" max="100" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Contact ID</label>
                                    <input type="number" name="contact_id" x-model="editContact" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Company</label>
                                    <input type="text" name="company" x-model="editCompany" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                    <textarea name="notes" x-model="editNotes" rows="3" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400"></textarea>
                                </div>
                            </div>
                            <div class="flex justify-end gap-3 mt-6">
                                <button type="button" @click="showEdit=false" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg">Cancel</button>
                                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">Update Deal</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Delete Confirmation Modal -->
                <div x-show="showDelete" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="showDelete=false">
                    <div class="glass rounded-2xl p-6 w-full max-w-md">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Confirm Delete</h3>
                        <p class="text-gray-700 mb-2">Are you sure you want to delete this deal?</p>
                        <p class="text-gray-900 font-medium mb-6" x-text="editDeal"></p>
                        <form method="POST" :action="`{{ route('crm.pipeline.index') }}/${editId}`">
                            @csrf
                            @method('DELETE')
                            <div class="flex justify-end gap-3">
                                <button type="button" @click="showDelete=false" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg">Cancel</button>
                                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">Delete</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>

</body>
</html>
