@extends('layouts.app')
@section('header-title', 'VIOLATION RECORDS')
@section('header-subtitle', 'View your violation notices and penalty history')

@section('content')
<div class="w-full flex flex-col font-['Open_Sans']">

    {{-- HEADER BANNER --}}
    <div class="flex-shrink-0 mb-6">
        <div class="relative w-full h-40 bg-gradient-to-r from-red-950 via-red-800 to-red-600 rounded-2xl shadow-xl overflow-hidden">
            <div class="absolute" style="top: 50%; right: 230px; transform: translate(50%, -50%);">
                <div class="absolute rounded-full" style="width: 944px; height: 944px; left: 50%; top: 50%; transform: translate(-50%, -50%); border: 56px solid rgba(255,255,255,0.10);"></div>
                <div class="absolute rounded-full" style="width: 736px; height: 736px; left: 50%; top: 50%; transform: translate(-50%, -50%); border: 56px solid rgba(255,255,255,0.10);"></div>
                <div class="absolute rounded-full" style="width: 528px; height: 528px; left: 50%; top: 50%; transform: translate(-50%, -50%); border: 56px solid rgba(255,255,255,0.10);"></div>
                <div class="absolute rounded-full" style="width: 320px; height: 320px; left: 50%; top: 50%; transform: translate(-50%, -50%); border: 56px solid rgba(255,255,255,0.10);"></div>
            </div>
            <div class="relative z-10 px-8 flex items-center justify-between h-full w-full">
                <div class="flex flex-col justify-center">
                    <p class="text-red-300 text-xs font-semibold uppercase tracking-widest mb-1">Tenant</p>
                    <h1 class="text-white text-3xl font-bold leading-tight">
                        Violation Records
                    </h1>
                    <p class="text-red-200 text-sm mt-1">View your violation notices and understand the penalties</p>
                </div>
                <div class="flex flex-col items-end justify-center">
                    <p class="text-white text-4xl font-bold" id="violation-greeting-time"></p>
                    <p class="text-red-200 text-sm mt-1" id="violation-greeting-date"></p>
                </div>
            </div>
        </div>
        <script>
            function updateViolationClock() {
                const now = new Date();
                const time = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
                const date = now.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
                document.getElementById('violation-greeting-time').textContent = time;
                document.getElementById('violation-greeting-date').textContent = date;
            }
            updateViolationClock();
            setInterval(updateViolationClock, 1000);
        </script>
    </div>

    {{-- TABS & MAIN CONTENT --}}
    <livewire:layouts.violations.tenant-violation-list />
</div>
@endsection
