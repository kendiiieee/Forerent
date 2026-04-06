<div class="flex flex-col w-full" style="font-family: 'Open Sans', sans-serif;">

    {{-- 1. ADD MANAGER BUTTON --}}
    <div class="flex justify-end mb-4">
        <x-ui.button-add
            text="Add Manager"
            tooltip="Assign a new manager to your properties"
            wire:click="$dispatch('openManagerModal_manager-dashboard')"
        />
    </div>

    {{-- 2. MAIN CONTENT GRID --}}
    <div id="units-container" class="flex flex-col lg:flex-row gap-6 h-full">

        {{-- Sidebar List --}}
        <div id="unit-navigation-sidebar" class="w-full lg:w-[30%] flex-shrink-0 h-[750px]">
            <div class="w-full bg-white p-6 rounded-3xl shadow-lg h-full flex flex-col">
                {{-- Header + Search --}}
                <div class="p-0 pb-3 border-b border-gray-50 flex-shrink-0">
                    <h2 class="text-2xl font-bold text-[#0B0A3F] mb-3">Manager</h2>

                    {{-- Search Bar --}}
                    <div class="relative">
                        <input
                            type="text"
                            placeholder="Search by manager name..."
                            wire:model.live="search"
                            class="w-full bg-[#F4F6FB] border border-slate-200 rounded-xl py-2.5 pl-4 pr-10 text-xs focus:outline-none focus:ring-2 focus:ring-blue-200 placeholder-slate-400 text-slate-700 transition"
                        >
                        <svg class="w-4 h-4 text-slate-400 absolute right-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                {{-- Manager List --}}
                <div class="flex-1 overflow-y-auto custom-scrollbar px-1 pt-1 space-y-3 mt-3">
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
        </div>

        {{-- Detail Card --}}
        <div id="unit-detail-card" class="w-full lg:w-[70%] h-[750px]">
            <livewire:layouts.managers.manager-detail :initialManagerId="$activeManagerId" />
        </div>
    </div>
</div>
