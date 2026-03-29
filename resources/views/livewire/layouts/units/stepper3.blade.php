<div class="p-6 md:p-8">
    <h3 class="text-lg font-semibold text-[#021C3F] mb-6">
        Review & Predict Price
    </h3>

    {{-- Success/Error Messages --}}
    @if (session('success'))
        <div class="p-3 bg-green-100 text-green-800 rounded-lg mb-6 text-sm" wire:key="success">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="p-3 bg-red-100 text-red-800 rounded-lg mb-6 text-sm" wire:key="error">
            {{ session('error') }}
        </div>
    @endif

    {{-- Unit Details Section --}}
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm mb-6">
        <div class="bg-[#003CC1] px-4 py-3 rounded-t-lg border-b border-gray-200">
            <h4 class="text-md font-semibold text-white">Unit Details</h4>
        </div>
        <div class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Building</span>
                        <span class="text-sm font-medium text-gray-900">{{ $properties->find($property_id)?->building_name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Floor</span>
                        <span class="text-sm font-medium text-gray-900">{{ $floor_number }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Living Area</span>
                        <span class="text-sm font-medium text-gray-900">{{ $living_area }} sqft</span>
                    </div>
                </div>
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Occupant Type</span>
                        <span class="text-sm font-medium text-gray-900">{{ $occupants }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Bed Type</span>
                        <span class="text-sm font-medium text-gray-900">{{ $bed_type }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Room Capacity</span>
                        <span class="text-sm font-medium text-gray-900">{{ $room_cap }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Selected Amenities Section --}}
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm mb-6">
        <div class="bg-[#003CC1] px-4 py-3 rounded-t-lg border-b border-gray-200">
            <h4 class="text-md font-semibold text-white">Selected Amenities</h4>
        </div>
        <div class="p-4">
            <div class="flex flex-wrap gap-2">
                @forelse (array_keys(array_filter($model_amenities)) as $amenityKey)
                    <span class="text-xs bg-blue-100 text-blue-800 px-3 py-1.5 rounded-full">{{ $amenity_labels[$amenityKey] }}</span>
                @empty
                    <span class="text-sm text-white">No amenities selected.</span>
                @endforelse
            </div>
        </div>
    </div>

    {{-- PREDICTION SECTION --}}
    <div class="rounded-xl overflow-hidden shadow-md mb-6" style="background: linear-gradient(90deg, #1D56D9 0%, #276AFF 47.95%, #2048BD 100%);">
        <div class="p-6 md:p-8 text-white">

            {{-- Top Section: Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">

                {{-- Left Column: Prediction Details --}}
                <div class="space-y-4">
                    <h2 class="text-2xl font-bold">AI Price Prediction</h2>

                    <div class="flex flex-col md:flex-row md:items-center gap-4">
                        <div>
                            <label class="text-sm font-medium opacity-80">Predicted Monthly Rate</label>
                        </div>
                        <div id="predict-price" class="relative">
                            {{-- Glass container --}}
                            <div class="w-32 h-12 bg-white/20 backdrop-blur-md rounded-lg border border-white/20 flex items-center justify-center shadow-lg">
                                <span class="text-2xl font-bold text-white">
                                    @if ($predicted_price)
                                        ₱{{ number_format($predicted_price, 0, '.', ',') }}
                                    @else
                                        ₱24,000
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    <p class="text-sm opacity-80 pt-2">
                        Based on location, capacity, and amenities
                    </p>
                </div>

               {{-- Right Column: Actual Price Input --}}
            @if ($predicted_price)
                <div class="relative md:text-right md:flex md:flex-col md:items-end">
                    <label for="actual_price" class="block text-l font-medium text-white opacity-80 mb-2 md:text-right">Set Actual Price</label>

                    {{-- Glass container --}}
                    <div id="actual-price" class="w-80 h-18 bg-white/20 backdrop-blur-md rounded-lg border border-white/20 flex items-center justify-center shadow-lg px-4 md:justify-end"
                        x-data="{
                            rawValue: '{{ $actual_price ? number_format((int)$actual_price, 0, '', '') : '' }}',
                            get formatted() {
                                return this.rawValue.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                            },
                            onInput(e) {
                                let digits = e.target.value.replace(/[^0-9]/g, '');
                                this.rawValue = digits;
                                e.target.value = digits.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                                $wire.set('actual_price', parseInt(digits) || 0);
                            }
                        }"
                    >
                        <div class="flex items-center w-full md:justify-end">
                            <span class="text-3xl font-medium text-white opacity-70 mr-2">₱</span>
                            <input type="text" inputmode="numeric" id="actual_price"
                                   :value="formatted"
                                   @input="onInput($event)"
                                   class="w-full bg-transparent text-3xl font-bold text-white placeholder-white placeholder-opacity-80 border-0 focus:ring-0 p-0 text-right md:text-right"
                                   placeholder="{{ number_format((int)$predicted_price, 0, '.', ',') }}">
                        </div>
                        @error('actual_price')
                            <span class="absolute left-0 -bottom-6 text-xs text-red-300 mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                    <span class="block text-sm text-right mt-2 opacity-80 w-full">per month</span>
                </div>
            @endif

            </div>

            {{-- Divider --}}
            <hr class="border-white border-opacity-20 my-6">

            {{-- Bottom Section: Metrics --}}
            <div class="grid grid-cols-2 gap-4 text-center">
                <div>
                    <span class="block text-3xl font-bold">
                        {{ count(array_filter($model_amenities)) }}
                    </span>
                    <span class="text-sm opacity-80">Amenities</span>
                </div>
                <div>
                    <span class="block text-3xl font-bold">{{ $room_cap }}</span>
                    <span class="text-sm opacity-80">Room Capacity</span>
                </div>
            </div>

        </div>
    </div>

    <div class="flex justify-between items-center mt-6">

        {{-- Previous Button --}}
        <button
            wire:click="previousStep"
            class="py-2.5 px-6 font-medium text-sm rounded-lg shadow-md transition-colors duration-200 text-gray-700 bg-gray-200 hover:bg-gray-300"
        >
            Previous
        </button>

        {{-- Save Button --}}
        @if ($predicted_price)
            <button
                wire:click="validateAndConfirm"
                class="py-2.5 px-6 font-medium text-sm text-white bg-[#070589] rounded-lg hover:bg-[#1511D6] transition-colors duration-200 shadow-md"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove wire:target="validateAndConfirm, saveUnit">Save Unit</span>
                <span wire:loading wire:target="validateAndConfirm, saveUnit">Processing...</span>
            </button>
        @endif
    </div>


    {{-- ================================================= --}}
    {{-- REUSABLE MODALS --}}
    {{-- ================================================= --}}

    {{-- 1. Publish Confirmation (Uses standard Confirm Modal) --}}
    <x-ui.modal-confirm
        name="publish-confirmation"
        title="Publish Unit?"
        description="Please review your listing one last time before it is published."
        confirmText="Save"
        cancelText="Cancel"
        confirmAction="saveUnit"
    />

    {{-- 2. Discard Confirmation (Uses new Cancel Modal) --}}
    <x-ui.modal-cancel
        name="discard-confirmation"
        route="{{ route('properties.index') }}"
    />

</div>
