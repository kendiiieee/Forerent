@props([
    'tabs' => [],
    'activeTab' => '',
    'counts' => [],
    'action' => 'setTab',
])

@php
    $containerPadding = 'px-2 py-2';
    $tabKeys = array_keys($tabs);
    $activeIndex = array_search($activeTab, $tabKeys);
    if ($activeIndex === false) $activeIndex = 0;
@endphp

<div
    x-data="{
        activeIndex: {{ $activeIndex }},
        left: 0,
        width: 0,
        animated: false,
        recalc() {
            const btns = this.$el.querySelectorAll('[data-tab-btn]');
            const btn = btns[this.activeIndex];
            if (btn) {
                this.left = btn.offsetLeft;
                this.width = btn.offsetWidth;
            }
        },
        select(index, el) {
            this.animated = true;
            this.activeIndex = index;
            this.left = el.offsetLeft;
            this.width = el.offsetWidth;
        }
    }"
    x-init="activeIndex = {{ $activeIndex }}; requestAnimationFrame(() => { requestAnimationFrame(() => { recalc(); }) })"
    x-on:resize.window.debounce.150ms="recalc()"
    {{ $attributes->merge(['class' => "relative flex items-center gap-1 bg-white rounded-lg shadow-sm border border-gray-200 w-full md:w-auto $containerPadding"]) }}
>
    {{-- Sliding Indicator --}}
    <div
        class="absolute rounded-lg bg-blue-600 pointer-events-none"
        :class="animated ? 'transition-all duration-300 ease-in-out' : ''"
        x-show="width > 0"
        x-cloak
        :style="'top: 4px; bottom: 4px; z-index: 0; left:' + left + 'px; width:' + width + 'px;'"
    ></div>

    @foreach($tabs as $key => $label)
        @php
            $index = array_search($key, $tabKeys);
        @endphp

        <button
            wire:click="{{ $action }}('{{ $key }}')"
            data-tab-btn
            x-on:click="select({{ $index }}, $el)"
            class="relative flex items-center gap-1 px-4 py-2 rounded-lg font-semibold whitespace-nowrap transition-colors duration-200"
            :class="activeIndex === {{ $index }} ? 'text-white' : 'text-gray-500 hover:text-blue-600'"
            style="font-family: 'Open Sans', sans-serif; z-index: 1;"
        >
            <span class="text-sm font-semibold">{{ $label }}</span>
            @if(!empty($counts) && isset($counts[$key]))
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full transition-colors duration-200"
                      :class="activeIndex === {{ $index }} ? 'bg-blue-400 text-white' : 'bg-gray-100 text-gray-600'">
                    {{ $counts[$key] }}
                </span>
            @endif
        </button>
    @endforeach
</div>
