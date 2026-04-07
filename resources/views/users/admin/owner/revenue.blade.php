@extends('layouts.app')

{{-- 1. Pass the Title and Subtitle to the Main Layout --}}
@section('header-title', 'REVENUE MANAGEMENT')
@section('header-subtitle', 'Track and manage property income')

@section('content')

    {{-- 2. Only keep the content specific to this page --}}
    @include('livewire.layouts.dashboard.admingreeting')

    {{-- Success Message --}}
    @if (session()->has('message'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    {{-- The Revenue Component --}}
    <livewire:layouts.financials.revenue-container />

    {{-- Modals --}}
    <livewire:layouts.managers.add-manager-modal modal-id="manager-dashboard" />

@endsection
