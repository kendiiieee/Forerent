@extends('layouts.app')

@section('header-title', 'TENANT MANAGEMENT')
@section('header-subtitle', 'Track tenant information and leases')

@section('content')

    @include('livewire.layouts.dashboard.admingreeting')

    {{-- Tenant Navigation (tabs, search, sort, list, detail) --}}
    <div class="mt-6">
        <livewire:layouts.tenants.tenant-navigation />
    </div>

@endsection

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('tenantSelected', (event) => {
            console.log('Tenant selected:', event.tenantId);
        });
    });
</script>
@endpush
