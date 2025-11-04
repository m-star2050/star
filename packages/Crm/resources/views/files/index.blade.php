<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM File Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body style="background: url('{{ asset('image/Screenshot_16.png') }}') center center / cover no-repeat fixed; min-height: 100vh; font-family: 'Inter', sans-serif;">

<style>
    .glass {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.18);
    }
    .sidebar-link{ display:flex; align-items:center; gap:.75rem; color:#0f172a; text-decoration:none; padding:.6rem .9rem; border-radius:.6rem; line-height:1; }
    .sidebar-link:hover{ background: rgba(0,0,0,.06); }
    .sidebar-link svg{ width:20px; height:20px; min-width:20px; min-height:20px; flex-shrink:0; display:block; }
    .sidebar-link span{ line-height:1.2; display:flex; align-items:center; }
    [x-cloak] { display: none !important; }
</style>

<div x-data="{mobileMenu:false, open:true, showUpload:false, showDelete:false, showBulkDelete:false, showPreview:false, deleteId:null, previewUrl:'', previewType:''}" class="relative">
    <div class="lg:hidden fixed top-0 left-0 right-0 z-50 glass rounded-b-2xl p-3 shadow-lg">
        <div class="flex items-center justify-between mb-4 mt-7">
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
                <div class="text-lg md:text-xl font-semibold tracking-wide">FILE MANAGEMENT</div>
                <button @click="showUpload=true" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-lg text-sm font-semibold transition flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Upload File
                </button>
            </div>

            @if(session('success'))
                <div class="glass rounded-xl p-4 mb-4 bg-green-500/20 border border-green-500/50 text-green-100">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="glass rounded-xl p-4 mb-4 bg-red-500/20 border border-red-500/50 text-red-100">
                    {{ session('error') }}
                </div>
            @endif

                <div class="glass rounded-xl p-4 mb-4">
                    <form method="GET" action="{{ route('crm.files.index') }}" class="flex flex-col md:flex-row gap-4 items-end">
                        @if(request()->filled('per_page'))
                            <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                        @endif
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-white mb-1">Search</label>
                            <input type="text" name="search" value="{{ $search }}" placeholder="File name..." class="w-full border rounded-lg px-3 py-2 bg-white/60 text-gray-700">
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-white mb-1">File Type</label>
                            <select name="file_type" class="w-full border rounded-lg px-3 py-2 bg-white/60 text-gray-700">
                                <option value="">All Types</option>
                                @foreach($fileTypes as $type)
                                    <option value="{{ $type }}" @selected($fileType === $type)>{{ strtoupper($type) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-white mb-1">Linked To</label>
                            <select name="linked_type" class="w-full border rounded-lg px-3 py-2 bg-white/60 text-gray-700">
                                <option value="">All</option>
                                <option value="contact" @selected($linkedType === 'contact')>Contacts</option>
                                <option value="lead" @selected($linkedType === 'lead')>Leads</option>
                                <option value="deal" @selected($linkedType === 'deal')>Deals</option>
                                <option value="task" @selected($linkedType === 'task')>Tasks</option>
                            </select>
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-white mb-1">Sort By</label>
                            <select name="sort" class="w-full border rounded-lg px-3 py-2 bg-white/60 text-gray-700">
                                <option value="created_at" @selected($sort === 'created_at')>Date Created</option>
                                <option value="original_name" @selected($sort === 'original_name')>File Name</option>
                                <option value="file_type" @selected($sort === 'file_type')>File Type</option>
                                <option value="file_size" @selected($sort === 'file_size')>File Size</option>
                                <option value="linked_type" @selected($sort === 'linked_type')>Linked To</option>
                                <option value="uploaded_by" @selected($sort === 'uploaded_by')>Uploaded By</option>
                            </select>
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-white mb-1">Order</label>
                            <select name="direction" class="w-full border rounded-lg px-3 py-2 bg-white/60 text-gray-700">
                                <option value="desc" @selected($direction === 'desc')>Descending</option>
                                <option value="asc" @selected($direction === 'asc')>Ascending</option>
                            </select>
                        </div>
                        <div class="flex-shrink-0">
                            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold">Filter</button>
                        </div>
                    </form>
                </div>

                <form method="POST" action="{{ route('crm.files.bulk-delete') }}" id="bulkForm">
                @csrf
                <div class="mb-2 flex items-end gap-2 px-2">
                    <button type="button" class="px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl shadow" @click="showBulkDelete=true">Delete Selected</button>
                    <span class="text-sm text-gray-200 pb-1 ml-4">Total: {{ $files->total() }} files</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @forelse($files as $file)
                        <div class="glass rounded-xl p-4 hover:bg-white/25 transition">
                            <div class="flex items-start justify-between mb-3">
                                <input type="checkbox" class="row-check mt-1" name="ids[]" value="{{ $file->id }}">
                                <div class="flex gap-2">
                                    @if($file->isImage())
                                        <button type="button" @click="previewUrl='{{ route('crm.files.preview', $file) }}'; previewType='image'; showPreview=true" class="p-1 bg-blue-500/30 hover:bg-blue-500/50 rounded text-white">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>
                                    @endif
                                    <a href="{{ route('crm.files.download', $file) }}" class="p-1 bg-green-500/30 hover:bg-green-500/50 rounded text-white">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    </a>
                                    <button type="button" @click="deleteId={{ $file->id }}; showDelete=true" class="p-1 bg-red-500/30 hover:bg-red-500/50 rounded text-white">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-3 mb-2">
                                <div class="p-3 rounded-lg bg-white/30">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 {{ $file->getIconClass() }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-white font-semibold text-sm truncate" title="{{ $file->original_name }}">{{ $file->original_name }}</p>
                                    <p class="text-white/70 text-xs">{{ strtoupper($file->file_type) }} • {{ $file->getFileSizeHumanAttribute() }}</p>
                                </div>
                            </div>
                            
                            @if($file->description)
                                <p class="text-white/80 text-xs mb-2 line-clamp-2">{{ $file->description }}</p>
                            @endif
                            
                            <div class="flex items-center justify-between text-xs text-white/70 border-t border-white/20 pt-2 mt-2">
                                <span>{{ $file->getLinkedTypeLabel() }}</span>
                                <span>{{ $file->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full glass rounded-xl p-12 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-white/50 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-white text-lg">No files found</p>
                            <button type="button" @click="showUpload=true" class="mt-4 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                                Upload Your First File
                            </button>
                        </div>
                    @endforelse
                </div>
                </form>

                <div class="flex flex-col md:flex-row items-center gap-3 p-3">
                    <div class="text-sm text-gray-600">
                        Showing <span class="font-medium">{{ $files->firstItem() ?? 0 }}</span>–<span class="font-medium">{{ $files->lastItem() ?? 0 }}</span> of <span class="font-medium">{{ $files->total() }}</span>
                    </div>
                    
                    <div class="flex gap-1 items-center md:flex-1 md:justify-center">
                        @if ($files->onFirstPage())
                            <span class="px-3 py-2 rounded-xl border text-gray-400 cursor-not-allowed">&laquo; Prev</span>
                        @else
                            <a href="{{ $files->previousPageUrl() }}" class="px-3 py-2 rounded-xl border hover:bg-white/40">&laquo; Prev</a>
                        @endif
                        @php
                            $current = $files->currentPage();
                            $last = $files->lastPage();
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
                                <a href="{{ $files->url($p) }}" class="px-3 py-2 rounded-xl border hover:bg-white/60">{{ $p }}</a>
                            @endif
                        @endforeach
                        @if ($files->hasMorePages())
                            <a href="{{ $files->nextPageUrl() }}" class="px-3 py-2 rounded-xl border hover:bg-white/40">Next &raquo;</a>
                        @else
                            <span class="px-3 py-2 rounded-xl border text-gray-400 cursor-not-allowed">Next &raquo;</span>
                        @endif
                    </div>
                    
                    <!-- Right Section: Items Per Page and Action Buttons -->
                    <form method="GET" class="flex flex-col sm:flex-row items-center gap-2 flex-wrap">
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-gray-600 whitespace-nowrap">Items per page</label>
                            <input type="number" name="per_page" min="1" max="100" value="{{ request('per_page', 10) }}" class="w-20 border rounded-xl px-3 py-2 bg-white/60 text-gray-700 shadow-inner">
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="hidden" name="search" value="{{ request('search') }}">
                            <input type="hidden" name="file_type" value="{{ request('file_type') }}">
                            <input type="hidden" name="linked_type" value="{{ request('linked_type') }}">
                            <input type="hidden" name="sort" value="{{ request('sort', 'created_at') }}">
                            <input type="hidden" name="direction" value="{{ request('direction', 'desc') }}">
                            <button class="px-4 py-2 rounded-xl border hover:bg-white/70 bg-white/40 text-gray-700 whitespace-nowrap">Apply</button>
                            <a href="{{ route('crm.files.index') }}" class="px-4 py-2 rounded-xl border bg-gray-100 hover:bg-gray-200 text-gray-600 whitespace-nowrap">Reset</a>
                        </div>
                    </form>
                </div>


                <!-- Upload Modal -->
            <div x-show="showUpload" x-cloak 
                 x-init="$watch('showUpload', value => { if(value) { setTimeout(() => { const form = document.getElementById('uploadForm'); if(form) { const tokenInput = form.querySelector('input[name=\"_token\"]'); const metaToken = document.querySelector('meta[name=\"csrf-token\"]'); if(tokenInput && metaToken) { tokenInput.value = metaToken.getAttribute('content'); } } }, 100); } })"
                 class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 overflow-y-auto" 
                 @click.self="showUpload=false">
                <div class="glass rounded-2xl p-6 w-full max-w-lg m-4">
                    <h3 class="text-xl font-bold text-white mb-4">Upload File</h3>
                    <form method="POST" action="{{ route('crm.files.store') }}" enctype="multipart/form-data" id="uploadForm" @submit="const tokenInput = $el.querySelector('input[name=\"_token\"]'); const metaToken = document.querySelector('meta[name=\"csrf-token\"]'); if(tokenInput && metaToken) { tokenInput.value = metaToken.getAttribute('content'); }">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-white mb-1">Select File *</label>
                                <input type="file" name="file" required class="w-full border rounded-lg px-3 py-2 bg-white text-gray-700">
                                <p class="text-xs text-white/70 mt-1">Max size: 10MB. Supported: PDF, DOCX, XLSX, JPG, PNG, etc.</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-white mb-1">Link To (Optional)</label>
                                <select name="linked_type" class="w-full border rounded-lg px-3 py-2 bg-white text-gray-700">
                                    <option value="">None</option>
                                    <option value="contact">Contact</option>
                                    <option value="lead">Lead</option>
                                    <option value="deal">Deal</option>
                                    <option value="task">Task</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-white mb-1">Link ID (Optional)</label>
                                <input type="number" name="linked_id" placeholder="Enter ID..." class="w-full border rounded-lg px-3 py-2 bg-white text-gray-700">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-white mb-1">Description (Optional)</label>
                                <textarea name="description" rows="3" placeholder="Add a description..." class="w-full border rounded-lg px-3 py-2 bg-white text-gray-700"></textarea>
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 mt-6">
                            <button type="button" @click="showUpload=false" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">Upload File</button>
                        </div>
                    </form>
                </div>
            </div>

                <!-- Delete Modal -->
                <div x-show="showDelete" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="showDelete=false">
                    <div class="bg-white rounded-2xl p-6 w-full max-w-md max-h-[90vh] overflow-y-auto">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Confirm Delete</h3>
                        <p class="text-gray-700 mb-6">Are you sure you want to delete this file? This action cannot be undone.</p>
                        <form method="POST" :action="`{{ route('crm.files.index') }}/${deleteId}`">
                            @csrf
                            @method('DELETE')
                            <div class="flex justify-end gap-3">
                                <button type="button" @click="showDelete=false" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg">Cancel</button>
                                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">Delete</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Bulk Delete Modal -->
                <div x-show="showBulkDelete" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center">
                    <div class="absolute inset-0 bg-black/50" @click="showBulkDelete=false"></div>
                    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 max-h-[90vh] overflow-y-auto">
                        <div class="text-lg font-semibold mb-3">Delete Selected Files</div>
                        <p class="text-sm text-gray-600 mb-4">Are you sure you want to delete the selected files? This action cannot be undone.</p>
                        <div class="flex justify-end gap-2">
                            <button type="button" class="px-4 py-2 rounded-lg border" @click="showBulkDelete=false">Cancel</button>
                            <button type="submit" form="bulkForm" class="px-4 py-2 rounded-lg bg-red-600 text-white">Delete</button>
                        </div>
                    </div>
                </div>

                <!-- Preview Modal -->
                <div x-show="showPreview" x-cloak class="fixed inset-0 bg-black/90 flex items-center justify-center z-50 p-4" @click.self="showPreview=false">
                    <button @click="showPreview=false" class="absolute top-4 right-4 p-2 bg-white/20 hover:bg-white/30 rounded-lg text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                    <img x-show="previewType=='image'" :src="previewUrl" class="max-w-full max-h-full rounded-lg shadow-2xl">
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Setup CSRF token for all AJAX requests
    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }
    
    let csrfToken = getCsrfToken();
    
    // Setup jQuery AJAX defaults if jQuery is available
    if (typeof jQuery !== 'undefined') {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });
        
        // Update token when meta tag changes
        const observer = new MutationObserver(function(mutations) {
            csrfToken = getCsrfToken();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });
        });
        
        const metaElement = document.querySelector('meta[name="csrf-token"]');
        if (metaElement) {
            observer.observe(metaElement, { attributes: true, attributeFilter: ['content'] });
        }
    }
    
    // Setup fetch defaults
    const originalFetch = window.fetch;
    window.fetch = function(url, options = {}) {
        options.headers = options.headers || {};
        options.headers['X-CSRF-TOKEN'] = getCsrfToken();
        options.headers['X-Requested-With'] = 'XMLHttpRequest';
        return originalFetch(url, options);
    };
    
    // Update CSRF token in all forms before submission
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (form.tagName === 'FORM' && form.method.toUpperCase() === 'POST') {
            // Check if CSRF token exists in form
            let csrfInput = form.querySelector('input[name="_token"]');
            if (!csrfInput) {
                // Create CSRF token input if it doesn't exist
                csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                form.appendChild(csrfInput);
            }
            // Always update token value with latest from meta tag
            const currentToken = getCsrfToken();
            if (currentToken) {
                csrfInput.value = currentToken;
            }
        }
    }, true);
    
    // Refresh CSRF token when upload modal opens
    document.addEventListener('alpine:init', () => {
        Alpine.data('uploadForm', () => ({
            refreshToken() {
                const form = document.querySelector('#uploadForm');
                if (form) {
                    const tokenInput = form.querySelector('input[name="_token"]');
                    if (tokenInput) {
                        tokenInput.value = getCsrfToken();
                    }
                }
            }
        }));
    });
    
    // Refresh CSRF token on page focus to prevent expiration
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            // Refresh CSRF token when page becomes visible again
            fetch('{{ route("crm.files.index") }}', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(response => {
                // Try to extract new token from response if available
                return response.text();
            }).then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newToken = doc.querySelector('meta[name="csrf-token"]');
                if (newToken) {
                    const metaToken = document.querySelector('meta[name="csrf-token"]');
                    if (metaToken) {
                        metaToken.setAttribute('content', newToken.getAttribute('content'));
                    }
                }
            }).catch(() => {
                // Silently fail - token refresh attempt
            });
        }
    });
</script>

