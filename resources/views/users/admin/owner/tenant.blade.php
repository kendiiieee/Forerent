@extends('layouts.app')

@section('header-title', 'TENANT MANAGEMENT')
@section('header-subtitle', 'Review tenants and approve new applications')

@section('content')

    @include('livewire.layouts.dashboard.admingreeting')

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
