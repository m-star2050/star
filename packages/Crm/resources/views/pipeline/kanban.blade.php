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
        .kanban-column { min-height: 500px; }
        .deal-card { cursor: move; transition: all 0.2s; }
        .deal-card:hover { transform: translateY(-2px); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .dragging { opacity: 0.5; }
        .drag-over { border-color: #3b82f6; background: rgba(59, 130, 246, 0.05); }
    </style>
</head>
<body>

<div x-data="kanbanBoard()" class="relative">
    <aside class="fixed top-3 left-3 h-[calc(100vh-24px)] glass rounded-2xl p-3 transition-all duration-300" :class="open ? 'w-64' : 'w-16'">
        <div class="flex items-center justify-between mb-4">
            <div class="text-gray-900 font-extrabold tracking-wide mt-5" :class="open ? 'opacity-100' : 'opacity-0 pointer-events-none'">WELCOME USER</div>
            <button @click="open=!open" class="text-white bg-white/20 border border-white/40 rounded-full w-7 h-7 flex items-center justify-center hover:bg-white/30 mt-5" :aria-expanded="open">
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
            <a href="{{ route('crm.pipeline.kanban') }}" class="sidebar-link bg-white/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M3 12v3c0 1.657 3.134 3 7 3s7-1.343 7-3v-3c0 1.657-3.134 3-7 3s-7-1.343-7-3z"/><path d="M3 7v3c0 1.657 3.134 3 7 3s7-1.343 7-3V7c0 1.657-3.134 3-7 3S3 8.657 3 7z"/><path d="M17 5c0 1.657-3.134 3-7 3S3 6.657 3 5s3.134-3 7-3 7 1.343 7 3z"/></svg>
                <span x-show="open" x-transition>Pipeline</span>
            </a>
        </nav>
    </aside>

    <div class="transition-all duration-300" :style="open ? 'padding-left:280px' : 'padding-left:88px'">
        <div class="min-h-screen px-2 py-8">
            <div class="w-full max-w-7xl mx-auto px-3 md:px-4 py-3">
                <div class="glass w-full rounded-xl px-6 py-3 mb-4 flex items-center justify-between text-white">
                    <div class="text-lg md:text-xl font-semibold tracking-wide">PIPELINE - KANBAN VIEW</div>
                    <div class="flex items-center gap-3">
                        <button type="button" @click="showCreate=true" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium shadow-md text-sm">
                            + New Deal
                        </button>
                        <a href="{{ route('crm.pipeline.index') }}" class="px-4 py-2 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-medium shadow-md text-sm">
                            Switch to List
                        </a>
                    </div>
                </div>

                <!-- Kanban Board -->
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <!-- Prospect Column -->
                    <div class="bg-white/90 backdrop-blur-sm rounded-xl p-4 shadow-lg">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-bold text-gray-800 text-sm uppercase">Prospect</h3>
                            <span class="bg-gray-200 text-gray-700 rounded-full px-2 py-1 text-xs font-semibold" x-text="deals.prospect.length"></span>
                        </div>
                        <div 
                            class="kanban-column space-y-3"
                            @drop="handleDrop($event, 'prospect')"
                            @dragover.prevent="handleDragOver($event)"
                            @dragleave="handleDragLeave($event)"
                        >
                            <template x-for="deal in deals.prospect" :key="deal.id">
                                <div 
                                    class="deal-card bg-white border border-gray-200 rounded-lg p-3 shadow-sm"
                                    draggable="true"
                                    @dragstart="handleDragStart($event, deal)"
                                    @dragend="handleDragEnd($event)"
                                >
                                    <h4 class="font-semibold text-gray-900 mb-2 text-sm" x-text="deal.deal_name"></h4>
                                    <div class="text-lg font-bold text-green-600 mb-2" x-text="'$' + parseFloat(deal.value).toLocaleString()"></div>
                                    <div class="text-xs text-gray-600 space-y-1">
                                        <div x-show="deal.company"><span class="font-medium">Company:</span> <span x-text="deal.company"></span></div>
                                        <div x-show="deal.close_date"><span class="font-medium">Close:</span> <span x-text="deal.close_date"></span></div>
                                        <div x-show="deal.probability"><span class="font-medium">Probability:</span> <span x-text="deal.probability + '%'"></span></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Negotiation Column -->
                    <div class="bg-white/90 backdrop-blur-sm rounded-xl p-4 shadow-lg">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-bold text-blue-800 text-sm uppercase">Negotiation</h3>
                            <span class="bg-blue-100 text-blue-700 rounded-full px-2 py-1 text-xs font-semibold" x-text="deals.negotiation.length"></span>
                        </div>
                        <div 
                            class="kanban-column space-y-3"
                            @drop="handleDrop($event, 'negotiation')"
                            @dragover.prevent="handleDragOver($event)"
                            @dragleave="handleDragLeave($event)"
                        >
                            <template x-for="deal in deals.negotiation" :key="deal.id">
                                <div 
                                    class="deal-card bg-white border border-blue-200 rounded-lg p-3 shadow-sm"
                                    draggable="true"
                                    @dragstart="handleDragStart($event, deal)"
                                    @dragend="handleDragEnd($event)"
                                >
                                    <h4 class="font-semibold text-gray-900 mb-2 text-sm" x-text="deal.deal_name"></h4>
                                    <div class="text-lg font-bold text-green-600 mb-2" x-text="'$' + parseFloat(deal.value).toLocaleString()"></div>
                                    <div class="text-xs text-gray-600 space-y-1">
                                        <div x-show="deal.company"><span class="font-medium">Company:</span> <span x-text="deal.company"></span></div>
                                        <div x-show="deal.close_date"><span class="font-medium">Close:</span> <span x-text="deal.close_date"></span></div>
                                        <div x-show="deal.probability"><span class="font-medium">Probability:</span> <span x-text="deal.probability + '%'"></span></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Proposal Column -->
                    <div class="bg-white/90 backdrop-blur-sm rounded-xl p-4 shadow-lg">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-bold text-yellow-800 text-sm uppercase">Proposal</h3>
                            <span class="bg-yellow-100 text-yellow-700 rounded-full px-2 py-1 text-xs font-semibold" x-text="deals.proposal.length"></span>
                        </div>
                        <div 
                            class="kanban-column space-y-3"
                            @drop="handleDrop($event, 'proposal')"
                            @dragover.prevent="handleDragOver($event)"
                            @dragleave="handleDragLeave($event)"
                        >
                            <template x-for="deal in deals.proposal" :key="deal.id">
                                <div 
                                    class="deal-card bg-white border border-yellow-200 rounded-lg p-3 shadow-sm"
                                    draggable="true"
                                    @dragstart="handleDragStart($event, deal)"
                                    @dragend="handleDragEnd($event)"
                                >
                                    <h4 class="font-semibold text-gray-900 mb-2 text-sm" x-text="deal.deal_name"></h4>
                                    <div class="text-lg font-bold text-green-600 mb-2" x-text="'$' + parseFloat(deal.value).toLocaleString()"></div>
                                    <div class="text-xs text-gray-600 space-y-1">
                                        <div x-show="deal.company"><span class="font-medium">Company:</span> <span x-text="deal.company"></span></div>
                                        <div x-show="deal.close_date"><span class="font-medium">Close:</span> <span x-text="deal.close_date"></span></div>
                                        <div x-show="deal.probability"><span class="font-medium">Probability:</span> <span x-text="deal.probability + '%'"></span></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Closed Won Column -->
                    <div class="bg-white/90 backdrop-blur-sm rounded-xl p-4 shadow-lg">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-bold text-green-800 text-sm uppercase">Closed Won</h3>
                            <span class="bg-green-100 text-green-700 rounded-full px-2 py-1 text-xs font-semibold" x-text="deals.closed_won.length"></span>
                        </div>
                        <div 
                            class="kanban-column space-y-3"
                            @drop="handleDrop($event, 'closed_won')"
                            @dragover.prevent="handleDragOver($event)"
                            @dragleave="handleDragLeave($event)"
                        >
                            <template x-for="deal in deals.closed_won" :key="deal.id">
                                <div 
                                    class="deal-card bg-white border border-green-200 rounded-lg p-3 shadow-sm"
                                    draggable="true"
                                    @dragstart="handleDragStart($event, deal)"
                                    @dragend="handleDragEnd($event)"
                                >
                                    <h4 class="font-semibold text-gray-900 mb-2 text-sm" x-text="deal.deal_name"></h4>
                                    <div class="text-lg font-bold text-green-600 mb-2" x-text="'$' + parseFloat(deal.value).toLocaleString()"></div>
                                    <div class="text-xs text-gray-600 space-y-1">
                                        <div x-show="deal.company"><span class="font-medium">Company:</span> <span x-text="deal.company"></span></div>
                                        <div x-show="deal.close_date"><span class="font-medium">Close:</span> <span x-text="deal.close_date"></span></div>
                                        <div x-show="deal.probability"><span class="font-medium">Probability:</span> <span x-text="deal.probability + '%'"></span></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Closed Lost Column -->
                    <div class="bg-white/90 backdrop-blur-sm rounded-xl p-4 shadow-lg">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-bold text-red-800 text-sm uppercase">Closed Lost</h3>
                            <span class="bg-red-100 text-red-700 rounded-full px-2 py-1 text-xs font-semibold" x-text="deals.closed_lost.length"></span>
                        </div>
                        <div 
                            class="kanban-column space-y-3"
                            @drop="handleDrop($event, 'closed_lost')"
                            @dragover.prevent="handleDragOver($event)"
                            @dragleave="handleDragLeave($event)"
                        >
                            <template x-for="deal in deals.closed_lost" :key="deal.id">
                                <div 
                                    class="deal-card bg-white border border-red-200 rounded-lg p-3 shadow-sm"
                                    draggable="true"
                                    @dragstart="handleDragStart($event, deal)"
                                    @dragend="handleDragEnd($event)"
                                >
                                    <h4 class="font-semibold text-gray-900 mb-2 text-sm" x-text="deal.deal_name"></h4>
                                    <div class="text-lg font-bold text-green-600 mb-2" x-text="'$' + parseFloat(deal.value).toLocaleString()"></div>
                                    <div class="text-xs text-gray-600 space-y-1">
                                        <div x-show="deal.company"><span class="font-medium">Company:</span> <span x-text="deal.company"></span></div>
                                        <div x-show="deal.close_date"><span class="font-medium">Close:</span> <span x-text="deal.close_date"></span></div>
                                        <div x-show="deal.probability"><span class="font-medium">Probability:</span> <span x-text="deal.probability + '%'"></span></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Deal Modal -->
    <div x-show="showCreate" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/50" @click="showCreate=false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-xl p-6">
            <div class="text-lg font-semibold mb-4">Create Deal</div>
            <form method="POST" action="{{ route('crm.pipeline.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf
                <input type="hidden" name="view_mode" value="kanban">
                <input name="deal_name" class="border rounded-lg px-3 py-2 md:col-span-2" placeholder="Deal Name" required>
                <select name="stage" class="border rounded-lg px-3 py-2">
                    <option value="prospect" selected>Prospect</option>
                    <option value="negotiation">Negotiation</option>
                    <option value="proposal">Proposal</option>
                    <option value="closed_won">Closed Won</option>
                    <option value="closed_lost">Closed Lost</option>
                </select>
                <input name="value" type="number" step="0.01" class="border rounded-lg px-3 py-2" placeholder="Deal Value" required>
                <input name="owner_user_id" type="number" class="border rounded-lg px-3 py-2" placeholder="Owner User ID">
                <input name="close_date" type="date" class="border rounded-lg px-3 py-2" placeholder="Close Date">
                <input name="probability" type="number" min="0" max="100" class="border rounded-lg px-3 py-2" placeholder="Probability (%)">
                <input name="company" class="border rounded-lg px-3 py-2" placeholder="Company Name">
                <input name="contact_id" type="number" class="border rounded-lg px-3 py-2" placeholder="Contact ID (optional)">
                <textarea name="notes" class="border rounded-lg px-3 py-2 md:col-span-2" rows="3" placeholder="Notes"></textarea>
                <div class="md:col-span-2 flex justify-end gap-2 mt-2">
                    <button type="button" class="px-4 py-2 rounded-lg border" @click="showCreate=false">Cancel</button>
                    <button class="px-4 py-2 rounded-lg bg-blue-600 text-white">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function kanbanBoard() {
    return {
        open: true,
        showCreate: false,
        draggedDeal: null,
        deals: {
            prospect: @json($dealsByStage['prospect'] ?? []),
            negotiation: @json($dealsByStage['negotiation'] ?? []),
            proposal: @json($dealsByStage['proposal'] ?? []),
            closed_won: @json($dealsByStage['closed_won'] ?? []),
            closed_lost: @json($dealsByStage['closed_lost'] ?? []),
        },

        handleDragStart(event, deal) {
            this.draggedDeal = deal;
            event.target.classList.add('dragging');
            event.dataTransfer.effectAllowed = 'move';
        },

        handleDragEnd(event) {
            event.target.classList.remove('dragging');
        },

        handleDragOver(event) {
            event.currentTarget.classList.add('drag-over');
        },

        handleDragLeave(event) {
            event.currentTarget.classList.remove('drag-over');
        },

        handleDrop(event, newStage) {
            event.currentTarget.classList.remove('drag-over');
            
            if (!this.draggedDeal) return;

            const oldStage = this.draggedDeal.stage;
            
            if (oldStage === newStage) {
                this.draggedDeal = null;
                return;
            }

            // Remove from old stage
            this.deals[oldStage] = this.deals[oldStage].filter(d => d.id !== this.draggedDeal.id);
            
            // Add to new stage
            this.draggedDeal.stage = newStage;
            this.deals[newStage].push(this.draggedDeal);

            // Update in database via AJAX
            this.updateDealStage(this.draggedDeal.id, newStage);

            this.draggedDeal = null;
        },

        updateDealStage(dealId, newStage) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            
            fetch(`/crm/pipeline/${dealId}/update-stage`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ stage: newStage })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('Failed to update deal stage');
                    // Reload page if update fails
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to update deal stage');
                window.location.reload();
            });
        }
    }
}
</script>
</body>
</html>

