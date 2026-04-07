<div class="bg-white rounded-3xl shadow-sm border border-gray-100 flex flex-col h-full overflow-hidden" style="font-family: 'Open Sans', sans-serif;">
    @if($currentTenant)
        <div class="flex flex-col h-full">

            {{-- 1. Fixed Header Card --}}
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

                            <div class="flex items-center gap-2">
                                @if($viewingTab === 'current')
                                <flux:tooltip :content="'Update this tenant\'s profile and lease information'" position="bottom">
                                    <button
                                        wire:click="editTenant"
                                        class="flex items-center gap-1.5 bg-white text-[#2360E8] rounded-lg px-3 py-1.5 text-xs font-semibold hover:bg-blue-50 transition-colors border border-white"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </button>
                                </flux:tooltip>
                                @endif
                            </div>
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
            <div
                class="flex-1 overflow-y-auto custom-scrollbar p-5 space-y-5"
                style="background: linear-gradient(180deg, #EEF2FF 0%, #F8FAFC 100%);"
                x-data
                x-on:scroll-to-error.window="
                    $nextTick(() => {
                        const firstError = $el.querySelector('.text-red-500, .text-xs.text-red-500, [class*=text-red]');
                        if (firstError) {
                            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    })
                "
            >

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
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1">Phone</p>
                            <p class="text-sm font-bold text-gray-800">{{ $currentTenant['contact_info']['contact_number'] }}</p>
                        </div>
                        <div class="bg-[#F8FAFF] rounded-xl p-3.5 border border-blue-50">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1">Email</p>
                            <p class="text-sm font-bold text-gray-800 truncate">{{ $currentTenant['contact_info']['email'] }}</p>
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

                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div class="bg-[#F8FAFF] rounded-xl p-3.5 border border-blue-50">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1">Bed Number</p>
                            <p class="text-sm font-bold text-gray-800">{{ $currentTenant['rent_details']['bed_number'] }}</p>
                        </div>
                        <div class="bg-[#F8FAFF] rounded-xl p-3.5 border border-blue-50">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1">Dorm Type</p>
                            <p class="text-sm font-bold text-gray-800">{{ $currentTenant['rent_details']['dorm_type'] }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div class="bg-[#EEF2FF] rounded-xl p-3.5">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1">Move-In Date</p>
                            <p class="text-sm font-bold text-[#070589] flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-[#2360E8]/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                                {{ \Carbon\Carbon::parse($currentTenant['rent_details']['lease_start_date'])->format('M d, Y') }}
                            </p>
                        </div>
                        <div class="bg-[#EEF2FF] rounded-xl p-3.5">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1">End Date</p>
                            <p class="text-sm font-bold text-[#070589] flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-[#2360E8]/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                                {{ \Carbon\Carbon::parse($currentTenant['rent_details']['lease_end_date'])->format('M d, Y') }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div class="bg-[#F8FAFF] rounded-xl p-3.5 border border-blue-50 text-center">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1">Term</p>
                            <p class="text-lg font-bold text-[#070589]">{{ $currentTenant['rent_details']['lease_term'] }}<span class="text-xs font-medium text-gray-400 ml-0.5">mos</span></p>
                        </div>
                        <div class="bg-[#F8FAFF] rounded-xl p-3.5 border border-blue-50 text-center">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1">Shift</p>
                            <p class="text-sm font-bold text-gray-800">{{ $currentTenant['rent_details']['shift'] }}</p>
                        </div>
                        <div class="bg-[#F8FAFF] rounded-xl p-3.5 border border-blue-50 text-center">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1">Auto Renew</p>
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
                        <h5 class="font-bold text-sm text-[#070589] uppercase tracking-wide">Payment Details</h5>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div class="bg-[#F8FAFF] rounded-xl p-3.5 border border-blue-50">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1">Monthly Rate</p>
                            <p class="text-sm font-bold text-[#070589]">&#8369; {{ number_format($currentTenant['move_in_details']['monthly_rate'], 2) }}</p>
                        </div>
                        <div class="bg-[#F8FAFF] rounded-xl p-3.5 border border-blue-50">
                            @php
                                $dueDay = $currentTenant['move_in_details']['monthly_due_date'];
                                $dueSuffix = match((int) $dueDay) { 1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th' };
                            @endphp
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1">Due Date</p>
                            <p class="text-sm font-bold text-gray-800">{{ $dueDay ? $dueDay . $dueSuffix . ' of the month' : '—' }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-[#F8FAFF] rounded-xl p-3.5 border border-blue-50">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1">Security Deposit</p>
                            <p class="text-sm font-bold text-[#070589]">&#8369; {{ number_format($currentTenant['move_in_details']['security_deposit'], 2) }}</p>
                        </div>
                        <div class="bg-[#F8FAFF] rounded-xl p-3.5 border border-blue-50">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1">Payment Status</p>
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

                {{-- Violation Records --}}
                @if($viewingTab === 'current')
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-[#EEF2FF] flex items-center justify-center">
                                <svg class="w-4 h-4 text-[#2360E8]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                            </div>
                            <h5 class="font-bold text-sm text-[#070589] uppercase tracking-wide">Violation Records</h5>
                            @if($violationCounts['total'] > 0)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" style="background:#eef2ff;color:#070589">{{ $violationCounts['total'] }}</span>
                            @endif
                        </div>
                        <button
                            type="button"
                            x-on:click="$dispatch('open-add-violation-modal', { leaseId: {{ $currentLeaseId ?? 'null' }} })"
                            class="flex items-center gap-1.5 text-xs font-semibold text-white rounded-lg px-3 py-1.5 transition" style="background:#070589" onmouseover="this.style.background='#000060'" onmouseout="this.style.background='#070589'"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Record
                        </button>
                    </div>

                    @if(count($violations) > 0)
                        {{-- Summary Badges --}}
                        <div class="flex gap-2 mb-3">
                            @if($violationCounts['issued'] > 0)
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-red-50 text-red-600 border border-red-100">{{ $violationCounts['issued'] }} Issued</span>
                            @endif
                            @if($violationCounts['acknowledged'] > 0)
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-yellow-50 text-yellow-700 border border-yellow-100">{{ $violationCounts['acknowledged'] }} Acknowledged</span>
                            @endif
                            @if($violationCounts['resolved'] > 0)
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-green-50 text-green-700 border border-green-100">{{ $violationCounts['resolved'] }} Resolved</span>
                            @endif
                        </div>

                        {{-- Violation List --}}
                        <div class="space-y-2">
                            @foreach($violations as $vio)
                                @php
                                    $vStatusStyles = match($vio['status']) {
                                        'Resolved'     => 'bg-green-100 text-green-700',
                                        'Issued'       => 'bg-red-100 text-red-700',
                                        'Acknowledged' => 'bg-yellow-100 text-yellow-800',
                                        default        => 'bg-gray-100 text-gray-700'
                                    };
                                    $vSeverityStyles = match($vio['severity']) {
                                        'serious' => 'bg-red-50 text-red-600',
                                        'major'   => 'bg-orange-50 text-orange-600',
                                        'minor'   => 'bg-blue-50 text-blue-600',
                                        default   => 'bg-gray-50 text-gray-600'
                                    };
                                    $vOffenseLabel = match($vio['offense_number']) {
                                        1 => '1st', 2 => '2nd', 3 => '3rd', default => $vio['offense_number'] . 'th'
                                    };
                                    $vPenaltyLabel = match($vio['penalty_type']) {
                                        'written_warning' => 'Warning',
                                        'fine' => 'Fine — PHP ' . number_format($vio['fine_amount'] ?? 0, 2),
                                        'lease_termination' => 'Termination',
                                        default => ucfirst($vio['penalty_type']),
                                    };
                                @endphp
                                <div class="bg-[#F8FAFF] rounded-xl p-3.5 border border-blue-50">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-[#2360E8]">{{ $vio['violation_number'] }}</span>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $vStatusStyles }}">{{ $vio['status'] }}</span>
                                        </div>
                                        <span class="text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($vio['violation_date'])->format('M d, Y') }}</span>
                                    </div>
                                    <p class="text-xs font-semibold text-[#070642] mb-1">{{ $vio['category'] }}</p>
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold {{ $vSeverityStyles }}">{{ ucfirst($vio['severity']) }}</span>
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-gray-100 text-gray-600">{{ $vOffenseLabel }} Offense</span>
                                        <span class="text-[10px] text-gray-500">{{ $vPenaltyLabel }}</span>
                                    </div>
                                    @if(!empty($vio['description']))
                                        <p class="text-[11px] text-gray-500 mt-1.5 line-clamp-2">{{ $vio['description'] }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-2" style="background:#eef2ff">
                                <svg class="w-5 h-5" style="color:#070589" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <p class="text-xs font-semibold text-gray-500">No violations on record</p>
                            <p class="text-[10px] text-gray-400 mt-0.5">Tenant is in good standing</p>
                        </div>
                    @endif
                </div>
                @endif

                {{-- Action Buttons --}}
                @if($viewingTab === 'current')
                    <div class="grid grid-cols-2 gap-3 pt-2">
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
                                {{ $moveOutInitiated ? 'Finalize Move Out' : 'Move Out' }}
                            </div>
                        </button>
                    </div>
                @endif

                {{-- Move-Out Progress Stepper --}}
                @if($moveOutInitiated)
                    @php
                        $moveOutSteps = [
                            ['num' => 1, 'title' => 'Initiated', 'done' => true],
                            ['num' => 2, 'title' => 'Inspection', 'done' => $moveOutInspectionSaved],
                            ['num' => 3, 'title' => 'Items Returned', 'done' => $moveOutInspectionSaved],
                            ['num' => 4, 'title' => 'Contract Signed', 'done' => $moveOutContractAgreed],
                            ['num' => 5, 'title' => 'Finalized', 'done' => (bool) ($currentTenant['move_out_details']['move_out_date'] ?? false)],
                        ];
                        $currentMoveOutStep = count($moveOutSteps);
                        foreach ($moveOutSteps as $s) {
                            if (!$s['done']) { $currentMoveOutStep = $s['num']; break; }
                        }
                    @endphp
                    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-6 h-6 rounded-lg bg-indigo-50 flex items-center justify-center">
                                <svg class="w-3.5 h-3.5 text-[#070589]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <h5 class="text-xs font-bold text-[#070589] uppercase tracking-wide">Move-Out Progress</h5>
                        </div>
                        <div class="flex items-center justify-between">
                            @foreach($moveOutSteps as $i => $step)
                                <div class="flex items-center {{ $i < count($moveOutSteps) - 1 ? 'flex-1' : '' }}">
                                    <div class="flex flex-col items-center">
                                        <div
                                            class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold border-2 transition-all duration-200
                                            {{ $currentMoveOutStep === $step['num']
                                                ? 'bg-[#070589] text-white border-[#070589] shadow-lg shadow-blue-500/20'
                                                : ($step['done']
                                                    ? 'bg-[#070589]/10 text-[#070589] border-[#070589]/30'
                                                    : 'bg-transparent text-gray-300 border-gray-200') }}"
                                        >
                                            @if($step['done'] && $currentMoveOutStep !== $step['num'])
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            @else
                                                {{ $step['num'] }}
                                            @endif
                                        </div>
                                        <span
                                            class="text-[11px] font-semibold mt-1.5 tracking-wide transition-all duration-200
                                            {{ $currentMoveOutStep === $step['num']
                                                ? 'text-[#070589]'
                                                : ($step['done']
                                                    ? 'text-[#070589]/60'
                                                    : 'text-gray-300') }}"
                                        >{{ $step['title'] }}</span>
                                    </div>
                                    @if($i < count($moveOutSteps) - 1)
                                        <div class="flex-1 mx-2 mt-[-14px]">
                                            <div class="h-0.5 rounded-full bg-gray-200 relative overflow-hidden">
                                                <div
                                                    class="absolute inset-y-0 left-0 bg-[#070589]/40 rounded-full transition-all duration-300 ease-out"
                                                    style="width: {{ $step['done'] ? '100%' : '0%' }}"
                                                ></div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Deposit Refund Tracking (shown after move-out is finalized) --}}
                @if(($currentTenant['move_out_details']['move_out_date'] ?? null) && ($currentTenant['deposit_refund']['amount'] ?? 0) > 0)
                    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-6 h-6 rounded-lg bg-amber-50 flex items-center justify-center">
                                <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>
                            </div>
                            <h5 class="text-xs font-bold text-amber-700 uppercase tracking-wide">Deposit Refund</h5>
                        </div>

                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div class="bg-gray-50 rounded-lg p-2.5">
                                <p class="text-[11px] text-gray-500">Refund Amount</p>
                                <p class="text-sm font-bold text-gray-800">PHP {{ number_format((float) ($currentTenant['deposit_refund']['amount'] ?? 0), 2) }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-2.5">
                                <p class="text-[11px] text-gray-500">Deadline</p>
                                @php
                                    $lease = \App\Models\Lease::find($currentLeaseId);
                                    $refundDeadline = $lease?->deposit_refund_deadline;
                                    $refundCompleted = $lease?->deposit_refund_completed_at;
                                    $refundRef = $lease?->deposit_refund_reference;
                                @endphp
                                <p class="text-sm font-bold {{ $refundDeadline && $refundDeadline->isPast() && !$refundCompleted ? 'text-red-600' : 'text-gray-800' }}">
                                    {{ $refundDeadline ? $refundDeadline->format('M d, Y') : '—' }}
                                </p>
                            </div>
                        </div>

                        @if($refundCompleted)
                            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 text-center">
                                <p class="text-xs font-bold text-emerald-700">Refund Completed</p>
                                <p class="text-[11px] text-emerald-600 mt-0.5">{{ $refundCompleted->format('M d, Y h:i A') }}</p>
                                @if($refundRef)
                                    <p class="text-[11px] text-emerald-600">Ref: {{ $refundRef }}</p>
                                @endif
                            </div>
                        @else
                            <div class="space-y-2">
                                <label class="block text-[11px] font-semibold text-gray-600">Reference Number (optional)</label>
                                <input type="text" wire:model="depositRefundReference" placeholder="e.g. GCash ref, bank transfer no."
                                    class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-400 focus:border-amber-400">
                                <button
                                    wire:click="markRefundCompleted"
                                    wire:loading.attr="disabled"
                                    class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-2.5 rounded-xl text-sm transition-colors"
                                >
                                    <span wire:loading.remove wire:target="markRefundCompleted">Mark Refund as Completed</span>
                                    <span wire:loading wire:target="markRefundCompleted">Processing...</span>
                                </button>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Contract Cards --}}
                <div class="space-y-3 pt-1 pb-2">

                    {{-- Move-In Inspection Expandable Card --}}
                    <x-inspection.expandable-card
                        title="Move-In Inspection"
                        subtitle="Record room condition and items received"
                        :saved="$inspectionSaved"
                        accentColor="emerald"
                        contentRef="moveInContent"
                        iconPath="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 012.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3l1.5 1.5 3-3.75"
                    >

                                @if($inspectionSaved)
                                    {{-- ===== READ-ONLY VIEW ===== --}}

                                    {{-- Room Condition Checklist (read-only) --}}
                                    <div>
                                        <h4 class="text-xs font-bold text-[#070589] uppercase mb-3 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Room Condition Checklist
                                        </h4>
                                        <div class="overflow-x-auto rounded-xl border border-gray-200">
                                            <table class="w-full text-xs">
                                                <thead>
                                                    <tr class="bg-gray-50 border-b border-gray-200">
                                                        <th class="text-left p-2.5 font-semibold text-gray-600 w-2/5">Item</th>
                                                        <th class="text-center p-2.5 font-semibold text-gray-600 w-16">Good</th>
                                                        <th class="text-center p-2.5 font-semibold text-gray-600 w-20">Damaged</th>
                                                        <th class="text-center p-2.5 font-semibold text-gray-600 w-18">Missing</th>
                                                        <th class="text-left p-2.5 font-semibold text-gray-600">Remarks</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($inspectionChecklist as $item)
                                                        <tr class="border-b border-gray-100">
                                                            <td class="p-2.5 text-gray-700 font-medium">{{ $item['item_name'] }}</td>
                                                            <td class="p-2.5 text-center">@if($item['condition'] === 'good')<span class="text-emerald-500 font-bold">&#10003;</span>@endif</td>
                                                            <td class="p-2.5 text-center">@if($item['condition'] === 'damaged')<span class="text-amber-500 font-bold">&#10003;</span>@endif</td>
                                                            <td class="p-2.5 text-center">@if($item['condition'] === 'missing')<span class="text-red-500 font-bold">&#10003;</span>@endif</td>
                                                            <td class="p-2.5 text-gray-500">{{ $item['remarks'] ?: '—' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    {{-- Items Received (read-only) --}}
                                    <div>
                                        <h4 class="text-xs font-bold text-[#070589] uppercase mb-3 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                                            Items Received by Tenant
                                        </h4>
                                        <div class="overflow-x-auto rounded-xl border border-gray-200">
                                            <table class="w-full text-xs">
                                                <thead>
                                                    <tr class="bg-gray-50 border-b border-gray-200">
                                                        <th class="text-left p-2.5 font-semibold text-gray-600 w-2/5">Item</th>
                                                        <th class="text-center p-2.5 font-semibold text-gray-600 w-16">Qty</th>
                                                        <th class="text-left p-2.5 font-semibold text-gray-600">Condition</th>
                                                        <th class="text-center p-2.5 font-semibold text-gray-600 w-24">Confirmed</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($itemsReceived as $item)
                                                        <tr class="border-b border-gray-100">
                                                            <td class="p-2.5 text-gray-700 font-medium">{{ $item['item_name'] }}</td>
                                                            <td class="p-2.5 text-center text-gray-700">{{ $item['quantity'] ?: '—' }}</td>
                                                            <td class="p-2.5 text-gray-500">{{ $item['condition'] ?: '—' }}</td>
                                                            <td class="p-2.5 text-center">
                                                                @if($item['tenant_confirmed'])
                                                                    <span class="text-blue-500 font-bold">&#10003;</span>
                                                                @else
                                                                    <span class="text-gray-300">—</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    {{-- Edit Button --}}
                                    <div class="flex justify-end pt-2">
                                        <button
                                            type="button"
                                            wire:click="$set('inspectionSaved', false)"
                                            class="px-5 py-2 text-xs font-semibold text-[#070589] bg-blue-50 hover:bg-blue-100 rounded-xl transition-colors flex items-center gap-1.5"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                                            Edit Inspection
                                        </button>
                                    </div>

                                @else
                                    {{-- ===== EDIT FORM ===== --}}

                                    {{-- Section 1: Room Condition Checklist --}}
                                    <div>
                                        <h4 class="text-xs font-bold text-[#070589] uppercase mb-3 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Room Condition Checklist
                                        </h4>
                                        <div class="overflow-x-auto rounded-xl border border-gray-200">
                                            <table class="w-full text-xs">
                                                <thead>
                                                    <tr class="bg-gray-50 border-b border-gray-200">
                                                        <th class="text-left p-2.5 font-semibold text-gray-600 w-2/5">Item</th>
                                                        <th class="text-center p-2.5 font-semibold text-gray-600 w-16">Good</th>
                                                        <th class="text-center p-2.5 font-semibold text-gray-600 w-20">Damaged</th>
                                                        <th class="text-center p-2.5 font-semibold text-gray-600 w-18">Missing</th>
                                                        <th class="text-left p-2.5 font-semibold text-gray-600">Remarks</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($inspectionChecklist as $index => $item)
                                                        <tr wire:key="movein-checklist-{{ $index }}" class="border-b border-gray-100 hover:bg-gray-50/50 transition-colors {{ $errors->has("inspectionChecklist.{$index}.condition") ? 'bg-red-50/50' : '' }}">
                                                            <td class="p-2.5 text-gray-700 font-medium">
                                                                {{ $item['item_name'] }}
                                                                @error("inspectionChecklist.{$index}.condition")
                                                                    <p class="text-[11px] text-red-500 font-normal mt-0.5">Required</p>
                                                                @enderror
                                                            </td>
                                                            <td class="p-2.5 text-center">
                                                                <label class="cursor-pointer">
                                                                    <input type="radio" name="inspectionChecklist_{{ $index }}_condition" wire:model.live="inspectionChecklist.{{ $index }}.condition" value="good"
                                                                           class="w-4 h-4 text-emerald-500 border-gray-300 focus:ring-emerald-400">
                                                                </label>
                                                            </td>
                                                            <td class="p-2.5 text-center">
                                                                <label class="cursor-pointer">
                                                                    <input type="radio" name="inspectionChecklist_{{ $index }}_condition" wire:model.live="inspectionChecklist.{{ $index }}.condition" value="damaged"
                                                                           class="w-4 h-4 text-amber-500 border-gray-300 focus:ring-amber-400">
                                                                </label>
                                                            </td>
                                                            <td class="p-2.5 text-center">
                                                                <label class="cursor-pointer">
                                                                    <input type="radio" name="inspectionChecklist_{{ $index }}_condition" wire:model.live="inspectionChecklist.{{ $index }}.condition" value="missing"
                                                                           class="w-4 h-4 text-red-500 border-gray-300 focus:ring-red-400">
                                                                </label>
                                                            </td>
                                                            <td class="p-2.5">
                                                                <input type="text" wire:model.defer="inspectionChecklist.{{ $index }}.remarks"
                                                                       placeholder="Optional notes..."
                                                                       class="w-full text-xs border border-gray-200 rounded-lg px-2.5 py-1.5 focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400 transition-colors placeholder:text-gray-300">
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    {{-- Section 2: Items Received --}}
                                    <div>
                                        <h4 class="text-xs font-bold text-[#070589] uppercase mb-3 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                                            Items Received by Tenant
                                        </h4>
                                        <div class="overflow-visible rounded-xl border border-gray-200">
                                            <table class="w-full text-xs">
                                                <thead>
                                                    <tr class="bg-gray-50 border-b border-gray-200">
                                                        <th class="text-left p-2.5 font-semibold text-gray-600">Item</th>
                                                        <th class="text-center p-2.5 font-semibold text-gray-600 w-24">Quantity</th>
                                                        <th class="text-left p-2.5 font-semibold text-gray-600">Condition</th>
                                                        <th class="text-center p-2.5 font-semibold text-gray-600 w-28">Tenant Confirmed</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($itemsReceived as $index => $item)
                                                        <tr wire:key="movein-item-{{ $index }}" class="border-b border-gray-100 hover:bg-gray-50/50 transition-colors {{ $errors->has("itemsReceived.{$index}.quantity") || $errors->has("itemsReceived.{$index}.condition") ? 'bg-red-50/50' : '' }}">
                                                            <td class="p-2.5 text-gray-700 font-medium">
                                                                {{ $item['item_name'] }}
                                                                @if($errors->has("itemsReceived.{$index}.condition") || $errors->has("itemsReceived.{$index}.quantity"))
                                                                    <p class="text-[11px] text-red-500 font-normal mt-0.5">Required</p>
                                                                @endif
                                                            </td>
                                                            <td class="p-2.5 text-center">
                                                                <input type="number" min="1" step="1"
                                                                       wire:model.live.debounce.300ms="itemsReceived.{{ $index }}.quantity"
                                                                       placeholder="1"
                                                                       onkeydown="if(!/[0-9]|Backspace|Tab|ArrowLeft|ArrowRight|Delete/.test(event.key))event.preventDefault()"
                                                                       oninput="this.value=this.value.replace(/^0+/,'').replace(/[^0-9]/g,'');if(this.value==='')this.value=''"
                                                                       class="w-14 text-xs text-center border rounded-lg px-1.5 py-1.5 focus:ring-1 transition-colors placeholder:text-gray-300 {{ $errors->has("itemsReceived.{$index}.quantity") ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : 'border-gray-200 focus:border-blue-400 focus:ring-blue-400' }}">
                                                            </td>
                                                            <td class="p-2.5">
                                                                <div x-data="{
                                                                    open: false,
                                                                    dropUp: false,
                                                                    toggleDropdown() {
                                                                        if (this.open) { this.open = false; return; }
                                                                        const btn = this.$refs.trigger{{ $index }};
                                                                        const rect = btn.getBoundingClientRect();
                                                                        const scrollParent = btn.closest('.overflow-y-auto') || document.documentElement;
                                                                        const containerBottom = scrollParent === document.documentElement
                                                                            ? window.innerHeight
                                                                            : scrollParent.getBoundingClientRect().bottom;
                                                                        this.dropUp = (containerBottom - rect.bottom) < 220;
                                                                        this.open = true;
                                                                    }
                                                                }" @click.away="open = false" @keydown.escape.stop="open = false" class="relative">
                                                                    <button
                                                                        x-ref="trigger{{ $index }}"
                                                                        @click="toggleDropdown()"
                                                                        type="button"
                                                                        class="w-full flex items-center justify-between gap-1.5 bg-white border rounded-lg px-2.5 py-1.5 text-xs transition-all hover:bg-gray-50 focus:ring-1 outline-none {{ $errors->has("itemsReceived.{$index}.condition") ? 'border-red-400 focus:ring-red-400' : 'border-gray-200 focus:ring-blue-400' }}"
                                                                    >
                                                                        <span class="truncate {{ empty($item['condition']) ? 'text-gray-400' : 'text-gray-700' }}">
                                                                            {{ $item['condition'] ?: 'Select condition...' }}
                                                                        </span>
                                                                        <svg :class="{ 'rotate-180': open }" class="w-3.5 h-3.5 text-gray-400 shrink-0 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                                        </svg>
                                                                    </button>
                                                                    <div
                                                                        x-show="open"
                                                                        x-transition
                                                                        style="display: none;"
                                                                        class="absolute left-0 z-30 w-full bg-white border border-gray-100 rounded-xl shadow-xl overflow-hidden"
                                                                        :class="dropUp ? 'bottom-full mb-1' : 'top-full mt-1'"
                                                                    >
                                                                        @foreach(['Good', 'New', 'Fair', 'Damaged', 'Not Provided'] as $condition)
                                                                            <x-dropdown-item
                                                                                wire:click="setItemCondition({{ $index }}, '{{ $condition }}')"
                                                                                :active="($item['condition'] ?? '') === $condition"
                                                                                @click="open = false"
                                                                            >
                                                                                {{ $condition }}
                                                                            </x-dropdown-item>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td class="p-2.5 text-center">
                                                                @if($item['tenant_confirmed'])
                                                                    <span class="text-blue-500 font-bold" title="Confirmed by tenant">&#10003;</span>
                                                                @else
                                                                    <span class="text-gray-300" title="Pending tenant confirmation">—</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    {{-- Action Buttons --}}
                                    <div class="flex justify-end gap-2 pt-2">
                                        <button
                                            type="button"
                                            @click="close()"
                                            class="px-5 py-2 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors"
                                        >
                                            Cancel
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="saveInspection"
                                            wire:loading.attr="disabled"
                                            class="px-5 py-2 text-xs font-bold text-white bg-[#070589] hover:bg-[#000060] rounded-xl transition-colors flex items-center gap-1.5 disabled:opacity-50"
                                        >
                                            <span wire:loading wire:target="saveInspection">
                                                <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                            </span>
                                            Save Inspection
                                        </button>
                                    </div>
                                @endif
                    </x-inspection.expandable-card>

                    {{-- Move-Out Inspection & Contract (only visible after move-out) --}}
                    @if(($currentTenant['move_out_details']['move_out_initiated_at'] ?? null) || ($currentTenant['move_out_details']['move_out_date'] ?? null))

                    {{-- Move-Out Inspection Expandable Card --}}
                    <x-inspection.expandable-card
                        title="Move-Out Inspection"
                        subtitle="Record room condition and items returned"
                        :saved="$moveOutInspectionSaved"
                        accentColor="red"
                        contentRef="moveOutContent"
                        iconPath="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"
                    >

                                @if($moveOutInspectionSaved)
                                    {{-- ===== READ-ONLY VIEW ===== --}}

                                    {{-- Room Condition Checklist (read-only) --}}
                                    <div>
                                        <h4 class="text-xs font-bold text-[#070589] uppercase mb-3 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Room Condition at Move-Out
                                        </h4>
                                        <div class="overflow-x-auto rounded-xl border border-gray-200">
                                            <table class="w-full text-xs">
                                                <thead>
                                                    <tr class="bg-gray-50 border-b border-gray-200">
                                                        <th class="text-left p-2.5 font-semibold text-gray-600 w-1/4">Item</th>
                                                        <th class="text-center p-2.5 font-semibold text-gray-600 w-14">Good</th>
                                                        <th class="text-center p-2.5 font-semibold text-gray-600 w-14">Damaged</th>
                                                        <th class="text-center p-2.5 font-semibold text-gray-600 w-14">Missing</th>
                                                        <th class="text-left p-2.5 font-semibold text-gray-600">Remarks</th>
                                                        <th class="text-right p-2.5 font-semibold text-gray-600 w-24">Repair Cost</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($moveOutChecklist as $item)
                                                        <tr class="border-b border-gray-100">
                                                            <td class="p-2.5 text-gray-700 font-medium">{{ $item['item_name'] }}</td>
                                                            <td class="p-2.5 text-center">@if($item['condition'] === 'good')<span class="text-emerald-500 font-bold">&#10003;</span>@endif</td>
                                                            <td class="p-2.5 text-center">@if($item['condition'] === 'damaged')<span class="text-amber-500 font-bold">&#10003;</span>@endif</td>
                                                            <td class="p-2.5 text-center">@if($item['condition'] === 'missing')<span class="text-red-500 font-bold">&#10003;</span>@endif</td>
                                                            <td class="p-2.5 text-gray-500">{{ $item['remarks'] ?: '—' }}</td>
                                                            <td class="p-2.5 text-right text-gray-700">{{ !empty($item['repair_cost']) ? '₱ ' . number_format($item['repair_cost'], 2) : '—' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    {{-- Items Returned (read-only) --}}
                                    <div>
                                        <h4 class="text-xs font-bold text-[#070589] uppercase mb-3 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                                            Items Returned by Tenant
                                        </h4>
                                        <div class="overflow-x-auto rounded-xl border border-gray-200">
                                            <table class="w-full text-xs">
                                                <thead>
                                                    <tr class="bg-gray-50 border-b border-gray-200">
                                                        <th class="text-left p-2.5 font-semibold text-gray-600 w-1/4">Item</th>
                                                        <th class="text-center p-2.5 font-semibold text-gray-600 w-14">Qty</th>
                                                        <th class="text-left p-2.5 font-semibold text-gray-600">Condition</th>
                                                        <th class="text-center p-2.5 font-semibold text-gray-600 w-16">Returned</th>
                                                        <th class="text-right p-2.5 font-semibold text-gray-600 w-24">Replacement</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($itemsReturned as $item)
                                                        <tr class="border-b border-gray-100">
                                                            <td class="p-2.5 text-gray-700 font-medium">{{ $item['item_name'] }}</td>
                                                            <td class="p-2.5 text-center text-gray-700">{{ $item['quantity'] ?: '—' }}</td>
                                                            <td class="p-2.5 text-gray-500">{{ $item['condition'] ?: '—' }}</td>
                                                            <td class="p-2.5 text-center">
                                                                @if($item['is_returned'] ?? false)
                                                                    <span class="text-emerald-500 font-bold">&#10003;</span>
                                                                @else
                                                                    <span class="text-red-400 font-bold">&#10007;</span>
                                                                @endif
                                                            </td>
                                                            <td class="p-2.5 text-right text-gray-700">{{ !empty($item['replacement_cost']) ? '₱ ' . number_format($item['replacement_cost'], 2) : '—' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    {{-- Disputed Items (manager resolution) --}}
                                    @php
                                        $disputedItems = collect($moveOutChecklist)->merge(collect($itemsReturned))
                                            ->filter(fn($i) => ($i['dispute_status'] ?? 'none') === 'disputed');
                                        $resolvedItems = collect($moveOutChecklist)->merge(collect($itemsReturned))
                                            ->filter(fn($i) => str_starts_with($i['dispute_status'] ?? '', 'resolved'));
                                    @endphp

                                    @if($disputedItems->isNotEmpty() || $resolvedItems->isNotEmpty())
                                    <div>
                                        <h4 class="text-xs font-bold text-[#070589] uppercase mb-3 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/></svg>
                                            Disputes
                                        </h4>

                                        @foreach($disputedItems as $item)
                                            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 mb-2" x-data="{ resolution: '' }">
                                                <div class="flex items-center justify-between mb-2">
                                                    <div>
                                                        <span class="text-xs font-bold text-amber-700">{{ $item['item_name'] }}</span>
                                                        <span class="ml-2 inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">DISPUTED</span>
                                                    </div>
                                                </div>
                                                <p class="text-xs text-gray-600 mb-2">Tenant's concern: <em>"{{ $item['dispute_remarks'] }}"</em></p>
                                                <div class="flex gap-2 items-end">
                                                    <input type="text" x-model="resolution" placeholder="Resolution remarks..."
                                                           class="flex-1 text-xs border border-gray-200 rounded-lg px-2.5 py-1.5 focus:border-[#070589] focus:ring-1 focus:ring-[#070589]">
                                                    <button @click="if(resolution) $wire.resolveDispute({{ $item['id'] }}, resolution, 'move_out', 'accepted')"
                                                            class="px-3 py-1.5 text-[11px] font-bold text-white bg-emerald-500 hover:bg-emerald-600 rounded-lg">Accept</button>
                                                    <button @click="if(resolution) $wire.resolveDispute({{ $item['id'] }}, resolution, 'move_out', 'rejected')"
                                                            class="px-3 py-1.5 text-[11px] font-bold text-white bg-red-400 hover:bg-red-500 rounded-lg">Reject</button>
                                                </div>
                                            </div>
                                        @endforeach

                                        @foreach($resolvedItems as $item)
                                            <div class="bg-gray-50 border border-gray-200 rounded-xl p-3 mb-2">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span class="text-xs font-bold text-gray-700">{{ $item['item_name'] }}</span>
                                                    @if($item['dispute_status'] === 'resolved_accepted')
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">ACCEPTED</span>
                                                    @elseif($item['dispute_status'] === 'resolved_rejected')
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-600">REJECTED</span>
                                                    @else
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-gray-200 text-gray-600">RESOLVED</span>
                                                    @endif
                                                </div>
                                                <p class="text-[11px] text-gray-500">Resolution: {{ $item['resolution_remarks'] }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                    @endif

                                    {{-- Edit Button --}}
                                    <div class="flex items-center justify-between pt-2">
                                        @if($moveOutContractAgreed)
                                            <p class="text-[11px] text-amber-600 font-medium">Editing will invalidate existing signatures.</p>
                                        @else
                                            <div></div>
                                        @endif
                                        <button
                                            type="button"
                                            wire:click="$set('moveOutInspectionSaved', false)"
                                            class="px-5 py-2 text-xs font-semibold text-[#070589] bg-blue-50 hover:bg-blue-100 rounded-xl transition-colors flex items-center gap-1.5"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                                            Edit Inspection
                                        </button>
                                    </div>

                                @else
                                    {{-- ===== EDIT FORM ===== --}}

                                    {{-- Section 1: Room Condition Checklist --}}
                                    <div>
                                        <h4 class="text-xs font-bold text-[#070589] uppercase mb-3 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Room Condition at Move-Out
                                        </h4>
                                        <div class="overflow-x-auto rounded-xl border border-gray-200">
                                            <table class="w-full text-xs">
                                                <thead>
                                                    <tr class="bg-gray-50 border-b border-gray-200">
                                                        <th class="text-left p-2.5 font-semibold text-gray-600 w-1/4">Item</th>
                                                        <th class="text-center p-2.5 font-semibold text-gray-600 w-14">Good</th>
                                                        <th class="text-center p-2.5 font-semibold text-gray-600 w-14">Damaged</th>
                                                        <th class="text-center p-2.5 font-semibold text-gray-600 w-14">Missing</th>
                                                        <th class="text-left p-2.5 font-semibold text-gray-600">Remarks</th>
                                                        <th class="text-right p-2.5 font-semibold text-gray-600 w-24">Repair Cost</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($moveOutChecklist as $index => $item)
                                                        <tr wire:key="moveout-checklist-{{ $index }}" class="border-b border-gray-100 hover:bg-gray-50/50 transition-colors {{ $errors->has("moveOutChecklist.{$index}.condition") ? 'bg-red-50/50' : '' }}">
                                                            <td class="p-2.5 text-gray-700 font-medium">
                                                                {{ $item['item_name'] }}
                                                                @error("moveOutChecklist.{$index}.condition")
                                                                    <p class="text-[11px] text-red-500 font-normal mt-0.5">Required</p>
                                                                @enderror
                                                            </td>
                                                            <td class="p-2.5 text-center">
                                                                <label class="cursor-pointer">
                                                                    <input type="radio" name="moveOutChecklist_{{ $index }}_condition" wire:model.live="moveOutChecklist.{{ $index }}.condition" value="good"
                                                                           class="w-4 h-4 text-emerald-500 border-gray-300 focus:ring-emerald-400">
                                                                </label>
                                                            </td>
                                                            <td class="p-2.5 text-center">
                                                                <label class="cursor-pointer">
                                                                    <input type="radio" name="moveOutChecklist_{{ $index }}_condition" wire:model.live="moveOutChecklist.{{ $index }}.condition" value="damaged"
                                                                           class="w-4 h-4 text-amber-500 border-gray-300 focus:ring-amber-400">
                                                                </label>
                                                            </td>
                                                            <td class="p-2.5 text-center">
                                                                <label class="cursor-pointer">
                                                                    <input type="radio" name="moveOutChecklist_{{ $index }}_condition" wire:model.live="moveOutChecklist.{{ $index }}.condition" value="missing"
                                                                           class="w-4 h-4 text-red-500 border-gray-300 focus:ring-red-400">
                                                                </label>
                                                            </td>
                                                            <td class="p-2.5">
                                                                <input type="text" wire:model.defer="moveOutChecklist.{{ $index }}.remarks"
                                                                       placeholder="Optional notes..."
                                                                       class="w-full text-xs border border-gray-200 rounded-lg px-2.5 py-1.5 focus:border-red-400 focus:ring-1 focus:ring-red-400 transition-colors placeholder:text-gray-300">
                                                            </td>
                                                            <td class="p-2.5">
                                                                <input type="number" min="0" step="0.01"
                                                                       wire:model.defer="moveOutChecklist.{{ $index }}.repair_cost"
                                                                       placeholder="0.00"
                                                                       class="w-full text-xs text-right border border-gray-200 rounded-lg px-2 py-1.5 focus:border-red-400 focus:ring-1 focus:ring-red-400 transition-colors placeholder:text-gray-300">
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    {{-- Section 2: Items Returned --}}
                                    <div>
                                        <h4 class="text-xs font-bold text-[#070589] uppercase mb-3 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                                            Items Returned by Tenant
                                        </h4>
                                        <div class="overflow-x-auto rounded-xl border border-gray-200">
                                            <table class="w-full text-xs">
                                                <thead>
                                                    <tr class="bg-gray-50 border-b border-gray-200">
                                                        <th class="text-left p-2.5 font-semibold text-gray-600 w-1/5">Item</th>
                                                        <th class="text-center p-2.5 font-semibold text-gray-600 w-14">Qty Issued</th>
                                                        <th class="text-center p-2.5 font-semibold text-gray-600 w-14">Qty Returned</th>
                                                        <th class="text-left p-2.5 font-semibold text-gray-600">Condition</th>
                                                        <th class="text-center p-2.5 font-semibold text-gray-600 w-16">Returned</th>
                                                        <th class="text-right p-2.5 font-semibold text-gray-600 w-24">Replacement</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($itemsReturned as $index => $item)
                                                        <tr wire:key="moveout-item-{{ $index }}" class="border-b border-gray-100 hover:bg-gray-50/50 transition-colors {{ $errors->has("itemsReturned.{$index}.quantity") || $errors->has("itemsReturned.{$index}.condition") ? 'bg-red-50/50' : '' }}">
                                                            <td class="p-2.5 text-gray-700 font-medium">
                                                                {{ $item['item_name'] }}
                                                                @if($errors->has("itemsReturned.{$index}.condition") || $errors->has("itemsReturned.{$index}.quantity"))
                                                                    <p class="text-[11px] text-red-500 font-normal mt-0.5">Required</p>
                                                                @endif
                                                            </td>
                                                            <td class="p-2.5 text-center">
                                                                <input type="number" min="1" step="1"
                                                                       wire:model.live.debounce.300ms="itemsReturned.{{ $index }}.quantity"
                                                                       placeholder="1"
                                                                       onkeydown="if(!/[0-9]|Backspace|Tab|ArrowLeft|ArrowRight|Delete/.test(event.key))event.preventDefault()"
                                                                       oninput="this.value=this.value.replace(/^0+/,'').replace(/[^0-9]/g,'');if(this.value==='')this.value=''"
                                                                       class="w-14 text-xs text-center border rounded-lg px-1.5 py-1.5 focus:ring-1 transition-colors placeholder:text-gray-300 {{ $errors->has("itemsReturned.{$index}.quantity") ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : 'border-gray-200 focus:border-blue-400 focus:ring-blue-400' }}">
                                                            </td>
                                                            <td class="p-2.5 text-center">
                                                                <input type="number" min="0" step="1"
                                                                       wire:model.live.debounce.300ms="itemsReturned.{{ $index }}.quantity_returned"
                                                                       placeholder="0"
                                                                       onkeydown="if(!/[0-9]|Backspace|Tab|ArrowLeft|ArrowRight|Delete/.test(event.key))event.preventDefault()"
                                                                       oninput="this.value=this.value.replace(/^0+/,'').replace(/[^0-9]/g,'');if(this.value==='')this.value=''"
                                                                       class="w-14 text-xs text-center border rounded-lg px-1.5 py-1.5 focus:ring-1 transition-colors placeholder:text-gray-300 {{ $errors->has("itemsReturned.{$index}.quantity_returned") ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : 'border-gray-200 focus:border-blue-400 focus:ring-blue-400' }}">
                                                                @error("itemsReturned.{$index}.quantity_returned") <p class="text-[10px] text-red-500 mt-0.5">{{ $message }}</p> @enderror
                                                            </td>
                                                            <td class="p-2.5">
                                                                <div x-data="{
                                                                    open: false,
                                                                    dropUp: false,
                                                                    toggleDropdown() {
                                                                        if (this.open) { this.open = false; return; }
                                                                        const btn = this.$refs.moTrigger{{ $index }};
                                                                        const rect = btn.getBoundingClientRect();
                                                                        const scrollParent = btn.closest('.overflow-y-auto') || document.documentElement;
                                                                        const containerBottom = scrollParent === document.documentElement
                                                                            ? window.innerHeight
                                                                            : scrollParent.getBoundingClientRect().bottom;
                                                                        this.dropUp = (containerBottom - rect.bottom) < 220;
                                                                        this.open = true;
                                                                    }
                                                                }" @click.away="open = false" @keydown.escape.stop="open = false" class="relative">
                                                                    <button
                                                                        x-ref="moTrigger{{ $index }}"
                                                                        @click="toggleDropdown()"
                                                                        type="button"
                                                                        class="w-full flex items-center justify-between gap-1.5 bg-white border rounded-lg px-2.5 py-1.5 text-xs transition-all hover:bg-gray-50 focus:ring-1 outline-none {{ $errors->has("itemsReturned.{$index}.condition") ? 'border-red-400 focus:ring-red-400' : 'border-gray-200 focus:ring-blue-400' }}"
                                                                    >
                                                                        <span class="truncate {{ empty($item['condition']) ? 'text-gray-400' : 'text-gray-700' }}">
                                                                            {{ $item['condition'] ?: 'Select condition...' }}
                                                                        </span>
                                                                        <svg :class="{ 'rotate-180': open }" class="w-3.5 h-3.5 text-gray-400 shrink-0 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                                        </svg>
                                                                    </button>
                                                                    <div
                                                                        x-show="open"
                                                                        x-transition
                                                                        style="display: none;"
                                                                        class="absolute left-0 z-30 w-full bg-white border border-gray-100 rounded-xl shadow-xl overflow-hidden"
                                                                        :class="dropUp ? 'bottom-full mb-1' : 'top-full mt-1'"
                                                                    >
                                                                        @foreach(['Good', 'New', 'Fair', 'Damaged', 'Not Returned'] as $condition)
                                                                            <x-dropdown-item
                                                                                wire:click="setMoveOutItemCondition({{ $index }}, '{{ $condition }}')"
                                                                                :active="($item['condition'] ?? '') === $condition"
                                                                                @click="open = false"
                                                                            >
                                                                                {{ $condition }}
                                                                            </x-dropdown-item>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td class="p-2.5 text-center">
                                                                <label class="cursor-pointer">
                                                                    <input type="checkbox" wire:model.defer="itemsReturned.{{ $index }}.is_returned"
                                                                           class="w-4 h-4 text-emerald-500 border-gray-300 rounded focus:ring-emerald-400">
                                                                </label>
                                                            </td>
                                                            <td class="p-2.5">
                                                                <input type="number" min="0" step="0.01"
                                                                       wire:model.defer="itemsReturned.{{ $index }}.replacement_cost"
                                                                       placeholder="0.00"
                                                                       class="w-full text-xs text-right border border-gray-200 rounded-lg px-2 py-1.5 focus:border-blue-400 focus:ring-1 focus:ring-blue-400 transition-colors placeholder:text-gray-300">
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    {{-- Action Buttons --}}
                                    <div class="flex justify-end gap-2 pt-2">
                                        <button
                                            type="button"
                                            @click="close()"
                                            class="px-5 py-2 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors"
                                        >
                                            Cancel
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="saveMoveOutInspection"
                                            wire:loading.attr="disabled"
                                            class="px-5 py-2 text-xs font-bold text-white bg-[#070589] hover:bg-[#000060] rounded-xl transition-colors flex items-center gap-1.5 disabled:opacity-50"
                                        >
                                            <span wire:loading wire:target="saveMoveOutInspection">
                                                <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                            </span>
                                            Save Inspection
                                        </button>
                                    </div>
                                @endif
                    </x-inspection.expandable-card>

                    @endif {{-- end move-out conditional --}}

                    {{-- Move-In Contract Card (always visible) --}}
                    <button
                        type="button"
                        wire:click="openMoveInContract"
                        class="w-full bg-white rounded-2xl border border-gray-100 p-4 shadow-sm hover:shadow-md transition-all hover:border-blue-200 group text-left"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center group-hover:bg-blue-100 transition-colors">
                                    <svg class="w-5 h-5 text-[#2360E8]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 00-2.25 2.25v9a2.25 2.25 0 002.25 2.25h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25H15M12 1.5v13.5m0 0l-3-3m3 3l3-3"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-[#070589]">Move-In Contract</p>
                                    <p class="text-[11px] text-gray-400">View or download the move-in lease agreement</p>
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-gray-300 group-hover:text-[#2360E8] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                            </svg>
                        </div>
                    </button>

                    {{-- Move-Out Contract Card (only after move-out) --}}
                    @if(($currentTenant['move_out_details']['move_out_initiated_at'] ?? null) || ($currentTenant['move_out_details']['move_out_date'] ?? null))
                    <button
                        type="button"
                        wire:click="openMoveOutContract"
                        class="w-full bg-white rounded-2xl border border-gray-100 p-4 shadow-sm hover:shadow-md transition-all hover:border-indigo-200 group text-left"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center group-hover:bg-indigo-100 transition-colors">
                                    <svg class="w-5 h-5 text-[#070589]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 00-2.25 2.25v9a2.25 2.25 0 002.25 2.25h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25H15m0-3l-3-3m0 0l-3 3m3-3v13.5"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-[#070589]">Move-Out Contract</p>
                                    <p class="text-[11px] text-gray-400">View or download the move-out settlement agreement</p>
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-gray-300 group-hover:text-[#070589] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                            </svg>
                        </div>
                    </button>
                    @endif
                </div>

            </div>
        </div>

        {{-- ═══════════════════════════════════════════════
             MOVE-IN CONTRACT MODAL (Full 14-Section Template)
        ═══════════════════════════════════════════════ --}}
        @if($showMoveInContract)
            @php
                $t = $currentTenant;
                $rate = $t['move_in_details']['monthly_rate'];
                $deposit = $t['move_in_details']['security_deposit'];
                $premium = $t['move_in_details']['short_term_premium'] ?? 0;
                $dueDay = $t['move_in_details']['monthly_due_date'];
                $dueSfx = match((int) $dueDay) { 1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th' };
                $totalMoveIn = $rate + $deposit;
                $moveInHasAnySig = $ownerSignature || $managerSignature || $tenantSignature;
            @endphp
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm"
                 x-data="{ showLeaveConfirm: false }">
                <div class="relative w-full max-w-4xl bg-white rounded-2xl shadow-xl overflow-hidden max-h-[90vh] flex flex-col">
                    <div class="bg-[#070589] text-white p-5 flex items-center justify-between flex-shrink-0">
                        <h2 class="text-lg font-bold">Move-In Contract</h2>
                        <flux:tooltip :content="'Close the contract viewer'" position="bottom">
                            <button @click="{{ $moveInHasAnySig ? "\$el.closest('.fixed').style.display='none'; \$wire.closeMoveInContract()" : 'showLeaveConfirm = true' }}" class="text-white hover:text-blue-200"><svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </flux:tooltip>
                    </div>
                    <div class="flex-1 overflow-y-auto p-8 space-y-6 text-sm text-gray-800" id="move-in-contract" style="font-family: 'Open Sans', sans-serif;">

                        @include('partials.move-in-contract-body', [
                            't' => $t,
                            'rate' => $rate,
                            'deposit' => $deposit,
                            'premium' => $premium,
                            'dueDay' => $dueDay,
                            'dueSfx' => $dueSfx,
                            'totalMoveIn' => $totalMoveIn,
                            'inspectionChecklist' => $inspectionChecklist,
                            'itemsReceived' => $itemsReceived,
                            'tenantSignature' => $tenantSignature,
                            'ownerSignature' => $ownerSignature,
                            'managerSignature' => $managerSignature,
                            'tenantSignedAt' => $tenantSignedAt,
                            'ownerSignedAt' => $ownerSignedAt,
                            'managerSignedAt' => $managerSignedAt,
                            'contractAgreed' => $contractAgreed,
                            'signatureMode' => 'manager',
                        ])

                    </div>
                    <div class="p-4 bg-gray-50 border-t flex justify-end gap-3 flex-shrink-0">
                        @if($contractAgreed)
                            <button wire:click="downloadSignedContract" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-6 rounded-xl text-sm transition-colors flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                Download Signed PDF
                            </button>
                        @endif
                        <button onclick="printContract('move-in-contract')" class="bg-[#070589] hover:bg-[#000060] text-white font-bold py-2.5 px-6 rounded-xl text-sm transition-colors">
                            Print Contract
                        </button>
                    </div>
                </div>
                <x-contract-leave-confirm closeAction="$el.closest('.fixed').style.display='none'; $wire.closeMoveInContract()" />
            </div>
        @endif

        {{-- ═══════════════════════════════════════════════
             MOVE-OUT CONTRACT MODAL
        ═══════════════════════════════════════════════ --}}
        @if($showMoveOutContract)
            @php
                $t = $currentTenant;
                $deposit = $t['move_in_details']['security_deposit'];
                $moveOutHasAnySig = $moveOutOwnerSignature || $moveOutManagerSignature || $moveOutTenantSignature;
            @endphp
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm"
                 x-data="{ showLeaveConfirm: false }">
                <div class="relative w-full max-w-4xl bg-white rounded-2xl shadow-xl overflow-hidden max-h-[90vh] flex flex-col">
                    <div class="bg-[#070589] text-white p-5 flex items-center justify-between flex-shrink-0">
                        <h2 class="text-lg font-bold">Move-Out Clearance & Deposit Settlement</h2>
                        <flux:tooltip :content="'Close the contract viewer'" position="bottom">
                            <button @click="{{ $moveOutHasAnySig ? "\$el.closest('.fixed').style.display='none'; \$wire.closeMoveOutContract()" : 'showLeaveConfirm = true' }}" class="text-white hover:text-blue-200"><svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </flux:tooltip>
                    </div>
                    <div class="flex-1 overflow-y-auto p-8 space-y-6 text-sm text-gray-800" id="move-out-contract" style="font-family: 'Open Sans', sans-serif;">

                        @include('partials.move-out-contract-body', [
                            't' => $t,
                            'deposit' => $deposit,
                            'moveOutChecklist' => $moveOutChecklist,
                            'itemsReturned' => $itemsReturned,
                            'inspectionChecklist' => $inspectionChecklist,
                            'moveOutTenantSignature' => $moveOutTenantSignature,
                            'moveOutOwnerSignature' => $moveOutOwnerSignature,
                            'moveOutManagerSignature' => $moveOutManagerSignature,
                            'moveOutTenantSignedAt' => $moveOutTenantSignedAt,
                            'moveOutOwnerSignedAt' => $moveOutOwnerSignedAt,
                            'moveOutManagerSignedAt' => $moveOutManagerSignedAt,
                            'moveOutContractAgreed' => $moveOutContractAgreed,
                            'outstandingBalances' => $t['outstanding_balances'] ?? [],
                            'depositRefund' => $t['deposit_refund'] ?? [],
                            'signatureMode' => 'manager',
                        ])

                    </div>
                    <div class="p-4 bg-gray-50 border-t flex justify-end gap-3 flex-shrink-0">
                        @if($moveOutContractAgreed)
                            <button wire:click="downloadMoveOutSignedContract" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-6 rounded-xl text-sm transition-colors flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                Download Signed PDF
                            </button>
                        @endif
                        <button onclick="printContract('move-out-contract')" class="bg-[#070589] hover:bg-[#000060] text-white font-bold py-2.5 px-6 rounded-xl text-sm transition-colors">
                            Print Contract
                        </button>
                    </div>
                </div>
                <x-contract-leave-confirm closeAction="$el.closest('.fixed').style.display='none'; $wire.closeMoveOutContract()" />
            </div>
        @endif

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

    {{-- E-SIGNATURE PAD MODALS (Move-In & Move-Out) --}}
    <x-inspection.signature-pad-modal
        :show="$showSignatureModal"
        title="Witness E-Signature"
        subtitle="Move-In Contract — Sign as witness below"
        signerName=""
        signerRole="Unit Manager / Witness"
        legalText="By clicking &quot;Apply Signature&quot;, I confirm that I have witnessed the signing of this Move-In Contract and attest to its authenticity. This electronic signature is legally binding under RA 8792 (Electronic Commerce Act of 2000)."
        wireCloseMethod="closeSignatureModal"
        wireSaveMethod="saveSignature"
        canvasRef="sigCanvasMoveIn"
    />

    <x-inspection.signature-pad-modal
        :show="$showMoveOutSignatureModal"
        title="Witness E-Signature"
        subtitle="Move-Out Contract — Sign as witness below"
        signerName=""
        signerRole="Unit Manager / Witness"
        legalText="By clicking &quot;Apply Signature&quot;, I confirm that I have witnessed the signing of this Move-Out Clearance &amp; Deposit Settlement Agreement. This electronic signature is legally binding under RA 8792 (Electronic Commerce Act of 2000)."
        wireCloseMethod="closeMoveOutSignatureModal"
        wireSaveMethod="saveMoveOutSignature"
        canvasRef="sigCanvasMoveOut"
    />

    {{-- Move-Out Confirmation Modal (with prerequisite checklist) --}}
    <div
        x-data="{ show: false }"
        x-show="show"
        x-on:open-modal.window="if ($event.detail === 'move-out-confirmation' || $event.detail[0] === 'move-out-confirmation') show = true"
        x-on:close-modal.window="if ($event.detail === 'move-out-confirmation' || $event.detail[0] === 'move-out-confirmation') show = false"
        x-on:keydown.escape.window="show = false"
        class="fixed inset-0 z-[99] flex items-center justify-center px-4 py-6"
        style="display: none;"
    >
        <div x-show="show" class="fixed inset-0 transform transition-all" x-on:click="show = false">
            <div class="absolute inset-0 bg-gray-600 opacity-50"></div>
        </div>
        <div x-show="show" class="bg-white rounded-[20px] overflow-hidden shadow-xl transform transition-all sm:w-full sm:max-w-[480px] p-8 relative z-[100]">
            <button @click="show = false" class="absolute top-5 right-5 text-[#0C0B50] hover:text-blue-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <div class="text-center mt-2 mb-4">
                <h3 class="text-xl font-bold text-[#0C0B50] mb-2">Finalize Move-Out</h3>
                <p class="text-gray-500 text-sm">The lease will be marked as Expired, the deposit refund calculated, and the bed freed.</p>
            </div>

            <div class="space-y-2 mb-5">
                @foreach($moveOutPrerequisites as $prereq)
                    <div class="flex items-center gap-2.5 p-2.5 rounded-lg {{ $prereq['done'] ? 'bg-emerald-50' : 'bg-red-50' }}">
                        @if($prereq['done'])
                            <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @else
                            <svg class="w-4 h-4 text-red-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                        @endif
                        <span class="text-xs font-medium {{ $prereq['done'] ? 'text-emerald-700' : 'text-red-600' }}">{{ $prereq['label'] }}</span>
                    </div>
                @endforeach
            </div>

            {{-- Deposit Interest Input (RA 9653 IRR Section 7b) --}}
            <div class="mb-4 p-3 bg-blue-50 rounded-xl border border-blue-200">
                <label class="block text-xs font-semibold text-blue-800 mb-1.5">Deposit Interest Earned (optional)</label>
                <p class="text-[11px] text-blue-600 mb-2">Enter the interest earned on the security deposit per RA 9653. This amount will be added to the tenant's refund.</p>
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-gray-600">PHP</span>
                    <input type="number" wire:model="depositInterestAmount" step="0.01" min="0" placeholder="0.00"
                        class="flex-1 text-sm border border-blue-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:border-blue-400">
                </div>
            </div>

            <div class="flex justify-center gap-4 px-2">
                <button @click="show = false" class="flex-1 bg-[#D6E6FF] hover:bg-[#c3daff] text-[#0C0B50] font-bold py-3 rounded-xl transition-colors text-sm">
                    Cancel
                </button>
                @php $allDone = collect($moveOutPrerequisites)->every(fn($p) => $p['done']); @endphp
                <button
                    wire:click="confirmMoveOut"
                    wire:loading.attr="disabled"
                    @if(!$allDone) disabled @endif
                    class="flex-1 font-bold py-3 rounded-xl transition-colors text-sm disabled:opacity-50 disabled:cursor-not-allowed {{ $allDone ? 'bg-[#104EA2] hover:bg-[#0d3f82] text-white shadow-md' : 'bg-gray-300 text-gray-500 cursor-not-allowed' }}"
                >
                    <span wire:loading.remove wire:target="confirmMoveOut">Yes, Finalize</span>
                    <span wire:loading wire:target="confirmMoveOut">Processing...</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Initiate Move-Out Form Modal --}}
    <x-ui.modal-confirm
        name="initiate-move-out"
        title="Initiate Move-Out"
        description="Start the move-out process for this tenant"
        confirmText="Start Move-Out Process"
        cancelText="Cancel"
        confirmAction="initiateMoveOut"
    >
        <div class="space-y-4 text-left">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Reason for Vacating <span class="text-red-500">*</span></label>
                <select wire:model="reasonForVacating" class="w-full text-sm border rounded-xl px-3 py-2 focus:border-[#070589] focus:ring-1 focus:ring-[#070589] {{ $errors->has('reasonForVacating') ? 'border-red-400' : 'border-gray-200' }}">
                    <option value="">Select a reason...</option>
                    <option value="End of lease term (contract expired)">End of lease term (contract expired)</option>
                    <option value="Voluntary early termination by Lessee">Voluntary early termination by Lessee</option>
                    <option value="Mutual agreement between both parties">Mutual agreement between both parties</option>
                    <option value="Lease violation or termination by Lessor">Lease violation or termination by Lessor</option>
                    <option value="Transfer to a different unit / building (internal transfer)">Transfer to a different unit / building</option>
                </select>
                @error('reasonForVacating') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Forwarding Address <span class="text-red-500">*</span></label>
                <input type="text" wire:model="forwardingAddress" placeholder="Address for deposit refund / correspondence"
                       class="w-full text-sm border rounded-xl px-3 py-2 focus:border-[#070589] focus:ring-1 focus:ring-[#070589] placeholder:text-gray-300 {{ $errors->has('forwardingAddress') ? 'border-red-400' : 'border-gray-200' }}">
                @error('forwardingAddress') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Refund Method <span class="text-red-500">*</span></label>
                    <select wire:model="depositRefundMethod" class="w-full text-sm border rounded-xl px-3 py-2 focus:border-[#070589] focus:ring-1 focus:ring-[#070589] {{ $errors->has('depositRefundMethod') ? 'border-red-400' : 'border-gray-200' }}">
                        <option value="">Select...</option>
                        <option value="GCash">GCash</option>
                        <option value="Maya">Maya</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="Cash">Cash</option>
                    </select>
                    @error('depositRefundMethod') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Account Name / Number <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="depositRefundAccount" placeholder="e.g. 0917-xxx-xxxx"
                           class="w-full text-sm border rounded-xl px-3 py-2 focus:border-[#070589] focus:ring-1 focus:ring-[#070589] placeholder:text-gray-300 {{ $errors->has('depositRefundAccount') ? 'border-red-400' : 'border-gray-200' }}">
                    @error('depositRefundAccount') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>
    </x-ui.modal-confirm>

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

<script>
    function printContract(elementId) {
        const content = document.getElementById(elementId);
        if (!content) return;

        // Clone and sanitize: remove no-print elements, show print-only elements
        const clone = content.cloneNode(true);
        clone.querySelectorAll('.no-print').forEach(el => el.remove());
        clone.querySelectorAll('.print-only').forEach(el => el.style.display = 'block');
        // Remove any stray buttons (wire:click etc.)
        clone.querySelectorAll('button[wire\\:click]').forEach(el => el.remove());

        // Remove any existing print iframe
        const existingFrame = document.getElementById('print-contract-frame');
        if (existingFrame) existingFrame.remove();

        const iframe = document.createElement('iframe');
        iframe.id = 'print-contract-frame';
        iframe.style.cssText = 'position:fixed;width:0;height:0;border:none;left:-9999px;top:-9999px;';
        document.body.appendChild(iframe);

        const doc = iframe.contentDocument || iframe.contentWindow.document;
        doc.open();
        doc.write(getPrintHTML(clone.innerHTML, elementId));
        doc.close();

        // Fix header to be edge-to-edge in print
        const header = doc.querySelector('.contract-body > div:first-child');
        if (header) {
            header.style.borderRadius = '0';
            header.style.margin = '-15mm -15mm 1.2rem -15mm';
            header.style.padding = '0.85rem 15mm';
        }

        function doPrint() {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
        }

        // Wait for images to load before printing
        const images = doc.querySelectorAll('img');
        if (images.length > 0) {
            let loaded = 0;
            images.forEach(img => {
                if (img.complete) {
                    loaded++;
                    if (loaded === images.length) doPrint();
                } else {
                    img.onload = img.onerror = () => {
                        loaded++;
                        if (loaded === images.length) doPrint();
                    };
                }
            });
        } else {
            setTimeout(doPrint, 300);
        }
    }

    function getPrintHTML(innerHTML, contractType) {
        const tenantName = @json(($currentTenant['personal_info']['first_name'] ?? '') . ' ' . ($currentTenant['personal_info']['last_name'] ?? 'Tenant'));
        const unitNumber = @json($currentTenant['personal_info']['unit'] ?? 'N-A');
        const purpose = contractType === 'move-out-contract' ? 'Move-Out-Contract' : 'Move-In-Contract';
        const docTitle = `${purpose}_${tenantName.replace(/\s+/g, '-')}_Unit-${unitNumber}`;
        return `
            <!DOCTYPE html>
            <html>
            <head>
                <title>${docTitle}</title>
                <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
                <style>
                    @page { margin: 1in 0; }
                    @page:first { margin: 0; }
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    body { font-family: 'Open Sans', sans-serif; font-size: 12px; color: #1f2937; line-height: 1.5; }
                    .contract-body { padding: 15mm 18mm; }
                    .contract-body > div { margin-bottom: 1.2rem; }
                    /* Make header stretch edge-to-edge on first page */
                    .contract-body > div:first-child { margin-left: -18mm !important; margin-right: -18mm !important; margin-top: -15mm !important; padding: 0.85rem 18mm !important; border-radius: 0 !important; }

                    table { page-break-inside: avoid; }

                    /* Typography */
                    h1 { font-size: 1.4rem; font-weight: 700; }
                    h3 { font-size: 0.8rem; font-weight: 700; text-transform: uppercase; margin-bottom: 0.6rem; padding-bottom: 0.25rem; border-bottom: 1px solid #e5e7eb; }
                    p { margin-bottom: 0.4rem; }
                    ul { padding-left: 1.25rem; margin-bottom: 0.5rem; }
                    li { margin-bottom: 0.15rem; }

                    /* Tables */
                    table { width: 100%; border-collapse: collapse; border: 1px solid #d1d5db; font-size: 0.78rem; margin-bottom: 0.6rem; }
                    td, th { padding: 0.4rem 0.5rem; border: 1px solid #d1d5db; }
                    th { background: #3B5998; color: white; text-align: left; }
                    .bg-gray-50, td.bg-gray-50 { background: #f9fafb; }

                    /* Dark header bar */
                    .bg-\\[\\#1a1a4e\\] { background: #1a1a4e; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                    .rounded-lg { border-radius: 0.5rem; }
                    .tracking-wide { letter-spacing: 0.025em; }
                    .tracking-wider { letter-spacing: 0.05em; }
                    .text-white { color: #ffffff; }
                    .text-gray-300 { color: #d1d5db; }

                    /* Colors */
                    .text-\\[\\#3B5998\\], h3 { color: #3B5998; }
                    .text-gray-500 { color: #6b7280; }
                    .text-gray-600 { color: #4b5563; }
                    .text-gray-700 { color: #374151; }
                    .text-gray-400 { color: #9ca3af; }
                    .text-gray-900 { color: #111827; }

                    /* Grid for signatures */
                    .grid { display: grid; }
                    .grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
                    .gap-12 { gap: 3rem; }
                    .gap-3 { gap: 0.75rem; }

                    /* Utilities */
                    .text-center { text-align: center; }
                    .text-right { text-align: right; }
                    .text-xs { font-size: 0.7rem; }
                    .text-sm { font-size: 0.78rem; }
                    .text-2xl { font-size: 1.4rem; }
                    .font-bold { font-weight: 700; }
                    .font-semibold { font-weight: 600; }
                    .uppercase { text-transform: uppercase; }
                    .italic { font-style: italic; }
                    .border-b { border-bottom: 1px solid #d1d5db; }
                    .border-t { border-top: 1px solid #d1d5db; }
                    .border { border: 1px solid #d1d5db; }
                    .rounded-lg { border-radius: 0.5rem; }
                    .rounded-xl { border-radius: 0.75rem; }
                    .mb-1 { margin-bottom: 0.25rem; }
                    .mb-2 { margin-bottom: 0.5rem; }
                    .mb-3 { margin-bottom: 0.75rem; }
                    .mb-4 { margin-bottom: 1rem; }
                    .mb-6 { margin-bottom: 1.5rem; }
                    .mt-2 { margin-top: 0.5rem; }
                    .mt-3 { margin-top: 0.75rem; }
                    .mt-6 { margin-top: 1.5rem; }
                    .mt-8 { margin-top: 2rem; }
                    .pt-3 { padding-top: 0.75rem; }
                    .pt-6 { padding-top: 1.5rem; }
                    .py-4 { padding-top: 1rem; padding-bottom: 1rem; }
                    .p-2 { padding: 0.5rem; }
                    .p-3 { padding: 0.75rem; }
                    .pl-5 { padding-left: 1.25rem; }
                    .pb-1 { padding-bottom: 0.25rem; }
                    .pb-2 { padding-bottom: 0.5rem; }
                    .pb-4 { padding-bottom: 1rem; }
                    .w-full { width: 100%; }
                    .w-1\\/3 { width: 33.333%; }
                    .h-16 { height: 4rem; }
                    .h-20 { height: 5rem; }
                    .max-w-lg { max-width: 32rem; }
                    .flex { display: flex; }
                    .flex-col { flex-direction: column; }
                    .items-center { align-items: center; }
                    .items-start { align-items: flex-start; }
                    .justify-between { justify-content: space-between; }
                    .space-y-0\\.5 > * + * { margin-top: 0.125rem; }
                    .space-y-1 > * + * { margin-top: 0.25rem; }
                    .space-y-6 > * + * { margin-top: 1.5rem; }
                    .list-disc { list-style-type: disc; }
                    .leading-relaxed { line-height: 1.625; }
                    .overflow-hidden { overflow: hidden; }
                    .border-r { border-right: 1px solid #d1d5db; }
                    .border-l { border-left: 1px solid #d1d5db; }
                    .ml-8 { margin-left: 2rem; }
                    .px-5 { padding-left: 1.25rem; padding-right: 1.25rem; }
                    .py-3 { padding-top: 0.75rem; padding-bottom: 0.75rem; }
                    img { max-width: 100%; height: auto; }
                    .no-print { display: none !important; }
                    .print-only { display: block !important; }

                    @media print {
                        body { background: #fff; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                        .contract-body { padding: 0 18mm; }
                        /* First page: edge-to-edge header */
                        .contract-body > div:first-child { margin-left: -18mm !important; margin-right: -18mm !important; margin-top: 0 !important; padding: 0.85rem 18mm !important; border-radius: 0 !important; }
                        table { page-break-inside: avoid; }
                        th { background: #3B5998 !important; color: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                    }
                </style>
            </head>
            <body>
                <div class="contract-body">
                    ${innerHTML}
                </div>
            </body>
            </html>
        `;
    }
    </script>
</div>

