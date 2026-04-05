<div class="mt-6 font-sans text-[#070642]">

    {{-- TOOLBAR --}}
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 border-b border-gray-200 pb-1">
        <div class="flex space-x-8 overflow-x-auto w-full md:w-auto">
            @foreach(['All', 'Pending', 'On Hold', 'Completed'] as $tab)
                <button
                    wire:click="setTab('{{ $tab }}')"
                    class="pb-3 text-lg font-bold whitespace-nowrap transition-colors relative
                    {{ $activeTab === $tab ? 'text-[#070642]' : 'text-gray-400 hover:text-gray-600' }}"
                >
                    {{ $tab }} <span class="ml-1 text-sm text-gray-500">{{ $counts[$tab] ?? 0 }}</span>
                    @if($activeTab === $tab)
                        <div class="absolute bottom-[-1px] left-0 w-full h-[3px] bg-[#070642]"></div>
                    @endif
                </button>
            @endforeach
        </div>
        <x-ui.button-add
            text="Add Maintenance Request"
            tooltip="Submit a new repair or maintenance ticket"
            class="mt-4 md:mt-0"
        />
    </div>

    {{-- CONTENT GRID --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        {{-- LEFT LIST (1/3 Width) --}}
        <div class="lg:col-span-4 flex flex-col gap-4">
            <h3 class="text-xl font-bold text-[#070642] mb-2">Maintenance Request</h3>
            <div class="flex flex-col gap-3 max-h-[800px] overflow-y-auto custom-scrollbar pr-2">
                @foreach($requests as $req)
                    @php $isActive = $selectedRequestId == $req->request_id; @endphp
                    <div
                        wire:click="selectRequest({{ $req->request_id }})"
                        class="cursor-pointer p-5 rounded-2xl border transition-all duration-200 shadow-sm
                        {{ $isActive
                            ? 'bg-[#2B66F5] text-white border-[#2B66F5] shadow-md scale-[1.02]'
                            : 'bg-white text-[#070642] border-gray-100 hover:border-blue-200 hover:shadow-md'
                        }}"
                    >
                        <div class="flex items-center gap-1 mb-1 opacity-90">
                            <span class="text-xs font-medium uppercase tracking-wider">{{ $req->tenant_name }}</span>
                        </div>
                        <h4 class="text-2xl font-bold">{{ $req->unit_name ?? 'Unit 101' }}</h4>
                    </div>
                @endforeach
            </div>
            <div class="mt-2">{{ $requests->links('livewire.layouts.components.paginate-blue') }}</div>
        </div>

        {{-- RIGHT DETAILS (2/3 Width) --}}
        <div class="lg:col-span-8">
            @if($selectedDetail)
                <div class="bg-white rounded-[2rem] shadow-xl overflow-hidden border border-gray-100">
                    <div class="bg-[#2B66F5] p-8 text-white flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div>
                            <p class="text-sm opacity-90 mb-1">{{ $selectedDetail->tenant_name }}</p>
                            <h2 class="text-4xl font-bold mb-1">{{ $selectedDetail->unit_name ?? 'Unit 101' }}</h2>
                            <p class="text-sm opacity-80">{{ $selectedDetail->building_name ?? 'Building Name' }}</p>
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            <div class="text-right">
                                <span class="text-xs opacity-75 block">Priority Level</span>
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full {{ str_contains($selectedDetail->urgency, '1') || str_contains($selectedDetail->urgency, '2') ? 'bg-red-400' : 'bg-green-400' }}"></span>
                                    <span class="font-bold text-lg">{{ filter_var($selectedDetail->urgency, FILTER_SANITIZE_NUMBER_INT) ?: '1' }}</span>
                                </div>
                            </div>
                            <div class="mt-2 text-right">
                                <span class="text-xs opacity-75 block">Ticket Number</span>
                                <span class="font-bold font-mono">{{ $selectedDetail->ticket_number }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="p-8">
                        <div class="flex items-center gap-2 mb-6 text-[#070642]">
                            <svg class="w-6 h-6 text-[#2B66F5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            <h3 class="text-xl font-bold">Maintenance Details</h3>
                        </div>
                        <div class="bg-[#F8F9FD] rounded-2xl p-6 mb-8">
                            <div class="mb-4">
                                <span class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Reported Date</span>
                                <span class="text-[#070642] font-semibold text-lg">{{ \Carbon\Carbon::parse($selectedDetail->log_date)->format('F d, Y') }}</span>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Issue Description</span>
                                <p class="text-gray-700 leading-relaxed">{{ $selectedDetail->problem }}</p>
                            </div>
                        </div>
                        <div class="mb-8">
                            <span class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">Images</span>
                            <div class="flex gap-4">
                                @foreach([1,2,3] as $img)
                                    <div class="w-24 h-24 bg-gray-100 rounded-xl flex items-center justify-center border-2 border-dashed border-gray-300">
                                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="flex gap-4 pt-4 border-t border-gray-100">
                            <flux:tooltip :content="'Permanently remove this maintenance request'" position="bottom">
                                <button class="flex-1 bg-[#2B66F5] hover:bg-blue-600 text-white font-bold py-3 px-6 rounded-xl transition-colors shadow-lg shadow-blue-200">Delete</button>
                            </flux:tooltip>
                            <flux:tooltip :content="'Mark this request as completed and resolved'" position="bottom">
                                <button class="flex-1 bg-[#070642] hover:bg-blue-900 text-white font-bold py-3 px-6 rounded-xl transition-colors shadow-lg">Resolved</button>
                            </flux:tooltip>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-[2rem] h-[500px] flex flex-col items-center justify-center text-gray-400 border border-dashed border-gray-200">
                    <p class="text-lg font-medium">Select a request to view details</p>
                </div>
            @endif
        </div>
    </div>
</div>
