@props([
    'title' => 'Items',
    'subtitle' => 'Confirm the items',
    'items' => [],
    'allConfirmed' => false,
    'accentColor' => 'indigo', // 'indigo' or 'orange'
    'wireConfirmMethod' => 'confirmItemReceived',
    'wireConfirmAllMethod' => 'confirmAllItems',
    'wireDisputeMethod' => '',
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
                    <div
                        x-data="{ showDisputeForm: false, disputeRemarks: '' }"
                        class="rounded-xl border {{ $item['tenant_confirmed'] ? 'border-emerald-200 bg-emerald-50/30' : (($item['dispute_status'] ?? 'none') === 'disputed' ? 'border-amber-300 bg-amber-50/30' : (($item['dispute_status'] ?? 'none') === 'resolved' ? 'border-blue-200 bg-blue-50/30' : 'border-gray-200')) }}"
                    >
                        <div class="flex items-center justify-between p-3">
                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                <div class="w-7 h-7 rounded-lg {{ $item['tenant_confirmed'] ? 'bg-emerald-100' : (($item['dispute_status'] ?? 'none') === 'disputed' ? 'bg-amber-100' : 'bg-gray-100') }} flex items-center justify-center flex-shrink-0">
                                    @if($item['tenant_confirmed'])
                                        <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    @elseif(($item['dispute_status'] ?? 'none') === 'disputed')
                                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                                    @else
                                        <span class="text-xs font-bold text-gray-400">{{ $index + 1 }}</span>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-800 truncate">{{ $item['item_name'] }}</p>
                                    <p class="text-[11px] text-gray-500">Qty: {{ $item['quantity'] ?? '—' }} &bull; {{ $item['condition'] ?? '—' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0 ml-3">
                                @if(($item['dispute_status'] ?? 'none') === 'disputed')
                                    <span class="px-2.5 py-1 text-[11px] font-bold text-amber-700 bg-amber-100 rounded-lg">Disputed</span>
                                @elseif(($item['dispute_status'] ?? 'none') === 'resolved')
                                    <span class="px-2.5 py-1 text-[11px] font-bold text-blue-700 bg-blue-100 rounded-lg">Resolved</span>
                                @else
                                    @if(!$item['tenant_confirmed'])
                                        <button
                                            wire:click="{{ $wireConfirmMethod }}({{ $index }})"
                                            class="px-3 py-1.5 text-[11px] font-bold {{ $colors['btn'] }} rounded-lg transition-colors"
                                        >
                                            Confirm
                                        </button>
                                    @endif
                                    @if($wireDisputeMethod)
                                        <button
                                            @click="showDisputeForm = !showDisputeForm"
                                            class="px-3 py-1.5 text-[11px] font-bold text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors"
                                        >
                                            Dispute
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </div>

                        {{-- Dispute form (slides open) --}}
                        @if($wireDisputeMethod && ($item['dispute_status'] ?? 'none') === 'none')
                            <div x-show="showDisputeForm" x-collapse x-cloak class="px-3 pb-3">
                                <div class="bg-red-50/50 border border-red-200 rounded-lg p-3">
                                    <label class="text-[11px] font-bold text-red-700 block mb-1.5">Why are you disputing this item?</label>
                                    <textarea
                                        x-model="disputeRemarks"
                                        rows="2"
                                        class="w-full text-xs border border-red-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-300 focus:border-red-300 resize-none"
                                        placeholder="Describe the issue (e.g., item was not received, wrong condition recorded...)"
                                    ></textarea>
                                    <div class="flex justify-end gap-2 mt-2">
                                        <button
                                            @click="showDisputeForm = false; disputeRemarks = ''"
                                            class="px-3 py-1.5 text-[11px] font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                                        >
                                            Cancel
                                        </button>
                                        <button
                                            @click="if(disputeRemarks.trim()) { $wire.{{ $wireDisputeMethod }}({{ $item['id'] ?? 0 }}, disputeRemarks); showDisputeForm = false; disputeRemarks = ''; }"
                                            class="px-3 py-1.5 text-[11px] font-bold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors"
                                        >
                                            Submit Dispute
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Resolution remarks (shown when resolved) --}}
                        @if(($item['dispute_status'] ?? 'none') === 'resolved' && !empty($item['resolution_remarks']))
                            <div class="px-3 pb-3">
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                    <p class="text-[11px] font-bold text-blue-700 mb-1">Manager's Resolution:</p>
                                    <p class="text-xs text-blue-800">{{ $item['resolution_remarks'] }}</p>
                                </div>
                            </div>
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
