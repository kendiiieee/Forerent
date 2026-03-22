@section('header-title', 'DASHBOARD')
@section('header-subtitle', 'Centralized rental property management overview')

<div class="w-full space-y-6">

    @include('livewire.layouts.dashboard.admingreeting')

    {{-- 1. Announcements --}}
    <livewire:layouts.dashboard.announcement-list :is-landlord="true" />

    {{-- 2. Calendar --}}
    <livewire:layouts.dashboard.calendar-widget />

    {{-- 3. Financial Overview --}}
    <div class="space-y-6">
        <h3 class="text-2xl font-bold text-[#070642]">Financial Overview</h3>

        {{--  Blue Cards (Donut) --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 2xl:grid-cols-3 gap-6">

            <livewire:layouts.dashboard.donut-stat-card
                title="Total Rent Collected"
                :amount="$totalRentCollected"
                label="Collected"
                :percentage="$rentCollectedPercentage"
            />

            <livewire:layouts.dashboard.donut-stat-card
                title="Total Uncollected Rent"
                :amount="$totalUncollectedRent"
                label="Uncollected"
                :percentage="$uncollectedPercentage"
            />

            <livewire:layouts.dashboard.donut-stat-card
                title="Total Income"
                :amount="$totalIncome"
                label="Collected"
                :percentage="$incomePercentage"
            />
        </div>

        {{-- White Cards (Gauge) --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 2xl:grid-cols-3 gap-6">

            {{-- Revenue --}}
            <livewire:layouts.dashboard.gauge-stat
                title="Revenue"
                :current="$revenueCurrent"
                :target="$revenueTarget"
            />

            {{-- Expenses --}}
            <livewire:layouts.dashboard.gauge-stat
                title="Total Expenses"
                :current="$expensesCurrent"
                :target="$expensesTarget"
            />

            {{-- ROI --}}
            <livewire:layouts.dashboard.gauge-stat
                title="Return On Investment"
                :current="$roiCurrent"
                :target="$roiTarget"
                prefix="+ "
                suffix="%"
            />

        </div>
    </div>

    {{-- Modal (Hidden by default) --}}
    <livewire:layouts.dashboard.announcement-modal />

</div>
