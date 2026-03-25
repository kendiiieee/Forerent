<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">

<style>
    input:-webkit-autofill,
    input:-webkit-autofill:hover,
    input:-webkit-autofill:focus,
    input:-webkit-autofill:active {
        -webkit-box-shadow: 0 0 0 9999px white inset !important;
        box-shadow: 0 0 0 9999px white inset !important;
        -webkit-text-fill-color: #374151 !important;
        transition: background-color 9999s ease-in-out 0s;
    }
</style>

<div class="w-full" style="font-family: 'Open Sans', sans-serif;">
    <div class="w-full bg-white rounded-2xl shadow-[0_4px_24px_rgba(0,0,0,0.08)] p-6 md:p-8">
        @if (session('success'))
            <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">
                {{ session('success') }}
            </div>
        @endif

        {{-- Profile Picture --}}
        <div class="mb-8 flex items-start gap-6">
            <div class="flex-shrink-0"
                 x-data="{ uploading: false, progress: 0, error: false }"
                 x-on:livewire-upload-start="uploading = true; progress = 0; error = false; console.log('Upload started')"
                 x-on:livewire-upload-finish="uploading = false; progress = 100; console.log('Upload finished')"
                 x-on:livewire-upload-cancel="uploading = false; console.log('Upload cancelled')"
                 x-on:livewire-upload-error="uploading = false; error = true; console.log('Upload error', $event)"
                 x-on:livewire-upload-progress="progress = $event.detail.progress; console.log('Progress', progress)"
            >
                <label class="cursor-pointer group relative block w-24 h-24">
                    @if ($profilePicture)
                        <img src="{{ $profilePicture->temporaryUrl() }}" class="w-full h-full rounded-full object-cover border-4 border-[#001B5E]">
                    @elseif ($this->existingProfileImgUrl)
                        <img src="{{ $this->existingProfileImgUrl }}" class="w-full h-full rounded-full object-cover border-4 border-[#001B5E]">
                    @else
                        <div class="w-full h-full rounded-full bg-[#001B5E] flex items-center justify-center">
                            <svg class="w-12 h-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                        </div>
                    @endif
                    {{-- Progress overlay with percentage --}}
                    <div x-show="uploading" x-cloak class="absolute inset-0 flex items-center justify-center rounded-full bg-black/50">
                        <span class="text-white text-xs font-bold" x-text="progress + '%'"></span>
                    </div>
                    {{-- Camera icon (bottom-right) --}}
                    <div class="absolute bottom-0 right-0 bg-white rounded-full p-1 border shadow">
                        <svg class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </div>
                    <input type="file" wire:model="profilePicture" class="hidden" accept="image/*"
                           @change="console.log('File selected:', $event.target.files[0]?.name)">
                </label>
                {{-- Error message --}}
                <div x-show="error" x-cloak class="mt-2 w-24">
                    <p class="text-[10px] text-red-500 font-medium text-center">Upload failed!</p>
                </div>
                {{-- Progress bar below avatar --}}
                <div x-show="uploading" x-cloak class="mt-2 w-24">
                    <div class="h-1 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full bg-[#2360E8] rounded-full transition-all duration-200" :style="'width: ' + progress + '%'"></div>
                    </div>
                    <p class="text-[10px] text-[#2360E8] mt-0.5 font-medium text-center">Uploading...</p>
                </div>
            </div>
            <div class="pt-2">
                <h3 class="text-xl font-bold leading-tight text-[#0C0B50]">Profile Picture</h3>
                <p class="mt-1 text-sm text-gray-500">This will be displayed on your profile</p>
                @error('profilePicture') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <label for="first_name" class="mb-1.5 block text-sm font-semibold text-gray-700">First Name</label>
                <div class="flex h-12 items-center rounded-xl border border-gray-200 bg-white px-4 shadow-sm transition-all duration-200 focus-within:border-blue-500 focus-within:ring-1 focus-within:ring-blue-500">
                    <input
                        type="text"
                        id="first_name"
                        wire:model.defer="firstName"
                        value="{{ $firstName ?? '' }}"
                        placeholder="Enter first name"
                        class="w-full border-0 bg-transparent text-sm text-gray-700 outline-none ring-0 placeholder:text-gray-300 focus:border-0 focus:outline-none focus:ring-0"
                    >
                </div>
                @error('firstName') <p class="ml-1 mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="last_name" class="mb-1.5 block text-sm font-semibold text-gray-700">Last Name</label>
                <div class="flex h-12 items-center rounded-xl border border-gray-200 bg-white px-4 shadow-sm transition-all duration-200 focus-within:border-blue-500 focus-within:ring-1 focus-within:ring-blue-500">
                    <input
                        type="text"
                        id="last_name"
                        wire:model.defer="lastName"
                        value="{{ $lastName ?? '' }}"
                        placeholder="Enter last name"
                        class="w-full border-0 bg-transparent text-sm text-gray-700 outline-none ring-0 placeholder:text-gray-300 focus:border-0 focus:outline-none focus:ring-0"
                    >
                </div>
                @error('lastName') <p class="ml-1 mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mb-4 mt-8 flex items-center gap-3">
            <h4 class="text-xl font-bold leading-tight text-[#0C0B50] md:text-xl">Contact Information</h4>
            <div class="h-px flex-1 bg-gray-200"></div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <label for="phone_number" class="mb-1.5 block text-sm font-semibold text-gray-700">Phone Number</label>
                <div class="flex h-12 items-center rounded-xl border border-gray-200 bg-white shadow-sm transition-all duration-200 focus-within:border-blue-500 focus-within:ring-1 focus-within:ring-blue-500">
                    <span class="inline-flex h-full items-center whitespace-nowrap border-r border-gray-200 px-3 text-xs font-semibold uppercase tracking-wide text-gray-400">
                        PH +63
                    </span>
                    <input
                        type="tel"
                        id="phone_number"
                        wire:model.defer="phoneNumber"
                        value="{{ $phoneNumber ?? '' }}"
                        inputmode="numeric"
                        maxlength="10"
                        pattern="[0-9]{10}"
                        oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)"
                        placeholder="Enter phone number"
                        class="w-full border-0 bg-transparent px-3 text-sm text-gray-700 outline-none ring-0 placeholder:text-gray-300 focus:border-0 focus:outline-none focus:ring-0"
                    >
                </div>
                @error('phoneNumber') <p class="ml-1 mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="mb-1.5 block text-sm font-semibold text-gray-700">Email Address</label>
                <div class="flex h-12 items-center rounded-xl border border-gray-200 bg-white px-4 shadow-sm transition-all duration-200 focus-within:border-blue-500 focus-within:ring-1 focus-within:ring-blue-500">
                    <input
                        type="email"
                        id="email"
                        wire:model.defer="email"
                        value="{{ $email ?? '' }}"
                        placeholder="Enter email address"
                        class="w-full border-0 bg-transparent text-sm text-gray-700 outline-none ring-0 placeholder:text-gray-300 focus:border-0 focus:outline-none focus:ring-0"
                    >
                </div>
                @error('email') <p class="ml-1 mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Valid ID Section (Tenant only) --}}
        @if(auth()->user()->role === 'tenant')
            <div class="mb-4 mt-8 flex items-center gap-3">
                <h4 class="text-xl font-bold leading-tight text-[#0C0B50] md:text-xl">Valid ID</h4>
                <div class="h-px flex-1 bg-gray-200"></div>
            </div>

            <div x-data="{ uploading: false, progress: 0 }"
                 x-on:livewire-upload-start="uploading = true; progress = 0"
                 x-on:livewire-upload-finish="uploading = false; progress = 100"
                 x-on:livewire-upload-cancel="uploading = false"
                 x-on:livewire-upload-error="uploading = false"
                 x-on:livewire-upload-progress="progress = $event.detail.progress"
            >
                @if ($governmentIdImage)
                    <div class="relative inline-block w-full max-w-md">
                        <img src="{{ $governmentIdImage->temporaryUrl() }}" class="w-full max-h-48 object-contain rounded-xl border border-gray-200 shadow-sm">
                        <button type="button" wire:click="removeGovernmentIdImage" class="absolute -top-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full border-2 border-white bg-red-500 text-xs font-bold text-white shadow-md hover:bg-red-600">&times;</button>
                    </div>
                @elseif ($this->existingGovernmentIdImageUrl)
                    <div class="relative inline-block w-full max-w-md">
                        <img src="{{ $this->existingGovernmentIdImageUrl }}" class="w-full max-h-48 object-contain rounded-xl border border-gray-200 shadow-sm">
                        <button type="button" wire:click="removeGovernmentIdImage" class="absolute -top-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full border-2 border-white bg-red-500 text-xs font-bold text-white shadow-md hover:bg-red-600">&times;</button>
                    </div>
                @else
                    <label class="flex w-full max-w-md cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 px-6 py-8 transition-colors duration-200 hover:border-blue-400 hover:bg-blue-50/50">
                        <svg class="mb-2 h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                        </svg>
                        <span class="text-sm font-semibold text-gray-600">Upload your Valid ID</span>
                        <span class="mt-1 text-xs text-gray-400">Photo or scan of your government-issued ID (max 10MB)</span>
                        <input type="file" wire:model="governmentIdImage" class="hidden" accept="image/*">
                    </label>
                @endif

                {{-- Progress bar --}}
                <div x-show="uploading" x-cloak class="mt-3 w-full max-w-md">
                    <div class="h-1.5 overflow-hidden rounded-full bg-gray-200">
                        <div class="h-full rounded-full bg-[#2360E8] transition-all duration-200" :style="'width: ' + progress + '%'"></div>
                    </div>
                    <p class="mt-0.5 text-[10px] font-medium text-[#2360E8]">Uploading... <span x-text="progress + '%'"></span></p>
                </div>

                @if ($governmentIdImage || $existingGovernmentIdImage)
                    <label class="mt-3 inline-flex cursor-pointer items-center gap-2 text-sm font-medium text-[#2360E8] hover:text-[#070589]" x-show="!uploading">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                        Change ID photo
                        <input type="file" wire:model="governmentIdImage" class="hidden" accept="image/*">
                    </label>
                @endif

                @error('governmentIdImage') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
        @endif

        <div class="mt-8 flex items-center justify-end">
            <button
                type="button"
                wire:click="confirmSave"
                class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition-colors duration-200 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >
                Save
            </button>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('open-save-confirm-modal', () => {
                const modalEl = document.getElementById('save-confirm-modal');
                if (modalEl) {
                    const modal = new Modal(modalEl);
                    modal.show();
                }
            });
        });
    </script>

    <div id="save-confirm-modal" wire:ignore.self tabindex="-1" class="fixed left-0 right-0 top-0 z-50 hidden h-[calc(100%-1rem)] max-h-full w-full items-center justify-center overflow-x-hidden overflow-y-auto md:inset-0">
        <div class="relative max-h-full w-full max-w-md p-4">
            <div class="relative rounded-2xl bg-white shadow-[0_10px_30px_rgba(0,0,0,0.15)]">
                <button type="button" class="absolute end-2.5 top-3 inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-100 hover:text-gray-700" data-modal-hide="save-confirm-modal">
                    <svg class="h-3 w-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>

                <div class="p-6 text-center">
                    <svg class="mx-auto mb-4 h-12 w-12 text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                    <h3 class="mb-5 text-base font-semibold text-gray-700">Are you sure you want to save these changes?</h3>

                    <div class="flex items-center justify-center gap-3">
                        <button wire:click="save" data-modal-hide="save-confirm-modal" type="button" class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition-colors duration-200 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Yes, save changes
                        </button>
                        <button wire:click="cancelSave" data-modal-hide="save-confirm-modal" type="button" class="rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 transition-colors duration-200 hover:bg-gray-50">
                            No, cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
