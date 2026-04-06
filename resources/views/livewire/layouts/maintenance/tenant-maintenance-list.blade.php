
<div class="flex flex-col w-full pb-6">

    {{-- TABS & ACTIONS ROW --}}
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 flex-shrink-0 gap-4">

        {{-- Tabs (reusable sort‑tab component) --}}
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

        />

        {{-- Sort dropdown + Add Request --}}
        <div class="flex items-center gap-3">
            <x-ui.sort-dropdown model="sortOrder" :current="$sortOrder" />

            <x-ui.button-add
                text="Add Maintenance Request"
                tooltip="Submit a new repair or maintenance ticket"
                x-on:click="$dispatch('open-maintenance-modal')"
            />
        </div>
    </div>

    {{-- MAIN CONTENT GRID --}}
    <div class="flex flex-col lg:flex-row gap-6 w-full">

        {{-- LEFT PANEL: LIST --}}
        <div class="w-full lg:w-[30%] flex-shrink-0 h-[750px] bg-white rounded-3xl shadow-sm border border-gray-100 flex flex-col overflow-hidden p-2">
            <div class="p-4 pb-3 border-b border-gray-50 flex-shrink-0">
                <h3 class="text-xl font-bold text-[#070642] mb-3">Maintenance Requests</h3>

                {{-- Search Bar --}}
                <div class="relative">
                    <input
                        type="text"
                        placeholder="Search by ticket or category..."
                        wire:model.live="search"
                        class="w-full bg-[#F4F6FB] border border-slate-200 rounded-xl py-2.5 pl-4 pr-10 text-xs focus:outline-none focus:ring-2 focus:ring-blue-200 placeholder-slate-400 text-slate-700 transition"
                    >
                    <svg class="w-4 h-4 text-slate-400 absolute right-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-4 space-y-2.5" style="scrollbar-width: thin; scrollbar-color: #e2e8f0 transparent;">
                @forelse($requests as $req)
                    @php
                        $isActive = $activeRequestId === $req->request_id;
                        $statusStyles = match($req->status) {
                            'Completed', 'Resolved'  => 'bg-green-100 text-green-700',
                            'Pending'                => 'bg-orange-100 text-orange-700',
                            'In Progress', 'Ongoing' => 'bg-yellow-100 text-yellow-800',
                            default                  => 'bg-gray-100 text-gray-700'
                        };
                        $ticketId = $req->ticket_number ?? 'TKT-' . str_pad($req->request_id, 4, '0', STR_PAD_LEFT);
                    @endphp

                    <div wire:click="selectRequest({{ $req->request_id }})"
                         class="cursor-pointer p-4 rounded-2xl border-2 transition-all duration-200
                            {{ $isActive ? 'border-[#2B66F5] bg-[#EEF3FF] shadow-md' : 'border-gray-100 bg-white hover:border-blue-200 hover:shadow-sm' }}">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h3 class="font-bold text-[#2B66F5] text-sm">{{ $ticketId }}</h3>
                                <p class="text-[11px] text-gray-400 mt-0.5">{{ \Carbon\Carbon::parse($req->created_at)->format('M d, Y') }}</p>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $statusStyles }}">{{ $req->status }}</span>
                        </div>
                        <p class="text-sm text-gray-600 mt-2 font-medium">{{ $req->category ?? 'General Maintenance' }}</p>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center h-full text-gray-400 py-16">
                        @if(!empty($search) || $activeTab !== 'all')
                            {{-- Filtered but no results --}}
                            <div class="bg-gray-50 p-6 rounded-full mb-4">
                                <svg class="h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <p class="font-semibold text-gray-500 text-sm">No matching requests</p>
                            <p class="text-xs text-gray-400 mt-1">Try adjusting your search or filter.</p>
                        @else
                            {{-- Truly empty — first-time user --}}
                            <div class="bg-[#F4F7FF] p-6 rounded-full mb-4">
                                <svg class="h-10 w-10 text-[#2B66F5] opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <p class="font-semibold text-gray-500 text-sm">No maintenance requests yet</p>
                            <p class="text-xs text-gray-400 mt-1 text-center px-4">Have an issue in your unit? Click "Add Maintenance Request" to submit a ticket.</p>
                        @endif
                    </div>
                @endforelse
            </div>
        </div>

        {{--
            RIGHT PANEL: DETAIL
            Removed p-2 so the detail component (blue header especially) fills edge-to-edge.
            overflow-hidden clips the detail's content to the rounded-3xl corners.
        --}}
        <div class="w-full lg:w-[70%] h-[750px] bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            <livewire:layouts.maintenance.tenant-maintenance-detail :initialRequestId="$activeRequestId" />
        </div>

    </div>
</div>
