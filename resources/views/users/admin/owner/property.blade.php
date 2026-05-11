@extends('layouts.app')

{{-- Pass Titles to the Main Layout --}}
@section('header-title', 'PROPERTY MANAGEMENT')
@section('header-subtitle', 'View and record property or unit')

@section('content')

    @include('livewire.layouts.dashboard.admingreeting')

    {{-- View Toggle: Properties / Contracts --}}
    <div x-data="{ activeView: 'properties' }" class="space-y-6">

        <div class="inline-flex items-center bg-gray-100 rounded-full p-1 gap-1">
            <button
                @click="activeView = 'properties'"
                :class="activeView === 'properties'
                    ? 'bg-[#070589] text-white shadow-md'
                    : 'bg-transparent text-gray-500 hover:text-gray-700'"
                class="relative px-6 py-2 rounded-full text-sm font-semibold transition-all duration-300 ease-in-out flex items-center gap-2"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5M3.75 3v18m16.5-18v18M5.25 3h13.5M5.25 21h13.5M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                </svg>
                Properties
            </button>
            <button
                @click="activeView = 'contracts'"
                :class="activeView === 'contracts'
                    ? 'bg-[#070589] text-white shadow-md'
                    : 'bg-transparent text-gray-500 hover:text-gray-700'"
                class="relative px-6 py-2 rounded-full text-sm font-semibold transition-all duration-300 ease-in-out flex items-center gap-2"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
                Contracts
            </button>
        </div>

        {{-- PROPERTIES VIEW --}}
        <div x-show="activeView === 'properties'" x-cloak>
            {{-- Buildings Section (full width) --}}
            <div>
                <livewire:layouts.properties.building-cards-section
                    :properties="$properties ?? []"
                    :show-add-button="true"
                    :show-add-unit-button="false"
                    title="Buildings"
                    add-button-event="openAddPropertyModal_property-dashboard"
                />
            </div>

            {{-- Property Details + Bed Status --}}
            <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6 lg:items-stretch w-full">
                <div class="w-full h-full self-stretch lg:col-span-2">
                    <livewire:layouts.properties.property-details />
                </div>
                <div class="w-full h-full self-stretch lg:col-span-1">
                    <livewire:layouts.property-widgets :initial-building-id="optional($properties->first())->property_id" />
                </div>
            </div>

            {{-- Units Section (full width) --}}
            <div class="mt-6">
                <livewire:layouts.units.unit-accordion :show-add-button="false" />
            </div>
        </div>

        {{-- CONTRACTS VIEW --}}
        <div x-show="activeView === 'contracts'" x-cloak>
            <livewire:layouts.properties.contracts-panel />
        </div>
    </div>

    {{-- Modals --}}
    <livewire:layouts.properties.add-property-modal modal-id="property-dashboard" />
    <livewire:layouts.units.add-unit-modal modal-id="property-dashboard" />
    <livewire:layouts.units.landlord-contract-viewer />

    @push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('propertyCreated', () => Livewire.dispatch('refresh-property-list'));
            Livewire.on('unitCreated', () => Livewire.dispatch('refresh-unit-list'));
        });
    </script>
    @endpush

@endsection
