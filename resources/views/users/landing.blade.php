<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ForeRent – Predict Your Property Success</title>
    <link rel="icon" href="{{ asset('images/favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"></noscript>
    <link rel="preload" href="/images/Landing_Page_Bg.webp" as="image" type="image/webp">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Open Sans', 'sans-serif'],
                    },
                    opacity: { '85': '0.85' },
                }
            }
        }
    </script>
</head>
<body class="h-full font-sans overflow-x-hidden">


{{-- ═══════════════════════════════════════════════════════════════
     HERO
═══════════════════════════════════════════════════════════════ --}}
<section id="hero-section" class="relative w-full min-h-screen flex flex-col pt-[80px]">

    {{-- Background --}}
    <img src="/images/Landing_Page_Bg.webp" alt="Landing Page Background"
         class="absolute inset-0 w-full h-full object-cover object-center z-0"
         fetchpriority="high" decoding="async">

    {{-- Navbar --}}
    <nav id="mainNav"
         class="group fixed inset-x-0 top-0 z-[999] flex items-center justify-between px-16 py-4
                border-b border-white/[0.28] bg-white/[0.22]
                backdrop-blur-[20px] backdrop-saturate-[180%]
                transition-[box-shadow,background] duration-[350ms] ease-in-out
                will-change-[background,box-shadow]
                [&.scrolled]:bg-white/[0.38] [&.scrolled]:shadow-[0_4px_32px_rgba(0,0,0,0.14)]
                [&.nav-dark]:border-b-white/[0.28]
                [&.nav-light]:!bg-white/[0.92] [&.nav-light]:shadow-[0_2px_24px_rgba(0,0,0,0.12)] [&.nav-light]:!border-b-black/[0.08]">

        {{-- Logo --}}
        <a href="#" class="flex items-center gap-3 no-underline shrink-0">
            <img src="/images/ForeRent_Logo.svg" alt="ForeRent Logo"
                 class="h-14 w-auto transition-transform duration-300 drop-shadow-[0_2px_8px_rgba(26,63,191,0.18)] hover:scale-[1.06]">
        </a>

        {{-- Nav Links --}}
        @php
            $navCls = 'nav-link relative inline-block py-2 px-1 font-semibold text-[0.92rem] tracking-[0.4px] no-underline text-white/[0.82] transition-colors duration-200 hover:text-white after:content-[\'\'] after:absolute after:bottom-0 after:left-0 after:w-full after:h-0.5 after:bg-white after:scale-x-0 after:origin-left after:transition-transform after:duration-300 hover:after:scale-x-100 [&.active]:text-white [&.active]:font-bold [&.active]:after:scale-x-100 [&.active]:after:h-[2.5px] group-[.nav-dark]:text-white/85 group-[.nav-dark]:hover:text-white group-[.nav-dark]:after:bg-white group-[.nav-light]:!text-[#1a3fbf] group-[.nav-light]:hover:!text-[#0b1f6b] group-[.nav-light]:after:!bg-[#1a3fbf] group-[.nav-light]:[&.active]:!text-[#0b1f6b] group-[.nav-light]:[&.active]:font-bold';
        @endphp
        <ul class="flex items-center gap-10 list-none">
            <li><a href="#" class="{{ $navCls }} active">Home</a></li>
            <li><a href="#about" class="{{ $navCls }}">About</a></li>
            <li><a href="#features" class="{{ $navCls }}">Features</a></li>
            <li><a href="#faq" class="{{ $navCls }}">FAQ</a></li>
            <li><a href="#locations" class="{{ $navCls }}">Locations</a></li>
            <li><a href="#contact" class="{{ $navCls }}">Contacts</a></li>
        </ul>

        {{-- CTA --}}
        <div class="flex items-center shrink-0">
            <a href="/login"
               class="relative overflow-hidden px-7 py-[10px] rounded-full text-white text-[0.9rem] font-bold no-underline
                      transition-all duration-300 cursor-pointer
                      bg-[linear-gradient(135deg,#1a3fbf,#0b1f6b)] shadow-[0_4px_16px_rgba(26,63,191,0.38)]
                      after:content-[''] after:absolute after:top-[-50%] after:left-[-75%] after:w-1/2 after:h-[200%]
                      after:bg-[linear-gradient(120deg,transparent,rgba(255,255,255,0.3),transparent)]
                      after:-skew-x-[20deg] after:transition-[left] after:duration-500
                      hover:after:left-[130%] hover:-translate-y-0.5 hover:shadow-[0_8px_24px_rgba(26,63,191,0.52)]">
                Log In
            </a>
        </div>
    </nav>

    {{-- Hero body --}}
    <div class="relative z-[2] flex-1 flex items-center justify-end px-16 pt-16 pb-32">
        <div class="w-full max-w-[490px] bg-white/[0.22] border border-white/[0.30] rounded-[20px] px-11 py-12
                    shadow-[0_8px_32px_rgba(0,0,0,0.18),inset_0_1px_0_rgba(255,255,255,0.35)]
                    backdrop-blur-[16px] backdrop-saturate-150">
            <h1 class="text-[2.25rem] font-extrabold text-white leading-tight mb-4">
                Let's Predict Your<br>
                Property Success
            </h1>
            <p class="text-[0.97rem] text-white/85 leading-[1.65] mb-9">
                We've created the smartest way to manage your property
                by creating the smartest way to predict.
            </p>
            <div class="flex gap-3.5 flex-wrap">
                <a href="#about" id="get-started-btn"
                   class="inline-flex items-center gap-2 px-8 py-[13px] rounded-[10px] text-white text-[0.92rem] font-bold
                          no-underline transition-all duration-200 cursor-pointer
                          bg-[linear-gradient(135deg,#1a3fbf_0%,#0b1f6b_100%)]
                          shadow-[0_4px_18px_rgba(26,63,191,0.45)]
                          hover:-translate-y-0.5 hover:shadow-[0_8px_24px_rgba(26,63,191,0.55)]">
                    Get Started
                </a>
            </div>
        </div>
    </div>

    {{-- Floating search bar (cascading dropdowns) --}}
    <div class="absolute bottom-0 left-1/2 -translate-x-1/2 translate-y-1/2 z-20 w-[calc(100%-120px)] max-w-[1100px]"
         x-data="searchBar()" x-init="init()">
        <form action="{{ route('landing') }}" method="GET"
              class="flex items-center bg-white rounded-[14px] shadow-[0_12px_40px_rgba(0,0,0,0.22)] pl-5 pr-2.5 py-2.5 gap-0">

            {{-- City / Address --}}
            <div class="flex-1 relative px-5 py-2 border-r border-gray-200 min-w-0">
                <input type="hidden" name="address" :value="address">
                <span class="text-[0.70rem] font-bold uppercase tracking-[0.8px] text-gray-400 mb-1 flex items-center gap-1">
                    City
                    <svg class="w-3 h-3 text-gray-400 transition-transform duration-200" :class="openDrop === 'city' && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/></svg>
                </span>
                <button type="button" @click="toggle('city')" @click.outside="closeDrop('city')"
                        class="w-full text-left font-semibold text-gray-800 font-sans text-[0.88rem] bg-transparent cursor-pointer border-none outline-none p-0 truncate">
                    <span x-text="address || 'Choose Location'" :class="!address && 'text-gray-400'"></span>
                </button>
                <div x-show="openDrop === 'city'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1"
                     class="absolute left-0 top-full mt-2 w-72 bg-white rounded-xl shadow-[0_12px_36px_rgba(0,0,0,0.15)] border border-gray-100 py-1.5 z-50 max-h-60 overflow-y-auto"
                     style="display: none;">
                    <button type="button" @click="selectAddress('')"
                            class="w-full text-left px-4 py-2.5 text-[0.85rem] text-gray-400 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150 cursor-pointer border-none bg-transparent font-sans">
                        Choose Location
                    </button>
                    <template x-for="addr in allAddresses" :key="addr">
                        <button type="button" @click="selectAddress(addr)"
                                class="w-full text-left px-4 py-2.5 text-[0.85rem] text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150 cursor-pointer border-none bg-transparent font-sans truncate"
                                :class="address === addr && 'bg-blue-50 text-blue-600 font-semibold'"
                                x-text="addr">
                        </button>
                    </template>
                </div>
            </div>

            {{-- Unit Type --}}
            <div class="flex-1 relative px-5 py-2 border-r border-gray-200 min-w-0">
                <input type="hidden" name="unit_type" :value="unitType">
                <span class="text-[0.70rem] font-bold uppercase tracking-[0.8px] text-gray-400 mb-1 flex items-center gap-1">
                    Unit Type
                    <svg class="w-3 h-3 text-gray-400 transition-transform duration-200" :class="openDrop === 'unitType' && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/></svg>
                </span>
                <button type="button" @click="toggle('unitType')" @click.outside="closeDrop('unitType')"
                        class="w-full text-left font-semibold text-gray-800 font-sans text-[0.88rem] bg-transparent cursor-pointer border-none outline-none p-0 truncate"
                        :class="!address && 'opacity-50 cursor-not-allowed'" :disabled="!address">
                    <span x-text="unitType || 'Any'" :class="!unitType && 'text-gray-400'"></span>
                </button>
                <div x-show="openDrop === 'unitType'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1"
                     class="absolute left-0 top-full mt-2 w-48 bg-white rounded-xl shadow-[0_12px_36px_rgba(0,0,0,0.15)] border border-gray-100 py-1.5 z-50"
                     style="display: none;">
                    <button type="button" @click="unitType = ''; openDrop = ''"
                            class="w-full text-left px-4 py-2.5 text-[0.85rem] text-gray-400 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150 cursor-pointer border-none bg-transparent font-sans">
                        Any
                    </button>
                    <template x-for="t in availableUnitTypes" :key="t">
                        <button type="button" @click="unitType = t; openDrop = ''"
                                class="w-full text-left px-4 py-2.5 text-[0.85rem] text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150 cursor-pointer border-none bg-transparent font-sans"
                                :class="unitType === t && 'bg-blue-50 text-blue-600 font-semibold'"
                                x-text="t">
                        </button>
                    </template>
                </div>
            </div>

            {{-- Price --}}
            <div class="flex-1 relative px-5 py-2 border-r border-gray-200 min-w-0">
                <input type="hidden" name="price" :value="priceValue">
                <span class="text-[0.70rem] font-bold uppercase tracking-[0.8px] text-gray-400 mb-1 flex items-center gap-1">
                    Price
                    <svg class="w-3 h-3 text-gray-400 transition-transform duration-200" :class="openDrop === 'price' && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/></svg>
                </span>
                <button type="button" @click="toggle('price')" @click.outside="closeDrop('price')"
                        class="w-full text-left font-semibold text-gray-800 font-sans text-[0.88rem] bg-transparent cursor-pointer border-none outline-none p-0 truncate"
                        :class="!address && 'opacity-50 cursor-not-allowed'" :disabled="!address">
                    <span x-text="priceLabel || 'Any Price'" :class="!priceLabel && 'text-gray-400'"></span>
                </button>
                <div x-show="openDrop === 'price'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1"
                     class="absolute left-0 top-full mt-2 w-56 bg-white rounded-xl shadow-[0_12px_36px_rgba(0,0,0,0.15)] border border-gray-100 py-1.5 z-50"
                     style="display: none;">
                    <button type="button" @click="priceValue = ''; priceLabel = ''; openDrop = ''"
                            class="w-full text-left px-4 py-2.5 text-[0.85rem] text-gray-400 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150 cursor-pointer border-none bg-transparent font-sans">
                        Any Price
                    </button>
                    <template x-for="range in availablePriceRanges" :key="range.value">
                        <button type="button" @click="priceValue = range.value; priceLabel = range.label; openDrop = ''"
                                class="w-full text-left px-4 py-2.5 text-[0.85rem] text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150 cursor-pointer border-none bg-transparent font-sans"
                                :class="priceValue === range.value && 'bg-blue-50 text-blue-600 font-semibold'"
                                x-text="range.label">
                        </button>
                    </template>
                </div>
            </div>

            {{-- Room Type --}}
            <div class="flex-1 relative px-5 py-2 min-w-0">
                <input type="hidden" name="furnishing" :value="roomType">
                <span class="text-[0.70rem] font-bold uppercase tracking-[0.8px] text-gray-400 mb-1 flex items-center gap-1">
                    Room Type
                    <svg class="w-3 h-3 text-gray-400 transition-transform duration-200" :class="openDrop === 'roomType' && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/></svg>
                </span>
                <button type="button" @click="toggle('roomType')" @click.outside="closeDrop('roomType')"
                        class="w-full text-left font-semibold text-gray-800 font-sans text-[0.88rem] bg-transparent cursor-pointer border-none outline-none p-0 truncate"
                        :class="!address && 'opacity-50 cursor-not-allowed'" :disabled="!address">
                    <span x-text="roomType || 'Any'" :class="!roomType && 'text-gray-400'"></span>
                </button>
                <div x-show="openDrop === 'roomType'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1"
                     class="absolute left-0 top-full mt-2 w-52 bg-white rounded-xl shadow-[0_12px_36px_rgba(0,0,0,0.15)] border border-gray-100 py-1.5 z-50"
                     style="display: none;">
                    <button type="button" @click="roomType = ''; openDrop = ''"
                            class="w-full text-left px-4 py-2.5 text-[0.85rem] text-gray-400 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150 cursor-pointer border-none bg-transparent font-sans">
                        Any
                    </button>
                    <template x-for="rt in availableRoomTypes" :key="rt">
                        <button type="button" @click="roomType = rt; openDrop = ''"
                                class="w-full text-left px-4 py-2.5 text-[0.85rem] text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150 cursor-pointer border-none bg-transparent font-sans"
                                :class="roomType === rt && 'bg-blue-50 text-blue-600 font-semibold'"
                                x-text="rt">
                        </button>
                    </template>
                </div>
            </div>

            <button type="submit" class="shrink-0 ml-3 w-14 h-14 rounded-[10px] border-none text-white flex items-center justify-center
                           transition-all duration-200 hover:opacity-90 cursor-pointer
                           bg-[linear-gradient(135deg,#1a3fbf_0%,#0b1f6b_100%)]">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <circle cx="11" cy="11" r="7"/><path d="m21 21-4.35-4.35" stroke-linecap="round"/>
                </svg>
            </button>
        </form>
    </div>

    <script>
        function searchBar() {
            const allPriceRanges = [
                { value: '0-3000',     label: '₱1,000 – ₱3,000',   min: 0,     max: 3000 },
                { value: '3000-6000',  label: '₱3,000 – ₱6,000',   min: 3000,  max: 6000 },
                { value: '6000-10000', label: '₱6,000 – ₱10,000',  min: 6000,  max: 10000 },
                { value: '10000-20000',label: '₱10,000 – ₱20,000', min: 10000, max: 20000 },
                { value: '20000+',     label: '₱20,000+',          min: 20000, max: Infinity },
            ];

            return {
                properties: @js($propertyData),
                allAddresses: @js($addresses),
                openDrop: '',

                address: @js(request('address', '')),
                unitType: @js(request('unit_type', '')),
                priceValue: @js(request('price', '')),
                priceLabel: '',
                roomType: @js(request('furnishing', '')),

                availableUnitTypes: [],
                availableRoomTypes: [],
                availablePriceRanges: [],

                init() {
                    if (this.priceValue) {
                        const found = allPriceRanges.find(r => r.value === this.priceValue);
                        if (found) this.priceLabel = found.label;
                    }
                    if (this.address) this.updateOptions();
                },

                toggle(name) {
                    if (name !== 'city' && !this.address) return;
                    this.openDrop = this.openDrop === name ? '' : name;
                },
                closeDrop(name) {
                    if (this.openDrop === name) this.openDrop = '';
                },

                selectAddress(addr) {
                    this.address = addr;
                    this.unitType = '';
                    this.priceValue = '';
                    this.priceLabel = '';
                    this.roomType = '';
                    this.openDrop = '';
                    this.updateOptions();
                },

                updateOptions() {
                    const prop = this.properties.find(p => p.address === this.address);
                    if (!prop) {
                        this.availableUnitTypes = [];
                        this.availableRoomTypes = [];
                        this.availablePriceRanges = [];
                        return;
                    }
                    this.availableUnitTypes = prop.unitTypes;
                    this.availableRoomTypes = prop.roomTypes;

                    const prices = prop.prices;
                    if (prices.length === 0) {
                        this.availablePriceRanges = [];
                        return;
                    }
                    this.availablePriceRanges = allPriceRanges.filter(range =>
                        prices.some(p => {
                            const price = parseFloat(p);
                            return price >= range.min && (range.max === Infinity || price <= range.max);
                        })
                    );
                }
            };
        }
    </script>

</section>

{{-- ═══════════════════════════════════════════════════════════════
     SEARCH RESULTS
═══════════════════════════════════════════════════════════════ --}}
@if(!empty($hasSearch))
<section id="search-results" class="bg-[#f8faff] pt-24 pb-16">
    <div class="max-w-[1200px] mx-auto px-8">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-2xl font-extrabold text-[#0b1f6b]">Search Results</h2>
                <p class="text-sm text-gray-500 mt-1">{{ $units->total() }} {{ Str::plural('unit', $units->total()) }} found</p>
            </div>
            <a href="{{ route('landing') }}" class="text-sm font-semibold text-[#1a3fbf] hover:underline">Clear Filters</a>
        </div>

        @if($units->isEmpty())
            <div class="text-center py-24">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                </svg>
                <p class="text-gray-400 text-lg font-semibold">No units match your filters</p>
                <p class="text-gray-400 text-sm mt-1">Try adjusting your search criteria</p>
            </div>
        @else
            {{-- Results grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-7">
                @foreach($units as $unit)
                    @php $photo = $unit->property->photos->first(); @endphp
                    <div class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-100 group">
                        {{-- Photo --}}
                        <div class="relative h-52 bg-gray-100 overflow-hidden">
                            @if($photo)
                                <img src="{{ asset('storage/' . $photo->file_path) }}"
                                     alt="{{ $unit->property->building_name }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">
                                    <svg class="w-16 h-16 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 7.5h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        {{-- Card body --}}
                        <div class="p-5">
                            {{-- Address --}}
                            <div class="flex items-start gap-1.5 mb-3">
                                <svg class="w-4 h-4 text-[#1a3fbf] mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                </svg>
                                <span class="text-sm text-gray-600 leading-snug">{{ $unit->property->address }}</span>
                            </div>

                            {{-- Info row --}}
                            <div class="flex items-center gap-4 text-xs text-gray-500 mb-4">
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0"/>
                                    </svg>
                                    {{ $unit->occupants }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545"/>
                                    </svg>
                                    {{ $unit->room_type ?? 'N/A' }}
                                </span>
                                @if($unit->living_area)
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15"/>
                                    </svg>
                                    {{ $unit->living_area }} sqft
                                </span>
                                @endif
                            </div>

                            {{-- Divider --}}
                            <div class="border-t border-gray-100 pt-4 flex items-center justify-between">
                                <span class="text-xl font-extrabold text-[#0b1f6b]">₱{{ number_format($unit->price, 0) }}<span class="text-xs font-normal text-gray-400 ml-1">/month</span></span>
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="10" r="5"/></svg>
                                    {{ $unit->beds->count() }} {{ Str::plural('bed', $unit->beds->count()) }} available
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-10">
                {{ $units->links() }}
            </div>
        @endif
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════════════════════
     BELOW HERO
═══════════════════════════════════════════════════════════════ --}}
<div class="{{ !empty($hasSearch) ? 'pt-0' : 'pt-[90px]' }} bg-[#f8faff]">

    {{-- Stats --}}
    <div class="flex justify-center max-w-[1100px] mx-auto px-16 pt-14 pb-12">
        <div class="flex-1 text-center px-8 border-r border-[#dde3f0]">
            <div class="text-[2.4rem] font-extrabold text-[#0b1f6b] leading-none mb-1.5">12<span class="text-[#1a3fbf]">K+</span></div>
            <div class="text-[0.84rem] text-gray-500 font-medium">Properties Listed</div>
        </div>
        <div class="flex-1 text-center px-8 border-r border-[#dde3f0]">
            <div class="text-[2.4rem] font-extrabold text-[#0b1f6b] leading-none mb-1.5">8<span class="text-[#1a3fbf]">K+</span></div>
            <div class="text-[0.84rem] text-gray-500 font-medium">Happy Tenants</div>
        </div>
        <div class="flex-1 text-center px-8 border-r border-[#dde3f0]">
            <div class="text-[2.4rem] font-extrabold text-[#0b1f6b] leading-none mb-1.5">95<span class="text-[#1a3fbf]">%</span></div>
            <div class="text-[0.84rem] text-gray-500 font-medium">Prediction Accuracy</div>
        </div>
        <div class="flex-1 text-center px-8">
            <div class="text-[2.4rem] font-extrabold text-[#0b1f6b] leading-none mb-1.5">50<span class="text-[#1a3fbf]">+</span></div>
            <div class="text-[0.84rem] text-gray-500 font-medium">Cities Covered</div>
        </div>
    </div>

    {{-- Neural Architecture Section --}}
    <section id="about" class="relative overflow-hidden py-20 px-16 bg-[linear-gradient(135deg,#0f172a_0%,#1e3a5f_100%)]">

        {{-- Radial glow (replaces ::before) --}}
        <div class="absolute -top-[40%] -right-[10%] w-[500px] h-[500px] rounded-full pointer-events-none
                    bg-[radial-gradient(circle,rgba(96,165,250,0.08)_0%,transparent_70%)]"></div>

        <div class="max-w-[1100px] mx-auto relative z-[1]">

            {{-- Header --}}
            <div class="flex items-end justify-between gap-16 mb-[68px]">
                <div class="flex-none">
                    <div class="inline-block text-[0.70rem] font-bold tracking-[2px] uppercase text-[#60a5fa] mb-4">
                        Neural Architecture
                    </div>
                    <h2 class="text-[2.8rem] font-extrabold text-white leading-tight max-w-[600px]">
                        Beyond Data.<br>
                        <span class="text-[#60a5fa] italic">Pure Intelligence.</span>
                    </h2>
                </div>
                <div class="flex-none pl-12 border-l border-[rgba(96,165,250,0.3)] pb-2">
                    <p class="text-[0.95rem] text-white/70 leading-relaxed max-w-[420px]">
                        Our proprietary Intelligence Layer transforms raw property
                        metrics into executable strategic directives.
                    </p>
                </div>
            </div>

            {{-- Cards Grid --}}
            <div class="grid grid-cols-3 gap-8">

                {{-- Module S1 --}}
                <div class="group/card relative overflow-hidden rounded-2xl p-10 cursor-pointer
                            border border-[rgba(96,165,250,0.2)] bg-[rgba(30,58,95,0.6)]
                            transition-all duration-300 ease-in-out
                            hover:bg-[rgba(96,165,250,0.15)] hover:border-[rgba(96,165,250,0.5)]
                            hover:-translate-y-1.5 hover:shadow-[0_20px_40px_rgba(96,165,250,0.2)]">
                    {{-- Hover shimmer (replaces ::before) --}}
                    <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(96,165,250,0.15),rgba(139,92,246,0.15))]
                                opacity-0 group-hover/card:opacity-100 transition-opacity duration-[350ms] pointer-events-none rounded-2xl"></div>
                    <div class="relative z-[1]">
                        <div class="w-14 h-14 rounded-xl flex items-center justify-center mb-6
                                    bg-[linear-gradient(135deg,#60a5fa,#3b82f6)]">
                            <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                        </div>
                        <span class="inline-block text-[0.65rem] font-bold tracking-[1.8px] uppercase text-[#93c5fd] mb-3">MODULE S1</span>
                        <h3 class="text-[1.4rem] font-extrabold text-white leading-tight mb-4">Hierarchical Clustering</h3>
                        <p class="text-[0.88rem] text-white/70 leading-[1.68]">Group similar properties or maintenance requests to optimize resource allocation and enable proactive maintenance planning.</p>
                        <div class="flex justify-between items-center mt-6 pt-6 border-t border-[rgba(96,165,250,0.15)]">
                            <div class="flex gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-[rgba(96,165,250,0.4)] transition-colors duration-300 group-hover/card:bg-[#60a5fa]"></span>
                                <span class="w-1.5 h-1.5 rounded-full bg-[rgba(96,165,250,0.4)]"></span>
                                <span class="w-1.5 h-1.5 rounded-full bg-[rgba(96,165,250,0.4)]"></span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Module S2 --}}
                <div class="group/card relative overflow-hidden rounded-2xl p-10 cursor-pointer
                            border border-[rgba(96,165,250,0.2)] bg-[rgba(30,58,95,0.6)]
                            transition-all duration-300 ease-in-out
                            hover:bg-[rgba(96,165,250,0.15)] hover:border-[rgba(96,165,250,0.5)]
                            hover:-translate-y-1.5 hover:shadow-[0_20px_40px_rgba(96,165,250,0.2)]">
                    <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(96,165,250,0.15),rgba(139,92,246,0.15))]
                                opacity-0 group-hover/card:opacity-100 transition-opacity duration-[350ms] pointer-events-none rounded-2xl"></div>
                    <div class="relative z-[1]">
                        <div class="w-14 h-14 rounded-xl flex items-center justify-center mb-6
                                    bg-[linear-gradient(135deg,#60a5fa,#3b82f6)]">
                            <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="inline-block text-[0.65rem] font-bold tracking-[1.8px] uppercase text-[#93c5fd] mb-3">MODULE S2</span>
                        <h3 class="text-[1.4rem] font-extrabold text-white leading-tight mb-4">Rental Price Prediction</h3>
                        <p class="text-[0.88rem] text-white/70 leading-[1.68]">Utilize Multiple Regression to suggest optimal rental prices based on area, bedrooms, and location attributes.</p>
                        <div class="flex justify-between items-center mt-6 pt-6 border-t border-[rgba(96,165,250,0.15)]">
                            <div class="flex gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-[rgba(96,165,250,0.4)] transition-colors duration-300 group-hover/card:bg-[#60a5fa]"></span>
                                <span class="w-1.5 h-1.5 rounded-full bg-[rgba(96,165,250,0.4)]"></span>
                                <span class="w-1.5 h-1.5 rounded-full bg-[rgba(96,165,250,0.4)]"></span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Module S3 --}}
                <div class="group/card relative overflow-hidden rounded-2xl p-10 cursor-pointer
                            border border-[rgba(96,165,250,0.2)] bg-[rgba(30,58,95,0.6)]
                            transition-all duration-300 ease-in-out
                            hover:bg-[rgba(96,165,250,0.15)] hover:border-[rgba(96,165,250,0.5)]
                            hover:-translate-y-1.5 hover:shadow-[0_20px_40px_rgba(96,165,250,0.2)]">
                    <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(96,165,250,0.15),rgba(139,92,246,0.15))]
                                opacity-0 group-hover/card:opacity-100 transition-opacity duration-[350ms] pointer-events-none rounded-2xl"></div>
                    <div class="relative z-[1]">
                        <div class="w-14 h-14 rounded-xl flex items-center justify-center mb-6
                                    bg-[linear-gradient(135deg,#60a5fa,#3b82f6)]">
                            <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                        </div>
                        <span class="inline-block text-[0.65rem] font-bold tracking-[1.8px] uppercase text-[#93c5fd] mb-3">MODULE S3</span>
                        <h3 class="text-[1.4rem] font-extrabold text-white leading-tight mb-4">Financial Forecasting</h3>
                        <p class="text-[0.88rem] text-white/70 leading-[1.68]">Institutional-grade estimates for future rental income and maintenance costs so drive data-driven decisions.</p>
                        <div class="flex justify-between items-center mt-6 pt-6 border-t border-[rgba(96,165,250,0.15)]">
                            <div class="flex gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-[rgba(96,165,250,0.4)] transition-colors duration-300 group-hover/card:bg-[#60a5fa]"></span>
                                <span class="w-1.5 h-1.5 rounded-full bg-[rgba(96,165,250,0.4)]"></span>
                                <span class="w-1.5 h-1.5 rounded-full bg-[rgba(96,165,250,0.4)]"></span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- Unified Ecosystem Section --}}
    <section class="bg-[#f8faff] py-20 px-16 relative overflow-hidden">
        <div class="max-w-[1100px] mx-auto">

            {{-- Header --}}
            <div class="text-center mb-16">
                <span class="inline-block text-[0.72rem] font-bold tracking-[2.5px] uppercase text-[#1a3fbf] mb-3">Unified Ecosystem</span>
                <h2 class="text-[2.2rem] font-extrabold text-[#0b1f6b] leading-tight mb-3">
                    Three distinct modules, one seamless<br>loop of property productivity.
                </h2>
                <p class="text-[0.95rem] text-gray-500 leading-relaxed max-w-[520px] mx-auto">
                    Tailored solutions for property owners, managers, and empowered tenants.
                </p>
            </div>

            {{-- Cards --}}
            <div id="ecosystemCards" class="flex gap-6 items-stretch h-[520px] justify-start">

                {{-- The Visionary Owner --}}
                <div class="eco-card active relative rounded-[20px] overflow-hidden cursor-pointer
                            flex flex-col justify-end p-8 min-h-[520px] shadow-[0_8px_24px_rgba(0,0,0,0.1)]
                            bg-gradient-to-br from-[#f5f5f7] to-[#eeeff2]
                            [flex:0_0_18%] opacity-85
                            [transition:flex_0.5s_ease-in-out,opacity_0.5s_ease-in-out]
                            [&.active]:[flex:0_0_60%] [&.active]:opacity-100"
                     data-card="owner">
                    {{-- BG image layer (replaces ::before) --}}
                    <div class="eco-bg absolute inset-0 bg-cover bg-center opacity-35 transition-all duration-500 z-[1]
                                [.active_&]:opacity-70 [.active_&]:blur-[5px] [.active_&]:brightness-105"
                         data-bg="/images/Owner.webp"></div>
                    {{-- Overlay layer (replaces ::after) --}}
                    <div class="eco-overlay absolute inset-0 bg-white/65 opacity-100 transition-opacity duration-500 z-[2] pointer-events-none
                                [.active_&]:opacity-80 [.active_&]:bg-[linear-gradient(160deg,#1a2847_0%,#0f1a2e_100%)]"></div>
                    <div class="relative z-[3] flex flex-col h-full">
                        <span class="inline-block text-[0.65rem] font-bold tracking-[1.5px] uppercase
                                     px-[14px] py-[6px] rounded-full bg-white/20 text-white mb-4 w-fit
                                     opacity-0 transition-opacity duration-500 delay-100
                                     [.active_&]:opacity-100">
                            PROPERTY OWNER
                        </span>
                        <h3 class="text-[1.4rem] font-extrabold text-[#1a1a1a] leading-tight mb-4
                                   transition-all duration-500
                                   [.active_&]:text-white [.active_&]:text-[1.9rem]">
                            The Visionary<br>Owner
                        </h3>
                        <ul class="list-none flex flex-col gap-3 flex-1 mt-auto mb-4
                                   opacity-0 transition-opacity duration-500 delay-150
                                   [.active_&]:opacity-100">
                            <li class="flex items-center gap-2.5 text-[0.88rem] text-white/85">
                                <span class="inline-flex items-center justify-center w-[18px] h-[18px] min-w-[18px] bg-white/25 rounded-full text-white text-[0.72rem] font-bold">&check;</span>
                                AI-powered financial performance dashboard.
                            </li>
                            <li class="flex items-center gap-2.5 text-[0.88rem] text-white/85">
                                <span class="inline-flex items-center justify-center w-[18px] h-[18px] min-w-[18px] bg-white/25 rounded-full text-white text-[0.72rem] font-bold">&check;</span>
                                Secure centralized document vault.
                            </li>
                            <li class="flex items-center gap-2.5 text-[0.88rem] text-white/85">
                                <span class="inline-flex items-center justify-center w-[18px] h-[18px] min-w-[18px] bg-white/25 rounded-full text-white text-[0.72rem] font-bold">&check;</span>
                                Manager assignment and oversight controls.
                            </li>
                            <li class="flex items-center gap-2.5 text-[0.88rem] text-white/85">
                                <span class="inline-flex items-center justify-center w-[18px] h-[18px] min-w-[18px] bg-white/25 rounded-full text-white text-[0.72rem] font-bold">&check;</span>
                                Occupancy trend visualization and tracking.
                            </li>
                        </ul>
                        <a href="#" class="text-[0.75rem] font-bold tracking-[1px] uppercase
                                           text-[#1a3fbf] no-underline transition-opacity duration-500
                                           [.active_&]:opacity-0 [.active_&]:pointer-events-none">
                            Expand Module +
                        </a>
                    </div>
                </div>

                {{-- The Strategic Manager --}}
                <div class="eco-card relative rounded-[20px] overflow-hidden cursor-pointer
                            flex flex-col justify-end p-8 min-h-[520px] shadow-[0_8px_24px_rgba(0,0,0,0.1)]
                            bg-gradient-to-br from-[#f5f5f7] to-[#eeeff2]
                            [flex:0_0_18%] opacity-85
                            [transition:flex_0.5s_ease-in-out,opacity_0.5s_ease-in-out]
                            [&.active]:[flex:0_0_60%] [&.active]:opacity-100"
                     data-card="manager">
                    <div class="eco-bg absolute inset-0 bg-cover bg-center opacity-35 transition-all duration-500 z-[1]
                                [.active_&]:opacity-70 [.active_&]:blur-[5px] [.active_&]:brightness-105"
                         data-bg="/images/Manager.webp"></div>
                    <div class="eco-overlay absolute inset-0 bg-white/65 opacity-100 transition-opacity duration-500 z-[2] pointer-events-none
                                [.active_&]:opacity-80 [.active_&]:bg-[linear-gradient(160deg,#1a3fbf_0%,#0a2878_100%)]"></div>
                    <div class="relative z-[3] flex flex-col h-full">
                        <span class="inline-block text-[0.65rem] font-bold tracking-[1.5px] uppercase
                                     px-[14px] py-[6px] rounded-full bg-white/20 text-white mb-4 w-fit
                                     opacity-0 transition-opacity duration-500 delay-100
                                     [.active_&]:opacity-100">
                            PROPERTY MANAGER
                        </span>
                        <h3 class="text-[1.4rem] font-extrabold text-[#1a1a1a] leading-tight mb-4
                                   transition-all duration-500
                                   [.active_&]:text-white [.active_&]:text-[1.9rem]">
                            The Strategic<br>Manager
                        </h3>
                        <ul class="list-none flex flex-col gap-3 flex-1 mt-auto mb-4
                                   opacity-0 transition-opacity duration-500 delay-150
                                   [.active_&]:opacity-100">
                            <li class="flex items-center gap-2.5 text-[0.88rem] text-white/85">
                                <span class="inline-flex items-center justify-center w-[18px] h-[18px] min-w-[18px] bg-white/25 rounded-full text-white text-[0.72rem] font-bold">&check;</span>
                                Full tenant lifecycle management tools.
                            </li>
                            <li class="flex items-center gap-2.5 text-[0.88rem] text-white/85">
                                <span class="inline-flex items-center justify-center w-[18px] h-[18px] min-w-[18px] bg-white/25 rounded-full text-white text-[0.72rem] font-bold">&check;</span>
                                Integrated real-time messenger system.
                            </li>
                            <li class="flex items-center gap-2.5 text-[0.88rem] text-white/85">
                                <span class="inline-flex items-center justify-center w-[18px] h-[18px] min-w-[18px] bg-white/25 rounded-full text-white text-[0.72rem] font-bold">&check;</span>
                                Maintenance ticket &amp; technician tracking.
                            </li>
                            <li class="flex items-center gap-2.5 text-[0.88rem] text-white/85">
                                <span class="inline-flex items-center justify-center w-[18px] h-[18px] min-w-[18px] bg-white/25 rounded-full text-white text-[0.72rem] font-bold">&check;</span>
                                Rent collection &amp; automated reminders.
                            </li>
                        </ul>
                        <a href="#" class="text-[0.75rem] font-bold tracking-[1px] uppercase
                                           text-[#1a3fbf] no-underline transition-opacity duration-500
                                           [.active_&]:opacity-0 [.active_&]:pointer-events-none">
                            Expand Module +
                        </a>
                    </div>
                </div>

                {{-- The Empowered Tenant --}}
                <div class="eco-card relative rounded-[20px] overflow-hidden cursor-pointer
                            flex flex-col justify-end p-8 min-h-[520px] shadow-[0_8px_24px_rgba(0,0,0,0.1)]
                            bg-gradient-to-br from-[#f5f5f7] to-[#eeeff2]
                            [flex:0_0_18%] opacity-85
                            [transition:flex_0.5s_ease-in-out,opacity_0.5s_ease-in-out]
                            [&.active]:[flex:0_0_60%] [&.active]:opacity-100"
                     data-card="tenant">
                    <div class="eco-bg absolute inset-0 bg-cover bg-center opacity-35 transition-all duration-500 z-[1]
                                [.active_&]:opacity-70 [.active_&]:blur-[5px] [.active_&]:brightness-105"
                         data-bg="/images/Tenant.webp"></div>
                    <div class="eco-overlay absolute inset-0 bg-white/65 opacity-100 transition-opacity duration-500 z-[2] pointer-events-none
                                [.active_&]:opacity-80 [.active_&]:bg-[linear-gradient(160deg,#0a1c4d_0%,#051028_100%)]"></div>
                    <div class="relative z-[3] flex flex-col h-full">
                        <span class="inline-block text-[0.65rem] font-bold tracking-[1.5px] uppercase
                                     px-[14px] py-[6px] rounded-full bg-white/20 text-white mb-4 w-fit
                                     opacity-0 transition-opacity duration-500 delay-100
                                     [.active_&]:opacity-100">
                            TENANT
                        </span>
                        <h3 class="text-[1.4rem] font-extrabold text-[#1a1a1a] leading-tight mb-4
                                   transition-all duration-500
                                   [.active_&]:text-white [.active_&]:text-[1.9rem]">
                            The Empowered<br>Tenant
                        </h3>
                        <ul class="list-none flex flex-col gap-3 flex-1 mt-auto mb-4
                                   opacity-0 transition-opacity duration-500 delay-150
                                   [.active_&]:opacity-100">
                            <li class="flex items-center gap-2.5 text-[0.88rem] text-white/85">
                                <span class="inline-flex items-center justify-center w-[18px] h-[18px] min-w-[18px] bg-white/25 rounded-full text-white text-[0.72rem] font-bold">&check;</span>
                                One-click payment history &amp; receipt access.
                            </li>
                            <li class="flex items-center gap-2.5 text-[0.88rem] text-white/85">
                                <span class="inline-flex items-center justify-center w-[18px] h-[18px] min-w-[18px] bg-white/25 rounded-full text-white text-[0.72rem] font-bold">&check;</span>
                                Submit and track maintenance live status.
                            </li>
                            <li class="flex items-center gap-2.5 text-[0.88rem] text-white/85">
                                <span class="inline-flex items-center justify-center w-[18px] h-[18px] min-w-[18px] bg-white/25 rounded-full text-white text-[0.72rem] font-bold">&check;</span>
                                Instant broadcast announcements receiver.
                            </li>
                            <li class="flex items-center gap-2.5 text-[0.88rem] text-white/85">
                                <span class="inline-flex items-center justify-center w-[18px] h-[18px] min-w-[18px] bg-white/25 rounded-full text-white text-[0.72rem] font-bold">&check;</span>
                                Direct chat with property management staff.
                            </li>
                        </ul>
                        <a href="#" class="text-[0.75rem] font-bold tracking-[1px] uppercase
                                           text-[#1a3fbf] no-underline transition-opacity duration-500
                                           [.active_&]:opacity-0 [.active_&]:pointer-events-none">
                            Expand Module +
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- Features / Why ForeRent Section --}}
    <section id="features" class="bg-[#f0f4ff] py-16 px-16">
        <div class="max-w-[1100px] mx-auto">

            {{-- Header --}}
            <div class="text-center mb-14">
                <span class="inline-block text-[0.72rem] font-bold tracking-[2.5px] uppercase text-[#1a3fbf] mb-3">Why ForeRent</span>
                <h2 class="text-[2rem] font-extrabold text-[#0b1f6b] leading-tight">
                    Everything you need to<br>
                    <span class="text-[#1a3fbf]">rent smarter</span>
                </h2>
            </div>

            {{-- Cards Grid --}}
            <div class="grid grid-cols-3 gap-7">

                {{-- AI-Powered Predictions --}}
                <div class="bg-white border border-[#e8edf7] rounded-2xl p-9 transition-all duration-200
                            hover:-translate-y-1 hover:shadow-[0_12px_36px_rgba(11,31,107,0.10)]">
                    <div class="w-[52px] h-[52px] rounded-xl flex items-center justify-center mb-5
                                bg-gradient-to-br from-[#dbeafe] to-[#eff6ff]">
                        <svg class="w-[26px] h-[26px] text-[#1a3fbf]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h3 class="text-[1.05rem] font-bold text-[#0b1f6b] mb-2.5">AI-Powered Predictions</h3>
                    <p class="text-[0.88rem] text-gray-500 leading-[1.65]">Our model analyzes thousands of data points to forecast rental prices and market trends with high accuracy.</p>
                </div>

                {{-- Location Intelligence --}}
                <div class="bg-white border border-[#e8edf7] rounded-2xl p-9 transition-all duration-200
                            hover:-translate-y-1 hover:shadow-[0_12px_36px_rgba(11,31,107,0.10)]">
                    <div class="w-[52px] h-[52px] flex items-center justify-center rounded-xl mb-5 bg-gradient-to-br from-[#dbeafe] to-[#eff6ff]">
                        <svg class="w-[26px] h-[26px] text-[#1a3fbf]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-[1.05rem] font-bold text-[#0b1f6b] mb-2.5">Location Intelligence</h3>
                    <p class="text-[0.88rem] text-gray-500 leading-[1.65]">Explore rentals by neighborhood with detailed insights on accessibility, amenities, and nearby developments.</p>
                </div>

                {{-- Verified Listings --}}
                <div class="bg-white border border-[#e8edf7] rounded-2xl p-9 transition-all duration-200
                            hover:-translate-y-1 hover:shadow-[0_12px_36px_rgba(11,31,107,0.10)]">
                    <div class="w-[52px] h-[52px] flex items-center justify-center rounded-xl mb-5 bg-gradient-to-br from-[#dbeafe] to-[#eff6ff]">
                        <svg class="w-[26px] h-[26px] text-[#1a3fbf]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <h3 class="text-[1.05rem] font-bold text-[#0b1f6b] mb-2.5">Verified Listings</h3>
                    <p class="text-[0.88rem] text-gray-500 leading-[1.65]">Every property is verified to ensure accurate details, genuine photos, and legitimate landlord contacts.</p>
                </div>

                {{-- Price Fairness Score --}}
                <div class="bg-white border border-[#e8edf7] rounded-2xl p-9 transition-all duration-200
                            hover:-translate-y-1 hover:shadow-[0_12px_36px_rgba(11,31,107,0.10)]">
                    <div class="w-[52px] h-[52px] flex items-center justify-center rounded-xl mb-5 bg-gradient-to-br from-[#dbeafe] to-[#eff6ff]">
                        <svg class="w-[26px] h-[26px] text-[#1a3fbf]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-[1.05rem] font-bold text-[#0b1f6b] mb-2.5">Price Fairness Score</h3>
                    <p class="text-[0.88rem] text-gray-500 leading-[1.65]">Instantly see if a listing is fairly priced, overpriced, or a great deal compared to similar units in the area.</p>
                </div>

                {{-- Real-Time Alerts --}}
                <div class="bg-white border border-[#e8edf7] rounded-2xl p-9 transition-all duration-200
                            hover:-translate-y-1 hover:shadow-[0_12px_36px_rgba(11,31,107,0.10)]">
                    <div class="w-[52px] h-[52px] flex items-center justify-center rounded-xl mb-5 bg-gradient-to-br from-[#dbeafe] to-[#eff6ff]">
                        <svg class="w-[26px] h-[26px] text-[#1a3fbf]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <h3 class="text-[1.05rem] font-bold text-[#0b1f6b] mb-2.5">Real-Time Alerts</h3>
                    <p class="text-[0.88rem] text-gray-500 leading-[1.65]">Get notified the moment a new listing matching your criteria becomes available in your preferred area.</p>
                </div>

                {{-- Market Reports --}}
                <div class="bg-white border border-[#e8edf7] rounded-2xl p-9 transition-all duration-200
                            hover:-translate-y-1 hover:shadow-[0_12px_36px_rgba(11,31,107,0.10)]">
                    <div class="w-[52px] h-[52px] flex items-center justify-center rounded-xl mb-5 bg-gradient-to-br from-[#dbeafe] to-[#eff6ff]">
                        <svg class="w-[26px] h-[26px] text-[#1a3fbf]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-[1.05rem] font-bold text-[#0b1f6b] mb-2.5">Market Reports</h3>
                    <p class="text-[0.88rem] text-gray-500 leading-[1.65]">Access monthly reports on rental market trends, average prices, and forecasts by city and property type.</p>
                </div>

            </div>
        </div>
    </section>

    {{-- FAQ Section --}}
    <section id="faq" class="bg-white py-24 px-16">
        <div class="max-w-[1100px] mx-auto">
            <div class="flex gap-20 items-start">

                {{-- Left column: header --}}
                <div class="w-[380px] shrink-0 sticky top-32 pt-4">
                    <div class="flex items-center gap-2 mb-6">
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-[#eef2ff] text-[#1a3fbf]">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-[0.8rem] font-semibold">Got questions? We've got answers</span>
                        </span>
                    </div>

                    <h2 class="text-[2.6rem] font-extrabold text-[#0b1f6b] leading-[1.15] mb-5">
                        Frequently asked<br>
                        <span class="text-[#1a3fbf] italic">questions</span>
                    </h2>

                    <p class="text-[0.95rem] text-gray-400 leading-[1.7] max-w-[340px]">
                        Choose a plan that fits your property needs and budget. No hidden fees, no surprises &mdash; just straightforward tools for powerful property management.
                    </p>
                </div>

                {{-- Right column: accordion --}}
                <div id="faqAccordion" class="flex-1 flex flex-col gap-4">

                    {{-- Q1 --}}
                    <div class="faq-item bg-[#f8faff] border border-[#e8edf7] rounded-2xl overflow-hidden transition-shadow duration-300">
                        <button class="faq-trigger w-full flex items-center justify-between px-7 py-5 text-left cursor-pointer bg-transparent border-none">
                            <span class="text-[0.95rem] font-bold text-[#0b1f6b] pr-4">What is ForeRent?</span>
                            <span class="faq-icon w-9 h-9 rounded-full flex items-center justify-center shrink-0 transition-all duration-300 bg-[#e8edf7] text-[#1a3fbf]">
                                <svg class="faq-chevron w-4 h-4 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </span>
                        </button>
                        <div class="faq-panel" hidden>
                            <div class="px-7 pb-6 text-[0.88rem] text-gray-500 leading-[1.75]">
                                ForeRent is an AI-driven property management platform that connects property owners, managers, and tenants in one unified ecosystem. It uses machine learning to predict rental prices, forecast financial performance, and streamline day-to-day property operations.
                            </div>
                        </div>
                    </div>

                    {{-- Q2 --}}
                    <div class="faq-item bg-[#f8faff] border border-[#e8edf7] rounded-2xl overflow-hidden transition-shadow duration-300">
                        <button class="faq-trigger w-full flex items-center justify-between px-7 py-5 text-left cursor-pointer bg-transparent border-none">
                            <span class="text-[0.95rem] font-bold text-[#0b1f6b] pr-4">How does the AI rental price prediction work?</span>
                            <span class="faq-icon w-9 h-9 rounded-full flex items-center justify-center shrink-0 transition-all duration-300 bg-[#e8edf7] text-[#1a3fbf]">
                                <svg class="faq-chevron w-4 h-4 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </span>
                        </button>
                        <div class="faq-panel" hidden>
                            <div class="px-7 pb-6 text-[0.88rem] text-gray-500 leading-[1.75]">
                                Our system uses Multiple Regression analysis to suggest optimal rental prices based on property attributes such as floor area, number of bedrooms, location, and nearby amenities. The model is continuously trained on real market data to ensure high prediction accuracy.
                            </div>
                        </div>
                    </div>

                    {{-- Q3 --}}
                    <div class="faq-item bg-[#f8faff] border border-[#e8edf7] rounded-2xl overflow-hidden transition-shadow duration-300">
                        <button class="faq-trigger w-full flex items-center justify-between px-7 py-5 text-left cursor-pointer bg-transparent border-none">
                            <span class="text-[0.95rem] font-bold text-[#0b1f6b] pr-4">Who is ForeRent designed for?</span>
                            <span class="faq-icon w-9 h-9 rounded-full flex items-center justify-center shrink-0 transition-all duration-300 bg-[#e8edf7] text-[#1a3fbf]">
                                <svg class="faq-chevron w-4 h-4 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </span>
                        </button>
                        <div class="faq-panel" hidden>
                            <div class="px-7 pb-6 text-[0.88rem] text-gray-500 leading-[1.75]">
                                ForeRent serves three key user roles: <strong>Property Owners</strong> who want AI-powered financial dashboards and oversight controls, <strong>Property Managers</strong> who handle tenant lifecycle, maintenance tickets, and rent collection, and <strong>Tenants</strong> who need easy access to payments, maintenance requests, and direct communication with management.
                            </div>
                        </div>
                    </div>

                    {{-- Q4 --}}
                    <div class="faq-item bg-[#f8faff] border border-[#e8edf7] rounded-2xl overflow-hidden transition-shadow duration-300">
                        <button class="faq-trigger w-full flex items-center justify-between px-7 py-5 text-left cursor-pointer bg-transparent border-none">
                            <span class="text-[0.95rem] font-bold text-[#0b1f6b] pr-4">Is my data secure on ForeRent?</span>
                            <span class="faq-icon w-9 h-9 rounded-full flex items-center justify-center shrink-0 transition-all duration-300 bg-[#e8edf7] text-[#1a3fbf]">
                                <svg class="faq-chevron w-4 h-4 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </span>
                        </button>
                        <div class="faq-panel" hidden>
                            <div class="px-7 pb-6 text-[0.88rem] text-gray-500 leading-[1.75]">
                                Absolutely. ForeRent uses secure authentication, role-based access control, and encrypted data storage to protect all user information. Property documents are stored in a centralized vault accessible only to authorized users, and all financial transactions are processed through secure channels.
                            </div>
                        </div>
                    </div>

                    {{-- Q5 --}}
                    <div class="faq-item bg-[#f8faff] border border-[#e8edf7] rounded-2xl overflow-hidden transition-shadow duration-300">
                        <button class="faq-trigger w-full flex items-center justify-between px-7 py-5 text-left cursor-pointer bg-transparent border-none">
                            <span class="text-[0.95rem] font-bold text-[#0b1f6b] pr-4">How do tenants submit maintenance requests?</span>
                            <span class="faq-icon w-9 h-9 rounded-full flex items-center justify-center shrink-0 transition-all duration-300 bg-[#e8edf7] text-[#1a3fbf]">
                                <svg class="faq-chevron w-4 h-4 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </span>
                        </button>
                        <div class="faq-panel" hidden>
                            <div class="px-7 pb-6 text-[0.88rem] text-gray-500 leading-[1.75]">
                                Tenants can submit maintenance requests directly through their dashboard with a description and photos of the issue. Once submitted, they can track the live status of their request as it moves through review, assignment to a technician, and resolution. Managers receive instant notifications for every new ticket.
                            </div>
                        </div>
                    </div>

                    {{-- Q6 --}}
                    <div class="faq-item bg-[#f8faff] border border-[#e8edf7] rounded-2xl overflow-hidden transition-shadow duration-300">
                        <button class="faq-trigger w-full flex items-center justify-between px-7 py-5 text-left cursor-pointer bg-transparent border-none">
                            <span class="text-[0.95rem] font-bold text-[#0b1f6b] pr-4">Can I manage multiple properties on ForeRent?</span>
                            <span class="faq-icon w-9 h-9 rounded-full flex items-center justify-center shrink-0 transition-all duration-300 bg-[#e8edf7] text-[#1a3fbf]">
                                <svg class="faq-chevron w-4 h-4 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </span>
                        </button>
                        <div class="faq-panel" hidden>
                            <div class="px-7 pb-6 text-[0.88rem] text-gray-500 leading-[1.75]">
                                Yes. Property owners can register multiple buildings and units under a single account. Each property can be assigned its own manager, and the owner dashboard provides a consolidated view of financial performance, occupancy rates, and maintenance activity across all properties.
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>

</div>

{{-- ═══════════════════════════════════════════════════════════════
     LOCATIONS / MAP
═══════════════════════════════════════════════════════════════ --}}
<section id="locations" class="relative">
    <div class="flex min-h-[620px]">

        {{-- Left: Full map --}}
        <div class="w-[55%] relative" id="locationMap"></div>

        {{-- Right: Contact info --}}
        <div class="w-[45%] bg-white px-14 py-16 flex flex-col justify-center">

            <h2 class="text-[2.2rem] font-extrabold text-[#0b1f6b] leading-tight mb-3">
                Find Our<br>Dormitories
            </h2>
            <p class="text-[0.92rem] text-gray-400 leading-relaxed mb-10">
                We're here to help you find the perfect place to stay. Visit our managed dormitory buildings across Metro Manila.
            </p>

            {{-- Location 1 --}}
            <div class="flex items-start gap-4 mb-8">
                <span class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 bg-[#eef2ff] text-[#1a3fbf]">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 010-5 2.5 2.5 0 010 5z"/>
                    </svg>
                </span>
                <div>
                    <h3 class="text-[1rem] font-bold text-[#0b1f6b] mb-1">BGC Dormitory</h3>
                    <p class="text-[0.88rem] text-gray-500 leading-snug">26th St., Bonifacio Global City, Taguig City</p>
                </div>
            </div>

            {{-- Location 2 --}}
            <div class="flex items-start gap-4 mb-8">
                <span class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 bg-[#eef2ff] text-[#1a3fbf]">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 010-5 2.5 2.5 0 010 5z"/>
                    </svg>
                </span>
                <div>
                    <h3 class="text-[1rem] font-bold text-[#0b1f6b] mb-1">Makati Dormitory</h3>
                    <p class="text-[0.88rem] text-gray-500 leading-snug">Ayala Ave., Legazpi Village, Makati City</p>
                </div>
            </div>

            {{-- Email --}}
            <div class="flex items-start gap-4 mb-8">
                <span class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 bg-[#eef2ff] text-[#1a3fbf]">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </span>
                <div>
                    <h3 class="text-[1rem] font-bold text-[#0b1f6b] mb-1">Email us at</h3>
                    <p class="text-[0.88rem] text-gray-500">info@forerent.com</p>
                </div>
            </div>

            {{-- Phone --}}
            <div class="flex items-start gap-4 mb-10">
                <span class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 bg-[#eef2ff] text-[#1a3fbf]">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                </span>
                <div>
                    <h3 class="text-[1rem] font-bold text-[#0b1f6b] mb-1">Contact us at</h3>
                    <p class="text-[0.88rem] text-gray-500">Service Center: (02) 8123-4567</p>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════
     FOOTER
═══════════════════════════════════════════════════════════════ --}}
<footer id="contact" class="text-white bg-[linear-gradient(135deg,#0a0f1e_0%,#0d1b3e_100%)]">

    {{-- Main footer body --}}
    <div class="max-w-[1200px] mx-auto px-16 pt-16 pb-12">
        <div class="flex gap-16">

            {{-- Brand column --}}
            <div class="flex-none w-64">
                <a href="#" class="flex items-center no-underline mb-5">
                    <img src="/images/white_logo.svg" alt="ForeRent Logo" class="h-14 w-auto">
                </a>

                <p class="text-white/50 text-[0.88rem] leading-relaxed mb-8">
                    AI-powered dormitory and property management platform for owners, managers, and tenants.
                </p>

                {{-- Social icon buttons --}}
                <div class="flex gap-3">
                    {{-- Facebook --}}
                    <a href="#" class="w-10 h-10 rounded-full border border-white/20 bg-white/[0.06] hover:bg-white/[0.14] hover:border-[#3b82f6] flex items-center justify-center transition-all duration-200">
                        <svg class="w-4 h-4 text-white/70" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </a>
                    {{-- Instagram --}}
                    <a href="#" class="w-10 h-10 rounded-full border border-white/20 bg-white/[0.06] hover:bg-white/[0.14] hover:border-[#3b82f6] flex items-center justify-center transition-all duration-200">
                        <svg class="w-4 h-4 text-white/70" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
                        </svg>
                    </a>
                    {{-- Email --}}
                    <a href="#" class="w-10 h-10 rounded-full border border-white/20 bg-white/[0.06] hover:bg-white/[0.14] hover:border-[#3b82f6] flex items-center justify-center transition-all duration-200">
                        <svg class="w-4 h-4 text-white/70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Nav columns --}}
            <div class="flex-1 grid grid-cols-2 gap-8 pt-1">
                <div>
                    <h4 class="text-[0.68rem] font-bold tracking-[2.5px] uppercase text-[#3b82f6] mb-6">Contact</h4>
                    <ul class="list-none flex flex-col gap-4">
                        <li><span class="text-[0.78rem] font-bold tracking-[1.5px] uppercase text-white/55">info@forerent.com</span></li>
                        <li><span class="text-[0.78rem] font-bold tracking-[1.5px] uppercase text-white/55">(02) 8123-4567</span></li>
                        <li><span class="text-[0.78rem] font-bold tracking-[1.5px] uppercase text-white/55">Metro Manila, PH</span></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-[0.68rem] font-bold tracking-[2.5px] uppercase text-[#3b82f6] mb-6">Legal</h4>
                    <ul class="list-none flex flex-col gap-4">
                        <li><a href="/privacy-policy" class="text-[0.78rem] font-bold tracking-[1.5px] uppercase text-white/55 hover:text-white no-underline transition-colors duration-200">Privacy Policy</a></li>
                        <li><a href="/terms-of-service" class="text-[0.78rem] font-bold tracking-[1.5px] uppercase text-white/55 hover:text-white no-underline transition-colors duration-200">Terms of Service</a></li>
                        <li><a href="/data-protection" class="text-[0.78rem] font-bold tracking-[1.5px] uppercase text-white/55 hover:text-white no-underline transition-colors duration-200">Data Protection</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom bar --}}
    <div class="border-t border-white/[0.08] max-w-[1200px] mx-auto px-16 py-5 flex items-center justify-between">
        <span class="text-[0.68rem] font-bold tracking-[2px] uppercase text-white/35">
            &copy; {{ date('Y') }} ForeRent. All rights reserved.
        </span>
        <div class="flex items-center gap-8">
            <a href="#" class="text-[0.68rem] font-bold tracking-[2px] uppercase text-white/35 hover:text-white no-underline transition-colors duration-200">Facebook</a>
            <a href="#" class="text-[0.68rem] font-bold tracking-[2px] uppercase text-white/35 hover:text-white no-underline transition-colors duration-200">Instagram</a>
            <a href="#" class="text-[0.68rem] font-bold tracking-[2px] uppercase text-white/35 hover:text-white no-underline transition-colors duration-200">Email</a>
        </div>
    </div>

</footer>

@vite('resources/js/app.js')

<script>
    // ─── Auto-scroll to search results ───────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        const results = document.getElementById('search-results');
        if (results) {
            setTimeout(() => results.scrollIntoView({ behavior: 'smooth', block: 'start' }), 300);
        }
    });

    // ─── Set eco-card background images from data attributes ──────────────
    document.querySelectorAll('.eco-bg[data-bg]').forEach(el => {
        el.style.backgroundImage = `url('${el.dataset.bg}')`;
    });

    // ─── NAVBAR THEME: Light vs Dark based on scroll position ─────────────
    const DARK_SECTION_IDS = ['hero-section', 'about'];

    function updateNavTheme() {
        const nav = document.getElementById('mainNav');
        const scrollY = window.scrollY;
        const navBottom = nav.offsetHeight + scrollY;

        if (scrollY > 30) nav.classList.add('scrolled');
        else nav.classList.remove('scrolled');

        let isDark = true;

        document.querySelectorAll('section[id], #hero-section').forEach(section => {
            const rect = section.getBoundingClientRect();
            const top = rect.top + scrollY;
            const bottom = rect.bottom + scrollY;
            if (navBottom >= top && navBottom <= bottom) {
                isDark = DARK_SECTION_IDS.includes(section.id);
            }
        });

        const lightWrapper = document.querySelector('div.pt-\\[90px\\]');
        if (lightWrapper) {
            const r = lightWrapper.getBoundingClientRect();
            const top = r.top + scrollY;
            const bottom = r.bottom + scrollY;
            if (navBottom >= top && navBottom <= bottom) {
                let insideDarkSub = false;
                DARK_SECTION_IDS.forEach(id => {
                    const el = document.getElementById(id);
                    if (el && id !== 'hero-section') {
                        const er = el.getBoundingClientRect();
                        if (navBottom >= er.top + scrollY && navBottom <= er.bottom + scrollY) {
                            insideDarkSub = true;
                        }
                    }
                });
                if (!insideDarkSub) isDark = false;
            }
        }

        if (isDark) {
            nav.classList.remove('nav-light');
            nav.classList.add('nav-dark');
        } else {
            nav.classList.remove('nav-dark');
            nav.classList.add('nav-light');
        }
    }

    let ticking = false;
    window.addEventListener('scroll', function() {
        if (!ticking) {
            requestAnimationFrame(function() {
                updateNavTheme();
                ticking = false;
            });
            ticking = true;
        }
    }, { passive: true });
    window.addEventListener('load', updateNavTheme);
    document.addEventListener('DOMContentLoaded', updateNavTheme);

    // ─── SMOOTH SCROLL ─────────────────────────────────────────────────
    function animatedScrollTo(targetEl, linkEl) {
        if (!targetEl) return;

        document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
        if (linkEl) linkEl.classList.add('active');

        const yOffset = -document.getElementById('mainNav').offsetHeight;
        const targetY = targetEl.getBoundingClientRect().top + window.scrollY + yOffset;
        const startY = window.scrollY;
        const distance = targetY - startY;
        const duration = 1200;
        let startTime = null;

        function easeInOutCubic(t) {
            return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
        }

        function step(timestamp) {
            if (!startTime) startTime = timestamp;
            const elapsed = timestamp - startTime;
            const progress = Math.min(elapsed / duration, 1);
            window.scrollTo(0, startY + distance * easeInOutCubic(progress));
            if (progress < 1) requestAnimationFrame(step);
        }

        requestAnimationFrame(step);
    }

    document.addEventListener('DOMContentLoaded', function () {

        document.querySelectorAll('.nav-link').forEach(link => {
            const href = link.getAttribute('href');

            if (href === '#' || href === '' || link.textContent.trim() === 'Home') {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    animatedScrollTo(document.getElementById('hero-section'), this);
                });
            } else if (href === '#about' || link.textContent.trim() === 'About') {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    animatedScrollTo(document.getElementById('about'), this);
                });
            } else if (href && href.startsWith('#')) {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) animatedScrollTo(target, this);
                });
            }
        });

        // ─── GET STARTED SMOOTH SCROLL ───────────────────────────────────
        const getStartedBtn = document.getElementById('get-started-btn');
        if (getStartedBtn) {
            getStartedBtn.addEventListener('click', function (e) {
                e.preventDefault();
                animatedScrollTo(document.getElementById('about'), null);
            });
        }

        // ─── ECOSYSTEM CARD INTERACTIONS ──────────────────────────────────
        const ecoCards = document.querySelectorAll('.eco-card');
        ecoCards.forEach(card => {
            card.addEventListener('click', function (e) {
                e.preventDefault();
                ecoCards.forEach(c => c.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // ─── FAQ ACCORDION (MUI-style) ───────────────────────────────────
        const faqItems = document.querySelectorAll('#faqAccordion .faq-item');

        function collapsePanel(item) {
            const panel = item.querySelector('.faq-panel');
            const icon = item.querySelector('.faq-icon');
            const chevron = item.querySelector('.faq-chevron');

            // Animate height to 0
            panel.style.height = panel.scrollHeight + 'px';
            panel.offsetHeight; // force reflow
            panel.style.transition = 'height 300ms cubic-bezier(0.4, 0, 0.2, 1), opacity 200ms ease';
            panel.style.height = '0px';
            panel.style.opacity = '0';
            panel.style.overflow = 'hidden';

            // Reset icon
            icon.classList.remove('bg-[#1a3fbf]', 'text-white', 'shadow-[0_4px_12px_rgba(26,63,191,0.35)]');
            icon.classList.add('bg-[#e8edf7]', 'text-[#1a3fbf]');
            chevron.classList.remove('rotate-180');

            // Reset card shadow
            item.classList.remove('shadow-[0_4px_24px_rgba(26,63,191,0.10)]');

            panel.addEventListener('transitionend', function handler(e) {
                if (e.propertyName === 'height') {
                    panel.hidden = true;
                    panel.style.transition = '';
                    panel.style.height = '';
                    panel.style.opacity = '';
                    panel.style.overflow = '';
                    panel.removeEventListener('transitionend', handler);
                }
            });
        }

        function expandPanel(item) {
            const panel = item.querySelector('.faq-panel');
            const icon = item.querySelector('.faq-icon');
            const chevron = item.querySelector('.faq-chevron');

            // Reveal and measure
            panel.hidden = false;
            panel.style.height = '0px';
            panel.style.opacity = '0';
            panel.style.overflow = 'hidden';
            panel.offsetHeight; // force reflow

            const targetHeight = panel.scrollHeight;
            panel.style.transition = 'height 300ms cubic-bezier(0.4, 0, 0.2, 1), opacity 250ms ease 80ms';
            panel.style.height = targetHeight + 'px';
            panel.style.opacity = '1';

            // Active icon
            icon.classList.remove('bg-[#e8edf7]', 'text-[#1a3fbf]');
            icon.classList.add('bg-[#1a3fbf]', 'text-white', 'shadow-[0_4px_12px_rgba(26,63,191,0.35)]');
            chevron.classList.add('rotate-180');

            // Card shadow
            item.classList.add('shadow-[0_4px_24px_rgba(26,63,191,0.10)]');

            panel.addEventListener('transitionend', function handler(e) {
                if (e.propertyName === 'height') {
                    panel.style.transition = '';
                    panel.style.height = '';
                    panel.style.overflow = '';
                    panel.removeEventListener('transitionend', handler);
                }
            });
        }

        faqItems.forEach(item => {
            item.querySelector('.faq-trigger').addEventListener('click', function () {
                const isOpen = !item.querySelector('.faq-panel').hidden;

                // Collapse all open panels
                faqItems.forEach(other => {
                    if (!other.querySelector('.faq-panel').hidden) {
                        collapsePanel(other);
                    }
                });

                // If it wasn't open, expand it
                if (!isOpen) {
                    expandPanel(item);
                }
            });
        });
    });
</script>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const map = L.map('locationMap', {
            zoomControl: false,
            attributionControl: false,
            scrollWheelZoom: false,
            dragging: true,
        }).setView([14.5525, 121.035], 13);

        L.tileLayer('https://tiles.stadiamaps.com/tiles/alidade_smooth/{z}/{x}/{y}{r}.png', {
            maxZoom: 20,
        }).addTo(map);

        const blueIcon = L.divIcon({
            className: '',
            html: '<div style="width:40px;height:40px;border-radius:50%;background:#1a3fbf;border:4px solid #fff;box-shadow:0 2px 12px rgba(26,63,191,0.4);"></div>',
            iconSize: [40, 40],
            iconAnchor: [20, 20],
        });

        L.marker([14.5525, 121.0505], { icon: blueIcon })
            .addTo(map)
            .bindPopup('<strong style="color:#0b1f6b">BGC Dormitory</strong><br><span style="color:#6b7280;font-size:0.85rem">26th St., BGC, Taguig City</span>');

        L.marker([14.5570, 121.0225], { icon: blueIcon })
            .addTo(map)
            .bindPopup('<strong style="color:#0b1f6b">Makati Dormitory</strong><br><span style="color:#6b7280;font-size:0.85rem">Ayala Ave., Legazpi Village, Makati</span>');

        L.control.zoom({ position: 'bottomleft' }).addTo(map);
    });
</script>

</body>
</html>
