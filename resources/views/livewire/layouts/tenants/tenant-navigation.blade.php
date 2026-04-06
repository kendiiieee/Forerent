{{-- resources/views/livewire/layouts/tenants/tenant-navigation.blade.php --}}

<div class="flex flex-col w-full" style="font-family: 'Open Sans', sans-serif;">

    {{-- 1. TABS & ACTIONS ROW --}}
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 flex-shrink-0 gap-4">

        {{-- Left Side: Tabs --}}
        @php
            $tabs = [
                'current'     => 'Current',
                'moving_out'  => 'Moving Out',
                'transferred' => 'Transferred',
                'moved_out'   => 'Moved Out',
            ];
        @endphp

        <x-ui.sort-tab
            :tabs="$tabs"
            :activeTab="$activeTab"
            :counts="$counts"
            action="setTab"

        />

        {{-- Right Side: Building Filter, Sort & Add Button --}}
        <div class="flex items-center gap-3">
            <x-ui.sort-dropdown model="sortOrder" :current="$sortOrder" />

            {{-- Building Filter Dropdown --}}
            <x-dropdown label="{{ $selectedBuildingName ? explode(' ', $selectedBuildingName)[0] : 'Building' }}" tooltip="Filter tenants by building">
                <x-dropdown-item
                    wire:click="selectBuilding(null)"
                    @click="open = false"
                    :active="$selectedBuildingId === null"
                >
                    All Buildings
                </x-dropdown-item>

                @foreach ($buildingOptions as $id => $name)
                    <x-dropdown-item
                        wire:click="selectBuilding({{ $id }})"
                        @click="open = false"
                        :active="$selectedBuildingId === $id"
                    >
                        {{ $name }}
                    </x-dropdown-item>
                @endforeach
            </x-dropdown>

            <x-ui.button-add
                text="Add Tenant"
                tooltip="Register a new tenant to a unit"
                x-on:click="$dispatch('open-add-tenant-modal')"
            />
        </div>

    </div>

    {{-- 2. MAIN CONTENT GRID --}}
    <div class="flex flex-col lg:flex-row gap-6 w-full">

        {{-- LEFT PANEL: LIST (30% width) --}}
        <div class="w-full lg:w-[30%] flex-shrink-0 h-[750px] bg-white rounded-3xl shadow-sm border border-gray-100 flex flex-col overflow-hidden p-2">

            {{-- Header Section with Title and Search --}}
            <div class="p-4 pb-3 border-b border-gray-50 flex-shrink-0 space-y-3">
                <h3 class="text-xl font-bold text-[#070642]">Tenants</h3>
                <x-ui.search-bar
                    model="search"
                    placeholder="Search..."
                    :suggestions="$suggestions"
                />
            </div>

            {{-- List Body --}}
            <div class="flex-1 overflow-y-auto p-4 space-y-2.5" style="scrollbar-width: thin; scrollbar-color: #e2e8f0 transparent;">
                @forelse ($tenants as $tenant)
                    @php
                        $isActive = ($tenant['id'] == $activeTenantId);

                        $statusStyles = match($tenant['payment_status']) {
                            'Paid'        => 'text-emerald-700 bg-emerald-50 border-emerald-200',
                            'Unpaid'      => 'text-amber-700 bg-amber-50 border-amber-200',
                            'Pending'     => 'text-amber-700 bg-amber-50 border-amber-200',
                            'Overdue'     => 'text-red-700 bg-red-50 border-red-200',
                            'Moving Out'  => 'text-orange-700 bg-orange-50 border-orange-200',
                            'Transferred' => 'text-blue-700 bg-blue-50 border-blue-200',
                            'Moved Out'   => 'text-gray-600 bg-gray-50 border-gray-200',
                            default       => 'text-gray-600 bg-gray-50 border-gray-200'
                        };
                        $dotStyles = match($tenant['payment_status']) {
                            'Paid'        => 'bg-emerald-500',
                            'Unpaid'      => 'bg-amber-500',
                            'Pending'     => 'bg-amber-500',
                            'Overdue'     => 'bg-red-500',
                            'Moving Out'  => 'bg-orange-500',
                            'Transferred' => 'bg-blue-500',
                            'Moved Out'   => 'bg-gray-400',
                            default       => 'bg-gray-400'
                        };
                    @endphp

                    <button
                        type="button"
                        wire:click="selectTenant({{ $tenant['id'] }})"
                        class="cursor-pointer w-full text-left p-5 rounded-2xl transition-all duration-200 border-2
                            {{ $isActive
                                ? 'border-[#0044F1] bg-[#1679FA] shadow-md'
                                : 'border-transparent bg-white ring-1 ring-gray-100 hover:border-[#93C5FD] hover:bg-[#EEF3FF] hover:shadow-sm' }}"
                    >
                        {{-- Top Row: Tenant Name and Status Badge --}}
                        <div class="flex justify-between items-center">
                            <h3 class="font-bold text-sm {{ $isActive ? 'text-white' : 'text-[#2B66F5]' }}">
                                {{ $tenant['first_name'] }} {{ $tenant['last_name'] }}
                            </h3>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $statusStyles }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $dotStyles }}"></span>
                                {{ $tenant['payment_status'] }}
                            </span>
                        </div>

                        {{-- Middle Row: Unit and Bed Info with Date --}}
                        <div class="flex justify-between items-center mt-3">
                            <p class="text-xs font-bold uppercase tracking-wide {{ $isActive ? 'text-white' : 'text-[#070642]' }}">
                                Unit {{ $tenant['unit'] }}
                                <span class="font-normal mx-1 {{ $isActive ? 'text-blue-200' : 'text-gray-300' }}">|</span>
                                Bed {{ $tenant['bed_number'] }}
                            </p>
                            @if($tenant['next_billing'])
                                <p class="text-[11px] {{ $isActive ? 'text-blue-100' : 'text-gray-400' }}">
                                    {{ \Carbon\Carbon::parse($tenant['next_billing'])->format('M d, Y') }}
                                </p>
                            @else
                                <p class="text-[11px] {{ $isActive ? 'text-blue-100' : 'text-gray-400' }}">
                                    No date
                                </p>
                            @endif
                        </div>

                    </button>
                @empty
                    <div class="flex flex-col items-center justify-center h-full text-gray-400 py-16">
                        <div class="bg-[#F4F7FF] p-6 rounded-full mb-4">
                            <svg class="h-10 w-10 text-[#2B66F5] opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                            </svg>
                        </div>
                        <p class="font-semibold text-gray-500 text-sm">No tenants found</p>
                        <p class="text-xs text-gray-400 mt-1">There are currently no tenants in this property.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- RIGHT PANEL: DETAIL (70% width) --}}
        <div class="w-full lg:w-[70%] h-[750px] bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            <livewire:layouts.tenants.tenant-detail :initialTenantId="$activeTenantId" />
        </div>

    </div>

    <livewire:layouts.tenants.add-tenant-modal />
    <livewire:layouts.violations.add-violation-modal />

</div>
