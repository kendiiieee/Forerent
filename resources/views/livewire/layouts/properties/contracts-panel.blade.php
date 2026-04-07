<div class="font-sans">
    <x-ui.card-with-tabs
        :tabs="['all' => 'All', 'pending' => 'Pending', 'signed' => 'Signed', 'draft' => 'Draft']"
        :counts="$counts"
        :activeTab="$activeTab"
        wire:model.live="activeTab"
    >
        {{-- FILTERS SLOT --}}
        <x-slot:filters>
            {{-- Search --}}
            <x-ui.search-bar
                model="search"
                placeholder="Search by tenant name or property..."
                :suggestions="$suggestions"
            />

            {{-- Month Filter --}}
            <x-dropdown label="{{ $monthOptions[$selectedMonth] ?? 'Month' }}" tooltip="Filter contracts by lease start month">
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

            {{-- Year Filter --}}
            <x-dropdown label="{{ $selectedYear ?? 'Year' }}" tooltip="Filter contracts by year">
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
            <x-dropdown label="{{ $selectedBuilding ? Str::limit($selectedBuilding, 15) : 'Building' }}" tooltip="Filter contracts by building">
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
                <x-ui.th>Tenant</x-ui.th>
                <x-ui.th>Property / Unit</x-ui.th>
                <x-ui.th>Lease Period</x-ui.th>
                <x-ui.th>Lease</x-ui.th>
                <x-ui.th>Signatures</x-ui.th>
                <x-ui.th>Contract</x-ui.th>
                <x-ui.th class="text-center">Action</x-ui.th>
            </x-slot:head>

            <x-slot:body>
                @forelse($leases as $lease)
                    @php
                        $cStatus = $lease->contract_status ?? 'draft';
                        $cBadge = match($cStatus) {
                            'executed' => 'bg-emerald-100 text-emerald-700',
                            'pending_signatures', 'pending_tenant', 'pending_owner', 'pending_manager' => 'bg-amber-100 text-amber-700',
                            default => 'bg-gray-100 text-gray-500',
                        };
                        $cLabel = match($cStatus) {
                            'executed' => 'Signed',
                            'pending_signatures' => 'Pending',
                            'pending_tenant' => 'Pending Tenant',
                            'pending_owner' => 'Pending Owner',
                            'pending_manager' => 'Pending Manager',
                            'draft' => 'Draft',
                            default => ucfirst(str_replace('_', ' ', $cStatus)),
                        };
                        $sigCount = collect([$lease->owner_signature, $lease->manager_signature, $lease->tenant_signature])->filter()->count();
                    @endphp
                    <x-ui.tr wire:key="contract-{{ $lease->lease_id }}">
                        <x-ui.td>
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center text-xs font-bold text-blue-700 flex-shrink-0">
                                    {{ strtoupper(substr($lease->tenant?->first_name ?? '?', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-sm text-gray-800">{{ $lease->tenant?->first_name }} {{ $lease->tenant?->last_name }}</p>
                                    <p class="text-[11px] text-gray-400">Bed #{{ $lease->bed?->bed_number }}</p>
                                </div>
                            </div>
                        </x-ui.td>

                        <x-ui.td>
                            <p class="text-sm font-medium text-gray-700">{{ $lease->bed?->unit?->property?->building_name }}</p>
                            <p class="text-[11px] text-gray-400">Unit {{ $lease->bed?->unit?->unit_number }}</p>
                        </x-ui.td>

                        <x-ui.td>
                            <p class="text-sm text-gray-600">{{ $lease->start_date?->format('M d, Y') }}</p>
                            <p class="text-[11px] text-gray-400">to {{ $lease->end_date?->format('M d, Y') }}</p>
                        </x-ui.td>

                        <x-ui.td>
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $lease->status === 'Active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $lease->status }}
                            </span>
                        </x-ui.td>

                        <x-ui.td>
                            <div class="flex items-center gap-1">
                                <div title="Owner: {{ $lease->owner_signature ? 'Signed' : 'Not signed' }}" class="w-5 h-5 rounded-full flex items-center justify-center text-[9px] font-bold {{ $lease->owner_signature ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-100 text-gray-400' }}">O</div>
                                <div title="Manager: {{ $lease->manager_signature ? 'Signed' : 'Not signed' }}" class="w-5 h-5 rounded-full flex items-center justify-center text-[9px] font-bold {{ $lease->manager_signature ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-100 text-gray-400' }}">M</div>
                                <div title="Tenant: {{ $lease->tenant_signature ? 'Signed' : 'Not signed' }}" class="w-5 h-5 rounded-full flex items-center justify-center text-[9px] font-bold {{ $lease->tenant_signature ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-100 text-gray-400' }}">T</div>
                                <span class="text-[10px] text-gray-400 ml-0.5">{{ $sigCount }}/3</span>
                            </div>
                        </x-ui.td>

                        <x-ui.td>
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $cBadge }}">{{ $cLabel }}</span>
                        </x-ui.td>

                        <x-ui.td class="text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button wire:click="viewContract({{ $lease->lease_id }}, 'move-in')"
                                        title="View Move-In Contract"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold border border-[#0906ae] text-[#0906ae] rounded-md hover:bg-blue-50 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Move In
                                </button>
                                @if($cStatus === 'executed')
                                    <button wire:click="viewContract({{ $lease->lease_id }}, 'move-out')"
                                            title="View Move-Out Contract"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold border border-orange-500 text-orange-500 rounded-md hover:bg-orange-50 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        Move Out
                                    </button>
                                @endif
                            </div>
                        </x-ui.td>
                    </x-ui.tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-10 h-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <p class="text-sm text-gray-400">No contracts found</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </x-slot:body>
        </x-ui.table>

        {{-- PAGINATION --}}
        <x-slot:footer>
            {{ $leases->links('livewire.layouts.components.paginate-blue') }}
        </x-slot:footer>
    </x-ui.card-with-tabs>
</div>
