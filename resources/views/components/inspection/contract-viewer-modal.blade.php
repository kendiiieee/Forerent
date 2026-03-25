@props([
    'show' => false,
    'title' => 'Contract',
    'wireCloseMethod' => 'closeMoveInContract',
    'contractId' => 'contract-content',
])

@if($show)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm">
        <div class="relative w-full max-w-4xl bg-white rounded-2xl shadow-xl overflow-hidden max-h-[90vh] flex flex-col">
            <div class="bg-[#070589] text-white p-5 flex items-center justify-between flex-shrink-0">
                <h2 class="text-lg font-bold">{{ $title }}</h2>
                <button wire:click="{{ $wireCloseMethod }}" class="text-white hover:text-blue-200">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-8 space-y-6 text-sm text-gray-800" id="{{ $contractId }}" style="font-family: 'Open Sans', sans-serif;">
                {{ $slot }}
            </div>
            <div class="p-4 bg-gray-50 border-t flex justify-end gap-3 flex-shrink-0">
                {{ $footer ?? '' }}
            </div>
        </div>
    </div>
@endif
