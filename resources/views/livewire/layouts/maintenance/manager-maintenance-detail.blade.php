<div class="h-full bg-white rounded-3xl shadow-sm border border-gray-100 flex flex-col overflow-hidden" x-data="{ lightbox: false, lightboxSrc: '', confirmDelete: null }">

    @if($ticket)
        @php
            $urgencyMap = [
                'Level 1' => ['dot' => 'bg-red-400',    'label' => 'Critical'],
                'Level 2' => ['dot' => 'bg-orange-400', 'label' => 'High'],
                'Level 3' => ['dot' => 'bg-yellow-400', 'label' => 'Medium'],
                'Level 4' => ['dot' => 'bg-green-400',  'label' => 'Low'],
            ];
            $uc = $urgencyMap[$ticket->urgency] ?? ['dot' => 'bg-gray-400', 'label' => $ticket->urgency ?? '—'];

            $statusStyles = match($ticket->status) {
                'Completed', 'Resolved'  => 'bg-green-100 text-green-700',
                'Pending'                => 'bg-orange-100 text-orange-700',
                'In Progress', 'Ongoing' => 'bg-yellow-100 text-yellow-800',
                default                  => 'bg-gray-100 text-gray-700'
            };

            // Fetch tenant + unit + building info via lease
            $leaseInfo = \Illuminate\Support\Facades\DB::table('leases')
                ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
                ->join('units', 'beds.unit_id', '=', 'units.unit_id')
                ->join('properties', 'units.property_id', '=', 'properties.property_id')
                ->join('users', 'leases.tenant_id', '=', 'users.user_id')
                ->where('leases.lease_id', $ticket->lease_id)
                ->select(
                    'units.unit_number',
                    'properties.building_name',
                    'properties.address',
                    'users.first_name',
                    'users.last_name'
                )
                ->first();

            $unitDisplay     = $leaseInfo ? 'Unit ' . $leaseInfo->unit_number : 'Unit —';
            $buildingDisplay = $leaseInfo ? $leaseInfo->building_name : '—';
            $addressDisplay  = $leaseInfo->address ?? '—';
            $tenantName      = $leaseInfo
                ? $leaseInfo->first_name . ' ' . $leaseInfo->last_name
                : ($ticket->logged_by ?? '—');


            // Parse image_path — supports JSON array (new) and plain string (old)
            $imagePaths = [];
            if (!empty($ticket->image_path)) {
                $decoded = json_decode($ticket->image_path, true);
                $imagePaths = is_array($decoded) ? $decoded : [$ticket->image_path];
            }

            $canMarkOngoing   = $ticket->status === 'Pending';
            $canMarkCompleted = in_array($ticket->status, ['Pending', 'Ongoing']);
        @endphp

        {{-- ── BLUE HEADER — only tenant name, unit, building, date ── --}}
        <div class="flex-shrink-0 bg-[#2B66F5] text-white px-6 py-5">
            <p class="text-xs text-blue-200 font-medium mb-0.5">{{ $tenantName }}</p>
            <h2 class="text-3xl font-bold leading-tight">{{ $unitDisplay }}</h2>
            <p class="text-sm text-blue-100 mt-0.5">{{ $buildingDisplay }}</p>

            <p class="text-xs text-blue-200 mt-3 pt-3 border-t border-blue-400/30">
                Submitted on
                <span class="font-semibold text-white">
                    {{ \Carbon\Carbon::parse($ticket->created_at)->format('F d, Y \a\t h:i A') }}
                </span>
            </p>
        </div>

        {{-- Success Toast --}}
        @if($successMessage)
            <div class="mx-5 mt-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm font-medium flex items-center gap-2 flex-shrink-0"
                 x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                 x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ $successMessage }}
            </div>
        @endif

        {{-- ── SCROLLABLE BODY ── --}}
        <div class="flex-1 overflow-y-auto" style="scrollbar-width: thin; scrollbar-color: #e2e8f0 transparent;">
            <div class="p-6 space-y-7">

                {{-- Issue Details grid — Ticket, Priority, Category, Status --}}
                <div>
                    <h3 class="text-sm font-bold text-[#070642] mb-3 flex items-center gap-2">
                        <span class="w-1 h-4 bg-[#2B66F5] rounded-full"></span>
                        Issue Details
                    </h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-[#F4F7FF] p-4 rounded-xl border border-blue-50">
                            <p class="text-gray-400 text-[10px] uppercase font-bold tracking-wide mb-1">Ticket Number</p>
                            <p class="text-[#070642] font-semibold font-mono text-sm">{{ $ticketIdDisplay }}</p>
                        </div>
                        <div class="bg-[#F4F7FF] p-4 rounded-xl border border-blue-50">
                            <p class="text-gray-400 text-[10px] uppercase font-bold tracking-wide mb-1">Priority Level</p>
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full {{ $uc['dot'] }}"></span>
                                <span class="text-[#070642] font-semibold text-sm">{{ $ticket->urgency }}</span>
                                <span class="text-xs text-gray-500">({{ $uc['label'] }})</span>
                            </div>
                        </div>
                        <div class="bg-[#F4F7FF] p-4 rounded-xl border border-blue-50">
                            <p class="text-gray-400 text-[10px] uppercase font-bold tracking-wide mb-1">Category</p>
                            <p class="text-[#070642] font-semibold text-sm">{{ $ticket->category ?? 'General Maintenance' }}</p>
                        </div>
                        <div class="bg-[#F4F7FF] p-4 rounded-xl border border-blue-50">
                            <p class="text-gray-400 text-[10px] uppercase font-bold tracking-wide mb-1">Status</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $statusStyles }}">
                                {{ $ticket->status }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Description --}}
                <div>
                    <h3 class="text-sm font-bold text-[#070642] mb-3 flex items-center gap-2">
                        <span class="w-1 h-4 bg-[#2B66F5] rounded-full"></span>
                        Description
                    </h3>
                    <div class="bg-gray-50 border border-gray-100 p-4 rounded-xl text-gray-700 leading-relaxed text-sm">
                        {{ $ticket->problem }}
                    </div>
                </div>

                {{-- Photos — lightbox + multi-image + placeholder if none --}}
                <div>
                    <h3 class="text-sm font-bold text-[#070642] mb-3 flex items-center gap-2">
                        <span class="w-1 h-4 bg-[#2B66F5] rounded-full"></span>
                        Photos
                        @if(!empty($imagePaths))
                            <span class="text-[10px] text-gray-400 font-normal ml-1">(click to enlarge)</span>
                        @endif
                    </h3>

                    {{-- Lightbox overlay --}}
                    <div
                        x-show="lightbox"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        @click="lightbox = false"
                        @keydown.escape.window="lightbox = false"
                        class="fixed inset-0 z-[9999] bg-black/85 flex items-center justify-center p-4 cursor-zoom-out"
                        style="display:none;"
                    >
                        <img :src="lightboxSrc" class="max-w-full max-h-full object-contain rounded-xl shadow-2xl" @click.stop>
                        <button @click="lightbox = false" class="absolute top-5 right-5 w-10 h-10 bg-white/20 hover:bg-white/30 text-white rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    @if(!empty($imagePaths))
                        <div class="grid {{ count($imagePaths) === 1 ? 'grid-cols-1' : 'grid-cols-2' }} gap-3">
                            @foreach($imagePaths as $imgPath)
                                <div
                                    class="rounded-xl overflow-hidden border border-gray-100 shadow-sm cursor-zoom-in group relative"
                                    @click="lightbox = true; lightboxSrc = '{{ asset('storage/' . $imgPath) }}'"
                                >
                                    <img
                                        src="{{ asset('storage/' . $imgPath) }}"
                                        alt="Maintenance issue photo"
                                        class="w-full {{ count($imagePaths) === 1 ? 'max-h-72' : 'h-36' }} object-cover group-hover:scale-105 transition-transform duration-300"
                                    >
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors flex items-center justify-center">
                                        <div class="opacity-0 group-hover:opacity-100 transition-opacity bg-white/80 rounded-full p-1.5">
                                            <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 flex flex-col items-center justify-center py-10 text-gray-400">
                            <div class="bg-white p-3 rounded-full shadow-sm mb-3">
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-gray-400">No photo attached</p>
                            <p class="text-xs text-gray-300 mt-0.5">Tenant did not upload an image</p>
                        </div>
                    @endif
                </div>

                {{-- ══════════════════════════════════════════════════════════ --}}
                {{-- ── MAINTENANCE COST TRACKER ── --}}
                {{-- ══════════════════════════════════════════════════════════ --}}
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-[#070642] flex items-center gap-2">
                            <span class="w-1 h-4 rounded-full" style="background-color: #10b981;"></span>
                            Maintenance Costs
                        </h3>

                        {{-- Add Cost Button --}}
                        @if(in_array($ticket->status, ['Ongoing', 'Completed', 'Resolved']))
                            <button
                                wire:click="toggleCostForm"
                                class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold transition-all duration-200"
                                style="{{ $showCostForm
                                    ? 'background:#f3f4f6; color:#4b5563;'
                                    : 'background:#ecfdf5; color:#047857; border:1px solid #a7f3d0;' }}"
                            >
                                @if($showCostForm)
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    Cancel
                                @else
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Add Cost
                                @endif
                            </button>
                        @endif
                    </div>

                    {{-- Cost Summary Cards --}}
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        {{-- This Request Total --}}
                        <div class="relative overflow-hidden rounded-2xl p-4 text-white shadow-sm" style="background: linear-gradient(135deg, #10b981, #059669);">
                            <div class="absolute top-0 right-0 w-16 h-16 rounded-full" style="background: rgba(255,255,255,0.1); transform: translate(16px, -16px);"></div>
                            <div class="absolute bottom-0 left-0 w-10 h-10 rounded-full" style="background: rgba(255,255,255,0.05); transform: translate(-12px, 12px);"></div>
                            <p class="text-[10px] uppercase font-bold tracking-wider mb-1" style="color: #d1fae5;">This Request</p>
                            <p class="text-2xl font-extrabold tracking-tight">
                                @php
                                    $formatted = number_format($requestTotal, 2);
                                    $parts = explode('.', $formatted);
                                @endphp
                                <span class="text-base font-bold mr-0.5" style="color: #a7f3d0;">PHP</span>{{ $parts[0] }}<span class="text-base" style="color: #a7f3d0;">.{{ $parts[1] }}</span>
                            </p>
                            <p class="text-[10px] mt-1.5" style="color: #a7f3d0;">
                                {{ count($costItems) }} {{ count($costItems) === 1 ? 'item' : 'items' }} logged
                            </p>
                        </div>

                        {{-- Unit Total --}}
                        <div class="relative overflow-hidden rounded-2xl p-4 text-white shadow-sm" style="background: linear-gradient(135deg, #2B66F5, #1a4fd4);">
                            <div class="absolute top-0 right-0 w-16 h-16 rounded-full" style="background: rgba(255,255,255,0.1); transform: translate(16px, -16px);"></div>
                            <div class="absolute bottom-0 left-0 w-10 h-10 rounded-full" style="background: rgba(255,255,255,0.05); transform: translate(-12px, 12px);"></div>
                            <p class="text-[10px] uppercase font-bold tracking-wider mb-1" style="color: #bfdbfe;">{{ $unitDisplay }} Total</p>
                            <p class="text-2xl font-extrabold tracking-tight">
                                @php
                                    $uFormatted = number_format($unitTotal, 2);
                                    $uParts = explode('.', $uFormatted);
                                @endphp
                                <span class="text-base font-bold mr-0.5" style="color: #bfdbfe;">PHP</span>{{ $uParts[0] }}<span class="text-base" style="color: #bfdbfe;">.{{ $uParts[1] }}</span>
                            </p>
                            <p class="text-[10px] mt-1.5" style="color: #bfdbfe;">
                                All-time maintenance
                            </p>
                        </div>
                    </div>

                    {{-- Add Cost Form (slide-down) --}}
                    @if($showCostForm)
                        <div class="rounded-2xl p-5 mb-4 space-y-4" style="background: linear-gradient(to bottom, rgba(236,253,245,0.8), white); border: 1px solid #d1fae5;"
                             x-data x-init="$nextTick(() => $refs.costInput?.focus())">

                            <div class="flex items-center gap-2 mb-1">
                                <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background-color: #d1fae5;">
                                    <svg class="w-4 h-4" style="color: #059669;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <h4 class="text-sm font-bold text-[#070642]">New Cost Entry</h4>
                            </div>

                            {{-- Description --}}
                            <div>
                                <label class="text-[10px] uppercase font-bold tracking-wide text-gray-400 mb-1.5 block">Description</label>
                                <input
                                    type="text"
                                    wire:model="costDescription"
                                    placeholder="e.g. Labor - Plumber (2 hrs), Pipe fittings..."
                                    maxlength="255"
                                    class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm text-gray-700 placeholder-gray-300 transition-all outline-none"
                                >
                                @error('costDescription')
                                    <p class="text-xs mt-1" style="color: #ef4444;">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Amount --}}
                            <div>
                                <label class="text-[10px] uppercase font-bold tracking-wide text-gray-400 mb-1.5 block">Amount (PHP)</label>
                                <style>
                                    input.hide-spinner::-webkit-outer-spin-button,
                                    input.hide-spinner::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
                                    input.hide-spinner { -moz-appearance: textfield; }
                                </style>
                                <input
                                    type="text"
                                    inputmode="decimal"
                                    wire:model="costAmount"
                                    x-ref="costInput"
                                    placeholder="Enter amount (e.g. 2500.00)"
                                    maxlength="12"
                                    x-on:input="$el.value = $el.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1')"
                                    class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm text-gray-700 placeholder-gray-300 transition-all outline-none font-mono hide-spinner"
                                >
                                @error('costAmount')
                                    <p class="text-xs mt-1" style="color: #ef4444;">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Quick Amount Presets --}}
                            <div>
                                <label class="text-[10px] uppercase font-bold tracking-wide text-gray-400 mb-1.5 block">Quick Select</label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach([500, 1000, 2500, 5000, 10000] as $preset)
                                        <button
                                            type="button"
                                            wire:click="$set('costAmount', {{ $preset }})"
                                            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all"
                                            style="{{ $costAmount == $preset
                                                ? 'background-color:#10b981; color:white; box-shadow:0 1px 2px rgba(0,0,0,0.1);'
                                                : 'background:white; border:1px solid #e5e7eb; color:#4b5563;' }}"
                                        >
                                            {{ number_format($preset) }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Save Button --}}
                            <button
                                wire:click="saveCost"
                                wire:loading.attr="disabled"
                                wire:target="saveCost"
                                class="w-full py-2.5 text-white font-bold text-sm rounded-xl transition-all duration-200 flex items-center justify-center gap-2 disabled:opacity-50"
                                style="background-color: #10b981; box-shadow: 0 1px 3px rgba(16,185,129,0.3);"
                                onmouseover="this.style.backgroundColor='#059669'" onmouseout="this.style.backgroundColor='#10b981'"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span wire:loading.remove wire:target="saveCost">Save Cost Entry</span>
                                <span wire:loading wire:target="saveCost">Saving...</span>
                            </button>
                        </div>
                    @endif

                    {{-- Cost Items List --}}
                    @if(!empty($costItems))
                        <div class="space-y-2">
                            @foreach($costItems as $index => $item)
                                <div class="group bg-white border border-gray-100 rounded-xl p-3.5 flex items-center gap-3 hover:border-gray-200 hover:shadow-sm transition-all duration-200">
                                    {{-- Icon --}}
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                                         style="background-color: {{ $index === 0 ? '#ecfdf5' : '#f9fafb' }};">
                                        <svg class="w-4 h-4" style="color: {{ $index === 0 ? '#10b981' : '#9ca3af' }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                    </div>

                                    {{-- Info --}}
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-[#070642] truncate">
                                            {{ $item['description'] ?? 'Maintenance Cost' }}
                                        </p>
                                        <p class="text-[10px] text-gray-400 mt-0.5">
                                            {{ \Carbon\Carbon::parse($item['completion_date'])->format('M d, Y') }}
                                        </p>
                                    </div>

                                    {{-- Amount --}}
                                    <div class="text-right flex-shrink-0">
                                        <p class="text-sm font-bold font-mono" style="color: #059669;">
                                            PHP {{ number_format($item['cost'], 2) }}
                                        </p>
                                    </div>

                                    {{-- Delete Button --}}
                                    <div class="flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button
                                            @click="confirmDelete = {{ $item['log_id'] }}"
                                            class="w-7 h-7 rounded-lg flex items-center justify-center transition-colors"
                                            style="background-color: #fef2f2;"
                                            onmouseover="this.style.backgroundColor='#fee2e2'" onmouseout="this.style.backgroundColor='#fef2f2'"
                                            title="Remove cost"
                                        >
                                            <svg class="w-3.5 h-3.5" style="color: #ef4444;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @endforeach

                            {{-- Total Bar --}}
                            @if(count($costItems) > 1)
                                <div class="rounded-xl p-3.5 flex items-center justify-between mt-1" style="background-color: #ecfdf5; border: 1px solid #d1fae5;">
                                    <span class="text-xs font-bold uppercase tracking-wide" style="color: #047857;">Request Total</span>
                                    <span class="text-base font-extrabold font-mono" style="color: #047857;">PHP {{ number_format($requestTotal, 2) }}</span>
                                </div>
                            @endif
                        </div>
                    @elseif($ticket->status === 'Pending')
                        {{-- Pending - can't add costs yet --}}
                        <div class="rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 py-6 flex flex-col items-center text-gray-400">
                            <div class="bg-white p-2.5 rounded-full shadow-sm mb-2">
                                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <p class="text-xs font-medium text-gray-400">Costs can be added once work begins</p>
                            <p class="text-[10px] text-gray-300 mt-0.5">Mark as Ongoing to start tracking costs</p>
                        </div>
                    @else
                        {{-- No costs yet --}}
                        <div class="rounded-xl border-2 border-dashed py-6 flex flex-col items-center text-gray-400" style="border-color: #a7f3d0; background: rgba(236,253,245,0.5);">
                            <div class="bg-white p-2.5 rounded-full shadow-sm mb-2">
                                <svg class="w-6 h-6" style="color: #6ee7b7;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <p class="text-xs font-medium text-gray-500">No costs recorded yet</p>
                            <p class="text-[10px] text-gray-400 mt-0.5">Click "Add Cost" above to log expenses</p>
                        </div>
                    @endif
                </div>

                {{-- Delete Confirmation Modal --}}
                <template x-if="confirmDelete">
                    <div class="fixed inset-0 z-[9998] flex items-center justify-center bg-black/40" @click.self="confirmDelete = null">
                        <div class="bg-white rounded-2xl shadow-2xl p-6 w-80 mx-4" @click.stop>
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center">
                                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-[#070642]">Remove Cost Entry?</h4>
                                    <p class="text-xs text-gray-400">This action cannot be undone.</p>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button @click="confirmDelete = null" class="flex-1 px-4 py-2 rounded-xl text-sm font-semibold bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
                                    Cancel
                                </button>
                                <button
                                    @click="$wire.removeCostItem(confirmDelete); confirmDelete = null"
                                    class="flex-1 px-4 py-2 rounded-xl text-sm font-bold bg-red-500 text-white hover:bg-red-600 transition-colors"
                                >
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Updates + Manager Action Buttons --}}
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-[#070642] flex items-center gap-2">
                            <span class="w-1 h-4 bg-[#2B66F5] rounded-full"></span>
                            Updates
                        </h3>

                        {{-- Action Buttons --}}
                        <div class="flex items-center gap-2">
                            @if($canMarkOngoing)
                                <button
                                    wire:click="markAsOngoing"
                                    wire:loading.attr="disabled"
                                    wire:target="markAsOngoing"
                                    class="flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-bold bg-yellow-50 text-yellow-700 border border-yellow-200 hover:bg-yellow-100 transition-colors disabled:opacity-50"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    <span wire:loading.remove wire:target="markAsOngoing">Mark as Ongoing</span>
                                    <span wire:loading wire:target="markAsOngoing">Updating...</span>
                                </button>
                            @endif

                            @if($canMarkCompleted)
                                <button
                                    wire:click="markAsCompleted"
                                    wire:loading.attr="disabled"
                                    wire:target="markAsCompleted"
                                    class="flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-bold bg-green-50 text-green-700 border border-green-200 hover:bg-green-100 transition-colors disabled:opacity-50"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span wire:loading.remove wire:target="markAsCompleted">Mark as Completed</span>
                                    <span wire:loading wire:target="markAsCompleted">Updating...</span>
                                </button>
                            @endif

                            @if($ticket->status === 'Completed')
                                <span class="flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Resolved
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Timeline --}}
                    <div class="pl-2">

                        @if(in_array($ticket->status, ['Completed', 'Resolved']))
                            <div class="flex gap-4 relative pb-6">
                                <div class="absolute left-[9px] top-6 bottom-0 w-[2px] bg-gray-200"></div>
                                <div class="flex-shrink-0 w-5 h-5 rounded-full bg-green-500 border-2 border-white shadow z-10 mt-0.5"></div>
                                <div class="flex-1">
                                    <p class="font-semibold text-[#070642] text-sm">Request completed</p>
                                    <p class="text-xs text-gray-400 mb-2">
                                        {{ \Carbon\Carbon::parse($ticket->updated_at)->format('M d, h:i A') }}
                                    </p>
                                    <div class="bg-white border border-green-100 shadow-sm p-3 rounded-xl text-sm text-gray-600">
                                        The maintenance issue has been resolved and marked as completed.
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if(in_array($ticket->status, ['Ongoing', 'In Progress', 'Completed', 'Resolved']))
                            <div class="flex gap-4 relative pb-6">
                                <div class="absolute left-[9px] top-6 bottom-0 w-[2px] bg-gray-200"></div>
                                <div class="flex-shrink-0 w-5 h-5 rounded-full bg-[#2B66F5] border-2 border-white shadow z-10 mt-0.5"></div>
                                <div class="flex-1">
                                    <p class="font-semibold text-[#070642] text-sm">Technician assigned</p>
                                    <p class="text-xs text-gray-400 mb-2">
                                        {{ \Carbon\Carbon::parse($ticket->updated_at)->format('M d, h:i A') }}
                                    </p>
                                    <div class="bg-white border border-gray-100 shadow-sm p-3 rounded-xl text-sm text-gray-600">
                                        A technician has been dispatched to check the issue.
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="flex gap-4 relative">
                            <div class="flex-shrink-0 w-5 h-5 rounded-full bg-gray-300 border-2 border-white shadow z-10 mt-0.5"></div>
                            <div class="flex-1">
                                <p class="font-semibold text-[#070642] text-sm">Request received</p>
                                <p class="text-xs text-gray-400 mb-2">
                                    {{ \Carbon\Carbon::parse($ticket->created_at)->format('M d, h:i A') }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    Maintenance request submitted successfully.
                                </p>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ── TENANT FEEDBACK ── --}}
                @if($feedback)
                    @php
                        $tagList = array_filter(array_map('trim', explode(',', $feedback->experience_tag ?? '')));
                        $ratingLabels = [1 => 'Poor', 2 => 'Fair', 3 => 'Good', 4 => 'Great', 5 => 'Excellent'];
                        $ratingLabel  = $ratingLabels[$feedback->rating] ?? '';
                    @endphp
                    <div>
                        <h3 class="text-sm font-bold text-[#070642] mb-4 flex items-center gap-2">
                            <span class="w-1 h-4 bg-[#2B66F5] rounded-full"></span>
                            Tenant Feedback
                        </h3>

                        <div class="bg-[#F4F7FF] border border-blue-50 rounded-2xl p-5 space-y-5">

                            {{-- Star Rating --}}
                            <div>
                                <p class="text-[10px] uppercase font-bold tracking-wide text-gray-400 mb-2">Overall Service Rating</p>
                                <div class="flex items-center gap-1.5">
                                    @for($s = 1; $s <= 5; $s++)
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            style="width:22px; height:22px;"
                                            fill="{{ $s <= $feedback->rating ? '#FBBF24' : '#E5E7EB' }}"
                                        >
                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                        </svg>
                                    @endfor
                                    <span class="ml-2 text-sm font-bold text-[#070642]">{{ $ratingLabel }}</span>
                                    <span class="text-xs text-gray-400">({{ $feedback->rating }}/5)</span>
                                </div>
                            </div>

                            {{-- Experience Tags --}}
                            @if(!empty($tagList))
                                <div>
                                    <p class="text-[10px] uppercase font-bold tracking-wide text-gray-400 mb-2">Experience</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($tagList as $tag)
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-[#2B66F5] text-white">
                                                {{ $tag }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Comment --}}
                            @if(!empty($feedback->comment))
                                <div>
                                    <p class="text-[10px] uppercase font-bold tracking-wide text-gray-400 mb-2">Comment</p>
                                    <div class="bg-white border border-blue-100 rounded-xl p-3 text-sm text-gray-700 leading-relaxed">
                                        {{ $feedback->comment }}
                                    </div>
                                </div>
                            @endif

                            {{-- Submitted at --}}
                            <p class="text-[10px] text-gray-400 pt-1 border-t border-blue-100">
                                Submitted {{ \Carbon\Carbon::parse($feedback->created_at)->format('M d, Y \a\t h:i A') }}
                            </p>

                        </div>
                    </div>
                @elseif(in_array($ticket->status, ['Completed', 'Resolved']))
                    {{-- Completed but no feedback yet --}}
                    <div>
                        <h3 class="text-sm font-bold text-[#070642] mb-4 flex items-center gap-2">
                            <span class="w-1 h-4 bg-[#2B66F5] rounded-full"></span>
                            Tenant Feedback
                        </h3>
                        <div class="rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50 py-8 flex flex-col items-center text-gray-400">
                            <svg class="w-8 h-8 mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                            <p class="text-sm font-medium text-gray-400">No feedback yet</p>
                            <p class="text-xs text-gray-300 mt-0.5">The tenant hasn't submitted a review.</p>
                        </div>
                    </div>
                @endif

            </div>
        </div>

    @else
        {{-- Empty State --}}
        <div class="flex-1 flex flex-col items-center justify-center text-gray-400 p-6">
            <div class="bg-[#F4F7FF] p-8 rounded-full mb-5">
                <svg class="h-16 w-16 text-[#2B66F5] opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-500 mb-1">No Ticket Selected</h3>
            <p class="text-sm text-center">Select a maintenance request from the list to view details.</p>
        </div>
    @endif

</div>
