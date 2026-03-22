<div class="font-sans">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

        {{-- Header --}}
        <div class="px-6 py-5 border-b border-gray-100">
            <h2 class="text-lg font-bold text-gray-900">Utility Bill Entry</h2>
            <p class="text-sm text-gray-500 mt-1">Input total Meralco or water bill for a unit. The system will auto-split among active tenants.</p>
        </div>

        {{-- Form --}}
        <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Unit Selection --}}
                <div>
                    <label for="selectedUnit" class="block text-sm font-semibold text-gray-700 mb-2">Select Unit</label>
                    <select wire:model.live="selectedUnit" id="selectedUnit" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
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
                <div>
                    <label for="totalAmount" class="block text-sm font-semibold text-gray-700 mb-2">Total Bill Amount (PHP)</label>
                    <input type="number" step="0.01" min="0" wire:model.live.debounce.300ms="totalAmount" id="totalAmount" placeholder="0.00" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
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

            {{-- Submit Button --}}
            <div class="flex justify-end">
                <button
                    wire:click="confirmSave"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                    @if(!$selectedUnit || !$totalAmount || $tenantCount === 0) disabled @endif
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Apply Utility Split
                </button>
            </div>
        </div>

        {{-- Confirmation Modal --}}
        @if($showConfirmation)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm">
                <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Confirm Utility Split</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        This will add a <strong>&#8369; {{ number_format($perTenantAmount, 2) }}</strong>
                        {{ $utilityType }} charge to each of the <strong>{{ $tenantCount }}</strong> active tenant(s)
                        for <strong>{{ \Carbon\Carbon::parse($billingPeriod . '-01')->format('F Y') }}</strong>.
                    </p>
                    <div class="flex justify-end gap-3">
                        <button wire:click="$set('showConfirmation', false)" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button wire:click="save" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">
                            Confirm & Apply
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Recent Utility Bills --}}
        @if($recentBills->isNotEmpty())
            <div class="border-t border-gray-100">
                <div class="px-6 py-4">
                    <h3 class="text-sm font-bold text-gray-700 mb-3">Recent Utility Bills</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    <th class="pb-3 pr-4">Unit</th>
                                    <th class="pb-3 pr-4">Type</th>
                                    <th class="pb-3 pr-4">Period</th>
                                    <th class="pb-3 pr-4">Total</th>
                                    <th class="pb-3 pr-4">Tenants</th>
                                    <th class="pb-3">Per Tenant</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($recentBills as $bill)
                                    <tr>
                                        <td class="py-3 pr-4 font-medium text-gray-900">
                                            {{ $bill->unit->property->building_name ?? '' }} — Unit {{ $bill->unit->unit_number ?? '' }}
                                        </td>
                                        <td class="py-3 pr-4">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $bill->utility_type === 'electricity' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800' }}">
                                                {{ ucfirst($bill->utility_type) }}
                                            </span>
                                        </td>
                                        <td class="py-3 pr-4 text-gray-600">{{ \Carbon\Carbon::parse($bill->billing_period)->format('M Y') }}</td>
                                        <td class="py-3 pr-4 font-semibold text-gray-900">&#8369; {{ number_format($bill->total_amount, 2) }}</td>
                                        <td class="py-3 pr-4 text-gray-600">{{ $bill->tenant_count }}</td>
                                        <td class="py-3 font-semibold text-gray-900">&#8369; {{ number_format($bill->per_tenant_amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
