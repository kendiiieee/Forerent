<div class="w-full min-h-full">
    {{-- Dynamic Content Switcher --}}
    @if($currentView === 'reports')

    <livewire:layouts.revenue-forecast />
    <div class=" my-4"></div>

    <livewire:layouts.financials.revenue-reports />

    @elseif($currentView === 'records')
    <livewire:layouts.financials.revenue-records />
    @endif
</div>