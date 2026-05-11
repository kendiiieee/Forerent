<div class="flex flex-col gap-6 w-full h-full">

        {{-- Property Status Card (Interactive Donut Chart) --}}
        <div class="bg-white rounded-2xl shadow-sm p-4 sm:p-6 border border-gray-100 h-full flex flex-col"
         x-data="{
            hoveredSegment: null,
            tooltipX: 0,
            tooltipY: 0,
            occupied: {{ $occupiedPercent }},
            available: {{ $availablePercent }},
            occupiedCount: {{ $occupiedBeds }},
            availableCount: {{ $availableBeds }},
            totalBeds: {{ $totalBeds }},

            handleHover(e, segment) {
                const wrapper = this.$refs.bedChart;
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
        <h3 class="text-lg sm:text-xl font-bold text-[#070642] leading-tight mb-4 sm:mb-6">Property Status</h3>

        {{-- Donut Chart --}}
        <div class="flex flex-col items-center justify-center">
            <div class="relative w-40 h-40 sm:w-48 sm:h-48 mx-auto" x-ref="bedChart">
                <svg viewBox="0 0 42 42" class="w-full h-full overflow-visible">
                    @php $gap = 3; @endphp

                    {{-- Occupied arc (dark navy) --}}
                    <path
                        :d="occupied >= 100
                            ? 'M 21 2.085 A 18.915 18.915 0 1 1 20.999 2.085'
                            : (occupied <= 0
                                ? ''
                                : segmentPath(21, 21, 15.915, 0 + {{ $gap / 2 }}, occupied * 3.6 - {{ $gap / 2 }}))"
                        fill="none"
                        stroke="#070589"
                        :stroke-width="hoveredSegment === 'occupied' ? '6' : '5'"
                        stroke-linecap="butt"
                        class="transition-all duration-300 ease-out cursor-pointer"
                        :style="hoveredSegment === 'occupied' ? 'filter: drop-shadow(0 0 6px rgba(7,5,137,0.5))' : ''"
                        style="pointer-events: visibleStroke;"
                        @mouseenter="hoveredSegment = 'occupied'"
                        @mousemove="handleHover($event, 'occupied')"
                        @mouseleave="handleLeave()"
                    />

                    {{-- Available arc (blue) --}}
                    <path
                        :d="available >= 100
                            ? 'M 21 2.085 A 18.915 18.915 0 1 1 20.999 2.085'
                            : (available <= 0
                                ? ''
                                : segmentPath(21, 21, 15.915, occupied * 3.6 + {{ $gap / 2 }}, 360 - {{ $gap / 2 }}))"
                        fill="none"
                        stroke="#2563EB"
                        :stroke-width="hoveredSegment === 'available' ? '6' : '5'"
                        stroke-linecap="butt"
                        class="transition-all duration-300 ease-out cursor-pointer"
                        :style="hoveredSegment === 'available' ? 'filter: drop-shadow(0 0 6px rgba(37,99,235,0.5))' : ''"
                        style="pointer-events: visibleStroke;"
                        @mouseenter="hoveredSegment = 'available'"
                        @mousemove="handleHover($event, 'available')"
                        @mouseleave="handleLeave()"
                    />

                    {{-- Invisible wider hit areas --}}
                    <path
                        :d="occupied <= 0 ? '' : (occupied >= 100 ? 'M 21 2.085 A 18.915 18.915 0 1 1 20.999 2.085' : segmentPath(21, 21, 15.915, 0 + {{ $gap / 2 }}, occupied * 3.6 - {{ $gap / 2 }}))"
                        fill="none" stroke="transparent" stroke-width="8" stroke-linecap="butt"
                        class="cursor-pointer" style="pointer-events: stroke;"
                        @mouseenter="hoveredSegment = 'occupied'"
                        @mousemove="handleHover($event, 'occupied')"
                        @mouseleave="handleLeave()"
                    />
                    <path
                        :d="available <= 0 ? '' : (available >= 100 ? 'M 21 2.085 A 18.915 18.915 0 1 1 20.999 2.085' : segmentPath(21, 21, 15.915, occupied * 3.6 + {{ $gap / 2 }}, 360 - {{ $gap / 2 }}))"
                        fill="none" stroke="transparent" stroke-width="8" stroke-linecap="butt"
                        class="cursor-pointer" style="pointer-events: stroke;"
                        @mouseenter="hoveredSegment = 'available'"
                        @mousemove="handleHover($event, 'available')"
                        @mouseleave="handleLeave()"
                    />
                </svg>

                {{-- Center label --}}
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <template x-if="!hoveredSegment">
                        <div class="flex flex-col items-center transition-all duration-300">
                            <span class="text-2xl sm:text-3xl font-bold text-[#070642]">{{ $totalBeds }}</span>
                            <span class="text-xs text-gray-400 mt-0.5">Beds</span>
                        </div>
                    </template>
                    <template x-if="hoveredSegment === 'occupied'">
                        <div class="flex flex-col items-center transition-all duration-300">
                            <span class="text-2xl sm:text-3xl font-bold text-[#070589]" x-text="occupiedCount"></span>
                            <span class="text-[11px] text-gray-400 mt-0.5">Occupied</span>
                        </div>
                    </template>
                    <template x-if="hoveredSegment === 'available'">
                        <div class="flex flex-col items-center transition-all duration-300">
                            <span class="text-2xl sm:text-3xl font-bold text-[#2563EB]" x-text="availableCount"></span>
                            <span class="text-[11px] text-gray-400 mt-0.5">Available</span>
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
                        <template x-if="hoveredSegment === 'occupied'">
                            <div class="space-y-0.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#070589] inline-block"></span>
                                    <span class="font-semibold text-[13px]">Occupied Beds</span>
                                </div>
                                <div class="text-white font-medium" x-text="occupiedCount + ' of ' + totalBeds + ' beds'"></div>
                                <div class="text-gray-400 text-[11px]">Beds with active tenants</div>
                            </div>
                        </template>
                        <template x-if="hoveredSegment === 'available'">
                            <div class="space-y-0.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#2563EB] inline-block"></span>
                                    <span class="font-semibold text-[13px]">Available Beds</span>
                                </div>
                                <div class="text-white font-medium" x-text="availableCount + ' of ' + totalBeds + ' beds'"></div>
                                <div class="text-gray-400 text-[11px]">Beds open for rent</div>
                            </div>
                        </template>
                        <div class="absolute left-1/2 -translate-x-1/2 -bottom-1 w-2 h-2 bg-[#070642] rotate-45"></div>
                    </div>
                </div>
            </div>

            {{-- Legend items --}}
            <div class="w-full mt-4 sm:mt-6 space-y-2 sm:space-y-3">
                {{-- Occupied Beds --}}
                <div class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl bg-gray-50/80 group cursor-default transition-all duration-200 hover:bg-[#070589]/5"
                     @mouseenter="hoveredSegment = 'occupied'"
                     @mouseleave="handleLeave()">
                    <span class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-[#070589] shrink-0 ring-4 ring-[#070589]/10 transition-all duration-200 group-hover:ring-[#070589]/25"></span>
                    <div class="flex-1 min-w-0">
                        <p class="text-[11px] text-gray-400 uppercase tracking-wider">Occupied</p>
                        <p class="text-xs sm:text-sm font-bold text-[#070642] truncate">{{ $occupiedBeds }} Beds</p>
                    </div>
                    <span class="text-sm sm:text-base font-bold text-[#070589] shrink-0">{{ $occupiedPercent }}%</span>
                </div>

                {{-- Available Beds --}}
                <div class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl bg-gray-50/80 group cursor-default transition-all duration-200 hover:bg-[#2563EB]/5"
                     @mouseenter="hoveredSegment = 'available'"
                     @mouseleave="handleLeave()">
                    <span class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-[#2563EB] shrink-0 ring-4 ring-[#2563EB]/10 transition-all duration-200 group-hover:ring-[#2563EB]/25"></span>
                    <div class="flex-1 min-w-0">
                        <p class="text-[11px] text-gray-400 uppercase tracking-wider">Available</p>
                        <p class="text-xs sm:text-sm font-bold text-[#070642] truncate">{{ $availableBeds }} Beds</p>
                    </div>
                    <span class="text-sm sm:text-base font-bold text-[#2563EB] shrink-0">{{ $availablePercent }}%</span>
                </div>
            </div>
        </div>

        {{-- Bottom stats bar --}}
        <div class="mt-4 sm:mt-6 pt-3 sm:pt-4 border-t border-gray-100 flex justify-between text-center">
            <div>
                <p class="text-xs text-gray-500">Occupancy Rate</p>
                <p class="font-bold text-gray-900">{{ $occupancyRate }}%</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Total Units</p>
                <p class="font-bold text-gray-900">{{ $totalUnits }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Total Floors</p>
                <p class="font-bold text-gray-900">{{ $totalFloors }}</p>
            </div>
        </div>
    </div>


</div>
