<x-ui.magic-card class="px-6 py-8 flex flex-col justify-between h-full min-h-96">
    <h4 class="text-lg font-bold text-gray-900 mb-6 text-center">{{ $title }}</h4>

    <div class="flex flex-col items-center justify-center flex-1 gap-6 w-full">
        {{-- Gauge SVG --}}
        {{-- FIX: Changed w-64 to w-full max-w-[16rem] so it shrinks --}}
        <div class="relative w-full max-w-[16rem] aspect-2/1 mx-auto">
            <svg class="w-full h-full" viewBox="0 0 100 50" preserveAspectRatio="xMidYMid meet">
                <path d="M 10 50 A 40 40 0 0 1 90 50" fill="none" stroke="#E5E7EB" stroke-width="6" stroke-linecap="round"/>
                <path d="M 10 50 A 40 40 0 0 1 90 50" fill="none" stroke="#1D4ED8" stroke-width="6" stroke-linecap="round"
                      stroke-dasharray="{{ (min($percentage, 100) / 100) * 126 }}, 126" />
            </svg>
            <div class="absolute inset-0 flex items-end justify-center pb-1">
                <span class="text-2xl font-bold text-gray-900">{{ $percentage }}%</span>
            </div>
        </div>

        {{-- Stats --}}
        <div class="w-full space-y-2 px-0 sm:px-2">
            <div class="flex flex-wrap justify-between items-center text-center sm:text-left gap-2">
                <span class="text-sm font-medium text-gray-500">Current Value</span>
                <span class="text-lg font-bold text-gray-900 whitespace-nowrap">{{ $prefix }}{{ $currentValue }}{{ $suffix }}</span>
            </div>
            <div class="flex flex-wrap justify-between items-center text-center sm:text-left border-t border-dashed border-gray-200 pt-2 gap-2">
                <span class="text-sm font-medium text-gray-500">Target Value</span>
                <span class="text-lg font-bold text-gray-900 whitespace-nowrap">{{ $prefix }}{{ $targetValue }}</span>
            </div>
        </div>
    </div>
</x-ui.magic-card>
