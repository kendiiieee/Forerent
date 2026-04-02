@extends('layouts.app')

@section('header-title', 'DASHBOARD')
@section('header-subtitle', 'Centralized rental property management overview')

@section('content')

     @include('livewire.layouts.dashboard.admingreeting')

    @if (session()->has('message'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg relative" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <livewire:layouts.dashboard.announcement-list :is-manager="true" />
    <livewire:layouts.dashboard.calendar-widget />

    <div class="w-full space-y-6">
        <livewire:layouts.dashboard.maintenance-stats />

        <livewire:layouts.dashboard.announcement-modal />

    </div>


@endsection
