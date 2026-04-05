<div>
    @if($isOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm">
            <div class="relative w-full max-w-2xl bg-white rounded-[20px] shadow-2xl overflow-hidden flex flex-col">

                {{-- Header --}}
                <div class="bg-[#09096B] text-white px-8 py-5 flex justify-between items-center flex-shrink-0">
                    <div>
                        <h2 class="text-lg font-bold uppercase tracking-wide">Add Maintenance Request</h2>
                        <p class="text-xs text-blue-200 mt-0.5">Fill in the details to submit a new ticket</p>
                    </div>
                    <flux:tooltip :content="'Close request form without saving'" position="bottom">
                        <button type="button"
                            x-on:click="$dispatch('open-modal', 'discard-maintenance-confirmation')"
                            class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-white/20 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </flux:tooltip>
                </div>

                {{-- Body --}}
                <div
                    class="p-7 space-y-6 overflow-y-auto max-h-[70vh]"
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

                    {{-- Summary Card --}}
                    <div class="bg-[#2672EC] rounded-xl p-5 text-white flex justify-between items-center shadow-lg">
                        <div>
                            <p class="text-xs opacity-80 mb-0.5">{{ $residentName }}</p>
                            <h3 class="text-2xl font-bold">{{ $unitNumber }}</h3>
                            <p class="text-sm opacity-80 mt-0.5">{{ $buildingName }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[11px] opacity-60 uppercase tracking-widest font-bold">Ticket Number</p>
                            <p class="text-sm font-bold font-mono mt-0.5">{{ $ticketNumber }}</p>
                        </div>
                    </div>

                    {{-- Category --}}
                    <div>
                        <label class="block text-[#333] font-bold mb-3 text-sm">Category</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach(['Plumbing', 'Electrical', 'Structural', 'Appliance', 'Pest Control'] as $cat)
                                <button type="button" wire:click="selectCategory('{{ $cat }}')"
                                    class="px-5 py-2 rounded-lg border text-sm font-semibold transition-all
                                        {{ $category === $cat
                                            ? 'bg-[#2672EC] border-[#2672EC] text-white shadow-sm'
                                            : 'bg-white border-gray-200 text-[#2672EC] hover:bg-blue-50 hover:border-blue-300' }}">
                                    {{ $cat }}
                                </button>
                            @endforeach
                        </div>
                        @error('category') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{--
                        DESCRIPTION FIX:
                        Wrapping the textarea in wire:ignore prevents Livewire's DOM morphing
                        from ever reading or overwriting this element's value.
                        Alpine owns the local state via x-model.
                        On every input event, Alpine explicitly calls $wire.setDescription()
                        to push the value into Livewire. This survives any re-render cycle,
                        including when the confirmation modal opens and Livewire re-renders.
                    --}}
                    <div>
                        <label for="desc-{{ $modalId }}" class="block text-[#333] font-bold mb-2 text-sm">
                            Issue Description
                        </label>
                        <div wire:ignore>
                            <textarea
                                id="desc-{{ $modalId }}"
                                rows="5"
                                x-data="{ val: '' }"
                                x-model="val"
                                x-on:input="$wire.setDescription(val)"
                                class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm text-gray-900 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#2672EC] focus:border-transparent resize-none placeholder-gray-400"
                                placeholder="Describe the issue in detail (at least 10 characters)..."
                            ></textarea>
                        </div>
                        @error('description')
                            <span class="text-red-500 text-xs mt-1 flex items-center gap-1">
                                <svg class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $message }}
                            </span>
                        @enderror
                        <p class="text-xs text-gray-400 mt-1.5 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            More detail helps us resolve your issue faster.
                        </p>
                    </div>

                    {{-- Multi-image Upload (max 3) --}}
                    <div x-data="{
                        previews: [],
                        handleFiles(event) {
                            const files = Array.from(event.target.files).slice(0, 3 - this.previews.length);
                            files.forEach(f => {
                                const reader = new FileReader();
                                reader.onload = e => this.previews.push(e.target.result);
                                reader.readAsDataURL(f);
                            });
                        },
                        removePreview(index) {
                            this.previews.splice(index, 1);
                            $wire.removeImage(index);
                        }
                    }">
                        <label class="block text-[#333] font-bold mb-2 text-sm">
                            Photos
                            <span class="text-gray-400 font-normal">(optional · up to 3 images)</span>
                        </label>

                        {{-- Preview grid --}}
                        <template x-if="previews.length > 0">
                            <div class="grid grid-cols-3 gap-3 mb-3">
                                <template x-for="(src, i) in previews" :key="i">
                                    <div class="relative group rounded-xl overflow-hidden border border-gray-200 aspect-square bg-gray-50">
                                        <img :src="src" class="w-full h-full object-cover">
                                        <flux:tooltip :content="'Remove this image from the request'" position="bottom">
                                            <button
                                                type="button"
                                                @click.prevent="removePreview(i)"
                                                class="absolute top-1.5 right-1.5 w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow"
                                            >
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </flux:tooltip>
                                    </div>
                                </template>

                                {{-- Add more slot (shown when < 3 images) --}}
                                <template x-if="previews.length < 3">
                                    <label class="relative cursor-pointer flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-[#2672EC]/40 aspect-square bg-[#F0F5FF] hover:bg-[#E6EEFF] transition-colors">
                                        <svg class="w-6 h-6 text-[#2672EC] opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        <p class="text-[11px] text-[#2672EC] font-bold mt-1">Add more</p>
                                        <input type="file" wire:model="images" accept="image/*" multiple
                                            @change="handleFiles($event)"
                                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                    </label>
                                </template>
                            </div>
                        </template>

                        {{-- Empty state upload zone --}}
                        <template x-if="previews.length === 0">
                            <div class="relative border-2 border-dashed border-[#2672EC]/40 rounded-xl p-6 bg-[#F0F5FF] text-center hover:bg-[#E6EEFF] transition-colors cursor-pointer">
                                <input type="file" wire:model="images" accept="image/*" multiple
                                    @change="handleFiles($event)"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                <div class="flex flex-col items-center pointer-events-none">
                                    <div class="bg-white p-3 rounded-full shadow-sm mb-2">
                                        <svg class="w-6 h-6 text-[#2672EC]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm text-gray-500 font-medium">Upload up to 3 photos</p>
                                    <p class="text-xs text-gray-400 mt-1">Drag & drop or <span class="text-[#2672EC] font-bold">browse</span> · Max 5MB each</p>
                                </div>
                            </div>
                        </template>

                        @error('images.*') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-7 py-5 border-t border-gray-100 flex justify-end flex-shrink-0">
                    <button type="button" wire:click="validateAndConfirm" wire:loading.attr="disabled"
                        class="bg-[#09096B] hover:bg-[#06064a] text-white font-bold px-8 py-2.5 rounded-full transition-all shadow-lg active:scale-95 text-sm flex items-center gap-2 disabled:opacity-60">
                        <span wire:loading.remove wire:target="validateAndConfirm,save">Save Request</span>
                        <span wire:loading wire:target="validateAndConfirm,save" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            Saving...
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <x-ui.modal-confirm name="save-maintenance-confirmation"
            title="Submit Maintenance Request?"
            description="Are you sure you want to submit this request?"
            confirmText="Yes, Submit" cancelText="Cancel" confirmAction="save"/>

        <x-ui.modal-cancel name="discard-maintenance-confirmation"
            title="Discard Unsaved Changes?"
            description="Are you sure you want to close? All details entered will be lost."
            discardText="Discard" returnText="Keep Editing" discardAction="close"/>
    @endif
</div>
