<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CRM Pipeline - Kanban</title>
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
        .sidebar-link{ display:flex; align-items:center; gap:.75rem; color:#0f172a; text-decoration:none; padding:.6rem .9rem; border-radius:.6rem; }
        .sidebar-link:hover{ background: rgba(0,0,0,.06); }
        .sidebar-link svg{ width:20px; height:20px; min-width:20px; min-height:20px; }
        .deal-card {
            cursor: move;
            transition: all 0.2s;
        }
        .deal-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .deal-card.dragging {
            opacity: 0.5;
        }
        .stage-column.drag-over {
            background: rgba(59, 130, 246, 0.1);
            border: 2px dashed #3b82f6;
        }
    </style>
</head>
<body>

<div x-data="kanbanBoard()" class="relative">
    <aside class="fixed top-3 left-3 h-[calc(100vh-24px)] glass rounded-2xl p-3 transition-all duration-300" :class="open ? 'w-64' : 'w-16'">
        <div class="flex items-center justify-between mb-4">
            <div class="text-gray-900 font-extrabold tracking-wide mt-5" :class="open ? 'opacity-100' : 'opacity-0 pointer-events-none'">WELCOME USER</div>
            <button @click="open=!open" class="text-white bg-white/20 border border-white/40 rounded-full w-7 h-7 flex items-center justify-center hover:bg-white/30 mt-5">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="open ? 'M15 19l-7-7 7-7' : 'M9 5l7 7-7 7'"/></svg>
            </button>
        </div>
        <div class="text-gray-900/80 text-xs uppercase tracking-wider mb-2 mt-5" :class="open ? 'opacity-100' : 'opacity-0 pointer-events-none'">General</div>
        <nav class="space-y-1 mt-4">
            <a href="{{ route('crm.contacts.index') }}" class="sidebar-link">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10.707 1.293a1 1 0 00-1.414 0l-7 7A1 1 0 003 10h1v7a1 1 0 001 1h4v-4h2v4h4a1 1 0 001-1v-7h1a1 1 0 00.707-1.707l-7-7z"/></svg>
                <span x-show="open" x-transition>Contacts</span>
            </a>
            <a href="{{ route('crm.leads.index') }}" class="sidebar-link">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M3 3h2l.4 2M7 13h8l2-8H5.4M7 13L6 6m1 7l-1 4m8-4l1 4m-5-4v4"/></svg>
                <span x-show="open" x-transition>Leads</span>
            </a>
            <a href="{{ route('crm.tasks.index') }}" class="sidebar-link">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/></svg>
                <span x-show="open" x-transition>Tasks</span>
            </a>
            <a href="{{ route('crm.pipeline.kanban') }}" class="sidebar-link bg-black/10">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M3 12v3c0 1.657 3.134 3 7 3s7-1.343 7-3v-3c0 1.657-3.134 3-7 3s-7-1.343-7-3z"/><path d="M3 7v3c0 1.657 3.134 3 7 3s7-1.343 7-3V7c0 1.657-3.134 3-7 3S3 8.657 3 7z"/><path d="M17 5c0 1.657-3.134 3-7 3S3 6.657 3 5s3.134-3 7-3 7 1.343 7 3z"/></svg>
                <span x-show="open" x-transition>Pipeline</span>
            </a>
            <a href="{{ route('crm.reports.index') }}" class="sidebar-link">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/></svg>
                <span x-show="open" x-transition>Reports</span>
            </a>
            <a href="{{ route('crm.files.index') }}" class="sidebar-link">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg>
                <span x-show="open" x-transition>Files</span>
            </a>
        </nav>
    </aside>

    <div class="transition-all duration-300" :style="open ? 'padding-left:280px' : 'padding-left:88px'">
        <div class="min-h-screen px-2 py-8">
            <div class="w-full max-w-[1600px] mx-auto px-3 md:px-4 py-3">
                <div class="glass w-full rounded-xl px-6 py-3 mb-4 flex items-center justify-between text-white">
                    <div class="text-lg md:text-xl font-semibold tracking-wide">SALES PIPELINE - KANBAN VIEW</div>
                    <a href="{{ route('crm.pipeline.index') }}" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 rounded-lg text-sm font-semibold transition">
                        Switch to List View
                    </a>
                </div>

                @if(session('status'))
                <div class="mb-4 px-4 py-3 rounded-lg bg-green-100 border border-green-400 text-green-700">
                    {{ session('status') }}
                </div>
                @endif

                <div class="mb-6 flex items-center gap-4">
                    <button type="button" @click="showCreate=true" class="flex items-center gap-2 px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 20 20" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"/></svg>
                        New Deal
                    </button>
                </div>

                <!-- Kanban Board -->
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                    @foreach(['prospect' => 'Prospect', 'negotiation' => 'Negotiation', 'proposal' => 'Proposal', 'closed_won' => 'Closed Won', 'closed_lost' => 'Closed Lost'] as $stageKey => $stageLabel)
                    <div class="flex flex-col h-full">
                        <div class="glass rounded-xl p-4 mb-3 text-center font-bold text-gray-800 uppercase text-sm tracking-wide">
                            {{ $stageLabel }}
                            <span class="block text-xs font-normal mt-1 text-gray-600">({{ $dealsByStage[$stageKey]->count() }} deals)</span>
                        </div>

                        <div 
                            class="stage-column flex-1 glass rounded-xl p-3 min-h-[400px] space-y-3"
                            data-stage="{{ $stageKey }}"
                            @drop.prevent="handleDrop($event, '{{ $stageKey }}')"
                            @dragover.prevent="dragOver($event)"
                            @dragleave.prevent="dragLeave($event)"
                        >
                            @forelse($dealsByStage[$stageKey] as $deal)
                            <div 
                                class="deal-card bg-white rounded-lg p-4 shadow-md border border-gray-200"
                                draggable="true"
                                data-deal-id="{{ $deal->id }}"
                                @dragstart="dragStart($event, {{ $deal->id }})"
                                @dragend="dragEnd($event)"
                            >
                                <h4 class="font-bold text-gray-900 mb-2 text-sm">{{ $deal->deal_name }}</h4>
                                
                                <div class="space-y-1 text-xs text-gray-600">
                                    <div class="flex justify-between items-center">
                                        <span class="font-semibold text-green-600">${{ number_format($deal->value, 0) }}</span>
                                        @if($deal->probability)
                                        <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full">{{ $deal->probability }}%</span>
                                        @endif
                                    </div>

                                    @if($deal->company)
                                    <div class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                        <span>{{ $deal->company }}</span>
                                    </div>
                                    @endif

                                    @if($deal->contact)
                                    <div class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        <span>{{ $deal->contact->name }}</span>
                                    </div>
                                    @endif

                                    @if($deal->owner_user_id)
                                    <div class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>Owner: {{ $deal->owner_user_id }}</span>
                                    </div>
                                    @endif

                                    @if($deal->close_date)
                                    <div class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span>{{ $deal->close_date->format('M d, Y') }}</span>
                                    </div>
                                    @endif
                                </div>

                                <div class="mt-3 flex gap-2">
                                    <button 
                                        @click="editDeal({{ $deal->id }}, '{{ $deal->deal_name }}', '{{ $deal->stage }}', {{ $deal->value }}, '{{ $deal->owner_user_id }}', '{{ $deal->close_date }}', {{ $deal->probability ?? 0 }}, '{{ $deal->contact_id }}', '{{ addslashes($deal->company) }}', '{{ addslashes($deal->notes) }}')"
                                        class="flex-1 px-2 py-1 bg-yellow-500 hover:bg-yellow-600 text-white rounded text-xs font-semibold"
                                    >
                                        Edit
                                    </button>
                                    <button 
                                        @click="deleteDeal({{ $deal->id }}, '{{ $deal->deal_name }}')"
                                        class="flex-1 px-2 py-1 bg-red-500 hover:bg-red-600 text-white rounded text-xs font-semibold"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                            @empty
                            <div class="text-center text-gray-500 text-sm py-8">
                                No deals in this stage
                            </div>
                            @endforelse
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Create Deal Modal -->
                <div x-show="showCreate" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 overflow-y-auto" @click.self="showCreate=false">
                    <div class="glass rounded-2xl p-6 w-full max-w-2xl m-4">
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Create New Deal</h3>
                        <form method="POST" action="{{ route('crm.pipeline.store') }}">
                            @csrf
                            <input type="hidden" name="view_mode" value="kanban">
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
                            <input type="hidden" name="view_mode" value="kanban">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Deal Name*</label>
                                    <input type="text" name="deal_name" x-model="editDealName" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
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
                        <p class="text-gray-900 font-medium mb-6" x-text="deleteDealName"></p>
                        <form method="POST" :action="`{{ route('crm.pipeline.index') }}/${deleteId}`">
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

<script>
function kanbanBoard() {
    return {
        open: true,
        showCreate: false,
        showEdit: false,
        showDelete: false,
        editId: null,
        editDealName: '',
        editStage: 'prospect',
        editValue: 0,
        editOwner: '',
        editCloseDate: '',
        editProbability: 0,
        editContact: '',
        editCompany: '',
        editNotes: '',
        deleteId: null,
        deleteDealName: '',
        draggedDealId: null,

        dragStart(event, dealId) {
            this.draggedDealId = dealId;
            event.target.classList.add('dragging');
        },

        dragEnd(event) {
            event.target.classList.remove('dragging');
            document.querySelectorAll('.stage-column').forEach(col => {
                col.classList.remove('drag-over');
            });
        },

        dragOver(event) {
            event.currentTarget.classList.add('drag-over');
        },

        dragLeave(event) {
            event.currentTarget.classList.remove('drag-over');
        },

        async handleDrop(event, newStage) {
            event.currentTarget.classList.remove('drag-over');
            
            if (!this.draggedDealId) return;

            // Update stage via AJAX
            try {
                const response = await fetch(`{{ route('crm.pipeline.index') }}/${this.draggedDealId}/update-stage`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ stage: newStage })
                });

                const data = await response.json();
                
                if (data.success) {
                    // Reload page to show updated board
                    window.location.reload();
                } else {
                    alert('Failed to update deal stage');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while updating the deal');
            }

            this.draggedDealId = null;
        },

        editDeal(id, name, stage, value, owner, closeDate, probability, contact, company, notes) {
            this.editId = id;
            this.editDealName = name;
            this.editStage = stage;
            this.editValue = value;
            this.editOwner = owner;
            this.editCloseDate = closeDate;
            this.editProbability = probability;
            this.editContact = contact;
            this.editCompany = company;
            this.editNotes = notes;
            this.showEdit = true;
        },

        deleteDeal(id, name) {
            this.deleteId = id;
            this.deleteDealName = name;
            this.showDelete = true;
        }
    }
}
</script>

<style>
    [x-cloak] { display: none !important; }
</style>

</body>
</html>

