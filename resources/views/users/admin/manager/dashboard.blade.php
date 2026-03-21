@extends('layouts.app')

@section('header-title', 'DASHBOARD')
@section('header-subtitle', 'Centralized rental property management overview')

@section('content')

     @include('livewire.layouts.dashboard.admingreeting')

    @if (session()->has('message'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg relative" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <div class="w-full space-y-6">
        <livewire:layouts.dashboard.maintenance-stats />

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 items-start">
            <div id="announcement-card-shell" class="h-full">
                <livewire:layouts.dashboard.announcement-list :is-manager="true" />
            </div>
            <div id="calendar-card-shell" class="h-full">
                <livewire:layouts.dashboard.calendar-widget />
            </div>
        </div>

        <livewire:layouts.dashboard.announcement-modal />

    </div>

    @once
    <script>
        function syncAnnouncementToCalendarHeight() {
            const announcementShell = document.getElementById('announcement-card-shell');
            const announcementCard = document.getElementById('announcement-card');
            const calendarCard = document.getElementById('calendar-card');

            if (!announcementShell || !announcementCard || !calendarCard) {
                return;
            }

            if (window.innerWidth < 1280) {
                announcementShell.style.height = '';
                announcementCard.style.height = '';
                return;
            }

            announcementShell.style.height = '';
            announcementCard.style.height = '';

            const calendarHeight = calendarCard.getBoundingClientRect().height;
            if (calendarHeight > 0) {
                announcementShell.style.height = `${calendarHeight}px`;
                announcementCard.style.height = '100%';
            }
        }

        function runHeightSyncSequence() {
            syncAnnouncementToCalendarHeight();
            requestAnimationFrame(syncAnnouncementToCalendarHeight);
            setTimeout(syncAnnouncementToCalendarHeight, 50);
            setTimeout(syncAnnouncementToCalendarHeight, 200);
        }

        document.addEventListener('DOMContentLoaded', runHeightSyncSequence);
        document.addEventListener('livewire:navigated', runHeightSyncSequence);
        window.addEventListener('resize', runHeightSyncSequence);
        runHeightSyncSequence();

        if (window.ResizeObserver) {
            const observer = new ResizeObserver(() => runHeightSyncSequence());
            document.addEventListener('DOMContentLoaded', () => {
                const calendarCard = document.getElementById('calendar-card');
                if (calendarCard) {
                    observer.observe(calendarCard);
                }
            });
        }

        if (window.MutationObserver) {
            const mutationObserver = new MutationObserver(() => runHeightSyncSequence());
            document.addEventListener('DOMContentLoaded', () => {
                const calendarCard = document.getElementById('calendar-card');
                if (calendarCard) {
                    mutationObserver.observe(calendarCard, { childList: true, subtree: true, attributes: true });
                }
            });
        }
    </script>
    @endonce

@endsection
