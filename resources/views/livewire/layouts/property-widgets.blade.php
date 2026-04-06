<div class="flex flex-col gap-6 w-full max-w-sm shrink-0">

    {{-- Unit Status Card (Interactive Donut Chart) --}}
    <div class="bg-white rounded-2xl shadow-sm p-4 sm:p-6 border border-gray-100"
         x-data="{
            hoveredSegment: null,
            tooltipX: 0,
            tooltipY: 0,
            occupied: {{ $occupiedPercent }},
            vacant: {{ $vacantPercent }},
            moveInReady: {{ $moveInReadyPercent }},
            occupiedCount: {{ $occupied }},
            vacantCount: {{ $vacant }},
            moveInReadyCount: {{ $moveInReady }},
            totalUnits: {{ $totalUnits }},

            handleHover(e, segment) {
                const wrapper = this.$refs.unitChart;
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
        <h3 class="text-lg sm:text-xl font-bold text-[#070642] leading-tight mb-4 sm:mb-6">Unit Status</h3>

        {{-- Donut Chart --}}
        <div class="flex flex-col items-center justify-center">
            <div class="relative w-40 h-40 sm:w-48 sm:h-48 mx-auto" x-ref="unitChart">
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
                        :d="moveInReady <= 0
                            ? ''
                            : segmentPath(21, 21, 15.915, occupied * 3.6 + {{ $gap / 2 }}, (occupied + moveInReady) * 3.6 - {{ $gap / 2 }})"
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

                    {{-- Vacant arc (light blue) --}}
                    <path
                        :d="vacant >= 100
                            ? 'M 21 2.085 A 18.915 18.915 0 1 1 20.999 2.085'
                            : (vacant <= 0
                                ? ''
                                : segmentPath(21, 21, 15.915, (occupied + moveInReady) * 3.6 + {{ $gap / 2 }}, 360 - {{ $gap / 2 }}))"
                        fill="none"
                        stroke="#60a5fa"
                        :stroke-width="hoveredSegment === 'vacant' ? '6' : '5'"
                        stroke-linecap="butt"
                        class="transition-all duration-300 ease-out cursor-pointer"
                        :style="hoveredSegment === 'vacant' ? 'filter: drop-shadow(0 0 6px rgba(96,165,250,0.5))' : ''"
                        style="pointer-events: visibleStroke;"
                        @mouseenter="hoveredSegment = 'vacant'"
                        @mousemove="handleHover($event, 'vacant')"
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
                        :d="moveInReady <= 0 ? '' : segmentPath(21, 21, 15.915, occupied * 3.6 + {{ $gap / 2 }}, (occupied + moveInReady) * 3.6 - {{ $gap / 2 }})"
                        fill="none" stroke="transparent" stroke-width="8" stroke-linecap="butt"
                        class="cursor-pointer" style="pointer-events: stroke;"
                        @mouseenter="hoveredSegment = 'available'"
                        @mousemove="handleHover($event, 'available')"
                        @mouseleave="handleLeave()"
                    />
                    <path
                        :d="vacant <= 0 ? '' : (vacant >= 100 ? 'M 21 2.085 A 18.915 18.915 0 1 1 20.999 2.085' : segmentPath(21, 21, 15.915, (occupied + moveInReady) * 3.6 + {{ $gap / 2 }}, 360 - {{ $gap / 2 }}))"
                        fill="none" stroke="transparent" stroke-width="8" stroke-linecap="butt"
                        class="cursor-pointer" style="pointer-events: stroke;"
                        @mouseenter="hoveredSegment = 'vacant'"
                        @mousemove="handleHover($event, 'vacant')"
                        @mouseleave="handleLeave()"
                    />
                </svg>

                {{-- Center label --}}
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <template x-if="!hoveredSegment">
                        <div class="flex flex-col items-center transition-all duration-300">
                            <span class="text-2xl sm:text-3xl font-bold text-[#070642]">{{ $totalUnits }}</span>
                            <span class="text-xs text-gray-400 mt-0.5">Units</span>
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
                            <span class="text-2xl sm:text-3xl font-bold text-[#2563EB]" x-text="moveInReadyCount"></span>
                            <span class="text-[11px] text-gray-400 mt-0.5">Available</span>
                        </div>
                    </template>
                    <template x-if="hoveredSegment === 'vacant'">
                        <div class="flex flex-col items-center transition-all duration-300">
                            <span class="text-2xl sm:text-3xl font-bold text-[#60a5fa]" x-text="vacantCount"></span>
                            <span class="text-[11px] text-gray-400 mt-0.5">Vacant</span>
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
                                    <span class="font-semibold text-[13px]">Occupied</span>
                                </div>
                                <div class="text-white font-medium" x-text="occupiedCount + ' of ' + totalUnits + ' units'"></div>
                                <div class="text-gray-400 text-[11px]" x-text="'All beds have active leases'"></div>
                            </div>
                        </template>
                        <template x-if="hoveredSegment === 'available'">
                            <div class="space-y-0.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#2563EB] inline-block"></span>
                                    <span class="font-semibold text-[13px]">Available</span>
                                </div>
                                <div class="text-white font-medium" x-text="moveInReadyCount + ' of ' + totalUnits + ' units'"></div>
                                <div class="text-gray-400 text-[11px]" x-text="'At least one bed still open'"></div>
                            </div>
                        </template>
                        <template x-if="hoveredSegment === 'vacant'">
                            <div class="space-y-0.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#60a5fa] inline-block"></span>
                                    <span class="font-semibold text-[13px]">Vacant</span>
                                </div>
                                <div class="text-white font-medium" x-text="vacantCount + ' of ' + totalUnits + ' units'"></div>
                                <div class="text-gray-400 text-[11px]" x-text="'No active lease on any bed'"></div>
                            </div>
                        </template>
                        <div class="absolute left-1/2 -translate-x-1/2 -bottom-1 w-2 h-2 bg-[#070642] rotate-45"></div>
                    </div>
                </div>
            </div>

            {{-- Legend items --}}
            <div class="w-full mt-4 sm:mt-6 space-y-2 sm:space-y-3">
                {{-- Occupied --}}
                <div class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl bg-gray-50/80 group cursor-default transition-all duration-200 hover:bg-[#070589]/5"
                     @mouseenter="hoveredSegment = 'occupied'"
                     @mouseleave="handleLeave()">
                    <span class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-[#070589] shrink-0 ring-4 ring-[#070589]/10 transition-all duration-200 group-hover:ring-[#070589]/25"></span>
                    <div class="flex-1 min-w-0">
                        <p class="text-[11px] text-gray-400 uppercase tracking-wider">Occupied</p>
                        <p class="text-xs sm:text-sm font-bold text-[#070642] truncate">{{ $occupied }} Units</p>
                    </div>
                    <span class="text-sm sm:text-base font-bold text-[#070589] shrink-0">{{ $occupiedPercent }}%</span>
                </div>

                {{-- Available --}}
                <div class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl bg-gray-50/80 group cursor-default transition-all duration-200 hover:bg-[#2563EB]/5"
                     @mouseenter="hoveredSegment = 'available'"
                     @mouseleave="handleLeave()">
                    <span class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-[#2563EB] shrink-0 ring-4 ring-[#2563EB]/10 transition-all duration-200 group-hover:ring-[#2563EB]/25"></span>
                    <div class="flex-1 min-w-0">
                        <p class="text-[11px] text-gray-400 uppercase tracking-wider">Available</p>
                        <p class="text-xs sm:text-sm font-bold text-[#070642] truncate">{{ $moveInReady }} Units</p>
                    </div>
                    <span class="text-sm sm:text-base font-bold text-[#2563EB] shrink-0">{{ $moveInReadyPercent }}%</span>
                </div>

                {{-- Vacant --}}
                <div class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl bg-gray-50/80 group cursor-default transition-all duration-200 hover:bg-[#60a5fa]/5"
                     @mouseenter="hoveredSegment = 'vacant'"
                     @mouseleave="handleLeave()">
                    <span class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-[#60a5fa] shrink-0 ring-4 ring-[#60a5fa]/10 transition-all duration-200 group-hover:ring-[#60a5fa]/25"></span>
                    <div class="flex-1 min-w-0">
                        <p class="text-[11px] text-gray-400 uppercase tracking-wider">Vacant</p>
                        <p class="text-xs sm:text-sm font-bold text-[#070642] truncate">{{ $vacant }} Units</p>
                    </div>
                    <span class="text-sm sm:text-base font-bold text-[#60a5fa] shrink-0">{{ $vacantPercent }}%</span>
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
                <p class="text-xs text-gray-500">Available Units</p>
                <p class="font-bold text-gray-900">{{ $availableUnits }}</p>
            </div>
        </div>
    </div>

    {{-- Vacancy Metrics Card (Progress Bar + Blue Cards) --}}
    <div class="bg-white rounded-2xl shadow-md p-4 md:p-6 border border-gray-100">

        <h3 class="text-xl font-bold text-gray-900 mb-4">Vacancy Metrics</h3>

        {{-- Vacancy Rate Progress --}}
        <div class="mb-6">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-gray-700">Vacancy Rate</span>
                <span class="text-sm font-medium text-gray-700">{{ $vacantBeds }} of {{ $totalBeds }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-[#2360E8] h-2 rounded-full transition-all" style="width: {{ $vacancyPercent }}%"></div>
            </div>
            <div class="flex justify-end mt-1">
                <span class="text-xs text-gray-500">{{ $vacancyPercent }}%</span>
            </div>
        </div>

        <hr class="my-6 border-gray-200">

        {{-- Metric Cards --}}
        <div class="space-y-3">

            {{-- Fully Vacant Units --}}
            <div class="bg-[#2360E8] rounded-2xl p-4 flex items-center gap-4 text-white">
                <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center rounded-lg bg-[#629BF8]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-light text-blue-100">Fully Vacant Units</p>
                    <p class="text-2xl font-bold">{{ $vacant }} Units</p>
                </div>
            </div>

            {{-- Ready to Lease --}}
            <div class="bg-[#2360E8] rounded-2xl p-4 flex items-center gap-4 text-white">
                <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center rounded-lg bg-[#629BF8]">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M11.47 3.841a.75.75 0 011.06 0l8.69 8.69a.75.75 0 101.06-1.061l-8.689-8.69a2.25 2.25 0 00-3.182 0l-8.69 8.69a.75.75 0 101.061 1.06l8.69-8.689z"/>
                        <path d="M12 5.432l8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 01-.75-.75v-4.5a.75.75 0 00-.75-.75h-3a.75.75 0 00-.75.75V21a.75.75 0 01-.75.75H5.625a1.875 1.875 0 01-1.875-1.875v-6.198a2.29 2.29 0 00.091-.086L12 5.43z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-light text-blue-100">Ready to Lease</p>
                    <p class="text-2xl font-bold">{{ $availableUnits }} Units</p>
                </div>
            </div>

        </div>

    </div>

</div>
