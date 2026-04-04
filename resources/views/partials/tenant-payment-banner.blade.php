{{-- Monochrome Blue Payment Banner --}}
<div class="relative overflow-hidden" style="background: linear-gradient(135deg, #020147 0%, #0a0b8a 40%, #1e3fae 70%, #2563eb 100%)">
    {{-- Mesh / glass orbs --}}
    <div class="absolute -right-12 -top-12 w-48 h-48 rounded-full" style="background: radial-gradient(circle, rgba(59,130,246,0.15) 0%, transparent 70%)"></div>
    <div class="absolute right-16 top-4 w-32 h-32 rounded-full" style="background: radial-gradient(circle, rgba(96,165,250,0.1) 0%, transparent 70%)"></div>
    <div class="absolute left-1/4 -bottom-16 w-44 h-44 rounded-full" style="background: radial-gradient(circle, rgba(37,99,235,0.12) 0%, transparent 70%)"></div>
    <div class="absolute left-0 top-0 w-24 h-24 rounded-full" style="background: radial-gradient(circle, rgba(147,197,253,0.06) 0%, transparent 70%)"></div>

    <div class="relative z-10 px-5 sm:px-6 py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <p class="text-xs font-bold text-blue-300/80 uppercase tracking-[0.2em] mb-2">Amount Due This Month</p>
            <p class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                <span class="text-lg font-bold text-white/70 mr-0.5">&#8369;</span>{{ number_format($amountDue, 2) }}
            </p>
            <div class="mt-2.5">
                @if($paymentStatus === 'Paid')
                    <span class="inline-flex items-center gap-1.5 text-[13px] font-semibold text-blue-200">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Paid in full
                    </span>
                @elseif($paymentStatus === 'No Billing')
                    <span class="text-[13px] font-medium text-white/40">No billing issued yet</span>
                @elseif($daysUntilDue > 0)
                    <span class="text-[13px] font-semibold text-blue-200">{{ $daysUntilDue }} {{ $daysUntilDue === 1 ? 'day' : 'days' }} left to pay</span>
                @elseif($daysUntilDue == 0)
                    <span class="text-[13px] font-bold text-red-300 animate-pulse">Due today!</span>
                @else
                    <span class="text-[13px] font-bold text-red-300">{{ abs($daysUntilDue) }} {{ abs($daysUntilDue) === 1 ? 'day' : 'days' }} overdue</span>
                @endif
            </div>
        </div>
        <div class="flex flex-col items-end gap-2.5">
            @if($paymentStatus !== 'No Billing')
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider
                    {{ $paymentStatus === 'Paid' ? 'bg-white/15 text-blue-200 ring-1 ring-white/20' : '' }}
                    {{ $paymentStatus === 'Unpaid' ? 'bg-white/10 text-blue-200 ring-1 ring-white/15' : '' }}
                    {{ $paymentStatus === 'Overdue' ? 'bg-red-500/20 text-red-200 ring-1 ring-red-400/30' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full mr-1.5
                        {{ $paymentStatus === 'Paid' ? 'bg-blue-300' : '' }}
                        {{ $paymentStatus === 'Unpaid' ? 'bg-blue-300' : '' }}
                        {{ $paymentStatus === 'Overdue' ? 'bg-red-400' : '' }}"></span>
                    {{ $paymentStatus }}
                </span>
            @endif
            @if(in_array($paymentStatus, ['Unpaid', 'Overdue']) && count($pendingPaymentRequests) === 0)
                <button wire:click="openPaymentModal" class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl bg-white text-[#070589] text-[13px] font-bold uppercase tracking-wide transition hover:bg-blue-50 shadow-lg shadow-black/10">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Pay Now
                </button>
            @elseif(count($pendingPaymentRequests) > 0)
                <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-lg bg-white/10 text-blue-200 text-[13px] font-bold uppercase tracking-wide ring-1 ring-white/15">
                    <svg class="w-3.5 h-3.5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Pending Verification
                </span>
            @endif
        </div>
    </div>
</div>
