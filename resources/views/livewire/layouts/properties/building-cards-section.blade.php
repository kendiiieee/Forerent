<div>
    {{-- Header --}}
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-bold text-gray-900">{{ $title }}</h3>

        <div class="flex items-center gap-2">
            @if($showAddButton)
                <button
                    type="button"
                    onclick="Livewire.dispatch('{{ $addButtonEvent }}')"
                    class="py-2 px-4 text-sm font-medium text-white bg-[#2360E8] rounded-lg hover:bg-[#1d4eb8] transition-colors">
                    + Add Property
                </button>
            @endif

            @if($showAddUnitButton)
                <button
                    type="button"
                    onclick="Livewire.dispatch('{{ $addUnitButtonEvent }}')"
                    class="py-2 px-4 text-sm font-medium text-white bg-[#2360E8] rounded-lg hover:bg-[#1d4eb8] transition-colors">
                    + Add Unit
                </button>
            @endif
        </div>
    </div>

    @php
        $containerClasses = $stacked
            ? 'flex flex-col gap-4 max-h-[680px] overflow-y-auto pr-1'
            : 'flex gap-4 overflow-x-auto pb-4 [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]';
    @endphp

    <div class="{{ $containerClasses }}">

        @forelse ($properties as $property)
            <div
                wire:key="building-{{ $property->property_id }}"
                wire:click="selectBuilding({{ $property->property_id }})"
                class="cursor-pointer rounded-lg {{ $stacked ? '' : 'transition-transform hover:scale-105' }}
                    {{ $selectedBuilding === $property->property_id
                        ? 'border-2 border-blue-500 bg-blue-50'
                        : 'border border-transparent' }}"
            >
                @include('livewire.layouts.properties.buildingcard', [
                    'property' => $property,
                    'compact' => $stacked,
                ])
            </div>
        @empty
            <div class="w-full flex flex-col items-center justify-center text-center p-16 border-2 border-dashed border-gray-300 rounded-lg bg-white">
                <h3 class="text-xl font-semibold text-gray-700">
                    {{ $emptyStateTitle }}
                </h3>
                <p class="text-gray-500 mt-2">
                    {{ $emptyStateDescription }}
                </p>

                @if($showAddButton)
                    <button
                        type="button"
                        onclick="Livewire.dispatch('{{ $addButtonEvent }}')"
                        class="mt-4 py-2 px-6 text-sm font-medium text-white bg-[#2360E8] rounded-lg hover:bg-[#1d4eb8] transition-colors">
                        Add Your First Property
                    </button>
                @endif
            </div>
        @endforelse
    </div>
</div>
