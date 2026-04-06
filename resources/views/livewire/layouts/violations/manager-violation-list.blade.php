<div class="flex flex-col w-full pb-6" style="font-family: 'Open Sans', sans-serif;">

    {{-- 1. TABS & ACTIONS ROW --}}
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 flex-shrink-0 gap-4">

        @php
            $tabs = [
                'all'          => 'All',
                'issued'       => 'Issued',
                'acknowledged' => 'Acknowledged',
                'resolved'     => 'Resolved',
            ];
        @endphp

        <x-ui.sort-tab
            :tabs="$tabs"
            :activeTab="$activeTab"
            :counts="$counts"
        />

        <div class="flex items-center gap-3 w-full md:w-auto justify-end">
            {{-- Add Violation Button --}}
            <button
                onclick="Livewire.dispatch('openModal', { component: 'layouts.violations.add-violation-modal' })"
                x-on:click="$dispatch('open-add-violation-modal')"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#070642] text-white text-xs font-bold rounded-xl hover:bg-[#0a0960] transition shadow-sm"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Record Violation
            </button>

            {{-- Building Filter --}}
            <x-dropdown label="{{ $selectedBuilding ? Str::before($selectedBuilding, ' ') . '...' : 'Building' }}" tooltip="Filter violations by building">
                <x-dropdown-item wire:click="$set('selectedBuilding', null)" @click="open = false">
                    All Buildings
                </x-dropdown-item>
                @foreach ($buildingOptions as $value => $label)
                    <x-dropdown-item
                        wire:click="$set('selectedBuilding', '{{ $value }}')"
                        @click="open = false"
                        :active="$selectedBuilding === $value"
                    >
                        {{ $label }}
                    </x-dropdown-item>
                @endforeach
            </x-dropdown>

            <x-ui.sort-dropdown model="sortOrder" :current="$sortOrder" />
        </div>
    </div>

    {{-- 2. MAIN CONTENT GRID --}}
    <div class="flex flex-col lg:flex-row gap-6 w-full">

        {{-- LEFT PANEL: LIST (30%) --}}
        <div class="w-full lg:w-[30%] flex-shrink-0 h-[750px] bg-white rounded-3xl shadow-sm border border-gray-100 flex flex-col overflow-hidden p-2">

            <div class="p-4 pb-3 border-b border-gray-50 flex-shrink-0">
                <h3 class="text-xl font-bold text-[#070642] mb-3">Violation Records</h3>

                <div class="relative">
                    <input
                        type="text"
                        placeholder="Search by violation #, tenant, or unit..."
                        wire:model.live="search"
                        class="w-full bg-[#F4F6FB] border border-slate-200 rounded-xl py-2.5 pl-4 pr-10 text-xs focus:outline-none focus:ring-2 focus:ring-blue-200 placeholder-slate-400 text-slate-700 transition"
                    >
                    <svg class="w-4 h-4 text-slate-400 absolute right-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-4 space-y-2.5" style="scrollbar-width: thin; scrollbar-color: #e2e8f0 transparent;">
                @forelse($violations as $vio)
                    @php
                        $isActive = $activeViolationId === $vio->violation_id;

                        $statusStyles = match($vio->status) {
                            'Resolved'     => 'bg-green-100 text-green-700',
                            'Issued'       => 'bg-red-100 text-red-700',
                            'Acknowledged' => 'bg-yellow-100 text-yellow-800',
                            default        => 'bg-gray-100 text-gray-700'
                        };

                        $severityStyles = match($vio->severity) {
                            'serious' => 'bg-red-50 text-red-600',
                            'major'   => 'bg-orange-50 text-orange-600',
                            'minor'   => 'bg-blue-50 text-blue-600',
                            default   => 'bg-gray-50 text-gray-600'
                        };

                        $offenseLabel = match($vio->offense_number) {
                            1 => '1st',
                            2 => '2nd',
                            3 => '3rd',
                            default => $vio->offense_number . 'th'
                        };
                    @endphp

                    <div wire:click="selectViolation({{ $vio->violation_id }})"
                         class="cursor-pointer p-4 rounded-2xl transition-all duration-200 border-2
                            {{ $isActive
                                ? 'border-[#0044F1] bg-[#1679FA] shadow-md'
                                : 'border-transparent bg-white ring-1 ring-gray-100 hover:border-[#93C5FD] hover:bg-[#EEF3FF] hover:shadow-sm' }}">

                        {{-- Top Row --}}
                        <div class="flex justify-between items-start">
                            <h3 class="font-bold text-sm {{ $isActive ? 'text-white' : 'text-[#2B66F5]' }}">
                                {{ $vio->violation_number }}
                            </h3>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $statusStyles }}">
                                {{ $vio->status }}
                            </span>
                        </div>

                        {{-- Middle Row --}}
                        <p class="text-xs font-bold uppercase tracking-wide my-3 {{ $isActive ? 'text-white' : 'text-[#070642]' }}">
                            {{ $vio->tenant_name ?? 'Unknown Tenant' }}
                            <span class="font-normal mx-1 {{ $isActive ? 'text-blue-200' : 'text-gray-300' }}">|</span>
                            Unit {{ $vio->unit_number }}
                        </p>

                        {{-- Bottom Row --}}
                        <div class="flex justify-between items-end">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $severityStyles }}">
                                    {{ ucfirst($vio->severity) }}
                                </span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-600">
                                    {{ $offenseLabel }} Offense
                                </span>
                            </div>
                            <p class="text-[11px] {{ $isActive ? 'text-blue-100' : 'text-gray-400' }}">
                                {{ \Carbon\Carbon::parse($vio->violation_date)->format('M d, Y') }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center h-full text-gray-400 py-16">
                        @if(!empty($search) || $activeTab !== 'all' || $selectedBuilding)
                            <div class="bg-gray-50 p-6 rounded-full mb-4">
                                <svg class="h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <p class="font-semibold text-gray-500 text-sm">No matching violations</p>
                            <p class="text-xs text-gray-400 mt-1">Try adjusting your search, filter, or tab.</p>
                        @else
                            <div class="bg-[#F4F7FF] p-6 rounded-full mb-4">
                                <svg class="h-10 w-10 text-[#2B66F5] opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                            </div>
                            <p class="font-semibold text-gray-500 text-sm">No violations recorded yet</p>
                            <p class="text-xs text-gray-400 mt-1 text-center px-4">Violation records will appear here once issued.</p>
                        @endif
                    </div>
                @endforelse
            </div>
        </div>

        {{-- RIGHT PANEL: DETAIL (70%) --}}
        <div class="w-full lg:w-[70%] h-[750px] bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            <livewire:layouts.violations.manager-violation-detail :initialViolationId="$activeViolationId" />
        </div>
    </div>
</div>
