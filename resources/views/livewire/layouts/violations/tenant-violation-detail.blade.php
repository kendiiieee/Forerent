<div class="h-full flex flex-col font-sans" x-data="{ lightbox: false, lightboxSrc: '' }">

    {{-- Lightbox --}}
    <div x-show="lightbox" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm" @click.self="lightbox = false" style="display: none;">
        <img :src="lightboxSrc" class="max-h-[85vh] max-w-[90vw] rounded-xl shadow-2xl" />
        <button @click="lightbox = false" class="absolute top-6 right-6 text-white bg-black/50 rounded-full p-2 hover:bg-black/70">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    @if($violation)
        @php
            $severityMap = [
                'minor'   => ['label' => 'Minor',   'bg' => 'bg-blue-50 text-blue-700'],
                'major'   => ['label' => 'Major',   'bg' => 'bg-orange-50 text-orange-700'],
                'serious' => ['label' => 'Serious', 'bg' => 'bg-red-50 text-red-700'],
            ];
            $sc = $severityMap[$violation->severity] ?? ['label' => ucfirst($violation->severity), 'bg' => 'bg-gray-50 text-gray-700'];

            $statusStyles = match($violation->status) {
                'Resolved'     => 'bg-green-100 text-green-700',
                'Issued'       => 'bg-red-100 text-red-700',
                'Acknowledged' => 'bg-yellow-100 text-yellow-800',
                default        => 'bg-gray-100 text-gray-700'
            };

            $penaltyLabel = match($violation->penalty_type) {
                'written_warning'  => 'Written Warning',
                'fine'             => 'Fine — PHP ' . number_format($violation->fine_amount ?? 0, 2),
                'lease_termination' => 'Lease Termination',
                default            => ucfirst($violation->penalty_type),
            };

            $penaltyStyles = match($violation->penalty_type) {
                'written_warning'  => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                'fine'             => 'bg-orange-100 text-orange-800 border-orange-200',
                'lease_termination' => 'bg-red-100 text-red-800 border-red-200',
                default            => 'bg-gray-100 text-gray-700 border-gray-200',
            };

            $evidencePaths = [];
            if (!empty($violation->evidence_path)) {
                $decoded = json_decode($violation->evidence_path, true);
                $evidencePaths = is_array($decoded) ? $decoded : [$violation->evidence_path];
            }

            $offenseLabel = match($violation->offense_number) {
                1 => '1st', 2 => '2nd', 3 => '3rd', default => $violation->offense_number . 'th'
            };
        @endphp

        <div class="flex-1 overflow-y-auto p-6 space-y-6" style="scrollbar-width: thin; scrollbar-color: #e2e8f0 transparent;">

            {{-- Header --}}
            <div class="bg-gradient-to-r from-red-900 to-red-700 rounded-2xl p-5 text-white">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-red-200 text-xs font-semibold uppercase tracking-widest mb-1">Violation Notice</p>
                        <h2 class="text-2xl font-bold">{{ $violationIdDisplay }}</h2>
                        <p class="text-red-100 text-sm mt-1">{{ $offenseLabel }} Offense &middot; {{ \Carbon\Carbon::parse($violation->violation_date)->format('F d, Y') }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $statusStyles }}">{{ $violation->status }}</span>
                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $sc['bg'] }}">{{ $sc['label'] }}</span>
                    </div>
                </div>

                {{-- Acknowledge button in header --}}
                @if($violation->status === 'Issued')
                    <div class="mt-4 pt-3 border-t border-red-600/50">
                        <button
                            wire:click="acknowledgeViolation"
                            wire:confirm="Are you sure you want to acknowledge this violation? This confirms you have received and read the notice."
                            class="px-5 py-2 bg-white text-red-800 text-xs font-bold rounded-xl hover:bg-red-50 transition shadow-sm"
                        >
                            Acknowledge Violation
                        </button>
                    </div>
                @endif
            </div>

            {{-- Penalty Card --}}
            <div class="rounded-xl p-4 border {{ $penaltyStyles }}">
                <p class="text-xs font-bold uppercase tracking-wide mb-1">Penalty Applied</p>
                <p class="text-lg font-bold">{{ $penaltyLabel }}</p>
                @if($violation->penalty_type === 'fine')
                    <p class="text-xs mt-1">This fine has been added to your billing. Please check your payment page.</p>
                @elseif($violation->penalty_type === 'lease_termination')
                    <p class="text-xs mt-1">This violation may result in lease termination. Please contact management.</p>
                @endif
            </div>

            {{-- Violation Details --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Category</p>
                    <p class="text-sm font-bold text-[#070642]">{{ $violation->category }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Issued At</p>
                    <p class="text-sm text-gray-700">{{ \Carbon\Carbon::parse($violation->issued_at)->format('F d, Y h:i A') }}</p>
                </div>
            </div>

            {{-- Description --}}
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Description</p>
                <div class="bg-[#F4F6FB] rounded-xl p-4 text-sm text-gray-700 leading-relaxed">
                    {{ $violation->description }}
                </div>
            </div>

            {{-- Evidence Photos --}}
            @if(!empty($evidencePaths))
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Evidence Photos</p>
                    <div class="flex gap-3 flex-wrap">
                        @foreach($evidencePaths as $path)
                            <img
                                src="{{ asset('storage/' . $path) }}"
                                @click="lightboxSrc = '{{ asset('storage/' . $path) }}'; lightbox = true"
                                class="h-24 w-24 object-cover rounded-xl cursor-pointer border-2 border-gray-100 hover:border-blue-300 transition shadow-sm"
                                alt="Evidence"
                            />
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Resolution Notes (if resolved) --}}
            @if($violation->status === 'Resolved' && $violation->resolution_notes)
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Resolution</p>
                    <div class="bg-green-50 rounded-xl p-4 text-sm text-green-800 leading-relaxed border border-green-100">
                        {{ $violation->resolution_notes }}
                    </div>
                </div>
            @endif

            {{-- Offense History --}}
            @if(!empty($offenseHistory))
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Your Violation History</p>
                    <div class="space-y-3">
                        @foreach($offenseHistory as $offense)
                            @php
                                $isCurrent = $offense['violation_id'] === $violation->violation_id;
                                $oLabel = match($offense['offense_number']) { 1 => '1st', 2 => '2nd', 3 => '3rd', default => $offense['offense_number'] . 'th' };
                                $pLabel = match($offense['penalty_type']) {
                                    'written_warning' => 'Warning',
                                    'fine' => 'Fine — PHP ' . number_format($offense['fine_amount'] ?? 0, 2),
                                    'lease_termination' => 'Termination',
                                    default => ucfirst($offense['penalty_type']),
                                };
                                $oStatus = match($offense['status']) {
                                    'Resolved' => 'bg-green-100 text-green-700',
                                    'Issued' => 'bg-red-100 text-red-700',
                                    'Acknowledged' => 'bg-yellow-100 text-yellow-800',
                                    default => 'bg-gray-100 text-gray-700',
                                };
                            @endphp
                            <div class="flex items-center gap-3 p-3 rounded-xl {{ $isCurrent ? 'bg-blue-50 border border-blue-200' : 'bg-gray-50' }}">
                                <span class="text-xs font-bold text-gray-500 w-8">{{ $oLabel }}</span>
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-[#070642]">
                                        {{ $offense['violation_number'] }} — {{ $offense['category'] }}
                                    </p>
                                    <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($offense['violation_date'])->format('M d, Y') }} &middot; {{ $pLabel }}</p>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $oStatus }}">{{ $offense['status'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Contract Reference --}}
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Penalty Schedule (Per Contract)</p>
                <div class="text-xs text-gray-600 space-y-1">
                    <p><span class="font-semibold">1st Offense:</span> Written Warning</p>
                    <p><span class="font-semibold">2nd Offense:</span> Fine of PHP 500.00</p>
                    <p><span class="font-semibold">3rd Offense:</span> Grounds for lease termination with possible deposit forfeiture</p>
                    <p><span class="font-semibold">Serious Violations:</span> May result in immediate termination</p>
                </div>
            </div>
        </div>

    @else
        {{-- Empty State --}}
        <div class="flex-1 flex flex-col items-center justify-center text-gray-400 p-8">
            <div class="bg-[#F4F7FF] p-6 rounded-full mb-4">
                <svg class="h-12 w-12 text-[#2B66F5] opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
            </div>
            <p class="font-semibold text-gray-500 text-sm">Select a violation to view details</p>
            <p class="text-xs text-gray-400 mt-1">Choose from the list on the left</p>
        </div>
    @endif
</div>
