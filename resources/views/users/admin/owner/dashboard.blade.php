@section('header-title', 'DASHBOARD')
@section('header-subtitle', 'Centralized rental property management overview')

<div class="w-full space-y-6">

    @include('livewire.layouts.dashboard.admingreeting')

    {{-- 1. Notifications + Calendar Section --}}
    <livewire:layouts.dashboard.announcement-list :is-landlord="true" />
    <livewire:layouts.dashboard.calendar-widget />

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
        <div class="bg-white rounded-2xl p-6 shadow-lg">
            <p class="text-xs uppercase tracking-wide text-gray-500">Total Units</p>
            <p class="mt-2 text-3xl font-bold text-[#070642]">{{ number_format($totalUnits) }}</p>
            <p class="mt-1 text-sm text-gray-500">All units in inventory</p>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-lg">
            <p class="text-xs uppercase tracking-wide text-gray-500">Fully Booked Units</p>
            <p class="mt-2 text-3xl font-bold text-[#070642]">{{ number_format($fullyBookedUnits) }}</p>
            <p class="mt-1 text-sm text-gray-500">All beds have active leases</p>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-lg">
            <p class="text-xs uppercase tracking-wide text-gray-500">Available Units</p>
            <p class="mt-2 text-3xl font-bold text-[#2B66F5]">{{ number_format($availableUnits) }}</p>
            <p class="mt-1 text-sm text-gray-500">At least one bed still open</p>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-lg">
            <p class="text-xs uppercase tracking-wide text-gray-500">Vacant Units</p>
            <p class="mt-2 text-3xl font-bold text-[#F5652B]">{{ number_format($vacantUnits) }}</p>
            <p class="mt-1 text-sm text-gray-500">No active lease on any bed</p>
        </div>
    </div>

    {{-- 2. Financial Overview with Graphs --}}
    <div class="space-y-6">
        <h3 class="text-2xl font-bold text-[#070642]">Financial Overview</h3>

        {{-- Graph Layout: Large left, single summary card right --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch">

            {{-- Left: Revenue vs Expenses (spans 2 columns) --}}
            <div class="lg:col-span-2 h-full" wire:ignore>
                @include('livewire.layouts.dashboard.revenue-expenses-chart')
            </div>

            {{-- Right Column: Single rent summary card --}}
            <div>
                @include('livewire.layouts.dashboard.rent-collected-chart')
            </div>
        </div>
    </div>

    {{-- Modal (Hidden by default) --}}
    <livewire:layouts.dashboard.announcement-modal />

</div>

