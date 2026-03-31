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

<div
    class="flex flex-col w-full"
    style="font-family: 'Open Sans', sans-serif;"
    x-data="{
        showCurrent: false,
        showNew: false,
        showConfirm: false,
        password: @entangle('password').live,
        requirementRules: @js($passwordRequirementRules),
        requirementStatus: {},
        get allMet() {
            return this.requirementRules.every((rule) => this.requirementStatus[rule.key]);
        },
        init() {
            this.validatePassword();
            this.$watch('password', () => this.validatePassword());
        },
        validatePassword() {
            const value = this.password || '';

            this.requirementStatus = {};

            this.requirementRules.forEach((rule) => {
                if (rule.type === 'min') {
                    this.requirementStatus[rule.key] = value.length >= Number(rule.value);
                    return;
                }

                if (rule.type === 'regex') {
                    this.requirementStatus[rule.key] = new RegExp(rule.pattern).test(value);
                    return;
                }

                this.requirementStatus[rule.key] = false;
            });
        }
    }"
>

    <div class="w-full bg-white rounded-2xl shadow-[0_4px_24px_rgba(0,0,0,0.08)] p-6 md:p-8">
        <div class="space-y-5">

            {{-- 1. CURRENT PASSWORD --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Current Password</label>
                <div class="flex items-center border border-gray-200 rounded-xl px-4 h-12 bg-white shadow-sm focus-within:border-[#3B82F6] focus-within:ring-1 focus-within:ring-[#3B82F6] transition-all duration-200">
                    {{-- Icon --}}
                    <div class="shrink-0 mr-3">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <input
                        :type="showCurrent ? 'text' : 'password'"
                        wire:model="current_password"
                        autocomplete="off"
                        placeholder="Enter current password"
                        class="flex-1 bg-transparent outline-none border-0 ring-0 focus:ring-0 focus:outline-none focus:border-0 text-gray-700 text-sm placeholder-gray-300"
                    />
                    <button type="button" @click="showCurrent = !showCurrent" class="ml-2 flex items-center text-gray-400 hover:text-[#0C0B50] transition-colors focus:outline-none">
                        <svg x-show="!showCurrent" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="showCurrent" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
                @error('current_password') <p class="text-xs text-red-500 mt-1.5 ml-1">{{ $message }}</p> @enderror
            </div>

            {{-- 2. NEW PASSWORD --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">New Password</label>
                <div class="flex items-center border border-gray-200 rounded-xl px-4 h-12 bg-white shadow-sm focus-within:border-[#3B82F6] focus-within:ring-1 focus-within:ring-[#3B82F6] transition-all duration-200">
                    {{-- Icon --}}
                    <div class="shrink-0 mr-3">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <input
                        :type="showNew ? 'text' : 'password'"
                        wire:model.live="password"
                        autocomplete="off"
                        placeholder="Enter new password"
                        class="flex-1 bg-transparent outline-none border-0 ring-0 focus:ring-0 focus:outline-none focus:border-0 text-gray-700 text-sm placeholder-gray-300"
                    />
                    <button type="button" @click="showNew = !showNew" class="ml-2 flex items-center text-gray-400 hover:text-[#0C0B50] transition-colors focus:outline-none">
                        <svg x-show="!showNew" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="showNew" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
                @error('password') <p class="text-xs text-red-500 mt-1.5 ml-1">{{ $message }}</p> @enderror
            </div>

            {{-- 3. PASSWORD REQUIREMENTS --}}
            <div class="bg-[#EEF2FF] border border-[#C7D2FE] rounded-xl p-4">
                <p class="flex items-center gap-2 text-xs font-bold text-[#0C0B50] uppercase tracking-wide mb-3">
                    <span class="inline-block w-2 h-2 rounded-full bg-[#3B5BDB] shrink-0"></span>
                    Your password must contain:
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <template x-for="rule in requirementRules" :key="rule.key">
                        <div class="flex items-center gap-2 text-xs transition-colors duration-200"
                             :class="requirementStatus[rule.key] ? 'text-green-600' : 'text-gray-500'">
                            <svg x-show="requirementStatus[rule.key]" class="w-4 h-4 text-green-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <svg x-show="!requirementStatus[rule.key]" class="w-4 h-4 text-red-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span x-text="rule.label"></span>
                        </div>
                    </template>

                </div>
            </div>

            {{-- 4. CONFIRM PASSWORD --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Confirm New Password</label>
                <div class="flex items-center border border-gray-200 rounded-xl px-4 h-12 bg-white shadow-sm focus-within:border-[#3B82F6] focus-within:ring-1 focus-within:ring-[#3B82F6] transition-all duration-200">
                    {{-- Icon --}}
                    <div class="shrink-0 mr-3">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <input
                        :type="showConfirm ? 'text' : 'password'"
                        wire:model="password_confirmation"
                        autocomplete="off"
                        placeholder="Confirm new password"
                        class="flex-1 bg-transparent outline-none border-0 ring-0 focus:ring-0 focus:outline-none focus:border-0 text-gray-700 text-sm placeholder-gray-300"
                    />
                    <button type="button" @click="showConfirm = !showConfirm" class="ml-2 flex items-center text-gray-400 hover:text-[#0C0B50] transition-colors focus:outline-none">
                        <svg x-show="!showConfirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="showConfirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
            </div>

        </div>

        {{-- ACTION BUTTONS --}}
        <div class="mt-8 flex items-center justify-end gap-4">
            <button
                type="button"
                wire:click="clearFields"
                @click="
                    showCurrent = false;
                    showNew = false;
                    showConfirm = false;
                    validatePassword();
                "
                class="text-sm font-semibold text-gray-500 hover:text-gray-700 transition-colors"
            >
                Clear
            </button>
            <button
                type="button"
                wire:click="requestPasswordChangeConfirmation"
                class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >
                Update Password
            </button>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('open-security-confirm-modal', () => {
                const modalEl = document.getElementById('security-confirm-modal');
                if (modalEl) {
                    const modal = new Modal(modalEl);
                    modal.show();
                }
            });
        });
    </script>

    <div id="security-confirm-modal" wire:ignore.self tabindex="-1" class="fixed left-0 right-0 top-0 z-50 hidden h-[calc(100%-1rem)] max-h-full w-full items-center justify-center overflow-x-hidden overflow-y-auto md:inset-0">
        <div class="relative max-h-full w-full max-w-md p-4">
            <div class="relative rounded-2xl bg-white shadow-[0_10px_30px_rgba(0,0,0,0.15)]">
                <button type="button" class="absolute end-2.5 top-3 inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-100 hover:text-gray-700" data-modal-hide="security-confirm-modal">
                    <svg class="h-3 w-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>

                <div class="p-6 text-center">
                    <svg class="mx-auto mb-4 h-12 w-12 text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                    <h3 class="mb-2 text-base font-semibold text-gray-700">Confirm password change?</h3>
                    <p class="mb-5 text-sm text-gray-500">You will be logged out after your password is updated, and you will need to sign in again.</p>

                    <div class="flex items-center justify-center gap-3">
                        <button wire:click="updatePassword" data-modal-hide="security-confirm-modal" type="button" class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition-colors duration-200 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Yes, change password
                        </button>
                        <button data-modal-hide="security-confirm-modal" type="button" class="rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 transition-colors duration-200 hover:bg-gray-50">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
