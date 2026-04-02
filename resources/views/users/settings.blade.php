@extends('layouts.app')

@section('header-title', 'SETTINGS')
@section('header-subtitle', 'Manage your account preferences and security protocols.')

@section('content')

    <div class="w-full h-full"
         x-data="{
            activeTab: 'personal-info',
            indicatorLeft: 0,
            indicatorWidth: 0,
            init() {
                this.$nextTick(() => this.updateIndicator());
            },
            updateIndicator() {
                const activeEl = this.$refs['tab-' + this.activeTab];
                if (activeEl) {
                    const container = this.$refs.tabContainer;
                    this.indicatorLeft = activeEl.offsetLeft;
                    this.indicatorWidth = activeEl.offsetWidth;
                }
            },
            switchTab(tab) {
                this.activeTab = tab;
                this.$nextTick(() => this.updateIndicator());
            }
         }"
         x-resize="updateIndicator()"
         style="font-family: 'Open Sans', sans-serif;"
    >

        {{-- TABS NAVIGATION --}}
        <div class="relative mb-8" x-ref="tabContainer">
            <div class="flex space-x-1 overflow-x-auto overflow-y-visible">

                <button
                    x-ref="tab-personal-info"
                    @click="switchTab('personal-info')"
                    class="relative whitespace-nowrap px-4 pb-3 pt-1 text-lg transition-colors duration-200 cursor-pointer"
                    :class="activeTab === 'personal-info' ? 'text-[#0C0B50] font-bold' : 'text-[#94A3B8] font-semibold hover:text-[#64748B]'"
                >
                    Personal Information
                </button>

                <button
                    x-ref="tab-security"
                    @click="switchTab('security')"
                    class="relative whitespace-nowrap px-4 pb-3 pt-1 text-lg transition-colors duration-200 cursor-pointer"
                    :class="activeTab === 'security' ? 'text-[#0C0B50] font-bold' : 'text-[#94A3B8] font-semibold hover:text-[#64748B]'"
                >
                    Security
                </button>

                @if(auth()->user()->role === 'tenant')
                    <button
                        x-ref="tab-my-unit"
                        @click="switchTab('my-unit')"
                        class="relative whitespace-nowrap px-4 pb-3 pt-1 text-lg transition-colors duration-200 cursor-pointer"
                        :class="activeTab === 'my-unit' ? 'text-[#0C0B50] font-bold' : 'text-[#94A3B8] font-semibold hover:text-[#64748B]'"
                    >
                        My Unit
                    </button>
                @endif

            </div>

            {{-- Bottom border --}}
            <div class="absolute bottom-0 left-0 right-0 h-[1px] bg-[#E2E8F0]"></div>

            {{-- Sliding indicator --}}
            <div
                class="absolute bottom-0 h-[3px] rounded-full bg-[#2360E8] transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)]"
                :style="'left: ' + indicatorLeft + 'px; width: ' + indicatorWidth + 'px;'"
                style="box-shadow: 0 4px 14px rgba(35, 96, 232, 0.4);"
            ></div>
        </div>

        {{-- TAB CONTENT --}}
        <div class="relative">
            <div
                x-show="activeTab === 'personal-info'"
                x-transition:enter="transition ease-out duration-250"
                x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
            >
                <livewire:actions.settings-form :key="'settings-profile-' . auth()->id()" />
            </div>

            <div
                x-show="activeTab === 'security'"
                x-transition:enter="transition ease-out duration-250"
                x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
            >
                <livewire:layouts.settings.security-form :key="'settings-security-' . auth()->id()" />
            </div>

            @if(auth()->user()->role === 'tenant')
                <div
                    x-show="activeTab === 'my-unit'"
                    x-transition:enter="transition ease-out duration-250"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                >
                    <livewire:layouts.settings.tenant-property-details :key="'settings-property-' . auth()->id()" />
                </div>
            @endif
        </div>
    </div>

@endsection
