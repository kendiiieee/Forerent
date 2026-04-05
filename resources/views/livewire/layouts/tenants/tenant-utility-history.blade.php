<div class="font-sans">
    <x-ui.card-with-tabs
         :tabs="['all' => 'All', 'electricity' => 'Electricity', 'water' => 'Water']"
         :counts="$counts"
         :activeTab="$activeTab"
         wire:model.live="activeTab"
    >
        {{-- FILTERS SLOT --}}
        <x-slot:filters>
            <x-dropdown label="{{ $monthOptions[$selectedMonth] ?? 'Month' }}" tooltip="Filter utility history by month">
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
        </x-slot:filters>

        {{-- TABLE SLOT --}}
        <x-ui.table>
            <x-slot:head>
                <x-ui.th>Type</x-ui.th>
                <x-ui.th>Period</x-ui.th>
                <x-ui.th>Your Share</x-ui.th>
                <x-ui.th class="text-center w-[80px]">Details</x-ui.th>
            </x-slot:head>

            <x-slot:body>
                @forelse ($items as $item)
                    @php
                        $billing = $item->billing;
                        $isElectricity = $item->charge_type === 'electricity_share';
                        $isExpanded = $expandedRow === $item->billing_item_id;
                        $rowColor = $isElectricity ? 'orange' : 'blue';
                    @endphp

                    {{-- Main Row --}}
                    <tr
                        wire:key="utility-{{ $item->billing_item_id }}"
                        wire:click="toggleRow({{ $item->billing_item_id }})"
                        class="cursor-pointer transition-colors focus:outline-none
                            {{ $isExpanded
                                ? ($isElectricity ? 'bg-orange-50' : 'bg-blue-50')
                                : 'hover:bg-gray-50' }}"
                    >
                        <x-ui.td>
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $isElectricity ? 'bg-orange-100 text-orange-600' : 'bg-blue-100 text-blue-700' }}">
                                {{ $isElectricity ? 'Electricity' : 'Water' }}
                            </span>
                        </x-ui.td>

                        <x-ui.td class="font-medium text-gray-900">
                            {{ $billing ? \Carbon\Carbon::parse($billing->billing_date)->format('F Y') : '—' }}
                        </x-ui.td>

                        <x-ui.td class="font-bold text-[#070642]">
                            &#8369; {{ number_format($item->amount, 2) }}
                        </x-ui.td>

                        <x-ui.td class="text-center">
                            <svg class="w-4 h-4 mx-auto transition-transform duration-200 {{ $isExpanded ? 'rotate-180' : '' }} {{ $isElectricity ? 'text-orange-400' : 'text-blue-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </x-ui.td>
                    </tr>

                    {{-- Expanded Breakdown Row --}}
                    @if($isExpanded)
                        <tr wire:key="expanded-{{ $item->billing_item_id }}" class="{{ $isElectricity ? 'bg-orange-50' : 'bg-blue-50' }}">
                            <td colspan="4" class="px-4 pb-4 pt-0">
                                <div class="rounded-xl bg-white shadow-sm border {{ $isElectricity ? 'border-orange-200' : 'border-blue-200' }} p-4 relative">
                                    {{-- Colored left accent bar --}}
                                    <div class="absolute left-0 top-3 bottom-3 w-1 rounded-full {{ $isElectricity ? 'bg-orange-400' : 'bg-blue-400' }}"></div>

                                    <div class="pl-4">
                                        <div class="flex items-center gap-2 mb-3">
                                            <div class="w-6 h-6 rounded-full flex items-center justify-center {{ $isElectricity ? 'bg-orange-100' : 'bg-blue-100' }}">
                                                @if($isElectricity)
                                                    <svg class="w-3.5 h-3.5 text-orange-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/>
                                                    </svg>
                                                @else
                                                    <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418"/>
                                                    </svg>
                                                @endif
                                            </div>
                                            <p class="text-sm font-bold {{ $isElectricity ? 'text-orange-700' : 'text-blue-700' }}">
                                                {{ $isElectricity ? 'Electricity' : 'Water' }} Breakdown
                                            </p>
                                            <span class="text-xs text-gray-400 font-medium">{{ $billing ? \Carbon\Carbon::parse($billing->billing_date)->format('F Y') : '' }}</span>
                                        </div>

                                        @if($expandedBill)
                                            <div class="grid grid-cols-3 gap-3">
                                                <div class="rounded-lg p-3 bg-gray-50">
                                                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Total Unit Bill</p>
                                                    <p class="text-base font-extrabold text-gray-900">&#8369; {{ number_format($expandedBill->total_amount, 2) }}</p>
                                                </div>
                                                <div class="rounded-lg p-3 bg-gray-50">
                                                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Split Between</p>
                                                    <p class="text-base font-extrabold text-gray-900">{{ $expandedBill->tenant_count }} {{ $expandedBill->tenant_count === 1 ? 'tenant' : 'tenants' }}</p>
                                                </div>
                                                <div class="rounded-lg p-3 {{ $isElectricity ? 'bg-orange-50 ring-1 ring-orange-200' : 'bg-blue-50 ring-1 ring-blue-200' }}">
                                                    <p class="text-[11px] font-semibold {{ $isElectricity ? 'text-orange-500' : 'text-blue-500' }} uppercase tracking-wider mb-1">Your Share</p>
                                                    <p class="text-base font-extrabold {{ $isElectricity ? 'text-orange-700' : 'text-blue-700' }}">&#8369; {{ number_format($expandedBill->per_tenant_amount, 2) }}</p>
                                                </div>
                                            </div>
                                            <p class="text-[11px] text-gray-400 mt-3 font-medium">
                                                &#8369;{{ number_format($expandedBill->total_amount, 2) }} &divide; {{ $expandedBill->tenant_count }} = <span class="{{ $isElectricity ? 'text-orange-600' : 'text-blue-600' }} font-bold">&#8369;{{ number_format($expandedBill->per_tenant_amount, 2) }} per tenant</span>
                                            </p>
                                        @else
                                            <div class="grid grid-cols-2 gap-3">
                                                <div class="rounded-lg p-3 bg-gray-50">
                                                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Description</p>
                                                    <p class="text-sm font-bold text-gray-900">{{ $item->description }}</p>
                                                </div>
                                                <div class="rounded-lg p-3 {{ $isElectricity ? 'bg-orange-50 ring-1 ring-orange-200' : 'bg-blue-50 ring-1 ring-blue-200' }}">
                                                    <p class="text-[11px] font-semibold {{ $isElectricity ? 'text-orange-500' : 'text-blue-500' }} uppercase tracking-wider mb-1">Your Share</p>
                                                    <p class="text-base font-extrabold {{ $isElectricity ? 'text-orange-700' : 'text-blue-700' }}">&#8369; {{ number_format($item->amount, 2) }}</p>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endif
                @empty
                    <x-ui.tr>
                        <x-ui.td colspan="4" class="text-center py-12 text-slate-500">
                            No utility charges found.
                        </x-ui.td>
                    </x-ui.tr>
                @endforelse
            </x-slot:body>
        </x-ui.table>

        <x-slot:footer>
            {{ $items->links('livewire.layouts.components.paginate-blue') }}
        </x-slot:footer>
    </x-ui.card-with-tabs>
</div>
