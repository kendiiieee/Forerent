<div id="calendar-card" class="bg-white rounded-xl shadow-md overflow-hidden w-full flex flex-col">
    <div class="px-4 sm:px-6 py-4 border-b border-gray-100">
        <h3 class="text-lg font-semibold text-[#070642]">Calendar</h3>
    </div>

    <div class="flex-1 min-h-0 flex flex-col lg:flex-row">
        {{-- Calendar Panel --}}
        <div id="calendar-grid-panel" class="lg:w-[340px] lg:min-w-[340px] p-4 border-b lg:border-b-0 lg:border-r border-gray-100">
            <div class="max-w-sm mx-auto">
                {{-- Calendar Header with Navigation --}}
                <div class="flex items-center justify-between mb-4">
                    <button
                        wire:click="previousMonth"
                        type="button"
                        class="p-1 hover:bg-gray-100 rounded transition-colors"
                        title="Previous month"
                    >
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <h3 class="text-sm font-bold text-gray-900 text-center flex-1">{{ $currentMonth }}</h3>

                    <button
                        wire:click="nextMonth"
                        type="button"
                        class="p-1 hover:bg-gray-100 rounded transition-colors"
                        title="Next month"
                    >
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>

                {{-- Return to Today Button --}}
                @if(\Carbon\Carbon::parse($selectedDate)->format('Y-m') !== \Carbon\Carbon::now()->format('Y-m'))
                <button
                    wire:click="currentMonth"
                    type="button"
                    class="w-full mb-3 text-xs font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded px-2 py-1 transition-colors"
                    title="Return to today"
                >
                    Today
                </button>
                @endif

                {{-- Calendar Grid --}}
                <div class="grid grid-cols-7 gap-1 text-center">
                    @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day)
                    <div class="text-xs font-medium text-gray-600 pb-1">{{ $day }}</div>
                    @endforeach

                    @foreach($calendarDays as $day)
                        @if($day === null)
                            <div class="aspect-square"></div>
                        @else
                            <button
                                wire:click="selectDate('{{ $currentYear }}-{{ date('m', strtotime($currentMonth)) }}-{{ $day }}')"
                                class="aspect-square flex flex-col items-center justify-center rounded text-xs transition-all
                                {{ \Carbon\Carbon::parse($selectedDate)->day == $day && \Carbon\Carbon::parse($selectedDate)->format('m') == date('m', strtotime($currentMonth))
                                    ? 'bg-blue-700 text-white font-bold'
                                    : 'hover:bg-gray-100 text-gray-700' }}">
                                <span>{{ $day }}</span>

                                {{-- Dot for days with announcements --}}
                                @if(in_array($day, $announcementDates))
                                    <span class="w-1.5 h-1.5 mt-0.5 rounded-full
                                    {{ \Carbon\Carbon::parse($selectedDate)->day == $day && \Carbon\Carbon::parse($selectedDate)->format('m') == date('m', strtotime($currentMonth))
                                        ? 'bg-white'
                                        : 'bg-blue-700' }}">
                                    </span>
                                @endif
                            </button>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Selected Date Announcements Panel --}}
        <div id="selected-date-panel" class="flex-1 min-h-0 flex flex-col overflow-hidden">
            <div class="bg-blue-700 px-6 py-3">
                <p class="text-[11px] font-medium uppercase tracking-wide text-blue-100">Selected Date</p>
                <h3 class="text-white text-sm font-semibold">{{ \Carbon\Carbon::parse($selectedDate)->format('F d, Y') }}</h3>
            </div>
            <div class="flex-1 min-h-0 p-6 overflow-y-auto pr-3">
                @forelse($dailyAnnouncements as $dailyAnnouncement)
                    <div class="border-b border-gray-200 pb-4 mb-4 last:border-0 last:pb-0 last:mb-0">
                        <h4 class="text-base font-bold text-gray-900 mb-1">{{ $dailyAnnouncement->headline }}</h4>
                        <p class="text-sm text-gray-600">{{ $dailyAnnouncement->details }}</p>
                    </div>
                @empty
                    <div class="h-full flex items-center justify-center">
                        <p class="text-gray-500 text-center">No announcements for selected date</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@once
<script>
    function syncSelectedDatePanelHeight() {
        const calendarGridPanel = document.getElementById('calendar-grid-panel');
        const selectedDatePanel = document.getElementById('selected-date-panel');

        if (!calendarGridPanel || !selectedDatePanel) {
            return;
        }

        if (window.innerWidth < 1024) {
            selectedDatePanel.style.height = '';
            return;
        }

        selectedDatePanel.style.height = `${calendarGridPanel.getBoundingClientRect().height}px`;
    }

    function runSelectedDateHeightSync() {
        syncSelectedDatePanelHeight();
        requestAnimationFrame(syncSelectedDatePanelHeight);
        setTimeout(syncSelectedDatePanelHeight, 50);
    }

    document.addEventListener('DOMContentLoaded', runSelectedDateHeightSync);
    document.addEventListener('livewire:navigated', runSelectedDateHeightSync);
    window.addEventListener('resize', runSelectedDateHeightSync);

    if (window.ResizeObserver) {
        const observer = new ResizeObserver(() => runSelectedDateHeightSync());
        document.addEventListener('DOMContentLoaded', () => {
            const calendarGridPanel = document.getElementById('calendar-grid-panel');
            if (calendarGridPanel) {
                observer.observe(calendarGridPanel);
            }
        });
    }
</script>
@endonce
