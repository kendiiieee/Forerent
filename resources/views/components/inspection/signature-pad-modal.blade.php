@props([
    'show' => false,
    'title' => 'E-Signature',
    'subtitle' => 'Draw your signature below using your mouse or finger',
    'signerName' => '',
    'signerRole' => '',
    'legalText' => 'By clicking "Apply Signature", I confirm that I have read and agree to all terms. This electronic signature is legally binding under RA 8792 (Electronic Commerce Act of 2000).',
    'wireCloseMethod' => 'closeSignatureModal',
    'wireSaveMethod' => 'saveSignature',
    'canvasRef' => 'sigCanvas',
])

@if($show)
    <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
         x-data="{
            pad: null,
            isEmpty: true,

            init() {
                this.loadLibrary().then(() => this.setupCanvas());
            },

            loadLibrary() {
                return new Promise((resolve) => {
                    if (window.SignaturePad) { resolve(); return; }
                    const script = document.createElement('script');
                    script.src = 'https://cdn.jsdelivr.net/npm/signature_pad@4.2.0/dist/signature_pad.umd.min.js';
                    script.onload = () => resolve();
                    document.head.appendChild(script);
                });
            },

            setupCanvas() {
                this.$nextTick(() => {
                    setTimeout(() => {
                        const canvas = this.$refs.{{ $canvasRef }};
                        if (!canvas) return;
                        const rect = canvas.getBoundingClientRect();
                        if (rect.width === 0 || rect.height === 0) {
                            setTimeout(() => this.setupCanvas(), 150);
                            return;
                        }
                        const ratio = Math.max(window.devicePixelRatio || 1, 1);
                        canvas.width = rect.width * ratio;
                        canvas.height = rect.height * ratio;
                        canvas.getContext('2d').scale(ratio, ratio);
                        this.pad = new SignaturePad(canvas, {
                            backgroundColor: 'rgba(255, 255, 255, 0)',
                            penColor: '#000000',
                            minWidth: 1,
                            maxWidth: 2.5,
                        });
                        this.pad.addEventListener('beginStroke', () => { this.isEmpty = false; });
                    }, 100);
                });
            },

            clearPad() {
                if (this.pad) { this.pad.clear(); this.isEmpty = true; }
            },

            submitSignature() {
                if (!this.pad || this.pad.isEmpty()) return;
                const dataUrl = this.pad.toDataURL('image/png');
                $wire.call('{{ $wireSaveMethod }}', dataUrl);
            }
         }"
    >
        <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl">
            {{-- Header --}}
            <div class="bg-gradient-to-r from-[#070589] to-[#2360E8] text-white p-5 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold">{{ $title }}</h2>
                    <p class="text-xs text-blue-200 mt-0.5">{{ $subtitle }}</p>
                </div>
                <button @click="$el.closest('.fixed').style.display='none'; $wire.{{ $wireCloseMethod }}()" class="text-white hover:text-blue-200 transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Signer info --}}
            @if($signerName)
            <div class="px-5 pt-4 pb-2">
                <div class="bg-gray-50 rounded-xl p-3 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-[#EEF2FF] flex items-center justify-center">
                        <svg class="w-4 h-4 text-[#2360E8]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-800">{{ $signerName }}</p>
                        <p class="text-[11px] text-gray-500">Signing as {{ $signerRole }}</p>
                    </div>
                </div>
            </div>
            @endif

            {{-- Canvas --}}
            <div class="px-5 py-3">
                <div class="border-2 border-gray-200 rounded-xl overflow-hidden bg-white relative" style="touch-action: none;">
                    <canvas
                        x-ref="{{ $canvasRef }}"
                        class="w-full cursor-crosshair"
                        style="height: 200px; display: block;"
                    ></canvas>
                    <div class="absolute bottom-10 left-8 right-8 border-b border-dashed border-gray-200 pointer-events-none"></div>
                    <div class="absolute bottom-6 left-1/2 -translate-x-1/2 pointer-events-none">
                        <span class="text-[11px] text-gray-300 uppercase tracking-wider" x-show="isEmpty">Sign here</span>
                    </div>
                </div>
            </div>

            {{-- Legal notice --}}
            <div class="px-5 pb-3">
                <p class="text-[11px] text-gray-400 leading-relaxed">{{ $legalText }}</p>
            </div>

            {{-- Actions --}}
            <div class="px-5 pb-5 flex items-center justify-between">
                <button @click="clearPad()" class="flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182"/></svg>
                    Clear
                </button>
                <div class="flex gap-2">
                    <button @click="$el.closest('.fixed').style.display='none'; $wire.{{ $wireCloseMethod }}()" class="px-5 py-2.5 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">Cancel</button>
                    <button @click="submitSignature()" :disabled="isEmpty" class="px-5 py-2.5 text-xs font-bold text-white bg-[#070589] hover:bg-[#000060] rounded-xl transition-colors disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        Apply Signature
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
