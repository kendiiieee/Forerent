<div class="space-y-3">
    <div>
        <h3 class="text-2xl font-bold text-gray-900">Maintenance</h3>
        <p class="mt-1 text-sm text-gray-500">
            @if($costDeltaPercent !== null)
                This summary compares maintenance spending so far this year with the same period last year. Costs are {{ $costTrendLabel }} {{ $costDeltaPercent }}% year-to-date.
            @else
                This summary compares maintenance spending year-to-date, but last year’s data is not enough for a fair comparison.
            @endif
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{--   Total Maintenance Cost --}}
        <div class="bg-[#263093] rounded-2xl p-6 text-white shadow-lg flex flex-col justify-between min-h-[140px]">
            <div>
                <p class="text-base font-medium opacity-90 mb-1">Total Maintenance Cost</p>
                <p class="text-3xl font-bold">&#8369; {{ number_format($totalCost) }}</p>
            </div>
            <p class="text-xs text-blue-200 mt-4">{{ $currentDate }}</p>
        </div>

        {{--   New Requests --}}
        <div class="bg-[#263093] rounded-2xl p-6 text-white shadow-lg flex flex-col justify-between min-h-[140px]">
            <div>
                <p class="text-base font-medium opacity-90 mb-1">New Requests</p>
                <p class="text-3xl font-bold">{{ $newRequests }}</p>
            </div>
            <p class="text-xs text-blue-200 mt-4">{{ $currentDate }}</p>
        </div>

        {{-- Pending Requests --}}
        <div class="bg-[#263093] rounded-2xl p-6 text-white shadow-lg flex flex-col justify-between min-h-[140px]">
            <div>
                <p class="text-base font-medium opacity-90 mb-1">Pending Requests</p>
                <p class="text-3xl font-bold">{{ $pendingRequests }}</p>
            </div>
            <p class="text-xs text-blue-200 mt-4">{{ $currentDate }}</p>
        </div>

    </div>
</div>
