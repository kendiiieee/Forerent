<div>
    @if($propertyId && $buildingName)
        <div
            x-data="{
                lightbox: false,
                lightboxIndex: 0,
                photos: @js($photos),
                showAllDocs: false,
                touchStartX: 0,
                touchEndX: 0,
                openLightbox(index) {
                    this.lightboxIndex = index;
                    this.lightbox = true;
                    document.body.style.overflow = 'hidden';
                },
                closeLightbox() {
                    this.lightbox = false;
                    document.body.style.overflow = '';
                },
                nextPhoto() {
                    this.lightboxIndex = (this.lightboxIndex + 1) % this.photos.length;
                },
                prevPhoto() {
                    this.lightboxIndex = (this.lightboxIndex - 1 + this.photos.length) % this.photos.length;
                },
                handleSwipe() {
                    const diff = this.touchStartX - this.touchEndX;
                    if (Math.abs(diff) > 50) {
                        diff > 0 ? this.nextPhoto() : this.prevPhoto();
                    }
                }
            }"
            @keydown.escape.window="closeLightbox()"
            @keydown.right.window="if(lightbox) nextPhoto()"
            @keydown.left.window="if(lightbox) prevPhoto()"
        >
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                {{-- ─── HERO SECTION: Photo + Info side by side ─── --}}
                <div class="flex flex-col sm:flex-row sm:items-stretch">

                    {{-- Left: Photo Gallery Area --}}
                    @if(count($photos) > 0)
                        <div class="sm:w-[45%] relative">
                            <div
                                class="relative h-56 sm:h-full cursor-pointer group overflow-hidden"
                                @click="openLightbox({{ $activePhotoIndex }})"
                            >
                                <img
                                    src="{{ $photos[$activePhotoIndex]['url'] ?? '' }}"
                                    alt="{{ $photos[$activePhotoIndex]['name'] ?? '' }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                >
                                <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-black/10 to-transparent"></div>

                                <div class="absolute top-3 left-3 bg-black/40 backdrop-blur-sm text-white text-[11px] font-semibold rounded-full px-2.5 py-1 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ $activePhotoIndex + 1 }}/{{ count($photos) }}
                                </div>

                                <div class="absolute top-3 right-3 w-8 h-8 bg-black/30 backdrop-blur-sm rounded-full flex items-center justify-center text-white opacity-0 group-hover:opacity-100 transition-opacity">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607zM10.5 7.5v6m3-3h-6"/>
                                    </svg>
                                </div>

                                @if(count($photos) > 1)
                                    <div class="absolute bottom-0 left-0 right-0 p-2.5">
                                        <div class="flex gap-1.5 justify-center">
                                            @foreach($photos as $index => $photo)
                                                <button
                                                    wire:click="setActivePhoto({{ $index }})"
                                                    class="w-10 h-10 rounded-lg overflow-hidden flex-shrink-0 transition-all duration-200 cursor-pointer
                                                        {{ $activePhotoIndex === $index
                                                            ? 'ring-2 ring-white shadow-lg scale-105'
                                                            : 'ring-1 ring-white/30 opacity-70 hover:opacity-100' }}"
                                                >
                                                    <img src="{{ $photo['url'] }}" alt="" class="w-full h-full object-cover">
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Right: Property Info --}}
                    <div class="{{ count($photos) > 0 ? 'sm:w-[55%]' : 'w-full' }} flex flex-col">

                        <div class="px-5 pt-5 pb-4 border-b border-gray-100">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="text-lg font-bold text-[#0C0B50] leading-tight truncate">{{ $buildingName }}</h3>
                                    <div class="flex items-center gap-1.5 mt-1.5">
                                        <svg class="w-3.5 h-3.5 text-[#2360E8] flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-xs text-gray-500 truncate">{{ $address }}</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1.5 flex-shrink-0">
                                    <div class="bg-[#2360E8]/10 rounded-lg px-2.5 py-1.5 text-center">
                                        <p class="text-[#2360E8] text-sm font-bold leading-none">{{ $unitCount }}</p>
                                        <p class="text-[#2360E8]/60 text-[11px] uppercase tracking-wider mt-0.5 font-medium">Units</p>
                                    </div>
                                    <div class="bg-[#070589]/10 rounded-lg px-2.5 py-1.5 text-center">
                                        <p class="text-[#070589] text-sm font-bold leading-none">{{ count($photos) }}</p>
                                        <p class="text-[#070589]/60 text-[11px] uppercase tracking-wider mt-0.5 font-medium">Photos</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="px-5 py-4 flex-1">
                            @if($description)
                                <div class="mb-4">
                                    <div class="flex items-center gap-1.5 mb-2">
                                        <svg class="w-3.5 h-3.5 text-[#2360E8]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/>
                                        </svg>
                                        <h4 class="text-xs font-semibold text-gray-900 uppercase tracking-wider">About</h4>
                                    </div>
                                    <p class="text-[13px] text-gray-600 leading-relaxed line-clamp-4">{{ $description }}</p>
                                </div>
                            @else
                                <div class="mb-4 bg-gray-50 rounded-xl p-4 border border-dashed border-gray-200 text-center">
                                    <p class="text-xs text-gray-400">No description added</p>
                                </div>
                            @endif

                            @if(count($documents) > 0)
                                <div>
                                    <div class="flex items-center justify-between mb-2.5">
                                        <div class="flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-[#2360E8]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            <h4 class="text-xs font-semibold text-gray-900 uppercase tracking-wider">Documents</h4>
                                            <span class="bg-gray-100 text-gray-500 text-[11px] font-bold rounded-full px-1.5 py-0.5">{{ count($documents) }}</span>
                                        </div>
                                        @if(count($documents) > 2)
                                            <button
                                                @click="showAllDocs = !showAllDocs"
                                                class="text-[11px] text-[#2360E8] hover:text-[#1d4eb8] font-medium transition-colors cursor-pointer"
                                                x-text="showAllDocs ? 'Show less' : 'Show all'"
                                            ></button>
                                        @endif
                                    </div>

                                    <div class="space-y-1.5">
                                        @foreach($documents as $index => $doc)
                                            <a
                                                href="{{ $doc['url'] }}"
                                                target="_blank"
                                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-gray-50 transition-colors group"
                                                x-show="{{ $index }} < 2 || showAllDocs"
                                                @if($index >= 2) x-cloak x-transition @endif
                                            >
                                                <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0
                                                    {{ str_ends_with(strtolower($doc['name']), '.pdf')
                                                        ? 'bg-red-50 text-red-400'
                                                        : 'bg-blue-50 text-blue-400' }}">
                                                    @if(str_ends_with(strtolower($doc['name']), '.pdf'))
                                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 2l5 5h-5V4zM8.5 13H10v4H8.5v-4zm2.5 0h1.5v4H11v-4zm2.5 0H15v4h-1.5v-4z"/>
                                                        </svg>
                                                    @else
                                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                        </svg>
                                                    @endif
                                                </div>

                                                <div class="flex-1 min-w-0">
                                                    <p class="text-xs font-semibold text-gray-700 group-hover:text-[#2360E8] transition-colors truncate">
                                                        {{ $this->getCategoryLabel($doc['category']) }}
                                                    </p>
                                                    <p class="text-[11px] text-gray-400 truncate">{{ $doc['name'] }}</p>
                                                </div>

                                                <div class="flex items-center gap-2 flex-shrink-0">
                                                    @if($doc['isPrivate'])
                                                        <span class="hidden sm:inline-flex items-center gap-0.5 text-[8px] font-bold text-amber-500 bg-amber-50 px-1.5 py-0.5 rounded-full uppercase tracking-wider border border-amber-100">
                                                            <svg class="w-2 h-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                            </svg>
                                                            Private
                                                        </span>
                                                    @endif
                                                    <div class="w-7 h-7 rounded-lg bg-gray-100 group-hover:bg-[#2360E8] flex items-center justify-center transition-all">
                                                        <svg class="w-3.5 h-3.5 text-gray-400 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                                                        </svg>
                                                    </div>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- LIGHTBOX --}}
            <template x-if="photos.length > 0">
                <div
                    x-show="lightbox"
                    x-cloak
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 z-[100] bg-black/90 backdrop-blur-sm flex items-center justify-center p-4"
                    @click.self="closeLightbox()"
                    @touchstart="touchStartX = $event.touches[0].clientX"
                    @touchend="touchEndX = $event.changedTouches[0].clientX; handleSwipe()"
                >
                    <flux:tooltip :content="'Close the image viewer'" position="bottom">
                        <button @click="closeLightbox()" class="absolute top-4 right-4 z-10 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition-colors cursor-pointer">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </flux:tooltip>

                    <div class="absolute top-4 left-4 text-white/70 text-sm font-medium bg-white/10 backdrop-blur-sm rounded-full px-3 py-1">
                        <span x-text="lightboxIndex + 1"></span> / <span x-text="photos.length"></span>
                    </div>

                    <flux:tooltip :content="'View the previous photo'" position="bottom">
                        <button x-show="photos.length > 1" @click.stop="prevPhoto()" class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-white/10 hover:bg-white/25 flex items-center justify-center text-white transition-all cursor-pointer">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
                            </svg>
                        </button>
                    </flux:tooltip>

                    <div class="max-w-5xl max-h-[85vh] flex items-center justify-center">
                        <img :src="photos[lightboxIndex]?.url" :alt="photos[lightboxIndex]?.name" class="max-w-full max-h-[85vh] object-contain rounded-lg shadow-2xl">
                    </div>

                    <flux:tooltip :content="'View the next photo'" position="bottom">
                        <button x-show="photos.length > 1" @click.stop="nextPhoto()" class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-white/10 hover:bg-white/25 flex items-center justify-center text-white transition-all cursor-pointer">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                            </svg>
                        </button>
                    </flux:tooltip>

                    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex items-center gap-2 bg-black/40 backdrop-blur-sm rounded-2xl px-3 py-2">
                        <template x-for="(photo, i) in photos" :key="i">
                            <button
                                @click.stop="lightboxIndex = i"
                                :class="lightboxIndex === i ? 'ring-2 ring-white scale-110' : 'opacity-50 hover:opacity-80'"
                                class="w-10 h-10 rounded-lg overflow-hidden flex-shrink-0 transition-all cursor-pointer"
                            >
                                <img :src="photo.url" class="w-full h-full object-cover">
                            </button>
                        </template>
                    </div>
                </div>
            </template>
        </div>

    @else
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="flex flex-col sm:flex-row items-center gap-4 p-8">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-[#070589]/8 to-[#2360E8]/8 flex items-center justify-center flex-shrink-0">
                    <svg class="w-7 h-7 text-[#2360E8]/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div class="text-center sm:text-left">
                    <p class="text-sm text-gray-500 font-medium">Select a building to view details</p>
                    <p class="text-xs text-gray-400 mt-0.5">Click on any building card above</p>
                </div>
            </div>
        </div>
    @endif
</div>
