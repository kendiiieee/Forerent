<div class="w-full font-sans">

    {{-- 1. THE LAYOUT SHELL --}}
    <x-ui.card-with-tabs
        :tabs="[
            'payment' => 'Payment History',
            'maintenance' => 'Total Maintenance History'
        ]"
        :activeTab="$activeTab"
    >

        {{-- 2. THE FILTERS --}}
        <x-slot:filters>

            <x-ui.search-bar
                model="search"
                placeholder="Search by name or reference number..."
            />

            {{-- Month Filter --}}
            <x-dropdown label="{{ $monthOptions[$selectedMonth] ?? 'Month' }}" tooltip="Filter records by month">
                <x-dropdown-item wire:click="$set('selectedMonth', null)" @click="open = false">
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

            {{-- Year Filter --}}
            <x-dropdown label="{{ $selectedYear ?? 'Year' }}" tooltip="Filter records by year">
                <x-dropdown-item wire:click="$set('selectedYear', null)" @click="open = false">
                    All Years
                </x-dropdown-item>
                @foreach ($yearOptions as $value => $label)
                    <x-dropdown-item
                        wire:click="$set('selectedYear', {{ $value }})"
                        @click="open = false"
                        :active="$selectedYear == $value"
                    >
                        {{ $label }}
                    </x-dropdown-item>
                @endforeach
            </x-dropdown>

            {{-- Building Filter --}}
            <x-dropdown label="{{ $selectedBuilding ?? 'Building' }}" tooltip="Filter records by building">
                {{-- CLEAR FILTER OPTION --}}
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


        {{-- 3. THE CONTENT TABLES --}}

        {{-- Table A: Payment History --}}
        @if ($activeTab === 'payment')
            <x-ui.table>
                <x-slot:head>
                    <x-ui.th class="w-[22%]">Reference</x-ui.th>
                    <x-ui.th class="w-[20%]">Category</x-ui.th>
                    <x-ui.th class="w-[20%]">Date</x-ui.th>
                    <x-ui.th class="w-[20%]">Amount</x-ui.th>
                    <x-ui.th class="w-[18%]">Action</x-ui.th>
                </x-slot:head>

                <x-slot:body>
                    @foreach ($paymentHistory as $payment)
                        <x-ui.tr>
                            <x-ui.td isHeader="true">{{ $payment->reference_number }}</x-ui.td>
                            <x-ui.td>{{ $payment->category }}</x-ui.td>
                            <x-ui.td>{{ \Carbon\Carbon::parse($payment->transaction_date)->format('F d, Y') }}</x-ui.td>
                            <x-ui.td>₱ {{ number_format($payment->amount, 2) }}</x-ui.td>
                            <x-ui.td>
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
                    @endforeach
                </x-slot:body>
            </x-ui.table>
        @endif

        {{-- Table B: Maintenance History --}}
        @if ($activeTab === 'maintenance')
            <x-ui.table>
                <x-slot:head>
                    <x-ui.th class="w-[10%]">Unit</x-ui.th>
                    <x-ui.th class="w-[20%]">Tenant Name</x-ui.th>
                    <x-ui.th class="w-[15%]">Date</x-ui.th>
                    <x-ui.th class="w-[40%]">Problem</x-ui.th>
                    <x-ui.th class="w-[15%]">Cost</x-ui.th>
                </x-slot:head>

                <x-slot:body>
                    @foreach ($maintenanceHistory as $maintenance)
                        <x-ui.tr>
                            <x-ui.td isHeader="true">{{ $maintenance->unit_number }}</x-ui.td>
                            <x-ui.td>{{ $maintenance->tenant_name }}</x-ui.td>
                            <x-ui.td>{{ \Carbon\Carbon::parse($maintenance->completion_date)->format('F d, Y') }}</x-ui.td>
                            <x-ui.td class="truncate max-w-xs" title="{{ $maintenance->problem }}">
                                {{ $maintenance->problem }}
                            </x-ui.td>
                            <x-ui.td>₱ {{ number_format($maintenance->cost, 2) }}</x-ui.td>
                        </x-ui.tr>
                    @endforeach
                </x-slot:body>
            </x-ui.table>
        @endif

        {{-- 4. FOOTER PAGINATION --}}
        <x-slot:footer>
            @if($activeTab === 'payment')
                {{ $paymentHistory->onEachSide(1)->links('livewire.layouts.components.paginate-blue') }}
            @else
                {{ $maintenanceHistory->onEachSide(1)->links('livewire.layouts.components.paginate-blue') }}
            @endif
        </x-slot:footer>

    </x-ui.card-with-tabs>

    {{-- Payment Receipt Modal --}}
    <livewire:layouts.financials.payment-receipt-modal />
</div>
