@props([
    'title' => 'Inspection',
    'subtitle' => 'Record room condition',
    'saved' => false,
    'accentColor' => 'emerald', // 'emerald' or 'red'
    'contentRef' => 'expandContent',
])

@php
    $colorMap = [
        'emerald' => ['icon_bg' => 'bg-emerald-50 group-hover:bg-emerald-100', 'icon_bg_open' => 'bg-emerald-100', 'icon_text' => 'text-emerald-600', 'chevron' => 'text-emerald-500', 'border_hover' => 'hover:border-emerald-200', 'border_open' => 'border-emerald-200'],
        'red' => ['icon_bg' => 'bg-red-50 group-hover:bg-red-100', 'icon_bg_open' => 'bg-red-100', 'icon_text' => 'text-red-600', 'chevron' => 'text-red-500', 'border_hover' => 'hover:border-red-200', 'border_open' => 'border-red-200'],
    ];
    $c = $colorMap[$accentColor] ?? $colorMap['emerald'];
@endphp

<div
    x-data="{
        open: false,
        animating: false,
        toggle() {
            if (this.open) { this.close(); } else { this.openCard(); }
        },
        openCard() {
            this.open = true;
            this.animating = true;
            const el = this.$refs.{{ $contentRef }};
            el.style.height = '0px';
            el.style.overflow = 'hidden';
            this.$nextTick(() => {
                el.style.height = el.scrollHeight + 'px';
                setTimeout(() => {
                    if (this.open) { el.style.height = 'auto'; el.style.overflow = 'visible'; }
                    this.animating = false;
                }, 500);
            });
        },
        close() {
            this.animating = true;
            const el = this.$refs.{{ $contentRef }};
            el.style.height = el.scrollHeight + 'px';
            el.style.overflow = 'hidden';
            requestAnimationFrame(() => {
                el.style.height = '0px';
                setTimeout(() => { this.open = false; this.animating = false; }, 500);
            });
        }
    }"
    class="w-full bg-white rounded-2xl border border-gray-100 shadow-sm transition-all overflow-hidden"
    :class="open ? '{{ $c['border_open'] }} shadow-md' : '{{ $c['border_hover'] }} hover:shadow-md'"
>
    {{-- Trigger Header --}}
    <button type="button" @click="toggle()" class="w-full p-4 text-left group">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors"
                     :class="open ? '{{ $c['icon_bg_open'] }}' : '{{ $c['icon_bg'] }}'">
                    <svg class="w-5 h-5 {{ $c['icon_text'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <p class="text-sm font-bold text-[#070589]">{{ $title }}</p>
                        @if($saved)
                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                Completed
                            </span>
                        @else
                            <span class="inline-flex items-center text-[10px] font-semibold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">Pending</span>
                        @endif
                    </div>
                    <p class="text-[10px] text-gray-400">{{ $subtitle }}</p>
                </div>
            </div>
            <svg class="w-5 h-5 text-gray-300 transition-all duration-300 ease-out"
                 :class="open ? 'rotate-90 {{ $c['chevron'] }}' : 'group-hover:{{ $c['chevron'] }}'"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
            </svg>
        </div>
    </button>

    {{-- Expandable Content --}}
    <div x-ref="{{ $contentRef }}" class="transition-[height] duration-500 ease-[cubic-bezier(0.34,1.56,0.64,1)]" style="height: 0px; overflow: hidden;">
        <div class="px-4 pb-5 space-y-5">
            <div class="border-t border-gray-100"></div>
            {{ $slot }}
        </div>
    </div>
</div>
