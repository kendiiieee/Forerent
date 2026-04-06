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

