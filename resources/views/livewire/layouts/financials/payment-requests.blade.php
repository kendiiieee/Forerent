<div class="font-sans">
    <x-ui.card-with-tabs
        :tabs="['Pending' => 'Pending', 'Confirmed' => 'Confirmed', 'Rejected' => 'Rejected']"
        :counts="$counts"
        :activeTab="$activeTab"
        wire:model.live="activeTab"
    >
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
                            <br><span class="text-[10px] text-gray-400">{{ $req->lease?->bed?->unit?->unit_number ?? '' }}</span>
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
                            <br><span class="text-[10px] text-gray-400">{{ $req->created_at->format('h:i A') }}</span>
                        </x-ui.td>

                        <x-ui.td class="text-center" @click.stop>
                            <button
                                wire:click.stop="viewRequest({{ $req->id }})"
                                class="inline-flex items-center px-3 py-1 border border-[#0906ae] text-[#0906ae] rounded-md text-xs font-bold hover:bg-blue-50 transition-colors"
                            >
                                Review
                            </button>
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
                <div class="bg-[#070589] text-white p-6 flex-shrink-0">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-xl font-bold uppercase">PAYMENT REVIEW</h2>
                            <p class="mt-1 text-sm text-blue-100">{{ $selectedRequest['tenant_name'] }} &middot; {{ $selectedRequest['property_name'] }}</p>
                        </div>
                        <button type="button" wire:click="closeDetailModal" class="text-white hover:text-blue-200 transition-colors focus:outline-none">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Scrollable Content --}}
                <div class="flex-1 overflow-y-auto custom-scrollbar">
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 mx-6 my-6 p-8">

                        {{-- Tenant & Property Info --}}
                        <h3 class="text-base font-bold text-[#070589] mb-4">Tenant Information</h3>
                        <div class="grid grid-cols-3 gap-4 mb-6">
                            <div>
                                <label class="text-xs font-semibold text-gray-700">Tenant</label>
                                <p class="text-sm font-bold text-gray-900 mt-1">{{ $selectedRequest['tenant_name'] }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-700">Unit / Bed</label>
                                <p class="text-sm font-bold text-gray-900 mt-1">{{ $selectedRequest['unit_number'] }} / {{ $selectedRequest['bed_number'] }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-700">Property</label>
                                <p class="text-sm font-bold text-gray-900 mt-1">{{ $selectedRequest['property_name'] }}</p>
                            </div>
                        </div>

                        {{-- Billing Info --}}
                        <h3 class="text-base font-bold text-[#070589] mb-4">Billing Information</h3>
                        <div class="grid grid-cols-3 gap-4 mb-6">
                            <div>
                                <label class="text-xs font-semibold text-gray-700">Billing Period</label>
                                <p class="text-sm font-bold text-gray-900 mt-1">{{ $selectedRequest['billing_period'] }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-700">Amount Due</label>
                                <p class="text-sm font-bold text-gray-900 mt-1">&#8369;{{ number_format($selectedRequest['billing_amount'], 2) }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-700">Due Date</label>
                                <p class="text-sm font-bold text-gray-900 mt-1">{{ $selectedRequest['billing_due'] }}</p>
                            </div>
                        </div>

                        {{-- Payment Details --}}
                        <h3 class="text-base font-bold text-[#070589] mb-4">Payment Details</h3>
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="text-xs font-semibold text-gray-700">Payment Method</label>
                                <p class="text-sm font-bold text-gray-900 mt-1">{{ $selectedRequest['payment_method'] }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-700">Reference Number</label>
                                <p class="text-sm font-bold text-gray-900 mt-1 font-mono">{{ $selectedRequest['reference_number'] ?: '—' }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-700">Amount Paid</label>
                                <p class="text-sm font-extrabold mt-1 {{ $selectedRequest['amount_paid'] >= $selectedRequest['billing_amount'] ? 'text-emerald-600' : 'text-red-600' }}">
                                    &#8369;{{ number_format($selectedRequest['amount_paid'], 2) }}
                                    @if($selectedRequest['amount_paid'] < $selectedRequest['billing_amount'])
                                        <span class="text-xs font-normal text-red-500">(Short by &#8369;{{ number_format($selectedRequest['billing_amount'] - $selectedRequest['amount_paid'], 2) }})</span>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-700">Date Submitted</label>
                                <p class="text-sm font-bold text-gray-900 mt-1">{{ \Carbon\Carbon::parse($selectedRequest['created_at'])->format('M d, Y h:i A') }}</p>
                            </div>
                        </div>

                        {{-- Proof Image --}}
                        <h3 class="text-base font-bold text-[#070589] mb-4">Proof of Payment</h3>
                        <div class="rounded-xl overflow-hidden border border-gray-200 bg-gray-50 mb-6">
                            <img
                                src="{{ asset('storage/' . $selectedRequest['proof_image']) }}"
                                alt="Proof of payment"
                                class="w-full max-h-80 object-contain cursor-pointer"
                                onclick="window.open(this.src, '_blank')"
                            >
                        </div>
                        <p class="text-[10px] text-gray-400 mb-6">Click image to view full size</p>

                        {{-- Reviewed info (for non-pending) --}}
                        @if($selectedRequest['status'] !== 'Pending')
                            <div class="p-4 rounded-xl {{ $selectedRequest['status'] === 'Confirmed' ? 'bg-emerald-50 border border-emerald-100' : 'bg-red-50 border border-red-100' }}">
                                <p class="text-xs font-bold uppercase tracking-wider {{ $selectedRequest['status'] === 'Confirmed' ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ $selectedRequest['status'] }}
                                </p>
                                @if($selectedRequest['reviewer_name'])
                                    <p class="text-xs text-gray-500 mt-1">By: {{ $selectedRequest['reviewer_name'] }} &middot; {{ $selectedRequest['reviewed_at'] ? \Carbon\Carbon::parse($selectedRequest['reviewed_at'])->format('M d, Y h:i A') : '' }}</p>
                                @endif
                                @if($selectedRequest['reject_reason'])
                                    <p class="text-sm text-red-600 font-medium mt-1">Reason: {{ $selectedRequest['reject_reason'] }}</p>
                                @endif
                            </div>
                        @endif

                        {{-- Reject Form (inline, only when toggled) --}}
                        @if($selectedRequest['status'] === 'Pending' && $showRejectForm)
                            <div class="mt-4">
                                <label class="text-xs font-semibold text-gray-700">Reason for Rejection</label>
                                <textarea
                                    wire:model="rejectReason"
                                    rows="3"
                                    class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="e.g. Amount doesn't match, Invalid receipt, Unreadable photo..."
                                ></textarea>
                                @error('rejectReason') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                        @endif

                    </div>
                </div>

                {{-- Footer --}}
                @if($selectedRequest['status'] === 'Pending')
                    <div class="p-6 bg-white border-t border-gray-200 flex justify-between flex-shrink-0">
                        @if($showRejectForm)
                            <button type="button" wire:click="toggleRejectForm"
                                class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3 px-8 rounded-xl text-sm transition-colors">
                                Cancel
                            </button>
                            <button type="button" wire:click="rejectPayment"
                                class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-10 rounded-xl text-sm transition-colors shadow-lg">
                                Confirm Rejection
                            </button>
                        @else
                            <button type="button" wire:click="toggleRejectForm"
                                class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-10 rounded-xl text-sm transition-colors shadow-lg">
                                Reject
                            </button>
                            <button type="button"
                                x-on:click="$dispatch('open-modal', 'confirm-payment-request')"
                                class="bg-[#070589] hover:bg-[#000060] text-white font-bold py-3 px-10 rounded-xl text-sm transition-colors shadow-lg">
                                Confirm Payment
                            </button>
                        @endif
                    </div>
                @else
                    <div class="p-6 bg-white border-t border-gray-200 flex justify-end flex-shrink-0">
                        <button type="button" wire:click="closeDetailModal"
                            class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3 px-8 rounded-xl text-sm transition-colors">
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
</div>
