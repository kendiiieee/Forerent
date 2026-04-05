<div>
    @if($isOpen)
        {{-- Main Add Property Modal  --}}
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm">
            <div class="relative w-full max-w-2xl bg-gray-50 rounded-2xl shadow-xl overflow-hidden max-h-[90vh] flex flex-col">

                {{-- Header --}}
                <div class="bg-[#070589] text-white p-6 flex-shrink-0">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-xl font-bold uppercase">
                                {{ $editingPropertyId ? 'EDIT ' . $buildingName . ' PROPERTY' : 'ADD NEW PROPERTY' }}
                            </h2>
                            <p class="mt-1 text-sm text-blue-100">
                                {{ $editingPropertyId ? 'Update property details' : 'Fill in the details to predict rental price' }}
                            </p>
                        </div>

                        <flux:tooltip :content="'Close property form without saving'" position="bottom">
                            <button
                                type="button"
                                x-on:click="$dispatch('open-modal', 'discard-property-confirmation')"
                                class="text-white hover:text-blue-200 transition-colors focus:outline-none">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <span class="sr-only">Close modal</span>
                            </button>
                        </flux:tooltip>

                    </div>
                </div>

                {{-- Scrollable Content --}}
                <div
                    class="flex-1 overflow-y-auto p-6"
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
                    <div class="space-y-6">

                        {{-- Unit Identification --}}
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                            <div>
                                <div class="flex items-center gap-2 mb-3">
                                    <svg class="w-5 h-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <h3 class="text-base font-semibold text-gray-900">Unit Identification</h3>
                                </div>

                                <p class="text-sm text-gray-600 mb-6">
                                    Basic property information collected. Unit details will be added in the next step.
                                </p>

                                <!-- Property Name -->
                                <div class="relative mb-6">
                                    <input
                                        wire:model.defer="buildingName"
                                        type="text"
                                        id="buildingName"
                                        class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-[#0030C5] peer"
                                        placeholder=" "
                                    />
                                    <label
                                        for="buildingName"
                                        class="absolute text-sm text-gray-500 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white px-2 peer-focus:px-2 peer-focus:text-[#0030C5] peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 start-1"
                                    >
                                        Property Name
                                    </label>
                                    @error('buildingName')
                                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Address -->
                                <div class="relative mb-6">
                                    <input
                                        wire:model.defer="address"
                                        type="text"
                                        id="address"
                                        class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-[#0030C5] peer"
                                        placeholder=" "
                                    />
                                    <label
                                        for="address"
                                        class="absolute text-sm text-gray-500 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white px-2 peer-focus:px-2 peer-focus:text-[#0030C5] peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 start-1"
                                    >
                                        Address
                                    </label>
                                    @error('address')
                                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="relative">
                                    <textarea
                                        wire:model.defer="description"
                                        id="description"
                                        rows="4"
                                        class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-[#0030C5] peer resize-none"
                                        placeholder=" "
                                    ></textarea>
                                    <label
                                        for="description"
                                        class="absolute text-sm text-gray-500 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white px-2 peer-focus:px-2 peer-focus:text-[#0030C5] peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 start-1"
                                    >
                                        Description
                                    </label>
                                    @error('description')
                                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                        </div>

                        {{-- Property Photos --}}
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                            <div>
                                <div class="flex items-center gap-2 mb-3">
                                    <svg class="w-5 h-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <h3 class="text-base font-semibold text-gray-900">Property Photos</h3>
                                </div>

                                <p class="text-sm text-gray-600 mb-6">
                                    Upload up to 5 photos of your property. <span class="text-red-500">*</span> (max 10MB each)
                                </p>

                                <div x-data="{
                                    existingCount: {{ count($existingPhotos) }},
                                    previews: [],
                                    get totalCount() { return this.existingCount + this.previews.length; },
                                    handleFiles(event) {
                                        const maxSlots = 5 - this.totalCount;
                                        const files = Array.from(event.target.files).slice(0, maxSlots);
                                        files.forEach(f => {
                                            const reader = new FileReader();
                                            reader.onload = e => this.previews.push(e.target.result);
                                            reader.readAsDataURL(f);
                                        });
                                    },
                                    removePreview(index) {
                                        this.previews.splice(index, 1);
                                        $wire.removePhoto(index);
                                    },
                                    removeExisting(id) {
                                        $wire.removeExistingPhoto(id);
                                        this.existingCount = Math.max(0, this.existingCount - 1);
                                    }
                                }">
                                    {{-- Combined photo grid --}}
                                    <template x-if="totalCount > 0">
                                        <div class="grid grid-cols-4 gap-3 mb-3">
                                            {{-- Existing photos --}}
                                            @foreach($existingPhotos as $photo)
                                                <div class="relative group rounded-lg overflow-hidden border border-gray-300 aspect-square bg-gray-50">
                                                    <img src="{{ $photo['url'] }}" class="w-full h-full object-cover" alt="{{ $photo['name'] }}">
                                                    <flux:tooltip :content="'Remove this photo from the upload'" position="bottom">
                                                        <button
                                                            type="button"
                                                            @click.prevent="removeExisting({{ $photo['id'] }})"
                                                            class="absolute top-1.5 right-1.5 w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                        </button>
                                                    </flux:tooltip>
                                                </div>
                                            @endforeach

                                            {{-- New photo previews --}}
                                            <template x-for="(src, i) in previews" :key="i">
                                                <div class="relative group rounded-lg overflow-hidden border border-gray-300 aspect-square bg-gray-50">
                                                    <img :src="src" class="w-full h-full object-cover">
                                                    <flux:tooltip :content="'Remove this photo from the upload'" position="bottom">
                                                        <button
                                                            type="button"
                                                            @click.prevent="removePreview(i)"
                                                            class="absolute top-1.5 right-1.5 w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                        </button>
                                                    </flux:tooltip>
                                                </div>
                                            </template>

                                            {{-- Add more slot --}}
                                            <template x-if="totalCount < 5">
                                                <label class="relative cursor-pointer flex flex-col items-center justify-center rounded-lg border border-dashed border-gray-300 aspect-square bg-gray-50 hover:bg-gray-100 transition-colors">
                                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                                    </svg>
                                                    <p class="text-xs text-gray-500 mt-1">Add more</p>
                                                    <input type="file" wire:model="newPhotos" accept="image/*" multiple
                                                        @change="handleFiles($event)"
                                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                                </label>
                                            </template>
                                        </div>
                                    </template>

                                    {{-- Empty state upload zone --}}
                                    <template x-if="totalCount === 0">
                                        <div class="relative border border-dashed border-gray-300 rounded-lg p-8 bg-gray-50 text-center hover:bg-gray-100 transition-colors cursor-pointer">
                                            <input type="file" wire:model="newPhotos" accept="image/*" multiple
                                                @change="handleFiles($event)"
                                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                            <div class="flex flex-col items-center pointer-events-none">
                                                <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                <p class="text-sm text-gray-500">Drag & drop or click to upload photos</p>
                                                <p class="text-xs text-gray-400 mt-1">JPG, PNG up to 10MB each</p>
                                            </div>
                                        </div>
                                    </template>

                                    @error('propertyPhotos') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    @error('propertyPhotos.*') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                        </div>

                        {{-- Property Documents --}}
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center gap-2 mb-1">
                                <svg class="w-5 h-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <h3 class="text-base font-semibold text-gray-900">Property Documents</h3>
                            </div>
                            <p class="text-xs text-gray-500 mb-5">Required <span class="text-red-500">*</span> — PDF or image, max 10MB each</p>

                            @php
                                $allDocs = [
                                    ['field' => 'businessPermit', 'label' => 'Business Permit', 'category' => 'business_permit', 'group' => 'owner'],
                                    ['field' => 'bir2303', 'label' => 'BIR 2303', 'category' => 'bir_2303', 'group' => 'owner'],
                                    ['field' => 'inspectionReport', 'label' => 'Inspection Report', 'category' => 'inspection_report', 'group' => 'owner'],
                                    ['field' => 'barangayClearance', 'label' => 'Barangay Clearance', 'category' => 'barangay_clearance', 'group' => 'owner'],
                                    ['field' => 'occupancyPermit', 'label' => 'Occupancy Permit', 'category' => 'occupancy_permit', 'group' => 'tenant'],
                                ];
                            @endphp

                            <div class="grid grid-cols-2 gap-3">
                                @foreach($allDocs as $doc)
                                    @php
                                        $docField = $doc['field'];
                                        $docLabel = $doc['label'];
                                        $existingDoc = collect($existingDocuments)->firstWhere('category', $doc['category']);
                                        $isOwnerOnly = $doc['group'] === 'owner';
                                    @endphp

                                    <div
                                        x-data="{
                                            uploading: false,
                                            progress: 0,
                                            fileName: '',
                                            error: '',
                                            maxSize: 10 * 1024 * 1024,
                                            allowedTypes: ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'],
                                            validateFile(event) {
                                                const file = event.target.files[0];
                                                this.error = '';
                                                if (!file) return;

                                                if (!this.allowedTypes.includes(file.type)) {
                                                    this.error = 'Only PDF, JPG, PNG allowed';
                                                    event.target.value = '';
                                                    return;
                                                }
                                                if (file.size > this.maxSize) {
                                                    this.error = 'File must be under 10MB';
                                                    event.target.value = '';
                                                    return;
                                                }

                                                this.fileName = file.name;
                                                this.uploading = true;
                                                this.progress = 0;
                                            }
                                        }"
                                        x-on:livewire-upload-start="uploading = true; error = ''"
                                        x-on:livewire-upload-finish="uploading = false; progress = 100"
                                        x-on:livewire-upload-cancel="uploading = false; progress = 0"
                                        x-on:livewire-upload-error="uploading = false; progress = 0; error = 'Upload failed — file may exceed the size limit (10MB)'"
                                        x-on:livewire-upload-progress="progress = $event.detail.progress"
                                        class="relative rounded-xl border border-gray-200 bg-gray-50 p-3 hover:border-gray-300 transition-colors {{ $doc['field'] === 'occupancyPermit' ? 'col-span-2' : '' }}"
                                    >
                                        {{-- Group badge --}}
                                        @if($isOwnerOnly)
                                            <span class="inline-flex items-center gap-1 text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1.5">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                Private
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1.5">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                Visible to tenants
                                            </span>
                                        @endif

                                        <p class="text-sm font-medium text-gray-700 mb-2">{{ $docLabel }} <span class="text-red-500">*</span></p>

                                        @if($existingDoc)
                                            {{-- Existing file uploaded --}}
                                            <div class="flex items-center justify-between gap-2">
                                                <div class="flex items-center gap-2 min-w-0">
                                                    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    <span class="text-xs text-gray-600 truncate">{{ $existingDoc['name'] }}</span>
                                                </div>
                                                <div class="flex items-center gap-1.5 flex-shrink-0">
                                                    <label class="cursor-pointer text-xs text-[#2360E8] hover:text-[#1d4eb8] font-medium transition-colors">
                                                        Replace
                                                        <input type="file" wire:model="{{ $docField }}" accept=".pdf,.jpg,.jpeg,.png"
                                                            @change="validateFile($event)" class="hidden">
                                                    </label>
                                                    <flux:tooltip :content="'Remove this document from the upload'" position="bottom">
                                                        <button type="button" wire:click="removeExistingDocument({{ $existingDoc['id'] }})" class="text-gray-400 hover:text-red-500 transition-colors">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                        </button>
                                                    </flux:tooltip>
                                                </div>
                                            </div>
                                        @elseif($this->{$docField})
                                            {{-- Newly uploaded file --}}
                                            <div class="flex items-center justify-between gap-2">
                                                <div class="flex items-center gap-2 min-w-0">
                                                    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    <span class="text-xs text-gray-600 truncate">{{ $this->{$docField}->getClientOriginalName() }}</span>
                                                </div>
                                                <flux:tooltip :content="'Remove this document from the upload'" position="bottom">
                                                <button type="button" wire:click="$set('{{ $docField }}', null)" class="text-gray-400 hover:text-red-500 transition-colors flex-shrink-0">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </flux:tooltip>
                                            </div>
                                        @else
                                            {{-- Empty upload --}}
                                            <label class="flex items-center gap-2 cursor-pointer group">
                                                <div class="w-8 h-8 rounded-lg bg-white border border-dashed border-gray-300 flex items-center justify-center group-hover:border-[#2360E8] group-hover:bg-blue-50 transition-colors flex-shrink-0">
                                                    <svg class="w-4 h-4 text-gray-400 group-hover:text-[#2360E8] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                                    </svg>
                                                </div>
                                                <span class="text-xs text-gray-400 group-hover:text-gray-600 transition-colors">Choose file...</span>
                                                <input type="file" wire:model="{{ $docField }}" accept=".pdf,.jpg,.jpeg,.png"
                                                    @change="validateFile($event)" class="hidden">
                                            </label>
                                        @endif

                                        {{-- Upload progress bar --}}
                                        <div x-show="uploading" x-cloak class="mt-2">
                                            <div class="w-full bg-gray-200 rounded-full h-1 overflow-hidden">
                                                <div class="bg-[#2360E8] h-1 rounded-full transition-all duration-300 ease-out"
                                                     :style="'width: ' + progress + '%'"></div>
                                            </div>
                                            <p class="text-[11px] text-gray-400 mt-1" x-text="progress < 100 ? 'Uploading... ' + progress + '%' : 'Complete'"></p>
                                        </div>

                                        {{-- Client-side validation error --}}
                                        <template x-if="error">
                                            <p class="text-red-500 text-[11px] mt-1.5" x-text="error"></p>
                                        </template>

                                        {{-- Server-side validation error --}}
                                        @error($docField) <p class="text-red-500 text-[11px] mt-1.5">{{ $message }}</p> @enderror
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Sticky Save Button --}}
                <div class="flex-shrink-0 bg-gray-50 border-t border-gray-200 px-6 py-4">
                    <div class="flex justify-end">
                        <button
                            type="button"
                            wire:click="validateAndConfirm"
                            class="px-8 py-3 bg-[#070589] text-white text-sm font-semibold rounded-lg hover:bg-[#001445] focus:ring-4 focus:ring-blue-300 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove wire:target="validateAndConfirm, next">Save</span>
                            <span wire:loading wire:target="validateAndConfirm, next">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing...
                            </span>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        {{-- 1. SAVE CONFIRMATION  --}}
        <x-ui.modal-confirm
            name="save-property-confirmation"
            title="Save Property?"
            description="Are you sure you want to add this new property?"
            confirmText="Yes, Save"
            cancelText="Cancel"
            confirmAction="next"
        />

        {{-- 2. DISCARD CONFIRMATION --}}
        <div
            x-data="{ show: false }"
            x-show="show"
            x-on:open-modal.window="if ($event.detail === 'discard-property-confirmation') show = true"
            x-on:close-modal.window="if ($event.detail === 'discard-property-confirmation') show = false"
            x-on:keydown.escape.window="show = false"
            class="fixed inset-0 z-[60] flex items-center justify-center px-4 py-6 sm:px-0"
            style="display: none;"
        >
            <div x-show="show" class="fixed inset-0 transform transition-all" x-on:click="show = false">
                <div class="absolute inset-0 bg-gray-600 opacity-50"></div>
            </div>

            <div x-show="show" class="bg-white rounded-[20px] overflow-hidden shadow-xl transform transition-all sm:w-full sm:max-w-[480px] p-8 relative z-[100]">
                <flux:tooltip :content="'Close this confirmation'" position="bottom">
                    <button @click="show = false" class="absolute top-5 right-5 text-[#0C0B50] hover:text-blue-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </flux:tooltip>

                <div class="text-center mt-4 mb-8">
                    <h3 class="text-2xl font-bold text-[#0C0B50] mb-3">Discard Unsaved Changes?</h3>
                    <p class="text-gray-500 text-sm leading-relaxed px-4">Are you sure you want to close? All details will be lost.</p>
                </div>

                <div class="flex justify-center gap-4 px-2">
                    <button
                        @click="show = false; $wire.close()"
                        class="flex-1 bg-[#D6E6FF] hover:bg-[#c3daff] text-[#0C0B50] font-bold py-3 rounded-xl transition-colors text-sm">
                        Discard
                    </button>

                    <button
                        @click="show = false"
                        class="flex-1 bg-[#104EA2] hover:bg-[#0d3f82] text-white font-bold py-3 rounded-xl transition-colors shadow-md text-sm">
                        Keep Editing
                    </button>
                </div>
            </div>
        </div>

    @endif
</div>
