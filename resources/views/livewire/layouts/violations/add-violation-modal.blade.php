<div>
    @if($isOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm">
            <div class="relative w-full max-w-3xl bg-gray-50 rounded-2xl shadow-xl overflow-hidden max-h-[95vh] flex flex-col">

                {{-- Header --}}
                <div class="bg-[#070589] text-white p-6 flex-shrink-0">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-xl font-bold uppercase">Record Violation</h2>
                            <p class="mt-1 text-sm text-blue-100">Issue a violation notice to a tenant</p>
                        </div>
                        <flux:tooltip :content="'Close violation form without saving'" position="bottom">
                            <button
                                type="button"
                                wire:click="close"
                                class="text-white hover:text-blue-200 transition-colors focus:outline-none"
                            >
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </flux:tooltip>
                    </div>
                </div>

                {{-- Body --}}
                <div class="p-6 space-y-6 overflow-y-auto flex-1">

                    {{-- Tenant Selector --}}
                    <div>
                        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2 block">Select Tenant</label>
                        <select
                            wire:model.live="leaseId"
                            class="w-full bg-white border border-gray-200 rounded-xl py-3 px-4 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-200 transition"
                        >
                            <option value="">-- Choose a tenant --</option>
                            @foreach($tenantLeases as $lease)
                                <option value="{{ $lease['lease_id'] }}">
                                    {{ $lease['tenant_name'] }} — Unit {{ $lease['unit_number'] }} ({{ $lease['building_name'] }})
                                </option>
                            @endforeach
                        </select>
                        @error('leaseId')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Penalty Preview --}}
                    @if($penaltyPreview)
                        @php
                            $previewStyles = match($penaltyPreview['penalty_type']) {
                                'written_warning' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
                                'fine' => 'bg-orange-50 border-orange-200 text-orange-800',
                                'lease_termination' => 'bg-red-50 border-red-200 text-red-800',
                                default => 'bg-gray-50 border-gray-200 text-gray-700',
                            };
                            $previewLabel = match($penaltyPreview['penalty_type']) {
                                'written_warning' => 'Written Warning',
                                'fine' => 'Fine — PHP ' . number_format($penaltyPreview['fine_amount'] ?? 0, 2),
                                'lease_termination' => 'Grounds for Lease Termination',
                                default => ucfirst($penaltyPreview['penalty_type']),
                            };
                            $offenseLabel = match($penaltyPreview['offense_number']) {
                                1 => '1st', 2 => '2nd', 3 => '3rd', default => $penaltyPreview['offense_number'] . 'th'
                            };
                        @endphp
                        <div class="rounded-xl p-4 border {{ $previewStyles }}">
                            <p class="text-xs font-bold uppercase tracking-wide mb-1">Penalty Preview</p>
                            <p class="text-sm font-semibold">{{ $offenseLabel }} Offense — {{ $previewLabel }}</p>
                        </div>
                    @endif

                    {{-- Category --}}
                    <div>
                        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2 block">Violation Category</label>
                        @php
                            $categories = [
                                'Noise Violation',
                                'Property Damage',
                                'Unauthorized Guest',
                                'Unauthorized Modification',
                                'Illegal Activity',
                                'Cleanliness',
                                'Curfew Violation',
                                'Smoking',
                                'Pet Violation',
                                'DIY Maintenance',
                                'Other',
                            ];
                        @endphp
                        <div class="flex flex-wrap gap-2">
                            @foreach($categories as $cat)
                                <button
                                    type="button"
                                    wire:click="$set('category', '{{ $cat }}')"
                                    class="px-3 py-2 rounded-xl text-xs font-semibold border transition
                                        {{ $category === $cat
                                            ? 'bg-[#070589] text-white border-[#070589]'
                                            : 'bg-white text-gray-600 border-gray-200 hover:border-blue-300 hover:bg-blue-50' }}"
                                >
                                    {{ $cat }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Severity --}}
                    <div>
                        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2 block">Severity Level</label>
                        <div class="grid grid-cols-3 gap-3">
                            @foreach(['minor' => ['Minor', 'Low-impact violation', 'bg-blue-50 border-blue-300 text-blue-700'], 'major' => ['Major', 'Significant impact', 'bg-orange-50 border-orange-300 text-orange-700'], 'serious' => ['Serious', 'Immediate termination', 'bg-red-50 border-red-300 text-red-700']] as $key => [$label, $desc, $activeStyle])
                                <button
                                    type="button"
                                    wire:click="$set('severity', '{{ $key }}')"
                                    class="p-3 rounded-xl border-2 text-center transition
                                        {{ $severity === $key
                                            ? $activeStyle . ' border-2'
                                            : 'bg-white border-gray-200 text-gray-500 hover:border-gray-300' }}"
                                >
                                    <p class="text-sm font-bold">{{ $label }}</p>
                                    <p class="text-[10px] mt-0.5">{{ $desc }}</p>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Violation Date --}}
                    <div>
                        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2 block">Violation Date</label>
                        <input
                            type="date"
                            wire:model="violationDate"
                            max="{{ now()->format('Y-m-d') }}"
                            class="w-full bg-white border border-gray-200 rounded-xl py-3 px-4 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-200 transition"
                        />
                        @error('violationDate')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2 block">Description</label>
                        <textarea
                            wire:model="description"
                            rows="4"
                            maxlength="2000"
                            class="w-full bg-white border border-gray-200 rounded-xl p-4 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-200 placeholder-gray-400 transition resize-none"
                            placeholder="Describe the violation in detail..."
                        ></textarea>
                        <div class="flex justify-between mt-1">
                            @error('description')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @else
                                <span></span>
                            @enderror
                            <p class="text-xs text-gray-400">{{ strlen($description) }}/2000</p>
                        </div>
                    </div>

                    {{-- Evidence Photos --}}
                    <div>
                        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2 block">Evidence Photos (Optional)</label>
                        <div class="flex items-center gap-3 flex-wrap">
                            @foreach($evidencePhotos as $index => $photo)
                                <div class="relative">
                                    <img src="{{ $photo->temporaryUrl() }}" class="h-20 w-20 object-cover rounded-xl border border-gray-200" />
                                    <button type="button" wire:click="removePhoto({{ $index }})"
                                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-600">
                                        &times;
                                    </button>
                                </div>
                            @endforeach
                            @if(count($evidencePhotos) < 3)
                                <label class="h-20 w-20 flex items-center justify-center border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <input type="file" wire:model="evidencePhotos" accept="image/*" class="hidden" />
                                </label>
                            @endif
                        </div>
                        @error('evidencePhotos.*')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Footer --}}
                <div class="p-6 bg-white border-t border-gray-200 flex justify-between flex-shrink-0">
                    <button type="button" wire:click="close"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3 px-8 rounded-xl text-sm transition-colors">
                        Cancel
                    </button>
                    <button type="button" wire:click="save"
                        class="bg-[#070589] hover:bg-[#000060] text-white font-bold py-3 px-10 rounded-xl text-sm transition-colors shadow-lg">
                        Issue Violation
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
