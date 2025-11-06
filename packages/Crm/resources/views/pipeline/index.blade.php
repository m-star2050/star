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
        #pipelineTable td { 
            padding: 0.875rem 0.75rem !important; 
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.15);
            color: #1f2937;
            font-size: 0.875rem;
            background-color: rgba(255,255,255,0.05);
        }
        #pipelineTable th { 
            padding: 1rem 0.75rem !important; 
            text-align: center;
            font-weight: 600;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: #374151;
        }
        
        #pipelineTable tbody tr:hover {
            background-color: rgba(255,255,255,0.15);
        }
        
        #pipelineTable tbody tr {
            transition: background-color 0.15s ease;
        }
        
        #contactsTable th:nth-child(3),
        #contactsTable td:nth-child(3),
        #contactsTable th:nth-child(4),
        #contactsTable td:nth-child(4),
        #contactsTable th:nth-child(5),
        #contactsTable td:nth-child(5),
        #contactsTable th:nth-child(6),
        #contactsTable td:nth-child(6),
        #contactsTable th:nth-child(7),
        #contactsTable td:nth-child(7),
        #contactsTable th:nth-child(8),
        #contactsTable td:nth-child(8) {
            padding-left: 0.25rem !important;
            padding-right: 0.25rem !important;
        }
        
        #pipelineTable {
            width: 100% !important;
            table-layout: auto;
            min-width: 1200px !important;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        #contactsTable th,
        #pipelineTable td {
            white-space: nowrap;
        }
        
        .overflow-x-auto {
            overflow-x: auto !important;
            overflow-y: visible !important;
        }
        
        #contactsTable th .flex.items-center {
            justify-content: center;
        }
        
        #contactsTable th.sorting,
        #contactsTable th.sorting_asc,
        #contactsTable th.sorting_desc {
            position: relative;
            padding-right: 2rem !important;
        }
        
        #contactsTable th.sorting:before,
        #contactsTable th.sorting:after,
        #contactsTable th.sorting_asc:before,
        #contactsTable th.sorting_asc:after,
        #contactsTable th.sorting_desc:before,
        #contactsTable th.sorting_desc:after {
            display: none !important;
        }
        
        #contactsTable th.sorting::after {
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
        
        #contactsTable th.sorting::before {
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
        
        #contactsTable th.sorting_asc::after {
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
        
        #contactsTable th.sorting_asc::before {
            display: none;
        }
        
        #contactsTable th.sorting_desc::before {
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
        
        #pipelineTable th.sorting_desc::after {
            display: none;
        }
        
        #pipelineTable th.sorting,
        #pipelineTable th.sorting_asc,
        #pipelineTable th.sorting_desc {
            white-space: nowrap;
        }
        .kanban-column {
            min-height: 400px;
            background: rgba(255,255,255,0.15);
            border-radius: 0.75rem;
            padding: 1rem;
        }
        .kanban-card {
            background: rgba(255,255,255,0.9);
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 0.75rem;
            cursor: move;
            transition: all 0.2s ease;
            border: 1px solid rgba(0,0,0,0.1);
        }
        .kanban-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }
        .kanban-card.dragging {
            opacity: 0.5;
        }
        .kanban-column.drag-over {
            background: rgba(59, 130, 246, 0.2);
            border: 2px dashed rgba(59, 130, 246, 0.5);
        }
    </style>
</head>
<body>

<div x-data="{mobileMenu:false, open:true, showCreate:false, showEdit:false, showDelete:false, showBulkDelete:false, editId:null, editDeal:'', editStage:'prospect', editValue:'', editOwner:'', editCloseDate:'', editProbability:'', editContact:'', editCompany:'', editNotes:'', showNotification:false, notificationMessage:'', notificationType:'success', wasCreateOpen:false, viewMode:'list', kanbanData:{}, draggedCard:null, draggedFromStage:null, getStageLabel(stage) { const labels = {'prospect': 'Prospect', 'negotiation': 'Negotiation', 'proposal': 'Proposal', 'closed_won': 'Closed Won', 'closed_lost': 'Closed Lost'}; return labels[stage] || stage; }, handleDragStart(event, card, stage) { if (typeof window.handleDragStart === 'function') window.handleDragStart(event, card, stage); }, handleDragEnd(event) { if (typeof window.handleDragEnd === 'function') window.handleDragEnd(event); }, handleDrop(event, stage) { if (typeof window.handleDrop === 'function') window.handleDrop(event, stage); }, editDealFromKanban(dealId) { if (typeof window.editDealFromKanban === 'function') window.editDealFromKanban(dealId); }, deleteDealFromKanban(dealId) { if (typeof window.deleteDealFromKanban === 'function') window.deleteDealFromKanban(dealId); }}" 
     x-init="$watch('showCreate', value => { if (value && !wasCreateOpen) { setTimeout(() => { const form = document.getElementById('createForm'); if (form) form.reset(); const stage = document.getElementById('createStage'); if (stage) stage.value = 'prospect'; const btn = document.getElementById('createSubmitBtn'); if (btn) { btn.disabled = false; btn.textContent = 'Create Deal'; } } }, 100); } wasCreateOpen = value; }); $watch('viewMode', value => { if (value === 'kanban' && Object.keys(kanbanData).length === 0) { setTimeout(() => { if (typeof loadKanbanData === 'function') loadKanbanData(); }, 100); } });" 
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
                    <div class="text-2xl md:text-3xl font-bold text-gray-800 tracking-tight">Pipeline</div>
                    <div class="text-sm text-gray-500 font-medium">Manage your sales pipeline and deals</div>
                </div>
                <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-6">
                    <div class="flex items-end gap-6 w-full md:max-w-xl">
                        <button type="button" id="newDealBtn" @click="showCreate=true" class="flex-shrink-0 flex items-center gap-2.5 px-6 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-[1.02]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 20 20" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"/></svg>
                            New Deal
                        </button>
                        <button type="button" @click="viewMode = viewMode === 'list' ? 'kanban' : 'list'; if (viewMode === 'kanban' && typeof loadKanbanData === 'function') { setTimeout(() => loadKanbanData(), 100); }" class="flex-shrink-0 flex items-center gap-2.5 px-6 py-3 rounded-xl bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white font-semibold shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-[1.02]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                            <span x-text="viewMode === 'list' ? 'Kanban View' : 'List View'"></span>
                        </button>
                    </div>
                    <div class="flex items-end gap-3 md:ml-auto w-full md:w-auto" id="datatableSearchContainer">
                    </div>
                </div>
                <div class="mb-6 glass-card rounded-2xl p-5">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                        <div class="md:col-span-5 flex items-center justify-between mb-2">
                            <div class="font-bold text-lg text-gray-800 tracking-tight flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                                Filters
                        </div>
                            <button type="button" id="applyFilters" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold shadow-md hover:shadow-lg transition-all duration-200 hover:scale-[1.02]">Apply Filters</button>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Stage</label>
                            <select id="filterStage" class="w-full border-2 border-white/30 rounded-xl px-4 py-2.5 bg-white/15 backdrop-blur-sm text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                                <option value="">All Stages</option>
                                <option value="prospect">Prospect</option>
                                <option value="negotiation">Negotiation</option>
                                <option value="proposal">Proposal</option>
                                <option value="closed_won">Closed Won</option>
                                <option value="closed_lost">Closed Lost</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Owner</label>
                            <input type="number" id="filterOwner" class="w-full border-2 border-white/30 rounded-xl px-4 py-2.5 bg-white/15 backdrop-blur-sm text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400 transition-all duration-200" autocomplete="off" placeholder="User ID">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Value Min</label>
                            <input type="number" id="filterValueMin" step="0.01" class="w-full border-2 border-white/30 rounded-xl px-4 py-2.5 bg-white/15 backdrop-blur-sm text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400 transition-all duration-200" autocomplete="off" placeholder="Min value">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Value Max</label>
                            <input type="number" id="filterValueMax" step="0.01" class="w-full border-2 border-white/30 rounded-xl px-4 py-2.5 bg-white/15 backdrop-blur-sm text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400 transition-all duration-200" autocomplete="off" placeholder="Max value">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Probability</label>
                            <input type="number" id="filterProbability" min="0" max="100" class="w-full border-2 border-white/30 rounded-xl px-4 py-2.5 bg-white/15 backdrop-blur-sm text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400 transition-all duration-200" autocomplete="off" placeholder="Min %">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Close Date From</label>
                            <input type="date" id="filterCloseDateFrom" class="w-full border-2 border-white/30 rounded-xl px-4 py-2.5 bg-white/15 backdrop-blur-sm text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" autocomplete="off">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Close Date To</label>
                            <input type="date" id="filterCloseDateTo" class="w-full border-2 border-white/30 rounded-xl px-4 py-2.5 bg-white/15 backdrop-blur-sm text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" autocomplete="off">
                        </div>
                </div>
                </div>
                <div class="mb-4 flex flex-col sm:flex-row sm:items-center gap-3">
                    <button type="button" id="bulkDeleteBtn" class="px-4 py-2.5 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white rounded-xl shadow-md hover:shadow-lg transition-all duration-200 text-sm font-semibold flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        Delete Selected
                    </button>
                    <span class="text-sm text-gray-600 font-medium flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        Click 'Export' to download to Excel
                        </span>
                    </div>
                <div class="overflow-x-auto rounded-2xl shadow-2xl glass-card -mx-2 sm:mx-0" style="overflow-x: auto; overflow-y: visible;">
                    <table id="pipelineTable" class="w-full text-sm bg-white/15 backdrop-blur-sm rounded-2xl whitespace-nowrap" style="min-width: 1200px;" x-show="viewMode === 'list'">
                        <thead class="uppercase bg-white/25 backdrop-blur-sm text-gray-700 rounded-t-2xl border-b-2 border-white/20">
                            <tr>
                                <th class="p-3 text-center"><input type="checkbox" id="selectAll"></th>
                                <th class="p-3 font-semibold tracking-widest text-center">
                                        <div class="flex items-center justify-center gap-2">
                                        <span>Deal Name</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                                        </svg>
                                        </div>
                                </th>
                                <th class="p-3 font-semibold tracking-widest text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <span>Stage</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </th>
                                <th class="p-3 font-semibold tracking-widest text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <span>Value</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </th>
                                <th class="p-3 font-semibold tracking-widest text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <span>Owner</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                                        </svg>
                                    </div>
                                </th>
                                <th class="p-3 font-semibold tracking-widest text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <span>Close Date</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </th>
                                <th class="p-3 font-semibold tracking-widest text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <span>Probability</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </th>
                                <th class="p-3 font-semibold tracking-widest text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <span>Company</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </th>
                                <th class="p-3 font-semibold tracking-widest text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <span>Contact</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                                        </svg>
                                    </div>
                                </th>
                                <th class="p-3 font-semibold tracking-widest text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <span>Created</span>
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
                <div x-show="viewMode === 'kanban'" class="overflow-x-auto rounded-2xl shadow-2xl glass-card -mx-2 sm:mx-0" style="overflow-x: auto; overflow-y: visible;">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 p-4" style="min-width: 1200px;">
                        <template x-for="(stage, stageKey) in ['prospect', 'negotiation', 'proposal', 'closed_won', 'closed_lost']" :key="stageKey">
                            <div class="kanban-column" 
                                 @dragover.prevent="if (draggedCard) { $event.currentTarget.classList.add('drag-over'); }"
                                 @dragleave.prevent="$event.currentTarget.classList.remove('drag-over')"
                                 @drop.prevent="handleDrop($event, stage)"
                                 :data-stage="stage">
                                <div class="font-bold text-lg text-gray-800 mb-3 text-center" x-text="getStageLabel(stage)"></div>
                                <template x-for="(card, index) in (kanbanData[stage] || [])" :key="card.id">
                                    <div class="kanban-card" 
                                         draggable="true"
                                         @dragstart="handleDragStart($event, card, stage)"
                                         @dragend="handleDragEnd($event)">
                                        <div class="font-semibold text-gray-900 mb-2" x-text="card.deal_name"></div>
                                        <div class="text-sm text-gray-600 mb-1"><span class="font-medium">Value:</span> <span x-text="card.value"></span></div>
                                        <div class="text-sm text-gray-600 mb-1"><span class="font-medium">Company:</span> <span x-text="card.company"></span></div>
                                        <div class="text-sm text-gray-600 mb-1"><span class="font-medium">Contact:</span> <span x-text="card.contact"></span></div>
                                        <div class="text-sm text-gray-600 mb-1"><span class="font-medium">Probability:</span> <span x-text="card.probability"></span></div>
                                        <div class="text-sm text-gray-600 mb-2"><span class="font-medium">Owner:</span> <span x-text="card.owner"></span></div>
                                        <div class="flex gap-2 mt-2">
                                            <button type="button" @click="editDealFromKanban(card.id)" class="flex-1 px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded hover:bg-blue-200">Edit</button>
                                            <button type="button" @click="deleteDealFromKanban(card.id)" class="flex-1 px-2 py-1 text-xs bg-red-100 text-red-700 rounded hover:bg-red-200">Delete</button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
    <div x-show="showBulkDelete" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showBulkDelete=false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 sm:p-8" @click.stop>
            <div class="flex items-center gap-4 mb-4">
                <div class="flex-shrink-0 w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Delete Selected Deals</h2>
                    <p class="text-sm text-gray-600 mt-1">This action cannot be undone</p>
                </div>
            </div>
            <p class="text-sm text-gray-700 mb-6 ml-16">Are you sure you want to delete the selected deals? All associated data will be permanently removed.</p>
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
                        <a href="{{ route('crm.pipeline.export', request()->query()) }}" class="px-4 py-2.5 rounded-xl border-2 border-green-200 bg-gradient-to-r from-green-50 to-green-100 hover:from-green-100 hover:to-green-200 text-green-700 font-semibold text-sm shadow-sm hover:shadow-md transition-all duration-200 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            Export
                        </a>
                        <button type="button" id="resetFilters" class="px-4 py-2.5 rounded-xl border-2 border-gray-200 bg-white hover:bg-gray-50 text-gray-700 font-semibold text-sm shadow-sm hover:shadow-md transition-all duration-200 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>
                </div>

    <div x-show="showCreate" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showCreate=false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl p-6 sm:p-8 max-h-[90vh] overflow-y-auto" @click.stop>
            <div class="flex items-center justify-between mb-6 pb-4 border-b-2 border-gray-100">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Create New Deal</h2>
                    <p class="text-sm text-gray-500 mt-1">Add a new deal to your pipeline</p>
                </div>
                <button type="button" @click="showCreate=false" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form id="createForm" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            @csrf
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Deal Name <span class="text-red-500">*</span></label>
                    <input name="deal_name" id="createDealName" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 bg-white text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400 transition-all duration-200" placeholder="Enter deal name" required>
                                </div>
                                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Stage <span class="text-red-500">*</span></label>
                    <select name="stage" id="createStage" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 bg-white text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                        <option value="prospect" selected>Prospect</option>
                                        <option value="negotiation">Negotiation</option>
                                        <option value="proposal">Proposal</option>
                                        <option value="closed_won">Closed Won</option>
                                        <option value="closed_lost">Closed Lost</option>
                                    </select>
                                </div>
                                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Value <span class="text-red-500">*</span></label>
                    <input name="value" id="createValue" type="number" step="0.01" min="0" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 bg-white text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400 transition-all duration-200" placeholder="0.00" required>
                                </div>
                                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Owner User ID</label>
                    <input name="owner_user_id" id="createOwner" type="number" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 bg-white text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400 transition-all duration-200" placeholder="User ID">
                                </div>
                                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Close Date</label>
                    <input name="close_date" id="createCloseDate" type="date" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 bg-white text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400 transition-all duration-200">
                                </div>
                                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Probability (%)</label>
                    <input name="probability" id="createProbability" type="number" min="0" max="100" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 bg-white text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400 transition-all duration-200" placeholder="0-100">
                                </div>
                                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Contact ID</label>
                    <input name="contact_id" id="createContact" type="number" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 bg-white text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400 transition-all duration-200" placeholder="Contact ID">
                                </div>
                                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Company</label>
                    <input name="company" id="createCompany" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 bg-white text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400 transition-all duration-200" placeholder="Company name">
                                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Notes</label>
                    <textarea name="notes" id="createNotes" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 bg-white text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400 transition-all duration-200" rows="4" placeholder="Additional notes..."></textarea>
                                </div>
                <div class="md:col-span-2 flex justify-end gap-3 mt-4 pt-4 border-t border-gray-100">
                    <button type="button" class="px-6 py-3 rounded-xl border-2 border-gray-200 bg-white hover:bg-gray-50 text-gray-700 font-semibold transition-all duration-200 cancel-create-btn" @click="showCreate=false">Cancel</button>
                    <button type="submit" id="createSubmitBtn" class="px-6 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold shadow-lg hover:shadow-xl transition-all duration-200">Create Deal</button>
                            </div>
                        </form>
                    </div>
                </div>

    <div x-show="showEdit" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showEdit=false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl p-6 sm:p-8 max-h-[90vh] overflow-y-auto" @click.stop>
            <div class="flex items-center justify-between mb-6 pb-4 border-b-2 border-gray-100">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Edit Deal</h2>
                    <p class="text-sm text-gray-500 mt-1">Update deal information</p>
                </div>
                <button type="button" @click="showEdit=false" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form id="editForm" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            @csrf
                            @method('PUT')
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Deal Name <span class="text-red-500">*</span></label>
                    <input name="deal_name" id="editDealName" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 bg-white text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400 transition-all duration-200" placeholder="Enter deal name" x-model="editDeal" required>
                                </div>
                                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Stage <span class="text-red-500">*</span></label>
                    <select name="stage" id="editStage" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 bg-white text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" x-model="editStage">
                                        <option value="prospect">Prospect</option>
                                        <option value="negotiation">Negotiation</option>
                                        <option value="proposal">Proposal</option>
                                        <option value="closed_won">Closed Won</option>
                                        <option value="closed_lost">Closed Lost</option>
                                    </select>
                                </div>
                                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Value <span class="text-red-500">*</span></label>
                    <input name="value" id="editValue" type="number" step="0.01" min="0" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 bg-white text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400 transition-all duration-200" placeholder="0.00" x-model="editValue" required>
                                </div>
                                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Owner User ID</label>
                    <input name="owner_user_id" id="editOwner" type="number" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 bg-white text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400 transition-all duration-200" placeholder="User ID" x-model="editOwner">
                                </div>
                                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Close Date</label>
                    <input name="close_date" id="editCloseDate" type="date" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 bg-white text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400 transition-all duration-200" x-model="editCloseDate">
                                </div>
                                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Probability (%)</label>
                    <input name="probability" id="editProbability" type="number" min="0" max="100" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 bg-white text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400 transition-all duration-200" placeholder="0-100" x-model="editProbability">
                                </div>
                                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Contact ID</label>
                    <input name="contact_id" id="editContact" type="number" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 bg-white text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400 transition-all duration-200" placeholder="Contact ID" x-model="editContact">
                                </div>
                                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Company</label>
                    <input name="company" id="editCompany" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 bg-white text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400 transition-all duration-200" placeholder="Company name" x-model="editCompany">
                                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Notes</label>
                    <textarea name="notes" id="editNotes" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 bg-white text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400 transition-all duration-200" rows="4" placeholder="Additional notes..." x-model="editNotes"></textarea>
                                </div>
                <div class="md:col-span-2 flex justify-end gap-3 mt-4 pt-4 border-t border-gray-100">
                    <button type="button" class="px-6 py-3 rounded-xl border-2 border-gray-200 bg-white hover:bg-gray-50 text-gray-700 font-semibold transition-all duration-200 cancel-edit-btn" @click="showEdit=false">Cancel</button>
                    <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold shadow-lg hover:shadow-xl transition-all duration-200">Save Changes</button>
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
                    <h2 class="text-xl font-bold text-gray-800">Delete Deal</h2>
                    <p class="text-sm text-gray-600 mt-1">This action cannot be undone</p>
                </div>
            </div>
            <p class="text-sm text-gray-700 mb-6 ml-16">Are you sure you want to delete this deal? All associated data will be permanently removed.</p>
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" class="px-6 py-3 rounded-xl border-2 border-gray-200 bg-white hover:bg-gray-50 text-gray-700 font-semibold transition-all duration-200 cancel-delete-btn" @click="showDelete=false">Cancel</button>
                <button type="button" id="confirmDelete" class="px-6 py-3 rounded-xl bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-semibold shadow-lg hover:shadow-xl transition-all duration-200">Delete Deal</button>
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

$(document).ready(function() {
    let table = $('#pipelineTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('crm.pipeline.datatable') }}',
            data: function(d) {
                d.stage = $('#filterStage').val() || '';
                d.owner_user_id = $('#filterOwner').val() || '';
                d.value_min = $('#filterValueMin').val() || '';
                d.value_max = $('#filterValueMax').val() || '';
                d.close_date_from = $('#filterCloseDateFrom').val() || '';
                d.close_date_to = $('#filterCloseDateTo').val() || '';
                d.probability = $('#filterProbability').val() || '';
            }
        },
        columns: [
            { data: 'id', name: 'id', orderable: false, searchable: false, render: function(data) {
                return '<input type="checkbox" class="row-check rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" value="' + data + '">';
            }},
            { data: 'deal_name', name: 'deal_name', render: function(data) {
                return '<span class="font-medium text-gray-900">' + (data || '-') + '</span>';
            }},
            { data: 'stage_html', name: 'stage', orderable: false, searchable: false },
            { data: 'value', name: 'value', render: function(data) {
                return '<span class="text-gray-700 font-semibold">' + (data || '-') + '</span>';
            }},
            { data: 'owner_user_id', name: 'owner_user_id', render: function(data) {
                return '<span class="text-gray-700">' + (data || '-') + '</span>';
            }},
            { data: 'close_date', name: 'close_date', render: function(data) {
                return '<span class="text-gray-700">' + (data || '-') + '</span>';
            }},
            { data: 'probability', name: 'probability', render: function(data) {
                return '<span class="text-gray-700">' + (data || '-') + '</span>';
            }},
            { data: 'company', name: 'company', render: function(data) {
                return '<span class="text-gray-700">' + (data || '-') + '</span>';
            }},
            { data: 'contact', name: 'contact_id', render: function(data) {
                return '<span class="text-gray-700">' + (data || '-') + '</span>';
            }},
            { data: 'created_at', name: 'created_at', render: function(data) {
                return '<span class="text-gray-700">' + (data || '-') + '</span>';
            }},
            { data: 'actions_html', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[9, 'desc']],
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
    
    function resetCreateForm() {
        $('#createForm')[0].reset();
        $('#createStage').val('prospect');
        const submitBtn = $('#createSubmitBtn');
        submitBtn.prop('disabled', false).text('Create Deal');
    }
    
    function openCreateModal() {
        const alpineData = getAlpineData();
        if (alpineData && alpineData.__x) {
            alpineData.__x.$data.showCreate = true;
        }
        resetCreateForm();
        
        const modal = $('[x-show="showCreate"]');
        if (modal.length) {
            modal.show();
        }
    }

    let currentDealId = null;

    $(document).on('click', '.edit-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const btn = $(this);
        currentDealId = btn.data('id');
        
        if (!currentDealId) {
            showNotification('Deal ID not found.', 'error');
            return;
        }
        
        $('#editDealName').val(btn.data('deal') || '');
        $('#editStage').val(btn.data('stage') || 'prospect');
        $('#editValue').val(btn.data('value') || '');
        $('#editOwner').val(btn.data('owner') || '');
        $('#editCloseDate').val(btn.data('close-date') || '');
        $('#editProbability').val(btn.data('probability') || '');
        $('#editContact').val(btn.data('contact') || '');
        $('#editCompany').val(btn.data('company') || '');
        $('#editNotes').val(btn.data('notes') || '');
        
        const alpineData = getAlpineData();
        if (alpineData && alpineData.__x) {
            const data = alpineData.__x.$data;
            data.editId = currentDealId;
            data.editDeal = btn.data('deal') || '';
            data.editStage = btn.data('stage') || 'prospect';
            data.editValue = btn.data('value') || '';
            data.editOwner = btn.data('owner') || '';
            data.editCloseDate = btn.data('close-date') || '';
            data.editProbability = btn.data('probability') || '';
            data.editContact = btn.data('contact') || '';
            data.editCompany = btn.data('company') || '';
            data.editNotes = btn.data('notes') || '';
            data.showEdit = true;
        }
        
        const editModal = $('[x-show="showEdit"]');
        if (editModal.length) {
            editModal.removeAttr('style');
            editModal.show();
            editModal.css('display', 'flex');
        }
    });
    
    $(document).on('click', '.delete-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const btn = $(this);
        currentDealId = btn.data('id');
        
        if (!currentDealId) {
            showNotification('Deal ID not found.', 'error');
            return;
        }
        
        const alpineData = getAlpineData();
        if (alpineData && alpineData.__x) {
            const data = alpineData.__x.$data;
            data.editId = currentDealId;
            data.showDelete = true;
        }
        
        const deleteModal = $('[x-show="showDelete"]');
        if (deleteModal.length) {
            deleteModal.removeAttr('style');
            deleteModal.show();
            deleteModal.css('display', 'flex');
        }
    });

    $('#createForm').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const submitBtn = $('#createSubmitBtn');
        const originalText = submitBtn.text();
        submitBtn.prop('disabled', true).text('Creating...');
        
        const formData = {
            _token: '{{ csrf_token() }}',
            deal_name: $('#createDealName').val(),
            stage: $('#createStage').val() || 'prospect',
            value: $('#createValue').val() || 0,
            owner_user_id: $('#createOwner').val() || null,
            close_date: $('#createCloseDate').val() || null,
            probability: $('#createProbability').val() || null,
            contact_id: $('#createContact').val() || null,
            company: $('#createCompany').val() || null,
            notes: $('#createNotes').val() || null
        };
        
        $.ajax({
            url: '{{ route('crm.pipeline.store') }}',
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            data: formData,
            success: function(response) {
                console.log('Create deal response:', response);
                
                if (!response || !response.success) {
                    showNotification('Deal creation failed. Please try again.', 'error');
                    submitBtn.prop('disabled', false).text(originalText);
                    return;
                }
                
                submitBtn.prop('disabled', false).text(originalText);
                table.ajax.reload();
                if (typeof loadKanbanData === 'function') {
                    loadKanbanData();
                }
                
                resetCreateForm();
                
                const alpineData = getAlpineData();
                if (alpineData && alpineData.__x) {
                    alpineData.__x.$data.showCreate = false;
                }
                
                const modal = $('[x-show="showCreate"]');
                if (modal.length) {
                    modal.hide();
                    modal.css('display', 'none');
                }
                
                setTimeout(function() {
                    const alpineDataAfter = getAlpineData();
                    if (alpineDataAfter && alpineDataAfter.__x) {
                        alpineDataAfter.__x.$data.showCreate = false;
                    }
                    const modalAfter = $('[x-show="showCreate"]');
                    if (modalAfter.length && modalAfter.is(':visible')) {
                        modalAfter.hide();
                        modalAfter.css('display', 'none');
                    }
                }, 50);
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).text(originalText);
                console.error('Error creating deal:', xhr);
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    let errors = Object.values(xhr.responseJSON.errors).flat();
                    showNotification('Validation errors:\n' + errors.join('\n'), 'error');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    showNotification('Error: ' + xhr.responseJSON.message, 'error');
                } else {
                    showNotification('Error creating deal. Please try again.', 'error');
                }
            }
        });
        
        return false;
    });

    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        const alpineData = getAlpineData();
        if (alpineData && alpineData.__x) {
            currentDealId = alpineData.__x.$data.editId || currentDealId;
        }
        if (!currentDealId) {
            showNotification('Deal ID not found.', 'error');
            return;
        }
        
        const formData = {
            _token: '{{ csrf_token() }}',
            _method: 'PUT',
            deal_name: $('#editDealName').val(),
            stage: $('#editStage').val(),
            value: $('#editValue').val(),
            owner_user_id: $('#editOwner').val() || null,
            close_date: $('#editCloseDate').val() || null,
            probability: $('#editProbability').val() || null,
            contact_id: $('#editContact').val() || null,
            company: $('#editCompany').val() || null,
            notes: $('#editNotes').val() || null
        };
        
        $.ajax({
            url: '{{ route('crm.pipeline.update', '__ID__') }}'.replace('__ID__', currentDealId),
            method: 'POST',
            data: formData,
            success: function(response) {
                table.ajax.reload();
                if (typeof loadKanbanData === 'function') {
                    loadKanbanData();
                }
                closeModal('showEdit');
                
                const editModal = $('[x-show="showEdit"]');
                if (editModal.length) {
                    editModal.hide();
                    editModal.css('display', 'none');
                }
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    let errors = Object.values(xhr.responseJSON.errors).flat();
                    showNotification('Validation errors:\n' + errors.join('\n'), 'error');
                } else {
                    showNotification('Error updating deal.', 'error');
                }
            }
        });
    });

    $('#confirmDelete').on('click', function() {
        const alpineData = getAlpineData();
        if (alpineData && alpineData.__x) {
            currentDealId = alpineData.__x.$data.editId || currentDealId;
        }
        if (!currentDealId) {
            showNotification('Deal ID not found.', 'error');
            return;
        }
        
        $.ajax({
            url: '{{ route('crm.pipeline.destroy', '__ID__') }}'.replace('__ID__', currentDealId),
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                _method: 'DELETE'
            },
            success: function(response) {
                table.ajax.reload();
                if (typeof loadKanbanData === 'function') {
                    loadKanbanData();
                }
                closeModal('showDelete');
                currentDealId = null;
                
                const deleteModal = $('[x-show="showDelete"]');
                if (deleteModal.length) {
                    deleteModal.hide();
                    deleteModal.css('display', 'none');
                }
            },
            error: function() {
                showNotification('Error deleting deal.', 'error');
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
            url: '{{ route('crm.pipeline.bulk-delete') }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                ids: currentBulkDeleteIds
            },
            success: function(response) {
                table.ajax.reload();
                if (typeof loadKanbanData === 'function') {
                    loadKanbanData();
                }
                closeModal('showBulkDelete');
                
                const bulkDeleteModal = $('[x-show="showBulkDelete"]');
                if (bulkDeleteModal.length) {
                    bulkDeleteModal.hide();
                    bulkDeleteModal.css('display', 'none');
                }
            },
            error: function() {
                showNotification('Error deleting deals.', 'error');
            }
        });
    });

    $('#selectAll').on('click', function() {
        $('.row-check').prop('checked', $(this).prop('checked'));
    });
    
    setTimeout(function() {
        $('.cancel-create-btn').off('click').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            closeModal('showCreate');
            return false;
        });
        
        $('.cancel-edit-btn').off('click').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            closeModal('showEdit');
            return false;
        });
        
        $('.cancel-delete-btn').off('click').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            closeModal('showDelete');
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
    
    $(document).off('click', '.cancel-create-btn').on('click', '.cancel-create-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        closeModal('showCreate');
        return false;
    });
    
    $(document).off('click', '.cancel-edit-btn').on('click', '.cancel-edit-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        closeModal('showEdit');
        return false;
    });
    
    $(document).off('click', '.cancel-delete-btn').on('click', '.cancel-delete-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        closeModal('showDelete');
        return false;
    });
    
    $(document).off('click', '.cancel-bulk-delete-btn').on('click', '.cancel-bulk-delete-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        closeModal('showBulkDelete');
        return false;
    });

    $('#applyFilters').on('click', function() {
        table.ajax.reload(function() {
        }, false);
        if (typeof loadKanbanData === 'function') {
            loadKanbanData();
        }
    });

    $('#resetFilters').on('click', function() {
        $('#filterStage').val('');
        $('#filterOwner').val('');
        $('#filterValueMin').val('');
        $('#filterValueMax').val('');
        $('#filterCloseDateFrom').val('');
        $('#filterCloseDateTo').val('');
        $('#filterProbability').val('');
        table.search('').draw();
        table.ajax.reload(null, false);
        if (typeof loadKanbanData === 'function') {
            loadKanbanData();
        }
    });
    
    $('#filterStage, #filterOwner, #filterValueMin, #filterValueMax, #filterCloseDateFrom, #filterCloseDateTo, #filterProbability').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#applyFilters').click();
        }
    });
    
    $(document).on('click', '#newDealBtn', function(e) {
        const modal = $('[x-show="showCreate"]');
        if (modal.length) {
            modal.removeAttr('style');
        }
        
        setTimeout(function() {
            resetCreateForm();
        }, 150);
    });

    $(document).on('click', '.toggle-stage-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const btn = $(this);
        const dealId = btn.data('id');
        const currentStage = btn.data('stage');
        
        const stages = ['prospect', 'negotiation', 'proposal', 'closed_won', 'closed_lost'];
        const currentIndex = stages.indexOf(currentStage);
        const nextIndex = (currentIndex + 1) % stages.length;
        const newStage = stages[nextIndex];
        
        $.ajax({
            url: '{{ route('crm.pipeline.update-stage', '__ID__') }}'.replace('__ID__', dealId),
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                stage: newStage
            },
            success: function(response) {
                table.ajax.reload(null, false);
                if (typeof loadKanbanData === 'function') {
                    loadKanbanData();
                }
            },
            error: function() {
                showNotification('Error updating deal stage.', 'error');
            }
        });
    });
});

function loadKanbanData() {
    const filterData = {
        stage: $('#filterStage').val() || '',
        owner_user_id: $('#filterOwner').val() || '',
        value_min: $('#filterValueMin').val() || '',
        value_max: $('#filterValueMax').val() || '',
        close_date_from: $('#filterCloseDateFrom').val() || '',
        close_date_to: $('#filterCloseDateTo').val() || '',
        probability: $('#filterProbability').val() || ''
    };
    
    $.ajax({
        url: '{{ route('crm.pipeline.kanban-data') }}',
        method: 'GET',
        data: filterData,
        success: function(response) {
            const alpineData = getAlpineData();
            if (alpineData && alpineData.__x) {
                const stages = ['prospect', 'negotiation', 'proposal', 'closed_won', 'closed_lost'];
                const kanbanData = {};
                
                stages.forEach(stage => {
                    kanbanData[stage] = [];
                });
                
                if (response.data) {
                    response.data.forEach(function(deal) {
                        const stage = deal.stage || 'prospect';
                        if (!kanbanData[stage]) {
                            kanbanData[stage] = [];
                        }
                        kanbanData[stage].push({
                            id: deal.id,
                            deal_name: deal.deal_name,
                            value: deal.value,
                            company: deal.company,
                            contact: deal.contact,
                            probability: deal.probability,
                            close_date: deal.close_date,
                            owner: deal.owner
                        });
                    });
                }
                
                alpineData.__x.$data.kanbanData = kanbanData;
            }
        },
        error: function() {
            showNotification('Error loading kanban data.', 'error');
        }
    });
}

window.handleDragStart = function(event, card, stage) {
    const alpineData = getAlpineData();
    if (alpineData && alpineData.__x) {
        alpineData.__x.$data.draggedCard = card;
        alpineData.__x.$data.draggedFromStage = stage;
    }
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/html', event.target.outerHTML);
    event.target.classList.add('dragging');
};

window.handleDragEnd = function(event) {
    event.target.classList.remove('dragging');
    document.querySelectorAll('.kanban-column').forEach(col => {
        col.classList.remove('drag-over');
    });
};

window.handleDrop = function(event, newStage) {
    event.preventDefault();
    event.currentTarget.classList.remove('drag-over');
    
    const alpineData = getAlpineData();
    if (!alpineData || !alpineData.__x) return;
    
    const data = alpineData.__x.$data;
    const card = data.draggedCard;
    const fromStage = data.draggedFromStage;
    
    if (!card || fromStage === newStage) {
        data.draggedCard = null;
        data.draggedFromStage = null;
        return;
    }
    
    $.ajax({
        url: '{{ route('crm.pipeline.update-stage', '__ID__') }}'.replace('__ID__', card.id),
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            stage: newStage
        },
        success: function(response) {
            if (data.kanbanData[fromStage]) {
                const index = data.kanbanData[fromStage].findIndex(c => c.id === card.id);
                if (index !== -1) {
                    data.kanbanData[fromStage].splice(index, 1);
                }
            }
            
            if (!data.kanbanData[newStage]) {
                data.kanbanData[newStage] = [];
            }
            
            card.stage = newStage;
            data.kanbanData[newStage].push(card);
            
            data.draggedCard = null;
            data.draggedFromStage = null;
            
            if (typeof table !== 'undefined') {
                table.ajax.reload(null, false);
            }
        },
        error: function() {
            showNotification('Error updating deal stage.', 'error');
            data.draggedCard = null;
            data.draggedFromStage = null;
            loadKanbanData();
        }
    });
};

window.editDealFromKanban = function(dealId) {
    $.ajax({
        url: '{{ route('crm.pipeline.kanban-data') }}',
        method: 'GET',
        success: function(response) {
            const deal = response.data.find(d => d.id == dealId);
            if (deal) {
                const alpineData = getAlpineData();
                if (alpineData && alpineData.__x) {
                    const data = alpineData.__x.$data;
                    data.editId = deal.id;
                    data.editDeal = deal.deal_name || '';
                    data.editStage = deal.stage || 'prospect';
                    data.editValue = deal.value ? deal.value.replace('$', '').replace(/,/g, '') : '0';
                    data.editOwner = deal.owner ? deal.owner.replace('User ', '') : '';
                    data.editCloseDate = deal.close_date || '';
                    data.editProbability = deal.probability ? deal.probability.replace('%', '') : '0';
                    data.editContact = '';
                    data.editCompany = deal.company || '';
                    data.editNotes = '';
                    data.showEdit = true;
                }
                
                const editModal = $('[x-show="showEdit"]');
                if (editModal.length) {
                    editModal.removeAttr('style');
                    editModal.show();
                    editModal.css('display', 'flex');
                }
                
                currentDealId = deal.id;
            }
        }
    });
};

window.deleteDealFromKanban = function(dealId) {
    const alpineData = getAlpineData();
    if (alpineData && alpineData.__x) {
        alpineData.__x.$data.editId = dealId;
        alpineData.__x.$data.showDelete = true;
    }
    
    const deleteModal = $('[x-show="showDelete"]');
    if (deleteModal.length) {
        deleteModal.removeAttr('style');
        deleteModal.show();
        deleteModal.css('display', 'flex');
    }
    
    currentDealId = dealId;
};
</script>
</body>
</html>

