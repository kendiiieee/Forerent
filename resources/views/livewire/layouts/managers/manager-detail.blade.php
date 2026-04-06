<div class="bg-white rounded-3xl shadow-lg p-6 flex flex-col h-full">
    @if($currentManager)
        {{-- 1. Manager Profile Header Card --}}
        <div class="bg-blue-600 text-white p-6 rounded-2xl flex items-stretch justify-between gap-6">
            {{-- Left Column: Profile Info --}}
            <div class="flex items-center gap-4">

                {{-- Profile Image --}}
                <div class="flex-shrink-0 bg-white rounded-full shadow-lg overflow-hidden w-20 h-20">
                    <img src="{{ $currentManager->profile_image_url }}" alt="{{ $currentManager->first_name }}" class="w-full h-full object-cover">
                </div>

                {{-- User Info --}}
                <div class="flex-1">
                    <h3 class="font-bold text-2xl mb-2">{{ $currentManager->first_name }} {{ $currentManager->last_name }}</h3>
                    <div class="flex flex-col gap-1.5">
                        <span class="flex items-center gap-2 text-sm text-white/90">
                            {{-- Mail Icon --}}
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M1.5 8.67v8.58a3 3 0 003 3h15a3 3 0 003-3V8.67l-8.928 5.493a3 3 0 01-3.144 0L1.5 8.67z" />
                                <path d="M22.5 6.908V6.75a3 3 0 00-3-3h-15a3 3 0 00-3 3v.158l9.714 5.978a1.5 1.5 0 001.572 0L22.5 6.908z" />
                            </svg>
                            {{ $currentManager->email }}
                        </span>
                        <span class="flex items-center gap-2 text-sm text-white/90">
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C6.477 18 2 13.523 2 8V3z" />
                            </svg>
                            {{ '(+63) ' . substr($currentManager->contact, -10, 3) . '-' . substr($currentManager->contact, -7, 3) . '-' . substr($currentManager->contact, -4) }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Right Column: Statistics + Divider --}}
            <div class="flex items-center gap-6">
                <div class="w-px bg-white/30 self-stretch"></div>

                <div class="flex-shrink-0">
                    <div class="mb-4">
                        <p class="text-sm text-white/80">Total Properties Managed</p>
                        <p class="text-2xl font-bold">{{ $totalBuildings }} Buildings</p>
                    </div>
                    <div>
                        <p class="text-sm text-white/80">Total Units Managed</p>
                        <p class="text-2xl font-bold">{{ $totalUnits }} Units</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Assigned Properties Section --}}
        <div class="mt-6">
            <div class="flex items-center justify-between mb-4">
                <h4 class="font-bold text-gray-900 text-xl">Assigned Properties</h4>

                {{-- Edit button  --}}
                <flux:tooltip :content="'Update this manager\'s profile information'" position="bottom">
                    <button
                        type="button"
                        wire:click="editManager"
                        class="text-blue-600 hover:text-blue-800 transition-colors p-2 rounded-full hover:bg-blue-50"
                    >
                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                        </svg>
                    </button>
                </flux:tooltip>

            </div>
            <h5 class="font-bold text-gray-800 text-base mb-3">Buildings</h5>
        </div>

        {{-- 3. Building Selector Cards --}}
        <div class="flex gap-3 overflow-x-auto pb-3 pt-2 px-2 custom-horizontal-scrollbar">
            @foreach ($buildings as $building)
                @php
                    $isActive = $building->property_id == $selectedBuildingId;
                @endphp
                <button
                    type="button"
                    wire:click="selectBuilding({{ $building->property_id }})"
                    @class([
                        'flex-shrink-0 p-4 rounded-xl w-56 text-left transition-all duration-200',
                        'bg-blue-600 text-white shadow-lg scale-105' => $isActive,
                        'bg-blue-50 text-blue-700 border-2 border-blue-200 hover:bg-blue-100 hover:border-blue-300' => !$isActive,
                    ])
                >
                    <div class="font-bold text-base mb-1">{{ $building->building_name }}</div>
                    <div @class([
                    'text-sm',
                    'text-white/90' => $isActive,
                    'text-blue-600' => !$isActive,
                ])>
                        {{ $building->address }}
                    </div>
                </button>
            @endforeach
        </div>

        {{-- 4. Unit Details Table --}}
        <div class="flex-1 flex flex-col min-h-0 mt-6">
            {{-- Table Header --}}
            <div class="flex-shrink-0 flex justify-between px-6 py-4 bg-[#2D3E9C] rounded-t-xl">
                <span class="w-1/3 text-sm font-bold text-white uppercase tracking-wide">Unit Number</span>
                <span class="w-1/3 text-sm font-bold text-white uppercase tracking-wide">Available Beds</span>
                <span class="w-1/3 text-sm font-bold text-white uppercase tracking-wide">Status</span>
            </div>

            {{-- Scrollable Table Content --}}
            <div class="flex-1 overflow-y-auto custom-vertical-scrollbar bg-white rounded-b-xl border-2 border-t-0 border-gray-200">
                @forelse ($units as $unit)
                    <div class="flex justify-between items-center px-6 py-4 border-b border-dotted border-gray-300 last:border-b-0 hover:bg-gray-50 transition-colors">
                        <span class="w-1/3 text-base font-bold text-gray-900">{{ $unit->unit_number }}</span>
                        <span class="w-1/3">
                        <div class="text-base font-semibold text-gray-900">{{ $unit->available_beds }} of {{ $unit->total_beds }}</div>
                        <div class="text-xs text-gray-500">{{ $unit->occupants }}</div>
                    </span>
                        <span class="w-1/3">
                        <span @class([
                            'inline-block px-4 py-1.5 text-sm font-semibold rounded-full',
                            'bg-red-100 text-red-700' => strtolower($unit->status) === 'full',
                            'bg-green-100 text-green-700' => strtolower($unit->status) === 'vacant',
                            'bg-gray-100 text-gray-700' => !in_array(strtolower($unit->status), ['full', 'vacant']),
                        ])>
                            {{ $unit->status }}
                        </span>
                    </span>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <p class="font-medium">No units found for this building</p>
                    </div>
                @endforelse
            </div>
        </div>
    @else
        {{-- Empty State --}}
        <div class="flex items-center justify-center h-full">
            <div class="text-center max-w-md">
                {{-- User Icon --}}
                <div class="inline-flex items-center justify-center w-24 h-24 bg-blue-100 rounded-full mb-6">
                    <svg class="w-12 h-12 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>

                {{-- Message --}}
                <h3 class="text-2xl font-bold text-gray-900 mb-2">No Manager Selected</h3>
                <p class="text-gray-600 text-lg mb-6">
                    Please select a manager from the sidebar to view their details, assigned properties, and units.
                </p>

                {{-- Pointer Arrow --}}
                <div class="flex items-center justify-center gap-2 text-blue-600">
                    <svg class="w-6 h-6 animate-bounce -rotate-90" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                    </svg>
                    <span class="font-medium">Select a manager from the left</span>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- Custom scrollbar styles --}}
@push('styles')
    <style>
        /* Vertical Scrollbar */
        .custom-vertical-scrollbar::-webkit-scrollbar {
            width: 10px;
        }
        .custom-vertical-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }
        .custom-vertical-scrollbar::-webkit-scrollbar-thumb {
            background: #2D3E9C;
            border-radius: 10px;
            border: 2px solid #f1f5f9;
        }
        .custom-vertical-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #1e2b6f;
        }

        /* Horizontal Scrollbar - Hidden */
        .custom-horizontal-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .custom-horizontal-scrollbar::-webkit-scrollbar {
            display: none;
        }
    </style>
@endpush
