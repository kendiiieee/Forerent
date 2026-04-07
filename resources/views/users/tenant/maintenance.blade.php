@extends('layouts.app')
@section('header-title', 'MAINTENANCE REQUEST MANAGEMENT')
@section('header-subtitle', 'Submit and track your maintenance requests')

@section('content')
<div class="w-full flex flex-col font-['Open_Sans']">

    {{-- 1. HEADER & BANNER SECTION --}}
    <div class="flex-shrink-0 mb-6">
        <div class="relative w-full h-40 bg-gradient-to-r from-blue-950 via-blue-800 to-blue-600 rounded-2xl shadow-xl overflow-hidden">
            <!-- Circular ring effects -->
            <div class="absolute" style="top: 50%; right: 230px; transform: translate(50%, -50%);">
                <div class="absolute rounded-full" style="width: 944px; height: 944px; left: 50%; top: 50%; transform: translate(-50%, -50%); border: 56px solid rgba(255,255,255,0.10);"></div>
                <div class="absolute rounded-full" style="width: 736px; height: 736px; left: 50%; top: 50%; transform: translate(-50%, -50%); border: 56px solid rgba(255,255,255,0.10);"></div>
                <div class="absolute rounded-full" style="width: 528px; height: 528px; left: 50%; top: 50%; transform: translate(-50%, -50%); border: 56px solid rgba(255,255,255,0.10);"></div>
                <div class="absolute rounded-full" style="width: 320px; height: 320px; left: 50%; top: 50%; transform: translate(-50%, -50%); border: 56px solid rgba(255,255,255,0.10);"></div>
            </div>
            <!-- Content -->
            <div class="relative z-10 px-8 flex items-center justify-between h-full w-full">
                <div class="flex flex-col justify-center">
                    <p class="text-blue-300 text-xs font-semibold uppercase tracking-widest mb-1">Tenant</p>
                    <h1 class="text-white text-3xl font-bold leading-tight">
                        Welcome Back,
                        <span class="text-cyan-400">{{ auth()->check() ? strtoupper(auth()->user()->first_name) : 'GUEST' }}!</span>
                    </h1>
                    <p class="text-blue-200 text-sm mt-1">Manage your maintenance requests and track progress</p>
                </div>
                <div class="flex flex-col items-end justify-center">
                    <p class="text-white text-4xl font-bold" id="maintenance-greeting-time"></p>
                    <p class="text-blue-200 text-sm mt-1" id="maintenance-greeting-date"></p>
                </div>
            </div>
        </div>
        <script>
            function updateMaintenanceClock() {
                const now = new Date();
                const time = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
                const date = now.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
                document.getElementById('maintenance-greeting-time').textContent = time;
                document.getElementById('maintenance-greeting-date').textContent = date;
            }
            updateMaintenanceClock();
            setInterval(updateMaintenanceClock, 1000);
        </script>
    </div>

    {{-- 2. TABS & MAIN CONTENT MANAGED BY LIVEWIRE --}}
    <livewire:layouts.maintenance.tenant-maintenance-list />

    {{-- Modals --}}
    <livewire:layouts.maintenance.add-maintenance-modal />
</div>
@endsection
