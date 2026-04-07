@extends('layouts.app')

@section('header-title', 'PAYMENT DOCUMENTS')
@section('header-subtitle', 'Access payment history and document new receipts')

@section('content')

    @include('livewire.layouts.dashboard.admingreeting')

    {{-- Primary View Toggle --}}
    <div x-data="{ view: 'rent' }" class="space-y-4">

        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            {{-- View Toggle --}}
            <div class="inline-flex items-center bg-gray-100 rounded-full p-1 gap-1">
                <button
                    @click="view = 'rent'; $nextTick(() => window.dispatchEvent(new Event('resize')))"
                    :class="view === 'rent'
                        ? 'bg-[#070589] text-white shadow-md'
                        : 'bg-transparent text-gray-500 hover:text-gray-700'"
                    class="relative px-6 py-2 rounded-full text-sm font-semibold transition-all duration-300 ease-in-out flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                    </svg>
                    Rent Payments
                </button>
                <button
                    @click="view = 'utility'; $nextTick(() => window.dispatchEvent(new Event('resize')))"
                    :class="view === 'utility'
                        ? 'bg-[#070589] text-white shadow-md'
                        : 'bg-transparent text-gray-500 hover:text-gray-700'"
                    class="relative px-6 py-2 rounded-full text-sm font-semibold transition-all duration-300 ease-in-out flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                    </svg>
                    Utility Bills
                </button>
            </div>
        </div>

        {{-- Rent Payments View --}}
        <div x-show="view === 'rent'" x-cloak>
            <livewire:layouts.tenants.payment-history />
        </div>

        {{-- Utility Bills View --}}
        <div x-show="view === 'utility'" x-cloak>
            <livewire:layouts.tenants.tenant-utility-history />
        </div>
    </div>

@endsection
