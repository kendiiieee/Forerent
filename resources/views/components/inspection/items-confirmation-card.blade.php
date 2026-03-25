@props([
    'title' => 'Items',
    'subtitle' => 'Confirm the items',
    'items' => [],
    'allConfirmed' => false,
    'accentColor' => 'indigo', // 'indigo' or 'orange'
    'wireConfirmMethod' => 'confirmItemReceived',
    'wireConfirmAllMethod' => 'confirmAllItems',
    'emptyTitle' => 'No data yet',
    'emptyMessage' => 'Items will appear here after the manager records the inspection.',
    'embedded' => false,
])

@php
    $colorMap = [
        'indigo' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-600', 'btn' => 'text-blue-600 bg-blue-50 hover:bg-blue-100', 'confirmAll' => 'bg-indigo-600 hover:bg-indigo-700'],
        'orange' => ['bg' => 'bg-orange-50', 'text' => 'text-orange-600', 'btn' => 'text-orange-600 bg-orange-50 hover:bg-orange-100', 'confirmAll' => 'bg-orange-600 hover:bg-orange-700'],
    ];
    $colors = $colorMap[$accentColor] ?? $colorMap['indigo'];
@endphp

@if(!$embedded)<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">@endif
    <div class="{{ $embedded ? 'pb-4' : 'px-6 py-5 border-b border-gray-100' }} flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl {{ $colors['bg'] }} flex items-center justify-center">
                <svg class="w-5 h-5 {{ $colors['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-900">{{ $title }}</h3>
                <p class="text-xs text-gray-500">{{ $subtitle }}</p>
            </div>
        </div>
        @if($allConfirmed)
            <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold uppercase tracking-wide">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-2"></span>
                Confirmed
            </span>
        @elseif(count($items) > 0)
            <span class="inline-flex items-center px-3 py-1 rounded-full bg-amber-50 text-amber-700 text-xs font-bold uppercase tracking-wide">
                {{ collect($items)->where('tenant_confirmed', true)->count() }}/{{ count($items) }}
            </span>
        @endif
    </div>

    <div class="{{ $embedded ? '' : 'p-6' }}">
        @if(count($items) > 0)
            <div class="space-y-2 mb-4">
                @foreach($items as $index => $item)
                    <div class="flex items-center justify-between p-3 rounded-xl border {{ $item['tenant_confirmed'] ? 'border-emerald-200 bg-emerald-50/30' : 'border-gray-200' }}">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <div class="w-7 h-7 rounded-lg {{ $item['tenant_confirmed'] ? 'bg-emerald-100' : 'bg-gray-100' }} flex items-center justify-center flex-shrink-0">
                                @if($item['tenant_confirmed'])
                                    <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                @else
                                    <span class="text-xs font-bold text-gray-400">{{ $index + 1 }}</span>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-800 truncate">{{ $item['item_name'] }}</p>
                                <p class="text-[10px] text-gray-500">Qty: {{ $item['quantity'] ?? '—' }} &bull; {{ $item['condition'] ?? '—' }}</p>
                            </div>
                        </div>
                        @if(!$item['tenant_confirmed'])
                            <button
                                wire:click="{{ $wireConfirmMethod }}({{ $index }})"
                                class="ml-3 px-3 py-1.5 text-[10px] font-bold {{ $colors['btn'] }} rounded-lg transition-colors flex-shrink-0"
                            >
                                Confirm
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>

            @if(!$allConfirmed)
                <button
                    wire:click="{{ $wireConfirmAllMethod }}"
                    class="w-full py-2.5 px-4 {{ $colors['confirmAll'] }} text-white font-bold rounded-xl text-xs transition-colors"
                >
                    Confirm All {{ $title }}
                </button>
            @else
                <div class="text-center py-2 px-4 bg-emerald-50 rounded-xl border border-emerald-200">
                    <p class="text-xs font-bold text-emerald-700">All items confirmed</p>
                </div>
            @endif
        @else
            <div class="text-center py-8">
                <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                </div>
                <p class="text-sm font-medium text-gray-600">{{ $emptyTitle }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $emptyMessage }}</p>
            </div>
        @endif
    </div>
@if(!$embedded)</div>@endif
