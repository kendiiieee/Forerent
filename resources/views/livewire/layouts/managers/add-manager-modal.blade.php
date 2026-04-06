<div>
    @if($isOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm">
            <div class="relative w-full max-w-4xl bg-gray-50 rounded-2xl shadow-xl overflow-hidden max-h-[90vh] flex flex-col">

                <!-- Modal Header - Blue Background -->
                <div class="bg-[#070589] text-white p-6 flex-shrink-0">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-xl font-bold uppercase">
                                {{ $managerId ? 'EDIT MANAGER' : 'ADD NEW MANAGER' }}
                            </h2>
                            <p class="mt-1 text-sm text-blue-100">
                                {{ $managerId ? 'Update manager information and property assignments.' : 'Create an account for the new property manager.' }}
                            </p>
                        </div>
                        <flux:tooltip :content="'Close manager form without saving'" position="bottom">
                            <button
                                type="button"
                                x-on:click="$dispatch('open-modal', 'discard-manager-confirmation')"
                                class="text-white hover:text-blue-200 transition-colors focus:outline-none">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <span class="sr-only">Close modal</span>
                            </button>
                        </flux:tooltip>
                    </div>
                </div>

                <div
                    class="flex-1 overflow-y-auto"
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
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 mx-6 my-6">
                        <div class="space-y-6 p-6">

                            <div class="mb-6">
                                <div class="flex items-center gap-2 mb-4">
                                    <svg class="w-5 h-5 text-gray-700" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                    </svg>
                                    <h3 class="text-base font-semibold text-gray-900">Profile Information</h3>
                                </div>

                                <div class="flex items-start gap-6">
                                    <div class="flex flex-col items-center text-center flex-shrink-0">
                                        <label for="profilePicture-{{ $modalId }}" class="cursor-pointer relative group">
                                            @if ($profilePicture)
                                                @if(is_string($profilePicture))
                                                    <img src="{{ Storage::url($profilePicture) }}" alt="Profile Preview" class="w-24 h-24 rounded-full object-cover shadow-md border-4 border-[#001B5E]">
                                                @else
                                                    <img src="{{ $profilePicture->temporaryUrl() }}" alt="Profile Preview" class="w-24 h-24 rounded-full object-cover shadow-md border-4 border-[#001B5E]">
                                                @endif
                                            @else
                                                <div class="w-24 h-24 rounded-full bg-[#001B5E] flex items-center justify-center shadow-md">
                                                    <svg class="w-12 h-12 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                                    </svg>
                                                </div>
                                            @endif
                                            <div class="absolute -bottom-1 -right-1 w-7 h-7 bg-white rounded-full flex items-center justify-center shadow-md border border-gray-200 group-hover:bg-gray-50">
                                                <svg class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                                                </svg>
                                            </div>
                                        </label>
                                        <input
                                            type="file"
                                            wire:model="profilePicture"
                                            id="profilePicture-{{ $modalId }}"
                                            class="hidden"
                                            accept="image/*"
                                        >
                                        <span class="mt-2 font-medium text-xs text-gray-900">Profile Picture</span>
                                        <span class="text-xs text-gray-500">This will be displayed on your profile</span>
                                        @error('profilePicture')
                                        <span class="text-xs text-red-500 mt-1">{{ $message }}</span>
                                        @enderror

                                        <div wire:loading wire:target="profilePicture" class="mt-2 text-xs text-blue-600">
                                            Uploading...
                                        </div>
                                    </div>

                                    <div class="flex-1 flex flex-col gap-4">
                                        <div class="relative">
                                            <input
                                                wire:model.defer="userForm.firstName"
                                                type="text"
                                                id="firstName-{{ $modalId }}"
                                                class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-[#0030C5] peer"
                                                placeholder=" "
                                            />
                                            <label
                                                for="firstName-{{ $modalId }}"
                                                class="absolute text-sm text-gray-500 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white px-2 peer-focus:px-2 peer-focus:text-[#0030C5] peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 start-1"
                                            >
                                                First Name
                                            </label>
                                            @error('userForm.firstName')
                                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="relative">
                                            <input
                                                wire:model.defer="userForm.lastName"
                                                type="text"
                                                id="lastName-{{ $modalId }}"
                                                class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-[#0030C5] peer"
                                                placeholder=" "
                                            />
                                            <label
                                                for="lastName-{{ $modalId }}"
                                                class="absolute text-sm text-gray-500 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white px-2 peer-focus:px-2 peer-focus:text-[#0030C5] peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 start-1"
                                            >
                                                Last Name
                                            </label>
                                            @error('userForm.lastName')
                                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-6 border-gray-200 border-dashed">

                            <div class="mb-6">
                                <div class="flex items-center gap-2 mb-4">
                                    <svg class="w-5 h-5 text-gray-700" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                                    </svg>
                                    <h3 class="text-base font-semibold text-gray-900">Contact Information</h3>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="relative">
                                        <input
                                            wire:model.defer="userForm.phoneNumber"
                                            type="tel"
                                            id="phone-{{ $modalId }}"
                                            class="block pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-[#0030C5] pr-2.5 pl-16"
                                            placeholder="9876543210"
                                            maxlength="10"
                                            inputmode="numeric"
                                            pattern="[0-9]*"
                                        />

                                        <div class="absolute top-4 left-0 px-3 flex items-center space-x-2 pointer-events-none">
                                            <span class="text-sm text-gray-500">+63</span>
                                            <span class="border-l border-gray-300 h-5"></span>
                                        </div>

                                        <label
                                            for="phone-{{ $modalId }}"
                                            class="absolute text-sm text-gray-500 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white px-2 peer-focus:px-2 peer-focus:text-[#0030C5] peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 start-1">
                                            Phone Number
                                        </label>

                                        @error('userForm.phoneNumber')
                                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="relative">
                                        <input
                                            wire:model.defer="userForm.email"
                                            type="email"
                                            id="email-{{ $modalId }}"
                                            class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-[#0030C5] peer"
                                            placeholder=" "
                                        />
                                        <label
                                            for="email-{{ $modalId }}"
                                            class="absolute text-sm text-gray-500 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white px-2 peer-focus:px-2 peer-focus:text-[#0030C5] peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 start-1"
                                        >
                                            Email Address
                                        </label>
                                        @error('userForm.email')
                                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                        @else
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ $managerId ? 'Manager login email address.' : 'Login credentials will be sent to this email.' }}
                                            </p>
                                            @enderror
                                    </div>
                                </div>
                            </div>

                            <hr class="my-6 border-gray-200 border-dashed">

                            <div>
                                <div class="flex items-center gap-2 mb-4">
                                    <svg class="w-5 h-5 text-gray-700" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                                    </svg>
                                    <h3 class="text-base font-semibold text-gray-900">Assign Properties</h3>
                                </div>

                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div class="relative">
                                        <select
                                            wire:model.live="selectedBuilding"
                                            id="building-{{ $modalId }}"
                                            class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-[#0030C5] peer cursor-pointer"
                                        >
                                            <option value="" hidden>Select Building</option>
                                            @foreach($buildings as $building)
                                                <option value="{{ $building['property_id'] }}">{{ $building['building_name'] }}</option>
                                            @endforeach
                                        </select>
                                        <label
                                            for="building-{{ $modalId }}"
                                            class="absolute text-sm text-gray-500 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white px-2 peer-focus:px-2 peer-focus:text-[#0030C5] start-1"
                                        >
                                            Building Name
                                        </label>
                                        <svg class="absolute right-3 top-4 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                        @error('selectedBuilding')
                                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="relative">
                                        <select
                                            wire:model.live="selectedFloor"
                                            id="floor-{{ $modalId }}"
                                            class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-[#0030C5] peer cursor-pointer"
                                            @if(!$selectedBuilding) disabled @endif
                                        >
                                            <option value="">Select Floor</option>
                                            @foreach($floors as $floor)
                                                <option value="{{ $floor }}">{{ $floor }}</option>
                                            @endforeach
                                        </select>
                                        <label
                                            for="floor-{{ $modalId }}"
                                            class="absolute text-sm text-gray-500 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white px-2 peer-focus:px-2 peer-focus:text-[#0030C5] start-1"
                                        >
                                            Floor
                                        </label>
                                        <svg class="absolute right-3 top-4 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                        @error('selectedFloor')
                                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Unit Number</label>
                                    <div class="border border-gray-300 rounded-lg p-4 max-h-48 overflow-y-auto bg-white">
                                        @if(count($availableUnits) > 0)
                                            <div class="grid grid-cols-2 gap-3">
                                                @foreach($availableUnits as $unit)
                                                    <label class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                                        <input
                                                            type="checkbox"
                                                            wire:model.live="selectedUnits"
                                                            value="{{ $unit['id'] }}"
                                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                                                        />
                                                        <span class="text-sm text-gray-900">{{ $unit['number'] }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-sm text-gray-500 text-center py-4">
                                                @if($selectedBuilding && $selectedFloor)
                                                    No units available for this selection
                                                @else
                                                    Please select a building and floor
                                                @endif
                                            </p>
                                        @endif
                                    </div>
                                    @error('selectedUnits')
                                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="p-6 bg-white border-t border-gray-200 flex justify-end flex-shrink-0">
                    <button
                        type="button"
                        wire:click="validateAndConfirm"
                        class="px-8 py-2.5 bg-[#070589] text-white text-sm font-semibold rounded-lg hover:bg-[#001445] focus:ring-4 focus:ring-blue-300 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove wire:target="save">{{ $managerId ? 'Update Manager' : 'Save Manager' }}</span>
                        <span wire:loading wire:target="save">{{ $managerId ? 'Updating...' : 'Saving...' }}</span>
                    </button>
                </div>
            </div>
        </div>

        <x-ui.modal-confirm
            name="save-manager-confirmation"
            title="Save Manager?"
            description="Are you sure you want to save this manager's information?"
            confirmText="Yes, Save"
            cancelText="Cancel"
            confirmAction="save"
        />

        <x-ui.modal-cancel
            name="discard-manager-confirmation"
            title="Discard Unsaved Changes?"
            description="Are you sure you want to close? All details will be lost."
            discardText="Discard"
            returnText="Keep Editing"
            discardAction="close"
        />

    @endif
</div>
