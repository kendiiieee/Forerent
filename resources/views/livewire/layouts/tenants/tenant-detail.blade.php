{{-- resources/views/livewire/layouts/tenant-detail.blade.php --}}

{{--
    Main parent container:
    - `flex flex-col` creates the vertical layout.
    - `h-full` ensures it fills the parent container from tenant.blade.php.
--}}
<div class="bg-white rounded-3xl shadow-sm border border-gray-100 flex flex-col h-full overflow-hidden">
    @if($currentTenant)
        {{-- Wrapper for the "content" state --}}
        <div class="flex flex-col h-full">

            {{-- 1. Fixed Header Card --}}
            <div class="flex-shrink-0 rounded-t-3xl z-10 overflow-hidden" style="background: linear-gradient(135deg, #070589 0%, #0a1ea8 40%, #2360E8 100%);">


                <div class="relative p-6">
                    {{-- Top row: label --}}
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: rgba(255,255,255,0.12);">
                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <span class="text-xs font-semibold uppercase tracking-widest" style="color: rgba(191,219,254,0.9);">Tenant Profile</span>
                    </div>

                    {{-- Name + Unit --}}
                    <div class="flex items-center gap-3 mb-3">
                        <h3 class="text-white font-bold text-2xl leading-tight">
                            {{ $currentTenant['personal_info']['first_name'] }} {{ $currentTenant['personal_info']['last_name'] }}
                        </h3>
                        <div class="rounded-lg px-3 py-1 flex items-center gap-1.5" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.1); backdrop-filter: blur(8px);">
                            <span class="text-sm font-medium" style="color: rgba(191,219,254,0.8);">Unit</span>
                            <span class="text-white text-sm font-bold">{{ $currentTenant['personal_info']['unit'] }}</span>
                        </div>
                    </div>

                    {{-- Info chips --}}
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="flex items-center gap-1.5 rounded-lg px-3 py-1.5" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.06);">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" style="color: #93c5fd;" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-xs truncate max-w-[220px]" style="color: rgba(255,255,255,0.85);">{{ $currentTenant['personal_info']['address'] }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 rounded-lg px-3 py-1.5" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.06);">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" style="color: #93c5fd;" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                            </svg>
                            <span class="text-xs" style="color: rgba(255,255,255,0.85);">{{ $currentTenant['personal_info']['property'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Scrollable Content Area (`flex-1 overflow-y-auto`) --}}
            <div class="flex-1 overflow-y-auto custom-scrollbar p-6 space-y-4 bg-gray-50">

                {{-- Helper function for label-value pairs --}}
                @php
                $detailItem = function($label, $value, $isGreen = false) {
                    return '
                        <div>
                            <label class="block text-sm font-medium text-gray-500">'.$label.'</label>
                            <p class="text-base font-semibold ' . ($isGreen ? 'text-green-600' : 'text-gray-900') . '">'.$value.'</p>
                        </div>
                    ';
                };
                @endphp

                {{-- Contact Details Card --}}
                <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
                    <h5 class="font-bold text-lg text-gray-900 mb-4">Contact Details</h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                        {!! $detailItem('Contact Number', $currentTenant['contact_info']['contact_number']) !!}
                        {!! $detailItem('Email', $currentTenant['contact_info']['email']) !!}
                    </div>
                </div>

                {{-- Rent Details Card --}}
                <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
                    <h5 class="font-bold text-lg text-gray-900 mb-4">Rent Details</h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                        {!! $detailItem('Bed Number', $currentTenant['rent_details']['bed_number']) !!}
                        {!! $detailItem('Dorm Type', $currentTenant['rent_details']['dorm_type']) !!}
                        {!! $detailItem('Start Date', \Carbon\Carbon::parse($currentTenant['rent_details']['lease_start_date'])->format('F j, Y')) !!}
                        {!! $detailItem('End Date', \Carbon\Carbon::parse($currentTenant['rent_details']['lease_end_date'])->format('F j, Y')) !!}
                        {!! $detailItem('Term', $currentTenant['rent_details']['lease_term']) !!}
                        {!! $detailItem('Shift', $currentTenant['rent_details']['shift']) !!}
                        {!! $detailItem('Auto Renew Contract', $currentTenant['rent_details']['auto_renew'] ? 'Yes' : 'No') !!}
                    </div>
                </div>

                {{-- Move In Details Card --}}
                <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
                    <h5 class="font-bold text-lg text-gray-900 mb-4">Move In Details</h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                        {!! $detailItem('Move-in Date', \Carbon\Carbon::parse($currentTenant['move_in_details']['move_in_date'])->format('F j, Y')) !!}
                        {!! $detailItem('Monthly Rate', 'P ' . number_format($currentTenant['move_in_details']['monthly_rate'], 2)) !!}
                        {!! $detailItem('Security Deposit', 'P ' . number_format($currentTenant['move_in_details']['security_deposit'], 2)) !!}
                        {!! $detailItem('Payment Status', $currentTenant['move_in_details']['payment_status'], $currentTenant['move_in_details']['payment_status'] === 'Paid') !!}
                    </div>
                </div>

                {{-- 3. Action Buttons (only for current tenants) --}}
                @if($viewingTab === 'current')
                    <div class="flex space-x-3 pt-4">
                        <button
                            type="button"
                            wire:click="transferTenant"
                            class="flex-1 py-3 px-4 rounded-lg font-semibold text-white bg-[#1080FC] hover:bg-[#0e74e3] transition-colors focus:outline-none focus:ring-2 focus:ring-[#1080FC] focus:ring-offset-2"
                        >
                            Transfer
                        </button>
                        <button
                            type="button"
                            wire:click="moveOutTenant"
                            class="flex-1 py-3 px-4 rounded-lg font-semibold text-white bg-[#070589] hover:bg-[#05046a] transition-colors focus:outline-none focus:ring-2 focus:ring-[#070589] focus:ring-offset-2"
                        >
                            Move Out
                        </button>
                    </div>
                @endif

            </div>
        </div>
    @else
        {{-- Empty State (Unchanged) --}}
        <div class="flex items-center justify-center h-full">
            <div class="text-center max-w-md p-6">
                {{-- User Icon --}}
                <div class="inline-flex items-center justify-center w-24 h-24 bg-blue-100 rounded-full mb-6">
                    <svg class="w-12 h-12 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                {{-- Message --}}
                <h3 class="text-2xl font-bold text-gray-900 mb-2">No Tenant Selected</h3>
                <p class="text-gray-600 text-lg mb-6">
                    Please select a tenant from the sidebar to view their details, lease information, and manage their tenancy.
                </p>
                {{-- Pointer Arrow --}}
                <div class="flex items-center justify-center gap-2 text-blue-600">
                    <svg class="w-6 h-6 animate-bounce -rotate-90" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                    </svg>
                    <span class="font-medium">Select a tenant from the left</span>
                </div>
            </div>
        </div>
    @endif

        <x-ui.modal-confirm
            name="move-out-confirmation"
            title="Move Out Tenant?"
            description="Are you sure you want to move out this tenant? Their lease will be marked as Expired and their bed will be freed."
            confirmText="Yes, Move Out"
            cancelText="Cancel"
            confirmAction="confirmMoveOut"
        />
</div>

{{-- Custom Scrollbar Styles (Copied from your navigation file) --}}
@push('styles')
<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 8px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #0039C6; /* Blue scrollbar from image */
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #002A8F;
    }
</style>
@endpush
