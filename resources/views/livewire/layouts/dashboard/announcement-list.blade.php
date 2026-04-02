<div id="announcement-card" class="bg-white rounded-xl shadow-md overflow-hidden w-full flex flex-col">
    <div class="bg-blue-800 px-4 sm:px-6 py-3 sm:py-4 flex justify-between items-center">
        <h3 class="text-white text-base sm:text-lg font-semibold">Announcement</h3>

       {{-- Show Add button for Landlord and Manager only --}}
        @if(in_array($role, ['landlord', 'manager'], true))
            <button
                wire:click="$dispatch('open-announcement-modal')"
                type="button"
                class="inline-flex items-center gap-1.5 sm:gap-2 text-white hover:text-gray-200 transition-colors text-sm font-semibold"
                title="Create Announcement">
                <span class="hidden sm:inline">Create Announcement</span>
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </button>
        @endif
    </div>
    <div class="p-4 sm:p-6 space-y-4 overflow-y-auto" style="max-height: 300px;">
        @forelse($announcements as $announcement)
        <div class="border-b border-gray-200 pb-4 last:border-0 last:pb-0 flex justify-between items-start group">
            <div class="flex-1">
                <div class="text-sm text-blue-700 font-semibold mb-1">{{ $announcement->notification_date ? $announcement->notification_date->format('M d, Y') : $announcement->created_at->format('M d, Y') }}</div>
                <h4 class="text-base font-bold text-gray-900 mb-1">{{ $announcement['headline'] }}</h4>
                <p class="text-sm text-gray-600">{{ $announcement['details'] }}</p>
            </div>

            {{-- Edit button (show only for owners/managers) --}}
            @if(in_array($role, ['landlord', 'manager'], true))
            <button
                wire:click="$dispatch('edit-announcement', { announcementId: {{ $announcement->announcement_id }} })"
                type="button"
                class="ml-3 text-gray-400 hover:text-blue-700 transition-colors opacity-0 group-hover:opacity-100 focus:outline-none"
                title="Edit announcement"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </button>
            @endif
        </div>
        @empty
        <p class="text-gray-500 text-center py-4">No announcements yet</p>
        @endforelse
    </div>
</div>
