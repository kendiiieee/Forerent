<div class="font-sans">
    <x-ui.card-with-tabs
        :tabs="['All' => 'All', 'Pending' => 'Pending', 'Confirmed' => 'Confirmed', 'Rejected' => 'Rejected']"
        :counts="$counts"
        :activeTab="$activeTab"
        wire:model.live="activeTab"
    >
        <x-slot:filters>
            {{-- Search --}}
            <div class="relative w-full sm:w-64">
                <input
                    type="text"
                    placeholder="Search by tenant name or ref..."
                    wire:model.live.debounce.300ms="search"
                    class="w-full bg-white border border-gray-200 rounded-full py-2.5 pl-4 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200 placeholder-gray-400 text-gray-700 transition shadow-sm"
                >
                <svg class="w-4 h-4 text-gray-400 absolute right-4 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            {{-- Month Filter --}}
            <x-dropdown label="{{ $selectedMonth ? ($monthOptions[$selectedMonth] ?? 'Month') : 'Month' }}" tooltip="Filter requests by month">
                <x-dropdown-item wire:click="$set('selectedMonth', '')" @click="open = false">
                    All Months
                </x-dropdown-item>
                @foreach ($monthOptions as $value => $label)
                    <x-dropdown-item
                        wire:click="$set('selectedMonth', '{{ $value }}')"
                        @click="open = false"
                        :active="$selectedMonth === $value"
                    >
                        {{ $label }}
                    </x-dropdown-item>
                @endforeach
            </x-dropdown>

            {{-- Building Filter --}}
            <x-dropdown label="{{ $selectedBuilding ?: 'Building' }}" tooltip="Filter requests by building">
                <x-dropdown-item wire:click="$set('selectedBuilding', '')" @click="open = false">
                    All Buildings
                </x-dropdown-item>
                @foreach ($buildingOptions as $value => $label)
                    <x-dropdown-item
                        wire:click="$set('selectedBuilding', '{{ $value }}')"
                        @click="open = false"
                        :active="$selectedBuilding === $value"
                    >
                        {{ $label }}
                    </x-dropdown-item>
                @endforeach
            </x-dropdown>
        </x-slot:filters>

        {{-- TABLE --}}
        <x-ui.table>
            <x-slot:head>
                <x-ui.th>Tenant</x-ui.th>
                <x-ui.th>Billing Period</x-ui.th>
                <x-ui.th>Amount</x-ui.th>
                <x-ui.th>Method</x-ui.th>
                <x-ui.th>Reference</x-ui.th>
                <x-ui.th>Submitted</x-ui.th>
                <x-ui.th class="text-center">Action</x-ui.th>
            </x-slot:head>

            <x-slot:body>
                @forelse($requests as $req)
                    <x-ui.tr wire:key="pr-{{ $req->id }}"
                        wire:click="viewRequest({{ $req->id }})"
                        class="cursor-pointer hover:bg-gray-50 transition-colors group">

                        <x-ui.td class="group-hover:text-blue-600 font-medium">
                            {{ $req->tenant?->first_name }} {{ $req->tenant?->last_name }}
                            <br><span class="text-[11px] text-gray-400">{{ $req->lease?->bed?->unit?->unit_number ?? '' }}</span>
                        </x-ui.td>

                        <x-ui.td>
                            {{ $req->billing?->billing_date ? \Carbon\Carbon::parse($req->billing->billing_date)->format('M Y') : 'N/A' }}
                        </x-ui.td>

                        <x-ui.td>
                            ₱ {{ number_format($req->amount_paid, 2) }}
                        </x-ui.td>

                        <x-ui.td>{{ $req->payment_method }}</x-ui.td>

                        <x-ui.td>
                            <span class="font-mono">{{ $req->reference_number ?: '—' }}</span>
                        </x-ui.td>

                        <x-ui.td>
                            {{ $req->created_at->format('M d, Y') }}
                            <br><span class="text-[11px] text-gray-400">{{ $req->created_at->format('h:i A') }}</span>
                        </x-ui.td>

                        <x-ui.td class="text-center" @click.stop>
                            <flux:tooltip :content="'Review and manage this payment request'" position="bottom">
                                <button
                                    wire:click.stop="viewRequest({{ $req->id }})"
                                    class="inline-flex items-center px-3 py-1 border border-[#0906ae] text-[#0906ae] rounded-md text-xs font-bold hover:bg-blue-50 transition-colors"
                                >
                                    Review
                                </button>
                            </flux:tooltip>
                        </x-ui.td>
                    </x-ui.tr>
                @empty
                    <x-ui.tr>
                        <x-ui.td colspan="7" class="text-center py-12 text-slate-500">
                            No {{ strtolower($activeTab) }} payment requests.
                        </x-ui.td>
                    </x-ui.tr>
                @endforelse
            </x-slot:body>
        </x-ui.table>

        <x-slot:footer>
            {{ $requests->links('livewire.layouts.components.paginate-blue') }}
        </x-slot:footer>
    </x-ui.card-with-tabs>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- REVIEW MODAL (Add Tenant modal style)                      --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    @if($showDetailModal && $selectedRequest)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm" x-data>
            <div class="relative w-full max-w-3xl bg-gray-50 rounded-2xl shadow-xl overflow-hidden max-h-[95vh] flex flex-col">

                {{-- Header --}}
                <div class="flex-shrink-0 bg-[#2B66F5] text-white px-6 py-5">
                    <div class="flex items-start justify-between">
                        <div class="min-w-0">
                            <p class="text-xs text-blue-200 font-medium mb-0.5 truncate">{{ $selectedRequest['tenant_name'] }}</p>
                            <h2 class="text-2xl font-bold leading-tight">{{ $selectedRequest['billing_period'] }}</h2>
                            <p class="text-sm text-blue-100 mt-0.5 truncate">{{ $selectedRequest['property_name'] }} &middot; Unit {{ $selectedRequest['unit_number'] }}/{{ $selectedRequest['bed_number'] }}</p>
                        </div>
                        <div class="flex-shrink-0 flex items-center gap-3 pt-1">
                            @if($selectedRequest['status'] === 'Pending')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-orange-100 text-orange-700">Pending</span>
                            @elseif($selectedRequest['status'] === 'Confirmed')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700">Confirmed</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700">Rejected</span>
                            @endif
                            <flux:tooltip :content="'Close this review panel'" position="bottom">
                                <button type="button" x-on:click="$dispatch('open-modal', 'cancel-payment-review')" class="text-white/70 hover:text-white transition-colors focus:outline-none">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </flux:tooltip>
                        </div>
                    </div>
                    <p class="text-xs text-blue-200 mt-3 pt-3 border-t border-blue-400/30">
                        Submitted on <span class="font-semibold text-white">{{ \Carbon\Carbon::parse($selectedRequest['created_at'])->format('M d, Y h:i A') }}</span>
                    </p>
                </div>

                {{-- Scrollable Content --}}
                <div class="flex-1 overflow-y-auto custom-scrollbar bg-white" id="payment-review-scroll-container">
                    <div class="p-6 space-y-7">

                        {{-- Payment Summary --}}
                        <div>
                            <h3 class="text-sm font-bold text-[#070642] mb-3 flex items-center gap-2">
                                <span class="w-1 h-4 bg-[#2B66F5] rounded-full"></span>
                                Payment Summary
                            </h3>
                            <div class="bg-[#2672EC] rounded-xl p-5 text-white shadow-lg">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-[11px] opacity-60 uppercase tracking-widest font-bold">Amount Paid</p>
                                        <h3 class="text-2xl font-bold mt-0.5">&#8369;{{ number_format($selectedRequest['amount_paid'], 2) }}</h3>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[11px] opacity-60 uppercase tracking-widest font-bold">Amount Due</p>
                                        <p class="text-lg font-bold mt-0.5">&#8369;{{ number_format($selectedRequest['billing_amount'], 2) }}</p>
                                    </div>
                                </div>
                                @if($selectedRequest['amount_paid'] < $selectedRequest['billing_amount'])
                                    <div class="mt-3 pt-3 border-t border-white/20">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-500/20 text-white text-xs font-bold">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                            </svg>
                                            Short by &#8369;{{ number_format($selectedRequest['billing_amount'] - $selectedRequest['amount_paid'], 2) }}
                                        </span>
                                    </div>
                                @else
                                    <div class="mt-3 pt-3 border-t border-white/20">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-green-500/20 text-white text-xs font-bold">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                            </svg>
                                            Full amount covered
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Payment Details --}}
                        <div>
                            <h3 class="text-sm font-bold text-[#070642] mb-3 flex items-center gap-2">
                                <span class="w-1 h-4 bg-[#2B66F5] rounded-full"></span>
                                Payment Details
                            </h3>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-[#F4F7FF] p-4 rounded-xl border border-blue-50">
                                    <p class="text-gray-400 text-[11px] uppercase font-bold tracking-wide mb-1">Due Date</p>
                                    <p class="text-[#070642] font-semibold text-sm">{{ $selectedRequest['billing_due'] }}</p>
                                </div>
                                <div class="bg-[#F4F7FF] p-4 rounded-xl border border-blue-50">
                                    <p class="text-gray-400 text-[11px] uppercase font-bold tracking-wide mb-1">Payment Method</p>
                                    <p class="text-[#070642] font-semibold text-sm">{{ $selectedRequest['payment_method'] }}</p>
                                </div>
                                <div class="bg-[#F4F7FF] p-4 rounded-xl border border-blue-50 col-span-2">
                                    <p class="text-gray-400 text-[11px] uppercase font-bold tracking-wide mb-1">Reference Number</p>
                                    <p class="text-[#070642] font-semibold text-sm font-mono">{{ $selectedRequest['reference_number'] ?: '—' }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Proof Image --}}
                        <div>
                            <h3 class="text-sm font-bold text-[#070642] mb-3 flex items-center gap-2">
                                <span class="w-1 h-4 bg-[#2B66F5] rounded-full"></span>
                                Proof of Payment
                            </h3>
                            @if(!empty($selectedRequest['proof_image']))
                                <div class="rounded-xl overflow-hidden border border-blue-50 bg-[#F4F7FF] cursor-pointer group relative" onclick="window.open('{{ asset('storage/' . $selectedRequest['proof_image']) }}', '_blank')">
                                    <img
                                        src="{{ asset('storage/' . $selectedRequest['proof_image']) }}"
                                        alt="Proof of payment"
                                        class="w-full max-h-72 object-contain"
                                    >
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors flex items-center justify-center">
                                        <span class="opacity-0 group-hover:opacity-100 transition-opacity bg-black/60 text-white text-xs font-medium px-3 py-1.5 rounded-full">
                                            Click to enlarge
                                        </span>
                                    </div>
                                </div>
                            @else
                                <div class="flex items-center gap-3 p-4 rounded-xl bg-[#F4F7FF] border border-blue-50">
                                    <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                                    </svg>
                                    <p class="text-sm text-gray-500">Cash payment — no proof image attached.</p>
                                </div>
                            @endif
                        </div>

                        {{-- Reviewed info (for non-pending) --}}
                        @if($selectedRequest['status'] !== 'Pending')
                            <div class="flex items-start gap-3 p-4 rounded-xl {{ $selectedRequest['status'] === 'Confirmed' ? 'bg-green-100 border border-green-200' : 'bg-red-100 border border-red-200' }}">
                                @if($selectedRequest['status'] === 'Confirmed')
                                    <svg class="w-5 h-5 text-green-700 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-red-700 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                @endif
                                <div>
                                    <p class="text-sm font-bold {{ $selectedRequest['status'] === 'Confirmed' ? 'text-green-700' : 'text-red-700' }}">
                                        {{ $selectedRequest['status'] }}
                                    </p>
                                    @if($selectedRequest['reviewer_name'])
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $selectedRequest['reviewer_name'] }} &middot; {{ $selectedRequest['reviewed_at'] ? \Carbon\Carbon::parse($selectedRequest['reviewed_at'])->format('M d, Y h:i A') : '' }}</p>
                                    @endif
                                    @if($selectedRequest['reject_reason'])
                                        <p class="text-sm text-red-700 font-medium mt-1">{{ $selectedRequest['reject_reason'] }}</p>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- Reject Form (inline, only when toggled) --}}
                        @if($selectedRequest['status'] === 'Pending' && $showRejectForm)
                            <div class="rounded-xl border border-red-200 bg-red-50 p-4" id="reject-reasons-section">
                                <p class="text-sm font-bold text-[#070642] mb-3">Select reason(s) for rejection</p>
                                <div class="space-y-2">
                                    @foreach(['Amount doesn\'t match', 'Invalid receipt', 'Unreadable photo', 'Duplicate submission', 'Wrong reference number', 'Expired payment', 'Other'] as $reason)
                                        <label class="flex items-center gap-2.5 cursor-pointer group">
                                            <input type="checkbox" wire:model.live="rejectReasons" value="{{ $reason }}"
                                                class="rounded border-gray-300 text-[#070589] focus:ring-[#070589]">
                                            <span class="text-sm text-gray-700 group-hover:text-gray-900">{{ $reason }}</span>
                                        </label>
                                    @endforeach
                                </div>

                                @if(in_array('Other', $rejectReasons))
                                    <textarea
                                        wire:model="rejectOtherReason"
                                        rows="2"
                                        class="w-full mt-3 border-gray-300 rounded-lg text-sm focus:border-[#0030C5] focus:ring-[#0030C5]"
                                        placeholder="Please specify the reason..."
                                    ></textarea>
                                    @error('rejectOtherReason') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                @endif

                                @error('rejectReasons') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        @endif

                    </div>
                </div>

                {{-- Footer --}}
                @if($selectedRequest['status'] === 'Pending')
                    <div class="px-6 py-4 border-t border-gray-100 flex justify-center gap-4 flex-shrink-0">
                        @if($showRejectForm)
                            <button type="button" x-on:click="$dispatch('open-modal', 'cancel-payment-review')"
                                class="flex-1 bg-[#D6E6FF] hover:bg-[#c3daff] text-[#0C0B50] font-bold py-3 rounded-xl text-sm transition-colors">
                                Cancel
                            </button>
                            <button type="button" wire:click="rejectPayment"
                                class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-xl text-sm transition-colors shadow-md">
                                Confirm Rejection
                            </button>
                        @else
                            <button type="button"
                                wire:click="toggleRejectForm"
                                x-on:click="
                                    const scrollToReject = () => {
                                        const el = document.getElementById('reject-reasons-section');
                                        const container = document.getElementById('payment-review-scroll-container');
                                        if (el && container) {
                                            container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
                                        } else {
                                            requestAnimationFrame(scrollToReject);
                                        }
                                    };
                                    setTimeout(scrollToReject, 100);
                                "
                                class="flex-1 bg-[#D6E6FF] hover:bg-[#c3daff] text-[#0C0B50] font-bold py-3 rounded-xl text-sm transition-colors">
                                Reject
                            </button>
                            <button type="button"
                                x-on:click="$dispatch('open-modal', 'confirm-payment-request')"
                                class="flex-1 bg-[#104EA2] hover:bg-[#0d3f82] text-white font-bold py-3 rounded-xl text-sm transition-colors shadow-md">
                                Confirm Payment
                            </button>
                        @endif
                    </div>
                @else
                    <div class="px-6 py-4 border-t border-gray-100 flex justify-end flex-shrink-0">
                        <button type="button" wire:click="closeDetailModal"
                            class="bg-[#D6E6FF] hover:bg-[#c3daff] text-[#0C0B50] font-bold py-3 px-8 rounded-xl text-sm transition-colors">
                            Close
                        </button>
                    </div>
                @endif

            </div>
        </div>
    @endif

    {{-- Confirmation Modal --}}
    <x-ui.modal-confirm
        name="confirm-payment-request"
        title="Confirm Payment"
        description="Are you sure you want to confirm this payment? This will mark the billing as Paid and create a transaction record."
        confirmText="Yes, Confirm"
        cancelText="Cancel"
        confirmAction="confirmPayment"
    />

    {{-- Cancel Review Modal --}}
    <x-ui.modal-cancel
        name="cancel-payment-review"
        title="Discard Unsaved Changes?"
        description="Are you sure you want to close? All unsaved progress will be lost."
        discardText="Discard"
        returnText="Keep Editing"
        discardAction="closeDetailModal"
    />
</div>
