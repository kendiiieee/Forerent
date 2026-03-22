@extends('layouts.app')

@section('header-title', 'TENANT MANAGEMENT')
@section('header-subtitle', 'Track tenant information and leases')

@section('content')

    @include('livewire.layouts.dashboard.admingreeting')

    {{-- Building Cards Section --}}
    <div class="mt-6">
        <livewire:layouts.properties.building-cards-section
            :show-add-button="false"
            title="Properties"
            empty-state-title="No properties available"
            empty-state-description="Properties will appear here once added to the system"
            event-name="tenant-property-selected"
        />
    </div>

    {{-- Tenant Navigation (tabs, search, sort, list, detail) --}}
    <div class="mt-6">
        <livewire:layouts.tenants.tenant-navigation />
    </div>

@endsection

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('buildingSelected', (event) => {
            console.log('Building selected in tenant page:', event.buildingId);
        });

        Livewire.on('propertyCreated', (propertyId) => {
            console.log('Property created:', propertyId);
            Livewire.dispatch('refresh-property-list');
        });

        Livewire.on('tenantSelected', (event) => {
            console.log('Tenant selected:', event.tenantId);
        });
    });
</script>
@endpush
