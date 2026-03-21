@extends('layouts.app')

{{-- Pass Titles to the Main Layout --}}
@section('header-title', 'PROPERTY MANAGEMENT')
@section('header-subtitle', 'View and record property or unit')

@section('content')

    @include('livewire.layouts.dashboard.admingreeting')

    {{-- Success Message --}}
    @if (session()->has('message'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    {{-- Main Content Grid --}}
    <div class="flex flex-col lg:flex-row gap-6">

        {{-- Left Column: 70% --}}
        <div class="w-full lg:w-[70%] flex flex-col gap-6">

            {{-- Buildings Section --}}
            <div>
                <livewire:layouts.properties.building-cards-section
                    :properties="$properties ?? []"
                    :show-add-button="true"
                    :show-add-unit-button="true"
                    title="Buildings"
                    add-button-event="openAddPropertyModal_property-dashboard"
                    add-unit-button-event="open-add-unit-modal"
                />
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

    @push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('propertyCreated', () => Livewire.dispatch('refresh-property-list'));
            Livewire.on('unitCreated', () => Livewire.dispatch('refresh-unit-list'));
        });
    </script>
    @endpush

@endsection
