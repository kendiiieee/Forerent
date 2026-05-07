@props([
    /** Visual style — must match the host form's existing design. */
    'variant' => 'default', // default | floating | compact

    /** Names of the parent Livewire properties to wire:set against. */
    'provinceModel' => 'provinceId',
    'cityModel'     => 'cityId',
    'barangayModel' => 'barangayId',
    'streetModel'   => 'street',

    /** Current selected values (for showing the chosen label in the trigger). */
    'provinceId' => null,
    'cityId'     => null,
    'barangayId' => null,
    'street'     => '',

    /** Pre-loaded option lists. The host trait is `WithPsgcAddress`. */
    'provinces' => [],
    'cities'    => [],
    'barangays' => [],

    /** Section heading. Pass null + showLabel=false to hide. */
    'label'     => 'Address',
    'showLabel' => true,

    /** Drives the red asterisk only — parent component owns actual validation rules. */
    'required'  => false,
])

@php
    $lookup = function ($collection, $id) {
        if (!$id) return null;
        foreach ($collection as $item) {
            if ((int) $item->id === (int) $id) return $item->name;
        }
        return null;
    };

    $provinceName = $lookup($provinces, $provinceId);
    $cityName     = $lookup($cities, $cityId);
    $barangayName = $lookup($barangays, $barangayId);

    $cityDisabled = empty($cities) || (is_countable($cities) && count($cities) === 0);
    $brgyDisabled = empty($barangays) || (is_countable($barangays) && count($barangays) === 0);

    $variantClasses = match ($variant) {
        'floating' => [
            'trigger'      => 'block px-2.5 pb-2.5 pt-4 w-full text-sm text-left text-gray-900 bg-white rounded-lg border border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-[#0030C5] cursor-pointer disabled:bg-gray-50 disabled:cursor-not-allowed',
            'optionActive' => 'bg-[#0030C5]/10 text-[#0030C5]',
            'street'       => 'block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-[#0030C5] peer',
        ],
        'compact' => [
            'trigger'      => 'w-full text-sm text-left border rounded-xl px-3 py-2 bg-white focus:outline-none focus:border-[#070589] focus:ring-1 focus:ring-[#070589] cursor-pointer disabled:bg-gray-50 disabled:cursor-not-allowed',
            'optionActive' => 'bg-[#070589]/10 text-[#070589]',
            'street'       => 'w-full text-sm border rounded-xl px-3 py-2 focus:border-[#070589] focus:ring-1 focus:ring-[#070589] placeholder:text-gray-300 border-gray-200',
        ],
        default => [
            'trigger'      => 'w-full text-sm text-left border-gray-300 rounded-lg px-3 py-2 bg-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 cursor-pointer disabled:bg-gray-50 disabled:cursor-not-allowed border',
            'optionActive' => 'bg-blue-50 text-blue-700',
            'street'       => 'w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2 border',
        ],
    };
@endphp

{{-- ========================= LABEL ========================= --}}
@if ($showLabel && $label)
    @if ($variant === 'compact')
        <label class="block text-xs font-semibold text-gray-700 mb-1">{{ $label }} @if($required)<span class="text-red-500">*</span>@endif</label>
    @elseif ($variant === 'floating')
        <p class="text-sm font-medium text-gray-700 mb-2">{{ $label }} @if($required)<span class="text-red-500">*</span>@endif</p>
    @else
        <label class="text-xs font-semibold text-gray-700">{{ $label }} @if($required)<span class="text-red-500">*</span>@endif</label>
    @endif
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-3 {{ $showLabel && !$variant === 'compact' ? 'mt-1' : '' }}">
    {{-- ===================== PROVINCE ===================== --}}
    <div x-data="{ open: false, search: '' }" @keydown.escape.window="open = false" class="relative">
        <button type="button"
                @click="open = !open"
                class="{{ $variantClasses['trigger'] }} pr-8 relative">
            <span class="block truncate {{ $provinceName ? 'text-gray-900' : 'text-gray-400' }}">
                {{ $provinceName ?? 'Select Province' }}
            </span>
            <svg class="absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>

        <div x-show="open" x-cloak
             @click.outside="open = false; search = ''"
             x-transition.opacity.duration.100ms
             class="absolute z-50 left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg flex flex-col"
             style="max-height: 16rem;">
            <div class="p-2 border-b border-gray-100">
                <input type="text" x-model="search" x-ref="search" @click.stop
                       placeholder="Search province..."
                       class="w-full text-xs border border-gray-200 rounded px-2 py-1.5 focus:outline-none focus:border-gray-400">
            </div>
            <div class="overflow-y-auto flex-1 py-1">
                @foreach ($provinces as $p)
                    <button type="button"
                            x-show="$el.dataset.name.includes(search.toLowerCase())"
                            data-name="{{ strtolower($p->name) }}"
                            @click.prevent="$wire.set('{{ $provinceModel }}', {{ $p->id }}); open = false; search = '';"
                            class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 {{ (int)$provinceId === (int)$p->id ? $variantClasses['optionActive'] : 'text-gray-700' }}">
                        {{ $p->name }}
                    </button>
                @endforeach
                <p x-show="![...$el.parentElement.children].some(c => c !== $el && c.style.display !== 'none' && c.dataset.name?.includes(search.toLowerCase()))"
                   x-cloak class="px-3 py-2 text-xs text-gray-400 italic">No matches.</p>
            </div>
        </div>
        @error($provinceModel) <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
    </div>

    {{-- ===================== CITY ===================== --}}
    <div x-data="{ open: false, search: '' }" @keydown.escape.window="open = false" class="relative">
        <button type="button"
                @click="open = !open"
                @disabled($cityDisabled)
                class="{{ $variantClasses['trigger'] }} pr-8 relative">
            <span class="block truncate {{ $cityName ? 'text-gray-900' : 'text-gray-400' }}">
                {{ $cityName ?? ($cityDisabled ? 'Select province first' : 'Select City / Municipality') }}
            </span>
            <svg class="absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>

        @unless ($cityDisabled)
        <div x-show="open" x-cloak
             @click.outside="open = false; search = ''"
             x-transition.opacity.duration.100ms
             class="absolute z-50 left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg flex flex-col"
             style="max-height: 16rem;">
            <div class="p-2 border-b border-gray-100">
                <input type="text" x-model="search" @click.stop
                       placeholder="Search city..."
                       class="w-full text-xs border border-gray-200 rounded px-2 py-1.5 focus:outline-none focus:border-gray-400">
            </div>
            <div class="overflow-y-auto flex-1 py-1">
                @foreach ($cities as $c)
                    <button type="button"
                            x-show="$el.dataset.name.includes(search.toLowerCase())"
                            data-name="{{ strtolower($c->name) }}"
                            @click.prevent="$wire.set('{{ $cityModel }}', {{ $c->id }}); open = false; search = '';"
                            class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 {{ (int)$cityId === (int)$c->id ? $variantClasses['optionActive'] : 'text-gray-700' }}">
                        {{ $c->name }}
                    </button>
                @endforeach
            </div>
        </div>
        @endunless
        @error($cityModel) <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
    </div>

    {{-- ===================== BARANGAY ===================== --}}
    <div x-data="{ open: false, search: '' }" @keydown.escape.window="open = false" class="relative">
        <button type="button"
                @click="open = !open"
                @disabled($brgyDisabled)
                class="{{ $variantClasses['trigger'] }} pr-8 relative">
            <span class="block truncate {{ $barangayName ? 'text-gray-900' : 'text-gray-400' }}">
                {{ $barangayName ?? ($brgyDisabled ? 'Select city first' : 'Select Barangay') }}
            </span>
            <svg class="absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>

        @unless ($brgyDisabled)
        <div x-show="open" x-cloak
             @click.outside="open = false; search = ''"
             x-transition.opacity.duration.100ms
             class="absolute z-50 left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg flex flex-col"
             style="max-height: 16rem;">
            <div class="p-2 border-b border-gray-100">
                <input type="text" x-model="search" @click.stop
                       placeholder="Search barangay..."
                       class="w-full text-xs border border-gray-200 rounded px-2 py-1.5 focus:outline-none focus:border-gray-400">
            </div>
            <div class="overflow-y-auto flex-1 py-1">
                @foreach ($barangays as $b)
                    <button type="button"
                            x-show="$el.dataset.name.includes(search.toLowerCase())"
                            data-name="{{ strtolower($b->name) }}"
                            @click.prevent="$wire.set('{{ $barangayModel }}', {{ $b->id }}); open = false; search = '';"
                            class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 {{ (int)$barangayId === (int)$b->id ? $variantClasses['optionActive'] : 'text-gray-700' }}">
                        {{ $b->name }}
                    </button>
                @endforeach
            </div>
        </div>
        @endunless
        @error($barangayModel) <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
    </div>

    {{-- ===================== STREET ===================== --}}
    <div>
        <input wire:model.defer="{{ $streetModel }}" type="text"
               class="{{ $variantClasses['street'] }}"
               placeholder="House #, Street">
        @error($streetModel) <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
    </div>
</div>
