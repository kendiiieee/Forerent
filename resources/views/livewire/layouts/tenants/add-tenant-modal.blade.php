<div>
    @if($isOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm">
            <div class="relative w-full {{ $isTransfer ? 'max-w-4xl' : 'max-w-3xl' }} bg-gray-50 rounded-2xl shadow-xl overflow-hidden max-h-[95vh] flex flex-col">

                {{-- Header --}}
                <div class="bg-[#070589] text-white p-6 flex-shrink-0">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-xl font-bold uppercase">
                                {{ $isTransfer ? 'TRANSFER TENANT' : 'ADD NEW TENANT' }}
                            </h2>
                            <p class="mt-1 text-sm text-blue-100">
                                {{ $isTransfer ? 'Move tenant from current assignment to a new bed or unit' : 'Fill in the details to add new tenant' }}
                            </p>
                        </div>
                        <button
                            type="button"
                            x-on:click="$dispatch('open-modal', '{{ $isTransfer ? 'discard-transfer-confirmation' : 'discard-tenant-confirmation' }}')"
                            class="text-white hover:text-blue-200 transition-colors focus:outline-none"
                        >
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Scrollable Content --}}
                <div class="flex-1 overflow-y-auto custom-scrollbar">

                    @if($isTransfer)
                        {{-- ═══════════════════════════════════════════════
                             TRANSFER MODE LAYOUT
                        ═══════════════════════════════════════════════ --}}
                        <div class="p-6 space-y-5">

                            {{-- SECTION 1: Tenant Info — compact banner --}}
                            <div class="bg-white rounded-xl border border-gray-200 p-4">
                                <div class="flex items-center gap-4">
                                    {{-- Avatar --}}
                                    <div class="w-14 h-14 rounded-full overflow-hidden border-3 border-[#070589] flex-shrink-0">
                                        @if($existingProfileImg)
                                            <img src="{{ asset('storage/' . $existingProfileImg) }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full bg-[#070589] flex items-center justify-center">
                                                <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                                            </div>
                                        @endif
                                    </div>
                                    {{-- Name + contact --}}
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-base font-bold text-[#0C0B50]">{{ $firstName }} {{ $lastName }}</h3>
                                        <div class="flex items-center gap-4 mt-1">
                                            <span class="text-xs text-gray-500 flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                                {{ $email }}
                                            </span>
                                            @if($phoneNumber)
                                                <span class="text-xs text-gray-500 flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                                                    +63 {{ $phoneNumber }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    {{-- Gender badge --}}
                                    @if($gender)
                                        <span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full {{ $gender === 'Male' ? 'bg-blue-50 text-blue-600' : 'bg-pink-50 text-pink-600' }}">
                                            {{ $gender }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- SECTION 2: Side-by-side comparison — Old vs New --}}
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

                                {{-- LEFT: Current Rent Details (read-only) --}}
                                <div class="bg-white rounded-xl border border-gray-200 p-5">
                                    <div class="flex items-center gap-2 mb-4">
                                        <div class="w-7 h-7 rounded-lg bg-gray-100 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-bold text-gray-800">Current Assignment</h4>
                                            <p class="text-[10px] text-gray-400">This lease will be closed upon transfer</p>
                                        </div>
                                    </div>

                                    <div class="space-y-3">
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <p class="text-[10px] text-gray-400 uppercase tracking-wider font-medium">Building</p>
                                                <p class="text-sm text-gray-700 font-medium mt-0.5">{{ $currentBuilding }}</p>
                                            </div>
                                            <div>
                                                <p class="text-[10px] text-gray-400 uppercase tracking-wider font-medium">Unit</p>
                                                <p class="text-sm text-gray-700 font-medium mt-0.5">{{ $currentUnit }}</p>
                                            </div>
                                            <div>
                                                <p class="text-[10px] text-gray-400 uppercase tracking-wider font-medium">Bed</p>
                                                <p class="text-sm text-gray-700 font-medium mt-0.5">{{ $currentBed }}</p>
                                            </div>
                                            <div>
                                                <p class="text-[10px] text-gray-400 uppercase tracking-wider font-medium">Dorm Type</p>
                                                <p class="text-sm text-gray-700 font-medium mt-0.5">{{ $currentDormType }}</p>
                                            </div>
                                        </div>

                                        <div class="border-t border-gray-100 pt-3">
                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <p class="text-[10px] text-gray-400 uppercase tracking-wider font-medium">Term</p>
                                                    <p class="text-sm text-gray-700 font-medium mt-0.5">{{ $currentTerm }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-[10px] text-gray-400 uppercase tracking-wider font-medium">Shift</p>
                                                    <p class="text-sm text-gray-700 font-medium mt-0.5">{{ $currentShift }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-[10px] text-gray-400 uppercase tracking-wider font-medium">Start Date</p>
                                                    <p class="text-sm text-gray-700 font-medium mt-0.5">{{ $currentStartDate }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-[10px] text-gray-400 uppercase tracking-wider font-medium">End Date</p>
                                                    <p class="text-sm text-gray-700 font-medium mt-0.5">{{ $currentEndDate }}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="border-t border-gray-100 pt-3">
                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <p class="text-[10px] text-gray-400 uppercase tracking-wider font-medium">Monthly Rate</p>
                                                    <p class="text-sm text-gray-700 font-bold mt-0.5">{{ $currentRate }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-[10px] text-gray-400 uppercase tracking-wider font-medium">Auto Renew</p>
                                                    <p class="text-sm mt-0.5">
                                                        @if($currentAutoRenew)
                                                            <span class="text-green-600 font-medium">Yes</span>
                                                        @else
                                                            <span class="text-gray-500 font-medium">No</span>
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- RIGHT: New Rent Details (editable) --}}
                                <div class="bg-white rounded-xl border border-gray-200 p-5">
                                    <div class="flex items-center gap-2 mb-4">
                                        <div class="w-7 h-7 rounded-lg bg-[#2360E8]/10 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-[#2360E8]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-bold text-[#070589]">New Assignment</h4>
                                            <p class="text-[10px] text-gray-400">Select the new location and lease terms</p>
                                        </div>
                                    </div>

                                    <div class="space-y-3">
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold">Building</label>
                                                <select wire:model.live="selectedBuilding" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-[#2360E8] focus:ring-[#2360E8]">
                                                    <option value="">Select Building</option>
                                                    @foreach($buildings as $b)
                                                        <option value="{{ $b->property_id }}">{{ $b->building_name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('selectedBuilding') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                            </div>
                                            <div>
                                                <label class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold">Unit</label>
                                                <select wire:model.live="selectedUnit" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-[#2360E8] focus:ring-[#2360E8]" {{ empty($units) ? 'disabled' : '' }}>
                                                    <option value="">Select Unit</option>
                                                    @foreach($units as $u)
                                                        <option value="{{ $u->unit_id }}">{{ $u->unit_number }}</option>
                                                    @endforeach
                                                </select>
                                                @error('selectedUnit') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                            </div>
                                            <div>
                                                <label class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold">Bed</label>
                                                <select wire:model.live="selectedBed" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-[#2360E8] focus:ring-[#2360E8]" {{ empty($beds) ? 'disabled' : '' }}>
                                                    <option value="">Select Bed</option>
                                                    @foreach($beds as $bed)
                                                        <option value="{{ $bed->bed_id }}">{{ $bed->bed_number }}</option>
                                                    @endforeach
                                                </select>
                                                @error('selectedBed') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                            </div>
                                            <div>
                                                <label class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold">Dorm Type</label>
                                                <select wire:model="dormType" disabled class="w-full mt-1 border-gray-300 rounded-lg text-sm bg-gray-50">
                                                    <option value="">—</option>
                                                    <option value="Female">All Female</option>
                                                    <option value="Male">All Male</option>
                                                    <option value="Co-ed">Co-ed</option>
                                                </select>
                                                @error('dormType') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                            </div>
                                        </div>

                                        <div class="border-t border-gray-100 pt-3">
                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <label class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold">Term</label>
                                                    <select wire:model="term" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-[#2360E8] focus:ring-[#2360E8]">
                                                        <option value="">Select Term</option>
                                                        <option value="6">6 Months</option>
                                                        <option value="12">1 Year</option>
                                                    </select>
                                                    @error('term') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                                </div>
                                                <div>
                                                    <label class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold">Shift</label>
                                                    <select wire:model="shift" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-[#2360E8] focus:ring-[#2360E8]">
                                                        <option value="">Select Shift</option>
                                                        <option value="Morning">Day</option>
                                                        <option value="Night">Night</option>
                                                    </select>
                                                    @error('shift') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                                </div>
                                                <div class="col-span-2">
                                                    <label class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold">Start Date</label>
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

                            {{-- SECTION 3: New Move In Details --}}
                            <div class="bg-white rounded-xl border border-gray-200 p-5">
                                <div class="flex items-center gap-2 mb-4">
                                    <div class="w-7 h-7 rounded-lg bg-[#2360E8]/10 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-[#2360E8]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-[#070589]">New Move In Details</h4>
                                        <p class="text-[10px] text-gray-400">Financial and move-in details for the new assignment</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                                    <div>
                                        <label class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold">Move In Date</label>
                                        <input wire:model="moveInDate" type="date" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-[#2360E8] focus:ring-[#2360E8]">
                                        @error('moveInDate') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold">Monthly Rate</label>
                                        <div class="relative mt-1">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><span class="text-gray-400 text-xs">₱</span></div>
                                            <input wire:model="monthlyRate" type="number" class="w-full pl-8 border-gray-300 rounded-lg text-sm focus:border-[#2360E8] focus:ring-[#2360E8]">
                                        </div>
                                        @error('monthlyRate') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold">Security Deposit</label>
                                        <div class="relative mt-1">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><span class="text-gray-400 text-xs">₱</span></div>
                                            <input wire:model="securityDeposit" type="number" class="w-full pl-8 border-gray-300 rounded-lg text-sm focus:border-[#2360E8] focus:ring-[#2360E8]">
                                        </div>
                                        @error('securityDeposit') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold">Payment Status</label>
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
                             ADD MODE LAYOUT (unchanged)
                        ═══════════════════════════════════════════════ --}}
                        <div class="bg-white rounded-xl shadow-lg border border-gray-200 mx-6 my-6 p-8">
                            <form wire:submit.prevent="validateAndConfirm" class="space-y-8">

                                {{-- Profile Information --}}
                                <div>
                                    <h3 class="text-base font-bold text-[#070589] flex items-center gap-2 mb-4">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                        Profile Information
                                    </h3>
                                    <div class="flex items-start gap-6 mb-6">
                                        <div class="flex-shrink-0">
                                            <label class="cursor-pointer group relative block w-24 h-24">
                                                @if ($profilePicture)
                                                    <img src="{{ $profilePicture->temporaryUrl() }}" class="w-full h-full rounded-full object-cover border-4 border-[#001B5E]">
                                                @else
                                                    <div class="w-full h-full rounded-full bg-[#001B5E] flex items-center justify-center">
                                                        <svg class="w-12 h-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                                                    </div>
                                                @endif
                                                <div class="absolute bottom-0 right-0 bg-white rounded-full p-1 border shadow">
                                                    <svg class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                                </div>
                                                <input type="file" wire:model="profilePicture" class="hidden" accept="image/*">
                                            </label>
                                            <div class="mt-2 text-center">
                                                <p class="text-xs font-bold text-[#001B5E]">Profile Picture</p>
                                                <p class="text-[10px] text-gray-500">This will be displayed on your profile</p>
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

                                {{-- Contact Information --}}
                                <div>
                                    <h3 class="text-base font-bold text-[#070589] flex items-center gap-2 mb-4">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                        Contact Information
                                    </h3>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="text-xs font-semibold text-gray-700">Phone Number</label>
                                            <div class="relative mt-1">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><span class="text-gray-500 sm:text-sm">+63</span></div>
                                                <input wire:model="phoneNumber" type="text" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="w-full pl-12 border-gray-100 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Enter 10 digit number">
                                            </div>
                                            @error('phoneNumber') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="text-xs font-semibold text-gray-700">Email Address</label>
                                            <input wire:model="email" type="email" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                                            <p class="text-[10px] text-gray-500 mt-1">Login credentials will be sent to this email</p>
                                            @error('email') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- Rent Details --}}
                                <div>
                                    <h3 class="text-base font-bold text-[#070589] flex items-center gap-2 mb-4">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                        Rent Details
                                    </h3>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="text-xs font-semibold text-gray-700">Building Name</label>
                                            <select wire:model.live="selectedBuilding" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                                                <option value="">Select Building</option>
                                                @foreach($buildings as $b)
                                                    <option value="{{ $b->property_id }}">{{ $b->building_name }}</option>
                                                @endforeach
                                            </select>
                                            @error('selectedBuilding') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="text-xs font-semibold text-gray-700">Unit Number</label>
                                            <select wire:model.live="selectedUnit" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500" {{ empty($units) ? 'disabled' : '' }}>
                                                <option value="">Select Unit</option>
                                                @foreach($units as $u)
                                                    <option value="{{ $u->unit_id }}">{{ $u->unit_number }}</option>
                                                @endforeach
                                            </select>
                                            @error('selectedUnit') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="text-xs font-semibold text-gray-700">Bed Number</label>
                                            <select wire:model.live="selectedBed" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500" {{ empty($beds) ? 'disabled' : '' }}>
                                                <option value="">Select Bed</option>
                                                @foreach($beds as $bed)
                                                    <option value="{{ $bed->bed_id }}">{{ $bed->bed_number }}</option>
                                                @endforeach
                                            </select>
                                            @error('selectedBed') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="text-xs font-semibold text-gray-700">Dorm Type</label>
                                            <select wire:model="dormType" disabled class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                                                <option value="">Select Type</option>
                                                <option value="Female">All Female</option>
                                                <option value="Male">All Male</option>
                                                <option value="Co-ed">Co-ed</option>
                                            </select>
                                            @error('dormType') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="text-xs font-semibold text-gray-700">Term</label>
                                            <select wire:model="term" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                                                <option value="">Select Term</option>
                                                <option value="6">6 Months</option>
                                                <option value="12">1 Year</option>
                                            </select>
                                            @error('term') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
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
                                            <label class="text-xs font-semibold text-gray-700">Start Date</label>
                                            <input wire:model="startDate" type="date" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                                            @error('startDate') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="col-span-2 flex items-center gap-2">
                                            <input wire:model="autoRenew" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <label class="text-xs font-semibold text-gray-700">Auto Renew Contract</label>
                                        </div>
                                    </div>
                                </div>

                                {{-- Move In Details --}}
                                <div>
                                    <h3 class="text-base font-bold text-[#070589] flex items-center gap-2 mb-4">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        Move In Details
                                    </h3>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="text-xs font-semibold text-gray-700">Move In Date</label>
                                            <input wire:model="moveInDate" type="date" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                                            @error('moveInDate') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="text-xs font-semibold text-gray-700">Monthly Rate</label>
                                            <div class="relative mt-1">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><span class="text-gray-500 text-xs">₱</span></div>
                                                <input wire:model="monthlyRate" type="number" class="w-full pl-8 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                                            </div>
                                            @error('monthlyRate') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="text-xs font-semibold text-gray-700">Security Deposit</label>
                                            <div class="relative mt-1">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><span class="text-gray-500 text-xs">₱</span></div>
                                                <input wire:model="securityDeposit" type="number" class="w-full pl-8 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                                            </div>
                                            @error('securityDeposit') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
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
                                        <div class="col-span-2">
                                            <label class="text-xs font-semibold text-gray-700">Registration</label>
                                            <input wire:model="registration" type="text" class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="---">
                                        </div>
                                    </div>
                                </div>

                            </form>
                        </div>
                    @endif

                </div>

                {{-- Footer --}}
                <div class="p-6 bg-white border-t border-gray-200 flex justify-end">
                    <button
                        type="button"
                        wire:click="validateAndConfirm"
                        class="bg-[#070589] hover:bg-[#000060] text-white font-bold py-3 px-10 rounded-xl text-sm transition-colors shadow-lg"
                    >
                        {{ $isTransfer ? 'Transfer Tenant' : 'Save Tenant' }}
                    </button>
                </div>

            </div>
        </div>

        {{-- Confirmation Modals --}}
        <x-ui.modal-confirm name="save-tenant-confirmation" title="Save Tenant?" description="Are you sure you want to save this tenant's information?" confirmText="Yes, Save" cancelText="Cancel" confirmAction="save" />
        <x-ui.modal-confirm name="transfer-tenant-confirmation" title="Transfer Tenant?" description="Are you sure you want to transfer this tenant? Their current lease will be closed and a new one will be created." confirmText="Yes, Transfer" cancelText="Cancel" confirmAction="save" />
        <x-ui.modal-cancel name="discard-tenant-confirmation" title="Discard Unsaved Changes?" description="Are you sure you want to close? All details will be lost." discardText="Discard" returnText="Keep Editing" discardAction="close" />
        <x-ui.modal-cancel name="discard-transfer-confirmation" title="Discard Transfer?" description="Are you sure you want to close? All changes will be lost." discardText="Discard" returnText="Keep Editing" discardAction="close" />

    @endif
</div>
