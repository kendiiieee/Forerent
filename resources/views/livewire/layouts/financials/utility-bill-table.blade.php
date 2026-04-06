<div class="font-sans">
    <x-ui.card-with-tabs
         :tabs="['all' => 'All', 'electricity' => 'Electricity', 'water' => 'Water']"
         :counts="$counts"
         :activeTab="$activeTab"
         wire:model.live="activeTab"
    >
        {{-- FILTERS SLOT --}}
        <x-slot:filters>
            {{-- Search --}}
            <x-ui.search-bar
                model="search"
                placeholder="Search by building name..."
                :suggestions="$suggestions"
            />

            {{-- Month Filter --}}
            <x-dropdown label="{{ $monthOptions[$selectedMonth] ?? 'Month' }}" tooltip="Filter bills by month">
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
            <x-dropdown label="{{ $selectedBuilding ? Str::before($selectedBuilding, ' ') . '...' : 'Building' }}" tooltip="Filter bills by building">
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

        {{-- TABLE SLOT --}}
        <x-ui.table>
            <x-slot:head>
                <x-ui.th class="w-[22%]">Building</x-ui.th>
                <x-ui.th class="w-[12%]">Unit</x-ui.th>
                <x-ui.th class="w-[12%]">Type</x-ui.th>
                <x-ui.th class="w-[12%]">Period</x-ui.th>
                <x-ui.th class="w-[14%]">Total</x-ui.th>
                <x-ui.th class="w-[10%]">Tenants</x-ui.th>
                <x-ui.th class="w-[18%]">Per Tenant</x-ui.th>
            </x-slot:head>

            <x-slot:body>
                @forelse ($bills as $bill)
                    <x-ui.tr wire:key="bill-{{ $bill->utility_bill_id }}">
                        <x-ui.td class="font-medium">
                            {{ $bill->unit->property->building_name ?? '—' }}
                        </x-ui.td>

                        <x-ui.td>
                            Unit {{ $bill->unit->unit_number ?? '—' }}
                        </x-ui.td>

                        <x-ui.td>
                            <span class="px-2 py-1 rounded-full text-xs font-bold {{ $bill->utility_type === 'electricity' ? 'bg-orange-100 text-orange-600' : 'bg-blue-100 text-blue-700' }}">
                                {{ ucfirst($bill->utility_type) }}
                            </span>
                        </x-ui.td>

                        <x-ui.td>
                            {{ \Carbon\Carbon::parse($bill->billing_period)->format('F Y') }}
                        </x-ui.td>

                        <x-ui.td>
                            &#8369; {{ number_format($bill->total_amount, 2) }}
                        </x-ui.td>

                        <x-ui.td class="text-center">
                            {{ $bill->tenant_count }}
                        </x-ui.td>

                        <x-ui.td>
                            &#8369; {{ number_format($bill->per_tenant_amount, 2) }}
                        </x-ui.td>
                    </x-ui.tr>
                @empty
                    <x-ui.tr>
                        <x-ui.td colspan="7" class="text-center py-12 text-slate-500">
                            No utility bills found.
                        </x-ui.td>
                    </x-ui.tr>
                @endforelse
            </x-slot:body>
        </x-ui.table>

        <x-slot:footer>
            {{ $bills->links('livewire.layouts.components.paginate-blue') }}
        </x-slot:footer>
    </x-ui.card-with-tabs>
</div>
