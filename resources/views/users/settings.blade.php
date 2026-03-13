@extends('layouts.app')

@section('header-title', 'SETTINGS')
@section('header-subtitle', 'Manage your account preferences and security protocols.')

@section('content')

    <div class="w-full h-full" x-data="{ activeTab: 'personal-info' }">

        {{-- TABS NAVIGATION --}}
        <div class="mb-8 flex space-x-8 overflow-x-auto overflow-y-visible border-b border-[#E2E8F0] pb-1">

            <button
                @click="activeTab = 'personal-info'"
                class="relative whitespace-nowrap pb-3 text-lg font-bold transition-colors"
                :class="activeTab === 'personal-info' ? 'text-[#0750ce]' : 'text-[#94A3B8] hover:text-[#64748B]'"
            >
                Personal Information
                <div x-show="activeTab === 'personal-info'" class="absolute -bottom-px left-0 h-1 w-full rounded-t-full bg-[#0750ce] shadow-[0_8px_20px_rgba(47,107,255,0.35)]"></div>
            </button>

            <button
                @click="activeTab = 'security'"
                class="relative whitespace-nowrap pb-3 text-lg font-bold transition-colors"
                :class="activeTab === 'security' ? 'text-[#0750ce]' : 'text-[#94A3B8] hover:text-[#64748B]'"
            >
                Security
                <div x-show="activeTab === 'security'" class="absolute -bottom-px left-0 h-1 w-full rounded-t-full bg-[#0750ce] shadow-[0_8px_20px_rgba(47,107,255,0.35)]"></div>
            </button>

        </div>

        {{-- TAB CONTENT --}}
        <div>
            <div x-show="activeTab === 'personal-info'">
                <livewire:actions.settings-form />
            </div>

            <div x-show="activeTab === 'security'">
                <livewire:layouts.settings.security-form />
            </div>
        </div>
    </div>

@endsection
