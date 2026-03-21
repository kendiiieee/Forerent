<div class="min-h-screen bg-[#F4F7FE] p-4 md:p-6 font-sans">

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
        :activeTab="$activeTab"
    >
        {{-- DROPDOWNS (In the Right Slot) --}}
        <x-slot:filters>
            {{-- Month Filter --}}
            <x-dropdown label="{{ $monthOptions[$selectedMonth] ?? 'Month' }}">
                <x-dropdown-item wire:click="$set('selectedMonth', null)" @click="open = false">
                    All Months
                </x-dropdown-item>
                @foreach ($monthOptions as $value => $label)
                    <x-dropdown-item
                        wire:click="$set('selectedMonth', {{ $value }})"
                        @click="open = false"
                        :active="$selectedMonth == $value"
                    >
                        {{ $label }}
                    </x-dropdown-item>
                @endforeach
            </x-dropdown>

            {{-- Building Filter --}}
            <x-dropdown label="{{ $selectedBuilding ?? 'Building' }}">
                <x-dropdown-item wire:click="$set('selectedBuilding', null)" @click="open = false">
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
                <x-ui.th>Reference Number</x-ui.th>
                <x-ui.th>Category</x-ui.th>
                <x-ui.th>Billing Date</x-ui.th>
                <x-ui.th>Transaction Date</x-ui.th>
                <x-ui.th>Total Amount</x-ui.th>
            </x-slot:head>

            <x-slot:body>
                @forelse ($payments as $payment)
                    <x-ui.tr>
                        <x-ui.td isHeader="true">{{ $payment->reference_number }}</x-ui.td>
                        <x-ui.td>{{ $payment->category }}</x-ui.td>
                        <x-ui.td>
                            @if($payment->category == 'Rent Payment')
                                {{ \Carbon\Carbon::parse($payment->billing->billing_date)->format('F d, Y') }}
                            @else
                                N/A
                            @endif
                        </x-ui.td>
                        <x-ui.td>{{ \Carbon\Carbon::parse($payment->transaction_date)->format('F d, Y') }}</x-ui.td>
                        <x-ui.td class="font-bold text-[#070642]">₱ {{ number_format($payment->amount, 2) }}</x-ui.td>

                    </x-ui.tr>
                @empty
                    <tr><td colspan="5" class="text-center py-8 text-gray-500">No records found.</td></tr>
                @endforelse
            </x-slot:body>
        </x-ui.table>

        <x-slot:footer>
            {{ $payments->onEachSide(1)->links('livewire.layouts.components.paginate-blue') }}
        </x-slot:footer>
    </x-ui.card-with-tabs>
</div>
