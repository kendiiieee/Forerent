@props([
    'tabs' => [],
    'activeTab' => '',
    'filters' => null,
    'counts' => [] // 1. New Prop for the numbers
])

<div>
    {{-- Header (outside the card) --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-4">

        {{-- Tabs --}}
        @if(count($tabs) > 0)
            <x-ui.sort-tab
                :tabs="$tabs"
                :activeTab="$activeTab"
                :counts="$counts"
                action="setTab"
            />
        @endif

        {{-- Filters Slot --}}
        @if($filters)
            <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto justify-end">
                {{ $filters }}
            </div>
        @endif
    </div>

    {{-- Card (table content only) --}}
    <div class="bg-white rounded-[20px] md:rounded-[30px] shadow-sm p-4 md:p-8 min-h-[600px] flex flex-col relative">

        {{-- Content --}}
        <div class="relative flex-grow">
            {{ $slot }}
        </div>

        {{-- Footer --}}
        @if(isset($footer))
            <div class="mt-4 pt-4 border-t border-gray-100">
                <div class="flex justify-center">
                    {{ $footer }}
                </div>
            </div>
        @endif
    </div>
</div>
