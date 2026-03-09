@props(['model', 'current' => 'newest'])

@php
    $labelText = 'Sort: ' . ucfirst($current);
@endphp

<div x-data="{ open: false }" @click.away="open = false" @keydown.escape.stop="open = false" class="relative w-full sm:w-auto">
    {{-- Trigger Button --}}
    <button
        @click="open = !open"
        type="button"
        class="w-full sm:w-auto flex items-center justify-between gap-2 bg-white text-gray-800 border border-gray-200 rounded-lg px-4 py-2 font-opensans font-medium text-sm shadow-sm transition-all hover:bg-gray-50 focus:ring-2 focus:ring-blue-300 outline-none"
        aria-haspopup="true"
        :aria-expanded="open"
    >
        {{-- vertical arrows icon --}}
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-gray-500">
            <path d="m21 16-4 4-4-4"/>
            <path d="M17 20V4"/>
            <path d="m3 8 4-4 4 4"/>
            <path d="M7 4v16"/>
        </svg>

        <span>{{ $labelText }}</span>

        {{-- dropdown caret --}}
        <svg :class="{ 'rotate-180': open }" class="w-4 h-4 text-gray-500 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    {{-- Panel --}}
    <div
        x-show="open"
        x-transition.origin.top.right
        style="display: none;"
        class="absolute right-0 z-30 w-full sm:w-40 mt-2 bg-white border border-gray-100 rounded-xl shadow-xl overflow-hidden"
    >
        <x-dropdown-item wire:click="$set('{{ $model }}','newest')" :active="$current === 'newest'">
            Newest
        </x-dropdown-item>
        <x-dropdown-item wire:click="$set('{{ $model }}','oldest')" :active="$current === 'oldest'">
            Oldest
        </x-dropdown-item>
    </div>
</div>
