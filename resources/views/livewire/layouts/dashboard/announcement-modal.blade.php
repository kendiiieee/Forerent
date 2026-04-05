<div>
    {{-- Main Announcement Form Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm" aria-labelledby="modal-title" role="dialog" aria-modal="true">

        {{-- Modal Panel --}}
        <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-xl overflow-hidden flex flex-col max-h-[90vh]">

            {{-- 1. Header Section --}}
            <div class="bg-[#070589] text-white p-4 sm:p-6 flex-shrink-0">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-xl font-bold uppercase">{{ $editingAnnouncementId ? 'EDIT' : '' }} ANNOUNCEMENT</h2>
                        <p class="mt-1 text-sm text-blue-100">{{ $editingAnnouncementId ? 'Update the details of your announcement' : 'Fill in the details to post a new update' }}</p>
                    </div>
                    {{-- Close Button (Triggers Discard Modal) --}}
                    <flux:tooltip :content="'Discard and close this form'" position="bottom">
                        <button
                            type="button"
                            x-on:click="$dispatch('open-modal', 'discard-announcement-confirmation')"
                            class="text-white hover:text-blue-200 transition-colors focus:outline-none">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </flux:tooltip>
                </div>
            </div>

            {{-- 2. Form Body --}}
            <div class="flex-1 overflow-y-auto p-4 sm:p-8">
                <div class="space-y-6">

                    {{-- White Container Card --}}
                    <div class="bg-white rounded-2xl border border-gray-200 p-4 sm:p-6 shadow-sm space-y-6">

                        {{-- Headline --}}
                        <div class="relative">
                            <input
                                wire:model="headline"
                                type="text"
                                id="headline"
                                class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-[#0030C5] peer placeholder-transparent"
                                placeholder=" "
                            />
                            <label
                                for="headline"
                                class="absolute text-sm text-gray-500 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white px-2 peer-focus:px-2 peer-focus:text-[#0030C5] peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 start-1 pointer-events-none"
                            >
                                Headline
                            </label>
                            @error('headline')
                                <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Details --}}
                        <div class="relative">
                            <textarea
                                wire:model="details"
                                id="details"
                                rows="5"
                                class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-[#0030C5] peer resize-none placeholder-transparent"
                                placeholder=" "
                            ></textarea>
                            <label
                                for="details"
                                class="absolute text-sm text-gray-500 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white px-2 peer-focus:px-2 peer-focus:text-[#0030C5] peer-placeholder-shown:scale-100 peer-placeholder-shown:top-6 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 start-1 pointer-events-none"
                            >
                                Details
                            </label>
                            @error('details')
                                <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Property (Floating Label Textarea) --}}
                        <div class="relative">
                            <select
                                wire:model="propertyId"
                                id="propertyId"
                                class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-[#0030C5] peer"
                            >
                                <option value="" {{ is_null($propertyId) ? 'selected' : '' }} hidden>
                                    Select a property
                                </option>

                                @foreach ($properties as $property)
                                    <option value="{{ $property->property_id }}">
                                        {{ $property->building_name }}
                                    </option>
                                @endforeach
                            </select>
                            <label
                                for="propertyId"
                                class="absolute text-sm text-gray-500 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white px-2 peer-focus:px-2 peer-focus:text-[#0030C5] peer-placeholder-shown:scale-100 peer-placeholder-shown:top-6 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 start-1 pointer-events-none"
                            >
                                Property
                            </label>
                            @error('propertyId')
                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Notification Date --}}
                        <div class="relative">
                            <input
                                wire:model="notificationDate"
                                type="date"
                                id="notificationDate"
                                min="{{ now()->toDateString() }}"
                                class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-[#0030C5] peer placeholder-transparent"
                                placeholder=" "
                            />
                            <label
                                for="notificationDate"
                                class="absolute text-sm text-gray-500 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white px-2 peer-focus:px-2 peer-focus:text-[#0030C5] peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 start-1 pointer-events-none"
                            >
                                Notification Date
                            </label>
                            @error('notificationDate')
                                <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>
                </div>
            </div>

            {{-- 3. Footer --}}
            <div class="p-4 sm:p-6 pt-0 flex justify-end">
                {{-- Save Button   --}}
                <button
                    type="button"
                    x-on:click="$dispatch('open-modal', 'save-announcement-confirmation')"
                    class="px-8 py-3 bg-[#070589] text-white text-sm font-semibold rounded-lg hover:bg-[#001445] focus:ring-4 focus:ring-blue-300 transition-colors shadow-lg">
                    Save
                </button>
            </div>
        </div>
    </div>


    {{--  Confirmation Modal   --}}
    <x-ui.modal-confirm
        name="save-announcement-confirmation"
        title="Post Announcement?"
        description="Your announcement will be saved and will be visible to everyone immediately. Do you want to proceed?"
        confirmText="Confirm Post"
        cancelText="Cancel"
        confirmAction="confirmPost"
    />

    {{--  Cancel Modal --}}
    <x-ui.modal-cancel
        name="discard-announcement-confirmation"
        title="Discard Unsaved Changes?"
        description="Are you sure you want to close? All details will be lost."
        discardText="Discard"
        returnText="Keep Editing"
        discardAction="closeModal"
    />

    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('open-announcement-modal', () => {
            @this.call('openModal');
        });
    });
</script>
@endpush
