@props([
    'show' => false,
    'title' => 'Contract',
    'wireCloseMethod' => 'closeMoveInContract',
    'contractId' => 'contract-content',
    'hasSignatures' => false,
    'contractAgreed' => false,
    'statusText' => '',
    'wireDownloadMethod' => null,
])

@if($show)
    @php
        $modalName = 'leave-confirm-' . $contractId;
        // If fully signed OR no signatures at all → close directly. Only warn when partially signed.
        $canCloseDirectly = $contractAgreed || !$hasSignatures;
        $closeAction = $canCloseDirectly
            ? "\$el.closest('.fixed').style.display='none'; \$wire.{$wireCloseMethod}()"
            : "\$dispatch('open-modal', '{$modalName}')";
    @endphp
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
         x-data>
        {{-- Modal Container --}}
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col mx-4 overflow-hidden">

            {{-- Header --}}
            <div class="flex-shrink-0 px-6 py-4 border-b border-gray-200 flex items-center justify-between" style="background: linear-gradient(135deg, #070589 0%, #2360E8 100%);">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background: rgba(255,255,255,0.15);">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h2 class="text-white font-bold text-lg">{{ $title }}</h2>
                </div>

                {{-- Close Button --}}
                <button @click="{{ $closeAction }}" class="text-white/80 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Contract Body (Scrollable) --}}
            <div class="flex-1 overflow-y-auto p-4 sm:p-8 space-y-4 sm:space-y-6 text-sm text-gray-800" id="{{ $contractId }}" style="font-family: 'Open Sans', sans-serif;">
                {{ $slot }}
            </div>

            {{-- Footer --}}
            <div class="flex-shrink-0 px-6 py-3 border-t border-gray-200 bg-gray-50 flex items-center justify-between">
                <p class="text-xs text-gray-400">{{ $statusText }}</p>
                <div class="flex gap-2 sm:gap-3">
                    {{ $footer ?? '' }}
                </div>
            </div>
        </div>
    </div>

    {{-- Leave Confirmation --}}
    <x-ui.modal-confirm
        name="{{ $modalName }}"
        title="Leave without signing?"
        description="This contract has not been signed yet. Are you sure you want to close it?"
        confirmText="Leave"
        cancelText="Stay"
        confirmAction="{{ $wireCloseMethod }}"
    />
@endif
