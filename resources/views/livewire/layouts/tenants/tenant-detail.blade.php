<div class="bg-white rounded-3xl shadow-sm border border-gray-100 flex flex-col h-full overflow-hidden" style="font-family: 'Open Sans', sans-serif;">
    @if($currentTenant)
        <div class="flex flex-col h-full">

            {{-- 1. Fixed Header Card (retained) --}}
            <div class="flex-shrink-0 rounded-t-3xl z-10 overflow-hidden" style="background: linear-gradient(135deg, #070589 0%, #0a1ea8 40%, #2360E8 100%);">
                <div class="relative p-6">
                    {{-- Top row: label + Edit button --}}
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: rgba(255,255,255,0.12);">
                                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-semibold uppercase tracking-widest" style="color: rgba(191,219,254,0.9);">Tenant Profile</span>
                        </div>

                        @if($viewingTab === 'current')
                            <button
                                wire:click="editTenant"
                                class="flex items-center gap-1.5 bg-white text-[#2360E8] rounded-lg px-3 py-1.5 text-xs font-semibold hover:bg-blue-50 transition-colors border border-white"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit
                            </button>
                        @endif
                    </div>

                    {{-- Name + Unit --}}
                    <div class="flex items-center gap-3 mb-3">
                        <h3 class="text-white font-bold text-2xl leading-tight">
                            {{ $currentTenant['personal_info']['first_name'] }} {{ $currentTenant['personal_info']['last_name'] }}
                        </h3>
                        <div class="rounded-lg px-3 py-1 flex items-center gap-1.5" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.1); backdrop-filter: blur(8px);">
                            <span class="text-sm font-medium" style="color: rgba(191,219,254,0.8);">Unit</span>
                            <span class="text-white text-sm font-bold">{{ $currentTenant['personal_info']['unit'] }}</span>
                        </div>
                    </div>

                    {{-- Info chips --}}
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="flex items-center gap-1.5 rounded-lg px-3 py-1.5" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.06);">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" style="color: #93c5fd;" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-xs truncate max-w-[220px]" style="color: rgba(255,255,255,0.85);">{{ $currentTenant['personal_info']['address'] }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 rounded-lg px-3 py-1.5" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.06);">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" style="color: #93c5fd;" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                            </svg>
                            <span class="text-xs" style="color: rgba(255,255,255,0.85);">{{ $currentTenant['personal_info']['property'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Scrollable Content Area --}}
            <div class="flex-1 overflow-y-auto custom-scrollbar p-5 space-y-5" style="background: linear-gradient(180deg, #EEF2FF 0%, #F8FAFC 100%);">

                {{-- Contact Details --}}
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-8 h-8 rounded-xl bg-[#EEF2FF] flex items-center justify-center">
                            <svg class="w-4 h-4 text-[#2360E8]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h5 class="font-bold text-sm text-[#070589] uppercase tracking-wide">Contact Details</h5>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-[#F8FAFF] rounded-xl p-3.5 border border-blue-50">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1">Phone</p>
                            <p class="text-sm font-bold text-gray-800 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-[#2360E8]/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                                {{ $currentTenant['contact_info']['contact_number'] }}
                            </p>
                        </div>
                        <div class="bg-[#F8FAFF] rounded-xl p-3.5 border border-blue-50">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1">Email</p>
                            <p class="text-sm font-bold text-gray-800 truncate flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-[#2360E8]/40 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                                {{ $currentTenant['contact_info']['email'] }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Rent Details --}}
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-8 h-8 rounded-xl bg-[#EEF2FF] flex items-center justify-center">
                            <svg class="w-4 h-4 text-[#2360E8]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205l3 1m1.5.5l-1.5-.5M6.75 7.364V3h-3v18m3-13.636l10.5-3.819"/>
                            </svg>
                        </div>
                        <h5 class="font-bold text-sm text-[#070589] uppercase tracking-wide">Rent Details</h5>
                    </div>

                    {{-- Top row: Bed + Dorm --}}
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div class="bg-[#F8FAFF] rounded-xl p-3.5 border border-blue-50">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1">Bed Number</p>
                            <p class="text-sm font-bold text-gray-800">{{ $currentTenant['rent_details']['bed_number'] }}</p>
                        </div>
                        <div class="bg-[#F8FAFF] rounded-xl p-3.5 border border-blue-50">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1">Dorm Type</p>
                            <p class="text-sm font-bold text-gray-800">{{ $currentTenant['rent_details']['dorm_type'] }}</p>
                        </div>
                    </div>

                    {{-- Date range --}}
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div class="bg-[#EEF2FF] rounded-xl p-3.5">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1">Start Date</p>
                            <p class="text-sm font-bold text-[#070589] flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-[#2360E8]/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                                {{ \Carbon\Carbon::parse($currentTenant['rent_details']['lease_start_date'])->format('M d, Y') }}
                            </p>
                        </div>
                        <div class="bg-[#EEF2FF] rounded-xl p-3.5">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1">End Date</p>
                            <p class="text-sm font-bold text-[#070589] flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-[#2360E8]/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                                {{ \Carbon\Carbon::parse($currentTenant['rent_details']['lease_end_date'])->format('M d, Y') }}
                            </p>
                        </div>
                    </div>

                    {{-- Bottom row: Term, Shift, Auto Renew --}}
                    <div class="grid grid-cols-3 gap-3">
                        <div class="bg-[#F8FAFF] rounded-xl p-3.5 border border-blue-50 text-center">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1">Term</p>
                            <p class="text-lg font-bold text-[#070589]">{{ $currentTenant['rent_details']['lease_term'] }}<span class="text-xs font-medium text-gray-400 ml-0.5">mos</span></p>
                        </div>
                        <div class="bg-[#F8FAFF] rounded-xl p-3.5 border border-blue-50 text-center">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1">Shift</p>
                            <p class="text-sm font-bold text-gray-800">{{ $currentTenant['rent_details']['shift'] }}</p>
                        </div>
                        <div class="bg-[#F8FAFF] rounded-xl p-3.5 border border-blue-50 text-center">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1">Auto Renew</p>
                            @if($currentTenant['rent_details']['auto_renew'])
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 bg-emerald-50 rounded-full px-2.5 py-0.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Yes
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-gray-500 bg-gray-100 rounded-full px-2.5 py-0.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> No
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Move In Details --}}
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-8 h-8 rounded-xl bg-[#EEF2FF] flex items-center justify-center">
                            <svg class="w-4 h-4 text-[#2360E8]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h5 class="font-bold text-sm text-[#070589] uppercase tracking-wide">Move In Details</h5>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div class="bg-[#F8FAFF] rounded-xl p-3.5 border border-blue-50">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1">Move-in Date</p>
                            <p class="text-sm font-bold text-gray-800">{{ \Carbon\Carbon::parse($currentTenant['move_in_details']['move_in_date'])->format('M d, Y') }}</p>
                        </div>
                        <div class="bg-[#F8FAFF] rounded-xl p-3.5 border border-blue-50">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1">Monthly Rate</p>
                            <p class="text-sm font-bold text-[#070589]">&#8369; {{ number_format($currentTenant['move_in_details']['monthly_rate'], 2) }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-[#F8FAFF] rounded-xl p-3.5 border border-blue-50">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1">Security Deposit</p>
                            <p class="text-sm font-bold text-[#070589]">&#8369; {{ number_format($currentTenant['move_in_details']['security_deposit'], 2) }}</p>
                        </div>
                        <div class="bg-[#F8FAFF] rounded-xl p-3.5 border border-blue-50">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1">Payment Status</p>
                            @php
                                $status = $currentTenant['move_in_details']['payment_status'];
                                $statusStyles = match($status) {
                                    'Paid' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
                                    'Unpaid' => 'text-amber-700 bg-amber-50 border-amber-200',
                                    'Overdue' => 'text-red-700 bg-red-50 border-red-200',
                                    default => 'text-gray-600 bg-gray-50 border-gray-200',
                                };
                                $dotStyles = match($status) {
                                    'Paid' => 'bg-emerald-500',
                                    'Unpaid' => 'bg-amber-500',
                                    'Overdue' => 'bg-red-500',
                                    default => 'bg-gray-400',
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold {{ $statusStyles }} rounded-full px-2.5 py-0.5 border mt-0.5">
                                <span class="w-1.5 h-1.5 rounded-full {{ $dotStyles }}"></span>
                                {{ $status }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                @if($viewingTab === 'current')
                    <div class="grid grid-cols-2 gap-3 pt-2 pb-1">
                        <button
                            type="button"
                            wire:click="transferTenant"
                            class="group py-3 px-5 rounded-xl font-bold text-sm text-white transition-all duration-200 hover:shadow-lg hover:shadow-blue-500/25 hover:-translate-y-0.5 active:translate-y-0"
                            style="background: linear-gradient(135deg, #2360E8 0%, #1080FC 100%);"
                        >
                            <div class="flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/>
                                </svg>
                                Transfer
                            </div>
                        </button>
                        <button
                            type="button"
                            wire:click="moveOutTenant"
                            class="group py-3 px-5 rounded-xl font-bold text-sm text-white transition-all duration-200 hover:shadow-lg hover:shadow-indigo-900/25 hover:-translate-y-0.5 active:translate-y-0"
                            style="background: linear-gradient(135deg, #070589 0%, #0a1ea8 100%);"
                        >
                            <div class="flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
                                </svg>
                                Move Out
                            </div>
                        </button>
                    </div>
                @endif

            </div>
        </div>
    @else
        {{-- Empty State --}}
        <div class="flex items-center justify-center h-full" style="background: linear-gradient(180deg, #EEF2FF 0%, #F8FAFC 100%);">
            <div class="text-center max-w-md p-6">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl mb-6" style="background: linear-gradient(135deg, #070589 0%, #2360E8 100%);">
                    <svg class="w-10 h-10 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-[#070589] mb-2">No Tenant Selected</h3>
                <p class="text-gray-500 text-sm mb-6">
                    Select a tenant from the sidebar to view their details, lease information, and manage their tenancy.
                </p>
                <div class="flex items-center justify-center gap-2 text-[#2360E8]">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    <span class="font-semibold text-sm">Select a tenant from the left</span>
                </div>
            </div>
        </div>
    @endif

    <x-ui.modal-confirm
        name="move-out-confirmation"
        title="Move Out Tenant?"
        description="Are you sure you want to move out this tenant? Their lease will be marked as Expired and their bed will be freed."
        confirmText="Yes, Move Out"
        cancelText="Cancel"
        confirmAction="confirmMoveOut"
    />
</div>

@push('styles')
<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #CBD5E1;
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #2360E8;
    }
</style>
@endpush
