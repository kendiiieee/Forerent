<div x-data="{ showAllPenalties: false }">

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- TAB NAVIGATION                                             --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    @php
        $dashTabs = [
            'overview' => 'Overview',
            'inspection' => 'Inspection & Contract',
        ];
        $dashCounts = [];
        if (($paymentStatus === 'Overdue' || ($paymentStatus === 'Unpaid' && $daysUntilDue <= 3)) || (!$tenantSignature && $ownerSignature) || $openMaintenanceCount > 0) {
            $actionCount = 0;
            if ($paymentStatus === 'Overdue' || ($paymentStatus === 'Unpaid' && $daysUntilDue <= 3)) $actionCount++;
            if (!$tenantSignature && $ownerSignature) $actionCount++;
            if ($moveOutDate && !$moveOutTenantSignature && $moveOutOwnerSignature) $actionCount++;
            if ($openMaintenanceCount > 0) $actionCount++;
            if ($lease && $daysUntilExpiry <= 30 && $daysUntilExpiry > 0) $actionCount++;
            if ($actionCount > 0) $dashCounts['overview'] = $actionCount;
        }
    @endphp

    <div class="mb-5">
        <x-ui.sort-tab
            :tabs="$dashTabs"
            :activeTab="$dashTab"
            :counts="$dashCounts"
            action="setDashTab"
        />
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- TAB: OVERVIEW                                              --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    @if($dashTab === 'overview')
    @php
        $totalDays = $lease ? \Carbon\Carbon::parse($lease->start_date)->diffInDays(\Carbon\Carbon::parse($leaseEndDate)) : 0;
        $elapsed = $lease ? max(\Carbon\Carbon::parse($lease->start_date)->diffInDays(now()), 0) : 0;
        $leaseProgress = $totalDays > 0 ? min(($elapsed / $totalDays) * 100, 100) : 0;
        $utilityTotal = $electricityShare + $waterShare;
        $utilityMax = max($electricityShare, $waterShare, 1);
    @endphp
    <div class="space-y-4">
        <div class="rounded-2xl overflow-hidden shadow-lg shadow-blue-900/10">@include('partials.tenant-payment-banner')</div>

        {{-- Overdue warning --}}
        @if($daysUntilDue < 0 && $paymentStatus !== 'Paid')
            <div class="px-3.5 py-2.5 rounded-xl bg-red-50 flex items-center justify-between gap-2.5">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    <p class="text-[13px] font-medium text-red-600">Your payment is {{ abs($daysUntilDue) }} {{ abs($daysUntilDue) === 1 ? 'day' : 'days' }} overdue.</p>
                </div>
                @if(count($pendingPaymentRequests) === 0)
                    <button wire:click="openPaymentModal" class="flex-shrink-0 px-3 py-1 rounded-lg bg-red-600 text-white text-xs font-bold uppercase tracking-wide hover:bg-red-700 transition">
                        Pay Now
                    </button>
                @else
                    <span class="flex-shrink-0 px-3 py-1 rounded-lg bg-amber-100 text-amber-700 text-xs font-bold uppercase tracking-wide">
                        Pending Verification
                    </span>
                @endif
            </div>
        @endif

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-gray-100 p-4 text-left"><div class="flex items-center mb-3"><div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#eef2ff"><svg class="w-5 h-5" style="color:#070589" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div></div><p class="text-xs font-bold uppercase tracking-wider" style="color:#070589">Due Date</p><p class="text-xl font-extrabold text-gray-900 mt-1">{{ $dueDate ? \Carbon\Carbon::parse($dueDate)->format('M d') : 'N/A' }}</p>@if($daysUntilDue > 0 && $paymentStatus !== 'Paid')<p class="text-xs font-semibold mt-1" style="color:#2563eb">{{ $daysUntilDue }}d left</p>@elseif($daysUntilDue < 0 && $paymentStatus !== 'Paid')<p class="text-xs font-bold text-red-500 mt-1">{{ abs($daysUntilDue) }}d overdue</p>@elseif($paymentStatus === 'Paid')<p class="text-xs font-semibold mt-1" style="color:#2563eb">Settled</p>@endif</div>
            <div class="bg-white rounded-2xl border border-gray-100 p-4 text-left"><div class="flex items-center mb-3"><div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#eef2ff"><svg class="w-5 h-5" style="color:#070589" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div></div><p class="text-xs font-bold uppercase tracking-wider" style="color:#070589">Outstanding</p><p class="text-xl font-extrabold text-gray-900 mt-1">&#8369;{{ number_format($outstandingBalance, 2) }}</p>@if($outstandingBalance > 0)<p class="text-xs font-semibold text-red-500 mt-1">Previous months</p>@else<p class="text-xs font-semibold mt-1" style="color:#2563eb">All clear</p>@endif</div>
            <div class="bg-white rounded-2xl border border-gray-100 p-4 text-left"><div class="flex items-center mb-3"><div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#eef2ff"><svg class="w-5 h-5" style="color:#070589" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0l8.955 8.955M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg></div></div><p class="text-xs font-bold uppercase tracking-wider" style="color:#070589">Monthly Rate</p><p class="text-xl font-extrabold text-gray-900 mt-1">&#8369;{{ number_format($contractRate, 2) }}</p><p class="text-xs font-semibold mt-1" style="color:#2563eb">{{ $isShortTerm ? 'Short-term' : 'Long-term' }}</p></div>
            <div class="bg-white rounded-2xl border border-gray-100 p-4 text-left"><div class="flex items-center mb-3"><div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#eef2ff"><svg class="w-5 h-5" style="color:#070589" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div></div><p class="text-xs font-bold uppercase tracking-wider" style="color:#070589">Lease</p><p class="text-xl font-extrabold text-gray-900 mt-1">{{ $leaseStatus }}</p>@if($lease)<p class="text-xs font-semibold mt-1" style="color:#2563eb">{{ max($daysUntilExpiry, 0) }}d remaining</p>@endif</div>
        </div>

        {{-- ══ Lease Ring + Billing Cycle ════════════════════════════ --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            @if($lease)
            <div class="rounded-2xl overflow-hidden relative text-left" style="background: linear-gradient(160deg, #020147 0%, #070589 45%, #1e3fae 100%)">
                <div class="absolute -right-10 -top-10 w-36 h-36 rounded-full" style="background: radial-gradient(circle, rgba(96,165,250,0.08) 0%, transparent 70%)"></div>
                <div class="absolute -left-6 bottom-4 w-24 h-24 rounded-full" style="background: radial-gradient(circle, rgba(59,130,246,0.06) 0%, transparent 70%)"></div>
                <div class="relative z-10 p-6 h-full flex flex-col">
                    <div class="flex items-center justify-between mb-1">
                        <p class="text-xs font-bold text-blue-200 uppercase tracking-[0.2em]">Lease Expiry</p>
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[13px] font-bold uppercase bg-white/10 text-blue-200 ring-1 ring-white/10"><span class="w-1.5 h-1.5 rounded-full {{ $leaseStatus === 'Active' ? 'bg-blue-300' : 'bg-red-400' }}"></span>{{ $leaseStatus }}</span>
                    </div>
                    <p class="text-xs text-white/70 mb-4">{{ \Carbon\Carbon::parse($lease->start_date)->format('M d, Y') }} — {{ \Carbon\Carbon::parse($leaseEndDate)->format('M d, Y') }}</p>
                    <div class="flex items-center justify-center py-4">
                        <div class="relative">
                            <svg class="w-32 h-32 -rotate-90" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="42" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="6"/>
                                <circle cx="50" cy="50" r="42" fill="none" stroke="{{ $daysUntilExpiry <= 30 ? '#f87171' : '#60a5fa' }}" stroke-width="6" stroke-linecap="round" stroke-dasharray="{{ 2 * 3.14159 * 42 }}" stroke-dashoffset="{{ 2 * 3.14159 * 42 * (1 - $leaseProgress / 100) }}" class="transition-all duration-1000"/>
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <p class="text-4xl font-extrabold text-white leading-none">{{ max($daysUntilExpiry, 0) }}</p>
                                <p class="text-xs font-medium text-white/70 mt-1">days left</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-auto space-y-1.5">
                        <div class="flex justify-between text-xs"><span class="text-white/70">Term</span><span class="font-bold text-white">{{ $leaseTerm }} {{ $leaseTerm === 1 ? 'month' : 'months' }}</span></div>
                        <div class="flex justify-between text-xs"><span class="text-white/70">Shift</span><span class="font-bold text-white">{{ $lease->shift }}</span></div>
                        <div class="flex justify-between text-xs"><span class="text-white/70">Auto-Renew</span><span class="font-bold text-white">{{ $autoRenew ? 'Enabled' : 'Off' }}</span></div>
                        <div class="w-full rounded-full h-1 overflow-hidden mt-2" style="background:rgba(255,255,255,0.08)"><div class="h-full rounded-full bg-blue-400 transition-all duration-500" style="width:{{ $leaseProgress }}%"></div></div>
                        <p class="text-[13px] text-white/60 text-center">{{ round($leaseProgress) }}% elapsed</p>
                    </div>
                </div>
            </div>
            @endif

            <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 p-5">
                <div class="flex items-center gap-2.5 mb-5">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#eef2ff"><svg class="w-5 h-5" style="color:#070589" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
                    <div><p class="text-sm font-bold text-gray-900">Billing Cycle</p><p class="text-xs text-gray-400">{{ $billingStartDate ? \Carbon\Carbon::parse($billingStartDate)->format('M d') : '—' }} — {{ $nextPaymentDate ? \Carbon\Carbon::parse($nextPaymentDate)->format('M d, Y') : '—' }}</p></div>
                </div>
                <div class="relative">
                    <div class="w-full rounded-full h-3 overflow-hidden" style="background:#eef2ff"><div class="h-full rounded-full transition-all duration-500 relative" style="width:{{ $billingProgress }}%; background: linear-gradient(90deg, #070589, #2563eb)"><div class="absolute right-0 top-1/2 -translate-y-1/2 w-4 h-4 rounded-full bg-white border-[2.5px] shadow-md" style="border-color:#070589"></div></div></div>
                    <div class="flex items-center justify-between mt-2.5"><span class="text-xs font-medium text-gray-400">{{ $billingProgress }}% through cycle</span><span class="text-xs font-bold" style="color:#070589">Due: {{ $dueDate ? \Carbon\Carbon::parse($dueDate)->format('M d') : 'N/A' }}</span></div>
                </div>
                <div class="grid grid-cols-3 gap-3 mt-5">
                    <div class="text-center p-3 rounded-xl" style="background:#f8f9ff"><p class="text-[13px] font-bold uppercase tracking-wider" style="color:#070589">Due In</p><p class="text-base font-extrabold text-gray-900 mt-1">@if($paymentStatus === 'Paid') Paid @elseif($daysUntilDue < 0) {{ abs($daysUntilDue) }}d @else {{ $daysUntilDue }}d @endif</p></div>
                    <div class="text-center p-3 rounded-xl" style="background:#f8f9ff"><p class="text-[13px] font-bold uppercase tracking-wider" style="color:#070589">Overdue</p><p class="text-base font-extrabold {{ $outstandingBalance > 0 ? 'text-red-600' : 'text-gray-900' }} mt-1">{{ $outstandingBalance > 0 ? '₱' . number_format($outstandingBalance, 0) : 'None' }}</p></div>
                    <div class="text-center p-3 rounded-xl" style="background:#f8f9ff"><p class="text-[13px] font-bold uppercase tracking-wider" style="color:#070589">Next Bill</p><p class="text-base font-extrabold text-gray-900 mt-1">{{ $nextPaymentDate ? \Carbon\Carbon::parse($nextPaymentDate)->format('M d') : 'N/A' }}</p></div>
                </div>
            </div>
        </div>

        {{-- Maintenance + Utilities + Contract --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden flex flex-col">
                <a href="{{ route('tenant.maintenance') }}" class="block overflow-hidden group relative" style="background: linear-gradient(135deg, #eef2ff 0%, #dbeafe 100%)">
                    <div class="px-5 py-4 relative z-10">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(7,5,137,0.1)">
                                    <svg class="w-4 h-4" style="color:#070589" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <p class="text-[13px] font-bold uppercase tracking-widest" style="color:#070589">Maintenance</p>
                            </div>
                            <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" style="color:#070589" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </div>
                        <p class="text-4xl font-extrabold tracking-tight" style="color:#070589">{{ $openMaintenanceCount }}</p>
                        <p class="text-[13px] font-medium" style="color:#2563eb">Open Requests</p>
                    </div>
                </a>
                <div class="px-4 py-2 flex-1 overflow-auto">
                    @forelse($recentRequests as $request)
                        <a href="{{ route('tenant.maintenance') }}" class="flex items-center gap-2.5 py-2 {{ !$loop->last ? 'border-b border-gray-50' : '' }} hover:bg-blue-50/30 -mx-1 px-1 rounded-lg transition-colors">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0" style="background:#eef2ff">
                                <svg class="w-3.5 h-3.5" style="color:#070589" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-gray-800 truncate">{{ $request->problem }}</p>
                                <p class="text-[13px] text-gray-400">{{ \Carbon\Carbon::parse($request->log_date)->format('M d') }}</p>
                            </div>
                            <span class="flex-shrink-0 text-[13px] font-bold px-1.5 py-0.5 rounded-full" style="background:#eef2ff;color:#070589">{{ $request->status }}</span>
                        </a>
                    @empty
                        <div class="text-center py-5">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center mx-auto mb-2" style="background:#eef2ff">
                                <svg class="w-4 h-4" style="color:#070589" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <p class="text-xs font-medium text-gray-400">All clear!</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Utilities --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 text-left">
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#eef2ff">
                            <svg class="w-5 h-5" style="color:#070589" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-900">Utilities</p>
                            <p class="text-xs text-gray-400">{{ $billingPeriod ?: 'Current period' }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xl font-extrabold text-gray-900">&#8369;{{ number_format($utilityTotal, 2) }}</p>
                        <p class="text-[13px] text-gray-400">your share</p>
                    </div>
                </div>
                @if($electricityShare > 0 || $waterShare > 0)
                    <div class="mb-3.5">
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full" style="background:#070589"></span><span class="text-[13px] font-semibold text-gray-600">Electricity</span></div>
                            <span class="text-[13px] font-bold text-gray-900">&#8369;{{ number_format($electricityShare, 2) }}</span>
                        </div>
                        <div class="w-full rounded-full h-2.5 overflow-hidden" style="background:#eef2ff">
                            <div class="h-full rounded-full transition-all duration-700" style="width:{{ $utilityMax > 0 ? min(($electricityShare / $utilityMax) * 100, 100) : 0 }}%;background:linear-gradient(90deg,#070589,#2563eb)"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full" style="background:#93c5fd"></span><span class="text-[13px] font-semibold text-gray-600">Water</span></div>
                            <span class="text-[13px] font-bold text-gray-900">&#8369;{{ number_format($waterShare, 2) }}</span>
                        </div>
                        <div class="w-full rounded-full h-2.5 overflow-hidden" style="background:#eef2ff">
                            <div class="h-full rounded-full transition-all duration-700" style="width:{{ $utilityMax > 0 ? min(($waterShare / $utilityMax) * 100, 100) : 0 }}%;background:linear-gradient(90deg,#60a5fa,#93c5fd)"></div>
                        </div>
                    </div>
                    <p class="text-[13px] text-gray-400 mt-3">Total &#8369;{{ number_format($electricityTotal + $waterTotal, 2) }} &divide; {{ $tenantCount }} tenants</p>
                @else
                    <div class="text-center py-3"><p class="text-[13px] text-gray-400">No utility bills yet</p></div>
                @endif
            </div>

            {{-- Contract Status --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 flex flex-col">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#eef2ff">
                            <svg class="w-5 h-5" style="color:#070589" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900">Contract</h3>
                    </div>
                    @if($contractAgreed)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold uppercase" style="background:#eef2ff;color:#070589">Signed</span>
                    @elseif(!$tenantSignature && $ownerSignature)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-red-50 text-red-600 text-xs font-bold uppercase animate-pulse">Action Needed</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold uppercase" style="background:#eef2ff;color:#070589">Pending</span>
                    @endif
                </div>
                <div class="flex items-center gap-2 mb-5">
                    <div class="flex items-center gap-2 flex-1 p-2.5 rounded-xl" style="background:{{ $ownerSignature ? '#eef2ff' : '#f9fafb' }}">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0" style="background:{{ $ownerSignature ? '#070589' : '#e5e7eb' }}">
                            @if($ownerSignature)
                                <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            @else
                                <span class="text-[13px] font-bold text-gray-400">1</span>
                            @endif
                        </div>
                        <div><p class="text-xs font-bold" style="color:{{ $ownerSignature ? '#070589' : '#9ca3af' }}">Lessor</p><p class="text-[13px]" style="color:{{ $ownerSignature ? '#2563eb' : '#d1d5db' }}">{{ $ownerSignature ? 'Signed' : 'Waiting' }}</p></div>
                    </div>
                    <div class="w-5 h-px" style="background:{{ $ownerSignature ? '#070589' : '#e5e7eb' }}"></div>
                    <div class="flex items-center gap-2 flex-1 p-2.5 rounded-xl" style="background:{{ $tenantSignature ? '#eef2ff' : ($ownerSignature ? '#eff6ff' : '#f9fafb') }}">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0" style="background:{{ $tenantSignature ? '#070589' : ($ownerSignature ? '#2563eb' : '#e5e7eb') }}">
                            @if($tenantSignature)
                                <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            @else
                                <span class="text-[13px] font-bold {{ $ownerSignature ? 'text-white' : 'text-gray-400' }}">2</span>
                            @endif
                        </div>
                        <div><p class="text-xs font-bold" style="color:{{ $tenantSignature ? '#070589' : ($ownerSignature ? '#2563eb' : '#9ca3af') }}">You</p><p class="text-[13px]" style="color:{{ $tenantSignature ? '#2563eb' : ($ownerSignature ? '#60a5fa' : '#d1d5db') }}">{{ $tenantSignature ? 'Signed' : ($ownerSignature ? 'Your turn' : 'Waiting') }}</p></div>
                    </div>
                </div>
                <div class="mt-auto">
                    @if(!$tenantSignature && $ownerSignature)
                        <button wire:click="toggleContract" class="w-full py-2.5 px-4 text-white font-bold rounded-xl text-[13px] transition-all flex items-center justify-center gap-2 hover:opacity-90" style="background:#070589;box-shadow:0 4px 14px rgba(7,5,137,0.3)">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                            Read & Sign Contract
                        </button>
                    @else
                        <button wire:click="setDashTab('inspection')" class="w-full py-2.5 px-4 font-bold rounded-xl text-[13px] transition-colors flex items-center justify-center gap-2 hover:opacity-80" style="background:#eef2ff;color:#070589">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                            View Contract & Inspection
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Violation Records --}}
        @if($violationCounts['total'] > 0)
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-red-50">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Violation Records</h3>
                        <p class="text-xs text-gray-400">{{ $violationCounts['total'] }} total &middot; {{ $violationCounts['issued'] }} unacknowledged</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    @if($violationCounts['issued'] > 0)
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-100 text-red-700">{{ $violationCounts['issued'] }} Issued</span>
                    @endif
                    @if($violationCounts['acknowledged'] > 0)
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-yellow-100 text-yellow-700">{{ $violationCounts['acknowledged'] }} Acknowledged</span>
                    @endif
                    @if($violationCounts['resolved'] > 0)
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-green-100 text-green-700">{{ $violationCounts['resolved'] }} Resolved</span>
                    @endif
                </div>
            </div>

            <div class="space-y-2.5">
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
                            'written_warning' => 'Written Warning',
                            'fine' => 'Fine — PHP ' . number_format($vio['fine_amount'] ?? 0, 2),
                            'lease_termination' => 'Lease Termination',
                            default => ucfirst($vio['penalty_type']),
                        };
                        $vPenaltyStyles = match($vio['penalty_type']) {
                            'written_warning' => 'bg-yellow-50 text-yellow-800 border-yellow-100',
                            'fine' => 'bg-orange-50 text-orange-800 border-orange-100',
                            'lease_termination' => 'bg-red-50 text-red-800 border-red-100',
                            default => 'bg-gray-50 text-gray-700 border-gray-100',
                        };
                    @endphp
                    <div class="rounded-xl p-4 border border-gray-100 {{ $vio['status'] === 'Issued' ? 'bg-red-50/30' : 'bg-gray-50/50' }}">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-[#070589]">{{ $vio['violation_number'] }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $vStatusStyles }}">{{ $vio['status'] }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $vSeverityStyles }}">{{ ucfirst($vio['severity']) }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-600">{{ $vOffenseLabel }} Offense</span>
                            </div>
                            <span class="text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($vio['violation_date'])->format('M d, Y') }}</span>
                        </div>

                        <p class="text-sm font-semibold text-gray-800 mb-1">{{ $vio['category'] }}</p>
                        <p class="text-xs text-gray-500 mb-2 line-clamp-2">{{ $vio['description'] }}</p>

                        {{-- Penalty Badge --}}
                        <div class="flex items-center justify-between">
                            <span class="inline-flex px-2.5 py-1 rounded-lg text-[10px] font-bold border {{ $vPenaltyStyles }}">{{ $vPenaltyLabel }}</span>

                            @if($vio['status'] === 'Issued')
                                <button
                                    wire:click="acknowledgeViolation({{ $vio['violation_id'] }})"
                                    wire:confirm="Are you sure you want to acknowledge this violation? This confirms you have received and read the notice."
                                    class="px-3 py-1.5 bg-[#070589] text-white text-[10px] font-bold rounded-lg hover:bg-[#0a0a6e] transition"
                                >
                                    Acknowledge
                                </button>
                            @endif
                        </div>

                        @if($vio['status'] === 'Resolved' && !empty($vio['resolution_notes']))
                            <div class="mt-2 bg-green-50 rounded-lg p-2.5 border border-green-100">
                                <p class="text-[10px] font-bold text-green-700 uppercase mb-0.5">Resolution</p>
                                <p class="text-xs text-green-800">{{ $vio['resolution_notes'] }}</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Penalty Schedule Reference --}}
            <div class="mt-4 bg-gray-50 rounded-xl p-3.5 border border-gray-100">
                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wide mb-1.5">Penalty Schedule (Per Contract)</p>
                <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-[11px] text-gray-600">
                    <p><span class="font-semibold">1st Offense:</span> Written Warning</p>
                    <p><span class="font-semibold">2nd Offense:</span> Fine of PHP 500.00</p>
                    <p><span class="font-semibold">3rd Offense:</span> Lease Termination</p>
                    <p><span class="font-semibold">Serious:</span> Immediate Termination</p>
                </div>
            </div>
        </div>
        @endif

        {{-- Move Dates + Payment Requests (bento row) --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            {{-- Move Dates --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5">
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background:#eef2ff">
                        <svg class="w-4 h-4" style="color:#070589" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900">Move Dates</h3>
                </div>
                <div class="space-y-2.5">
                    <div class="p-3 rounded-xl flex items-center justify-between" style="background:#f8f9ff">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider" style="color:#070589">Move-In</p>
                            <p class="text-sm font-extrabold text-gray-900 mt-0.5">{{ $moveInDate ? \Carbon\Carbon::parse($moveInDate)->format('M d, Y') : 'N/A' }}</p>
                        </div>
                        <div class="w-8 h-8 rounded-full flex items-center justify-center" style="background:#eef2ff">
                            <svg class="w-4 h-4" style="color:#070589" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                        </div>
                    </div>
                    <div class="p-3 rounded-xl flex items-center justify-between {{ $moveOutDate ? 'bg-red-50/50' : '' }}" style="{{ !$moveOutDate ? 'background:#f8f9ff' : '' }}">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider {{ $moveOutDate ? 'text-red-400' : '' }}" style="{{ !$moveOutDate ? 'color:#070589' : '' }}">Move-Out</p>
                            <p class="text-sm font-extrabold text-gray-900 mt-0.5">{{ $moveOutDate ? \Carbon\Carbon::parse($moveOutDate)->format('M d, Y') : 'N/A' }}</p>
                        </div>
                        <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $moveOutDate ? 'bg-red-100/80' : '' }}" style="{{ !$moveOutDate ? 'background:#eef2ff' : '' }}">
                            <svg class="w-4 h-4 {{ $moveOutDate ? 'text-red-400' : '' }}" style="{{ !$moveOutDate ? 'color:#070589' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Payment Requests --}}
            @if(count($pendingPaymentRequests) > 0 || count($rejectedPaymentRequests) > 0)
            <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background:#eef2ff">
                            <svg class="w-4 h-4" style="color:#070589" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900">Payment Requests</h3>
                    </div>
                </div>
                <div class="px-5 py-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($pendingPaymentRequests as $pr)
                        <div class="p-3.5 rounded-xl border" style="background:#f8f9ff;border-color:#e0e7ff">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-xs font-bold text-gray-900">{{ $pr['billing'] ? \Carbon\Carbon::parse($pr['billing']['billing_date'])->format('F Y') : 'N/A' }}</p>
                                <span class="text-xs font-bold px-2 py-0.5 rounded-full" style="background:#eef2ff;color:#070589">Pending</span>
                            </div>
                            <p class="text-lg font-extrabold text-gray-900">&#8369;{{ number_format($pr['amount_paid'], 2) }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ $pr['payment_method'] }} &middot; {{ \Carbon\Carbon::parse($pr['created_at'])->format('M d, h:i A') }}</p>
                        </div>
                    @endforeach
                    @foreach($rejectedPaymentRequests as $pr)
                        <div class="p-3.5 rounded-xl bg-red-50/80 border border-red-100">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-xs font-bold text-gray-900">{{ $pr['billing'] ? \Carbon\Carbon::parse($pr['billing']['billing_date'])->format('F Y') : 'N/A' }}</p>
                                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-700">Rejected</span>
                            </div>
                            <p class="text-lg font-extrabold text-gray-900">&#8369;{{ number_format($pr['amount_paid'], 2) }}</p>
                            @if($pr['reject_reason'])
                                <p class="text-xs font-medium text-red-500 mt-1.5">{{ $pr['reject_reason'] }}</p>
                            @endif
                            <button wire:click="resubmitPayment({{ $pr['id'] }})" class="mt-2.5 w-full py-2 rounded-xl text-white text-xs font-bold uppercase tracking-wide transition hover:opacity-90" style="background:#070589">Re-submit Payment</button>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

    </div>


    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- TAB: INSPECTION & CONTRACT                                 --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    @elseif($dashTab === 'inspection')
    <div class="space-y-5">

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
                            class="px-4 py-1.5 rounded-lg text-[13px] font-bold transition-all duration-200">
                        Move-In
                    </button>
                    @php $moveOutAvailable = $moveOutDate || $moveOutInitiated; @endphp
                    <button @click="{{ $moveOutAvailable ? "activeTab = 'moveout'" : '' }}"
                            :class="activeTab === 'moveout' ? 'bg-white text-gray-900 shadow-sm' : '{{ $moveOutAvailable ? 'text-gray-400 hover:text-gray-600' : 'text-gray-300 cursor-not-allowed' }}'"
                            class="px-4 py-1.5 rounded-lg text-[13px] font-bold transition-all duration-200"
                            {{ !$moveOutAvailable ? 'disabled' : '' }}>
                        Move-Out
                        @if(!$moveOutAvailable)
                            <span class="ml-1 text-[13px] opacity-50">(N/A)</span>
                        @endif
                    </button>
                </div>

                {{-- Dynamic Status Badge --}}
                <div>
                    <template x-if="activeTab === 'movein'">
                        <span>
                            @if($contractAgreed)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-600 text-xs font-bold uppercase tracking-wider">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>Signed
                                </span>
                            @elseif($ownerSignature && !$tenantSignature)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-amber-50 text-amber-600 text-xs font-bold uppercase tracking-wider animate-pulse">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5"></span>Action Needed
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-gray-50 text-gray-400 text-xs font-bold uppercase tracking-wider">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-300 mr-1.5"></span>Pending
                                </span>
                            @endif
                        </span>
                    </template>
                    @if($moveOutDate)
                    <template x-if="activeTab === 'moveout'">
                        <span>
                            @if($moveOutContractAgreed)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-600 text-xs font-bold uppercase tracking-wider">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>Signed
                                </span>
                            @elseif(count($moveOutChecklist) > 0)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-600 text-xs font-bold uppercase tracking-wider">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>Inspected
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-gray-50 text-gray-400 text-xs font-bold uppercase tracking-wider">
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
                        <h4 class="text-xs font-bold text-indigo-500 uppercase tracking-wider mb-4">Contract & Signature</h4>

                        <div class="rounded-xl bg-[#F4F7FC] p-3.5 mb-4 space-y-2">
                            <div class="flex justify-between text-[13px]">
                                <span class="text-gray-400">Property</span>
                                <span class="font-bold text-gray-700">{{ $contractData['property'] ?? '—' }}</span>
                            </div>
                            <div class="flex justify-between text-[13px]">
                                <span class="text-gray-400">Unit / Bed</span>
                                <span class="font-bold text-gray-700">{{ $contractData['unit'] ?? '—' }} / {{ $contractData['bed'] ?? '—' }}</span>
                            </div>
                            <div class="flex justify-between text-[13px]">
                                <span class="text-gray-400">Lease Period</span>
                                <span class="font-bold text-gray-700">{{ $contractData['start_date'] ?? '—' }} — {{ $contractData['end_date'] ?? '—' }}</span>
                            </div>
                            <div class="flex justify-between text-[13px]">
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
                                        <p class="text-xs font-bold {{ $ownerSignature ? 'text-emerald-600' : 'text-gray-400' }}">Lessor / Manager</p>
                                        <p class="text-[13px] {{ $ownerSignature ? 'text-emerald-500' : 'text-gray-300' }}">{{ $ownerSignature ? 'Signed: ' . $ownerSignedAt : 'Awaiting signature' }}</p>
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
                                        <p class="text-xs font-bold {{ $tenantSignature ? 'text-emerald-600' : 'text-blue-600' }}">Your Signature</p>
                                        <p class="text-[13px] {{ $tenantSignature ? 'text-emerald-500' : 'text-blue-400' }}">{{ $tenantSignature ? 'Signed: ' . $tenantSignedAt : 'Your signature is required' }}</p>
                                    </div>
                                </div>
                                @if($tenantSignature)<img src="{{ asset('storage/' . $tenantSignature) }}" class="h-7 object-contain" alt="Signature">@endif
                            </div>
                        </div>

                        <button wire:click="toggleContract" class="w-full py-2.5 px-4 bg-primary hover:bg-primary/90 text-white font-bold rounded-xl text-[13px] transition-colors flex items-center justify-center gap-2">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                            {{ !$tenantSignature && $ownerSignature ? 'Read & Sign Contract' : 'View Contract' }}
                        </button>

                        @if($contractAgreed)
                            <div class="text-center py-2 px-3 bg-emerald-50/50 rounded-xl mt-3">
                                <p class="text-[13px] font-bold text-emerald-600">Contract Fully Signed</p>
                                <p class="text-[13px] text-emerald-400 mt-0.5">Both parties have signed electronically per RA 8792.</p>
                            </div>
                        @elseif(!$tenantSignature && !$ownerSignature)
                            <div class="text-center py-2 px-3 bg-gray-50/50 rounded-xl mt-3">
                                <p class="text-xs text-gray-400">Waiting for the lessor/manager to sign first.</p>
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
                            wireDisputeMethod="disputeInspectionItem"
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
                        <h4 class="text-xs font-bold text-indigo-500 uppercase tracking-wider mb-4">Clearance & Settlement</h4>

                        <div class="rounded-xl bg-[#F4F7FC] p-3.5 mb-4 space-y-2">
                            <div class="flex justify-between text-[13px]">
                                <span class="text-gray-400">Move-Out Date</span>
                                <span class="font-bold text-gray-700">{{ \Carbon\Carbon::parse($moveOutDate)->format('M d, Y') }}</span>
                            </div>
                            <div class="flex justify-between text-[13px]">
                                <span class="text-gray-400">Security Deposit</span>
                                <span class="font-extrabold text-gray-900">&#8369;{{ number_format($securityDeposit, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-[13px]">
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
                                        <p class="text-xs font-bold {{ $moveOutOwnerSignature ? 'text-emerald-600' : 'text-gray-400' }}">Lessor / Manager</p>
                                        <p class="text-[13px] {{ $moveOutOwnerSignature ? 'text-emerald-500' : 'text-gray-300' }}">{{ $moveOutOwnerSignature ? 'Signed: ' . $moveOutOwnerSignedAt : 'Awaiting signature' }}</p>
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
                                        <p class="text-xs font-bold {{ $moveOutTenantSignature ? 'text-emerald-600' : 'text-blue-600' }}">Your Signature</p>
                                        <p class="text-[13px] {{ $moveOutTenantSignature ? 'text-emerald-500' : 'text-blue-400' }}">{{ $moveOutTenantSignature ? 'Signed: ' . $moveOutTenantSignedAt : 'Your signature is required' }}</p>
                                    </div>
                                </div>
                                @if($moveOutTenantSignature)<img src="{{ asset('storage/' . $moveOutTenantSignature) }}" class="h-7 object-contain" alt="Signature">@endif
                            </div>
                        </div>

                        <button wire:click="toggleMoveOutContract" class="w-full py-2.5 px-4 bg-primary hover:bg-primary/90 text-white font-bold rounded-xl text-[13px] transition-colors flex items-center justify-center gap-2">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                            View Move-Out Contract
                        </button>

                        @if($moveOutContractAgreed)
                            <div class="text-center py-2 px-3 bg-emerald-50/50 rounded-xl mt-3">
                                <p class="text-[13px] font-bold text-emerald-600">Move-Out Contract Fully Signed</p>
                                <p class="text-[13px] text-emerald-400 mt-0.5">Both parties have signed electronically per RA 8792.</p>
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
                            wireDisputeMethod="disputeMoveOutItem"
                            emptyTitle="No move-out inspection data yet"
                            emptyMessage="Items will appear here after the manager records the move-out inspection."
                            :embedded="true"
                        />
                    </div>
                </div>
            </div>
            @endif

        </div>

        {{-- Clearance Checklist Card --}}
        @if($moveOutDate || $moveOutInitiated)
        <div class="bg-white rounded-2xl shadow-[0_1px_3px_rgba(0,0,0,0.04)] overflow-hidden">
            <div class="px-5 py-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center">
                        <svg class="w-[18px] h-[18px] text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </div>
                    <h3 class="text-[15px] font-bold text-gray-900">Clearance Checklist</h3>
                </div>
            </div>
            <div class="px-5 pb-5">
                <div class="space-y-2">
                    @php
                        $checklistItems = [
                            ['label' => 'Documents returned', 'done' => $itemsReturnedConfirmedByTenant],
                            ['label' => 'Bills settled', 'done' => $billsSettled],
                            ['label' => 'Room inspection done', 'done' => $inspectionDone],
                        ];
                    @endphp
                    @foreach($checklistItems as $item)
                        <div class="flex items-center gap-2.5 text-[13px]">
                            @if($item['done'])
                                <span class="w-4 h-4 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-2.5 h-2.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                </span>
                                <span class="text-gray-600">{{ $item['label'] }}</span>
                            @else
                                <span class="w-4 h-4 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>
                                </span>
                                <span class="text-gray-400">{{ $item['label'] }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        @else
            <div class="bg-white rounded-2xl shadow-[0_1px_3px_rgba(0,0,0,0.04)] p-10 text-center">
                <p class="text-sm text-gray-400">No active lease found</p>
            </div>
        @endif

    </div>

    @endif

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- MODALS (available across all tabs)                         --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    @if($showMoveOutContract && $lease)
        <div wire:poll.5s="refreshMoveOutData"></div>
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
                'outstandingBalances' => $t['outstanding_balances'] ?? [],
                'depositRefund' => $t['deposit_refund'] ?? [],
                'signatureMode' => 'tenant',
            ])

            <x-slot:footer>
                @if($moveOutContractAgreed)
                    <button wire:click="downloadMoveOutSignedContract" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-6 rounded-xl text-sm transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                        Download Signed PDF
                    </button>
                @endif
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
        <div wire:poll.5s="refreshContractData"></div>
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
    {{-- PAYMENT REQUEST MODAL (Multi-step, Add Tenant style)       --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    @if($showPaymentModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm" x-data>
            <div class="relative w-full max-w-3xl bg-gray-50 rounded-2xl shadow-xl overflow-hidden max-h-[95vh] flex flex-col">

                {{-- Header --}}
                <div class="bg-[#070589] text-white p-6 flex-shrink-0">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-xl font-bold uppercase">PAY NOW</h2>
                            <p class="mt-1 text-sm text-blue-100">Submit your payment for verification</p>
                        </div>
                        <flux:tooltip :content="'Close payment details'" position="bottom">
                            <button type="button" x-on:click="$dispatch('open-modal', 'cancel-payment-modal')" class="text-white hover:text-blue-200 transition-colors focus:outline-none">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </flux:tooltip>
                    </div>

                    {{-- Stepper --}}
                    @if($paymentStep < 4)
                        <div class="mt-5">
                            <div class="flex items-center justify-between">
                                @php
                                    $paySteps = [
                                        ['num' => 1, 'title' => 'Select Billing'],
                                        ['num' => 2, 'title' => 'Payment Method'],
                                        ['num' => 3, 'title' => 'Submit Proof'],
                                    ];
                                @endphp
                                @foreach($paySteps as $i => $step)
                                    <div class="flex items-center {{ $i < count($paySteps) - 1 ? 'flex-1' : '' }}">
                                        <button
                                            type="button"
                                            wire:click="{{ $step['num'] < $paymentStep ? 'goToPaymentStep(' . $step['num'] . ')' : '' }}"
                                            class="flex flex-col items-center group {{ $paymentStep > $step['num'] ? 'cursor-pointer' : 'cursor-default' }}"
                                        >
                                            <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold border-2 transition-all duration-200
                                                {{ $paymentStep === $step['num'] ? 'bg-white text-[#070589] border-white shadow-lg shadow-white/20' : '' }}
                                                {{ $paymentStep > $step['num'] ? 'bg-white/20 text-white border-white/40' : '' }}
                                                {{ $paymentStep < $step['num'] ? 'bg-transparent text-blue-200 border-blue-300/30' : '' }}">
                                                @if($paymentStep > $step['num'])
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                @else
                                                    {{ $step['num'] }}
                                                @endif
                                            </div>
                                            <span class="text-xs font-semibold mt-1.5 tracking-wide transition-all duration-200
                                                {{ $paymentStep === $step['num'] ? 'text-white' : '' }}
                                                {{ $paymentStep > $step['num'] ? 'text-blue-200' : '' }}
                                                {{ $paymentStep < $step['num'] ? 'text-blue-300/50' : '' }}">{{ $step['title'] }}</span>
                                        </button>
                                        @if($i < count($paySteps) - 1)
                                            <div class="flex-1 mx-2 mt-[-14px]">
                                                <div class="h-0.5 rounded-full bg-blue-300/20 relative overflow-hidden">
                                                    <div class="absolute inset-y-0 left-0 bg-white/60 rounded-full transition-all duration-300 ease-out"
                                                        style="width: {{ $paymentStep > $step['num'] ? '100%' : '0%' }}"></div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Scrollable Content --}}
                <div class="flex-1 overflow-y-auto custom-scrollbar">
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 mx-6 my-6 p-8">

                        {{-- STEP 1: Select Billing --}}
                        @if($paymentStep === 1)
                            <h3 class="text-base font-bold text-[#070589] mb-1">Select Billing</h3>
                            <p class="text-sm text-gray-500 mb-5">Choose which billing you want to pay.</p>

                            @if(count($unpaidBillings) > 0)
                                <div class="space-y-3">
                                    @foreach($unpaidBillings as $billing)
                                        <button
                                            type="button"
                                            wire:click="selectBilling({{ $billing['billing_id'] }})"
                                            class="w-full p-4 rounded-xl border-2 text-left transition-all hover:border-[#2360E8] hover:bg-blue-50/50
                                                {{ $billing['status'] === 'Overdue' ? 'border-red-200 bg-red-50/30' : 'border-gray-200' }}"
                                        >
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="text-sm font-bold text-gray-900">
                                                        {{ \Carbon\Carbon::parse($billing['billing_date'])->format('F Y') }}
                                                    </p>
                                                    <p class="text-xs text-gray-500 mt-0.5">
                                                        Due: {{ $billing['due_date'] ? \Carbon\Carbon::parse($billing['due_date'])->format('M d, Y') : 'N/A' }}
                                                    </p>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-base font-extrabold text-gray-900">&#8369;{{ number_format($billing['to_pay'], 2) }}</p>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase
                                                        {{ $billing['status'] === 'Overdue' ? 'bg-red-100 text-red-600' : 'bg-amber-100 text-amber-600' }}">
                                                        {{ $billing['status'] }}
                                                    </span>
                                                </div>
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-12">
                                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <p class="text-sm text-gray-400">No unpaid billings found.</p>
                                </div>
                            @endif

                        {{-- STEP 2: Payment Method --}}
                        @elseif($paymentStep === 2)
                            @php $selectedBilling = collect($unpaidBillings)->firstWhere('billing_id', $selectedBillingId); @endphp

                            <h3 class="text-base font-bold text-[#070589] mb-1">Payment Method</h3>
                            <p class="text-sm text-gray-500 mb-5">Select how you will pay and follow the instructions.</p>

                            {{-- Selected billing summary --}}
                            @if($selectedBilling)
                                <div class="p-4 rounded-xl bg-[#F4F7FC] border border-gray-200 mb-5">
                                    <div class="grid grid-cols-3 gap-4">
                                        <div>
                                            <p class="text-xs text-gray-500">Billing Period</p>
                                            <p class="text-sm font-bold text-gray-900 mt-0.5">{{ \Carbon\Carbon::parse($selectedBilling['billing_date'])->format('F Y') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">Amount Due</p>
                                            <p class="text-sm font-bold text-gray-900 mt-0.5">&#8369;{{ number_format($selectedBilling['to_pay'], 2) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">Due Date</p>
                                            <p class="text-sm font-bold text-gray-900 mt-0.5">{{ $selectedBilling['due_date'] ? \Carbon\Carbon::parse($selectedBilling['due_date'])->format('M d, Y') : 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Payment methods --}}
                            <label class="text-xs font-semibold text-gray-700 mb-2 block">Choose Payment Method</label>
                            <div class="grid grid-cols-3 gap-3 mb-5">
                                {{-- GCash --}}
                                <button type="button" wire:click="selectPaymentMethod('GCash')"
                                    class="py-3 px-2 rounded-xl border-2 text-center transition-all {{ $selectedPaymentMethod === 'GCash' ? 'border-transparent' : 'border-gray-200' }}"
                                    style="{{ $selectedPaymentMethod === 'GCash' ? 'background-color:#0070E0; color:#fff; border-color:#0070E0;' : '' }}">
                                    <p class="text-sm font-bold" style="color: {{ $selectedPaymentMethod === 'GCash' ? '#fff' : '#0070E0' }}">GCash</p>
                                </button>

                                {{-- Maya --}}
                                <button type="button" wire:click="selectPaymentMethod('Maya')"
                                    class="py-3 px-2 rounded-xl border-2 text-center transition-all {{ $selectedPaymentMethod === 'Maya' ? 'border-transparent' : 'border-gray-200' }}"
                                    style="{{ $selectedPaymentMethod === 'Maya' ? 'background-color:#27AE60; color:#fff; border-color:#27AE60;' : '' }}">
                                    <p class="text-sm font-bold" style="color: {{ $selectedPaymentMethod === 'Maya' ? '#fff' : '#27AE60' }}">Maya</p>
                                </button>

                                {{-- Bank Transfer --}}
                                <button type="button" wire:click="selectPaymentMethod('Bank Transfer')"
                                    class="py-3 px-2 rounded-xl border-2 text-center transition-all {{ $selectedPaymentMethod === 'Bank Transfer' ? 'border-transparent' : 'border-gray-200' }}"
                                    style="{{ $selectedPaymentMethod === 'Bank Transfer' ? 'background-color:#2C3E50; color:#fff; border-color:#2C3E50;' : '' }}">
                                    <p class="text-sm font-bold" style="color: {{ $selectedPaymentMethod === 'Bank Transfer' ? '#fff' : '#2C3E50' }}">Bank Transfer</p>
                                </button>
                            </div>

                            {{-- Payment instructions per method --}}
                            @if($selectedPaymentMethod === 'GCash')
                                <div class="p-4 rounded-xl bg-blue-50/60 border border-blue-100">
                                    <p class="text-xs font-semibold text-[#0070E0] mb-3">Send via GCash</p>
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs text-gray-500">Send to</span>
                                            <span class="text-sm font-bold text-gray-900">{{ $paymentOwnerInfo['owner_name'] ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs text-gray-500">GCash Number</span>
                                            <span class="text-sm font-bold text-[#0070E0]">{{ $paymentOwnerInfo['contact'] ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs text-gray-500">Amount</span>
                                            <span class="text-sm font-bold text-gray-900">&#8369;{{ number_format($selectedBilling['to_pay'] ?? 0, 2) }}</span>
                                        </div>
                                    </div>
                                    <p class="text-[11px] text-gray-400 mt-3 pt-2 border-t border-blue-100">Open GCash → Send Money → enter details above → screenshot the receipt</p>
                                </div>
                            @elseif($selectedPaymentMethod === 'Maya')
                                <div class="p-4 rounded-xl bg-green-50/60 border border-green-100">
                                    <p class="text-xs font-semibold text-[#27AE60] mb-3">Send via Maya</p>
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs text-gray-500">Send to</span>
                                            <span class="text-sm font-bold text-gray-900">{{ $paymentOwnerInfo['owner_name'] ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs text-gray-500">Maya Number</span>
                                            <span class="text-sm font-bold text-[#27AE60]">{{ $paymentOwnerInfo['contact'] ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs text-gray-500">Amount</span>
                                            <span class="text-sm font-bold text-gray-900">&#8369;{{ number_format($selectedBilling['to_pay'] ?? 0, 2) }}</span>
                                        </div>
                                    </div>
                                    <p class="text-[11px] text-gray-400 mt-3 pt-2 border-t border-green-100">Open Maya → Send Money → enter details above → screenshot the receipt</p>
                                </div>
                            @elseif($selectedPaymentMethod === 'Bank Transfer')
                                <div class="p-4 rounded-xl bg-gray-50 border border-gray-200">
                                    <p class="text-xs font-semibold text-[#2C3E50] mb-3">Send via Bank Transfer</p>
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs text-gray-500">Transfer to</span>
                                            <span class="text-sm font-bold text-gray-900">{{ $paymentOwnerInfo['owner_name'] ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs text-gray-500">Amount</span>
                                            <span class="text-sm font-bold text-gray-900">&#8369;{{ number_format($selectedBilling['to_pay'] ?? 0, 2) }}</span>
                                        </div>
                                    </div>
                                    <p class="text-[11px] text-gray-400 mt-3 pt-2 border-t border-gray-200">Contact your property manager for bank account details → transfer → screenshot the receipt</p>
                                </div>
                            @endif

                        {{-- STEP 3: Proof of Payment --}}
                        @elseif($paymentStep === 3)
                            @php $selectedBilling = collect($unpaidBillings)->firstWhere('billing_id', $selectedBillingId); @endphp

                            <h3 class="text-base font-bold text-[#070589] mb-1">Submit Proof of Payment</h3>
                            <p class="text-sm text-gray-500 mb-5">Fill in the details and upload your receipt.</p>

                            {{-- Rejection reason banner --}}
                            @if($resubmitRejectReason)
                                <div class="p-3.5 rounded-xl bg-red-50 border border-red-200 mb-5 flex items-start gap-3">
                                    <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                                    <div>
                                        <p class="text-xs font-bold text-red-700 uppercase tracking-wide">Previous submission rejected</p>
                                        <p class="text-sm text-red-600 mt-1">{{ $resubmitRejectReason }}</p>
                                        <p class="text-xs text-gray-500 mt-1.5">Your previous details are pre-filled below. Update the fields that need correction and resubmit.</p>
                                    </div>
                                </div>
                            @endif

                            {{-- Summary --}}
                            <div class="p-4 rounded-xl bg-[#F4F7FC] border border-gray-200 mb-6">
                                <div class="grid grid-cols-3 gap-4">
                                    <div>
                                        <p class="text-xs text-gray-500">Billing</p>
                                        <p class="text-sm font-bold text-gray-900 mt-0.5">{{ $selectedBilling ? \Carbon\Carbon::parse($selectedBilling['billing_date'])->format('F Y') : '' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Method</p>
                                        <p class="text-sm font-bold text-gray-900 mt-0.5">{{ $selectedPaymentMethod }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Send to</p>
                                        <p class="text-sm font-bold text-[#2360E8] mt-0.5">{{ $paymentOwnerInfo['owner_name'] ?? '' }}</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Form --}}
                            <form wire:submit="submitPaymentRequest">
                                <div class="grid grid-cols-2 gap-4">
                                    {{-- Reference Number --}}
                                    <div>
                                        <label class="text-xs font-semibold text-gray-700">Reference Number</label>
                                        <input type="text" wire:model="paymentReferenceNumber"
                                            class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500"
                                            placeholder="e.g. 1234567890">
                                        @error('paymentReferenceNumber') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>

                                    {{-- Amount Paid --}}
                                    <div>
                                        <label class="text-xs font-semibold text-gray-700">Amount Paid (&#8369;)</label>
                                        <input type="number" step="0.01" wire:model="paymentAmountPaid"
                                            class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500"
                                            placeholder="0.00">
                                        @error('paymentAmountPaid') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                {{-- Proof Image Upload --}}
                                <div class="mt-4"
                                    x-data="{ uploading: false, progress: 0 }"
                                    x-on:livewire-upload-start="uploading = true; progress = 0"
                                    x-on:livewire-upload-finish="uploading = false; progress = 100"
                                    x-on:livewire-upload-cancel="uploading = false"
                                    x-on:livewire-upload-error="uploading = false"
                                    x-on:livewire-upload-progress="progress = $event.detail.progress"
                                >
                                    <label class="text-xs font-semibold text-gray-700">Proof of Payment</label>
                                    <label class="mt-1 flex flex-col items-center justify-center w-full h-40 border-2 border-gray-300 border-dashed rounded-xl cursor-pointer bg-gray-50 hover:bg-gray-100 transition relative overflow-hidden">
                                        @if($paymentProofImage)
                                            <img src="{{ $paymentProofImage->temporaryUrl() }}" alt="Preview" class="absolute inset-0 w-full h-full object-contain p-2">
                                        @elseif($previousProofImagePath)
                                            <img src="{{ asset('storage/' . $previousProofImagePath) }}" alt="Previous proof" class="absolute inset-0 w-full h-full object-contain p-2 opacity-60">
                                            <div class="absolute bottom-1 left-0 right-0 text-center">
                                                <span class="text-[11px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">Previous upload — click to replace</span>
                                            </div>
                                        @else
                                            <div class="flex flex-col items-center justify-center pt-5 pb-6" x-show="!uploading">
                                                <svg class="w-8 h-8 mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                                                <p class="text-xs text-gray-500 font-semibold">Click to upload receipt</p>
                                                <p class="text-xs text-gray-400 mt-0.5">PNG, JPG up to 10MB</p>
                                            </div>
                                        @endif
                                        {{-- Upload progress --}}
                                        <div x-show="uploading" x-cloak class="absolute inset-0 bg-white/80 flex flex-col items-center justify-center">
                                            <div class="w-3/4 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                                <div class="h-full bg-[#2360E8] rounded-full transition-all duration-200" :style="'width: ' + progress + '%'"></div>
                                            </div>
                                            <p class="text-xs text-[#2360E8] font-medium mt-2">Uploading... <span x-text="progress + '%'"></span></p>
                                        </div>
                                        <input type="file" wire:model="paymentProofImage" accept="image/*" class="hidden">
                                    </label>
                                    @error('paymentProofImage') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                </div>
                            </form>

                        {{-- STEP 4: Success --}}
                        @elseif($paymentStep === 4)
                            <div class="text-center py-8">
                                <div class="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900">Payment Submitted!</h3>
                                <p class="text-sm text-gray-500 mt-2 max-w-sm mx-auto">Your proof of payment has been sent to your property manager for verification. You'll be notified once it's confirmed.</p>

                                <div class="mt-6 p-4 rounded-xl bg-amber-50 border border-amber-100 inline-flex items-center gap-2">
                                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <p class="text-sm font-semibold text-amber-700">Status: Pending Verification</p>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>

                {{-- Footer --}}
                <div class="p-6 bg-white border-t border-gray-200 flex justify-between flex-shrink-0">
                    @if($paymentStep === 1)
                        <div></div>
                        <p class="text-xs text-gray-400 self-center">Select a billing to continue</p>
                    @elseif($paymentStep === 2)
                        <button type="button" wire:click="goToPaymentStep(1)"
                            class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3 px-8 rounded-xl text-sm transition-colors">
                            Back
                        </button>
                        @if($selectedPaymentMethod)
                            <button type="button" wire:click="confirmPaymentMethod"
                                class="bg-[#070589] hover:bg-[#000060] text-white font-bold py-3 px-10 rounded-xl text-sm transition-colors shadow-lg">
                                Continue
                            </button>
                        @else
                            <p class="text-xs text-gray-400 self-center">Select a payment method to continue</p>
                        @endif
                    @elseif($paymentStep === 3)
                        <button type="button" wire:click="goToPaymentStep(2)"
                            class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3 px-8 rounded-xl text-sm transition-colors">
                            Back
                        </button>
                        <button type="button" wire:click="submitPaymentRequest"
                            class="bg-[#070589] hover:bg-[#000060] text-white font-bold py-3 px-10 rounded-xl text-sm transition-colors shadow-lg"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-wait">
                            <span wire:loading.remove wire:target="submitPaymentRequest">Submit Payment</span>
                            <span wire:loading wire:target="submitPaymentRequest">Submitting...</span>
                        </button>
                    @elseif($paymentStep === 4)
                        <div></div>
                        <button type="button" wire:click="closePaymentModal"
                            class="bg-[#070589] hover:bg-[#000060] text-white font-bold py-3 px-10 rounded-xl text-sm transition-colors shadow-lg">
                            Done
                        </button>
                    @endif
                </div>

            </div>
        </div>

        {{-- Cancel Payment Confirmation --}}
        <x-ui.modal-cancel
            name="cancel-payment-modal"
            title="Cancel Payment?"
            description="Are you sure you want to cancel? Your payment progress will not be saved."
            discardText="Yes, Cancel"
            returnText="Continue Payment"
            discardAction="closePaymentModal"
        />
    @endif

</div>
