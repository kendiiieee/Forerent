@extends('layouts.app')
@section('header-title', 'MAINTENANCE MANAGEMENT')
@section('header-subtitle', 'Monitor costs, track trends, and manage repair tickets')

@section('content')
<div class="p-6 w-full flex flex-col font-['Poppins']">

    {{-- 1. HEADER & BANNER SECTION --}}
    <div class="flex-shrink-0 mb-6">
        <div class="w-full h-32 flex items-center bg-gradient-to-r from-[#070642] to-[#1a4fd1] rounded-3xl px-8 text-white shadow-lg relative overflow-hidden">
            <h2 class="text-3xl font-medium relative z-10">Hello, {{ Auth::user()->first_name ?? 'Tenant' }}!</h2>
            <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 rounded-full -mr-16 -mt-16 pointer-events-none"></div>
            <div class="absolute bottom-0 right-20 w-32 h-32 bg-white opacity-5 rounded-full mb-[-40px] pointer-events-none"></div>
        </div>
    </div>

    {{-- 2. TABS & MAIN CONTENT MANAGED BY LIVEWIRE --}}
    <livewire:layouts.maintenance.tenant-maintenance-list />

    {{-- Modals --}}
    <livewire:layouts.maintenance.add-maintenance-modal />
</div>
@endsection
