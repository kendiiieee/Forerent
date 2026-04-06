@extends('layouts.app')

{{-- Pass Titles to the Main Layout --}}
@section('header-title', 'PROPERTY MANAGEMENT')
@section('header-subtitle', 'View and record property or unit')

@section('content')

    @include('livewire.layouts.dashboard.admingreeting')

    {{-- Main Content Grid --}}
    <div class="flex flex-col lg:flex-row gap-6">

        {{-- Left Column: 70% --}}
        <div class="w-full lg:w-[70%] flex flex-col gap-6">

            {{-- Buildings Section --}}
            <div>
                <livewire:layouts.properties.building-cards-section
                    :properties="$properties ?? []"
                    :show-add-button="true"
                    :show-add-unit-button="false"
                    title="Buildings"
                    add-button-event="openAddPropertyModal_property-dashboard"
                />
            </div>

            {{-- Property Details Section --}}
            <div class="mt-6">
                <livewire:layouts.properties.property-details />
            </div>

            {{-- Units Section --}}
            <div class="mt-6">
                <livewire:layouts.units.unit-accordion :show-add-button="false" />
            </div>
        </div>

        {{-- Right Sidebar: 30% --}}
        <div class="w-full lg:w-[30%] flex flex-col gap-6">
            <livewire:layouts.property-widgets />
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
