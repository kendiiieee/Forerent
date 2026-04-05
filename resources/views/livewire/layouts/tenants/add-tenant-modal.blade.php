<div>
    @if($isOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm">
            <div class="relative w-full {{ $isTransfer ? 'max-w-4xl' : 'max-w-3xl' }} bg-gray-50 rounded-2xl shadow-xl overflow-hidden max-h-[95vh] flex flex-col">

                {{-- Header --}}
                <div class="bg-[#070589] text-white p-6 flex-shrink-0">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-xl font-bold uppercase">
                                @if($isTransfer) TRANSFER TENANT
                                @elseif($isEdit) EDIT TENANT
                                @else ADD NEW TENANT
                                @endif
                            </h2>
                            <p class="mt-1 text-sm text-blue-100">
                                @if($isTransfer) Move tenant from current assignment to a new bed or unit
                                @elseif($isEdit) Update tenant details and lease information
                                @else Fill in the details to add new tenant
                                @endif
                            </p>
                        </div>
                        <flux:tooltip :content="'Close tenant form without saving'" position="bottom">
                            <button
                                type="button"
                                x-on:click="$dispatch('open-modal', '{{ $isTransfer ? 'discard-transfer-confirmation' : ($isEdit ? 'discard-edit-confirmation' : 'discard-tenant-confirmation') }}')"
                                class="text-white hover:text-blue-200 transition-colors focus:outline-none"
                            >
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </flux:tooltip>
                    </div>

                    {{-- Stepper (only for add/edit mode) --}}
                    @if(!$isTransfer)
                        <div class="mt-5" x-data>
                            <div class="flex items-center justify-between">
                                @php
                                    $steps = [
                                        ['num' => 1, 'title' => 'Profile'],
                                        ['num' => 2, 'title' => 'Contact & ID'],
                                        ['num' => 3, 'title' => 'Rent Details'],
                                        ['num' => 4, 'title' => 'Payment'],
                                    ];
                                @endphp
                                @foreach($steps as $i => $step)
                                    <div class="flex items-center {{ $i < count($steps) - 1 ? 'flex-1' : '' }}">
                                        <button
                                            type="button"
                                            @click="{{ $step['num'] }} < $wire.currentStep && ($wire.currentStep = {{ $step['num'] }})"
                                            class="flex flex-col items-center group"
                                            :class="{ 'cursor-pointer': $wire.currentStep > {{ $step['num'] }}, 'cursor-default': $wire.currentStep <= {{ $step['num'] }} }"
                                        >
                                            <div
                                                class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold border-2 transition-all duration-200"
                                                :class="{
                                                    'bg-white text-[#070589] border-white shadow-lg shadow-white/20': $wire.currentStep === {{ $step['num'] }},
                                                    'bg-white/20 text-white border-white/40': $wire.currentStep > {{ $step['num'] }},
                                                    'bg-transparent text-blue-200 border-blue-300/30': $wire.currentStep < {{ $step['num'] }}
                                                }"
                                            >
                                                <template x-if="$wire.currentStep > {{ $step['num'] }}">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                </template>
                                                <template x-if="$wire.currentStep <= {{ $step['num'] }}">
                                                    <span>{{ $step['num'] }}</span>
                                                </template>
                                            </div>
                                            <span
                                                class="text-[11px] font-semibold mt-1.5 tracking-wide transition-all duration-200"
                                                :class="{
                                                    'text-white': $wire.currentStep === {{ $step['num'] }},
                                                    'text-blue-200': $wire.currentStep > {{ $step['num'] }},
                                                    'text-blue-300/50': $wire.currentStep < {{ $step['num'] }}
                                                }"
                                            >{{ $step['title'] }}</span>
                                        </button>
                                        @if($i < count($steps) - 1)
                                            <div class="flex-1 mx-2 mt-[-14px]">
                                                <div class="h-0.5 rounded-full bg-blue-300/20 relative overflow-hidden">
                                                    <div
                                                        class="absolute inset-y-0 left-0 bg-white/60 rounded-full transition-all duration-300 ease-out"
                                                        :style="'width: ' + ($wire.currentStep > {{ $step['num'] }} ? '100%' : '0%')"
                                                    ></div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Scrollable Content --}}
                <div
                    class="flex-1 overflow-y-auto custom-scrollbar"
                    x-data
                    x-on:scroll-to-error.window="
                        $nextTick(() => {
                            const firstError = $el.querySelector('.text-red-500, .text-xs.text-red-500');
                            if (firstError) {
                                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }
                        })
                    "
                >

                    @if($isTransfer)
                        {{-- ═══════════════════════════════════════════════
                             TRANSFER MODE LAYOUT (unchanged structure)
                        ═══════════════════════════════════════════════ --}}
                        <div class="p-6 space-y-5">

                            {{-- Tenant Info Banner --}}
                            <div class="bg-white rounded-xl border border-gray-200 p-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-14 h-14 rounded-full overflow-hidden border-3 border-[#070589] flex-shrink-0">
                                        @if($existingProfileImg)
                                            <img src="{{ asset('storage/' . $existingProfileImg) }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full bg-[#070589] flex items-center justify-center">
                                                <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-base font-bold text-[#0C0B50]">{{ $firstName }} {{ $lastName }}</h3>
                                        <div class="flex items-center gap-4 mt-1">
                                            <span class="text-xs text-gray-500">{{ $email }}</span>
                                            @if($phoneNumber)
                                                <span class="text-xs text-gray-500">+63 {{ $phoneNumber }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    @if($gender)
                                        <span class="text-[11px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full {{ $gender === 'Male' ? 'bg-blue-50 text-blue-600' : 'bg-pink-50 text-pink-600' }}">
                                            {{ $gender }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Side-by-side: Old vs New --}}
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

                                {{-- LEFT: Current (read-only) --}}
                                <div class="bg-white rounded-xl border border-gray-200 p-5">
                                    <div class="flex items-center gap-2 mb-4">
                                        <div class="w-7 h-7 rounded-lg bg-gray-100 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-bold text-gray-800">Current Assignment</h4>
                                            <p class="text-[11px] text-gray-400">This lease will be closed upon transfer</p>
                                        </div>
                                    </div>
                                    <div class="space-y-3">
                                        <div class="grid grid-cols-2 gap-3">
                                            <div><p class="text-[11px] text-gray-400 uppercase tracking-wider font-medium">Building</p><p class="text-sm text-gray-700 font-medium mt-0.5">{{ $currentBuilding }}</p></div>
                                            <div><p class="text-[11px] text-gray-400 uppercase tracking-wider font-medium">Unit</p><p class="text-sm text-gray-700 font-medium mt-0.5">{{ $currentUnit }}</p></div>
                                            <div><p class="text-[11px] text-gray-400 uppercase tracking-wider font-medium">Bed</p><p class="text-sm text-gray-700 font-medium mt-0.5">{{ $currentBed }}</p></div>
                                            <div><p class="text-[11px] text-gray-400 uppercase tracking-wider font-medium">Dorm Type</p><p class="text-sm text-gray-700 font-medium mt-0.5">{{ $currentDormType }}</p></div>
                                        </div>
                                        <div class="border-t border-gray-100 pt-3">
                                            <div class="grid grid-cols-2 gap-3">
                                                <div><p class="text-[11px] text-gray-400 uppercase tracking-wider font-medium">Term</p><p class="text-sm text-gray-700 font-medium mt-0.5">{{ $currentTerm }}</p></div>
                                                <div><p class="text-[11px] text-gray-400 uppercase tracking-wider font-medium">Shift</p><p class="text-sm text-gray-700 font-medium mt-0.5">{{ $currentShift }}</p></div>
                                                <div><p class="text-[11px] text-gray-400 uppercase tracking-wider font-medium">Move-In Date</p><p class="text-sm text-gray-700 font-medium mt-0.5">{{ $currentStartDate }}</p></div>
                                                <div><p class="text-[11px] text-gray-400 uppercase tracking-wider font-medium">End Date</p><p class="text-sm text-gray-700 font-medium mt-0.5">{{ $currentEndDate }}</p></div>
                                            </div>
                                        </div>
                                        <div class="border-t border-gray-100 pt-3">
                                            <div class="grid grid-cols-2 gap-3">
                                                <div><p class="text-[11px] text-gray-400 uppercase tracking-wider font-medium">Monthly Rate</p><p class="text-sm text-gray-700 font-bold mt-0.5">{{ $currentRate }}</p></div>
                                                <div><p class="text-[11px] text-gray-400 uppercase tracking-wider font-medium">Auto Renew</p><p class="text-sm mt-0.5">@if($currentAutoRenew)<span class="text-green-600 font-medium">Yes</span>@else<span class="text-gray-500 font-medium">No</span>@endif</p></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- RIGHT: New Assignment (editable) --}}
                                <div class="bg-white rounded-xl border border-gray-200 p-5">
                                    <div class="flex items-center gap-2 mb-4">
                                        <div class="w-7 h-7 rounded-lg bg-[#2360E8]/10 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-[#2360E8]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-bold text-[#070589]">New Assignment</h4>
                                            <p class="text-[11px] text-gray-400">Select the new location and lease terms</p>
                                        </div>
                                    </div>
                                    <div class="space-y-3">
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="text-[11px] text-gray-500 uppercase tracking-wider font-semibold">Building</label>
                                                <select wire:model.live="selectedBuilding" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-[#2360E8] focus:ring-[#2360E8]">
                                                    <option value="">Select Building</option>
                                                    @foreach($buildings as $b)<option value="{{ $b->property_id }}">{{ $b->building_name }}</option>@endforeach
                                                </select>
                                                @error('selectedBuilding') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                            </div>
                                            <div>
                                                <label class="text-[11px] text-gray-500 uppercase tracking-wider font-semibold">Unit</label>
                                                <select wire:model.live="selectedUnit" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-[#2360E8] focus:ring-[#2360E8]" {{ empty($units) ? 'disabled' : '' }}>
                                                    <option value="">Select Unit</option>
                                                    @foreach($units as $u)<option value="{{ $u->unit_id }}">{{ $u->unit_number }}</option>@endforeach
                                                </select>
                                                @error('selectedUnit') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                            </div>
                                            <div>
                                                <label class="text-[11px] text-gray-500 uppercase tracking-wider font-semibold">Bed</label>
                                                <select wire:model.live="selectedBed" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-[#2360E8] focus:ring-[#2360E8]" {{ empty($beds) ? 'disabled' : '' }}>
                                                    <option value="">Select Bed</option>
                                                    @foreach($beds as $bed)<option value="{{ $bed->bed_id }}">{{ $bed->bed_number }}</option>@endforeach
                                                </select>
                                                @error('selectedBed') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                            </div>
                                            <div>
                                                <label class="text-[11px] text-gray-500 uppercase tracking-wider font-semibold">Dorm Type</label>
                                                <select wire:model="dormType" disabled class="w-full mt-1 border-gray-300 rounded-lg text-sm bg-gray-50">
                                                    <option value="">—</option>
                                                    <option value="Female">All Female</option>
                                                    <option value="Male">All Male</option>
                                                    <option value="Co-ed">Co-ed</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="border-t border-gray-100 pt-3">
                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <label class="text-[11px] text-gray-500 uppercase tracking-wider font-semibold">Term</label>
                                                    <select wire:model.live="term" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-[#2360E8] focus:ring-[#2360E8]">
                                                        <option value="">Select Term</option>
                                                        <option value="1">1 Month</option>
                                                        <option value="3">3 Months</option>
                                                        <option value="6">6 Months</option>
                                                        <option value="12">1 Year</option>
                                                    </select>
                                                    @error('term') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                                </div>
                                                <div>
                                                    <label class="text-[11px] text-gray-500 uppercase tracking-wider font-semibold">Shift</label>
                                                    <select wire:model="shift" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-[#2360E8] focus:ring-[#2360E8]">
                                                        <option value="">Select Shift</option>
                                                        <option value="Morning">Day</option>
                                                        <option value="Night">Night</option>
                                                    </select>
                                                    @error('shift') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                                </div>
                                                <div class="col-span-2">
                                                    <label class="text-[11px] text-gray-500 uppercase tracking-wider font-semibold">Move-In Date</label>
                                                    <input wire:model="startDate" type="date" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-[#2360E8] focus:ring-[#2360E8]">
                                                    @error('startDate') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                                </div>
                                                <div class="col-span-2 flex items-center gap-2">
                                                    <input wire:model="autoRenew" type="checkbox" class="rounded border-gray-300 text-[#2360E8] focus:ring-[#2360E8]">
                                                    <label class="text-xs font-medium text-gray-600">Auto Renew Contract</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- New Payment Details --}}
                            <div class="bg-white rounded-xl border border-gray-200 p-5">
                                <div class="flex items-center gap-2 mb-4">
                                    <div class="w-7 h-7 rounded-lg bg-[#2360E8]/10 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-[#2360E8]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-[#070589]">Payment Details</h4>
                                        <p class="text-[11px] text-gray-400">Financial details for the new assignment</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                                    <div>
                                        <label class="text-[11px] text-gray-500 uppercase tracking-wider font-semibold">Monthly Rate</label>
                                        <div class="relative mt-1"><div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><span class="text-gray-400 text-xs">₱</span></div><input wire:model="monthlyRate" type="number" class="w-full pl-8 border-gray-300 rounded-lg text-sm focus:border-[#2360E8] focus:ring-[#2360E8]"></div>
                                        @error('monthlyRate') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="text-[11px] text-gray-500 uppercase tracking-wider font-semibold">Security Deposit</label>
                                        <div class="relative mt-1"><div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><span class="text-gray-400 text-xs">₱</span></div><input wire:model="securityDeposit" type="number" class="w-full pl-8 border-gray-300 rounded-lg text-sm focus:border-[#2360E8] focus:ring-[#2360E8]"></div>
                                        @error('securityDeposit') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="text-[11px] text-gray-500 uppercase tracking-wider font-semibold">Monthly Due Date</label>
                                        <select wire:model="monthlyDueDate" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-[#2360E8] focus:ring-[#2360E8]">
                                            <option value="">Select Day</option>
                                            <option value="1">1st of the month</option>
                                            <option value="5">5th of the month</option>
                                            <option value="15">15th of the month</option>
                                        </select>
                                        @error('monthlyDueDate') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="text-[11px] text-gray-500 uppercase tracking-wider font-semibold">Payment Status</label>
                                        <select wire:model="paymentStatus" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-[#2360E8] focus:ring-[#2360E8]">
                                            <option value="">Select Status</option>
                                            <option value="Paid">Paid</option>
                                            <option value="Unpaid">Unpaid</option>
                                        </select>
                                        @error('paymentStatus') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                    @else
                        {{-- ═══════════════════════════════════════════════
                             ADD / EDIT MODE — STEPPER LAYOUT
                        ═══════════════════════════════════════════════ --}}
                        <div class="bg-white rounded-xl shadow-lg border border-gray-200 mx-6 my-6 p-8">
                            {{-- STEP 1: Profile Information --}}
                            <div x-show="$wire.currentStep === 1" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak>
                                <h3 class="text-base font-bold text-[#070589] mb-4">Profile Information</h3>
                                <div class="flex items-start gap-6 mb-6">
                                    <div class="flex-shrink-0" x-data="{ uploading: false, progress: 0 }"
                                         x-on:livewire-upload-start="uploading = true; progress = 0"
                                         x-on:livewire-upload-finish="uploading = false; progress = 100"
                                         x-on:livewire-upload-cancel="uploading = false"
                                         x-on:livewire-upload-error="uploading = false"
                                         x-on:livewire-upload-progress="progress = $event.detail.progress"
                                    >
                                        <label class="cursor-pointer group relative block w-24 h-24">
                                            @if ($profilePicture)
                                                <img src="{{ $profilePicture->temporaryUrl() }}" class="w-full h-full rounded-full object-cover border-4 border-[#001B5E]">
                                            @elseif ($existingProfileImg)
                                                <img src="{{ asset('storage/' . $existingProfileImg) }}" class="w-full h-full rounded-full object-cover border-4 border-[#001B5E]">
                                            @else
                                                <div class="w-full h-full rounded-full bg-[#001B5E] flex items-center justify-center">
                                                    <svg class="w-12 h-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                                                </div>
                                            @endif
                                            {{-- Upload overlay --}}
                                            <div x-show="uploading" x-cloak class="absolute inset-0 rounded-full bg-black/50 flex items-center justify-center">
                                                <span class="text-white text-xs font-bold" x-text="progress + '%'"></span>
                                            </div>
                                            <div class="absolute bottom-0 right-0 bg-white rounded-full p-1 border shadow">
                                                <svg class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                            </div>
                                            <input type="file" wire:model="profilePicture" class="hidden" accept="image/*">
                                        </label>
                                        {{-- Progress bar --}}
                                        <div x-show="uploading" x-cloak class="mt-2 w-24">
                                            <div class="h-1 bg-gray-200 rounded-full overflow-hidden">
                                                <div class="h-full bg-[#2360E8] rounded-full transition-all duration-200" :style="'width: ' + progress + '%'"></div>
                                            </div>
                                            <p class="text-[11px] text-[#2360E8] text-center mt-0.5 font-medium">Uploading...</p>
                                        </div>
                                        <div x-show="!uploading" class="mt-2 text-center">
                                            <p class="text-xs font-bold text-[#001B5E]">Profile Picture</p>
                                            <p class="text-[11px] text-gray-500">This will be displayed on your profile</p>
                                        </div>
                                    </div>
                                    <div class="flex-1 grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="text-xs font-semibold text-gray-700">First Name</label>
                                            <input wire:model="firstName" type="text" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                                            @error('firstName') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="text-xs font-semibold text-gray-700">Last Name</label>
                                            <input wire:model="lastName" type="text" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                                            @error('lastName') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="col-span-2">
                                            <label class="text-xs font-semibold text-gray-700">Gender</label>
                                            <select wire:model="gender" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                                                <option value="">Select Gender</option>
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                            </select>
                                            @error('gender') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- STEP 2: Contact & Personal Details --}}
                            <div x-show="$wire.currentStep === 2" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak>
                                <h3 class="text-base font-bold text-[#070589] mb-4">Contact Information</h3>
                                <div class="grid grid-cols-2 gap-4 mb-6">
                                    <div>
                                        <label class="text-xs font-semibold text-gray-700">Phone Number</label>
                                        <div class="relative mt-1">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><span class="text-gray-500 sm:text-sm">+63</span></div>
                                            <input wire:model="phoneNumber" type="text" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="w-full pl-12 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Enter 10 digit number">
                                        </div>
                                        @error('phoneNumber') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-gray-700">Email Address</label>
                                        <input wire:model="email" type="email" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                                        <p class="text-[11px] text-gray-500 mt-1">Login credentials will be sent to this email</p>
                                        @error('email') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-span-2">
                                        <label class="text-xs font-semibold text-gray-700">Permanent Home Address</label>
                                        <input wire:model="permanentAddress" type="text" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Full permanent address">
                                        @error('permanentAddress') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <h3 class="text-base font-bold text-[#070589] mb-4">Government ID</h3>
                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="text-xs font-semibold text-gray-700">ID Type</label>
                                        <select wire:model.live="governmentIdType" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="">Select ID Type</option>
                                            <option value="Passport">Passport</option>
                                            <option value="Driver's License">Driver's License</option>
                                            <option value="UMID">UMID</option>
                                            <option value="National ID">National ID</option>
                                            <option value="Postal ID">Postal ID</option>
                                            <option value="Other">Other</option>
                                        </select>
                                        @error('governmentIdType') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                    @if($governmentIdType === 'Other')
                                        <div>
                                            <label class="text-xs font-semibold text-gray-700">Specify ID Type</label>
                                            <input wire:model="governmentIdTypeOther" type="text" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g. PhilHealth ID, SSS ID">
                                            @error('governmentIdTypeOther') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                        </div>
                                    @endif
                                    <div>
                                        <label class="text-xs font-semibold text-gray-700">ID Number</label>
                                        <input wire:model="governmentIdNumber" type="text" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Enter ID number">
                                        @error('governmentIdNumber') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="mb-6"
                                     x-data="{ idUploading: false, idProgress: 0 }"
                                     x-on:livewire-upload-start="idUploading = true; idProgress = 0"
                                     x-on:livewire-upload-finish="idUploading = false; idProgress = 100"
                                     x-on:livewire-upload-cancel="idUploading = false"
                                     x-on:livewire-upload-error="idUploading = false"
                                     x-on:livewire-upload-progress="idProgress = $event.detail.progress"
                                >
                                    <label class="text-xs font-semibold text-gray-700">Upload Valid ID</label>
                                    <div class="mt-1">
                                        @if ($governmentIdImage)
                                            <div class="relative inline-block w-full">
                                                <img src="{{ $governmentIdImage->temporaryUrl() }}" class="w-full max-h-40 object-contain rounded-lg border border-gray-200">
                                                <flux:tooltip :content="'Remove this ID document'" position="bottom">
                                                    <button type="button" wire:click="$set('governmentIdImage', null)" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold hover:bg-red-600 shadow-md border-2 border-white z-10">&times;</button>
                                                </flux:tooltip>
                                            </div>
                                        @elseif ($existingGovernmentIdImage)
                                            <div class="relative inline-block w-full">
                                                <img src="{{ asset('storage/' . $existingGovernmentIdImage) }}" class="w-full max-h-40 object-contain rounded-lg border border-gray-200">
                                                <flux:tooltip :content="'Remove this ID document'" position="bottom">
                                                    <button type="button" wire:click="$set('existingGovernmentIdImage', null)" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold hover:bg-red-600 shadow-md border-2 border-white z-10">&times;</button>
                                                </flux:tooltip>
                                            </div>
                                        @endif

                                        {{-- Upload progress bar --}}
                                        <div x-show="idUploading" x-cloak class="mt-2">
                                            <div class="h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                                <div class="h-full bg-[#2360E8] rounded-full transition-all duration-200" :style="'width: ' + idProgress + '%'"></div>
                                            </div>
                                            <p class="text-[11px] text-[#2360E8] mt-0.5 font-medium">Uploading... <span x-text="idProgress + '%'"></span></p>
                                        </div>

                                        <label class="mt-2 flex items-center gap-2 cursor-pointer text-sm text-[#2360E8] hover:text-[#070589] font-medium" x-show="!idUploading">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                                            {{ $governmentIdImage || $existingGovernmentIdImage ? 'Change ID photo' : 'Upload ID photo' }}
                                            <input type="file" wire:model="governmentIdImage" class="hidden" accept="image/*">
                                        </label>
                                        <p class="text-[11px] text-gray-400 mt-1" x-show="!idUploading">Photo or scan of your valid government ID (max 10MB)</p>
                                        @error('governmentIdImage') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <h3 class="text-base font-bold text-[#070589] mb-4">Company / School</h3>
                                <div class="grid grid-cols-2 gap-4 mb-6">
                                    <div>
                                        <label class="text-xs font-semibold text-gray-700">Company / School</label>
                                        <input wire:model="companySchool" type="text" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Company or school name">
                                        @error('companySchool') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-gray-700">Position / Course</label>
                                        <input wire:model="positionCourse" type="text" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Job title or course">
                                        @error('positionCourse') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <h3 class="text-base font-bold text-[#070589] mb-4">Emergency Contact</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-xs font-semibold text-gray-700">Full Name</label>
                                        <input wire:model="emergencyContactName" type="text" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Emergency contact name">
                                        @error('emergencyContactName') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-gray-700">Relationship</label>
                                        <select wire:model.live="emergencyContactRelationship" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="">Select Relationship</option>
                                            <option value="Parent">Parent</option>
                                            <option value="Sibling">Sibling</option>
                                            <option value="Spouse">Spouse</option>
                                            <option value="Friend">Friend</option>
                                            <option value="Guardian">Guardian</option>
                                            <option value="Other">Other</option>
                                        </select>
                                        @error('emergencyContactRelationship') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                    @if($emergencyContactRelationship === 'Other')
                                        <div class="col-span-2">
                                            <label class="text-xs font-semibold text-gray-700">Specify Relationship</label>
                                            <input wire:model="emergencyContactRelationshipOther" type="text" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g. Uncle, Cousin, Colleague">
                                            @error('emergencyContactRelationshipOther') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                        </div>
                                    @endif
                                    <div class="col-span-2">
                                        <label class="text-xs font-semibold text-gray-700">Contact Number</label>
                                        <div class="relative mt-1">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><span class="text-gray-500 sm:text-sm">+63</span></div>
                                            <input wire:model="emergencyContactNumber" type="text" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="w-full pl-12 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Enter 10 digit number">
                                        </div>
                                        @error('emergencyContactNumber') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- STEP 3: Rent Details --}}
                            <div x-show="$wire.currentStep === 3" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak>
                                <h3 class="text-base font-bold text-[#070589] mb-4">Rent Details</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-xs font-semibold text-gray-700">Building Name</label>
                                        <select wire:model.live="selectedBuilding" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="">Select Building</option>
                                            @foreach($buildings as $b)<option value="{{ $b->property_id }}">{{ $b->building_name }}</option>@endforeach
                                        </select>
                                        @error('selectedBuilding') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-gray-700">Unit Number</label>
                                        <select wire:model.live="selectedUnit" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500" {{ empty($units) ? 'disabled' : '' }}>
                                            <option value="">Select Unit</option>
                                            @foreach($units as $u)<option value="{{ $u->unit_id }}">{{ $u->unit_number }}</option>@endforeach
                                        </select>
                                        @error('selectedUnit') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-gray-700">Bed Number</label>
                                        <select wire:model.live="selectedBed" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500" {{ empty($beds) ? 'disabled' : '' }}>
                                            <option value="">Select Bed</option>
                                            @foreach($beds as $bed)<option value="{{ $bed->bed_id }}">{{ $bed->bed_number }}</option>@endforeach
                                        </select>
                                        @error('selectedBed') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-gray-700">Dorm Type</label>
                                        <select wire:model="dormType" disabled class="w-full mt-1 border-gray-300 rounded-lg text-sm bg-gray-50">
                                            <option value="">Select Type</option>
                                            <option value="Female">All Female</option>
                                            <option value="Male">All Male</option>
                                            <option value="Co-ed">Co-ed</option>
                                        </select>
                                        @error('dormType') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-gray-700">Term</label>
                                        <select wire:model.live="term" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="">Select Term</option>
                                            <option value="1">1 Month</option>
                                            <option value="3">3 Months</option>
                                            <option value="6">6 Months</option>
                                            <option value="12">1 Year</option>
                                        </select>
                                        @error('term') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                        @if($shortTermPremium > 0)
                                            <div class="mt-2 bg-amber-50 rounded-lg p-2.5 border border-amber-200">
                                                <p class="text-xs font-semibold text-amber-700">Short-Term Premium</p>
                                                <p class="text-sm font-bold text-amber-600 mt-0.5">+ ₱ 500.00 / month</p>
                                                <p class="text-[11px] text-amber-500 mt-0.5">Applied — term is under 6 months</p>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-gray-700">Shift</label>
                                        <select wire:model="shift" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="">Select Shift</option>
                                            <option value="Morning">Day</option>
                                            <option value="Night">Night</option>
                                        </select>
                                        @error('shift') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-span-2">
                                        <label class="text-xs font-semibold text-gray-700">Move-In Date</label>
                                        <input wire:model="startDate" type="date" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error('startDate') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-span-2 flex items-center gap-2">
                                        <input wire:model="autoRenew" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <label class="text-xs font-semibold text-gray-700">Auto Renew Contract</label>
                                    </div>
                                </div>
                            </div>

                            {{-- STEP 4: Payment Details --}}
                            <div x-show="$wire.currentStep === 4" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak>
                                <h3 class="text-base font-bold text-[#070589] mb-4">Payment Details</h3>
                                <div class="grid grid-cols-2 gap-4 mb-6">
                                    <div>
                                        <label class="text-xs font-semibold text-gray-700">Monthly Rate <span class="font-normal text-gray-400">(1 Month Advance)</span></label>
                                        <div class="relative mt-1"><div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><span class="text-gray-500 text-xs">₱</span></div><input wire:model="monthlyRate" type="number" class="w-full pl-8 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500"></div>
                                        @error('monthlyRate') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-gray-700">Security Deposit <span class="font-normal text-gray-400">(1 Month Deposit)</span></label>
                                        <div class="relative mt-1"><div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><span class="text-gray-500 text-xs">₱</span></div><input wire:model="securityDeposit" type="number" class="w-full pl-8 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500"></div>
                                        @error('securityDeposit') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-gray-700">Monthly Due Date</label>
                                        <select wire:model="monthlyDueDate" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="">Select Day</option>
                                            <option value="1">1st of the month</option>
                                            <option value="5">5th of the month</option>
                                            <option value="15">15th of the month</option>
                                        </select>
                                        <p class="text-[11px] text-gray-400 mt-0.5">The day of the month rent is due</p>
                                        @error('monthlyDueDate') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-gray-700">Payment Status</label>
                                        <select wire:model="paymentStatus" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="">Select Status</option>
                                            <option value="Paid">Paid</option>
                                            <option value="Unpaid">Unpaid</option>
                                        </select>
                                        @error('paymentStatus') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                {{-- Total Move-In Cost Summary --}}
                                @if($monthlyRate && $securityDeposit)
                                    <div class="bg-[#EEF2FF] rounded-xl p-4 border border-blue-100 mb-6">
                                        <h4 class="text-xs font-bold text-[#070589] uppercase tracking-wide mb-3">Total Move-In Cost</h4>
                                        <div class="space-y-1.5 text-sm">
                                            <div class="flex justify-between"><span class="text-gray-600">1 Month Advance</span><span class="font-semibold">&#8369; {{ number_format($monthlyRate, 2) }}</span></div>
                                            <div class="flex justify-between"><span class="text-gray-600">Security Deposit</span><span class="font-semibold">&#8369; {{ number_format($securityDeposit, 2) }}</span></div>
                                            <div class="border-t border-blue-200 pt-1.5 flex justify-between font-bold text-[#070589]">
                                                <span>TOTAL DUE</span>
                                                <span>&#8369; {{ number_format($monthlyRate + $securityDeposit, 2) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <h3 class="text-base font-bold text-[#070589] mb-4">Fixed Contract Terms</h3>
                                <div class="grid grid-cols-3 gap-3">
                                    <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                        <p class="text-xs font-semibold text-gray-700">Late Payment Penalty</p>
                                        <p class="text-sm font-bold text-[#070589] mt-1">1% of monthly rent / day</p>
                                        <p class="text-[11px] text-gray-400 mt-0.5">Auto-computed on overdue payments</p>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                        <p class="text-xs font-semibold text-gray-700">Early Termination Policy</p>
                                        <p class="text-xs text-gray-600 mt-1">Deposit is <span class="font-bold text-red-600">forfeited in full</span> if tenant moves out before lease end. No additional fee.</p>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                        <p class="text-xs font-semibold text-gray-700">Reservation Policy</p>
                                        <p class="text-xs text-gray-600 mt-1">No reservation fee. Slot held for <span class="font-bold">3 calendar days</span>. Auto-released if unpaid.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>

                {{-- Footer --}}
                <div class="p-6 bg-white border-t border-gray-200 flex justify-between">
                    @if(!$isTransfer)
                        {{-- Back button: pure Alpine, instant --}}
                        <button
                            type="button"
                            x-on:click="if ($wire.currentStep > 1) $wire.currentStep--"
                            x-show="$wire.currentStep > 1"
                            x-cloak
                            class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3 px-8 rounded-xl text-sm transition-colors"
                        >
                            Back
                        </button>
                        <div x-show="$wire.currentStep <= 1"></div>

                        {{-- Next button: server validation --}}
                        <button
                            type="button"
                            wire:click="nextStep"
                            x-show="$wire.currentStep < {{ $totalSteps }}"
                            x-cloak
                            class="bg-[#070589] hover:bg-[#000060] text-white font-bold py-3 px-10 rounded-xl text-sm transition-colors shadow-lg"
                        >
                            Next
                        </button>

                        {{-- Save button: last step --}}
                        <button
                            type="button"
                            wire:click="validateAndConfirm"
                            x-show="$wire.currentStep >= {{ $totalSteps }}"
                            x-cloak
                            class="bg-[#070589] hover:bg-[#000060] text-white font-bold py-3 px-10 rounded-xl text-sm transition-colors shadow-lg"
                        >
                            @if($isEdit) Update Tenant @else Save Tenant @endif
                        </button>
                    @else
                        <div></div>
                        <button
                            type="button"
                            wire:click="validateAndConfirm"
                            class="bg-[#070589] hover:bg-[#000060] text-white font-bold py-3 px-10 rounded-xl text-sm transition-colors shadow-lg"
                        >
                            Transfer Tenant
                        </button>
                    @endif
                </div>

            </div>
        </div>

        {{-- Confirmation Modals --}}
        <x-ui.modal-confirm name="save-tenant-confirmation" title="Save Tenant?" description="Are you sure you want to save this tenant's information?" confirmText="Yes, Save" cancelText="Cancel" confirmAction="save" />
        <x-ui.modal-confirm name="transfer-tenant-confirmation" title="Transfer Tenant?" description="Are you sure you want to transfer this tenant? Their current lease will be closed and a new one will be created." confirmText="Yes, Transfer" cancelText="Cancel" confirmAction="save" />
        <x-ui.modal-cancel name="discard-tenant-confirmation" title="Discard Unsaved Changes?" description="Are you sure you want to close? All details will be lost." discardText="Discard" returnText="Keep Editing" discardAction="close" />
        <x-ui.modal-cancel name="discard-transfer-confirmation" title="Discard Transfer?" description="Are you sure you want to close? All changes will be lost." discardText="Discard" returnText="Keep Editing" discardAction="close" />
        <x-ui.modal-confirm name="edit-tenant-confirmation" title="Update Tenant?" description="Are you sure you want to save the changes to this tenant's information?" confirmText="Yes, Update" cancelText="Cancel" confirmAction="save" />
        <x-ui.modal-cancel name="discard-edit-confirmation" title="Discard Changes?" description="Are you sure you want to close? All unsaved changes will be lost." discardText="Discard" returnText="Keep Editing" discardAction="close" />

    @endif
</div>
