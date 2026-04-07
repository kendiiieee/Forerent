<div x-data="{
    sidebarExpanded: true,
    mobileMenuOpen: false,
    toggleMobileSidebar() {
        this.mobileMenuOpen = !this.mobileMenuOpen;
        this.sidebarExpanded = true;
    },
    toggleDesktopSidebar() {
        this.sidebarExpanded = !this.sidebarExpanded;
    }
}"
x-init="
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) {
            mobileMenuOpen = false;
        }
    });
"
class="relative h-screen flex">
<div x-show="mobileMenuOpen"
     @click="mobileMenuOpen = false"
     x-transition:enter="transition-opacity ease-linear duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-linear duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
class="fixed inset-0 bg-gradient-to-br from-gray-900/50 via-gray-900/20 to-gray-900/30 backdrop-blur-[3px] z-40 lg:hidden"     style="display: none;">
</div>

    <button @click="toggleMobileSidebar()"
            class="fixed top-3 sm:top-4 left-3 sm:left-4 z-50 p-1.5 sm:p-2 rounded-lg bg-white shadow-lg lg:hidden hover:bg-gray-100 transition-colors">
        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>

    <nav :class="{
            '-translate-x-full': !mobileMenuOpen,
            'translate-x-0': mobileMenuOpen
         }"
         :style="`width: ${sidebarExpanded ? 256 : 80}px; transition: width 320ms cubic-bezier(0.4, 0, 0.2, 1), transform 300ms cubic-bezier(0.4, 0, 0.2, 1);`"
         class="fixed lg:relative left-0 top-0 h-full z-50 flex-shrink-0 bg-white border-r border-gray-200 will-change-[transform,width] lg:translate-x-0">

        <button @click="toggleDesktopSidebar()"
                class="absolute -right-3 top-10 z-50 hidden lg:flex items-center justify-center w-6 h-6 bg-white border border-gray-200 rounded-full shadow-sm hover:bg-gray-50 focus:outline-none transform transition-transform duration-300"
                :class="!sidebarExpanded ? 'rotate-180' : ''">
            <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>

        <aside class="h-full flex flex-col w-full">
           <div class="flex items-center justify-center py-6 px-4 border-b border-gray-100 flex-shrink-0">
                <a :href="sidebarExpanded ? '{{ route($navigations['dashboard']['route']) }}' : '#'"
                   class="transition-all duration-300 flex items-center gap-2">

                    <div x-show="sidebarExpanded"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-x-2"
                         x-transition:enter-end="opacity-100 translate-x-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-x-0"
                         x-transition:leave-end="opacity-0 -translate-x-2"
                         x-cloak>
                        <x-icons.logoprimary class="h-10 w-auto" />
                    </div>

                    <div x-show="!sidebarExpanded"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         x-cloak>
                        <x-icons.logosecondary class="w-10 h-10" />
                    </div>

                </a>
            </div>

            <div class="flex-1 px-3 py-4 overflow-y-auto overflow-x-hidden custom-scrollbar">
                <ul class="space-y-2 font-medium">
                    @foreach($navigations as $key => $navigation)
                        @if(isset($navigation['children']))
                            <li x-data="{ open: false }">
                                <button type="button"
                                        @click="
                                            if (!sidebarExpanded) {
                                                sidebarExpanded = true;
                                                setTimeout(() => open = true, 50);
                                            } else {
                                                open = !open;
                                            }
                                        "
                                        class="group flex items-center w-full p-3 rounded-lg text-[#6B7280] hover:bg-[#DFE8FC] hover:text-[#070642] transition-all duration-200 hover:translate-x-1 hover:shadow-sm active:scale-[0.98] relative"
                                        :class="!sidebarExpanded && 'justify-center'"
                                        :title="!sidebarExpanded ? '{{ $navigation['label'] }}' : ''">

                                    <x-dynamic-component :component="$navigation['icon']" class="w-5 h-5 flex-shrink-0 text-[#6B7280] transition-transform duration-200 group-hover:scale-110 group-hover:text-[#070642]" />

                                    <span x-show="sidebarExpanded"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 -translate-x-2"
                                        x-transition:enter-end="opacity-100 translate-x-0"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 translate-x-0"
                                        x-transition:leave-end="opacity-0 -translate-x-2"
                                          class="flex-1 ms-3 text-left whitespace-nowrap">
                                        {{ $navigation['label'] }}
                                    </span>

                                    <svg x-show="sidebarExpanded"
                                         :class="open && 'rotate-180'"
                                         class="w-3 h-3 transition-transform"
                                         xmlns="http://www.w3.org/2000/svg"
                                         fill="none"
                                         viewBox="0 0 10 6">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="m1 1 4 4 4-4" />
                                    </svg>
                                </button>

                                <ul x-show="open && sidebarExpanded"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 -translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 -translate-y-1"
                                    class="py-2 space-y-2">
                                    @foreach ($navigation['children'] as $child)
                                        <li>
                                            <a href="{{ route($navigation['route'], $child['query'] ?? []) }}"
                                               class="group flex items-center w-full p-2 pl-11 rounded-lg text-[#6B7280] hover:bg-[#DFE8FC] hover:text-[#070642] transition-all duration-200 hover:translate-x-1">
                                                {{ $child['label'] }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @else
                            <li>
                                <a href="{{ route($navigation['route']) }}"
                                   class="group flex items-center p-3 rounded-lg {{ $this->getActiveClass($navigation['route']) }} transition-all duration-200 hover:translate-x-1 hover:shadow-sm active:scale-[0.98]"
                                   :class="!sidebarExpanded && 'justify-center'"
                                   :title="!sidebarExpanded ? '{{ $navigation['label'] }}' : ''">
                                    <x-dynamic-component :component="$navigation['icon']" class="flex-shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs($navigation['route']) ? 'text-[#070642]' : 'text-[#6B7280] group-hover:text-[#070642]' }}" />
                                    <span x-show="sidebarExpanded"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 -translate-x-2"
                                        x-transition:enter-end="opacity-100 translate-x-0"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 translate-x-0"
                                        x-transition:leave-end="opacity-0 -translate-x-2"
                                          class="ms-3 whitespace-nowrap">
                                        {{ $navigation['label'] }}
                                    </span>
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>

                <div class="border-t border-gray-200 my-4"></div>

                <p x-show="sidebarExpanded"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-x-2"
                         x-transition:enter-end="opacity-100 translate-x-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-x-0"
                         x-transition:leave-end="opacity-0 -translate-x-2"
                   class="px-3 mb-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    Others
                </p>
                <div x-show="!sidebarExpanded" class="h-px bg-gray-200 mx-3 mb-3"></div>

                <ul class="space-y-2 font-medium">
                    <li>
                        <a href="{{ route('settings') }}"
                           class="group flex items-center p-3 rounded-lg transition-all duration-200 hover:translate-x-1 hover:shadow-sm active:scale-[0.98] {{ request()->routeIs('settings') ? 'bg-[#DFE8FC] text-[#070642]' : 'text-[#6B7280] hover:bg-[#DFE8FC] hover:text-[#070642]' }}"
                           :class="!sidebarExpanded && 'justify-center'"
                           :title="!sidebarExpanded ? 'Settings' : ''">
                            <x-icons.settings class="flex-shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('settings') ? 'text-[#070642]' : 'text-[#6B7280] group-hover:text-[#070642]' }}" />
                            <span x-show="sidebarExpanded"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 -translate-x-2"
                                    x-transition:enter-end="opacity-100 translate-x-0"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 translate-x-0"
                                    x-transition:leave-end="opacity-0 -translate-x-2"
                                  class="ms-3 whitespace-nowrap">
                                Settings
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('logout') }}"
                           data-logout-trigger
                           class="group flex items-center p-3 text-[#6B7280] rounded-lg hover:bg-[#DFE8FC] hover:text-[#070642] transition-all duration-200 hover:translate-x-1 hover:shadow-sm active:scale-[0.98]"
                           :class="!sidebarExpanded && 'justify-center'"
                           :title="!sidebarExpanded ? 'Log Out' : ''">
                            <x-icons.logout class="flex-shrink-0 text-[#6B7280] transition-transform duration-200 group-hover:scale-110 group-hover:text-[#070642]" />
                            <span x-show="sidebarExpanded"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 -translate-x-2"
                                    x-transition:enter-end="opacity-100 translate-x-0"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 translate-x-0"
                                    x-transition:leave-end="opacity-0 -translate-x-2"
                                  class="ms-3 whitespace-nowrap">
                                Log Out
                            </span>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Account User Display --}}
            <div class="border-t border-gray-200 px-3 py-4 flex-shrink-0">
                <a href="{{ url('settings') }}" wire:navigate
                   class="group flex items-center p-3 rounded-lg text-[#6B7280] hover:bg-[#DFE8FC] hover:text-[#070642] transition-all duration-200 hover:shadow-sm active:scale-[0.98]"
                   :class="!sidebarExpanded && 'justify-center'"
                   :title="!sidebarExpanded ? '{{ auth()->user()->first_name ?? 'Account' }}' : ''">
                    <div class="flex-shrink-0 w-9 h-9 rounded-full transition-transform duration-200 group-hover:scale-110 overflow-hidden border-2 border-blue-600">
                        @if(auth()->user()->profile_image_url)
                            <img src="{{ auth()->user()->profile_image_url }}" class="w-full h-full object-cover" alt="Profile">
                        @else
                            <div class="w-full h-full bg-white flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                                </svg>
                            </div>
                        @endif
                    </div>
                    <div x-show="sidebarExpanded"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-x-2"
                         x-transition:enter-end="opacity-100 translate-x-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-x-0"
                         x-transition:leave-end="opacity-0 -translate-x-2"
                         class="ms-3 overflow-hidden">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->first_name ?? '' }} {{ auth()->user()->last_name ?? '' }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ ucfirst(auth()->user()->role) }}</p>
                    </div>
                </a>
            </div>
        </aside>
    </nav>
</div>
