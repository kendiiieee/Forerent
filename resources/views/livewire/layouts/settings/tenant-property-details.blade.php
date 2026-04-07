<div class="flex flex-col w-full space-y-6" style="font-family: 'Open Sans', sans-serif;">

    @if(!$hasLease)
        {{-- No active lease --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="flex flex-col sm:flex-row items-center gap-4 p-8">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-[#070589]/8 to-[#2360E8]/8 flex items-center justify-center flex-shrink-0">
                    <svg class="w-7 h-7 text-[#2360E8]/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div class="text-center sm:text-left">
                    <p class="text-sm text-gray-500 font-medium">No active lease found</p>
                    <p class="text-xs text-gray-400 mt-0.5">Property and unit details will appear here once you have an active lease.</p>
                </div>
            </div>
        </div>
    @else

        {{-- ============ UNIT DETAILS (Card Design) ============ --}}
        @if($unit)
            <div style="background: #fff; border-radius: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 6px 16px rgba(0,0,0,0.04), 0 24px 48px rgba(0,0,0,0.03); overflow: hidden; transition: box-shadow 0.25s ease; font-family: 'Open Sans', sans-serif;">

                {{-- Header: dark section with unit info --}}
                <div style="background: linear-gradient(135deg, #1e40af 0%, #2563eb 50%, #3b82f6 100%); padding: 24px 28px 20px;">
                    <div>
                        <h3 style="color: #fff; font-size: 20px; font-weight: 800; line-height: 1.2; margin: 0;">Unit #{{ $unit['unit_number'] }}</h3>
                        <p style="color: rgba(255,255,255,0.4); font-size: 13px; margin: 2px 0 0;">{{ $buildingName }}</p>
                    </div>
                </div>

                {{-- Price section --}}
                <div style="padding: 20px 28px 16px; display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <p style="font-size: 10px; color: #9ca3af; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; margin: 0 0 4px;">Monthly Rent</p>
                        <p style="margin: 0; line-height: 1;">
                            <span style="font-size: 28px; font-weight: 800; color: #2360E8;">P {{ number_format($unit['price'], 2) }}</span>
                            <span style="font-size: 14px; font-weight: 500; color: #9ca3af; margin-left: 4px;">/ mo</span>
                        </p>
                    </div>
                    <span style="display: inline-flex; align-items: center; gap: 6px; border: 1.5px solid rgba(35,96,232,0.35); color: #2360E8; font-size: 12px; font-weight: 600; border-radius: 9999px; padding: 6px 14px;">
                        <span style="width: 8px; height: 8px; border-radius: 50%; background: #2360E8;"></span>
                        Available
                    </span>
                </div>

                {{-- Stats grid --}}
                <div style="padding: 0 28px 20px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                    @if($unit['living_area'])
                        <div style="border: 1px solid #e5e7eb; border-radius: 16px; padding: 20px 22px; transition: all 0.25s ease; cursor: default;"
                             onmouseenter="this.style.transform='translateY(-2px)';this.style.borderColor='rgba(35,96,232,0.3)';this.style.boxShadow='0 4px 12px rgba(35,96,232,0.08)';"
                             onmouseleave="this.style.transform='';this.style.borderColor='#e5e7eb';this.style.boxShadow='';">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                                <svg style="width: 18px; height: 18px; color: #9ca3af;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0112 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125.504-1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M12 10.875v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125M10.875 12c-.621 0-1.125.504-1.125 1.125M12 10.875c0-.621.504-1.125 1.125-1.125m0 0v-.375c0-.621.504-1.125 1.125-1.125M13.125 12c.621 0 1.125.504 1.125 1.125m-1.125-1.125c-.621 0-1.125.504-1.125 1.125m0 0v.375"/>
                                </svg>
                                <span style="font-size: 11px; color: #9ca3af; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em;">Area</span>
                            </div>
                            <p style="margin: 0; font-size: 26px; font-weight: 800; color: #1a3a6b; line-height: 1;">{{ $unit['living_area'] }} <span style="font-size: 13px; font-weight: 500; color: #9ca3af;">sqft</span></p>
                        </div>
                    @endif

                    @if($unit['bed_type'])
                        <div style="border: 1px solid #e5e7eb; border-radius: 16px; padding: 20px 22px; transition: all 0.25s ease; cursor: default;"
                             onmouseenter="this.style.transform='translateY(-2px)';this.style.borderColor='rgba(35,96,232,0.3)';this.style.boxShadow='0 4px 12px rgba(35,96,232,0.08)';"
                             onmouseleave="this.style.transform='';this.style.borderColor='#e5e7eb';this.style.boxShadow='';">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                                <svg style="width: 18px; height: 18px; color: #9ca3af;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 7.125C2.25 6.504 2.754 6 3.375 6h6c.621 0 1.125.504 1.125 1.125v3.75c0 .621-.504 1.125-1.125 1.125h-6a1.125 1.125 0 01-1.125-1.125v-3.75zM14.25 8.625c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v2.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 01-1.125-1.125v-2.25z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75V12h19.5v6.75m-19.5 0h19.5m-19.5 0v.75c0 .414.336.75.75.75h.75m17.25-1.5v.75c0 .414-.336.75-.75.75h-.75m-15 0h15"/>
                                </svg>
                                <span style="font-size: 11px; color: #9ca3af; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em;">Bed Type</span>
                            </div>
                            <p style="margin: 0; font-size: 26px; font-weight: 800; color: #1a3a6b; line-height: 1;">{{ $unit['bed_type'] }}</p>
                        </div>
                    @endif

                    @if($unit['room_cap'])
                        <div style="border: 1px solid #e5e7eb; border-radius: 16px; padding: 20px 22px; transition: all 0.25s ease; cursor: default;"
                             onmouseenter="this.style.transform='translateY(-2px)';this.style.borderColor='rgba(35,96,232,0.3)';this.style.boxShadow='0 4px 12px rgba(35,96,232,0.08)';"
                             onmouseleave="this.style.transform='';this.style.borderColor='#e5e7eb';this.style.boxShadow='';">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                                <svg style="width: 18px; height: 18px; color: #9ca3af;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128H5.228A2.25 2.25 0 013 16.878v0a4.125 4.125 0 017.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07m0 0a5.856 5.856 0 00-4.853-2.928 5.862 5.862 0 00-4.574 2.928m9.427 0V16.2M12 9.75a3 3 0 100-6 3 3 0 000 6zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                                </svg>
                                <span style="font-size: 11px; color: #9ca3af; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em;">Capacity</span>
                            </div>
                            <p style="margin: 0; font-size: 26px; font-weight: 800; color: #1a3a6b; line-height: 1;">{{ $unit['room_cap'] }} <span style="font-size: 13px; font-weight: 500; color: #9ca3af;">{{ $unit['room_cap'] > 1 ? 'persons' : 'person' }}</span></p>
                        </div>
                    @endif
                </div>

                {{-- Tags row --}}
                <div style="padding: 0 28px 24px; display: flex; flex-wrap: wrap; align-items: center; gap: 8px;">
                    @if($unit['furnishing'])
                        <span style="display: inline-flex; align-items: center; gap: 6px; background: #f3f4f6; border: 1px solid #e5e7eb; color: #4b5563; font-size: 12px; font-weight: 500; border-radius: 9999px; padding: 6px 14px; transition: transform 0.2s ease; cursor: default;"
                              onmouseenter="this.style.transform='translateY(-1px)';" onmouseleave="this.style.transform='';">
                            <svg style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                            </svg>
                            {{ $unit['furnishing'] }} unit
                        </span>
                    @endif
                    <span style="display: inline-flex; align-items: center; gap: 6px; background: #f3f4f6; border: 1px solid #e5e7eb; color: #4b5563; font-size: 12px; font-weight: 500; border-radius: 9999px; padding: 6px 14px; transition: transform 0.2s ease; cursor: default;"
                          onmouseenter="this.style.transform='translateY(-1px)';" onmouseleave="this.style.transform='';">
                        <svg style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5L7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5"/>
                        </svg>
                        {{ $unit['floor_number'] }}{{ $unit['floor_number'] == 1 ? 'st' : ($unit['floor_number'] == 2 ? 'nd' : ($unit['floor_number'] == 3 ? 'rd' : 'th')) }} Floor
                    </span>
                    <span style="display: inline-flex; align-items: center; gap: 6px; background: rgba(35,96,232,0.08); border: 1px solid rgba(35,96,232,0.3); color: #2360E8; font-size: 12px; font-weight: 500; border-radius: 9999px; padding: 6px 14px; transition: transform 0.2s ease; cursor: default;"
                          onmouseenter="this.style.transform='translateY(-1px)';" onmouseleave="this.style.transform='';">
                        <svg style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                        </svg>
                        @if($unit['occupants'] === 'Male') All Male
                        @elseif($unit['occupants'] === 'Female') All Female
                        @else Co-ed
                        @endif
                    </span>
                </div>
            </div>
        @endif

        {{-- ============ PROPERTY DETAILS (same layout as landlord PropertyDetails) ============ --}}
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

                {{-- HERO SECTION: Photo + Info side by side --}}
                <div class="flex flex-col sm:flex-row">

                    {{-- Left: Photo Gallery --}}
                    @if(count($photos) > 0)
                        <div class="sm:w-[45%] relative">
                            <div
                                class="relative h-56 sm:h-full sm:min-h-[280px] cursor-pointer group overflow-hidden"
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
                    <button @click="closeLightbox()" class="absolute top-4 right-4 z-10 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    <div class="absolute top-4 left-4 text-white/70 text-sm font-medium bg-white/10 backdrop-blur-sm rounded-full px-3 py-1">
                        <span x-text="lightboxIndex + 1"></span> / <span x-text="photos.length"></span>
                    </div>

                    <button x-show="photos.length > 1" @click.stop="prevPhoto()" class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-white/10 hover:bg-white/25 flex items-center justify-center text-white transition-all cursor-pointer">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
                        </svg>
                    </button>

                    <div class="max-w-5xl max-h-[85vh] flex items-center justify-center">
                        <img :src="photos[lightboxIndex]?.url" :alt="photos[lightboxIndex]?.name" class="max-w-full max-h-[85vh] object-contain rounded-lg shadow-2xl">
                    </div>

                    <button x-show="photos.length > 1" @click.stop="nextPhoto()" class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-white/10 hover:bg-white/25 flex items-center justify-center text-white transition-all cursor-pointer">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                        </svg>
                    </button>

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

        {{-- ============ UNIT AMENITIES ============ --}}
        @if(count($amenities) > 0)
            @php
                $amenityIcons = [
                    'Free_Wifi' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 017.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 011.06 0z"/>',
                    'Hot_Cold_Shower' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/>',
                    'Electric_Fan' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>',
                    'Water_Kettle' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 00.495-7.467 5.99 5.99 0 00-1.925 3.546 5.974 5.974 0 01-2.133-1A3.75 3.75 0 0012 18z"/>',
                    'Closet_Cabinet' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 6.878V6a2.25 2.25 0 012.25-2.25h7.5A2.25 2.25 0 0118 6v.878m-12 0c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 004.5 9v.878m13.5-3A2.25 2.25 0 0119.5 9v.878m0 0a2.246 2.246 0 00-.75-.128H5.25c-.263 0-.515.045-.75.128m15 0A2.25 2.25 0 0121 12v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6c0-.98.626-1.813 1.5-2.122"/>',
                    'Housekeeping' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"/>',
                    'Refrigerator' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5V18M15 7.5V18M3 16.811V8.69c0-.864.933-1.406 1.683-.977l7.108 4.061a1.125 1.125 0 010 1.954l-7.108 4.061A1.125 1.125 0 013 16.811z"/>',
                    'Microwave' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 7.5l16.5-4.125M12 6.75c-2.708 0-5.363.224-7.948.655C2.999 7.58 2.25 8.507 2.25 9.574v9.176A2.25 2.25 0 004.5 21h15a2.25 2.25 0 002.25-2.25V9.574c0-1.067-.75-1.994-1.802-2.169A48.329 48.329 0 0012 6.75zm-1.683 6.443l-.005.005-.006-.005.006-.005.005.005zm-.005 2.127l-.005-.006.005-.005.005.005-.005.006zm-2.116-.006l-.005.006-.006-.006.005-.005.006.005zm-.005-2.116l-.006-.005.006-.005.005.005-.005.005z"/>',
                    'Rice_Cooker' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z"/>',
                    'Dining_Table' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75l-1.5.75a3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0L3 16.5m15-12.75H6"/>',
                    'Utility_Subsidy' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>',
                    'AC_Unit' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/>',
                    'Induction_Cooker' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 00.495-7.467 5.99 5.99 0 00-1.925 3.546 5.974 5.974 0 01-2.133-1A3.75 3.75 0 0012 18z"/>',
                    'Washing_Machine' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 00-3.7-3.7 48.678 48.678 0 00-7.324 0 4.006 4.006 0 00-3.7 3.7c-.017.22-.032.441-.046.662M19.5 12l3-3m-3 3l-3-3m-12 3c0 1.232.046 2.453.138 3.662a4.006 4.006 0 003.7 3.7 48.656 48.656 0 007.324 0 4.006 4.006 0 003.7-3.7c.017-.22.032-.441.046-.662M4.5 12l3 3m-3-3l-3 3"/>',
                    'Access_Pool' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75l-1.5.75a3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0L3 16.5m15-12.75H6"/>',
                    'Access_Gym' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 8.25V18a2.25 2.25 0 002.25 2.25h13.5A2.25 2.25 0 0021 18V8.25m-18 0V6a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 6v2.25m-18 0h18M5.25 6h.008v.008H5.25V6zM7.5 6h.008v.008H7.5V6zm2.25 0h.008v.008H9.75V6z"/>',
                ];
            @endphp

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden p-5">
                <div class="flex items-center gap-1.5 mb-4">
                    <svg class="w-3.5 h-3.5 text-[#2360E8]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                    </svg>
                    <h4 class="text-xs font-semibold text-gray-900 uppercase tracking-wider">Unit Amenities</h4>
                    <span class="bg-gray-100 text-gray-500 text-[11px] font-bold rounded-full px-1.5 py-0.5">{{ count($amenities) }}</span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-2.5">
                    @foreach($amenities as $amenityKey)
                        <div class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 bg-[#F5F8FF]">
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 bg-[#2360E8] text-white">
                                <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    {!! $amenityIcons[$amenityKey] ?? '<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>' !!}
                                </svg>
                            </div>
                            <span class="text-sm font-semibold text-[#070589] leading-tight">
                                {{ ucwords(str_replace('_', ' ', $amenityKey)) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    @endif
</div>
