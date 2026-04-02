<div class="font-sans">

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
                            <button
                                wire:click="viewReceipt({{ $payment->billing_id }})"
                                class="inline-flex items-center px-3 py-1 border border-[#0906ae] text-[#0906ae] rounded-md text-xs font-bold hover:bg-blue-50 transition-colors"
                            >
                                View
                            </button>
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
</div>
