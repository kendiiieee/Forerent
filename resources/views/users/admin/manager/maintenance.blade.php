@extends('layouts.app')

@section('header-title', 'MAINTENANCE MANAGEMENT')
@section('header-subtitle', 'Monitor costs, track trends, and manage repair tickets')

@section('content')

    {{-- 1. Greeting & Context --}}
    @include('livewire.layouts.dashboard.admingreeting')

    {{-- MAIN CONTAINER --}}
    <div class="space-y-6 mt-6">

        <div class="xl:col-span-2">
            <livewire:layouts.maintenance.projected-maintenance-cost />
        </div>

        {{--
            We moved the 2-column layout into the list component
            so the sort tabs can sit on top of both the list and the details.
        --}}
        <div class="w-full">
            <livewire:layouts.maintenance.manager-maintenance-list />
        </div>

    </div>

@endsection
