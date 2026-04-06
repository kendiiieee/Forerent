@extends('layouts.app')

@section('header-title', 'VIOLATION MANAGEMENT')
@section('header-subtitle', 'Track and manage tenant violations and penalties')

@section('content')

    {{-- 1. Greeting & Context --}}
    @include('livewire.layouts.dashboard.admingreeting')

    {{-- MAIN CONTAINER --}}
    <div class="space-y-6 mt-6">

        <div class="w-full">
            <livewire:layouts.violations.manager-violation-list />
        </div>

    </div>

    {{-- Add Violation Modal --}}
    <livewire:layouts.violations.add-violation-modal />

@endsection
