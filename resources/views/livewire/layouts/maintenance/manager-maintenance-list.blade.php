<div class="flex flex-col w-full pb-6" style="font-family: 'Open Sans', sans-serif;">

    {{-- 1. TABS & ACTIONS ROW --}}
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 flex-shrink-0 gap-4">

        {{-- Left Side: Sort Tabs --}}
        @php
            $tabs = [
                'all'       => 'All',
                'pending'   => 'Pending',
                'ongoing'   => 'Ongoing',
                'completed' => 'Completed',
            ];
        @endphp

        <x-ui.sort-tab
            :tabs="$tabs"
            :activeTab="$activeTab"
            :counts="$counts"
            size="lg"
        />

        {{-- Right Side: Sort Dropdown --}}
        <div class="flex items-center gap-4">
            <x-ui.sort-dropdown model="sortOrder" :current="$sortOrder" />
        </div>

    </div>

    {{-- 2. MAIN CONTENT GRID --}}
    <div class="flex flex-col lg:flex-row gap-6 w-full">

        {{-- LEFT PANEL: LIST (30% width) --}}
        <div class="w-full lg:w-[30%] flex-shrink-0 h-[750px] bg-white rounded-3xl shadow-sm border border-gray-100 flex flex-col overflow-hidden p-2">

            {{-- List Header --}}
            <div class="p-4 pb-2 border-b border-gray-50 flex-shrink-0">
                <h3 class="text-xl font-bold text-[#070642]">Maintenance Requests</h3>
            </div>

            {{-- List Body --}}
            <div class="flex-1 overflow-y-auto p-4 space-y-2.5" style="scrollbar-width: thin; scrollbar-color: #e2e8f0 transparent;">
                @forelse($requests as $req)
                    @php
                        $isActive = $activeRequestId === $req->request_id;

                        // Exact badge colors
                        $statusStyles = match($req->status) {
                            'Completed', 'Resolved'  => 'bg-green-100 text-green-700',
                            'Pending'                => 'bg-orange-100 text-orange-700',
                            'In Progress', 'Ongoing' => 'bg-yellow-100 text-yellow-800',
                            default                  => 'bg-gray-100 text-gray-700'
                        };

                        $ticketId = $req->ticket_number ?? 'TKT-' . str_pad($req->request_id, 4, '0', STR_PAD_LEFT);
                    @endphp

                    <div wire:click="selectRequest({{ $req->request_id }})"
                         {{--
                            STATE MANAGEMENT:
                            Inactive base: border-transparent, bg-white, grey ring
                            Inactive HOVER: Light blue background (`#EEF3FF`) and border (`#93C5FD` / blue-300)
                            Active CLICKED: Darker blue background (`#1679FA`) and border (`#0044F1`)
                         --}}
                         class="cursor-pointer p-4 rounded-2xl transition-all duration-200 border-2
                            {{ $isActive
                                ? 'border-[#0044F1] bg-[#1679FA] shadow-md'
                                : 'border-transparent bg-white ring-1 ring-gray-100 hover:border-[#93C5FD] hover:bg-[#EEF3FF] hover:shadow-sm' }}">

                        {{-- Top Row: Ticket ID and Status Badge --}}
                        <div class="flex justify-between items-start">
                            <h3 class="font-bold text-sm {{ $isActive ? 'text-white' : 'text-[#2B66F5]' }}">
                                {{ $ticketId }}
                            </h3>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $statusStyles }}">
                                {{ $req->status }}
                            </span>
                        </div>

                        {{-- Middle Row: Tenant/Unit Context with added vertical margins (my-3) --}}
                        <p class="text-xs font-bold uppercase tracking-wide my-3 {{ $isActive ? 'text-white' : 'text-[#070642]' }}">
                            {{ $req->tenant_name ?? 'Unknown Tenant' }}
                            <span class="font-normal mx-1 {{ $isActive ? 'text-blue-200' : 'text-gray-300' }}">|</span>
                            Unit {{ $req->unit_number }}
                        </p>

                        {{-- Bottom Row: Category and Date (Bottom Right) --}}
                        <div class="flex justify-between items-end">
                            <p class="text-sm font-medium {{ $isActive ? 'text-white' : 'text-gray-600' }}">
                                {{ $req->category ?? 'General Maintenance' }}
                            </p>
                            <p class="text-[10px] {{ $isActive ? 'text-blue-100' : 'text-gray-400' }}">
                                {{ \Carbon\Carbon::parse($req->created_at)->format('M d, Y') }}
                            </p>
                        </div>

                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center h-full text-gray-400 py-16">
                        <div class="bg-[#F4F7FF] p-6 rounded-full mb-4">
                            <svg class="h-10 w-10 text-[#2B66F5] opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <p class="font-semibold text-gray-500 text-sm">No requests found</p>
                        <p class="text-xs text-gray-400 mt-1">There are currently no tickets in this category.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- RIGHT PANEL: DETAIL (70% width) --}}
        <div class="w-full lg:w-[70%] h-[750px] bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            <livewire:layouts.maintenance.manager-maintenance-detail />
        </div>

    </div>
</div>