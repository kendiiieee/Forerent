@props(['active' => false])

<button
    type="button"
    {{ $attributes->merge(['class' => 'w-full text-left px-4 py-2 hover:bg-blue-50 focus:bg-blue-50 focus:outline-none cursor-pointer text-sm flex items-center justify-between transition-colors ' . ($active ? 'text-blue-600 font-bold bg-blue-50' : 'text-gray-600')]) }}
>
    <span class="min-w-0 flex-1 truncate" title="{{ trim(strip_tags((string) $slot)) }}">{{ $slot }}</span>

    @if($active)
        <svg class="w-4 h-4 text-blue-600 shrink-0 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
    @endif
</button>
