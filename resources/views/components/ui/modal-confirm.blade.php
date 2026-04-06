@props([
    'name',
    'title',
    'description',
    'confirmText' => 'Save',
    'cancelText' => 'Cancel',
    'confirmAction' => null,
    'cancelUrl' => null,
])

<div
    x-data="{ show: false }"
    x-show="show"
    {{-- FIX: Listen for both String and Array event formats --}}
    x-on:open-modal.window="if ($event.detail === '{{ $name }}' || $event.detail[0] === '{{ $name }}') show = true"
    x-on:close-modal.window="if ($event.detail === '{{ $name }}' || $event.detail[0] === '{{ $name }}') show = false"
    x-on:keydown.escape.window="show = false"
    class="fixed inset-0 z-[99] flex items-center justify-center px-4 py-6 sm:px-0"
    style="display: none;"
>
    {{-- Backdrop --}}
    <div x-show="show" class="fixed inset-0 transform transition-all" x-on:click="show = false">
        <div class="absolute inset-0 bg-gray-600 opacity-50"></div>
    </div>

    <div x-show="show" class="bg-white rounded-[20px] overflow-hidden shadow-xl transform transition-all sm:w-full sm:max-w-[480px] p-8 relative z-[100]">

        <button @click="show = false" class="absolute top-5 right-5 text-[#0C0B50] hover:text-blue-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <div class="text-center mt-4 {{ $slot->isEmpty() ? 'mb-8' : 'mb-4' }}">
            <h3 class="text-2xl font-bold text-[#0C0B50] mb-3">{{ $title }}</h3>
            <p class="text-gray-500 text-sm leading-relaxed px-4">{{ $description }}</p>
        </div>

        @if(!$slot->isEmpty())
            <div class="mb-6 px-2">
                {{ $slot }}
            </div>
        @endif

        <div class="flex justify-center gap-4 px-2">
            {{-- Cancel Button --}}
            @if($cancelUrl)
                <a href="{{ $cancelUrl }}" class="flex-1 bg-[#D6E6FF] hover:bg-[#c3daff] text-[#0C0B50] font-bold py-3 rounded-xl transition-colors text-sm flex items-center justify-center">
                    {{ $cancelText }}
                </a>
            @else
                <button @click="show = false" class="flex-1 bg-[#D6E6FF] hover:bg-[#c3daff] text-[#0C0B50] font-bold py-3 rounded-xl transition-colors text-sm">
                    {{ $cancelText }}
                </button>
            @endif

            {{-- Confirm Button --}}
            {{-- FIX: Removed @click="show = false" so Livewire has time to fire --}}
            <button
                @if($confirmAction) wire:click="{{ $confirmAction }}" @endif
                wire:loading.attr="disabled"
                class="flex-1 bg-[#104EA2] hover:bg-[#0d3f82] text-white font-bold py-3 rounded-xl transition-colors shadow-md text-sm disabled:opacity-50 disabled:cursor-not-allowed"
            >
                {{-- Optional: Show loading text --}}
                <span wire:loading.remove wire:target="{{ $confirmAction }}">
                    {{ $confirmText }}
                </span>
                <span wire:loading wire:target="{{ $confirmAction }}">
                    Processing...
                </span>
            </button>
        </div>
    </div>
</div>
