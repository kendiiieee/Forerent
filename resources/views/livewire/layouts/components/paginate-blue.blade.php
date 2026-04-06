<div>
    @if ($paginator->hasPages())
        <div class="flex flex-col items-center gap-3 mt-4">

            {{-- 1. The Buttons Row --}}
            <div class="flex flex-wrap justify-center items-center gap-1">

                {{-- PREVIOUS PAGE LINK (<) --}}
                @if ($paginator->onFirstPage())
                    <span class="w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center rounded-md bg-gray-100 text-gray-400 border border-gray-200 cursor-not-allowed">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 1 1 5l4 4"/>
                        </svg>
                    </span>
                @else
                    <flux:tooltip :content="'Go to the previous page'" position="bottom">
                        <button wire:click="previousPage('{{ $paginator->getPageName() }}')" class="w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center rounded-md bg-[#0631AA] text-white hover:bg-blue-800 transition-colors">
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 1 1 5l4 4"/>
                            </svg>
                        </button>
                    </flux:tooltip>
                @endif

                {{-- PAGINATION NUMBERS --}}
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center text-gray-500 text-xs sm:text-sm">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center rounded-md bg-white border border-gray-200 text-[#0631AA] font-bold shadow-sm text-xs sm:text-sm">
                                    {{ $page }}
                                </span>
                            @else
                                <button wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" class="w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center rounded-md bg-[#0631AA] text-white hover:bg-blue-800 transition-colors text-xs sm:text-sm">
                                        {{ $page }}
                                    </button>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- NEXT PAGE LINK (>) --}}
                @if ($paginator->hasMorePages())
                    <flux:tooltip :content="'Go to the next page'" position="bottom">
                        <button wire:click="nextPage('{{ $paginator->getPageName() }}')" class="w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center rounded-md bg-[#0631AA] text-white hover:bg-blue-800 transition-colors">
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                            </svg>
                        </button>
                    </flux:tooltip>
                @else
                    <span class="w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center rounded-md bg-gray-100 text-gray-400 border border-gray-200 cursor-not-allowed">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                        </svg>
                    </span>
                @endif
            </div>

            {{-- Summary Text --}}
            <div class="text-xs sm:text-sm text-gray-600 font-medium text-center">
                Showing <span class="font-bold text-gray-900">{{ $paginator->firstItem() }}</span> to <span class="font-bold text-gray-900">{{ $paginator->lastItem() }}</span> of <span class="font-bold text-gray-900">{{ $paginator->total() }}</span> results
            </div>

        </div>
    @endif
</div>
