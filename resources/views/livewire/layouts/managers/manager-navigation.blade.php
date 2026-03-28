<div class="w-full bg-white p-6 rounded-3xl shadow-lg h-full flex flex-col">
    {{-- Header Section --}}
    {{--
       responsive behavior:
       1. Mobile: Stacked (flex-col)
       2. Tablet (sm): Side-by-side (flex-row)
       3. Laptop (lg): Stacked (flex-col) -> This is the fix for "this size"
       4. Desktop (xl): Side-by-side (flex-row)
    --}}
    <div class="flex flex-col sm:flex-row lg:flex-col xl:flex-row items-start sm:items-center lg:items-start xl:items-center justify-between gap-3 mb-6">
        <h2 class="text-2xl font-bold text-[#0B0A3F]">Manager</h2>

        {{-- Add Manager Button --}}
        <x-ui.button-add
            text="Add Manager"
            wire:click="$dispatch('openManagerModal_manager-dashboard')"
        />
    </div>

    {{-- Manager List Container --}}
    <div class="flex-1 overflow-y-auto custom-scrollbar px-1 space-y-3">
        @forelse ($managers as $manager)
            @php
                $isActive = ($manager['user_id'] == $this->activeManagerId);

                $buttonClasses = $isActive
                    ? 'bg-blue-600 text-white shadow-md ring-2 ring-blue-600 ring-offset-1'
                    : 'bg-white text-gray-700 border border-gray-200 hover:bg-blue-50 hover:text-blue-700 hover:border-blue-300';
            @endphp

            <button
                type="button"
                wire:click="selectManager({{ $manager->user_id }})"
                class="w-full text-left font-semibold p-4 rounded-xl transition-all duration-200 focus:outline-none {{ $buttonClasses }}"
            >
                {{ $manager['first_name'] }} {{ $manager['last_name'] }}
            </button>
        @empty
            <div class="flex flex-col items-center justify-center py-10 text-center">
                <div class="bg-gray-100 p-4 rounded-full mb-3">
                    <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <p class="text-gray-500 font-medium">No managers found.</p>
                <p class="text-xs text-gray-400 mt-1 mb-4">Get started by adding a new manager.</p>
            </div>
        @endforelse
    </div>
</div>


