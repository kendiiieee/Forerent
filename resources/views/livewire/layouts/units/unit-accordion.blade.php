<div class="w-full bg-white rounded-2xl shadow-lg p-4 md:p-6 flex flex-col h-full">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-6 flex-shrink-0 gap-4">
        <h3 class="text-xl font-bold text-gray-900">
            @if($selectedBuildingName)
                {{ $selectedBuildingName }} Units
            @else
                Units
            @endif
        </h3>
        <div class="flex items-center gap-3">
            <x-ui.search-bar
                model="search"
                placeholder="Search by unit number..."
                :suggestions="$suggestions"
            />
            <x-ui.sort-dropdown model="sortBy" current="{{ $sortBy }}" />
            @if(auth()->user()->role === 'landlord')
                <x-ui.button-add
                    text="Add Unit"
                    wire:click="$dispatch('open-add-unit-modal')"
                />
            @endif
        </div>
    </div>

    <div class="flex-1 overflow-y-auto px-1 space-y-4"
         x-data="{ openId: {{ $openUnitId ?? 'null' }} }"
         x-init="
            $wire.on('unitsReset', () => { openId = {{ $openUnitId ?? 'null' }} });
         ">
        @forelse ($units as $unit)
            @php
                $status = $this->calculateUnitStatus($unit);
                $isOpen = $openUnitId === $unit->unit_id;
            @endphp

            <div wire:key="unit-wrapper-{{ $unit->unit_id }}"
                 x-data="{ hovered: false }"
                 class="rounded-lg">

                {{-- Collapsed Header --}}
                <button
                    x-show="openId !== {{ $unit->unit_id }}"
                    @click="openId = {{ $unit->unit_id }}; $wire.toggleUnit({{ $unit->unit_id }})"
                    @mouseenter="hovered = true"
                    @mouseleave="hovered = false"
                    type="button"
                    class="w-full flex justify-between items-center p-4 rounded-lg text-gray-700 transition-all duration-200 border"
                    :class="hovered ? 'bg-[#EFF6FF] border-blue-200 ring-2 ring-inset ring-blue-300' : 'bg-white border-gray-200'"
                >
                    <div class="flex items-center gap-3">
                        <span class="font-semibold text-base">Unit #{{ $unit->unit_number }}</span>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold flex items-center gap-2 bg-white border border-gray-200">
                            <div class="w-2 h-2 rounded-full {{ $this->getStatusDotClass($status) }}"></div>
                            <span class="{{ $this->getStatusTextClass($status) }}">{{ $status }}</span>
                        </span>
                    </div>
                    <svg class="w-5 h-5 text-gray-500 transition-transform duration-200"
                         :class="hovered ? 'rotate-180' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                {{-- Expanded Card --}}
                <div x-show="openId === {{ $unit->unit_id }}"
                     x-collapse.duration.300ms
                     class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">

                    {{-- Blue Header Strip --}}
                    <div class="w-full p-4 bg-[#2360E8] text-white">
                        <div class="flex justify-between items-start gap-4">
                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-blue-100 mb-1">
                                    {{ $unit->property->building_name ?? 'N/A' }} - {{ $this->getFloorSuffix($unit->floor_number) }} Floor
                                </p>
                                <h4 class="text-xl font-bold mb-2">Unit #{{ $unit->unit_number }}</h4>
                                <div class="flex items-center gap-1.5 text-sm text-blue-100">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="truncate">{{ $unit->property->address ?? 'N/A' }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 flex-shrink-0">
                                <span class="bg-white rounded-full px-3 py-1.5 text-xs font-semibold flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full {{ $this->getStatusDotClass($status) }}"></div>
                                    <span class="{{ $this->getStatusTextClass($status) }}">{{ $status }}</span>
                                </span>

                                <button
                                    wire:click.prevent="$dispatch('open-unit-modal', { unitId: {{ $unit->unit_id }} })"
                                    class="flex items-center gap-1.5 bg-white text-[#2360E8] rounded-lg px-3 py-1.5 text-xs font-semibold hover:bg-blue-50 transition-colors border border-white"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Edit
                                </button>

                                <button @click="openId = null; $wire.toggleUnit({{ $unit->unit_id }})"
                                        class="text-white hover:text-blue-100 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Content Body --}}
                    <div class="p-4">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="w-5 h-5 text-[#2360E8]" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M11.47 3.841a.75.75 0 011.06 0l8.69 8.69a.75.75 0 101.06-1.061l-8.689-8.69a2.25 2.25 0 00-3.182 0l-8.69 8.69a.75.75 0 101.061 1.06l8.69-8.689z"/>
                                <path d="M12 5.432l8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 01-.75-.75v-4.5a.75.75 0 00-.75-.75h-3a.75.75 0 00-.75.75V21a.75.75 0 01-.75.75H5.625a1.875 1.875 0 01-1.875-1.875v-6.198a2.29 2.29 0 00.091-.086L12 5.43z"/>
                            </svg>
                            <h3 class="font-bold text-[#2360E8]">Unit Specifications</h3>
                        </div>

                        <div class="mb-6">
                            <div class="bg-[#263093] text-white text-xs font-medium p-3 grid grid-cols-7 gap-2 rounded-t-lg">
                                <span>Room Capacity</span>
                                <span>Unit Capacity</span>
                                <span>Room Type</span>
                                <span>Bed Type</span>
                                <span>Utility Subsidy</span>
                                <span>Occupied Unit</span>
                                <span>Base Rate</span>
                            </div>
                            <div class="bg-gray-50 text-gray-800 text-sm p-3 grid grid-cols-7 gap-2 border border-t-0 border-gray-200 rounded-b-lg">
                                <span>{{ $specifications['room_capacity'] ?? 'N/A' }}</span>
                                <span>{{ $specifications['unit_capacity'] ?? 'N/A' }}</span>
                                <span>{{ $specifications['room_type'] ?? 'N/A' }}</span>
                                <span>{{ $specifications['bed_type'] ?? 'N/A' }}</span>
                                <span>{{ $specifications['utility_subsidy'] ?? 'N/A' }}</span>
                                <div>
                                    <span class="font-medium">{{ $specifications['occupied_unit'] ?? 'N/A' }}</span>
                                    @if(!empty($specifications['occupied_unit_sub']))
                                        <span class="block text-xs text-gray-500">{{ $specifications['occupied_unit_sub'] }}</span>
                                    @endif
                                </div>
                                <span class="font-bold text-base">{{ $specifications['base_rate'] ?? 'N/A' }}</span>
                            </div>
                        </div>

                        <livewire:layouts.units.amenities-grid :amenities="$this->getUnitAmenities($unit)" :wire:key="'amenities-' . $unit->unit_id" />
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-12 text-gray-500 bg-white rounded-lg border-2 border-dashed border-gray-300">
                <p class="font-medium text-lg text-gray-700 mb-2">No units available</p>
                <p class="text-sm text-gray-500">Select a building to view its units</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination Block --}}
    @if (method_exists($units, 'hasPages') && $units->hasPages())
        <div class="flex justify-center items-center gap-2 mt-6 pt-4 border-t border-gray-200 flex-shrink-0">
            @if ($units->onFirstPage())
                <button disabled class="w-9 h-9 flex items-center justify-center border-2 border-gray-300 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                </button>
            @else
                <button wire:click="previousPage" class="w-9 h-9 flex items-center justify-center border-2 border-[#2360E8] bg-[#2360E8] text-white rounded-lg hover:bg-[#1d4eb8] transition-colors">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                </button>
            @endif

            @for ($page = 1; $page <= $units->lastPage(); $page++)
                <button wire:click="gotoPage({{ $page }})"
                        class="w-9 h-9 flex items-center justify-center font-bold rounded-lg transition-colors {{ $units->currentPage() === $page ? 'bg-[#2360E8] text-white' : 'border-2 border-gray-300 text-gray-700 hover:bg-gray-100' }}">
                    {{ $page }}
                </button>
            @endfor

            @if ($units->hasMorePages())
                <button wire:click="nextPage" class="w-9 h-9 flex items-center justify-center border-2 border-[#2360E8] bg-[#2360E8] text-white rounded-lg hover:bg-[#1d4eb8] transition-colors">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                </button>
            @else
                <button disabled class="w-9 h-9 flex items-center justify-center border-2 border-gray-300 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                </button>
            @endif
        </div>
    @endif
</div>
