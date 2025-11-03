<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM Reports & Analytics</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

<div x-data="{mobileMenu:false, open:true}" class="relative">
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
                <div class="glass w-full rounded-xl px-6 py-3 mb-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-white">
                    <div class="text-lg md:text-xl font-semibold tracking-wide">REPORTS & ANALYTICS</div>
                    <a href="{{ route('crm.reports.export', request()->query()) }}" class="px-4 py-2 bg-green-600 hover:bg-green-700 rounded-lg text-sm font-semibold transition whitespace-nowrap w-full sm:w-auto text-center">
                        Export Report
                    </a>
                </div>

                <!-- Filters -->
                <div class="glass rounded-xl p-4 mb-4">
                    <form method="GET" action="{{ route('crm.reports.index') }}" class="flex flex-col md:flex-row gap-4 items-end">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-white mb-1">Date From</label>
                            <input type="date" name="date_from" value="{{ $dateFrom }}" class="w-full border rounded-lg px-3 py-2 bg-white/60 text-gray-700">
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-white mb-1">Date To</label>
                            <input type="date" name="date_to" value="{{ $dateTo }}" class="w-full border rounded-lg px-3 py-2 bg-white/60 text-gray-700">
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-white mb-1">User ID (Optional)</label>
                            <input type="number" name="user_id" value="{{ $userId }}" placeholder="Filter by user" class="w-full border rounded-lg px-3 py-2 bg-white/60 text-gray-700">
                        </div>
                        <div class="flex gap-2 flex-shrink-0 w-full md:w-auto">
                            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold whitespace-nowrap flex-1 md:flex-initial">Apply Filters</button>
                            <a href="{{ route('crm.reports.index') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-semibold whitespace-nowrap flex-1 md:flex-initial">Reset</a>
                        </div>
                    </form>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <!-- Total Contacts -->
                <div class="glass rounded-xl p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white/80 text-sm font-medium">Total Contacts</p>
                            <h3 class="text-3xl font-bold text-white mt-2">{{ number_format($stats['total_contacts']) }}</h3>
                        </div>
                        <div class="p-3 bg-blue-500/30 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total Leads -->
                <div class="glass rounded-xl p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white/80 text-sm font-medium">Total Leads</p>
                            <h3 class="text-3xl font-bold text-white mt-2">{{ number_format($stats['total_leads']) }}</h3>
                            <p class="text-xs text-green-300 mt-1">{{ $stats['conversion_rate'] }}% converted</p>
                        </div>
                        <div class="p-3 bg-purple-500/30 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total Deals -->
                <div class="glass rounded-xl p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white/80 text-sm font-medium">Total Deals</p>
                            <h3 class="text-3xl font-bold text-white mt-2">{{ number_format($stats['total_deals']) }}</h3>
                            <p class="text-xs text-green-300 mt-1">Won: {{ $stats['deals_won'] }} | Lost: {{ $stats['deals_lost'] }}</p>
                        </div>
                        <div class="p-3 bg-yellow-500/30 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total Revenue -->
                <div class="glass rounded-xl p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white/80 text-sm font-medium">Total Revenue</p>
                            <h3 class="text-3xl font-bold text-white mt-2">${{ number_format($stats['total_revenue'], 2) }}</h3>
                            <p class="text-xs text-yellow-300 mt-1">Pending: ${{ number_format($stats['pending_revenue'], 2) }}</p>
                        </div>
                        <div class="p-3 bg-green-500/30 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Deals by Stage -->
                <div class="glass rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Deals by Stage</h3>
                    <canvas id="dealsByStageChart"></canvas>
                </div>

                <!-- Monthly Revenue Trend -->
                <div class="glass rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Revenue Trend (Last 6 Months)</h3>
                    <canvas id="monthlyRevenueChart"></canvas>
                </div>
            </div>

            <!-- Lead Sources -->
            <div class="glass rounded-xl p-6 mb-6">
                <h3 class="text-lg font-semibold text-white mb-4">Top Lead Sources</h3>
                <canvas id="leadSourcesChart" style="max-height: 300px;"></canvas>
            </div>

            <!-- User Performance Table -->
            <div class="glass rounded-xl p-6">
                <h3 class="text-lg font-semibold text-white mb-4">User Performance</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm bg-white/20 backdrop-blur-sm border border-white/30 rounded-xl">
                        <thead class="uppercase bg-white/40 text-gray-800">
                            <tr>
                                <th class="p-3 text-center font-semibold tracking-widest">User ID</th>
                                <th class="p-3 text-center font-semibold tracking-widest">Total Deals</th>
                                <th class="p-3 text-center font-semibold tracking-widest">Won</th>
                                <th class="p-3 text-center font-semibold tracking-widest">Lost</th>
                                <th class="p-3 text-center font-semibold tracking-widest">Win Rate</th>
                                <th class="p-3 text-center font-semibold tracking-widest">Total Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($userPerformance as $user)
                                <tr class="border-b border-white/30 bg-white/20 hover:bg-white/40 transition">
                                    <td class="p-3 text-center font-semibold text-white">User {{ $user->owner_user_id }}</td>
                                    <td class="p-3 text-center text-white">{{ $user->total_deals }}</td>
                                    <td class="p-3 text-center text-green-300 font-semibold">{{ $user->won_deals }}</td>
                                    <td class="p-3 text-center text-red-300 font-semibold">{{ $user->lost_deals }}</td>
                                    <td class="p-3 text-center text-white">
                                        @php
                                            $winRate = $user->total_deals > 0 ? round(($user->won_deals / $user->total_deals) * 100, 2) : 0;
                                        @endphp
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold 
                                            {{ $winRate >= 50 ? 'bg-green-500 text-white' : 'bg-red-500 text-white' }}">
                                            {{ $winRate }}%
                                        </span>
                                    </td>
                                    <td class="p-3 text-center text-white font-bold">${{ number_format($user->total_revenue, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-8 text-center text-white">No user performance data available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Deals by Stage Chart
const dealsByStageCtx = document.getElementById('dealsByStageChart').getContext('2d');
const dealsByStageChart = new Chart(dealsByStageCtx, {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($dealsByStage->pluck('stage')->map(fn($s) => ucfirst(str_replace('_', ' ', $s)))->toArray()) !!},
        datasets: [{
            label: 'Deals',
            data: {!! json_encode($dealsByStage->pluck('count')->toArray()) !!},
            backgroundColor: [
                'rgba(156, 163, 175, 0.8)',
                'rgba(59, 130, 246, 0.8)',
                'rgba(251, 191, 36, 0.8)',
                'rgba(34, 197, 94, 0.8)',
                'rgba(239, 68, 68, 0.8)'
            ],
            borderColor: 'rgba(255, 255, 255, 0.8)',
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: { color: '#fff', padding: 15, font: { size: 12 } }
            }
        }
    }
});

// Monthly Revenue Chart
const monthlyRevenueCtx = document.getElementById('monthlyRevenueChart').getContext('2d');
const monthlyRevenueChart = new Chart(monthlyRevenueCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($monthlyRevenue->map(fn($m) => date('M Y', mktime(0, 0, 0, $m->month, 1, $m->year)))->toArray()) !!},
        datasets: [{
            label: 'Revenue ($)',
            data: {!! json_encode($monthlyRevenue->pluck('revenue')->toArray()) !!},
            borderColor: 'rgba(34, 197, 94, 1)',
            backgroundColor: 'rgba(34, 197, 94, 0.2)',
            fill: true,
            tension: 0.4,
            borderWidth: 3,
            pointRadius: 5,
            pointBackgroundColor: 'rgba(34, 197, 94, 1)'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { labels: { color: '#fff', font: { size: 12 } } }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { color: '#fff' },
                grid: { color: 'rgba(255, 255, 255, 0.1)' }
            },
            x: {
                ticks: { color: '#fff' },
                grid: { color: 'rgba(255, 255, 255, 0.1)' }
            }
        }
    }
});

// Lead Sources Chart
const leadSourcesCtx = document.getElementById('leadSourcesChart').getContext('2d');
const leadSourcesChart = new Chart(leadSourcesCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($leadsBySource->pluck('source')->map(fn($s) => ucfirst($s))->toArray()) !!},
        datasets: [{
            label: 'Leads',
            data: {!! json_encode($leadsBySource->pluck('count')->toArray()) !!},
            backgroundColor: 'rgba(147, 51, 234, 0.8)',
            borderColor: 'rgba(147, 51, 234, 1)',
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { labels: { color: '#fff', font: { size: 12 } } }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { color: '#fff', stepSize: 1 },
                grid: { color: 'rgba(255, 255, 255, 0.1)' }
            },
            x: {
                ticks: { color: '#fff' },
                grid: { color: 'rgba(255, 255, 255, 0.1)' }
            }
        }
    }
});
</script>

</body>
</html>

