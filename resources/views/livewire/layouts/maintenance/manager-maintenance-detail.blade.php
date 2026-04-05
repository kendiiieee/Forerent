<div class="bg-white rounded-3xl shadow-sm border border-gray-100 flex flex-col h-full overflow-hidden" style="font-family: 'Open Sans', sans-serif;" x-data="{ lightbox: false, lightboxSrc: '', confirmDelete: null }">

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

            $imagePaths = [];
            if (!empty($ticket->image_path)) {
                $decoded = json_decode($ticket->image_path, true);
                $imagePaths = is_array($decoded) ? $decoded : [$ticket->image_path];
            }

            $canMarkOngoing   = $ticket->status === 'Pending';
            $canMarkCompleted = in_array($ticket->status, ['Pending', 'Ongoing']);
        @endphp

        <div class="flex flex-col h-full">

            {{-- 1. Fixed Header --}}
            <div class="flex-shrink-0 rounded-t-3xl z-10 overflow-hidden" style="background: linear-gradient(135deg, #070589 0%, #0a1ea8 40%, #2360E8 100%);">
                <div class="relative p-6">
                    {{-- Top row: label --}}
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: rgba(255,255,255,0.12);">
                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17l-5.78 3.04 1.1-6.44-4.68-4.56 6.47-.94L11.42 0l2.89 5.87 6.47.94-4.68 4.56 1.1 6.44z"/>
                            </svg>
                        </div>
                        <span class="text-xs font-semibold uppercase tracking-widest" style="color: rgba(191,219,254,0.9);">Maintenance Request</span>
                    </div>

                    {{-- Tenant Name + Unit --}}
                    <div class="flex items-center gap-3 mb-3">
                        <h3 class="text-white font-bold text-2xl leading-tight">{{ $tenantName }}</h3>
                        <div class="rounded-lg px-3 py-1 flex items-center gap-1.5" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.1); backdrop-filter: blur(8px);">
                            <span class="text-sm font-medium" style="color: rgba(191,219,254,0.8);">Unit</span>
                            <span class="text-white text-sm font-bold">{{ $leaseInfo->unit_number ?? '—' }}</span>
                        </div>
                    </div>

                    {{-- Info chips --}}
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="flex items-center gap-1.5 rounded-lg px-3 py-1.5" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.06);">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" style="color: #93c5fd;" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-xs truncate max-w-[220px]" style="color: rgba(255,255,255,0.85);">{{ $addressDisplay }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 rounded-lg px-3 py-1.5" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.06);">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" style="color: #93c5fd;" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                            </svg>
                            <span class="text-xs" style="color: rgba(255,255,255,0.85);">{{ $buildingDisplay }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 rounded-lg px-3 py-1.5" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.06);">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" style="color: #93c5fd;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-xs" style="color: rgba(255,255,255,0.85);">{{ \Carbon\Carbon::parse($ticket->created_at)->format('M d, Y \a\t h:i A') }}</span>
                        </div>
                    </div>
                </div>
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

            {{-- 2. Scrollable Content Area --}}
            <div class="flex-1 overflow-y-auto custom-scrollbar p-5 space-y-5" style="background: linear-gradient(180deg, #EEF2FF 0%, #F8FAFC 100%);">

                {{-- ══════════════════════════════════════════════════════════ --}}
                {{-- ── SECTION 1: ISSUE DETAILS ── --}}
                {{-- ══════════════════════════════════════════════════════════ --}}
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-8 h-8 rounded-xl bg-[#EEF2FF] flex items-center justify-center">
                            <svg class="w-4 h-4 text-[#2360E8]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <h5 class="font-bold text-sm text-[#070589] uppercase tracking-wide">Issue Details</h5>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-[#F8FAFF] rounded-xl p-3.5 border border-blue-50">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1">Ticket Number</p>
                            <p class="text-sm font-bold text-gray-800 font-mono">{{ $ticketIdDisplay }}</p>
                        </div>
                        <div class="bg-[#F8FAFF] rounded-xl p-3.5 border border-blue-50">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1">Category</p>
                            <p class="text-sm font-bold text-gray-800">{{ $ticket->category ?? 'General Maintenance' }}</p>
                        </div>
                        <div class="bg-[#F8FAFF] rounded-xl p-3.5 border border-blue-50">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1">Status</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $statusStyles }}">
                                {{ $ticket->status }}
                            </span>
                        </div>
                        <div class="bg-[#F8FAFF] rounded-xl p-3.5 border border-blue-50">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1">Priority Level</p>
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full {{ $uc['dot'] }}"></span>
                                <span class="text-sm font-bold text-gray-800">{{ $ticket->urgency }}</span>
                                <span class="text-xs text-gray-500">({{ $uc['label'] }})</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── DESCRIPTION ── --}}
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-8 h-8 rounded-xl bg-[#EEF2FF] flex items-center justify-center">
                            <svg class="w-4 h-4 text-[#2360E8]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/>
                            </svg>
                        </div>
                        <h5 class="font-bold text-sm text-[#070589] uppercase tracking-wide">Description</h5>
                    </div>
                    <div class="bg-[#F8FAFF] rounded-xl p-3.5 border border-blue-50 text-sm text-gray-700 leading-relaxed">
                        {{ $ticket->problem }}
                    </div>
                </div>

                {{-- ── PHOTOS ── --}}
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-8 h-8 rounded-xl bg-[#EEF2FF] flex items-center justify-center">
                            <svg class="w-4 h-4 text-[#2360E8]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h5 class="font-bold text-sm text-[#070589] uppercase tracking-wide">Photos</h5>
                        @if(!empty($imagePaths))
                            <span class="text-[11px] text-gray-400 font-normal ml-1">(click to enlarge)</span>
                        @endif
                    </div>

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
                        <flux:tooltip :content="'Close the image viewer'" position="bottom">
                            <button @click="lightbox = false" class="absolute top-5 right-5 w-10 h-10 bg-white/20 hover:bg-white/30 text-white rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </flux:tooltip>
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
                        <div class="rounded-xl border-2 border-dashed border-gray-200 bg-[#F8FAFF] flex flex-col items-center justify-center py-10 text-gray-400">
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
                {{-- ── SECTION 2: MANAGE REQUEST ── --}}
                {{-- ══════════════════════════════════════════════════════════ --}}
                @php
                    $allFieldsFilled = $ticket->assigned_to && $ticket->expected_completion_date && $ticket->urgency;
                    $isEditable = in_array($ticket->status, ['Pending', 'Ongoing']);
                @endphp
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:shadow-md transition-shadow" x-data="{ editing: {{ $allFieldsFilled ? 'false' : 'true' }} }">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-[#EEF2FF] flex items-center justify-center">
                                <svg class="w-4 h-4 text-[#2360E8]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <h5 class="font-bold text-sm text-[#070589] uppercase tracking-wide">Manage Request</h5>
                        </div>
                        @if($isEditable && $allFieldsFilled)
                            <flux:tooltip :content="'Modify the request description and details'" position="bottom">
                                <button @click="editing = !editing" class="flex items-center gap-1.5 bg-white text-[#2360E8] rounded-lg px-3 py-1.5 text-xs font-semibold hover:bg-[#EEF2FF] transition-colors border border-blue-100">
                                    <svg x-show="!editing" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    <span x-show="!editing">Edit</span>
                                    <svg x-show="editing" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    <span x-show="editing">Cancel</span>
                                </button>
                            </flux:tooltip>
                        @endif
                    </div>

                    {{-- Read-only summary --}}
                    <div x-show="!editing" class="grid grid-cols-3 gap-3">
                        <div class="bg-[#F8FAFF] rounded-xl p-3.5 border border-blue-50">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1">Assigned To</p>
                            <p class="text-sm font-bold text-gray-800">{{ $ticket->assigned_to ?? 'Not assigned' }}</p>
                        </div>
                        <div class="bg-[#F8FAFF] rounded-xl p-3.5 border border-blue-50">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1">Expected Completion</p>
                            <p class="text-sm font-bold text-gray-800">
                                {{ $ticket->expected_completion_date ? \Carbon\Carbon::parse($ticket->expected_completion_date)->format('M d, Y') : 'Not set' }}
                            </p>
                        </div>
                        <div class="bg-[#F8FAFF] rounded-xl p-3.5 border border-blue-50">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1">Current Priority</p>
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full {{ $uc['dot'] }}"></span>
                                <span class="text-sm font-bold text-gray-800">{{ $uc['label'] }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Editable form --}}
                    @if($isEditable)
                        <div x-show="editing" x-cloak class="space-y-3">
                            <div>
                                <label class="text-[11px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1.5 block">Change Priority</label>
                                <select wire:model="newUrgency" class="w-full px-3 py-2 bg-[#F8FAFF] border border-blue-50 rounded-xl text-sm text-gray-700 outline-none focus:ring-2 focus:ring-blue-200">
                                    <option value="">Select priority...</option>
                                    <option value="Level 1">Level 1 — Critical</option>
                                    <option value="Level 2">Level 2 — High</option>
                                    <option value="Level 3">Level 3 — Medium</option>
                                    <option value="Level 4">Level 4 — Low</option>
                                </select>
                                @error('newUrgency') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-[11px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1.5 block">Assigned Worker / Vendor</label>
                                <input type="text" wire:model="assignedTo" placeholder="e.g. Juan (Plumber), ABC Services" maxlength="255"
                                    class="w-full px-3 py-2 bg-[#F8FAFF] border border-blue-50 rounded-xl text-sm text-gray-700 placeholder-gray-300 outline-none focus:ring-2 focus:ring-blue-200">
                                @error('assignedTo') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-[11px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1.5 block">Expected Completion Date</label>
                                <input type="date" wire:model="expectedCompletionDate"
                                    class="w-full px-3 py-2 bg-[#F8FAFF] border border-blue-50 rounded-xl text-sm text-gray-700 outline-none focus:ring-2 focus:ring-blue-200">
                                @error('expectedCompletionDate') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="flex justify-end">
                                <button wire:click="saveManageRequest" class="px-5 py-2 text-xs font-bold text-white bg-[#070589] hover:bg-[#000060] rounded-xl transition-colors">
                                    Save Changes
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- ══════════════════════════════════════════════════════════ --}}
                {{-- ── MAINTENANCE COSTS ── --}}
                {{-- ══════════════════════════════════════════════════════════ --}}
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-[#EEF2FF] flex items-center justify-center">
                                <svg class="w-4 h-4 text-[#2360E8]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <h5 class="font-bold text-sm text-[#070589] uppercase tracking-wide">Maintenance Costs</h5>
                        </div>

                        @if(in_array($ticket->status, ['Ongoing', 'Completed', 'Resolved']))
                            <div class="flex items-center gap-2">
                                @if(empty($costItems))
                                    <span class="text-[11px] text-amber-500 font-medium">No costs yet — add one</span>
                                @endif
                                <flux:tooltip :content="'Log a new maintenance expense'" position="bottom">
                                    <button
                                        wire:click="toggleCostForm"
                                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold transition-all duration-200"
                                        style="background:#EEF2FF; color:#070589; border:1px solid #c7d2fe;"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        Add Cost
                                    </button>
                                </flux:tooltip>
                            </div>
                        @elseif($ticket->status === 'Pending')
                            <span class="text-[11px] text-amber-500 font-medium">Mark as Ongoing to start tracking costs</span>
                        @endif
                    </div>

                    {{-- Cost Summary Card --}}
                    <div class="mb-4">
                        <div class="relative overflow-hidden rounded-2xl p-4 text-white shadow-sm" style="background: linear-gradient(135deg, #070589, #0a1ea8);">
                            <div class="absolute top-0 right-0 w-16 h-16 rounded-full" style="background: rgba(255,255,255,0.1); transform: translate(16px, -16px);"></div>
                            <div class="absolute bottom-0 left-0 w-10 h-10 rounded-full" style="background: rgba(255,255,255,0.05); transform: translate(-12px, 12px);"></div>
                            <p class="text-[11px] uppercase font-bold tracking-wider mb-1" style="color: #bfdbfe;">Total Cost</p>
                            <p class="text-2xl font-extrabold tracking-tight">
                                @php
                                    $formatted = number_format($requestTotal, 2);
                                    $parts = explode('.', $formatted);
                                @endphp
                                <span class="text-base font-bold mr-0.5" style="color: #bfdbfe;">PHP</span>{{ $parts[0] }}<span class="text-base" style="color: #bfdbfe;">.{{ $parts[1] }}</span>
                            </p>
                            <p class="text-[11px] mt-1.5" style="color: #bfdbfe;">
                                {{ count($costItems) }} {{ count($costItems) === 1 ? 'item' : 'items' }} logged
                            </p>
                        </div>
                    </div>

                    {{-- Add Cost Form --}}
                    @if($showCostForm)
                        <div class="rounded-2xl p-5 mb-4 space-y-4" style="background: linear-gradient(to bottom, rgba(238,242,255,0.8), white); border: 1px solid #c7d2fe;"
                             x-data x-init="$nextTick(() => $refs.costInput?.focus())">
                            <h4 class="text-sm font-bold text-[#070589] mb-1">{{ $editingCostId ? 'Edit Cost Entry' : 'New Cost Entry' }}</h4>
                            <div>
                                <label class="text-[11px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1.5 block">Description</label>
                                <input type="text" wire:model="costDescription" placeholder="e.g. Labor - Plumber (2 hrs), Pipe fittings..." maxlength="255"
                                    class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm text-gray-700 placeholder-gray-300 outline-none focus:ring-2 focus:ring-blue-200">
                                @error('costDescription') <p class="text-xs mt-1 text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-[11px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1.5 block">Amount (PHP)</label>
                                <style>
                                    input.hide-spinner::-webkit-outer-spin-button,
                                    input.hide-spinner::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
                                    input.hide-spinner { -moz-appearance: textfield; }
                                </style>
                                <input type="text" inputmode="decimal" wire:model="costAmount" x-ref="costInput"
                                    placeholder="Enter amount (e.g. 2500.00)" maxlength="12"
                                    x-on:input="$el.value = $el.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1')"
                                    class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm text-gray-700 placeholder-gray-300 outline-none font-mono hide-spinner focus:ring-2 focus:ring-blue-200">
                                @error('costAmount') <p class="text-xs mt-1 text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-[11px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1.5 block">Charge To</label>
                                <div class="flex gap-2">
                                    <button type="button" wire:click="$set('chargedTo', 'owner')"
                                        class="px-6 py-2 rounded-xl text-xs font-bold transition-all duration-200 border"
                                        style="{{ $chargedTo === 'owner' ? 'background:#eff6ff; border-color:#93c5fd; color:#1d4ed8;' : 'background:white; border-color:#e5e7eb; color:#6b7280;' }}">
                                        Owner
                                    </button>
                                    <button type="button" wire:click="$set('chargedTo', 'tenant')"
                                        class="px-6 py-2 rounded-xl text-xs font-bold transition-all duration-200 border"
                                        style="{{ $chargedTo === 'tenant' ? 'background:#fef3c7; border-color:#fcd34d; color:#92400e;' : 'background:white; border-color:#e5e7eb; color:#6b7280;' }}">
                                        Tenant
                                    </button>
                                </div>
                                <p class="text-[11px] mt-1.5 {{ $chargedTo === 'tenant' ? 'text-amber-600' : 'text-blue-500' }}">
                                    @if($chargedTo === 'tenant')
                                        This cost will be added to the tenant's billing.
                                    @else
                                        This cost will be recorded as an owner expense.
                                    @endif
                                </p>
                                @error('chargedTo') <p class="text-xs mt-1 text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-[11px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-1.5 block">Quick Select</label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach([500, 1000, 2500, 5000, 10000] as $preset)
                                        <button type="button" wire:click="$set('costAmount', {{ $preset }})"
                                            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all"
                                            style="{{ $costAmount == $preset ? 'background-color:#070589; color:white; box-shadow:0 1px 2px rgba(0,0,0,0.1);' : 'background:white; border:1px solid #e5e7eb; color:#4b5563;' }}">
                                            {{ number_format($preset) }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                            <div class="flex justify-end gap-2">
                                <button wire:click="$set('showCostForm', false)"
                                    class="px-5 py-2 text-xs font-semibold rounded-xl transition-colors bg-gray-100 text-gray-600 hover:bg-gray-200">
                                    Cancel
                                </button>
                                <button wire:click="saveCost" wire:loading.attr="disabled" wire:target="saveCost"
                                    class="px-5 py-2 text-xs font-bold text-white rounded-xl transition-colors disabled:opacity-50 bg-[#070589] hover:bg-[#000060]">
                                    <span wire:loading.remove wire:target="saveCost">{{ $editingCostId ? 'Update Cost' : 'Save Cost Entry' }}</span>
                                    <span wire:loading wire:target="saveCost">Saving...</span>
                                </button>
                            </div>
                        </div>
                    @endif

                    {{-- Cost Items List --}}
                    @if(!empty($costItems))
                        <div class="space-y-2">
                            @foreach($costItems as $index => $item)
                                <div class="group bg-[#F8FAFF] border border-blue-50 rounded-xl p-3.5 flex items-center gap-3 hover:shadow-sm transition-all duration-200">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 bg-[#EEF2FF]">
                                        <svg class="w-4 h-4 text-[#2360E8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-800 truncate">
                                            {{ $item['description'] ?? 'Maintenance Cost' }}
                                        </p>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <p class="text-[11px] text-gray-400">
                                                {{ \Carbon\Carbon::parse($item['completion_date'])->format('M d, Y') }}
                                            </p>
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[11px] font-bold uppercase tracking-wide
                                                {{ ($item['charged_to'] ?? 'owner') === 'tenant' ? 'bg-amber-100 text-amber-700' : 'bg-blue-50 text-blue-600' }}">
                                                {{ ($item['charged_to'] ?? 'owner') === 'tenant' ? 'Tenant' : 'Owner' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <p class="text-sm font-bold font-mono" style="color: #059669;">
                                            PHP {{ number_format($item['cost'], 2) }}
                                        </p>
                                    </div>
                                    <div class="flex-shrink-0 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <flux:tooltip :content="'Edit this expense entry'" position="bottom">
                                            <button wire:click="editCostItem({{ $item['log_id'] }})"
                                                class="w-7 h-7 rounded-lg flex items-center justify-center transition-colors bg-[#EEF2FF] hover:bg-blue-100">
                                                <svg class="w-3.5 h-3.5 text-[#2360E8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                        </flux:tooltip>
                                        <flux:tooltip :content="'Remove this expense entry'" position="bottom">
                                            <button @click="confirmDelete = {{ $item['log_id'] }}"
                                                class="w-7 h-7 rounded-lg flex items-center justify-center transition-colors bg-red-50 hover:bg-red-100">
                                                <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </flux:tooltip>
                                    </div>
                                </div>
                            @endforeach

                            @if(count($costItems) > 1)
                                <div class="rounded-xl p-3.5 flex items-center justify-between mt-1" style="background-color: #ecfdf5; border: 1px solid #d1fae5;">
                                    <span class="text-xs font-bold uppercase tracking-wide" style="color: #047857;">Request Total</span>
                                    <span class="text-base font-extrabold font-mono" style="color: #047857;">PHP {{ number_format($requestTotal, 2) }}</span>
                                </div>
                            @endif
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
                                    <h4 class="text-sm font-bold text-[#070589]">Remove Cost Entry?</h4>
                                    <p class="text-xs text-gray-400">This action cannot be undone.</p>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button @click="confirmDelete = null" class="flex-1 px-4 py-2 rounded-xl text-sm font-semibold bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
                                    Cancel
                                </button>
                                <button @click="$wire.removeCostItem(confirmDelete); confirmDelete = null"
                                    class="flex-1 px-4 py-2 rounded-xl text-sm font-bold bg-red-500 text-white hover:bg-red-600 transition-colors">
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Cost Threshold Warning --}}
                @if($requestTotal >= \App\Livewire\Layouts\Maintenance\ManagerMaintenanceDetail::COST_WARNING_THRESHOLD)
                    <div class="flex items-center gap-3 px-4 py-3 bg-amber-50 border border-amber-200 rounded-xl">
                        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-bold text-amber-700">Cost Threshold Exceeded</p>
                            <p class="text-xs text-amber-600">This request's total (PHP {{ number_format($requestTotal, 2) }}) exceeds the PHP {{ number_format(\App\Livewire\Layouts\Maintenance\ManagerMaintenanceDetail::COST_WARNING_THRESHOLD, 0) }} threshold. Please review before adding more costs.</p>
                        </div>
                    </div>
                @endif

                {{-- ══════════════════════════════════════════════════════════ --}}
                {{-- ── INTERNAL NOTES ── --}}
                {{-- ══════════════════════════════════════════════════════════ --}}
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-8 h-8 rounded-xl bg-[#EEF2FF] flex items-center justify-center">
                            <svg class="w-4 h-4 text-[#2360E8]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <h5 class="font-bold text-sm text-[#070589] uppercase tracking-wide">Internal Notes</h5>
                        <span class="text-[11px] text-gray-400 font-normal">(not visible to tenant)</span>
                    </div>

                    <div class="mb-3">
                        <input type="text" wire:model="noteText" wire:keydown.enter="saveNote" placeholder="Add a note..." maxlength="1000"
                            class="w-full px-3 py-2 bg-[#F8FAFF] border border-blue-50 rounded-xl text-sm text-gray-700 placeholder-gray-300 outline-none focus:ring-2 focus:ring-blue-200 focus:border-transparent mb-2">
                        <div class="flex items-center justify-between">
                            @error('noteText') <p class="text-xs text-red-500">{{ $message }}</p> @else <span></span> @enderror
                            <flux:tooltip :content="'Add a note visible only to staff'" position="bottom">
                                <button wire:click="saveNote" class="px-5 py-2 text-xs font-bold text-white bg-[#070589] hover:bg-[#000060] rounded-xl transition-colors">
                                    Add
                                </button>
                            </flux:tooltip>
                        </div>
                    </div>

                    @if(!empty($notes))
                        <div class="space-y-2 max-h-48 overflow-y-auto custom-scrollbar">
                            @foreach($notes as $n)
                                <div class="group flex items-start gap-3 bg-[#F8FAFF] border border-blue-50 rounded-xl p-3">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-gray-700">{{ $n['note'] }}</p>
                                        <p class="text-[11px] text-gray-400 mt-1">
                                            {{ $n['author_name'] }} &middot; {{ \Carbon\Carbon::parse($n['created_at'])->format('M d, h:i A') }}
                                        </p>
                                    </div>
                                    <flux:tooltip :content="'Permanently remove this note'" position="bottom">
                                        <button wire:click="deleteNote({{ $n['note_id'] }})"
                                            class="opacity-0 group-hover:opacity-100 transition-opacity w-6 h-6 flex items-center justify-center rounded-full hover:bg-red-100 text-gray-400 hover:text-red-500 flex-shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </flux:tooltip>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-gray-400 italic">No internal notes yet.</p>
                    @endif
                </div>

                {{-- ══════════════════════════════════════════════════════════ --}}
                {{-- ── ACTIVITY LOG ── --}}
                {{-- ══════════════════════════════════════════════════════════ --}}
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-8 h-8 rounded-xl bg-[#EEF2FF] flex items-center justify-center">
                            <svg class="w-4 h-4 text-[#2360E8]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h5 class="font-bold text-sm text-[#070589] uppercase tracking-wide">Activity Log</h5>
                    </div>

                    @if(!empty($activities))
                        <div class="space-y-2 max-h-60 overflow-y-auto custom-scrollbar">
                            @foreach($activities as $act)
                                @php
                                    $borderColor = match($act['action']) {
                                        'status_changed' => 'border-l-blue-500 bg-blue-50/30',
                                        'cost_added', 'cost_removed' => 'border-l-emerald-500 bg-emerald-50/30',
                                        'note_added' => 'border-l-purple-500 bg-purple-50/30',
                                        'urgency_changed' => 'border-l-red-500 bg-red-50/30',
                                        'worker_assigned', 'eta_updated' => 'border-l-indigo-500 bg-indigo-50/30',
                                        'archived' => 'border-l-gray-400 bg-gray-50/30',
                                        default => 'border-l-gray-300 bg-gray-50/30'
                                    };
                                @endphp
                                <div class="border-l-[3px] rounded-r-lg px-3 py-2.5 {{ $borderColor }}">
                                    <p class="text-sm text-gray-700">{{ $act['details'] }}</p>
                                    <p class="text-[11px] text-gray-400 mt-0.5">
                                        {{ $act['actor_name'] }} &middot; {{ \Carbon\Carbon::parse($act['created_at'])->format('M d, h:i A') }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-gray-400 italic">No activity recorded yet.</p>
                    @endif
                </div>

                {{-- ══════════════════════════════════════════════════════════ --}}
                {{-- ── UPDATES TIMELINE ── --}}
                {{-- ══════════════════════════════════════════════════════════ --}}
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-[#EEF2FF] flex items-center justify-center">
                                <svg class="w-4 h-4 text-[#2360E8]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <h5 class="font-bold text-sm text-[#070589] uppercase tracking-wide">Updates</h5>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex items-center gap-2">
                            @if($canMarkOngoing)
                                <button x-on:click="$dispatch('open-modal', 'confirm-mark-ongoing')"
                                    class="px-4 py-1.5 rounded-full text-xs font-bold bg-yellow-50 text-yellow-700 border border-yellow-200 hover:bg-yellow-100 transition-colors">
                                    Mark as Ongoing
                                </button>
                            @endif

                            @if($canMarkCompleted)
                                <button x-on:click="$dispatch('open-modal', 'confirm-mark-completed')"
                                    class="px-4 py-1.5 rounded-full text-xs font-bold bg-green-50 text-green-700 border border-green-200 hover:bg-green-100 transition-colors">
                                    Mark as Completed
                                </button>
                            @endif

                            @if($ticket->status === 'Completed')
                                <span class="flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Resolved
                                </span>
                                <flux:tooltip :content="'Undo the last status change'" position="bottom">
                                    <button x-on:click="$dispatch('open-modal', 'confirm-revert-status')"
                                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-gray-50 text-gray-500 border border-gray-200 hover:bg-gray-100 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                        </svg>
                                        Revert
                                    </button>
                                </flux:tooltip>
                            @elseif($ticket->status === 'Ongoing')
                                <flux:tooltip :content="'Change status back to Pending'" position="bottom">
                                    <button x-on:click="$dispatch('open-modal', 'confirm-revert-status')"
                                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-gray-50 text-gray-500 border border-gray-200 hover:bg-gray-100 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                        </svg>
                                        Revert
                                    </button>
                                </flux:tooltip>
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
                                    <p class="font-semibold text-gray-800 text-sm">Request completed</p>
                                    <p class="text-xs text-gray-400 mb-2">
                                        {{ \Carbon\Carbon::parse($ticket->updated_at)->format('M d, h:i A') }}
                                    </p>
                                    <div class="bg-[#F8FAFF] border border-blue-50 shadow-sm p-3 rounded-xl text-sm text-gray-600">
                                        The maintenance issue has been resolved and marked as completed.
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if(in_array($ticket->status, ['Ongoing', 'In Progress', 'Completed', 'Resolved']))
                            <div class="flex gap-4 relative pb-6">
                                <div class="absolute left-[9px] top-6 bottom-0 w-[2px] bg-gray-200"></div>
                                <div class="flex-shrink-0 w-5 h-5 rounded-full bg-[#070589] border-2 border-white shadow z-10 mt-0.5"></div>
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-800 text-sm">Technician assigned</p>
                                    <p class="text-xs text-gray-400 mb-2">
                                        {{ \Carbon\Carbon::parse($ticket->updated_at)->format('M d, h:i A') }}
                                    </p>
                                    <div class="bg-[#F8FAFF] border border-blue-50 shadow-sm p-3 rounded-xl text-sm text-gray-600">
                                        {{ $ticket->assigned_to ? $ticket->assigned_to . ' has been assigned to handle this issue.' : 'A technician has been dispatched to check the issue.' }}
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="flex gap-4 relative">
                            <div class="flex-shrink-0 w-5 h-5 rounded-full bg-gray-300 border-2 border-white shadow z-10 mt-0.5"></div>
                            <div class="flex-1">
                                <p class="font-semibold text-gray-800 text-sm">Request received</p>
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

                {{-- ══════════════════════════════════════════════════════════ --}}
                {{-- ── TENANT FEEDBACK ── --}}
                {{-- ══════════════════════════════════════════════════════════ --}}
                @if($feedback)
                    @php
                        $tagList = array_filter(array_map('trim', explode(',', $feedback->experience_tag ?? '')));
                        $ratingLabels = [1 => 'Poor', 2 => 'Fair', 3 => 'Good', 4 => 'Great', 5 => 'Excellent'];
                        $ratingLabel  = $ratingLabels[$feedback->rating] ?? '';
                    @endphp
                    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2.5 mb-4">
                            <div class="w-8 h-8 rounded-xl bg-[#EEF2FF] flex items-center justify-center">
                                <svg class="w-4 h-4 text-[#2360E8]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                </svg>
                            </div>
                            <h5 class="font-bold text-sm text-[#070589] uppercase tracking-wide">Tenant Feedback</h5>
                        </div>

                        <div class="bg-[#F8FAFF] rounded-xl p-4 border border-blue-50 space-y-4">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-2">Overall Service Rating</p>
                                <div class="flex items-center gap-1.5">
                                    @for($s = 1; $s <= 5; $s++)
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="width:22px; height:22px;"
                                            fill="{{ $s <= $feedback->rating ? '#FBBF24' : '#E5E7EB' }}">
                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                        </svg>
                                    @endfor
                                    <span class="ml-2 text-sm font-bold text-gray-800">{{ $ratingLabel }}</span>
                                    <span class="text-xs text-gray-400">({{ $feedback->rating }}/5)</span>
                                </div>
                            </div>

                            @if(!empty($tagList))
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-2">Experience</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($tagList as $tag)
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-[#070589] text-white">
                                                {{ $tag }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if(!empty($feedback->comment))
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-wider text-[#2360E8]/60 mb-2">Comment</p>
                                    <div class="bg-white border border-blue-100 rounded-xl p-3 text-sm text-gray-700 leading-relaxed">
                                        {{ $feedback->comment }}
                                    </div>
                                </div>
                            @endif

                            <p class="text-[11px] text-gray-400 pt-1 border-t border-blue-100">
                                Submitted {{ \Carbon\Carbon::parse($feedback->created_at)->format('M d, Y \a\t h:i A') }}
                            </p>
                        </div>
                    </div>
                @elseif(in_array($ticket->status, ['Completed', 'Resolved']))
                    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2.5 mb-4">
                            <div class="w-8 h-8 rounded-xl bg-[#EEF2FF] flex items-center justify-center">
                                <svg class="w-4 h-4 text-[#2360E8]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                </svg>
                            </div>
                            <h5 class="font-bold text-sm text-[#070589] uppercase tracking-wide">Tenant Feedback</h5>
                        </div>
                        <div class="rounded-xl border-2 border-dashed border-gray-200 bg-[#F8FAFF] py-8 flex flex-col items-center text-gray-400">
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

        {{-- Confirmation Modals --}}

        <x-ui.modal-confirm name="confirm-mark-ongoing"
            title="Mark as Ongoing?"
            description="This will update the status to Ongoing and the tenant will be notified."
            confirmText="Yes, Mark as Ongoing" cancelText="Cancel" confirmAction="markAsOngoing"/>

        <x-ui.modal-confirm name="confirm-mark-completed"
            title="Mark as Completed?"
            description="This will mark the request as completed. The tenant will be notified and can leave feedback."
            confirmText="Yes, Mark as Completed" cancelText="Cancel" confirmAction="markAsCompleted"/>

        <x-ui.modal-confirm name="confirm-revert-status"
            title="Revert Status?"
            description="This will move the request back to its previous status. The tenant will be notified."
            confirmText="Yes, Revert" cancelText="Cancel" confirmAction="revertStatus"/>

    @else
        {{-- Empty State --}}
        <div class="flex-1 flex flex-col items-center justify-center text-gray-400 p-6">
            <div class="bg-[#EEF2FF] p-8 rounded-full mb-5">
                <svg class="h-16 w-16 text-[#2360E8] opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-500 mb-1">No Ticket Selected</h3>
            <p class="text-sm text-center">Select a maintenance request from the list to view details.</p>
        </div>
    @endif

</div>
