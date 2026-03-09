@props([
    'tabs' => [],
    'activeTab' => '',
    'counts' => [],
    'action' => 'setTab',
    'size' => 'md',
])


@php
    // container padding grows in the "lg" size
    $containerPadding = $size === 'lg' ? 'px-2 py-2' : 'px-2 py-1';
    // base for individual tabs
    $base = 'flex items-center gap-1 px-4 py-2 rounded-lg transition-all duration-150 font-semibold';
@endphp

<div {{ $attributes->merge(['class' => "flex items-center justify-center gap-2 bg-white rounded-lg shadow-sm border border-gray-200 w-full md:w-auto overflow-hidden overflow-x-auto $containerPadding"]) }}>
    @foreach($tabs as $key => $label)
        @php
            $isActive = $activeTab === $key;
            $count = $counts[$key] ?? 0;
            if ($isActive) {
                $stateClasses = 'bg-blue-600 text-white text-base';
                $badgeClasses = 'ml-1 bg-blue-400 text-white';
                $labelClasses = 'font-bold text-base';
            } else {
                $stateClasses = 'bg-transparent text-gray-500 hover:bg-gray-100 hover:text-blue-600';
                $badgeClasses = 'ml-1 bg-gray-100 text-gray-600';
                $labelClasses = 'font-semibold text-sm';
            }
        @endphp

        <button
            wire:click="{{ $action }}('{{ $key }}')"
            class="{{ $base }} {{ $stateClasses }} group"
            style="font-family: 'Open Sans', sans-serif;"
        >
            <span class="{{ $labelClasses }}">{{ $label }}</span>
            <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $badgeClasses }}">{{ $count }}</span>
        </button>
    @endforeach
</div>
