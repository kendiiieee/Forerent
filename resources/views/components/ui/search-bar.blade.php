@props([
    'model' => 'search',
    'placeholder' => 'Search...',
    'suggestions' => [],
])

<div
    x-data="{
        open: false,
        inputVal: $wire.get('{{ $model }}') || '',
        suggestions: @js($suggestions),
        get filtered() {
            if (!this.inputVal || this.inputVal.length < 1) return [];
            const q = this.inputVal.toLowerCase();
            return this.suggestions.filter(s => s.toLowerCase().includes(q)).slice(0, 6);
        },
        search(val) {
            this.inputVal = val;
            $wire.set('{{ $model }}', val);
        },
        select(value) {
            this.inputVal = value;
            this.open = false;
            $wire.set('{{ $model }}', value);
        }
    }"
    @click.away="open = false"
    class="relative"
>
    <div class="relative flex items-center bg-white border border-gray-200 rounded-xl overflow-hidden transition-all duration-200 focus-within:border-[#2B66F5]"
         style="min-width: 220px;">
        <input
            type="text"
            x-model="inputVal"
            x-on:input.debounce.300ms="search(inputVal)"
            @focus="open = true"
            placeholder="{{ $placeholder }}"
            class="w-full py-2 pl-4 pr-10 text-sm text-gray-700 placeholder-gray-400 bg-transparent border-none outline-none focus:ring-0 focus:outline-none"
        />
        <svg class="absolute right-3 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
    </div>

    {{-- Autocomplete Dropdown --}}
    <div
        x-show="open && filtered.length > 0"
        x-transition.origin.top
        style="display: none;"
        class="absolute left-0 right-0 z-50 mt-1 bg-white border border-gray-100 rounded-xl shadow-lg overflow-hidden max-h-64 overflow-y-auto"
    >
        <template x-for="(item, index) in filtered" :key="index">
            <button
                type="button"
                @mousedown.prevent="select(item)"
                class="w-full px-4 py-2.5 text-left text-sm text-gray-700 hover:bg-[#EEF3FF] hover:text-[#2B66F5] transition-colors duration-150 flex items-center gap-2"
            >
                <svg class="w-3.5 h-3.5 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <span x-text="item"></span>
            </button>
        </template>
    </div>
</div>
