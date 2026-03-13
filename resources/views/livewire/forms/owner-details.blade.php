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
    <form wire:submit.prevent="confirmSave">
        <div class="w-full bg-white rounded-2xl shadow-[0_4px_24px_rgba(0,0,0,0.08)] p-6 md:p-8">
            @if (session('success'))
                <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-8 flex items-center gap-4">
                <div class="h-20 w-20 shrink-0 rounded-full bg-[#0C0B8A]"></div>
                <div>
                    <h3 class="text-xl font-bold leading-tight text-[#0C0B50] md:text-xl">Profile Picture</h3>
                    <p class="mt-1 text-sm text-gray-500">This will be displayed on your profile</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label for="first_name" class="mb-1.5 block text-sm font-semibold text-gray-700">First Name</label>
                    <div class="flex h-12 items-center rounded-xl border border-gray-200 bg-white px-4 shadow-sm transition-all duration-200 focus-within:border-blue-500 focus-within:ring-1 focus-within:ring-blue-500">
                        <input
                            type="text"
                            id="first_name"
                            wire:model.lazy="firstName"
                            placeholder="Juan"
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
                            wire:model.lazy="lastName"
                            placeholder="Dela Cruz"
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
                            wire:model.lazy="phoneNumber"
                            inputmode="numeric"
                            maxlength="10"
                            pattern="[0-9]{10}"
                            oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)"
                            placeholder="9123456789"
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
                            wire:model.lazy="email"
                            placeholder="juan.delacruz@example.com"
                            class="w-full border-0 bg-transparent text-sm text-gray-700 outline-none ring-0 placeholder:text-gray-300 focus:border-0 focus:outline-none focus:ring-0"
                        >
                    </div>
                    @error('email') <p class="ml-1 mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-8 flex items-center justify-end">
                <button
                    type="submit"
                    data-modal-target="save-confirm-modal"
                    data-modal-toggle="save-confirm-modal"
                    class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition-colors duration-200 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    Save
                </button>
            </div>
        </div>
    </form>

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
