<div class="bg-white rounded-2xl p-4 sm:p-6 shadow-lg h-full flex flex-col"
     wire:key="rent-chart-{{ $rentSummaryMonth }}"
     x-data="{
        hoveredSegment: null,
        tooltipX: 0,
        tooltipY: 0,
        collected: {{ $rentCollectedPercentage }},
        uncollected: {{ $uncollectedPercentage }},
        totalCollected: {{ $totalRentCollected }},
        totalUncollected: {{ $totalUncollectedRent }},

        formatCurrency(val) {
            return '₱ ' + Number(val).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        handleHover(e, segment) {
            const wrapper = this.$refs.chartWrapper;
            const rect = wrapper.getBoundingClientRect();
            this.tooltipX = e.clientX - rect.left;
            this.tooltipY = e.clientY - rect.top - 10;
            this.hoveredSegment = segment;
        },

        handleLeave() {
            this.hoveredSegment = null;
        },

        segmentPath(centerX, centerY, radius, startAngle, endAngle) {
            const start = this.polarToCartesian(centerX, centerY, radius, endAngle);
            const end = this.polarToCartesian(centerX, centerY, radius, startAngle);
            const largeArc = endAngle - startAngle <= 180 ? 0 : 1;
            return [
                'M', start.x, start.y,
                'A', radius, radius, 0, largeArc, 0, end.x, end.y
            ].join(' ');
        },

        polarToCartesian(cx, cy, r, angleDeg) {
            const rad = (angleDeg - 90) * Math.PI / 180;
            return {
                x: cx + r * Math.cos(rad),
                y: cy + r * Math.sin(rad)
            };
        }
    }"
>
    {{-- Header --}}
    <div class="flex items-start justify-between gap-2 sm:gap-3 mb-4 sm:mb-6">
        <div class="min-w-0">
            <h3 class="text-lg sm:text-xl font-bold text-[#070642] leading-tight truncate">Rent Collection</h3>
            <p class="text-xs text-gray-400 mt-0.5">{{ $rentSummaryPeriodLabel }}</p>
        </div>
        <div class="shrink-0" x-data="{ open: false }" @click.away="open = false" @keydown.escape.stop="open = false">
            <div class="relative">
                <button
                    @click="open = !open"
                    type="button"
                    class="flex items-center gap-2 bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm font-medium text-[#070642] shadow-sm hover:border-gray-300 transition-all focus:outline-none focus:ring-0"
                >
                    <span>{{ $rentSummaryMonthOptions[$rentSummaryMonth] ?? $rentSummaryPeriodLabel }}</span>
                    <svg :class="{ 'rotate-180': open }" class="h-4 w-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div
                    x-show="open"
                    x-transition.origin.top.right
                    style="display: none;"
                    class="absolute right-0 origin-top-right z-30 w-44 mt-2 bg-white border border-gray-100 rounded-xl shadow-xl overflow-hidden max-h-60 overflow-y-auto custom-scrollbar"
                >
                    @foreach($rentSummaryMonthOptions as $monthKey => $monthLabel)
                        <x-dropdown-item
                            :active="$rentSummaryMonth === $monthKey"
                            wire:click="$set('rentSummaryMonth', '{{ $monthKey }}')"
                            @click="open = false"
                        >
                            {{ $monthLabel }}
                        </x-dropdown-item>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Donut Chart --}}
    <div class="flex-1 flex flex-col items-center justify-center">
        <div class="relative w-40 h-40 sm:w-48 sm:h-48 mx-auto" x-ref="chartWrapper">
            <svg viewBox="0 0 42 42" class="w-full h-full overflow-visible">
                @php $gap = 3; @endphp

                {{-- Collected arc (dark navy like reference image) --}}
                <path
                    :d="collected >= 100
                        ? 'M 21 2.085 A 18.915 18.915 0 1 1 20.999 2.085'
                        : (collected <= 0
                            ? ''
                            : segmentPath(21, 21, 15.915, 0 + {{ $gap / 2 }}, collected * 3.6 - {{ $gap / 2 }}))"
                    fill="none"
                    stroke="#1a237e"
                    :stroke-width="hoveredSegment === 'collected' ? '6' : '5'"
                    stroke-linecap="butt"
                    class="transition-all duration-300 ease-out cursor-pointer"
                    :style="hoveredSegment === 'collected' ? 'filter: drop-shadow(0 0 6px rgba(26,35,126,0.5))' : ''"
                    style="pointer-events: visibleStroke;"
                    @mouseenter="hoveredSegment = 'collected'"
                    @mousemove="handleHover($event, 'collected')"
                    @mouseleave="handleLeave()"
                />

                {{-- Uncollected arc (light blue like reference image) --}}
                <path
                    :d="uncollected >= 100
                        ? 'M 21 2.085 A 18.915 18.915 0 1 1 20.999 2.085'
                        : (uncollected <= 0
                            ? ''
                            : segmentPath(21, 21, 15.915, collected * 3.6 + {{ $gap / 2 }}, 360 - {{ $gap / 2 }}))"
                    fill="none"
                    stroke="#4fc3f7"
                    :stroke-width="hoveredSegment === 'uncollected' ? '6' : '5'"
                    stroke-linecap="butt"
                    class="transition-all duration-300 ease-out cursor-pointer"
                    :style="hoveredSegment === 'uncollected' ? 'filter: drop-shadow(0 0 6px rgba(79,195,247,0.5))' : ''"
                    style="pointer-events: visibleStroke;"
                    @mouseenter="hoveredSegment = 'uncollected'"
                    @mousemove="handleHover($event, 'uncollected')"
                    @mouseleave="handleLeave()"
                />

                {{-- Invisible wider hit areas for easier hover --}}
                <path
                    :d="collected >= 100
                        ? 'M 21 2.085 A 18.915 18.915 0 1 1 20.999 2.085'
                        : (collected <= 0
                            ? ''
                            : segmentPath(21, 21, 15.915, 0 + {{ $gap / 2 }}, collected * 3.6 - {{ $gap / 2 }}))"
                    fill="none"
                    stroke="transparent"
                    stroke-width="8"
                    stroke-linecap="butt"
                    class="cursor-pointer"
                    style="pointer-events: stroke;"
                    @mouseenter="hoveredSegment = 'collected'"
                    @mousemove="handleHover($event, 'collected')"
                    @mouseleave="handleLeave()"
                />
                <path
                    :d="uncollected >= 100
                        ? 'M 21 2.085 A 18.915 18.915 0 1 1 20.999 2.085'
                        : (uncollected <= 0
                            ? ''
                            : segmentPath(21, 21, 15.915, collected * 3.6 + {{ $gap / 2 }}, 360 - {{ $gap / 2 }}))"
                    fill="none"
                    stroke="transparent"
                    stroke-width="8"
                    stroke-linecap="butt"
                    class="cursor-pointer"
                    style="pointer-events: stroke;"
                    @mouseenter="hoveredSegment = 'uncollected'"
                    @mousemove="handleHover($event, 'uncollected')"
                    @mouseleave="handleLeave()"
                />
            </svg>

            {{-- Center label --}}
            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                <template x-if="!hoveredSegment">
                    <div class="flex flex-col items-center transition-all duration-300">
                        <span class="text-2xl sm:text-3xl font-bold text-[#070642]" x-text="Math.round(collected) + '%'"></span>
                        <span class="text-xs text-gray-400 mt-0.5">Collected</span>
                    </div>
                </template>
                <template x-if="hoveredSegment === 'collected'">
                    <div class="flex flex-col items-center transition-all duration-300">
                        <span class="text-sm font-bold text-[#1a237e] leading-tight" x-text="formatCurrency(totalCollected)"></span>
                        <span class="text-[11px] text-gray-400 mt-0.5">Collected</span>
                    </div>
                </template>
                <template x-if="hoveredSegment === 'uncollected'">
                    <div class="flex flex-col items-center transition-all duration-300">
                        <span class="text-sm font-bold text-[#4fc3f7] leading-tight" x-text="formatCurrency(totalUncollected)"></span>
                        <span class="text-[11px] text-gray-400 mt-0.5">Uncollected</span>
                    </div>
                </template>
            </div>

            {{-- Tooltip --}}
            <div x-show="hoveredSegment" x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute pointer-events-none z-30"
                 :style="'left: ' + tooltipX + 'px; top: ' + tooltipY + 'px; transform: translate(-50%, -100%);'">
                <div class="bg-[#070642] text-white text-xs rounded-lg px-3 py-2.5 shadow-2xl whitespace-nowrap">
                    <template x-if="hoveredSegment === 'collected'">
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#1a237e] inline-block"></span>
                                <span class="font-semibold text-[13px]">Collected</span>
                            </div>
                            <div class="text-white font-medium" x-text="formatCurrency(totalCollected)"></div>
                            <div class="text-gray-400 text-[11px]" x-text="Math.round(collected * 10) / 10 + '% of expected rent'"></div>
                        </div>
                    </template>
                    <template x-if="hoveredSegment === 'uncollected'">
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#4fc3f7] inline-block"></span>
                                <span class="font-semibold text-[13px]">Uncollected</span>
                            </div>
                            <div class="text-white font-medium" x-text="formatCurrency(totalUncollected)"></div>
                            <div class="text-gray-400 text-[11px]" x-text="Math.round(uncollected * 10) / 10 + '% of expected rent'"></div>
                        </div>
                    </template>
                    <div class="absolute left-1/2 -translate-x-1/2 -bottom-1 w-2 h-2 bg-[#070642] rotate-45"></div>
                </div>
            </div>
        </div>

        {{-- Legend items --}}
        <div class="w-full mt-4 sm:mt-6 space-y-2 sm:space-y-3">
            {{-- Collected --}}
            <div class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl bg-gray-50/80 group cursor-default transition-all duration-200 hover:bg-[#1a237e]/5"
                 @mouseenter="hoveredSegment = 'collected'"
                 @mouseleave="handleLeave()">
                <span class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-[#1a237e] shrink-0 ring-4 ring-[#1a237e]/10 transition-all duration-200 group-hover:ring-[#1a237e]/25"></span>
                <div class="flex-1 min-w-0">
                    <p class="text-[11px] sm:text-[11px] text-gray-400 uppercase tracking-wider">Collected</p>
                    <p class="text-xs sm:text-sm font-bold text-[#070642] truncate">₱ {{ number_format($totalRentCollected, 2) }}</p>
                </div>
                <span class="text-sm sm:text-base font-bold text-[#1a237e] shrink-0">{{ round($rentCollectedPercentage, 1) }}%</span>
            </div>

            {{-- Uncollected --}}
            <div class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl bg-gray-50/80 group cursor-default transition-all duration-200 hover:bg-[#4fc3f7]/5"
                 @mouseenter="hoveredSegment = 'uncollected'"
                 @mouseleave="handleLeave()">
                <span class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-[#4fc3f7] shrink-0 ring-4 ring-[#4fc3f7]/10 transition-all duration-200 group-hover:ring-[#4fc3f7]/25"></span>
                <div class="flex-1 min-w-0">
                    <p class="text-[11px] sm:text-[11px] text-gray-400 uppercase tracking-wider">Uncollected</p>
                    <p class="text-xs sm:text-sm font-bold text-[#070642] truncate">₱ {{ number_format($totalUncollectedRent, 2) }}</p>
                </div>
                <span class="text-sm sm:text-base font-bold text-[#4fc3f7] shrink-0">{{ round($uncollectedPercentage, 1) }}%</span>
            </div>
        </div>
    </div>

    {{-- Bottom stats bar --}}
    <div class="mt-4 sm:mt-6 pt-3 sm:pt-4 border-t border-gray-100">
        <p class="text-xs sm:text-sm text-center">
            <span class="font-semibold text-green-600">{{ round($rentCollectedPercentage, 1) }}%</span>
            <span class="text-gray-500">of expected rent in {{ $rentSummaryPeriodLabel }} is collected</span>
        </p>
    </div>
</div>
