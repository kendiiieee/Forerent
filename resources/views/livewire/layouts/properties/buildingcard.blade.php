<div x-data="{ hovered: false }"
     @mouseenter="hovered = true"
     @mouseleave="hovered = false"
     class="bg-white rounded-lg shadow-md overflow-hidden shrink-0 w-64 transition-all cursor-pointer hover:shadow-xl selected-building:bg-[#2360E8]">

    {{-- Image Container --}}
    <div class="relative h-48 overflow-hidden flex items-center justify-center bg-gray-100">
        <img
            src="{{ $property->thumbnail ? asset('storage/' . $property->thumbnail) : asset('office-building.png') }}"
            alt="{{ $property->building_name }}"
            class="{{ $property->thumbnail ? 'w-full h-full object-cover' : 'max-h-full max-w-full object-contain' }} transition-transform hover:scale-110 duration-300">

        {{-- Edit Button (appears on hover) --}}
        <flux:tooltip :content="'Modify this property\'s details'" position="bottom">
            <button
                @click.stop="Livewire.dispatch('editProperty', [{{ $property->property_id }}])"
                x-show="hovered"
                x-transition
                class="absolute top-2 right-2 p-2 bg-white rounded-lg shadow-md hover:bg-gray-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </button>
        </flux:tooltip>
    </div>

    {{-- Content Container --}}
    <div class="p-4">
        <h3 class="text-lg font-semibold mb-1 truncate building-card-title text-gray-900">
            {{ $property->building_name }}
        </h3>
        <p class="text-sm flex items-start gap-1 building-card-address text-gray-600">
            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
            </svg>
            <span class="line-clamp-2">{{ $property->address }}</span>
        </p>
    </div>
</div>
