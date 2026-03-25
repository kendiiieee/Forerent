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
                                <button
                                    onclick="printContract('move-in-contract')"
                                    class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors"
                                    style="background: rgba(255,255,255,0.15); color: rgba(255,255,255,0.9); border: 1px solid rgba(255,255,255,0.25);"
                                    onmouseover="this.style.background='rgba(255,255,255,0.25)'"
                                    onmouseout="this.style.background='rgba(255,255,255,0.15)'"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.056 48.056 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/>
                                    </svg>
                                    Print Contract
                                </button>
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
                            <p class="text-sm font-bold text-gray-800">{{ $currentTenant['contact_info']['contact_number'] }}</p>
                        </div>
                        <div class="bg-[#F8FAFF] rounded-xl p-3.5 border border-blue-50">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1">Email</p>
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
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1">Bed Number</p>
                            <p class="text-sm font-bold text-gray-800">{{ $currentTenant['rent_details']['bed_number'] }}</p>
                        </div>
                        <div class="bg-[#F8FAFF] rounded-xl p-3.5 border border-blue-50">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1">Dorm Type</p>
                            <p class="text-sm font-bold text-gray-800">{{ $currentTenant['rent_details']['dorm_type'] }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div class="bg-[#EEF2FF] rounded-xl p-3.5">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1">Move-In Date</p>
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
                        <h5 class="font-bold text-sm text-[#070589] uppercase tracking-wide">Payment Details</h5>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div class="bg-[#F8FAFF] rounded-xl p-3.5 border border-blue-50">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1">Monthly Rate</p>
                            <p class="text-sm font-bold text-[#070589]">&#8369; {{ number_format($currentTenant['move_in_details']['monthly_rate'], 2) }}</p>
                        </div>
                        <div class="bg-[#F8FAFF] rounded-xl p-3.5 border border-blue-50">
                            @php
                                $dueDay = $currentTenant['move_in_details']['monthly_due_date'];
                                $dueSuffix = match((int) $dueDay) { 1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th' };
                            @endphp
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1">Due Date</p>
                            <p class="text-sm font-bold text-gray-800">{{ $dueDay ? $dueDay . $dueSuffix . ' of the month' : '—' }}</p>
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
                                Move Out
                            </div>
                        </button>
                    </div>
                @endif

                {{-- Contract Cards --}}
                <div class="space-y-3 pt-1 pb-2">

                    {{-- Move-In Inspection Expandable Card --}}
                    <div
                        x-data="{
                            open: false,
                            animating: false,
                            height: '0',
                            init() {
                            },
                            toggle() {
                                if (this.open) { this.close(); } else { this.openCard(); }
                            },
                            openCard() {
                                this.open = true;
                                this.animating = true;
                                const el = this.$refs.content;
                                el.style.height = '0px';
                                el.style.overflow = 'hidden';
                                this.$nextTick(() => {
                                    el.style.height = el.scrollHeight + 'px';
                                    setTimeout(() => {
                                        if (this.open) {
                                            el.style.height = 'auto';
                                            el.style.overflow = 'visible';
                                        }
                                        this.animating = false;
                                    }, 500);
                                });
                            },
                            close() {
                                this.animating = true;
                                const el = this.$refs.content;
                                el.style.height = el.scrollHeight + 'px';
                                el.style.overflow = 'hidden';
                                requestAnimationFrame(() => {
                                    el.style.height = '0px';
                                    setTimeout(() => {
                                        this.open = false;
                                        this.animating = false;
                                    }, 500);
                                });
                            }
                        }"
                        class="w-full bg-white rounded-2xl border border-gray-100 shadow-sm transition-all overflow-hidden"
                        :class="open ? 'border-emerald-200 shadow-md' : 'hover:shadow-md hover:border-emerald-200'"
                    >
                        {{-- Trigger Header --}}
                        <button
                            type="button"
                            @click="toggle()"
                            class="w-full p-4 text-left group"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors"
                                         :class="open ? 'bg-emerald-100' : 'bg-emerald-50 group-hover:bg-emerald-100'">
                                        <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <p class="text-sm font-bold text-[#070589]">Move-In Inspection</p>
                                            @if($inspectionSaved)
                                                <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                                    Completed
                                                </span>
                                            @else
                                                <span class="inline-flex items-center text-[10px] font-semibold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">
                                                    Pending
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-[10px] text-gray-400">Record room condition and items received</p>
                                    </div>
                                </div>
                                {{-- Animated chevron --}}
                                <svg
                                    class="w-5 h-5 text-gray-300 transition-all duration-300 ease-out"
                                    :class="open ? 'rotate-90 text-emerald-500' : 'group-hover:text-emerald-500'"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                                </svg>
                            </div>
                        </button>

                        {{-- Expandable Content --}}
                        <div
                            x-ref="content"
                            class="transition-[height] duration-500 ease-[cubic-bezier(0.34,1.56,0.64,1)]"
                            style="height: 0px; overflow: hidden;"
                        >
                            <div class="px-4 pb-5 space-y-5">
                                {{-- Divider --}}
                                <div class="border-t border-gray-100"></div>

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
                                                        <tr class="border-b border-gray-100 hover:bg-gray-50/50 transition-colors {{ $errors->has("inspectionChecklist.{$index}.condition") ? 'bg-red-50/50' : '' }}">
                                                            <td class="p-2.5 text-gray-700 font-medium">
                                                                {{ $item['item_name'] }}
                                                                @error("inspectionChecklist.{$index}.condition")
                                                                    <p class="text-[9px] text-red-500 font-normal mt-0.5">Required</p>
                                                                @enderror
                                                            </td>
                                                            <td class="p-2.5 text-center">
                                                                <label class="cursor-pointer">
                                                                    <input type="radio" wire:model.defer="inspectionChecklist.{{ $index }}.condition" value="good"
                                                                           class="w-4 h-4 text-emerald-500 border-gray-300 focus:ring-emerald-400">
                                                                </label>
                                                            </td>
                                                            <td class="p-2.5 text-center">
                                                                <label class="cursor-pointer">
                                                                    <input type="radio" wire:model.defer="inspectionChecklist.{{ $index }}.condition" value="damaged"
                                                                           class="w-4 h-4 text-amber-500 border-gray-300 focus:ring-amber-400">
                                                                </label>
                                                            </td>
                                                            <td class="p-2.5 text-center">
                                                                <label class="cursor-pointer">
                                                                    <input type="radio" wire:model.defer="inspectionChecklist.{{ $index }}.condition" value="missing"
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
                                                    @foreach($itemsReceived as $index => $item)
                                                        <tr class="border-b border-gray-100 hover:bg-gray-50/50 transition-colors {{ $errors->has("itemsReceived.{$index}.quantity") || $errors->has("itemsReceived.{$index}.condition") ? 'bg-red-50/50' : '' }}">
                                                            <td class="p-2.5 text-gray-700 font-medium">{{ $item['item_name'] }}</td>
                                                            <td class="p-2.5 text-center">
                                                                <input type="number" min="1" step="1"
                                                                       wire:model.defer="itemsReceived.{{ $index }}.quantity"
                                                                       placeholder="1"
                                                                       onkeydown="if(!/[0-9]|Backspace|Tab|ArrowLeft|ArrowRight|Delete/.test(event.key))event.preventDefault()"
                                                                       oninput="this.value=this.value.replace(/^0+/,'').replace(/[^0-9]/g,'');if(this.value==='')this.value=''"
                                                                       class="w-14 text-xs text-center border rounded-lg px-1.5 py-1.5 focus:ring-1 transition-colors placeholder:text-gray-300 {{ $errors->has("itemsReceived.{$index}.quantity") ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : 'border-gray-200 focus:border-blue-400 focus:ring-blue-400' }}">
                                                                @error("itemsReceived.{$index}.quantity")
                                                                    <p class="text-[9px] text-red-500 mt-0.5">{{ $message }}</p>
                                                                @enderror
                                                            </td>
                                                            <td class="p-2.5">
                                                                <input type="text" wire:model.defer="itemsReceived.{{ $index }}.condition"
                                                                       placeholder="e.g. Good, New..."
                                                                       class="w-full text-xs border rounded-lg px-2.5 py-1.5 focus:ring-1 transition-colors placeholder:text-gray-300 {{ $errors->has("itemsReceived.{$index}.condition") ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : 'border-gray-200 focus:border-blue-400 focus:ring-blue-400' }}">
                                                                @error("itemsReceived.{$index}.condition")
                                                                    <p class="text-[9px] text-red-500 mt-0.5">{{ $message }}</p>
                                                                @enderror
                                                            </td>
                                                            <td class="p-2.5 text-center">
                                                                <label class="cursor-pointer">
                                                                    <input type="checkbox" wire:model.defer="itemsReceived.{{ $index }}.tenant_confirmed"
                                                                           class="w-4 h-4 text-blue-500 border-gray-300 rounded focus:ring-blue-400">
                                                                </label>
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
                                            class="px-5 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-colors flex items-center gap-1.5 disabled:opacity-50"
                                        >
                                            <span wire:loading.remove wire:target="saveInspection">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                            </span>
                                            <span wire:loading wire:target="saveInspection">
                                                <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                            </span>
                                            Save Inspection
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Move-Out Inspection & Contract (only visible after move-out) --}}
                    @if($currentTenant['move_out_details']['move_out_date'] ?? null)

                    {{-- Move-Out Inspection Expandable Card --}}
                    <div
                        x-data="{
                            open: false,
                            animating: false,
                            height: '0',
                            init() {
                            },
                            toggle() {
                                if (this.open) { this.close(); } else { this.openCard(); }
                            },
                            openCard() {
                                this.open = true;
                                this.animating = true;
                                const el = this.$refs.moveOutContent;
                                el.style.height = '0px';
                                el.style.overflow = 'hidden';
                                this.$nextTick(() => {
                                    el.style.height = el.scrollHeight + 'px';
                                    setTimeout(() => {
                                        if (this.open) {
                                            el.style.height = 'auto';
                                            el.style.overflow = 'visible';
                                        }
                                        this.animating = false;
                                    }, 500);
                                });
                            },
                            close() {
                                this.animating = true;
                                const el = this.$refs.moveOutContent;
                                el.style.height = el.scrollHeight + 'px';
                                el.style.overflow = 'hidden';
                                requestAnimationFrame(() => {
                                    el.style.height = '0px';
                                    setTimeout(() => {
                                        this.open = false;
                                        this.animating = false;
                                    }, 500);
                                });
                            }
                        }"
                        class="w-full bg-white rounded-2xl border border-gray-100 shadow-sm transition-all overflow-hidden"
                        :class="open ? 'border-red-200 shadow-md' : 'hover:shadow-md hover:border-red-200'"
                    >
                        {{-- Trigger Header --}}
                        <button
                            type="button"
                            @click="toggle()"
                            class="w-full p-4 text-left group"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors"
                                         :class="open ? 'bg-red-100' : 'bg-red-50 group-hover:bg-red-100'">
                                        <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <p class="text-sm font-bold text-[#070589]">Move-Out Inspection</p>
                                            @if($moveOutInspectionSaved)
                                                <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                                    Completed
                                                </span>
                                            @else
                                                <span class="inline-flex items-center text-[10px] font-semibold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">
                                                    Pending
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-[10px] text-gray-400">Record room condition and items returned</p>
                                    </div>
                                </div>
                                {{-- Animated chevron --}}
                                <svg
                                    class="w-5 h-5 text-gray-300 transition-all duration-300 ease-out"
                                    :class="open ? 'rotate-90 text-red-500' : 'group-hover:text-red-500'"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                                </svg>
                            </div>
                        </button>

                        {{-- Expandable Content --}}
                        <div
                            x-ref="moveOutContent"
                            class="transition-[height] duration-500 ease-[cubic-bezier(0.34,1.56,0.64,1)]"
                            style="height: 0px; overflow: hidden;"
                        >
                            <div class="px-4 pb-5 space-y-5">
                                {{-- Divider --}}
                                <div class="border-t border-gray-100"></div>

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
                                                        <th class="text-left p-2.5 font-semibold text-gray-600 w-2/5">Item</th>
                                                        <th class="text-center p-2.5 font-semibold text-gray-600 w-16">Good</th>
                                                        <th class="text-center p-2.5 font-semibold text-gray-600 w-20">Damaged</th>
                                                        <th class="text-center p-2.5 font-semibold text-gray-600 w-18">Missing</th>
                                                        <th class="text-left p-2.5 font-semibold text-gray-600">Remarks</th>
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
                                                        <th class="text-left p-2.5 font-semibold text-gray-600 w-2/5">Item</th>
                                                        <th class="text-center p-2.5 font-semibold text-gray-600 w-16">Qty</th>
                                                        <th class="text-left p-2.5 font-semibold text-gray-600">Condition</th>
                                                        <th class="text-center p-2.5 font-semibold text-gray-600 w-24">Confirmed</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($itemsReturned as $item)
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
                                                        <th class="text-left p-2.5 font-semibold text-gray-600 w-2/5">Item</th>
                                                        <th class="text-center p-2.5 font-semibold text-gray-600 w-16">Good</th>
                                                        <th class="text-center p-2.5 font-semibold text-gray-600 w-20">Damaged</th>
                                                        <th class="text-center p-2.5 font-semibold text-gray-600 w-18">Missing</th>
                                                        <th class="text-left p-2.5 font-semibold text-gray-600">Remarks</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($moveOutChecklist as $index => $item)
                                                        <tr class="border-b border-gray-100 hover:bg-gray-50/50 transition-colors {{ $errors->has("moveOutChecklist.{$index}.condition") ? 'bg-red-50/50' : '' }}">
                                                            <td class="p-2.5 text-gray-700 font-medium">
                                                                {{ $item['item_name'] }}
                                                                @error("moveOutChecklist.{$index}.condition")
                                                                    <p class="text-[9px] text-red-500 font-normal mt-0.5">Required</p>
                                                                @enderror
                                                            </td>
                                                            <td class="p-2.5 text-center">
                                                                <label class="cursor-pointer">
                                                                    <input type="radio" wire:model.defer="moveOutChecklist.{{ $index }}.condition" value="good"
                                                                           class="w-4 h-4 text-emerald-500 border-gray-300 focus:ring-emerald-400">
                                                                </label>
                                                            </td>
                                                            <td class="p-2.5 text-center">
                                                                <label class="cursor-pointer">
                                                                    <input type="radio" wire:model.defer="moveOutChecklist.{{ $index }}.condition" value="damaged"
                                                                           class="w-4 h-4 text-amber-500 border-gray-300 focus:ring-amber-400">
                                                                </label>
                                                            </td>
                                                            <td class="p-2.5 text-center">
                                                                <label class="cursor-pointer">
                                                                    <input type="radio" wire:model.defer="moveOutChecklist.{{ $index }}.condition" value="missing"
                                                                           class="w-4 h-4 text-red-500 border-gray-300 focus:ring-red-400">
                                                                </label>
                                                            </td>
                                                            <td class="p-2.5">
                                                                <input type="text" wire:model.defer="moveOutChecklist.{{ $index }}.remarks"
                                                                       placeholder="Optional notes..."
                                                                       class="w-full text-xs border border-gray-200 rounded-lg px-2.5 py-1.5 focus:border-red-400 focus:ring-1 focus:ring-red-400 transition-colors placeholder:text-gray-300">
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
                                                        <th class="text-left p-2.5 font-semibold text-gray-600 w-2/5">Item</th>
                                                        <th class="text-center p-2.5 font-semibold text-gray-600 w-16">Qty</th>
                                                        <th class="text-left p-2.5 font-semibold text-gray-600">Condition</th>
                                                        <th class="text-center p-2.5 font-semibold text-gray-600 w-24">Confirmed</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($itemsReturned as $index => $item)
                                                        <tr class="border-b border-gray-100 hover:bg-gray-50/50 transition-colors {{ $errors->has("itemsReturned.{$index}.quantity") || $errors->has("itemsReturned.{$index}.condition") ? 'bg-red-50/50' : '' }}">
                                                            <td class="p-2.5 text-gray-700 font-medium">{{ $item['item_name'] }}</td>
                                                            <td class="p-2.5 text-center">
                                                                <input type="number" min="1" step="1"
                                                                       wire:model.defer="itemsReturned.{{ $index }}.quantity"
                                                                       placeholder="1"
                                                                       onkeydown="if(!/[0-9]|Backspace|Tab|ArrowLeft|ArrowRight|Delete/.test(event.key))event.preventDefault()"
                                                                       oninput="this.value=this.value.replace(/^0+/,'').replace(/[^0-9]/g,'');if(this.value==='')this.value=''"
                                                                       class="w-14 text-xs text-center border rounded-lg px-1.5 py-1.5 focus:ring-1 transition-colors placeholder:text-gray-300 {{ $errors->has("itemsReturned.{$index}.quantity") ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : 'border-gray-200 focus:border-blue-400 focus:ring-blue-400' }}">
                                                                @error("itemsReturned.{$index}.quantity")
                                                                    <p class="text-[9px] text-red-500 mt-0.5">{{ $message }}</p>
                                                                @enderror
                                                            </td>
                                                            <td class="p-2.5">
                                                                <input type="text" wire:model.defer="itemsReturned.{{ $index }}.condition"
                                                                       placeholder="e.g. Good, Damaged..."
                                                                       class="w-full text-xs border rounded-lg px-2.5 py-1.5 focus:ring-1 transition-colors placeholder:text-gray-300 {{ $errors->has("itemsReturned.{$index}.condition") ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : 'border-gray-200 focus:border-blue-400 focus:ring-blue-400' }}">
                                                                @error("itemsReturned.{$index}.condition")
                                                                    <p class="text-[9px] text-red-500 mt-0.5">{{ $message }}</p>
                                                                @enderror
                                                            </td>
                                                            <td class="p-2.5 text-center">
                                                                <label class="cursor-pointer">
                                                                    <input type="checkbox" wire:model.defer="itemsReturned.{{ $index }}.tenant_confirmed"
                                                                           class="w-4 h-4 text-blue-500 border-gray-300 rounded focus:ring-blue-400">
                                                                </label>
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
                                            class="px-5 py-2 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors flex items-center gap-1.5 disabled:opacity-50"
                                        >
                                            <span wire:loading.remove wire:target="saveMoveOutInspection">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                            </span>
                                            <span wire:loading wire:target="saveMoveOutInspection">
                                                <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                            </span>
                                            Save Inspection
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

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
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-[#070589]">Move-In Contract</p>
                                    <p class="text-[10px] text-gray-400">View or download the move-in lease agreement</p>
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-gray-300 group-hover:text-[#2360E8] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                            </svg>
                        </div>
                    </button>

                    {{-- Move-Out Contract Card (only after move-out) --}}
                    @if($currentTenant['move_out_details']['move_out_date'] ?? null)
                    <button
                        type="button"
                        wire:click="openMoveOutContract"
                        class="w-full bg-white rounded-2xl border border-gray-100 p-4 shadow-sm hover:shadow-md transition-all hover:border-indigo-200 group text-left"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center group-hover:bg-indigo-100 transition-colors">
                                    <svg class="w-5 h-5 text-[#070589]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-[#070589]">Move-Out Contract</p>
                                    <p class="text-[10px] text-gray-400">View or download the move-out settlement agreement</p>
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
            @endphp
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm">
                <div class="relative w-full max-w-4xl bg-white rounded-2xl shadow-xl overflow-hidden max-h-[90vh] flex flex-col">
                    <div class="bg-[#070589] text-white p-5 flex items-center justify-between flex-shrink-0">
                        <h2 class="text-lg font-bold">Move-In Contract</h2>
                        <button wire:click="closeMoveInContract" class="text-white hover:text-blue-200"><svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
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
                            'tenantSignedAt' => $tenantSignedAt,
                            'ownerSignedAt' => $ownerSignedAt,
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
            </div>
        @endif

        {{-- ═══════════════════════════════════════════════
             MOVE-OUT CONTRACT MODAL
        ═══════════════════════════════════════════════ --}}
        @if($showMoveOutContract)
            @php
                $t = $currentTenant;
                $deposit = $t['move_in_details']['security_deposit'];
            @endphp
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm">
                <div class="relative w-full max-w-4xl bg-white rounded-2xl shadow-xl overflow-hidden max-h-[90vh] flex flex-col">
                    <div class="bg-[#070589] text-white p-5 flex items-center justify-between flex-shrink-0">
                        <h2 class="text-lg font-bold">Move-Out Clearance & Deposit Settlement</h2>
                        <button wire:click="closeMoveOutContract" class="text-white hover:text-blue-200"><svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                    <div class="flex-1 overflow-y-auto p-8 space-y-6 text-sm text-gray-800" id="move-out-contract" style="font-family: 'Open Sans', sans-serif;">

                        @include('partials.move-out-contract-body', [
                            't' => $t,
                            'deposit' => $deposit,
                            'moveOutChecklist' => $moveOutChecklist,
                            'itemsReturned' => $itemsReturned,
                            'inspectionChecklist' => $inspectionChecklist,
                        ])

                    </div>
                    <div class="p-4 bg-gray-50 border-t flex justify-end gap-3 flex-shrink-0">
                        <button onclick="printContract('move-out-contract')" class="bg-[#070589] hover:bg-[#000060] text-white font-bold py-2.5 px-6 rounded-xl text-sm transition-colors">
                            Print Contract
                        </button>
                    </div>
                </div>
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

    {{-- ═══════════════════════════════════════════════
         E-SIGNATURE PAD MODAL
    ═══════════════════════════════════════════════ --}}
    @if($showSignatureModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
             x-data="{
                pad: null,
                isEmpty: true,
                libLoaded: false,

                init() {
                    this.loadLibrary().then(() => {
                        this.libLoaded = true;
                        this.setupCanvas();
                    });
                },

                loadLibrary() {
                    return new Promise((resolve) => {
                        if (window.SignaturePad) {
                            resolve();
                            return;
                        }
                        const script = document.createElement('script');
                        script.src = 'https://cdn.jsdelivr.net/npm/signature_pad@4.2.0/dist/signature_pad.umd.min.js';
                        script.onload = () => resolve();
                        document.head.appendChild(script);
                    });
                },

                setupCanvas() {
                    this.$nextTick(() => {
                        setTimeout(() => {
                            const canvas = this.$refs.signatureCanvas;
                            if (!canvas) return;

                            const rect = canvas.getBoundingClientRect();
                            if (rect.width === 0 || rect.height === 0) {
                                setTimeout(() => this.setupCanvas(), 150);
                                return;
                            }

                            const ratio = Math.max(window.devicePixelRatio || 1, 1);
                            canvas.width = rect.width * ratio;
                            canvas.height = rect.height * ratio;
                            canvas.getContext('2d').scale(ratio, ratio);

                            this.pad = new SignaturePad(canvas, {
                                backgroundColor: 'rgba(255, 255, 255, 0)',
                                penColor: '#000000',
                                minWidth: 1,
                                maxWidth: 2.5,
                            });

                            this.pad.addEventListener('beginStroke', () => {
                                this.isEmpty = false;
                            });
                        }, 100);
                    });
                },

                clearPad() {
                    if (this.pad) {
                        this.pad.clear();
                        this.isEmpty = true;
                    }
                },

                submitSignature() {
                    if (!this.pad || this.pad.isEmpty()) return;
                    const dataUrl = this.pad.toDataURL('image/png');
                    $wire.call('saveSignature', dataUrl);
                }
             }"
        >
            <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl">
                {{-- Header --}}
                <div class="bg-gradient-to-r from-[#070589] to-[#2360E8] text-white p-5 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold">
                            {{ $signatureRole === 'tenant' ? 'Tenant' : 'Lessor' }} E-Signature
                        </h2>
                        <p class="text-xs text-blue-200 mt-0.5">Draw your signature below using your mouse or finger</p>
                    </div>
                    <button wire:click="closeSignatureModal" class="text-white hover:text-blue-200 transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Signer info --}}
                <div class="px-5 pt-4 pb-2">
                    <div class="bg-gray-50 rounded-xl p-3 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-[#EEF2FF] flex items-center justify-center">
                            <svg class="w-4 h-4 text-[#2360E8]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-800">
                                @if($signatureRole === 'tenant')
                                    {{ $currentTenant['personal_info']['first_name'] ?? '' }} {{ $currentTenant['personal_info']['last_name'] ?? '' }}
                                @else
                                    {{ $currentTenant['lessor_info']['representative'] ?? '' }}
                                @endif
                            </p>
                            <p class="text-[10px] text-gray-500">Signing as {{ $signatureRole === 'tenant' ? 'Lessee / Tenant' : 'Lessor / Authorized Representative' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Canvas --}}
                <div class="px-5 py-3">
                    <div class="border-2 border-gray-200 rounded-xl overflow-hidden bg-white relative" style="touch-action: none;">
                        <canvas
                            x-ref="signatureCanvas"
                            class="w-full cursor-crosshair"
                            style="height: 200px; display: block;"
                        ></canvas>
                        {{-- Signature line hint --}}
                        <div class="absolute bottom-10 left-8 right-8 border-b border-dashed border-gray-200 pointer-events-none"></div>
                        <div class="absolute bottom-6 left-1/2 -translate-x-1/2 pointer-events-none">
                            <span class="text-[9px] text-gray-300 uppercase tracking-wider" x-show="isEmpty">Sign here</span>
                        </div>
                    </div>
                </div>

                {{-- Legal notice --}}
                <div class="px-5 pb-3">
                    <p class="text-[10px] text-gray-400 leading-relaxed">
                        By clicking "Apply Signature", I confirm that I have read and agree to all terms in this Move-In Contract. This electronic signature is legally binding under RA 8792 (Electronic Commerce Act of 2000).
                    </p>
                </div>

                {{-- Actions --}}
                <div class="px-5 pb-5 flex items-center justify-between">
                    <button
                        @click="clearPad()"
                        class="flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182"/></svg>
                        Clear
                    </button>
                    <div class="flex gap-2">
                        <button
                            wire:click="closeSignatureModal"
                            class="px-5 py-2.5 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            @click="submitSignature()"
                            :disabled="isEmpty"
                            class="px-5 py-2.5 text-xs font-bold text-white bg-[#070589] hover:bg-[#000060] rounded-xl transition-colors disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            Apply Signature
                        </button>
                    </div>
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
        doc.write(getPrintHTML(clone.innerHTML));
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

    function getPrintHTML(innerHTML) {
        return `
            <!DOCTYPE html>
            <html>
            <head>
                <title>ForeRent</title>
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

