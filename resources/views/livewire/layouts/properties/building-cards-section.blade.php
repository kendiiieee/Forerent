<div
    x-init="
        @if($selectedBuilding)
            $nextTick(() => {
                Livewire.dispatch('buildingSelected', { buildingId: {{ $selectedBuilding }} });
                @if($eventName !== 'buildingSelected')
                    Livewire.dispatch('{{ $eventName }}', { id: {{ $selectedBuilding }} });
                @endif
            });
        @endif
    "
>
    {{-- Header --}}
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-bold text-gray-900">{{ $title }}</h3>

        <div class="flex items-center gap-2">
            @if($showAddButton)
                <x-ui.button-add
                    text="Add Property"
                    onclick="Livewire.dispatch('{{ $addButtonEvent }}')"
                />
            @endif

            @if($showAddUnitButton)
                <x-ui.button-add
                    text="Add Unit"
                    onclick="Livewire.dispatch('{{ $addUnitButtonEvent }}')"
                />
            @endif
        </div>
    </div>

    {{-- Horizontal Scroll Cards --}}
    <style>
        .building-scroll {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .building-scroll::-webkit-scrollbar {
            display: none;
        }

        /* Selected building card */
        .selected-building > div {
            background: linear-gradient(135deg, #1D56D9 0%, #2360E8 50%, #2D6FF5 100%) !important;
            box-shadow: 0 4px 20px rgba(35, 96, 232, 0.35);
        }
        .selected-building .building-card-title,
        .selected-building .building-card-address {
            color: white !important;
        }
    </style>

    <div
        x-data="{
            scrollContainer: null,
            canScrollLeft: false,
            canScrollRight: false,
            hovered: false,
            selectedId: {{ $selectedBuilding ?? 'null' }},
            init() {
                this.scrollContainer = this.$refs.scroller;
                this.checkScroll();
                this.scrollContainer.addEventListener('scroll', () => this.checkScroll());
                new ResizeObserver(() => this.checkScroll()).observe(this.scrollContainer);
                // Recheck after Livewire child components finish rendering
                setTimeout(() => this.checkScroll(), 100);
                setTimeout(() => this.checkScroll(), 500);
            },
            checkScroll() {
                const el = this.scrollContainer;
                if (!el) return;
                this.canScrollLeft = el.scrollLeft > 0;
                this.canScrollRight = el.scrollLeft < el.scrollWidth - el.clientWidth - 1;
            },
            scrollBy(direction) {
                this.scrollContainer.scrollBy({ left: direction * 300, behavior: 'smooth' });
            }
        }"
        class="relative"
        @mouseenter="hovered = true"
        @mouseleave="hovered = false"
    >
        {{-- Left fade + arrow --}}
        <div
            x-show="canScrollLeft && hovered"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-x-2"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 -translate-x-2"
            class="absolute left-0 top-0 bottom-0 z-10 flex items-center"
            style="display: none;"
        >
            {{-- Gradient fade --}}
            <div class="absolute left-0 top-0 bottom-0 w-20 bg-gradient-to-r from-[#F4F7FC] via-[#F4F7FC]/80 to-transparent pointer-events-none"></div>

            {{-- Arrow button --}}
            <button
                @click="scrollBy(-1)"
                class="relative ml-2 w-10 h-10 rounded-full bg-white shadow-lg border border-gray-200/80 flex items-center justify-center text-gray-700 hover:bg-[#2360E8] hover:text-white hover:border-[#2360E8] hover:shadow-[0_4px_14px_rgba(35,96,232,0.35)] transition-all duration-200 cursor-pointer active:scale-90"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
                </svg>
            </button>
        </div>

        {{-- Right fade + arrow --}}
        <div
            x-show="canScrollRight && hovered"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-x-2"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-2"
            class="absolute right-0 top-0 bottom-0 z-10 flex items-center justify-end"
            style="display: none;"
        >
            {{-- Gradient fade --}}
            <div class="absolute right-0 top-0 bottom-0 w-20 bg-gradient-to-l from-[#F4F7FC] via-[#F4F7FC]/80 to-transparent pointer-events-none"></div>

            {{-- Arrow button --}}
            <button
                @click="scrollBy(1)"
                class="relative mr-2 w-10 h-10 rounded-full bg-white shadow-lg border border-gray-200/80 flex items-center justify-center text-gray-700 hover:bg-[#2360E8] hover:text-white hover:border-[#2360E8] hover:shadow-[0_4px_14px_rgba(35,96,232,0.35)] transition-all duration-200 cursor-pointer active:scale-90"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                </svg>
            </button>
        </div>

        {{-- Scrollable Container --}}
        <div x-ref="scroller" class="building-scroll flex gap-4 overflow-x-auto py-2 px-1">
            @forelse ($properties as $property)
                <div
                    wire:key="building-{{ $property['property_id'] }}"
                    @click="
                        if (selectedId === {{ $property['property_id'] }}) return;
                        selectedId = {{ $property['property_id'] }};
                        Livewire.dispatch('buildingSelected', { buildingId: {{ $property['property_id'] }} });
                    "
                    class="cursor-pointer rounded-lg transition-all duration-300"
                    :class="selectedId === {{ $property['property_id'] }} ? 'selected-building' : 'hover:shadow-lg'"
                >
                    @include('livewire.layouts.properties.buildingcard', ['property' => (object) $property])
                </div>
            @empty
                <div class="w-full flex flex-col items-center justify-center text-center p-16 border-2 border-dashed border-gray-300 rounded-lg bg-white">
                    <h3 class="text-xl font-semibold text-gray-700">
                        {{ $emptyStateTitle }}
                    </h3>
                    <p class="text-gray-500 mt-2">
                        {{ $emptyStateDescription }}
                    </p>

                    @if($showAddButton)
                        <x-ui.button-add
                            text="Add Your First Property"
                            onclick="Livewire.dispatch('{{ $addButtonEvent }}')"
                            class="mt-4"
                        />
                    @endif
                </div>
            @endforelse
        </div>
    </div>
</div>
