<div class="space-y-6" x-data="{ showAllPenalties: false }">

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- ROW 1: PAYMENT & BILLING  +  BILLING BREAKDOWN            --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- Payment & Billing Card --}}
        <div class="xl:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Payment & Billing</h3>
                </div>
                @if($paymentStatus !== 'No Billing')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide
                        {{ $paymentStatus === 'Paid' ? 'bg-emerald-50 text-emerald-700' : '' }}
                        {{ $paymentStatus === 'Unpaid' ? 'bg-amber-50 text-amber-700' : '' }}
                        {{ $paymentStatus === 'Overdue' ? 'bg-red-50 text-red-700' : '' }}">
                        <span class="w-1.5 h-1.5 rounded-full mr-2
                            {{ $paymentStatus === 'Paid' ? 'bg-emerald-500' : '' }}
                            {{ $paymentStatus === 'Unpaid' ? 'bg-amber-500' : '' }}
                            {{ $paymentStatus === 'Overdue' ? 'bg-red-500' : '' }}"></span>
                        {{ $paymentStatus }}
                    </span>
                @endif
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Amount Due --}}
                    <div class="relative">
                        <p class="text-sm font-medium text-gray-500 mb-1">Amount Due This Month</p>
                        <p class="text-4xl font-extrabold text-gray-900">
                            <span class="text-xl align-top text-gray-500">&#8369;</span>{{ number_format($amountDue, 2) }}
                        </p>
                        @if($dueDate)
                            <div class="mt-3 flex items-center gap-2">
                                @if($paymentStatus === 'Paid')
                                    <span class="inline-flex items-center gap-1.5 text-sm text-emerald-600 font-medium">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        Paid in full
                                    </span>
                                @elseif($daysUntilDue > 0)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-sm font-semibold
                                        {{ $daysUntilDue <= 3 ? 'bg-red-50 text-red-700' : ($daysUntilDue <= 7 ? 'bg-amber-50 text-amber-700' : 'bg-blue-50 text-blue-700') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ $daysUntilDue }} {{ $daysUntilDue === 1 ? 'day' : 'days' }} left
                                    </span>
                                @elseif($daysUntilDue == 0)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-red-50 text-red-700 text-sm font-semibold animate-pulse">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                        Due today!
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-red-100 text-red-800 text-sm font-bold">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                        {{ abs($daysUntilDue) }} {{ abs($daysUntilDue) === 1 ? 'day' : 'days' }} overdue
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Right column stats --}}
                    <div class="space-y-4">
                        {{-- Outstanding Balance --}}
                        <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">Outstanding Balance</p>
                                    <p class="text-lg font-bold {{ $outstandingBalance > 0 ? 'text-orange-600' : 'text-gray-900' }}">
                                        &#8369;{{ number_format($outstandingBalance, 2) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Next Payment Due --}}
                        <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">Due Date</p>
                                    <p class="text-sm font-bold text-gray-900">
                                        {{ $dueDate ? \Carbon\Carbon::parse($dueDate)->format('M d, Y') : 'N/A' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Next Billing --}}
                        @if($nextPaymentDate)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">Next Billing Date</p>
                                    <p class="text-sm font-bold text-gray-900">{{ \Carbon\Carbon::parse($nextPaymentDate)->format('M d, Y') }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Billing Breakdown Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Billing Breakdown</h3>
                        @if($currentBilling)
                            <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($currentBilling->billing_date)->format('F Y') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="p-6">
                @if($billingItems && count($billingItems) > 0)
                    <div class="space-y-3">
                        @foreach($billingItems as $item)
                            <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full
                                        {{ $item->charge_category === 'recurring' ? 'bg-blue-400' : '' }}
                                        {{ $item->charge_category === 'conditional' ? 'bg-amber-400' : '' }}
                                        {{ $item->charge_category === 'move_in' ? 'bg-emerald-400' : '' }}
                                        {{ $item->charge_category === 'move_out' ? 'bg-red-400' : '' }}
                                    "></span>
                                    <span class="text-sm text-gray-700">{{ $item->description }}</span>
                                </div>
                                <span class="text-sm font-semibold {{ $item->charge_category === 'conditional' ? 'text-red-600' : 'text-gray-900' }}">
                                    &#8369;{{ number_format($item->amount, 2) }}
                                </span>
                            </div>
                        @endforeach

                        @if($currentBilling && $currentBilling->previous_balance > 0)
                            <div class="flex items-center justify-between py-2 border-t border-gray-100">
                                <span class="text-sm text-gray-500 italic">Previous Balance</span>
                                <span class="text-sm font-semibold text-orange-600">&#8369;{{ number_format($currentBilling->previous_balance, 2) }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="mt-4 pt-4 border-t-2 border-gray-900 flex items-center justify-between">
                        <span class="text-base font-bold text-gray-900">Total Amount Due</span>
                        <span class="text-xl font-extrabold text-blue-700">&#8369;{{ number_format($amountDue, 2) }}</span>
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <p class="text-sm text-gray-500">No billing items yet</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- ROW 2: UTILITY BREAKDOWN                                  --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 gap-6">

        {{-- Utility Split Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-yellow-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Utility Split</h3>
                </div>
                @if($tenantCount > 0)
                    <span class="px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-bold">{{ $tenantCount }} TENANTS</span>
                @endif
            </div>

            <div class="p-6 space-y-4">
                {{-- Electricity --}}
                <div class="p-4 rounded-xl bg-gradient-to-br from-amber-50 to-yellow-50 border border-amber-100">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span class="text-xs font-bold text-amber-700 uppercase tracking-wide">Electricity (Meralco)</span>
                    </div>
                    <p class="text-2xl font-extrabold text-gray-900">&#8369;{{ number_format($electricityShare, 2) }}</p>
                    @if($electricityTotal > 0)
                        <p class="text-xs text-gray-500 mt-1">
                            Total: &#8369;{{ number_format($electricityTotal, 2) }} &divide; {{ $tenantCount }}
                        </p>
                    @endif
                </div>

                {{-- Water --}}
                <div class="p-4 rounded-xl bg-gradient-to-br from-sky-50 to-blue-50 border border-sky-100">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3c-4 6-7 8.5-7 12a7 7 0 1014 0c0-3.5-3-6-7-12z"/></svg>
                        <span class="text-xs font-bold text-sky-700 uppercase tracking-wide">Water</span>
                    </div>
                    <p class="text-2xl font-extrabold text-gray-900">&#8369;{{ number_format($waterShare, 2) }}</p>
                    @if($waterTotal > 0)
                        <p class="text-xs text-gray-500 mt-1">
                            Total: &#8369;{{ number_format($waterTotal, 2) }} &divide; {{ $tenantCount }}
                        </p>
                    @endif
                </div>

                @if($billingPeriod)
                    <p class="text-xs text-center text-gray-400 pt-1">Billing Period: {{ $billingPeriod }}</p>
                @endif
            </div>
        </div>

    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- ROW 3: SECURITY DEPOSIT  +  LEASE & CONTRACT              --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        {{-- Security Deposit Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Security Deposit</h3>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-3 gap-3 mb-6">
                    {{-- Held --}}
                    <div class="text-center p-4 rounded-xl bg-blue-50 border border-blue-100">
                        <p class="text-xs font-bold text-blue-600 uppercase tracking-wide mb-2">Held</p>
                        <p class="text-2xl font-extrabold text-blue-800">&#8369;{{ number_format($securityDeposit, 2) }}</p>
                    </div>
                    {{-- Deductions --}}
                    <div class="text-center p-4 rounded-xl bg-red-50 border border-red-100">
                        <p class="text-xs font-bold text-red-600 uppercase tracking-wide mb-2">Deductions</p>
                        <p class="text-2xl font-extrabold text-red-700">&#8369;{{ number_format($totalPenalties, 2) }}</p>
                    </div>
                    {{-- Refundable --}}
                    <div class="text-center p-4 rounded-xl bg-emerald-50 border border-emerald-100">
                        <p class="text-xs font-bold text-emerald-600 uppercase tracking-wide mb-2">Refundable</p>
                        <p class="text-2xl font-extrabold text-emerald-700">&#8369;{{ number_format(max($securityDeposit - $totalPenalties, 0), 2) }}</p>
                    </div>
                </div>

                {{-- Deposit details --}}
                <div class="space-y-3">
                    <div class="flex items-center justify-between py-2 border-b border-gray-50">
                        <span class="text-sm text-gray-600">Advance Payment</span>
                        <span class="text-sm font-semibold text-gray-900">&#8369;{{ number_format($advanceAmount, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-gray-50">
                        <span class="text-sm text-gray-600">Active Penalties</span>
                        <span class="text-sm font-semibold {{ $totalPenalties > 0 ? 'text-red-600' : 'text-gray-900' }}">
                            &#8369;{{ number_format($totalPenalties, 2) }}
                        </span>
                    </div>
                </div>

                {{-- Active penalties list --}}
                @if(count($activePenalties) > 0)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">Recent Charges</p>
                        <div class="space-y-2">
                            @foreach($activePenalties as $penalty)
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>
                                        <span class="text-gray-700">{{ $penalty->description }}</span>
                                    </div>
                                    <span class="font-semibold text-red-600">&#8369;{{ number_format($penalty->amount, 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Lease & Contract Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-violet-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Lease & Contract</h3>
                </div>
                @if($lease)
                    <div class="flex items-center gap-2">
                        @if($isShortTerm)
                            <span class="px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 text-xs font-bold">SHORT-TERM</span>
                        @endif
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide
                            {{ $leaseStatus === 'Active' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                            <span class="w-1.5 h-1.5 rounded-full mr-2
                                {{ $leaseStatus === 'Active' ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                            {{ $leaseStatus }}
                        </span>
                    </div>
                @endif
            </div>

            @if($lease)
            <div class="p-6">
                {{-- Expiry Countdown --}}
                <div class="mb-6 p-4 rounded-xl
                    {{ $daysUntilExpiry <= 30 ? 'bg-red-50 border border-red-100' : ($daysUntilExpiry <= 60 ? 'bg-amber-50 border border-amber-100' : 'bg-blue-50 border border-blue-100') }}">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide
                                {{ $daysUntilExpiry <= 30 ? 'text-red-600' : ($daysUntilExpiry <= 60 ? 'text-amber-600' : 'text-blue-600') }}">
                                Days Until Lease Expiry
                            </p>
                            <p class="text-sm text-gray-600 mt-0.5">
                                Ends {{ \Carbon\Carbon::parse($leaseEndDate)->format('M d, Y') }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-4xl font-extrabold
                                {{ $daysUntilExpiry <= 30 ? 'text-red-700' : ($daysUntilExpiry <= 60 ? 'text-amber-700' : 'text-blue-700') }}">
                                {{ max($daysUntilExpiry, 0) }}
                            </p>
                            <p class="text-xs font-medium text-gray-500">days</p>
                        </div>
                    </div>
                    {{-- Progress bar --}}
                    @php
                        $totalDays = \Carbon\Carbon::parse($lease->start_date)->diffInDays(\Carbon\Carbon::parse($leaseEndDate));
                        $elapsed = max(\Carbon\Carbon::parse($lease->start_date)->diffInDays(now()), 0);
                        $progress = $totalDays > 0 ? min(($elapsed / $totalDays) * 100, 100) : 0;
                    @endphp
                    <div class="mt-3 w-full bg-white/60 rounded-full h-2 overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500
                            {{ $daysUntilExpiry <= 30 ? 'bg-red-500' : ($daysUntilExpiry <= 60 ? 'bg-amber-500' : 'bg-blue-500') }}"
                            style="width: {{ $progress }}%"></div>
                    </div>
                    <div class="flex justify-between mt-1">
                        <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($lease->start_date)->format('M d, Y') }}</span>
                        <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($leaseEndDate)->format('M d, Y') }}</span>
                    </div>
                </div>

                {{-- Contract Details --}}
                <div class="space-y-3">
                    <div class="flex items-center justify-between py-2.5 border-b border-gray-50">
                        <span class="text-sm text-gray-600">Contract Type</span>
                        <span class="text-sm font-semibold text-gray-900">
                            {{ $isShortTerm ? 'Short-Term' : 'Long-Term' }} ({{ $leaseTerm }} {{ $leaseTerm === 1 ? 'month' : 'months' }})
                        </span>
                    </div>
                    <div class="flex items-center justify-between py-2.5 border-b border-gray-50">
                        <span class="text-sm text-gray-600">Monthly Rate</span>
                        <span class="text-sm font-bold text-gray-900">&#8369;{{ number_format($contractRate, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2.5 border-b border-gray-50">
                        <span class="text-sm text-gray-600">Shift</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $lease->shift }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2.5">
                        <span class="text-sm text-gray-600">Auto-Renewal</span>
                        <span class="inline-flex items-center gap-1.5 text-sm font-semibold {{ $autoRenew ? 'text-emerald-600' : 'text-gray-500' }}">
                            @if($autoRenew)
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                Enabled
                            @else
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                Disabled
                            @endif
                        </span>
                    </div>
                </div>
            </div>
            @else
                <div class="p-6 text-center py-12">
                    <p class="text-sm text-gray-500">No active lease found</p>
                </div>
            @endif
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- ROW 4: MOVE-IN/MOVE-OUT  +  MAINTENANCE REQUESTS          --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- Move-In / Move-Out Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-cyan-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Move-In / Move-Out</h3>
                </div>
            </div>

            <div class="p-6 space-y-4">
                {{-- Move-in date --}}
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-emerald-600 uppercase tracking-wide">Move-In Date</p>
                            <p class="text-lg font-bold text-gray-900 mt-1">
                                {{ $moveInDate ? \Carbon\Carbon::parse($moveInDate)->format('M d, Y') : 'Not set' }}
                            </p>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                        </div>
                    </div>
                </div>

                {{-- Move-out date --}}
                <div class="p-4 rounded-xl {{ $moveOutDate ? 'bg-red-50 border border-red-100' : 'bg-gray-50 border border-gray-100' }}">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold {{ $moveOutDate ? 'text-red-600' : 'text-gray-500' }} uppercase tracking-wide">Move-Out Date</p>
                            <p class="text-lg font-bold text-gray-900 mt-1">
                                {{ $moveOutDate ? \Carbon\Carbon::parse($moveOutDate)->format('M d, Y') : 'N/A' }}
                            </p>
                        </div>
                        <div class="w-10 h-10 rounded-full {{ $moveOutDate ? 'bg-red-100' : 'bg-gray-200' }} flex items-center justify-center">
                            <svg class="w-5 h-5 {{ $moveOutDate ? 'text-red-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </div>
                    </div>
                </div>

                {{-- Clearance status (only during move-out) --}}
                @if($moveOutDate)
                    <div class="pt-2">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">Clearance Checklist</p>
                        <div class="space-y-2">
                            <div class="flex items-center gap-3 text-sm">
                                <span class="w-5 h-5 rounded-full bg-emerald-100 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                </span>
                                <span class="text-gray-700">Documents returned</span>
                            </div>
                            <div class="flex items-center gap-3 text-sm">
                                <span class="w-5 h-5 rounded-full bg-gray-200 flex items-center justify-center">
                                    <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                                </span>
                                <span class="text-gray-500">Bills settled</span>
                            </div>
                            <div class="flex items-center gap-3 text-sm">
                                <span class="w-5 h-5 rounded-full bg-gray-200 flex items-center justify-center">
                                    <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                                </span>
                                <span class="text-gray-500">Room inspection done</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Maintenance Requests Card --}}
        <div class="xl:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Maintenance Requests</h3>
                </div>
                @if($openMaintenanceCount > 0)
                    <span class="px-3 py-1 rounded-full bg-orange-50 text-orange-700 text-xs font-bold">
                        {{ $openMaintenanceCount }} OPEN
                    </span>
                @endif
            </div>

            <div class="p-6">
                {{-- Status summary pills --}}
                <div class="flex items-center gap-3 mb-5">
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-amber-50">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <span class="text-xs font-semibold text-amber-700">{{ $pendingMaintenanceCount }} Pending</span>
                    </div>
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-blue-50">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        <span class="text-xs font-semibold text-blue-700">{{ $ongoingMaintenanceCount }} In Progress</span>
                    </div>
                </div>

                {{-- Recent requests --}}
                @if(count($recentRequests) > 0)
                    <div class="space-y-3">
                        @foreach($recentRequests as $request)
                            <div class="flex items-start gap-4 p-4 rounded-xl bg-gray-50 hover:bg-gray-100 transition-colors">
                                <div class="flex-shrink-0 mt-0.5">
                                    <span class="w-3 h-3 rounded-full inline-block
                                        {{ $request->status === 'Pending' ? 'bg-amber-500' : '' }}
                                        {{ $request->status === 'Ongoing' ? 'bg-blue-500' : '' }}
                                        {{ $request->status === 'Completed' ? 'bg-emerald-500' : '' }}
                                    "></span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2">
                                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $request->problem }}</p>
                                        <span class="flex-shrink-0 px-2 py-0.5 rounded text-xs font-medium
                                            {{ $request->status === 'Pending' ? 'bg-amber-100 text-amber-700' : '' }}
                                            {{ $request->status === 'Ongoing' ? 'bg-blue-100 text-blue-700' : '' }}
                                            {{ $request->status === 'Completed' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                        ">{{ $request->status }}</span>
                                    </div>
                                    <div class="flex items-center gap-3 mt-1">
                                        <span class="text-xs text-gray-500">{{ $request->category }}</span>
                                        <span class="text-xs text-gray-400">&bull;</span>
                                        <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($request->log_date)->format('M d, Y') }}</span>
                                        <span class="text-xs text-gray-400">&bull;</span>
                                        <span class="text-xs font-medium
                                            {{ $request->urgency === 'Level 4' ? 'text-red-600' : '' }}
                                            {{ $request->urgency === 'Level 3' ? 'text-orange-600' : '' }}
                                            {{ $request->urgency === 'Level 2' ? 'text-amber-600' : '' }}
                                            {{ $request->urgency === 'Level 1' ? 'text-gray-600' : '' }}
                                        ">{{ $request->urgency }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        </div>
                        <p class="text-sm font-medium text-gray-700">All clear!</p>
                        <p class="text-xs text-gray-500 mt-1">No maintenance requests at the moment</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

