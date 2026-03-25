<div class="space-y-6" x-data="{ showAllPenalties: false }">

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- ROW 1: PAYMENT & BILLING  +  BILLING BREAKDOWN            --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- Payment & Billing Card --}}
        <div class="xl:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-row">

            {{-- Left accent strip --}}
            <div class="w-1.5 flex-shrink-0
                {{ $paymentStatus === 'Paid' ? 'bg-emerald-500' : '' }}
                {{ $paymentStatus === 'Unpaid' ? 'bg-amber-400' : '' }}
                {{ $paymentStatus === 'Overdue' ? 'bg-red-500' : '' }}
                {{ $paymentStatus === 'No Billing' ? 'bg-blue-500' : '' }}"></div>

            <div class="flex-1 min-w-0">
                {{-- Header --}}
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
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

                {{-- Amount + Status --}}
                <div class="px-6 pt-6 pb-5">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-2">Amount Due This Month</p>
                    <p class="text-5xl font-extrabold text-gray-900 tracking-tight">
                        <span class="text-2xl font-bold text-gray-400 align-top mr-0.5">&#8369;</span>{{ number_format($amountDue, 2) }}
                    </p>

                    @if($dueDate)
                        <div class="mt-4">
                            @if($paymentStatus === 'Paid')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-700 text-sm font-semibold">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    Paid in full
                                </span>
                            @elseif($daysUntilDue > 0)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-semibold
                                    {{ $daysUntilDue <= 3 ? 'bg-red-50 text-red-700' : ($daysUntilDue <= 7 ? 'bg-amber-50 text-amber-700' : 'bg-blue-50 text-blue-700') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $daysUntilDue }} {{ $daysUntilDue === 1 ? 'day' : 'days' }} left
                                </span>
                            @elseif($daysUntilDue == 0)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-red-50 text-red-700 text-sm font-semibold animate-pulse">
                                    Due today!
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-red-100 text-red-800 text-sm font-bold">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                    {{ abs($daysUntilDue) }} {{ abs($daysUntilDue) === 1 ? 'day' : 'days' }} overdue
                                </span>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Warning banner (only when overdue) --}}
                @if($daysUntilDue < 0 && $paymentStatus !== 'Paid')
                    <div class="mx-6 mb-5 px-4 py-3 rounded-xl bg-red-50 border border-red-100 flex items-center gap-3">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        <p class="text-sm text-red-700">
                            Your payment is {{ abs($daysUntilDue) }} {{ abs($daysUntilDue) === 1 ? 'day' : 'days' }} overdue. Pay now to avoid additional late fees.
                        </p>
                    </div>
                @endif

                {{-- Bottom stats --}}
                <div class="px-6 pb-5 pt-1 border-t border-gray-100 grid grid-cols-3 gap-5">
                    {{-- Outstanding --}}
                    <div class="pt-4">
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Outstanding</p>
                        <p class="text-xl font-extrabold {{ $outstandingBalance > 0 ? 'text-orange-600' : 'text-gray-900' }}">
                            &#8369;{{ number_format($outstandingBalance, 2) }}
                        </p>
                    </div>

                    {{-- Due Date --}}
                    <div class="pt-4">
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Due Date</p>
                        <p class="text-xl font-extrabold text-gray-900">
                            {{ $dueDate ? \Carbon\Carbon::parse($dueDate)->format('M d, Y') : 'N/A' }}
                        </p>
                    </div>

                    {{-- Next Bill --}}
                    <div class="pt-4">
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Next Bill</p>
                        <p class="text-xl font-extrabold text-gray-900">
                            {{ $nextPaymentDate ? \Carbon\Carbon::parse($nextPaymentDate)->format('M d, Y') : 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- ROW 2: SECURITY DEPOSIT  +  LEASE & CONTRACT              --}}
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
    {{-- ROW 3.5: CONTRACT SIGNING & ITEMS CONFIRMATION             --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    @if($lease)
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        {{-- Contract Signature Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Move-In Contract</h3>
                        <p class="text-xs text-gray-500">Review and sign your lease agreement</p>
                    </div>
                </div>
                @if($contractAgreed)
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold uppercase tracking-wide">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-2"></span>
                        Signed
                    </span>
                @elseif($ownerSignature && !$tenantSignature)
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-amber-50 text-amber-700 text-xs font-bold uppercase tracking-wide animate-pulse">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-2"></span>
                        Action Needed
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-50 text-gray-500 text-xs font-bold uppercase tracking-wide">
                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400 mr-2"></span>
                        Pending
                    </span>
                @endif
            </div>

            <div class="p-6">
                {{-- Contract Summary --}}
                <div class="bg-gray-50 rounded-xl p-4 mb-5 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Property</span>
                        <span class="font-semibold text-gray-800">{{ $contractData['property'] ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Unit / Bed</span>
                        <span class="font-semibold text-gray-800">{{ $contractData['unit'] ?? '—' }} / {{ $contractData['bed'] ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Lease Period</span>
                        <span class="font-semibold text-gray-800">{{ $contractData['start_date'] ?? '—' }} — {{ $contractData['end_date'] ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Monthly Rate</span>
                        <span class="font-bold text-gray-900">&#8369;{{ number_format($contractData['monthly_rate'] ?? 0, 2) }}</span>
                    </div>
                </div>

                {{-- Signature Status --}}
                <div class="space-y-3 mb-5">
                    {{-- Owner/Lessor Signature --}}
                    <div class="flex items-center justify-between p-3 rounded-xl border {{ $ownerSignature ? 'border-emerald-200 bg-emerald-50/50' : 'border-gray-200 bg-gray-50/50' }}">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg {{ $ownerSignature ? 'bg-emerald-100' : 'bg-gray-200' }} flex items-center justify-center">
                                @if($ownerSignature)
                                    <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                @else
                                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @endif
                            </div>
                            <div>
                                <p class="text-xs font-bold {{ $ownerSignature ? 'text-emerald-700' : 'text-gray-600' }}">Lessor / Manager</p>
                                <p class="text-[10px] {{ $ownerSignature ? 'text-emerald-600' : 'text-gray-400' }}">
                                    {{ $ownerSignature ? 'Signed: ' . $ownerSignedAt : 'Awaiting signature' }}
                                </p>
                            </div>
                        </div>
                        @if($ownerSignature)
                            <img src="{{ asset('storage/' . $ownerSignature) }}" class="h-8 object-contain" alt="Owner Signature">
                        @endif
                    </div>

                    {{-- Tenant Signature --}}
                    <div class="flex items-center justify-between p-3 rounded-xl border {{ $tenantSignature ? 'border-emerald-200 bg-emerald-50/50' : 'border-blue-200 bg-blue-50/30' }}">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg {{ $tenantSignature ? 'bg-emerald-100' : 'bg-blue-100' }} flex items-center justify-center">
                                @if($tenantSignature)
                                    <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                @else
                                    <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                                @endif
                            </div>
                            <div>
                                <p class="text-xs font-bold {{ $tenantSignature ? 'text-emerald-700' : 'text-blue-700' }}">Your Signature</p>
                                <p class="text-[10px] {{ $tenantSignature ? 'text-emerald-600' : 'text-blue-500' }}">
                                    {{ $tenantSignature ? 'Signed: ' . $tenantSignedAt : 'Your signature is required' }}
                                </p>
                            </div>
                        </div>
                        @if($tenantSignature)
                            <img src="{{ asset('storage/' . $tenantSignature) }}" class="h-8 object-contain" alt="Your Signature">
                        @endif
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="space-y-2">
                    {{-- Always show "View Contract" button --}}
                    <button
                        wire:click="toggleContract"
                        class="w-full py-3 px-4 bg-[#070589] hover:bg-[#000060] text-white font-bold rounded-xl text-sm transition-colors flex items-center justify-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        {{ !$tenantSignature && $ownerSignature ? 'Read & Sign Contract' : 'View Contract' }}
                    </button>

                    @if(!$tenantSignature && !$ownerSignature)
                        <div class="text-center py-2 px-4 bg-gray-50 rounded-xl">
                            <p class="text-xs text-gray-500">Waiting for the lessor/manager to sign first.</p>
                        </div>
                    @elseif($contractAgreed)
                        <div class="text-center py-2 px-4 bg-emerald-50 rounded-xl border border-emerald-200">
                            <p class="text-sm font-bold text-emerald-700">Contract Fully Signed</p>
                            <p class="text-[10px] text-emerald-600 mt-0.5">Both parties have signed electronically per RA 8792.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Items Received Confirmation Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Items Received</h3>
                        <p class="text-xs text-gray-500">Confirm the items you received at move-in</p>
                    </div>
                </div>
                @if($itemsConfirmedByTenant)
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold uppercase tracking-wide">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-2"></span>
                        Confirmed
                    </span>
                @elseif(count($itemsReceived) > 0)
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-amber-50 text-amber-700 text-xs font-bold uppercase tracking-wide">
                        {{ collect($itemsReceived)->where('tenant_confirmed', true)->count() }}/{{ count($itemsReceived) }}
                    </span>
                @endif
            </div>

            <div class="p-6">
                @if(count($itemsReceived) > 0)
                    <div class="space-y-2 mb-4">
                        @foreach($itemsReceived as $index => $item)
                            <div class="flex items-center justify-between p-3 rounded-xl border {{ $item['tenant_confirmed'] ? 'border-emerald-200 bg-emerald-50/30' : 'border-gray-200' }}">
                                <div class="flex items-center gap-3 flex-1 min-w-0">
                                    <div class="w-7 h-7 rounded-lg {{ $item['tenant_confirmed'] ? 'bg-emerald-100' : 'bg-gray-100' }} flex items-center justify-center flex-shrink-0">
                                        @if($item['tenant_confirmed'])
                                            <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        @else
                                            <span class="text-xs font-bold text-gray-400">{{ $index + 1 }}</span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-800 truncate">{{ $item['item_name'] }}</p>
                                        <p class="text-[10px] text-gray-500">Qty: {{ $item['quantity'] ?? '—' }} &bull; {{ $item['condition'] ?? '—' }}</p>
                                    </div>
                                </div>
                                @if(!$item['tenant_confirmed'])
                                    <button
                                        wire:click="confirmItemReceived({{ $index }})"
                                        class="ml-3 px-3 py-1.5 text-[10px] font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors flex-shrink-0"
                                    >
                                        Confirm
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if(!$itemsConfirmedByTenant)
                        <button
                            wire:click="confirmAllItems"
                            class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition-colors"
                        >
                            Confirm All Items Received
                        </button>
                    @else
                        <div class="text-center py-2 px-4 bg-emerald-50 rounded-xl border border-emerald-200">
                            <p class="text-xs font-bold text-emerald-700">All items confirmed</p>
                        </div>
                    @endif
                @else
                    <div class="text-center py-8">
                        <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                        </div>
                        <p class="text-sm font-medium text-gray-600">No inspection data yet</p>
                        <p class="text-xs text-gray-400 mt-1">Items will appear here after the manager records the move-in inspection.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- ROW 3.6: MOVE-OUT CONTRACT & ITEMS RETURNED                --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    @if($lease && $moveOutDate)
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        {{-- Move-Out Contract Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Move-Out Contract</h3>
                        <p class="text-xs text-gray-500">View your move-out clearance & settlement</p>
                    </div>
                </div>
                @if(count($moveOutChecklist) > 0)
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold uppercase tracking-wide">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-2"></span>
                        Inspected
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-50 text-gray-500 text-xs font-bold uppercase tracking-wide">
                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400 mr-2"></span>
                        Pending
                    </span>
                @endif
            </div>

            <div class="p-6">
                {{-- Move-Out Summary --}}
                <div class="bg-gray-50 rounded-xl p-4 mb-5 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Move-Out Date</span>
                        <span class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($moveOutDate)->format('M d, Y') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Security Deposit</span>
                        <span class="font-bold text-gray-900">&#8369;{{ number_format($securityDeposit, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Inspection Status</span>
                        <span class="font-semibold {{ count($moveOutChecklist) > 0 ? 'text-emerald-700' : 'text-amber-700' }}">
                            {{ count($moveOutChecklist) > 0 ? 'Completed' : 'Awaiting inspection' }}
                        </span>
                    </div>
                </div>

                {{-- View Contract Button --}}
                <button
                    wire:click="toggleMoveOutContract"
                    class="w-full py-3 px-4 bg-[#070589] hover:bg-[#000060] text-white font-bold rounded-xl text-sm transition-colors flex items-center justify-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                    View Move-Out Contract
                </button>
            </div>
        </div>

        {{-- Items Returned Confirmation Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Items Returned</h3>
                        <p class="text-xs text-gray-500">Confirm the items you've returned at move-out</p>
                    </div>
                </div>
                @if($itemsReturnedConfirmedByTenant)
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold uppercase tracking-wide">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-2"></span>
                        Confirmed
                    </span>
                @elseif(count($itemsReturned) > 0)
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-amber-50 text-amber-700 text-xs font-bold uppercase tracking-wide">
                        {{ collect($itemsReturned)->where('tenant_confirmed', true)->count() }}/{{ count($itemsReturned) }}
                    </span>
                @endif
            </div>

            <div class="p-6">
                @if(count($itemsReturned) > 0)
                    <div class="space-y-2 mb-4">
                        @foreach($itemsReturned as $index => $item)
                            <div class="flex items-center justify-between p-3 rounded-xl border {{ $item['tenant_confirmed'] ? 'border-emerald-200 bg-emerald-50/30' : 'border-gray-200' }}">
                                <div class="flex items-center gap-3 flex-1 min-w-0">
                                    <div class="w-7 h-7 rounded-lg {{ $item['tenant_confirmed'] ? 'bg-emerald-100' : 'bg-gray-100' }} flex items-center justify-center flex-shrink-0">
                                        @if($item['tenant_confirmed'])
                                            <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        @else
                                            <span class="text-xs font-bold text-gray-400">{{ $index + 1 }}</span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-800 truncate">{{ $item['item_name'] }}</p>
                                        <p class="text-[10px] text-gray-500">Qty: {{ $item['quantity'] ?? '—' }} &bull; {{ $item['condition'] ?? '—' }}</p>
                                    </div>
                                </div>
                                @if(!$item['tenant_confirmed'])
                                    <button
                                        wire:click="confirmItemReturned({{ $index }})"
                                        class="ml-3 px-3 py-1.5 text-[10px] font-bold text-orange-600 bg-orange-50 hover:bg-orange-100 rounded-lg transition-colors flex-shrink-0"
                                    >
                                        Confirm
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if(!$itemsReturnedConfirmedByTenant)
                        <button
                            wire:click="confirmAllReturned"
                            class="w-full py-2.5 px-4 bg-orange-600 hover:bg-orange-700 text-white font-bold rounded-xl text-xs transition-colors"
                        >
                            Confirm All Items Returned
                        </button>
                    @else
                        <div class="text-center py-2 px-4 bg-emerald-50 rounded-xl border border-emerald-200">
                            <p class="text-xs font-bold text-emerald-700">All items confirmed as returned</p>
                        </div>
                    @endif
                @else
                    <div class="text-center py-8">
                        <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                        </div>
                        <p class="text-sm font-medium text-gray-600">No move-out inspection data yet</p>
                        <p class="text-xs text-gray-400 mt-1">Items will appear here after the manager records the move-out inspection.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- MOVE-OUT CONTRACT VIEWER MODAL                              --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    @if($showMoveOutContract && $lease)
        @php
            $t = $tenantContractData;
            $deposit = $t['move_in_details']['security_deposit'];
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm">
            <div class="relative w-full max-w-4xl bg-white rounded-2xl shadow-xl overflow-hidden max-h-[90vh] flex flex-col">
                <div class="bg-[#070589] text-white p-5 flex items-center justify-between flex-shrink-0">
                    <h2 class="text-lg font-bold">Move-Out Clearance & Deposit Settlement</h2>
                    <button wire:click="toggleMoveOutContract" class="text-white hover:text-blue-200">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto p-8 space-y-6 text-sm text-gray-800" id="move-out-contract-tenant" style="font-family: 'Open Sans', sans-serif;">

                    @include('partials.move-out-contract-body', [
                        't' => $t,
                        'deposit' => $deposit,
                        'moveOutChecklist' => $moveOutChecklist,
                        'itemsReturned' => $itemsReturned,
                        'inspectionChecklist' => $moveOutInspectionChecklist,
                    ])

                </div>
                <div class="p-4 bg-gray-50 border-t flex justify-end gap-3 flex-shrink-0">
                    <button wire:click="toggleMoveOutContract" class="px-5 py-2.5 text-sm font-semibold text-gray-600 bg-gray-200 hover:bg-gray-300 rounded-xl">Close</button>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- FULL CONTRACT VIEWER MODAL                                  --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
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
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm">
            <div class="relative w-full max-w-4xl bg-white rounded-2xl shadow-xl overflow-hidden max-h-[90vh] flex flex-col">
                <div class="bg-[#070589] text-white p-5 flex items-center justify-between flex-shrink-0">
                    <h2 class="text-lg font-bold">Move-In Contract</h2>
                    <button wire:click="toggleContract" class="text-white hover:text-blue-200">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-8 space-y-6 text-sm text-gray-800" style="font-family: 'Open Sans', sans-serif;">
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
                </div>

                {{-- Footer --}}
                <div class="p-4 bg-gray-50 border-t flex justify-end gap-3 flex-shrink-0">
                    @if(!$tenantSignature && $ownerSignature)
                        <button wire:click="toggleContract" class="px-5 py-2.5 text-sm font-semibold text-gray-600 bg-gray-200 hover:bg-gray-300 rounded-xl">Close</button>
                        <button wire:click="openSignatureModal" class="bg-[#070589] hover:bg-[#000060] text-white font-bold py-2.5 px-6 rounded-xl text-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                            I Agree — Sign Now
                        </button>
                    @else
                        <button wire:click="toggleContract" class="bg-[#070589] hover:bg-[#000060] text-white font-bold py-2.5 px-6 rounded-xl text-sm">Close</button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- TENANT E-SIGNATURE MODAL                                   --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    @if($showSignatureModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
             x-data="{
                pad: null,
                isEmpty: true,

                init() {
                    this.loadLibrary().then(() => this.setupCanvas());
                },

                loadLibrary() {
                    return new Promise((resolve) => {
                        if (window.SignaturePad) { resolve(); return; }
                        const s = document.createElement('script');
                        s.src = 'https://cdn.jsdelivr.net/npm/signature_pad@4.2.0/dist/signature_pad.umd.min.js';
                        s.onload = () => resolve();
                        document.head.appendChild(s);
                    });
                },

                setupCanvas() {
                    this.$nextTick(() => {
                        setTimeout(() => {
                            const canvas = this.$refs.sigCanvas;
                            if (!canvas) return;
                            const rect = canvas.getBoundingClientRect();
                            if (rect.width === 0) { setTimeout(() => this.setupCanvas(), 150); return; }
                            const ratio = Math.max(window.devicePixelRatio || 1, 1);
                            canvas.width = rect.width * ratio;
                            canvas.height = rect.height * ratio;
                            canvas.getContext('2d').scale(ratio, ratio);
                            this.pad = new SignaturePad(canvas, {
                                backgroundColor: 'rgba(255,255,255,0)',
                                penColor: '#000',
                                minWidth: 1,
                                maxWidth: 2.5,
                            });
                            this.pad.addEventListener('beginStroke', () => { this.isEmpty = false; });
                        }, 100);
                    });
                },

                clearPad() { if (this.pad) { this.pad.clear(); this.isEmpty = true; } },

                submit() {
                    if (!this.pad || this.pad.isEmpty()) return;
                    $wire.call('saveTenantSignature', this.pad.toDataURL('image/png'));
                }
             }"
        >
            <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl">
                <div class="bg-gradient-to-r from-[#070589] to-[#2360E8] text-white p-5 rounded-t-2xl flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold">Sign Your Contract</h2>
                        <p class="text-xs text-blue-200 mt-0.5">Draw your signature below</p>
                    </div>
                    <button wire:click="closeSignatureModal" class="text-white hover:text-blue-200">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="px-5 pt-4 pb-2">
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-xs font-bold text-gray-800">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</p>
                        <p class="text-[10px] text-gray-500">Signing as Lessee / Tenant</p>
                    </div>
                </div>

                <div class="px-5 py-3">
                    <div class="border-2 border-gray-200 rounded-xl bg-white relative" style="touch-action: none;">
                        <canvas x-ref="sigCanvas" class="w-full cursor-crosshair" style="height: 200px; display: block;"></canvas>
                        <div class="absolute bottom-10 left-8 right-8 border-b border-dashed border-gray-200 pointer-events-none"></div>
                        <div class="absolute bottom-6 left-1/2 -translate-x-1/2 pointer-events-none">
                            <span class="text-[9px] text-gray-300 uppercase tracking-wider" x-show="isEmpty">Sign here</span>
                        </div>
                    </div>
                </div>

                <div class="px-5 pb-3">
                    <p class="text-[10px] text-gray-400">By clicking "Apply Signature", I confirm that I have read and agree to all terms. This e-signature is legally binding under RA 8792.</p>
                </div>

                <div class="px-5 pb-5 flex items-center justify-between">
                    <button @click="clearPad()" class="flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl">Clear</button>
                    <div class="flex gap-2">
                        <button wire:click="closeSignatureModal" class="px-5 py-2.5 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl">Cancel</button>
                        <button @click="submit()" :disabled="isEmpty" class="px-5 py-2.5 text-xs font-bold text-white bg-[#070589] hover:bg-[#000060] rounded-xl disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            Apply Signature
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

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

