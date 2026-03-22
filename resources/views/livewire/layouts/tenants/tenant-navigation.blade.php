{{-- resources/views/livewire/layouts/tenants/tenant-navigation.blade.php --}}

<div class="flex flex-col w-full" style="font-family: 'Open Sans', sans-serif;">

    {{-- 1. TABS & ACTIONS ROW --}}
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 flex-shrink-0 gap-4">

        {{-- Left Side: Tabs --}}
        @php
            $tabs = [
                'current'     => 'Current',
                'transferred' => 'Transferred',
                'moved_out'   => 'Moved Out',
            ];
        @endphp

        <x-ui.sort-tab
            :tabs="$tabs"
            :activeTab="$activeTab"
            :counts="$counts"
            action="setTab"
            size="lg"
        />

        {{-- Right Side: Search & Sort --}}
        <div class="flex items-center gap-3">
            <x-ui.search-bar
                model="search"
                placeholder="Search..."
                :suggestions="$suggestions"
            />
            <x-ui.sort-dropdown model="sortOrder" :current="$sortOrder" />
        </div>

    </div>

    {{-- 2. MAIN CONTENT GRID --}}
    <div class="flex flex-col lg:flex-row gap-6 w-full">

        {{-- LEFT PANEL: LIST (30% width) --}}
        <div class="w-full lg:w-[30%] flex-shrink-0 h-[750px] bg-white rounded-3xl shadow-sm border border-gray-100 flex flex-col overflow-hidden p-2">

            {{-- Header Section with Title and Add Button --}}
            <div class="p-4 pb-3 border-b border-gray-50 flex-shrink-0 flex items-center justify-between">
                <h3 class="text-xl font-bold text-[#070642]">Tenants</h3>
                <button
                    type="button"
                    x-on:click="$dispatch('open-add-tenant-modal')"
                    class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    <span class="text-sm">Add</span>
                </button>
            </div>

            {{-- List Body --}}
            <div class="flex-1 overflow-y-auto p-4 space-y-2.5" style="scrollbar-width: thin; scrollbar-color: #e2e8f0 transparent;">
                @forelse ($tenants as $tenant)
                    @php
                        $isActive = ($tenant['id'] == $activeTenantId);

                        $statusStyles = match($tenant['payment_status']) {
                            'Paid'        => 'bg-green-100 text-green-700',
                            'Pending'     => 'bg-yellow-100 text-yellow-800',
                            'Overdue'     => 'bg-red-100 text-red-700',
                            'Transferred' => 'bg-blue-100 text-blue-700',
                            'Moved Out'   => 'bg-gray-200 text-gray-600',
                            default       => 'bg-gray-100 text-gray-700'
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
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $statusStyles }}">
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
                                <p class="text-[10px] {{ $isActive ? 'text-blue-100' : 'text-gray-400' }}">
                                    {{ \Carbon\Carbon::parse($tenant['next_billing'])->format('M d, Y') }}
                                </p>
                            @else
                                <p class="text-[10px] {{ $isActive ? 'text-blue-100' : 'text-gray-400' }}">
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
            <livewire:layouts.tenants.tenant-detail />
        </div>

    </div>

    <livewire:layouts.tenants.add-tenant-modal />

</div>
