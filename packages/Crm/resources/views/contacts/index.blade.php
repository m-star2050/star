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
    <title>CRM Contacts</title>
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
        .glass { backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.35); box-shadow: inset 0 0 0 1px rgba(255,255,255,.15); }
        .sidebar-link{ display:flex; align-items:center; gap:.75rem; color:#0f172a; text-decoration:none; padding:.6rem .9rem; border-radius:.6rem; line-height:1; }
        .sidebar-link:hover{ background: rgba(0,0,0,.06); }
        .sidebar-link svg{ width:20px; height:20px; min-width:20px; min-height:20px; flex-shrink:0; display:block; }
        .sidebar-link span{ line-height:1.2; display:flex; align-items:center; }
        .hdr-wrap{max-width:1120px}
        
        /* DataTables styling */
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
        #contactsTable td { padding: 0.5rem; text-align: center; }
        #contactsTable th { padding: 0.75rem; text-align: center; }
    </style>
</head>
<body>

<div x-data="{mobileMenu:false, open:true, showCreate:false, showEdit:false, showDelete:false, showBulkDelete:false, editId:null, editName:'', editCompany:'', editEmail:'', editPhone:'', editAssigned:'', editStatus:'active', editTags:'', editNotes:'', showNotification:false, notificationMessage:'', notificationType:'success'}" class="relative">
    <div class="lg:hidden fixed top-0 left-0 right-0 z-50 glass rounded-b-2xl p-3 shadow-lg">
        <div class="flex items-center justify-between pt-40">
            <div class="text-gray-900 font-extrabold tracking-wide text-sm">WELCOME USER</div>
            <button @click="mobileMenu=!mobileMenu" class="text-gray-900 bg-white/20 border border-white/40 rounded-lg w-10 h-10 flex items-center justify-center hover:bg-white/30">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>
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
                    <div class="text-lg md:text-xl font-semibold tracking-wide">CONTACTS</div>
                </div>
                <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-6">
                    <div class="flex items-end gap-6 w-full md:max-w-xl">
                        <button type="button" @click="showCreate=true" class="flex-shrink-0 flex items-center gap-2 px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow-md">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 20 20" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"/></svg>
                            New Contact
                        </button>
                    </div>
                    <div class="flex items-end gap-3 md:ml-auto w-full md:w-auto" id="datatableSearchContainer">
                        <!-- DataTables search will be inserted here -->
                    </div>
                </div>
                <div class="mb-4">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                        <div class="md:col-span-5 flex items-center justify-between">
                            <div class="font-semibold text-xl text-gray-700 ml-1 tracking-wide">Filter</div>
                            <button type="button" id="applyFilters" class="px-5 py-2 rounded-xl bg-blue-600 text-white shadow hover:bg-blue-700">Apply Filters</button>
                        </div>
                        <div>
                            <input type="text" id="filterCompany" class="w-full border rounded-xl px-3 py-2 bg-transparent text-gray-800 focus:ring-2 focus:ring-blue-400 placeholder-gray-500" autocomplete="off" placeholder="Company">
                        </div>
                        <div>
                            <input type="number" id="filterAssigned" class="w-full border rounded-xl px-3 py-2 bg-transparent text-gray-800 focus:ring-2 focus:ring-blue-400 placeholder-gray-500" autocomplete="off" placeholder="Assigned User">
                        </div>
                        <div>
                            <select id="filterStatus" class="w-full border rounded-xl px-3 py-2 bg-transparent text-gray-800 focus:ring-2 focus:ring-blue-400">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                        <div>
                            <input type="date" id="filterCreatedFrom" class="w-full border rounded-xl px-3 py-2 bg-transparent text-gray-800 focus:ring-2 focus:ring-blue-400" autocomplete="off">
                        </div>
                        <div>
                            <input type="date" id="filterCreatedTo" class="w-full border rounded-xl px-3 py-2 bg-transparent text-gray-800 focus:ring-2 focus:ring-blue-400" autocomplete="off">
                        </div>
                    </div>
                </div>
                <div class="mb-2 flex flex-col sm:flex-row sm:items-end gap-2 px-2">
                    <button type="button" id="bulkDeleteBtn" class="px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl shadow text-sm">Delete Selected</button>
                    <span class="text-xs sm:text-sm text-gray-500 pb-1">Click the 'Export' button to download to Excel.</span>
                </div>
                <div class="overflow-x-auto rounded-xl shadow-xl glass -mx-2 sm:mx-0">
                    <table id="contactsTable" class="min-w-full text-xs sm:text-sm bg-white/20 backdrop-blur-sm border border-white/30 rounded-xl whitespace-nowrap">
                        <thead class="uppercase bg-white/40 text-gray-800 rounded-xl">
                            <tr>
                                <th class="p-3 text-center"><input type="checkbox" id="selectAll"></th>
                                <th class="p-3 font-semibold tracking-widest text-center">Name</th>
                                <th class="p-3 font-semibold tracking-widest text-center">Company</th>
                                <th class="p-3 font-semibold tracking-widest text-center">Email</th>
                                <th class="p-3 font-semibold tracking-widest text-center">Phone</th>
                                <th class="p-3 font-semibold tracking-widest text-center">Assigned</th>
                                <th class="p-3 font-semibold tracking-widest text-center">Tags</th>
                                <th class="p-3 font-semibold tracking-widest text-center">Created</th>
                                <th class="p-3 font-semibold tracking-widest text-center">Status</th>
                                <th class="p-3 font-semibold tracking-widest text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
    <div x-show="showBulkDelete" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" @click="showBulkDelete=false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-4 sm:p-6">
            <div class="text-lg font-semibold mb-3">Delete Selected</div>
            <p class="text-sm text-gray-600 mb-4">Are you sure you want to delete the selected contacts?</p>
            <div class="flex justify-end gap-2">
                <button type="button" class="px-4 py-2 rounded-lg border" @click="showBulkDelete=false">Cancel</button>
                <button type="button" id="confirmBulkDelete" class="px-4 py-2 rounded-lg bg-red-600 text-white">Delete</button>
            </div>
        </div>
    </div>
                <div class="flex flex-col md:flex-row items-center md:justify-between gap-3 p-3 text-xs sm:text-sm">
                    <div class="flex flex-wrap items-center gap-3" id="datatableLengthContainer">
                        <!-- DataTables length selector and info will be inserted here -->
                    </div>
                    <div class="flex flex-wrap items-center gap-2 justify-center" id="datatablePaginationContainer">
                        <!-- DataTables pagination will be inserted here -->
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('crm.contacts.export', request()->query()) }}" class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl border bg-green-100 hover:bg-green-200 text-green-700 text-xs sm:text-sm">Export</a>
                        <button type="button" id="resetFilters" class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl border bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs sm:text-sm">Reset</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div x-show="showCreate" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" @click="showCreate=false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-xl p-4 sm:p-6 max-h-[90vh] overflow-y-auto">
            <div class="text-lg font-semibold mb-4">Create Contact</div>
            <form id="createForm" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf
                <input name="name" id="createName" class="border rounded-lg px-3 py-2" placeholder="Name" required>
                <input name="company" id="createCompany" class="border rounded-lg px-3 py-2" placeholder="Company">
                <input name="email" id="createEmail" type="email" class="border rounded-lg px-3 py-2" placeholder="Email">
                <input name="phone" id="createPhone" class="border rounded-lg px-3 py-2" placeholder="Phone">
                <input name="assigned_user_id" id="createAssigned" type="number" class="border rounded-lg px-3 py-2" placeholder="Assigned User ID">
                <select name="status" id="createStatus" class="border rounded-lg px-3 py-2">
                    <option value="active">Active</option>
                    <option value="archived">Archived</option>
                </select>
                <input name="tags[]" id="createTags" class="border rounded-lg px-3 py-2 md:col-span-2" placeholder="Tags (comma separated)">
                <textarea name="notes" id="createNotes" class="border rounded-lg px-3 py-2 md:col-span-2" rows="3" placeholder="Notes"></textarea>
                <div class="md:col-span-2 flex justify-end gap-2 mt-2">
                    <button type="button" class="px-4 py-2 rounded-lg border" @click="showCreate=false">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white">Create</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="showEdit" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" @click="showEdit=false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-xl p-4 sm:p-6 max-h-[90vh] overflow-y-auto">
            <div class="text-lg font-semibold mb-4">Edit Contact</div>
            <form id="editForm" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf
                @method('PUT')
                <input name="name" id="editName" class="border rounded-lg px-3 py-2" placeholder="Name" x-model="editName" required>
                <input name="company" id="editCompany" class="border rounded-lg px-3 py-2" placeholder="Company" x-model="editCompany">
                <input name="email" id="editEmail" type="email" class="border rounded-lg px-3 py-2" placeholder="Email" x-model="editEmail">
                <input name="phone" id="editPhone" class="border rounded-lg px-3 py-2" placeholder="Phone" x-model="editPhone">
                <input name="assigned_user_id" id="editAssigned" type="number" class="border rounded-lg px-3 py-2" placeholder="Assigned User ID" x-model="editAssigned">
                <select name="status" id="editStatus" class="border rounded-lg px-3 py-2" x-model="editStatus">
                    <option value="active">Active</option>
                    <option value="archived">Archived</option>
                </select>
                <input name="tags[]" id="editTags" class="border rounded-lg px-3 py-2 md:col-span-2" placeholder="Tags (comma separated)" x-model="editTags">
                <textarea name="notes" id="editNotes" class="border rounded-lg px-3 py-2 md:col-span-2" rows="3" placeholder="Notes" x-model="editNotes"></textarea>
                <div class="md:col-span-2 flex justify-end gap-2 mt-2">
                    <button type="button" class="px-4 py-2 rounded-lg border" @click="showEdit=false">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white">Save</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="showDelete" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" @click="showDelete=false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-4 sm:p-6">
            <div class="text-lg font-semibold mb-3">Delete Contact</div>
            <p class="text-sm text-gray-600 mb-4">Are you sure you want to delete this contact?</p>
            <div class="flex justify-end gap-2">
                <button type="button" class="px-4 py-2 rounded-lg border" @click="showDelete=false">Cancel</button>
                <button type="button" id="confirmDelete" class="px-4 py-2 rounded-lg bg-red-600 text-white">Delete</button>
            </div>
        </div>
    </div>

    <!-- Notification Modal -->
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
// Setup CSRF token for all AJAX requests
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(document).ready(function() {
    // Initialize DataTables
    let table = $('#contactsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('crm.contacts.datatable') }}',
            data: function(d) {
                // Always send filter values (even if empty) so clearing filters works
                d.company = $('#filterCompany').val() || '';
                d.assigned_user_id = $('#filterAssigned').val() || '';
                d.status = $('#filterStatus').val() || '';
                d.created_from = $('#filterCreatedFrom').val() || '';
                d.created_to = $('#filterCreatedTo').val() || '';
            }
        },
        columns: [
            { data: 'id', name: 'id', orderable: false, searchable: false, render: function(data) {
                return '<input type="checkbox" class="row-check rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" value="' + data + '">';
            }},
            { data: 'name', name: 'name', render: function(data) {
                return '<span class="font-medium text-gray-900">' + (data || '-') + '</span>';
            }},
            { data: 'company', name: 'company', render: function(data) {
                return '<span class="text-gray-700">' + (data || '-') + '</span>';
            }},
            { data: 'email', name: 'email', render: function(data) {
                return '<span class="text-gray-700">' + (data || '-') + '</span>';
            }},
            { data: 'phone', name: 'phone', render: function(data) {
                return '<span class="text-gray-700">' + (data || '-') + '</span>';
            }},
            { data: 'assigned', name: 'assigned_user_id', render: function(data) {
                return '<span class="text-gray-700">' + (data || '-') + '</span>';
            }},
            { data: 'tags', name: 'tags', render: function(data) {
                return '<span class="text-gray-700">' + (data || '-') + '</span>';
            }},
            { data: 'created_at', name: 'created_at', render: function(data) {
                return '<span class="text-gray-700">' + (data || '-') + '</span>';
            }},
            { data: 'status_html', name: 'status', orderable: false, searchable: false },
            { data: 'actions_html', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[7, 'desc']],
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
            // Move search to top right container and style it
            const searchContainer = $('#datatableSearchContainer');
            const searchInput = $('.dataTables_filter');
            if (searchContainer.length && searchInput.length) {
                // Get the input element before moving
                const input = searchInput.find('input').first();
                
                // Create wrapper with styling
                const wrapper = $('<div class="flex items-center gap-2 bg-white/30 backdrop-blur-sm rounded-xl px-3 py-2 shadow-inner w-full md:w-80"></div>');
                wrapper.append('<label class="text-gray-600 px-2 text-base font-medium">Search</label>');
                wrapper.append('<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 103 10.5a7.5 7.5 0 0013.15 6.15z" /></svg>');
                
                // Style the search input
                input.addClass('bg-transparent border-0 outline-none ring-0 focus:ring-0 w-full text-gray-900 placeholder-gray-400 text-base px-2 py-1');
                input.attr('placeholder', '');
                
                // Remove the label and move input into wrapper
                searchInput.find('label').remove();
                wrapper.append(input);
                searchInput.html(wrapper);
                searchInput.appendTo(searchContainer);
                
                // Ensure DataTables search still works after moving
                // We need to manually trigger search since moving the DOM breaks DataTables handlers
                let searchTimeout;
                input.off('keyup.search input.search').on('keyup.search input.search', function() {
                    clearTimeout(searchTimeout);
                    const self = this;
                    searchTimeout = setTimeout(function() {
                        table.search(self.value).draw();
                    }, 300); // Debounce for 300ms
                });
                
                // Also handle on input for better responsiveness
                input.on('input', function() {
                    if (this.value === '') {
                        table.search('').draw();
                    }
                });
            }
            
            // Move length selector and info to left container
            const lengthContainer = $('#datatableLengthContainer');
            const lengthSelect = $('.dataTables_length');
            const info = $('.dataTables_info');
            if (lengthContainer.length) {
                if (lengthSelect.length) {
                    lengthSelect.appendTo(lengthContainer);
                    lengthSelect.css('margin', '0');
                    // Increase font size for label text
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
            
            // Move pagination to center container
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
                // Ensure pagination buttons have proper sizing
                pagination.find('.paginate_button').css({
                    'font-size': '0.875rem',
                    'font-weight': '500',
                    'padding': '0.5rem 0.875rem',
                    'min-height': '2.25rem'
                });
            }
            
            // Hide the default top section
            $('.dataTables_wrapper .top').hide();
        },
        drawCallback: function() {
            // Ensure pagination is properly styled and positioned after each draw
            const pagination = $('.dataTables_paginate');
            if (pagination.length) {
                const paginationContainer = $('#datatablePaginationContainer');
                if (paginationContainer.length && pagination.parent()[0] !== paginationContainer[0]) {
                    pagination.appendTo(paginationContainer);
                }
                
                // Re-apply pagination button styles
                pagination.find('.paginate_button').css({
                    'font-size': '0.875rem',
                    'font-weight': '500',
                    'padding': '0.5rem 0.875rem',
                    'min-height': '2.25rem'
                });
            }
            
            // Ensure length selector and info stay in the left container
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

    // Function to show notification modal
    function showNotification(message, type = 'success') {
        const alpineData = getAlpineData();
        if (alpineData && alpineData.__x) {
            const data = alpineData.__x.$data;
            data.notificationMessage = message;
            data.notificationType = type;
            data.showNotification = true;
        }
        // Directly show the modal as fallback
        const notificationModal = $('[x-show="showNotification"]');
        if (notificationModal.length) {
            notificationModal[0].style.display = 'flex';
        }
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            if (alpineData && alpineData.__x) {
                alpineData.__x.$data.showNotification = false;
            }
            if (notificationModal.length) {
                notificationModal[0].style.display = 'none';
            }
        }, 5000);
    }

    // Function to get Alpine.js instance
    function getAlpineData() {
        return document.querySelector('[x-data]');
    }

    // Store current contact ID for edit/delete
    let currentContactId = null;

    // Handle edit button clicks using event delegation
    $(document).on('click', '.edit-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const btn = $(this);
        currentContactId = btn.data('id');
        
        if (!currentContactId) {
            showNotification('Contact ID not found.', 'error');
            return;
        }
        
        // Update form fields directly via jQuery
        $('#editName').val(btn.data('name') || '');
        $('#editCompany').val(btn.data('company') || '');
        $('#editEmail').val(btn.data('email') || '');
        $('#editPhone').val(btn.data('phone') || '');
        $('#editAssigned').val(btn.data('assigned') || '');
        $('#editStatus').val(btn.data('status') || 'active');
        $('#editTags').val(btn.data('tags') || '');
        $('#editNotes').val(btn.data('notes') || '');
        
        // Update Alpine.js data
        const alpineData = getAlpineData();
        if (alpineData && alpineData.__x) {
            const data = alpineData.__x.$data;
            data.editId = currentContactId;
            data.editName = btn.data('name') || '';
            data.editCompany = btn.data('company') || '';
            data.editEmail = btn.data('email') || '';
            data.editPhone = btn.data('phone') || '';
            data.editAssigned = btn.data('assigned') || '';
            data.editStatus = btn.data('status') || 'active';
            data.editTags = btn.data('tags') || '';
            data.editNotes = btn.data('notes') || '';
            data.showEdit = true;
        }
        
        // Remove inline style to let Alpine.js control visibility
        const editModal = $('[x-show="showEdit"]');
        if (editModal.length) {
            editModal[0].style.display = '';
        }
    });
    
    // Handle delete button clicks using event delegation
    $(document).on('click', '.delete-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const btn = $(this);
        currentContactId = btn.data('id');
        
        if (!currentContactId) {
            showNotification('Contact ID not found.', 'error');
            return;
        }
        
        // Update Alpine.js data
        const alpineData = getAlpineData();
        if (alpineData && alpineData.__x) {
            const data = alpineData.__x.$data;
            data.editId = currentContactId;
            data.showDelete = true;
        }
        
        // Remove inline style to let Alpine.js control visibility
        const deleteModal = $('[x-show="showDelete"]');
        if (deleteModal.length) {
            deleteModal[0].style.display = '';
        }
    });

    // Create form submission
    $('#createForm').on('submit', function(e) {
        e.preventDefault();
        const formData = {
            _token: '{{ csrf_token() }}',
            name: $('#createName').val(),
            company: $('#createCompany').val(),
            email: $('#createEmail').val(),
            phone: $('#createPhone').val(),
            assigned_user_id: $('#createAssigned').val(),
            status: $('#createStatus').val(),
            tags: [$('#createTags').val()],
            notes: $('#createNotes').val()
        };
        
        $.ajax({
            url: '{{ route('crm.contacts.store') }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                table.ajax.reload();
                $('#createForm')[0].reset();
                const alpineData = getAlpineData();
                if (alpineData && alpineData.__x) {
                    alpineData.__x.$data.showCreate = false;
                }
                // Remove inline style to let Alpine.js control visibility
                const createModal = $('[x-show="showCreate"]');
                if (createModal.length) {
                    createModal[0].style.display = '';
                }
                showNotification('Contact created successfully.', 'success');
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    let errors = Object.values(xhr.responseJSON.errors).flat();
                    showNotification('Validation errors:\n' + errors.join('\n'), 'error');
                } else {
                    showNotification('Error creating contact.', 'error');
                }
            }
        });
    });

    // Edit form submission
    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        const alpineData = getAlpineData();
        if (alpineData && alpineData.__x) {
            currentContactId = alpineData.__x.$data.editId || currentContactId;
        }
        if (!currentContactId) {
            showNotification('Contact ID not found.', 'error');
            return;
        }
        
        const formData = {
            _token: '{{ csrf_token() }}',
            _method: 'PUT',
            name: $('#editName').val(),
            company: $('#editCompany').val(),
            email: $('#editEmail').val(),
            phone: $('#editPhone').val(),
            assigned_user_id: $('#editAssigned').val(),
            status: $('#editStatus').val(),
            tags: [$('#editTags').val()],
            notes: $('#editNotes').val()
        };
        
        $.ajax({
            url: '{{ route('crm.contacts.update', '__ID__') }}'.replace('__ID__', currentContactId),
            method: 'POST',
            data: formData,
            success: function(response) {
                table.ajax.reload();
                if (alpineData && alpineData.__x) {
                    alpineData.__x.$data.showEdit = false;
                }
                // Remove inline style to let Alpine.js control visibility
                const editModal = $('[x-show="showEdit"]');
                if (editModal.length) {
                    editModal[0].style.display = '';
                }
                showNotification('Contact updated successfully.', 'success');
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    let errors = Object.values(xhr.responseJSON.errors).flat();
                    showNotification('Validation errors:\n' + errors.join('\n'), 'error');
                } else {
                    showNotification('Error updating contact.', 'error');
                }
            }
        });
    });

    // Delete confirmation
    $('#confirmDelete').on('click', function() {
        const alpineData = getAlpineData();
        if (alpineData && alpineData.__x) {
            currentContactId = alpineData.__x.$data.editId || currentContactId;
        }
        if (!currentContactId) {
            showNotification('Contact ID not found.', 'error');
            return;
        }
        
        $.ajax({
            url: '{{ route('crm.contacts.destroy', '__ID__') }}'.replace('__ID__', currentContactId),
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                _method: 'DELETE'
            },
            success: function(response) {
                table.ajax.reload();
                if (alpineData && alpineData.__x) {
                    alpineData.__x.$data.showDelete = false;
                }
                // Remove inline style to let Alpine.js control visibility
                const deleteModal = $('[x-show="showDelete"]');
                if (deleteModal.length) {
                    deleteModal[0].style.display = '';
                }
                currentContactId = null;
                showNotification('Contact deleted successfully.', 'success');
            },
            error: function() {
                showNotification('Error deleting contact.', 'error');
            }
        });
    });

    // Bulk delete
    let currentBulkDeleteIds = [];
    $('#bulkDeleteBtn').on('click', function() {
        const selectedIds = $('.row-check:checked').map(function() {
            return $(this).val();
        }).get();
        
        if (selectedIds.length === 0) {
            showNotification('Please select at least one contact to delete.', 'error');
            return;
        }
        
        currentBulkDeleteIds = selectedIds;
        const alpineData = getAlpineData();
        if (alpineData && alpineData.__x) {
            alpineData.__x.$data.showBulkDelete = true;
        }
        
        // Remove inline style to let Alpine.js control visibility
        const bulkDeleteModal = $('[x-show="showBulkDelete"]');
        if (bulkDeleteModal.length) {
            bulkDeleteModal[0].style.display = '';
        }
    });

    $('#confirmBulkDelete').on('click', function() {
        $.ajax({
            url: '{{ route('crm.contacts.bulk-delete') }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                ids: currentBulkDeleteIds
            },
            success: function(response) {
                table.ajax.reload();
                const alpineData = getAlpineData();
                if (alpineData && alpineData.__x) {
                    alpineData.__x.$data.showBulkDelete = false;
                }
                // Remove inline style to let Alpine.js control visibility
                const bulkDeleteModal = $('[x-show="showBulkDelete"]');
                if (bulkDeleteModal.length) {
                    bulkDeleteModal[0].style.display = '';
                }
                showNotification('Selected contacts deleted successfully.', 'success');
            },
            error: function() {
                showNotification('Error deleting contacts.', 'error');
            }
        });
    });

    // Select all checkbox
    $('#selectAll').on('click', function() {
        $('.row-check').prop('checked', $(this).prop('checked'));
    });

    // Apply filters - reload table with current filter values
    $('#applyFilters').on('click', function() {
        // Force reload with current filter values
        table.ajax.reload(function() {
            // Callback after reload
        }, false); // false = don't reset pagination
    });

    // Reset filters
    $('#resetFilters').on('click', function() {
        $('#filterCompany').val('');
        $('#filterAssigned').val('');
        $('#filterStatus').val('');
        $('#filterCreatedFrom').val('');
        $('#filterCreatedTo').val('');
        // Clear DataTables search
        table.search('').draw();
        // Reload table
        table.ajax.reload(null, false);
    });
    
    // Allow Enter key to trigger filter
    $('#filterCompany, #filterAssigned, #filterStatus, #filterCreatedFrom, #filterCreatedTo').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#applyFilters').click();
        }
    });
});
</script>
</body>
</html>

