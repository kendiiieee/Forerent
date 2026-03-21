@if($compact)
    <div class="bg-white rounded-lg shadow-md overflow-hidden w-full transition-all hover:shadow-lg">
        <div class="flex items-stretch min-w-0">
            <div class="w-28 h-24 sm:w-32 sm:h-28 flex-shrink-0 overflow-hidden border-r border-gray-100">
                <img
                    src="{{ $property->image ? asset('storage/' . $property->image) : asset('office-building.png') }}"
                    alt="{{ $property->building_name }}"
                    class="w-full h-full object-cover"
                >
            </div>

            <div class="flex-1 min-w-0 p-3">
                <h3 class="text-base font-semibold text-gray-900 truncate">
                    {{ $property->building_name }}
                </h3>
                <p class="mt-1 text-sm text-gray-600 flex items-start gap-1.5">
                    <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="line-clamp-2">{{ $property->address }}</span>
                </p>
            </div>
        </div>
    </div>
@else
    <div class="bg-white rounded-lg shadow-md overflow-hidden flex-shrink-0 w-64 transition-all hover:shadow-lg">
        {{-- Image Container --}}
        <div class="relative h-48 overflow-hidden">
            <img
                src="{{ $property->image ? asset('storage/' . $property->image) : asset('office-building.png') }}"
                alt="{{ $property->building_name }}"
                class="w-full h-full object-cover">
        </div>

        {{-- Content Container --}}
        <div class="p-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-1 truncate">
                {{ $property->building_name }}
            </h3>
            <p class="text-sm text-gray-600 flex items-start gap-1">
                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                </svg>
                <span class="line-clamp-2">{{ $property->address }}</span>
            </p>
        </div>
    </div>
@endif
