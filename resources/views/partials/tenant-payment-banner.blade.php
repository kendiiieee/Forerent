<div class="relative overflow-hidden rounded-t-2xl bg-gradient-to-r from-blue-950 via-blue-800 to-blue-600">
    <div class="absolute -right-10 -top-10 w-40 h-40 rounded-full bg-white/[0.06]"></div>
    <div class="absolute -right-5 top-8 w-28 h-28 rounded-full bg-white/[0.04]"></div>
    <div class="absolute left-1/3 -bottom-12 w-36 h-36 rounded-full bg-blue-400/[0.08]"></div>

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
        <div class="flex flex-col items-end gap-2">
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
            @if(in_array($paymentStatus, ['Unpaid', 'Overdue']) && count($pendingPaymentRequests) === 0)
                <button wire:click="openPaymentModal" class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-lg bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white text-[11px] font-bold uppercase tracking-wide transition ring-1 ring-white/25">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Pay Now
                </button>
            @elseif(count($pendingPaymentRequests) > 0)
                <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-lg bg-amber-400/20 text-amber-300 text-[11px] font-bold uppercase tracking-wide ring-1 ring-amber-400/30">
                    <svg class="w-3.5 h-3.5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Pending Verification
                </span>
            @endif
        </div>
    </div>
</div>
