<div class="font-sans">

    {{-- PAYMENT BANNER --}}
    @if($paymentStatus !== 'No Billing')
        <div class="mb-6 rounded-2xl overflow-hidden">
            @include('partials.tenant-payment-banner')
        </div>
    @endif

    {{-- REJECTED PAYMENT REQUESTS --}}
    @if(count($rejectedPaymentRequests) > 0)
        <div class="mb-6 bg-white rounded-2xl border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-50 flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-red-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </div>
                <h3 class="text-sm font-bold text-gray-900">Rejected Payments — Resubmit Required</h3>
            </div>
            <div class="px-5 py-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
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

    {{-- TITLE LABEL --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-[#070642]">Payment Receipts</h2>
    </div>

    <x-ui.card-with-tabs
        :tabs="[
            'all' => 'All',
            'upcoming' => 'Upcoming',
            'paid' => 'Paid',
            'unpaid' => 'Unpaid'
        ]"
        :counts="$counts"
        :activeTab="$activeTab"
        wire:model.live="activeTab"
    >
        <x-slot:filters>
            <x-ui.search-bar
                model="search"
                placeholder="Search by reference number or category..."
                :suggestions="$suggestions"
            />
            <x-ui.sort-dropdown model="sortOrder" :current="$sortOrder" />
        </x-slot:filters>

        {{-- TABLE --}}
        <x-ui.table>
            <x-slot:head>
                <x-ui.th>Reference Number</x-ui.th>
                <x-ui.th>Category</x-ui.th>
                <x-ui.th>Billing Date</x-ui.th>
                <x-ui.th>Transaction Date</x-ui.th>
                <x-ui.th>Total Amount</x-ui.th>
                <x-ui.th class="text-center">Action</x-ui.th>
            </x-slot:head>

            <x-slot:body>
                @forelse ($payments as $payment)
                    <x-ui.tr>
                        <x-ui.td isHeader="true">{{ $payment->reference_number ?? '—' }}</x-ui.td>
                        <x-ui.td>{{ $payment->category ?? 'Rent Payment' }}</x-ui.td>
                        <x-ui.td>
                            {{ \Carbon\Carbon::parse($payment->billing_date)->format('F d, Y') }}
                        </x-ui.td>
                        <x-ui.td>
                            @if($payment->transaction_date)
                                {{ \Carbon\Carbon::parse($payment->transaction_date)->format('F d, Y') }}
                            @else
                                —
                            @endif
                        </x-ui.td>
                        <x-ui.td class="font-bold text-[#070642]">₱ {{ number_format($payment->to_pay, 2) }}</x-ui.td>
                        <x-ui.td class="text-center">
                            <flux:tooltip :content="'View payment receipt and details'" position="bottom">
                                <button
                                    wire:click="viewReceipt({{ $payment->billing_id }})"
                                    class="inline-flex items-center px-3 py-1 border border-[#0906ae] text-[#0906ae] rounded-md text-xs font-bold hover:bg-blue-50 transition-colors"
                                >
                                    View
                                </button>
                            </flux:tooltip>
                        </x-ui.td>
                    </x-ui.tr>
                @empty
                    <tr><td colspan="6" class="text-center py-8 text-gray-500">No records found.</td></tr>
                @endforelse
            </x-slot:body>
        </x-ui.table>

        <x-slot:footer>
            {{ $payments->onEachSide(1)->links('livewire.layouts.components.paginate-blue') }}
        </x-slot:footer>
    </x-ui.card-with-tabs>

    <livewire:layouts.financials.payment-receipt-modal />

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- PAY NOW MODAL                                              --}}
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
                                        <button type="button"
                                            wire:click="{{ $step['num'] < $paymentStep ? 'goToPaymentStep(' . $step['num'] . ')' : '' }}"
                                            class="flex flex-col items-center group {{ $paymentStep > $step['num'] ? 'cursor-pointer' : 'cursor-default' }}">
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
                                        <button type="button" wire:click="selectBilling({{ $billing['billing_id'] }})"
                                            class="w-full p-4 rounded-xl border-2 text-left transition-all hover:border-[#2360E8] hover:bg-blue-50/50
                                                {{ $billing['status'] === 'Overdue' ? 'border-red-200 bg-red-50/30' : 'border-gray-200' }}">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="text-sm font-bold text-gray-900">{{ \Carbon\Carbon::parse($billing['billing_date'])->format('F Y') }}</p>
                                                    <p class="text-xs text-gray-500 mt-0.5">Due: {{ $billing['due_date'] ? \Carbon\Carbon::parse($billing['due_date'])->format('M d, Y') : 'N/A' }}</p>
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

                            <label class="text-xs font-semibold text-gray-700 mb-2 block">Choose Payment Method</label>
                            <div class="grid grid-cols-3 gap-3 mb-5">
                                <button type="button" wire:click="selectPaymentMethod('GCash')"
                                    class="py-3 px-2 rounded-xl border-2 text-center transition-all {{ $selectedPaymentMethod === 'GCash' ? 'border-transparent' : 'border-gray-200' }}"
                                    style="{{ $selectedPaymentMethod === 'GCash' ? 'background-color:#0070E0; color:#fff; border-color:#0070E0;' : '' }}">
                                    <p class="text-sm font-bold" style="color: {{ $selectedPaymentMethod === 'GCash' ? '#fff' : '#0070E0' }}">GCash</p>
                                </button>
                                <button type="button" wire:click="selectPaymentMethod('Maya')"
                                    class="py-3 px-2 rounded-xl border-2 text-center transition-all {{ $selectedPaymentMethod === 'Maya' ? 'border-transparent' : 'border-gray-200' }}"
                                    style="{{ $selectedPaymentMethod === 'Maya' ? 'background-color:#27AE60; color:#fff; border-color:#27AE60;' : '' }}">
                                    <p class="text-sm font-bold" style="color: {{ $selectedPaymentMethod === 'Maya' ? '#fff' : '#27AE60' }}">Maya</p>
                                </button>
                                <button type="button" wire:click="selectPaymentMethod('Bank Transfer')"
                                    class="py-3 px-2 rounded-xl border-2 text-center transition-all {{ $selectedPaymentMethod === 'Bank Transfer' ? 'border-transparent' : 'border-gray-200' }}"
                                    style="{{ $selectedPaymentMethod === 'Bank Transfer' ? 'background-color:#2C3E50; color:#fff; border-color:#2C3E50;' : '' }}">
                                    <p class="text-sm font-bold" style="color: {{ $selectedPaymentMethod === 'Bank Transfer' ? '#fff' : '#2C3E50' }}">Bank Transfer</p>
                                </button>
                            </div>

                            @if($selectedPaymentMethod === 'GCash')
                                <div class="p-4 rounded-xl bg-blue-50/60 border border-blue-100">
                                    <p class="text-xs font-semibold text-[#0070E0] mb-3">Send via GCash</p>
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between"><span class="text-xs text-gray-500">Send to</span><span class="text-sm font-bold text-gray-900">{{ $paymentOwnerInfo['owner_name'] ?? 'N/A' }}</span></div>
                                        <div class="flex items-center justify-between"><span class="text-xs text-gray-500">GCash Number</span><span class="text-sm font-bold text-[#0070E0]">{{ $paymentOwnerInfo['contact'] ?? 'N/A' }}</span></div>
                                        <div class="flex items-center justify-between"><span class="text-xs text-gray-500">Amount</span><span class="text-sm font-bold text-gray-900">&#8369;{{ number_format($selectedBilling['to_pay'] ?? 0, 2) }}</span></div>
                                    </div>
                                    <p class="text-[11px] text-gray-400 mt-3 pt-2 border-t border-blue-100">Open GCash → Send Money → enter details above → screenshot the receipt</p>
                                </div>
                            @elseif($selectedPaymentMethod === 'Maya')
                                <div class="p-4 rounded-xl bg-green-50/60 border border-green-100">
                                    <p class="text-xs font-semibold text-[#27AE60] mb-3">Send via Maya</p>
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between"><span class="text-xs text-gray-500">Send to</span><span class="text-sm font-bold text-gray-900">{{ $paymentOwnerInfo['owner_name'] ?? 'N/A' }}</span></div>
                                        <div class="flex items-center justify-between"><span class="text-xs text-gray-500">Maya Number</span><span class="text-sm font-bold text-[#27AE60]">{{ $paymentOwnerInfo['contact'] ?? 'N/A' }}</span></div>
                                        <div class="flex items-center justify-between"><span class="text-xs text-gray-500">Amount</span><span class="text-sm font-bold text-gray-900">&#8369;{{ number_format($selectedBilling['to_pay'] ?? 0, 2) }}</span></div>
                                    </div>
                                    <p class="text-[11px] text-gray-400 mt-3 pt-2 border-t border-green-100">Open Maya → Send Money → enter details above → screenshot the receipt</p>
                                </div>
                            @elseif($selectedPaymentMethod === 'Bank Transfer')
                                <div class="p-4 rounded-xl bg-gray-50 border border-gray-200">
                                    <p class="text-xs font-semibold text-[#2C3E50] mb-3">Send via Bank Transfer</p>
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between"><span class="text-xs text-gray-500">Transfer to</span><span class="text-sm font-bold text-gray-900">{{ $paymentOwnerInfo['owner_name'] ?? 'N/A' }}</span></div>
                                        <div class="flex items-center justify-between"><span class="text-xs text-gray-500">Amount</span><span class="text-sm font-bold text-gray-900">&#8369;{{ number_format($selectedBilling['to_pay'] ?? 0, 2) }}</span></div>
                                    </div>
                                    <p class="text-[11px] text-gray-400 mt-3 pt-2 border-t border-gray-200">Contact your property manager for bank account details → transfer → screenshot the receipt</p>
                                </div>
                            @endif

                        {{-- STEP 3: Proof of Payment --}}
                        @elseif($paymentStep === 3)
                            @php $selectedBilling = collect($unpaidBillings)->firstWhere('billing_id', $selectedBillingId); @endphp

                            <h3 class="text-base font-bold text-[#070589] mb-1">Submit Proof of Payment</h3>
                            <p class="text-sm text-gray-500 mb-5">Fill in the details and upload your receipt.</p>

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

                            <form wire:submit="submitPaymentRequest">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-xs font-semibold text-gray-700">Reference Number</label>
                                        <input type="text" wire:model="paymentReferenceNumber"
                                            class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500"
                                            placeholder="e.g. 1234567890">
                                        @error('paymentReferenceNumber') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-gray-700">Amount Paid (&#8369;)</label>
                                        <input type="number" step="0.01" wire:model="paymentAmountPaid"
                                            class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500"
                                            placeholder="0.00">
                                        @error('paymentAmountPaid') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                </div>

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
