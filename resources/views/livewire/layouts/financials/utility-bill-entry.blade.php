<div>
    @if($isOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm">
            <div class="relative w-full max-w-2xl bg-gray-50 rounded-2xl shadow-xl overflow-hidden max-h-[95vh] flex flex-col">

                {{-- Header --}}
                <div class="bg-[#070589] text-white p-6 flex-shrink-0">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-xl font-bold uppercase">Utility Bill Entry</h2>
                            <p class="mt-1 text-sm text-blue-100">Input total Meralco or water bill for a unit. The system will auto-split among active tenants.</p>
                        </div>
                        <flux:tooltip :content="'Close utility bill form'" position="bottom">
                            <button type="button" wire:click="close" class="text-white hover:text-blue-200 transition-colors focus:outline-none">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </flux:tooltip>
                    </div>
                </div>

                {{-- Body --}}
                <div class="p-6 space-y-6 overflow-y-auto flex-1">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- Building Selection --}}
                        <div>
                            <label for="selectedBuilding" class="block text-sm font-semibold text-gray-700 mb-2">Select Building</label>
                            <select wire:model.live="selectedBuilding" id="selectedBuilding" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="">-- Select Building --</option>
                                @foreach($buildings as $building)
                                    <option value="{{ $building['id'] }}">{{ $building['label'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Unit Selection --}}
                        <div>
                            <label for="selectedUnit" class="block text-sm font-semibold text-gray-700 mb-2">Select Unit</label>
                            <select wire:model.live="selectedUnit" id="selectedUnit" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" @if(!$selectedBuilding) disabled @endif>
                                <option value="">-- Select Unit --</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit['id'] }}">{{ $unit['label'] }}</option>
                                @endforeach
                            </select>
                            @error('selectedUnit') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Utility Type --}}
                        <div>
                            <label for="utilityType" class="block text-sm font-semibold text-gray-700 mb-2">Utility Type</label>
                            <select wire:model.live="utilityType" id="utilityType" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="electricity">Electricity (Meralco)</option>
                                <option value="water">Water</option>
                            </select>
                            @error('utilityType') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Billing Period --}}
                        <div>
                            <label for="billingPeriod" class="block text-sm font-semibold text-gray-700 mb-2">Billing Period</label>
                            <input type="month" wire:model.live="billingPeriod" id="billingPeriod" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            @error('billingPeriod') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Total Amount --}}
                        <div class="md:col-span-2">
                            <label for="totalAmount" class="block text-sm font-semibold text-gray-700 mb-2">Total Bill Amount (PHP)</label>
                            <input type="number" step="0.01" min="1" max="999999.99" wire:model.live.debounce.300ms="totalAmount" id="totalAmount" placeholder="e.g. 500"
                                   x-on:keydown="if (['-', 'e', 'E', '+'].includes($event.key)) $event.preventDefault(); if ($event.key === '0' && ($el.value === '' || $el.value === '0')) $event.preventDefault()"
                                   x-on:paste.prevent
                                   class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            @if($totalAmount !== '' && $totalAmount !== null && (! is_numeric($totalAmount) || (float) $totalAmount < 1))
                                <p class="mt-1 text-xs text-red-600">Amount must be at least ₱1.00.</p>
                            @elseif($totalAmount !== '' && $totalAmount !== null && is_numeric($totalAmount) && (float) $totalAmount > 999999.99)
                                <p class="mt-1 text-xs text-red-600">Amount cannot exceed ₱999,999.99.</p>
                            @endif
                            @error('totalAmount') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Split Preview --}}
                    @if($tenantCount > 0 && $perTenantAmount > 0)
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-5">
                            <h4 class="text-sm font-bold text-blue-900 mb-3">Split Preview</h4>
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <p class="text-xs text-blue-600 font-medium">Total Bill</p>
                                    <p class="text-lg font-bold text-blue-900">&#8369; {{ number_format((float)$totalAmount, 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-blue-600 font-medium">Active Tenants</p>
                                    <p class="text-lg font-bold text-blue-900">{{ $tenantCount }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-blue-600 font-medium">Per Tenant Share</p>
                                    <p class="text-lg font-bold text-blue-900">&#8369; {{ number_format($perTenantAmount, 2) }}</p>
                                </div>
                            </div>
                            <p class="text-xs text-blue-600 mt-3">
                                &#8369; {{ number_format((float)$totalAmount, 2) }} &divide; {{ $tenantCount }} tenants = &#8369; {{ number_format($perTenantAmount, 2) }} each
                            </p>
                        </div>
                    @elseif($selectedUnit && $tenantCount === 0)
                        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-5">
                            <p class="text-sm text-yellow-800 font-medium">No active tenants found in this unit. Cannot split utility bill.</p>
                        </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="p-6 border-t border-gray-200 flex justify-end gap-3 flex-shrink-0">
                    <button
                        wire:click="close"
                        class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        wire:click="confirmSave"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#070589] hover:bg-[#000060] text-white text-sm font-semibold rounded-xl shadow-sm transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        @if(!$selectedUnit || !$totalAmount || $tenantCount === 0) disabled @endif
                    >
                        Apply Utility Split
                    </button>
                </div>

            </div>
        </div>
    @endif

    <x-ui.modal-confirm
        name="confirm-utility-split"
        title="Confirm Utility Split"
        description="This will split the utility bill among all active tenants in the selected unit and add it to their billing."
        confirmText="Confirm & Apply"
        cancelText="Cancel"
        confirmAction="save"
    />
</div>
