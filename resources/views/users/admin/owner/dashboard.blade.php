@section('header-title', 'DASHBOARD')
@section('header-subtitle', 'Centralized rental property management overview')

<div class="w-full space-y-6">

    @include('livewire.layouts.dashboard.admingreeting')

    {{-- 1. Notifications + Calendar Section --}}
    <livewire:layouts.dashboard.announcement-list :is-landlord="true" />
    <livewire:layouts.dashboard.calendar-widget />

    @php
        $kpiCards = [
            [
                'title' => 'Total Units',
                'value' => $totalUnits,
                'prev' => $prevTotalUnits,
                'desc' => 'All units in inventory',
                'gradientFrom' => '#1a3ed0',
                'gradientTo' => '#6ea8fe',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />',
            ],
            [
                'title' => 'Fully Booked Units',
                'value' => $fullyBookedUnits,
                'prev' => $prevFullyBookedUnits,
                'desc' => 'All beds have active leases',
                'gradientFrom' => '#047857',
                'gradientTo' => '#6ee7b7',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />',
            ],
            [
                'title' => 'Available Units',
                'value' => $availableUnits,
                'prev' => $prevAvailableUnits,
                'desc' => 'At least one bed still open',
                'gradientFrom' => '#5b21b6',
                'gradientTo' => '#c4b5fd',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />',
            ],
            [
                'title' => 'Vacant Units',
                'value' => $vacantUnits,
                'prev' => $prevVacantUnits,
                'desc' => 'No active lease on any bed',
                'gradientFrom' => '#b45309',
                'gradientTo' => '#fcd34d',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />',
            ],
        ];
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
        @foreach ($kpiCards as $card)
            @php
                $current = $card['value'];
            @endphp
            <div class="rounded-2xl p-6 shadow-lg relative overflow-hidden"
                 style="background: linear-gradient(135deg, {{ $card['gradientFrom'] }}, {{ $card['gradientTo'] }});">
                {{-- Decorative circle --}}
                <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full" style="background: rgba(255,255,255,0.1);"></div>

                <div class="flex items-start justify-between relative z-10">
                    <div>
                        <p class="text-sm font-medium" style="color: rgba(255,255,255,0.8);">{{ $card['title'] }}</p>
                        <p class="text-4xl font-bold text-white mt-2">{{ number_format($current) }}</p>
                        <p class="mt-2 text-sm" style="color: rgba(255,255,255,0.7);">{{ $card['desc'] }}</p>
                    </div>
                    <div class="rounded-xl p-2.5" style="background: rgba(255,255,255,0.2);">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $card['icon'] !!}</svg>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Pending Contracts Widget --}}
    @if($pendingContractsCount > 0)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-800">Contracts Awaiting Signature</h3>
                        <p class="text-xs text-gray-400">{{ $pendingContractsCount }} active {{ Str::plural('contract', $pendingContractsCount) }} pending</p>
                    </div>
                </div>
                <a href="{{ route('landlord.property') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                    View All
                </a>
            </div>

            <div class="divide-y divide-gray-50">
                @foreach($pendingContracts as $contract)
                    @php
                        $cLabel = match($contract['contract_status']) {
                            'pending_manager' => 'Pending Manager',
                            'pending_tenant' => 'Pending Tenant',
                            'pending_owner' => 'Pending Owner',
                            'pending_signatures' => 'Pending',
                            'draft' => 'Draft',
                            default => ucfirst(str_replace('_', ' ', $contract['contract_status'])),
                        };
                    @endphp
                    <div class="px-6 py-3.5 flex items-center justify-between hover:bg-gray-50/50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-xs font-bold text-blue-700">
                                {{ $contract['tenant_initial'] }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">{{ $contract['tenant_name'] }}</p>
                                <p class="text-[11px] text-gray-400">{{ $contract['property'] }} &middot; Unit {{ $contract['unit'] }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            {{-- Signature indicators --}}
                            <div class="flex items-center gap-0.5">
                                <div class="w-5 h-5 rounded-full flex items-center justify-center text-[9px] font-bold {{ !$contract['needs_owner_sign'] ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-100 text-gray-400' }}">O</div>
                                <div class="w-5 h-5 rounded-full flex items-center justify-center text-[9px] font-bold bg-gray-100 text-gray-400">M</div>
                                <div class="w-5 h-5 rounded-full flex items-center justify-center text-[9px] font-bold bg-gray-100 text-gray-400">T</div>
                                <span class="text-[10px] text-gray-400 ml-0.5">{{ $contract['sig_count'] }}/3</span>
                            </div>

                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-700">{{ $cLabel }}</span>

                            @if($contract['needs_owner_sign'])
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold text-blue-600 bg-blue-50 rounded-full">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                                    Sign Now
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- 2. Financial Overview with Graphs --}}
    <div class="space-y-6">
        <h3 class="text-2xl font-bold text-[#070642]">Financial Overview</h3>

        {{-- Graph Layout: Large left, single summary card right --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-stretch">

            {{-- Left: Revenue vs Expenses (spans 2 columns) --}}
            <div class="xl:col-span-2 h-full" wire:ignore>
                @include('livewire.layouts.dashboard.revenue-expenses-chart')
            </div>

            {{-- Right Column: Single rent summary card --}}
            <div>
                @include('livewire.layouts.dashboard.rent-collected-chart')
            </div>
        </div>
    </div>

    {{-- Modal (Hidden by default) --}}
    <livewire:layouts.dashboard.announcement-modal />

</div>

