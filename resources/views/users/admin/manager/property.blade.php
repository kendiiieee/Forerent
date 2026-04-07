@extends('layouts.app')

@section('header-title', 'PROPERTIES MANAGEMENT')
@section('header-subtitle', 'Centralized rental property management overview')

@section('content')
    @include('livewire.layouts.dashboard.admingreeting')

    {{-- Main Content Flex Layout --}}
    <div class="flex flex-col gap-6">

        {{-- Buildings Section --}}
        <div>
            <livewire:layouts.properties.building-cards-section
                :show-add-button="auth()->user()->role === 'landlord'"
                :stacked="true"
                title="Buildings"
            />
        </div>

        {{-- Property Details Section --}}
        <div>
            <livewire:layouts.properties.property-details />
        </div>

        {{-- Units Section --}}
        <div>
            <livewire:layouts.units.unit-accordion
                :show-add-button="false"
            />
        </div>
    </div>

    <livewire:layouts.properties.add-property-modal modal-id="property-dashboard" />
    <livewire:layouts.units.add-unit-modal />

    @push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('propertyUpdated', () => Livewire.dispatch('refresh-property-list'));
            Livewire.on('unitUpdated', () => Livewire.dispatch('refresh-unit-list'));
        });
    </script>
    @endpush
@endsection
