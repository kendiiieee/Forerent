{{-- 
  MODIFIED: 
  This layout combines the new gender tabs with your
  original scrolling list and pagination.
--}}
<div class="w-full bg-white p-4 md:p-6 rounded-2xl shadow-md h-full flex flex-col">
    <h2 class="text-xl md:text-2xl font-bold text-gray-800 mb-4">Units</h2>

    {{-- Gender Tabs (From new design) --}}
    <div class="mb-4 border-b border-gray-200 flex-shrink-0">
        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" role="tablist">
            <li class="mr-2" role="presentation">
                <button
                    class="inline-block p-4 rounded-t-lg border-b-2 {{ $activeGender === 'female' ? 'text-blue-600 border-blue-600' : 'text-gray-500 border-transparent hover:text-gray-600 hover:border-gray-300' }}"
                    wire:click="selectGender('female')"
                    type="button"
                    role="tab">
                    Female
                </button>
            </li>
            <li class="mr-2" role="presentation">
                <button
                    class="inline-block p-4 rounded-t-lg border-b-2 {{ $activeGender === 'male' ? 'text-blue-600 border-blue-600' : 'text-gray-500 border-transparent hover:text-gray-600 hover:border-gray-300' }}"
                    wire:click="selectGender('male')"
                    type="button"
                    role="tab">
                    Male
                </button>
            </li>
        </ul>
    </div>

    {{-- 
      Unit List Container (Restored from original)
      MODIFIED: flex-1 makes this section fill the available space
    --}}
    <div class="flex-1 overflow-y-auto custom-scrollbar pr-2 space-y-3">

        {{-- Loop through the PAGINATED units --}}
        @foreach ($paginatedUnits as $unit)
        @php
        // Define the base, active, and inactive classes from your original file
        $baseClasses = 'w-full text-left font-semibold p-4 rounded-lg border-2 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-400';

        $isActive = ($unit['id'] == $this->activeUnitId);

        // Restored original button styles
        $activeClasses = 'bg-blue-600 text-white border-blue-600';
        $inactiveClasses = 'bg-transparent text-blue-600 border-gray-200 hover:bg-blue-50 hover:border-blue-500';
        @endphp

        <button
            type="button"
            wire:click="selectUnit({{ $unit['id'] }})"
            class="{{ $baseClasses }} {{ $isActive ? $activeClasses : $inactiveClasses }}">
            {{ $unit['name'] }}
        </button>
        @endforeach
    </div>

    {{-- Pagination Block (Restored from original) --}}
    @if ($totalPages > 1)
    <div class="flex justify-center items-center gap-2 mt-6 md:mt-8 flex-shrink-0">

        @if ($currentPage > 1)
        <flux:tooltip :content="'View previous set of units'" position="bottom">
            <button
                wire:click="previousPage"
                class="p-2 w-8 h-8 md:w-10 md:h-10 border-2 border-[#0039C6] bg-[#0039C6] text-white rounded-lg hover:bg-[#002A8F] transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 md:h-5 md:w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
            </button>
        </flux:tooltip>
        @endif

        <div class="flex gap-2">
            @for ($page = 1; $page <= $totalPages; $page++)
                <button
                wire:click="gotoPage({{ $page }})"
                class="py-2 px-3 md:px-4 w-8 h-8 md:w-10 md:h-10 flex items-center justify-center font-bold rounded-lg transition-colors text-sm md:text-base
                        {{ $currentPage === $page ? 'bg-[#0039C6] text-white' : 'border-2 border-gray-300 text-gray-700 hover:bg-gray-100' }}">
                {{ $page }}
                </button>
                @endfor
        </div>

        @if ($currentPage < $totalPages)
            <flux:tooltip :content="'View next set of units'" position="bottom">
                <button
                    wire:click="nextPage"
                    class="p-2 w-8 h-8 md:w-10 md:h-10 border-2 border-[#0039C6] bg-[#0039C6] text-white rounded-lg hover:bg-[#002A8F] transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 md:h-5 md:w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                </button>
            </flux:tooltip>
            @endif
    </div>
    @endif

</div>

{{-- Custom scrollbar styles (Restored from original) --}}
@push('styles')
<style>
    /* Custom Scrollbar Styling */
    .custom-scrollbar::-webkit-scrollbar {
        width: 8px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #0039C6;
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #002A8F;
    }
</style>
@endpush