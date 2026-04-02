<div class="space-y-5" x-data="{ showAllPenalties: false }">

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- ROW 1: QUICK STAT CARDS                                    --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

        {{-- Outstanding --}}
        <div class="bg-white rounded-2xl p-5 shadow-[0_1px_3px_rgba(0,0,0,0.04)] hover:shadow-[0_4px_12px_rgba(0,0,0,0.06)] transition-shadow duration-300">
            <div class="flex items-center gap-2.5 mb-3">
                <div class="w-9 h-9 rounded-xl bg-orange-50 flex items-center justify-center">
                    <svg class="w-[18px] h-[18px] text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Outstanding</p>
            </div>
            <p class="text-[22px] font-extrabold {{ $outstandingBalance > 0 ? 'text-orange-600' : 'text-gray-900' }} leading-none">
                <span class="text-sm font-bold text-gray-300 mr-0.5">&#8369;</span>{{ number_format($outstandingBalance, 2) }}
            </p>
            <p class="mt-2.5 text-[11px] font-medium text-gray-400">
                Due {{ $dueDate ? \Carbon\Carbon::parse($dueDate)->format('M d, Y') : 'N/A' }}
            </p>
        </div>

        {{-- Lease Expiry --}}
        <div class="bg-white rounded-2xl p-5 shadow-[0_1px_3px_rgba(0,0,0,0.04)] hover:shadow-[0_4px_12px_rgba(0,0,0,0.06)] transition-shadow duration-300">
            <div class="flex items-center gap-2.5 mb-3">
                <div class="w-9 h-9 rounded-xl bg-violet-50 flex items-center justify-center">
                    <svg class="w-[18px] h-[18px] text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Lease Expiry</p>
            </div>
            @if($lease && $daysUntilExpiry <= 0)
                <p class="text-[22px] font-extrabold leading-none text-red-600">Contract Ended</p>
                <p class="mt-2.5 text-[11px] font-medium text-gray-400">
                    Ended {{ \Carbon\Carbon::parse($leaseEndDate)->format('M d, Y') }}
                </p>
            @else
                <p class="text-[22px] font-extrabold leading-none
                    {{ $lease && $daysUntilExpiry <= 30 ? 'text-red-600' : ($lease && $daysUntilExpiry <= 60 ? 'text-amber-600' : 'text-gray-900') }}">
                    {{ $lease ? $daysUntilExpiry : '—' }}
                    <span class="text-sm font-bold text-gray-300 ml-0.5">days</span>
                </p>
                <p class="mt-2.5 text-[11px] font-medium text-gray-400">
                    @if($lease)
                        Ends {{ \Carbon\Carbon::parse($leaseEndDate)->format('M d, Y') }}
                    @else
                        No active lease
                    @endif
                </p>
            @endif
        </div>

        {{-- Maintenance --}}
        <div class="bg-white rounded-2xl p-5 shadow-[0_1px_3px_rgba(0,0,0,0.04)] hover:shadow-[0_4px_12px_rgba(0,0,0,0.06)] transition-shadow duration-300">
            <div class="flex items-center gap-2.5 mb-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center">
                    <svg class="w-[18px] h-[18px] text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Maintenance</p>
            </div>
            <p class="text-[22px] font-extrabold text-gray-900 leading-none">
                {{ $openMaintenanceCount }}
                <span class="text-sm font-bold text-gray-300 ml-0.5">open</span>
            </p>
            <div class="mt-2.5 flex items-center gap-3">
                <span class="text-[11px] font-medium text-amber-500">{{ $pendingMaintenanceCount }} pending</span>
                <span class="text-[11px] font-medium text-blue-500">{{ $ongoingMaintenanceCount }} ongoing</span>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- ROW 2: PAYMENT & BILLING (left) + SECURITY DEPOSIT (right) --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

        {{-- Payment & Billing Card (2/3 width — primary focus) --}}
        <div class="xl:col-span-2 bg-white rounded-2xl shadow-[0_1px_3px_rgba(0,0,0,0.04)] overflow-hidden">

            {{-- Amount Due Banner --}}
            <div class="relative overflow-hidden rounded-t-2xl bg-gradient-to-r from-blue-950 via-blue-800 to-blue-600">
                {{-- Decorative circles --}}
                <div class="absolute -right-10 -top-10 w-40 h-40 rounded-full bg-white/[0.06]"></div>
                <div class="absolute -right-5 top-8 w-28 h-28 rounded-full bg-white/[0.04]"></div>
                <div class="absolute left-1/3 -bottom-12 w-36 h-36 rounded-full bg-blue-400/[0.08]"></div>

                {{-- Banner content --}}
                <div class="relative z-10 px-4 sm:px-6 py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2.5 mb-1">
                            <div class="w-8 h-8 rounded-lg bg-white/15 backdrop-blur-sm flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <p class="text-[11px] font-semibold text-blue-200 uppercase tracking-widest">Amount Due This Month</p>
                        </div>
                        <p class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight mt-1">
                            <span class="text-xl font-bold text-white/50 mr-0.5">&#8369;</span>{{ number_format($amountDue, 2) }}
                        </p>
                        <div class="mt-2">
                            @if($paymentStatus === 'Paid')
                                <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-emerald-300">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    Paid in full
                                </span>
                            @elseif($paymentStatus === 'No Billing')
                                <span class="text-[11px] font-medium text-white/50">No billing</span>
                            @elseif($daysUntilDue > 0)
                                <span class="text-[11px] font-semibold text-amber-300">{{ $daysUntilDue }} {{ $daysUntilDue === 1 ? 'day' : 'days' }} left to pay</span>
                            @elseif($daysUntilDue == 0)
                                <span class="text-[11px] font-bold text-red-300 animate-pulse">Due today!</span>
                            @else
                                <span class="text-[11px] font-bold text-red-300">{{ abs($daysUntilDue) }} {{ abs($daysUntilDue) === 1 ? 'day' : 'days' }} overdue</span>
                            @endif
                        </div>
                    </div>
                    @if($paymentStatus !== 'No Billing')
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                            {{ $paymentStatus === 'Paid' ? 'bg-emerald-400/20 text-emerald-300 ring-1 ring-emerald-400/30' : '' }}
                            {{ $paymentStatus === 'Unpaid' ? 'bg-amber-400/20 text-amber-300 ring-1 ring-amber-400/30' : '' }}
                            {{ $paymentStatus === 'Overdue' ? 'bg-red-400/20 text-red-300 ring-1 ring-red-400/30' : '' }}">
                            <span class="w-1.5 h-1.5 rounded-full mr-1.5
                                {{ $paymentStatus === 'Paid' ? 'bg-emerald-400' : '' }}
                                {{ $paymentStatus === 'Unpaid' ? 'bg-amber-400' : '' }}
                                {{ $paymentStatus === 'Overdue' ? 'bg-red-400' : '' }}"></span>
                            {{ $paymentStatus }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Overdue warning --}}
            @if($daysUntilDue < 0 && $paymentStatus !== 'Paid')
                <div class="mx-5 mt-4 px-3.5 py-2.5 rounded-xl bg-red-50 flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    <p class="text-[11px] font-medium text-red-600">Your payment is {{ abs($daysUntilDue) }} {{ abs($daysUntilDue) === 1 ? 'day' : 'days' }} overdue. Pay now to avoid additional late fees.</p>
                </div>
            @endif

            {{-- Bottom stats --}}
            <div class="px-5 py-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="p-3.5 rounded-xl bg-[#F4F7FC]">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Outstanding</p>
                        <p class="text-base font-extrabold {{ $outstandingBalance > 0 ? 'text-orange-600' : 'text-gray-900' }}">
                            &#8369;{{ number_format($outstandingBalance, 2) }}
                        </p>
                    </div>
                    <div class="p-3.5 rounded-xl bg-[#F4F7FC]">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Due Date</p>
                        <p class="text-base font-extrabold text-gray-900">
                            {{ $dueDate ? \Carbon\Carbon::parse($dueDate)->format('M d, Y') : 'N/A' }}
                        </p>
                    </div>
                    <div class="p-3.5 rounded-xl bg-[#F4F7FC]">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Next Bill</p>
                        <p class="text-base font-extrabold text-gray-900">
                            {{ $nextPaymentDate ? \Carbon\Carbon::parse($nextPaymentDate)->format('M d, Y') : 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Security Deposit Card (1/3 width — secondary) --}}
        <div class="xl:col-span-1 bg-white rounded-2xl shadow-[0_1px_3px_rgba(0,0,0,0.04)] overflow-hidden">
            <div class="px-5 py-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center">
                        <svg class="w-[18px] h-[18px] text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <h3 class="text-[15px] font-bold text-gray-900">Security Deposit</h3>
                </div>
            </div>

            <div class="px-5 pb-5">
                {{-- Stacked deposit amounts --}}
                <div class="space-y-2 mb-4">
                    <div class="flex items-center justify-between p-3 rounded-xl bg-blue-50/70">
                        <span class="text-[10px] font-bold text-blue-500 uppercase tracking-wide">Held</span>
                        <span class="text-sm font-extrabold text-blue-700">&#8369;{{ number_format($securityDeposit, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-red-50/70">
                        <span class="text-[10px] font-bold text-red-400 uppercase tracking-wide">Deductions</span>
                        <span class="text-sm font-extrabold text-red-600">&#8369;{{ number_format($totalPenalties, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-emerald-50/70">
                        <span class="text-[10px] font-bold text-emerald-500 uppercase tracking-wide">Refundable</span>
                        <span class="text-sm font-extrabold text-emerald-600">&#8369;{{ number_format(max($securityDeposit - $totalPenalties, 0), 2) }}</span>
                    </div>
                </div>

                <div class="space-y-0 divide-y divide-gray-50">
                    <div class="flex items-center justify-between py-2">
                        <span class="text-[11px] text-gray-400">Advance Payment</span>
                        <span class="text-[11px] font-bold text-gray-800">&#8369;{{ number_format($advanceAmount, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-[11px] text-gray-400">Active Penalties</span>
                        <span class="text-[11px] font-bold {{ $totalPenalties > 0 ? 'text-red-500' : 'text-gray-800' }}">
                            &#8369;{{ number_format($totalPenalties, 2) }}
                        </span>
                    </div>
                </div>

                @if(count($activePenalties) > 0)
                    <div class="mt-3 pt-3 border-t border-gray-50">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Recent Charges</p>
                        <div class="space-y-1.5">
                            @foreach($activePenalties as $penalty)
                                <div class="flex items-center justify-between text-[11px]">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-1 h-1 rounded-full bg-red-400"></span>
                                        <span class="text-gray-500">{{ $penalty->description }}</span>
                                    </div>
                                    <span class="font-bold text-red-500">&#8369;{{ number_format($penalty->amount, 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- ROW 3: LEASE & CONTRACT + MOVE IN/OUT                      --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 xl:grid-cols-5 gap-5">

        {{-- Lease & Contract Card --}}
        <div class="xl:col-span-3 bg-white rounded-2xl shadow-[0_1px_3px_rgba(0,0,0,0.04)] overflow-hidden">
            <div class="px-5 py-4 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-violet-50 flex items-center justify-center">
                        <svg class="w-[18px] h-[18px] text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h3 class="text-[15px] font-bold text-gray-900">Lease & Contract</h3>
                </div>
                @if($lease)
                    <div class="flex items-center gap-1.5">
                        @if($isShortTerm)
                            <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-600 text-[10px] font-bold uppercase">Short-Term</span>
                        @endif
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
                            {{ $leaseStatus === 'Active' ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' }}">
                            <span class="w-1.5 h-1.5 rounded-full mr-1.5
                                {{ $leaseStatus === 'Active' ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                            {{ $leaseStatus }}
                        </span>
                    </div>
                @endif
            </div>

            @if($lease)
            <div class="px-5 pb-5">
                {{-- Expiry countdown --}}
                @php
                    $totalDays = \Carbon\Carbon::parse($lease->start_date)->diffInDays(\Carbon\Carbon::parse($leaseEndDate));
                    $elapsed = max(\Carbon\Carbon::parse($lease->start_date)->diffInDays(now()), 0);
                    $progress = $totalDays > 0 ? min(($elapsed / $totalDays) * 100, 100) : 0;
                @endphp
                <div class="mb-4 p-4 rounded-xl
                    {{ $daysUntilExpiry <= 30 ? 'bg-red-50/70' : ($daysUntilExpiry <= 60 ? 'bg-amber-50/70' : 'bg-[#F4F7FC]') }}">
                    <div class="flex items-center justify-between mb-2.5">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider
                                {{ $daysUntilExpiry <= 30 ? 'text-red-500' : ($daysUntilExpiry <= 60 ? 'text-amber-500' : 'text-blue-500') }}">
                                Days Until Lease Expiry
                            </p>
                            <p class="text-[11px] text-gray-400 mt-0.5">Ends {{ \Carbon\Carbon::parse($leaseEndDate)->format('M d, Y') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-3xl font-extrabold
                                {{ $daysUntilExpiry <= 30 ? 'text-red-600' : ($daysUntilExpiry <= 60 ? 'text-amber-600' : 'text-gray-900') }}">
                                {{ max($daysUntilExpiry, 0) }}
                            </p>
                            <p class="text-[10px] font-medium text-gray-400">days</p>
                        </div>
                    </div>
                    <div class="w-full bg-white/80 rounded-full h-1.5 overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500
                            {{ $daysUntilExpiry <= 30 ? 'bg-red-400' : ($daysUntilExpiry <= 60 ? 'bg-amber-400' : 'bg-blue-400') }}"
                            style="width: {{ $progress }}%"></div>
                    </div>
                    <div class="flex justify-between mt-1">
                        <span class="text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($lease->start_date)->format('M d, Y') }}</span>
                        <span class="text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($leaseEndDate)->format('M d, Y') }}</span>
                    </div>
                </div>

                {{-- Contract Details --}}
                <div class="grid grid-cols-2 gap-2.5">
                    <div class="p-3 rounded-xl bg-[#F4F7FC]">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Contract Type</p>
                        <p class="text-sm font-bold text-gray-900">{{ $isShortTerm ? 'Short-Term' : 'Long-Term' }}</p>
                        <p class="text-[10px] text-gray-400">{{ $leaseTerm }} {{ $leaseTerm === 1 ? 'month' : 'months' }}</p>
                    </div>
                    <div class="p-3 rounded-xl bg-[#F4F7FC]">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Monthly Rate</p>
                        <p class="text-sm font-bold text-gray-900">&#8369;{{ number_format($contractRate, 2) }}</p>
                    </div>
                    <div class="p-3 rounded-xl bg-[#F4F7FC]">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Shift</p>
                        <p class="text-sm font-bold text-gray-900">{{ $lease->shift }}</p>
                    </div>
                    <div class="p-3 rounded-xl bg-[#F4F7FC]">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Auto-Renewal</p>
                        <span class="inline-flex items-center gap-1.5 text-sm font-bold {{ $autoRenew ? 'text-emerald-600' : 'text-gray-400' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $autoRenew ? 'bg-emerald-500' : 'bg-gray-300' }}"></span>
                            {{ $autoRenew ? 'Enabled' : 'Disabled' }}
                        </span>
                    </div>
                </div>
            </div>
            @else
                <div class="p-6 text-center py-10">
                    <p class="text-sm text-gray-400">No active lease found</p>
                </div>
            @endif
        </div>

        {{-- Move-In / Move-Out Card --}}
        <div class="xl:col-span-2 bg-white rounded-2xl shadow-[0_1px_3px_rgba(0,0,0,0.04)] overflow-hidden">
            <div class="px-5 py-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-cyan-50 flex items-center justify-center">
                        <svg class="w-[18px] h-[18px] text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    </div>
                    <h3 class="text-[15px] font-bold text-gray-900">Move-In / Move-Out</h3>
                </div>
            </div>

            <div class="px-5 pb-5 space-y-2.5">
                {{-- Move-in --}}
                <div class="p-3.5 rounded-xl bg-emerald-50/50 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-bold text-emerald-500 uppercase tracking-wider">Move-In Date</p>
                        <p class="text-[15px] font-bold text-gray-900 mt-0.5">
                            {{ $moveInDate ? \Carbon\Carbon::parse($moveInDate)->format('M d, Y') : 'Not set' }}
                        </p>
                    </div>
                    <div class="w-8 h-8 rounded-full bg-emerald-100/80 flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    </div>
                </div>

                {{-- Move-out --}}
                <div class="p-3.5 rounded-xl {{ $moveOutDate ? 'bg-red-50/50' : 'bg-gray-50/50' }} flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-bold {{ $moveOutDate ? 'text-red-400' : 'text-gray-400' }} uppercase tracking-wider">Move-Out Date</p>
                        <p class="text-[15px] font-bold text-gray-900 mt-0.5">
                            {{ $moveOutDate ? \Carbon\Carbon::parse($moveOutDate)->format('M d, Y') : 'N/A' }}
                        </p>
                    </div>
                    <div class="w-8 h-8 rounded-full {{ $moveOutDate ? 'bg-red-100/80' : 'bg-gray-100' }} flex items-center justify-center">
                        <svg class="w-4 h-4 {{ $moveOutDate ? 'text-red-400' : 'text-gray-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </div>
                </div>

                {{-- Clearance --}}
                @if($moveOutDate)
                    <div class="pt-2">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2.5">Clearance Checklist</p>
                        <div class="space-y-2">
                            <div class="flex items-center gap-2.5 text-[11px]">
                                <span class="w-4 h-4 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-2.5 h-2.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                </span>
                                <span class="text-gray-600">Documents returned</span>
                            </div>
                            <div class="flex items-center gap-2.5 text-[11px]">
                                <span class="w-4 h-4 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>
                                </span>
                                <span class="text-gray-400">Bills settled</span>
                            </div>
                            <div class="flex items-center gap-2.5 text-[11px]">
                                <span class="w-4 h-4 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>
                                </span>
                                <span class="text-gray-400">Room inspection done</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- ROW 4: INSPECTION & CONTRACT (Tabbed)                      --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    @if($lease)
    <div class="bg-white rounded-2xl shadow-[0_1px_3px_rgba(0,0,0,0.04)] overflow-hidden" x-data="{ activeTab: 'movein' }" wire:ignore.self>

        <div class="px-5 py-4 flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center">
                    <svg class="w-[18px] h-[18px] text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                </div>
                <h3 class="text-[15px] font-bold text-gray-900">Inspection & Contract</h3>
            </div>

            {{-- Tab Pills --}}
            <div class="flex items-center gap-1 bg-[#F4F7FC] rounded-xl p-1">
                <button @click="activeTab = 'movein'"
                        :class="activeTab === 'movein' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-400 hover:text-gray-600'"
                        class="px-4 py-1.5 rounded-lg text-[11px] font-bold transition-all duration-200">
                    Move-In
                </button>
                <button @click="{{ $moveOutDate ? "activeTab = 'moveout'" : '' }}"
                        :class="activeTab === 'moveout' ? 'bg-white text-gray-900 shadow-sm' : '{{ $moveOutDate ? 'text-gray-400 hover:text-gray-600' : 'text-gray-300 cursor-not-allowed' }}'"
                        class="px-4 py-1.5 rounded-lg text-[11px] font-bold transition-all duration-200"
                        {{ !$moveOutDate ? 'disabled' : '' }}>
                    Move-Out
                    @if(!$moveOutDate)
                        <span class="ml-1 text-[9px] opacity-50">(N/A)</span>
                    @endif
                </button>
            </div>

            {{-- Dynamic Status Badge --}}
            <div>
                <template x-if="activeTab === 'movein'">
                    <span>
                        @if($contractAgreed)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-600 text-[10px] font-bold uppercase tracking-wider">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>Signed
                            </span>
                        @elseif($ownerSignature && !$tenantSignature)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-amber-50 text-amber-600 text-[10px] font-bold uppercase tracking-wider animate-pulse">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5"></span>Action Needed
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-gray-50 text-gray-400 text-[10px] font-bold uppercase tracking-wider">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-300 mr-1.5"></span>Pending
                            </span>
                        @endif
                    </span>
                </template>
                @if($moveOutDate)
                <template x-if="activeTab === 'moveout'">
                    <span>
                        @if($moveOutContractAgreed)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-600 text-[10px] font-bold uppercase tracking-wider">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>Signed
                            </span>
                        @elseif(count($moveOutChecklist) > 0)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-600 text-[10px] font-bold uppercase tracking-wider">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>Inspected
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-gray-50 text-gray-400 text-[10px] font-bold uppercase tracking-wider">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-300 mr-1.5"></span>Pending
                            </span>
                        @endif
                    </span>
                </template>
                @endif
            </div>
        </div>

        {{-- ══════ MOVE-IN TAB ══════ --}}
        <div x-show="activeTab === 'movein'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="grid grid-cols-1 lg:grid-cols-2 border-t border-gray-50">

                <div class="p-5 lg:border-r border-gray-50">
                    <h4 class="text-[10px] font-bold text-indigo-500 uppercase tracking-wider mb-4">Contract & Signature</h4>

                    <div class="rounded-xl bg-[#F4F7FC] p-3.5 mb-4 space-y-2">
                        <div class="flex justify-between text-[11px]">
                            <span class="text-gray-400">Property</span>
                            <span class="font-bold text-gray-700">{{ $contractData['property'] ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between text-[11px]">
                            <span class="text-gray-400">Unit / Bed</span>
                            <span class="font-bold text-gray-700">{{ $contractData['unit'] ?? '—' }} / {{ $contractData['bed'] ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between text-[11px]">
                            <span class="text-gray-400">Lease Period</span>
                            <span class="font-bold text-gray-700">{{ $contractData['start_date'] ?? '—' }} — {{ $contractData['end_date'] ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between text-[11px]">
                            <span class="text-gray-400">Monthly Rate</span>
                            <span class="font-extrabold text-gray-900">&#8369;{{ number_format($contractData['monthly_rate'] ?? 0, 2) }}</span>
                        </div>
                    </div>

                    {{-- Signatures --}}
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center justify-between p-2.5 rounded-xl {{ $ownerSignature ? 'bg-emerald-50/50' : 'bg-gray-50/50' }}">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg {{ $ownerSignature ? 'bg-emerald-100' : 'bg-gray-100' }} flex items-center justify-center">
                                    @if($ownerSignature)
                                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    @else
                                        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold {{ $ownerSignature ? 'text-emerald-600' : 'text-gray-400' }}">Lessor / Manager</p>
                                    <p class="text-[9px] {{ $ownerSignature ? 'text-emerald-500' : 'text-gray-300' }}">{{ $ownerSignature ? 'Signed: ' . $ownerSignedAt : 'Awaiting signature' }}</p>
                                </div>
                            </div>
                            @if($ownerSignature)<img src="{{ asset('storage/' . $ownerSignature) }}" class="h-7 object-contain" alt="Signature">@endif
                        </div>

                        <div class="flex items-center justify-between p-2.5 rounded-xl {{ $tenantSignature ? 'bg-emerald-50/50' : 'bg-blue-50/30' }}">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg {{ $tenantSignature ? 'bg-emerald-100' : 'bg-blue-100' }} flex items-center justify-center">
                                    @if($tenantSignature)
                                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    @else
                                        <svg class="w-3.5 h-3.5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold {{ $tenantSignature ? 'text-emerald-600' : 'text-blue-600' }}">Your Signature</p>
                                    <p class="text-[9px] {{ $tenantSignature ? 'text-emerald-500' : 'text-blue-400' }}">{{ $tenantSignature ? 'Signed: ' . $tenantSignedAt : 'Your signature is required' }}</p>
                                </div>
                            </div>
                            @if($tenantSignature)<img src="{{ asset('storage/' . $tenantSignature) }}" class="h-7 object-contain" alt="Signature">@endif
                        </div>
                    </div>

                    <button wire:click="toggleContract" class="w-full py-2.5 px-4 bg-primary hover:bg-primary/90 text-white font-bold rounded-xl text-[11px] transition-colors flex items-center justify-center gap-2">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        {{ !$tenantSignature && $ownerSignature ? 'Read & Sign Contract' : 'View Contract' }}
                    </button>

                    @if($contractAgreed)
                        <div class="text-center py-2 px-3 bg-emerald-50/50 rounded-xl mt-3">
                            <p class="text-[11px] font-bold text-emerald-600">Contract Fully Signed</p>
                            <p class="text-[9px] text-emerald-400 mt-0.5">Both parties have signed electronically per RA 8792.</p>
                        </div>
                    @elseif(!$tenantSignature && !$ownerSignature)
                        <div class="text-center py-2 px-3 bg-gray-50/50 rounded-xl mt-3">
                            <p class="text-[10px] text-gray-400">Waiting for the lessor/manager to sign first.</p>
                        </div>
                    @endif
                </div>

                <div class="p-5">
                    <x-inspection.items-confirmation-card
                        title="Items Received"
                        subtitle="Confirm the items you received at move-in"
                        :items="$itemsReceived"
                        :allConfirmed="$itemsConfirmedByTenant"
                        accentColor="indigo"
                        wireConfirmMethod="confirmItemReceived"
                        wireConfirmAllMethod="confirmAllItems"
                        emptyTitle="No inspection data yet"
                        emptyMessage="Items will appear here after the manager records the move-in inspection."
                        :embedded="true"
                    />
                </div>
            </div>
        </div>

        {{-- ══════ MOVE-OUT TAB ══════ --}}
        @if($moveOutDate)
        <div x-show="activeTab === 'moveout'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="grid grid-cols-1 lg:grid-cols-2 border-t border-gray-50">

                <div class="p-5 lg:border-r border-gray-50">
                    <h4 class="text-[10px] font-bold text-indigo-500 uppercase tracking-wider mb-4">Clearance & Settlement</h4>

                    <div class="rounded-xl bg-[#F4F7FC] p-3.5 mb-4 space-y-2">
                        <div class="flex justify-between text-[11px]">
                            <span class="text-gray-400">Move-Out Date</span>
                            <span class="font-bold text-gray-700">{{ \Carbon\Carbon::parse($moveOutDate)->format('M d, Y') }}</span>
                        </div>
                        <div class="flex justify-between text-[11px]">
                            <span class="text-gray-400">Security Deposit</span>
                            <span class="font-extrabold text-gray-900">&#8369;{{ number_format($securityDeposit, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-[11px]">
                            <span class="text-gray-400">Inspection Status</span>
                            <span class="font-bold {{ count($moveOutChecklist) > 0 ? 'text-emerald-600' : 'text-amber-600' }}">
                                {{ count($moveOutChecklist) > 0 ? 'Completed' : 'Awaiting inspection' }}
                            </span>
                        </div>
                    </div>

                    {{-- Move-Out Signatures --}}
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center justify-between p-2.5 rounded-xl {{ $moveOutOwnerSignature ? 'bg-emerald-50/50' : 'bg-gray-50/50' }}">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg {{ $moveOutOwnerSignature ? 'bg-emerald-100' : 'bg-gray-100' }} flex items-center justify-center">
                                    @if($moveOutOwnerSignature)
                                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    @else
                                        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold {{ $moveOutOwnerSignature ? 'text-emerald-600' : 'text-gray-400' }}">Lessor / Manager</p>
                                    <p class="text-[9px] {{ $moveOutOwnerSignature ? 'text-emerald-500' : 'text-gray-300' }}">{{ $moveOutOwnerSignature ? 'Signed: ' . $moveOutOwnerSignedAt : 'Awaiting signature' }}</p>
                                </div>
                            </div>
                            @if($moveOutOwnerSignature)<img src="{{ asset('storage/' . $moveOutOwnerSignature) }}" class="h-7 object-contain" alt="Signature">@endif
                        </div>

                        <div class="flex items-center justify-between p-2.5 rounded-xl {{ $moveOutTenantSignature ? 'bg-emerald-50/50' : 'bg-blue-50/30' }}">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg {{ $moveOutTenantSignature ? 'bg-emerald-100' : 'bg-blue-100' }} flex items-center justify-center">
                                    @if($moveOutTenantSignature)
                                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    @else
                                        <svg class="w-3.5 h-3.5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold {{ $moveOutTenantSignature ? 'text-emerald-600' : 'text-blue-600' }}">Your Signature</p>
                                    <p class="text-[9px] {{ $moveOutTenantSignature ? 'text-emerald-500' : 'text-blue-400' }}">{{ $moveOutTenantSignature ? 'Signed: ' . $moveOutTenantSignedAt : 'Your signature is required' }}</p>
                                </div>
                            </div>
                            @if($moveOutTenantSignature)<img src="{{ asset('storage/' . $moveOutTenantSignature) }}" class="h-7 object-contain" alt="Signature">@endif
                        </div>
                    </div>

                    <button wire:click="toggleMoveOutContract" class="w-full py-2.5 px-4 bg-primary hover:bg-primary/90 text-white font-bold rounded-xl text-[11px] transition-colors flex items-center justify-center gap-2">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        View Move-Out Contract
                    </button>

                    @if($moveOutContractAgreed)
                        <div class="text-center py-2 px-3 bg-emerald-50/50 rounded-xl mt-3">
                            <p class="text-[11px] font-bold text-emerald-600">Move-Out Contract Fully Signed</p>
                            <p class="text-[9px] text-emerald-400 mt-0.5">Both parties have signed electronically per RA 8792.</p>
                        </div>
                    @endif
                </div>

                <div class="p-5">
                    <x-inspection.items-confirmation-card
                        title="Items Returned"
                        subtitle="Confirm the items you've returned at move-out"
                        :items="$itemsReturned"
                        :allConfirmed="$itemsReturnedConfirmedByTenant"
                        accentColor="orange"
                        wireConfirmMethod="confirmItemReturned"
                        wireConfirmAllMethod="confirmAllReturned"
                        emptyTitle="No move-out inspection data yet"
                        emptyMessage="Items will appear here after the manager records the move-out inspection."
                        :embedded="true"
                    />
                </div>
            </div>
        </div>
        @endif

    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- MODALS (unchanged functionality)                           --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    @if($showMoveOutContract && $lease)
        @php
            $t = $tenantContractData;
            $deposit = $t['move_in_details']['security_deposit'];
        @endphp
        <x-inspection.contract-viewer-modal
            :show="true"
            title="Move-Out Clearance & Deposit Settlement"
            wireCloseMethod="toggleMoveOutContract"
            contractId="move-out-contract-tenant"
        >
            @include('partials.move-out-contract-body', [
                't' => $t,
                'deposit' => $deposit,
                'moveOutChecklist' => $moveOutChecklist,
                'itemsReturned' => $itemsReturned,
                'inspectionChecklist' => $moveOutInspectionChecklist,
                'moveOutTenantSignature' => $moveOutTenantSignature,
                'moveOutOwnerSignature' => $moveOutOwnerSignature,
                'moveOutTenantSignedAt' => $moveOutTenantSignedAt,
                'moveOutOwnerSignedAt' => $moveOutOwnerSignedAt,
                'moveOutContractAgreed' => $moveOutContractAgreed,
                'signatureMode' => 'tenant',
            ])

            <x-slot:footer>
                @if(!$moveOutTenantSignature && $moveOutOwnerSignature)
                    <button wire:click="openMoveOutSignatureModal" class="bg-primary hover:bg-primary/90 text-white font-bold py-2.5 px-6 rounded-xl text-sm transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                        Sign Move-Out Contract
                    </button>
                @endif
                <button @click="$el.closest('.fixed').style.display='none'; $wire.toggleMoveOutContract()" class="px-5 py-2.5 text-sm font-semibold text-gray-500 bg-gray-100 hover:bg-gray-200 rounded-xl">Close</button>
            </x-slot:footer>
        </x-inspection.contract-viewer-modal>
    @endif

    @if($showContract && $lease)
        @php
            $t = $tenantContractData;
            $rate = $t['move_in_details']['monthly_rate'];
            $deposit = $t['move_in_details']['security_deposit'];
            $premium = $t['move_in_details']['short_term_premium'] ?? 0;
            $dueDay = $t['move_in_details']['monthly_due_date'];
            $dueSfx = match((int) $dueDay) { 1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th' };
            $totalMoveIn = $rate + $deposit;
        @endphp
        <x-inspection.contract-viewer-modal
            :show="true"
            title="Move-In Contract"
            wireCloseMethod="toggleContract"
            contractId="move-in-contract-tenant"
        >
            @include('partials.move-in-contract-body', [
                't' => $t,
                'rate' => $rate,
                'deposit' => $deposit,
                'premium' => $premium,
                'dueDay' => $dueDay,
                'dueSfx' => $dueSfx,
                'totalMoveIn' => $totalMoveIn,
                'inspectionChecklist' => $itemsReceived ? [] : [],
                'itemsReceived' => $itemsReceived,
                'tenantSignature' => $tenantSignature,
                'ownerSignature' => $ownerSignature,
                'tenantSignedAt' => $tenantSignedAt,
                'ownerSignedAt' => $ownerSignedAt,
                'contractAgreed' => $contractAgreed,
                'signatureMode' => 'tenant',
            ])

            <x-slot:footer>
                @if(!$tenantSignature && $ownerSignature)
                    <button wire:click="openSignatureModal" class="bg-primary hover:bg-primary/90 text-white font-bold py-2.5 px-6 rounded-xl text-sm transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                        Read & Sign Contract
                    </button>
                @endif
                <button @click="$el.closest('.fixed').style.display='none'; $wire.toggleContract()" class="bg-primary hover:bg-primary/90 text-white font-bold py-2.5 px-6 rounded-xl text-sm">Close</button>
            </x-slot:footer>
        </x-inspection.contract-viewer-modal>
    @endif

    <x-inspection.signature-pad-modal
        :show="$showSignatureModal"
        title="Sign Your Contract"
        subtitle="Draw your signature below using your mouse or finger"
        signerName=""
        signerRole="Lessee / Tenant"
        legalText="By clicking &quot;Apply Signature&quot;, I confirm that I have read and agree to all terms. This e-signature is legally binding under RA 8792."
        wireCloseMethod="closeSignatureModal"
        wireSaveMethod="saveTenantSignature"
        canvasRef="sigCanvasMoveIn"
    />

    <x-inspection.signature-pad-modal
        :show="$showMoveOutSignatureModal"
        title="Sign Move-Out Contract"
        subtitle="Draw your signature below using your mouse or finger"
        signerName=""
        signerRole="Lessee / Tenant"
        legalText="By clicking &quot;Apply Signature&quot;, I confirm that I have read and agree to all terms in this Move-Out Clearance &amp; Deposit Settlement Agreement. This e-signature is legally binding under RA 8792."
        wireCloseMethod="closeMoveOutSignatureModal"
        wireSaveMethod="saveMoveOutTenantSignature"
        canvasRef="sigCanvasMoveOut"
    />

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- ROW 5: MAINTENANCE REQUESTS                                 --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-2xl shadow-[0_1px_3px_rgba(0,0,0,0.04)] overflow-hidden">
        <div class="px-5 py-4 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-orange-50 flex items-center justify-center">
                    <svg class="w-[18px] h-[18px] text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <h3 class="text-[15px] font-bold text-gray-900">Maintenance Requests</h3>
            </div>
            <div class="flex items-center gap-2">
                @if($openMaintenanceCount > 0)
                    <span class="px-2.5 py-1 rounded-full bg-orange-50 text-orange-600 text-[10px] font-bold uppercase">
                        {{ $openMaintenanceCount }} Open
                    </span>
                @endif
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-50/70 text-[10px] font-semibold text-amber-600">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>{{ $pendingMaintenanceCount }} Pending
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-blue-50/70 text-[10px] font-semibold text-blue-600">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>{{ $ongoingMaintenanceCount }} In Progress
                </span>
            </div>
        </div>

        <div class="px-5 pb-5">
            @if(count($recentRequests) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                    @foreach($recentRequests as $request)
                        <div class="p-3.5 rounded-xl bg-[#F4F7FC] hover:bg-[#EDF1F9] transition-colors duration-200">
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <p class="text-[11px] font-bold text-gray-800 truncate flex-1">{{ $request->problem }}</p>
                                <span class="flex-shrink-0 px-2 py-0.5 rounded-full text-[9px] font-bold
                                    {{ $request->status === 'Pending' ? 'bg-amber-100 text-amber-600' : '' }}
                                    {{ $request->status === 'Ongoing' ? 'bg-blue-100 text-blue-600' : '' }}
                                    {{ $request->status === 'Completed' ? 'bg-emerald-100 text-emerald-600' : '' }}
                                ">{{ $request->status }}</span>
                            </div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-[10px] text-gray-400">{{ $request->category }}</span>
                                <span class="text-[10px] text-gray-300">&bull;</span>
                                <span class="text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($request->log_date)->format('M d') }}</span>
                                <span class="text-[10px] text-gray-300">&bull;</span>
                                <span class="text-[10px] font-semibold
                                    {{ $request->urgency === 'Level 4' ? 'text-red-500' : '' }}
                                    {{ $request->urgency === 'Level 3' ? 'text-orange-500' : '' }}
                                    {{ $request->urgency === 'Level 2' ? 'text-amber-500' : '' }}
                                    {{ $request->urgency === 'Level 1' ? 'text-gray-400' : '' }}
                                ">{{ $request->urgency }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <div class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center mx-auto mb-2">
                        <svg class="w-5 h-5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    </div>
                    <p class="text-[11px] font-semibold text-gray-600">All clear!</p>
                    <p class="text-[10px] text-gray-400 mt-0.5">No maintenance requests at the moment</p>
                </div>
            @endif
        </div>
    </div>
</div>
