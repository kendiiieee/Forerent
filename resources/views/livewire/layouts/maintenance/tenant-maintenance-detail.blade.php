{{--
    CHANGES:
    - Removed Priority Level, Ticket Number, Status from blue header
    - Added "Send Feedback" button in header (only for Completed/Resolved tickets)
    - Added Maintenance Feedback modal (star rating + experience tags + comment)
    - Merged Alpine state for lightbox + feedback modal into one x-data
--}}
<div
    class="h-full flex flex-col font-sans"
    x-data="{
        lightbox: false,
        lightboxSrc: '',
        feedbackOpen: false,
        rating: 0,
        hoverRating: 0,
        selectedTags: [],
        comment: '',

        openFeedback() {
            this.rating = 0;
            this.hoverRating = 0;
            this.selectedTags = [];
            this.comment = '';
            this.feedbackOpen = true;
        },
        ratingLabel() {
            const labels = ['', 'Poor', 'Fair', 'Good', 'Great', 'Excellent'];
            return labels[this.hoverRating || this.rating] || '';
        },
        toggleTag(tag) {
            const idx = this.selectedTags.indexOf(tag);
            if (idx === -1) { this.selectedTags.push(tag); }
            else { this.selectedTags.splice(idx, 1); }
        },
        hasTag(tag) {
            return this.selectedTags.includes(tag);
        }
    }"
>

    {{-- ══════════════════════════════════════════════
         LIGHTBOX OVERLAY
    ══════════════════════════════════════════════ --}}
    <div
        x-show="lightbox"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="lightbox = false"
        @keydown.escape.window="lightbox = false; feedbackOpen = false"
        class="fixed inset-0 z-[9999] bg-black/85 backdrop-blur-sm flex items-center justify-center p-4 cursor-zoom-out"
        style="display: none;"
    >
        <img :src="lightboxSrc" class="max-w-full max-h-full object-contain rounded-xl shadow-2xl" @click.stop>
        <flux:tooltip :content="'Close the image viewer'" position="bottom">
            <button
                @click="lightbox = false"
                class="absolute top-5 right-5 w-10 h-10 bg-white/20 hover:bg-white/30 text-white rounded-full flex items-center justify-center transition-colors"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </flux:tooltip>
    </div>

    {{-- ══════════════════════════════════════════════
         FEEDBACK MODAL
         Fixes applied:
         - Fixed modal height: header + footer are sticky, only body scrolls
         - Stars: NO CSS transforms at all — eliminates Chrome compositing
           ghosting/blur. Color-only transition is GPU-safe.
         - Textarea: fixed height with overflow-y-auto (no infinite expansion,
           no HTML bleed from auto-resize misfire)
         - isolation:isolate on modal card prevents stacking-context bleed
         - No backdrop click-to-close; only X button closes
    ══════════════════════════════════════════════ --}}
    <div
        x-show="feedbackOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[9998] flex items-center justify-center p-6"
        style="display: none;"
    >
        {{-- Backdrop — no click-to-close intentionally --}}
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

        {{-- Modal Card — fixed size, internal scroll, isolation prevents ghosting --}}
        <div
            x-show="feedbackOpen"
            x-transition:enter="transition ease-out duration-250"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2"
            class="relative z-10 flex flex-col bg-white rounded-2xl shadow-2xl w-full max-w-lg"
            style="height: 580px; isolation: isolate;"
            @click.stop
        >
            {{-- ── Sticky Header ── --}}
            <div class="flex-shrink-0 bg-[#2B66F5] px-6 py-5 flex justify-between items-center rounded-t-2xl">
                <h3 class="text-xl font-bold text-white">Maintenance Feedback</h3>
                <flux:tooltip :content="'Close the feedback form'" position="bottom">
                    <button
                        @click="feedbackOpen = false"
                        type="button"
                        class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-white/20 transition-colors text-white"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </flux:tooltip>
            </div>

            {{-- ── Scrollable Body ── --}}
            <div class="flex-1 overflow-y-auto px-6 py-6 space-y-6" style="scrollbar-width: thin; scrollbar-color: #e2e8f0 transparent;">

                {{-- Star Rating
                     Ghost/blur fix: zero CSS transforms on the SVG.
                     Only fill color changes — pure color swap has no compositing cost.
                     pointer-events-none on SVG prevents the jagged star path
                     from firing stray mouseenter/leave events.
                --}}
                <div>
                    <p class="text-sm font-bold text-gray-800 mb-3">Overall Service Rating</p>
                    <div class="flex items-center gap-3">
                        <template x-for="star in 5" :key="star">
                            <button
                                type="button"
                                @mouseenter="hoverRating = star"
                                @mouseleave="hoverRating = 0"
                                @click="rating = star"
                                class="focus:outline-none flex-shrink-0"
                                style="width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center;"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    style="width: 34px; height: 34px; pointer-events: none; transition: fill 120ms ease;"
                                    :style="star <= (hoverRating || rating)
                                        ? 'fill: #FBBF24;'
                                        : 'fill: #E5E7EB;'"
                                >
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            </button>
                        </template>
                    </div>
                    {{-- Reserve space so layout never shifts when label appears --}}
                    <p class="text-sm font-semibold text-[#2B66F5] mt-2" style="min-height: 20px;"
                       x-text="ratingLabel()"></p>
                </div>

                <div class="border-t border-gray-100"></div>

                {{-- Experience Tags — multi-select --}}
                <div>
                    <p class="text-sm font-bold text-gray-800 mb-3">What best describes your experience?</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach(['Fixed Fast', 'Fully Resolved', 'On Schedule', 'Partial Fix', 'Follow-up Needed'] as $tag)
                            <button
                                type="button"
                                @click="toggleTag('{{ $tag }}')"
                                :class="hasTag('{{ $tag }}')
                                    ? 'bg-[#2B66F5] border-[#2B66F5] text-white'
                                    : 'bg-white border-gray-200 text-gray-600 hover:border-[#2B66F5] hover:text-[#2B66F5]'"
                                class="px-4 py-2 rounded-full border text-xs font-semibold transition-colors"
                            >{{ $tag }}</button>
                        @endforeach
                    </div>
                </div>

                <div class="border-t border-gray-100"></div>

                {{-- Additional Comments — fixed height + scroll, no auto-expand to prevent layout explosion --}}
                <div>
                    <p class="text-sm font-bold text-gray-800 mb-3">Additional Comments</p>
                    <textarea
                        x-model="comment"
                        placeholder="Add a message..."
                        class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#2B66F5] bg-gray-50 resize-none"
                        style="height: 110px; overflow-y: auto;"
                    ></textarea>
                </div>

            </div>

            {{-- ── Sticky Footer ── --}}
            <div class="flex-shrink-0 px-6 py-4 border-t border-gray-100 flex justify-end gap-3 rounded-b-2xl bg-white">
                <button
                    @click="feedbackOpen = false"
                    type="button"
                    class="px-5 py-2.5 rounded-xl font-semibold text-gray-500 bg-gray-100 hover:bg-gray-200 transition-colors text-sm"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    @click="
                        $wire.saveFeedback(rating, selectedTags.join(','), comment);
                        feedbackOpen = false;
                    "
                    :disabled="rating === 0"
                    :class="rating === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-[#1a4fd1]'"
                    class="px-6 py-2.5 rounded-xl font-bold text-white bg-[#2B66F5] transition-colors text-sm shadow-sm"
                >
                    Submit Feedback
                </button>
            </div>
        </div>
    </div>

    {{-- ── Confirmation Modals (reusable component) ── --}}
    <x-ui.modal-confirm name="confirm-cancel-request"
        title="Cancel this request?"
        description="This action cannot be undone. Your maintenance request will be permanently cancelled."
        confirmText="Yes, Cancel Request" cancelText="Keep Request" confirmAction="cancelRequest"/>

    <x-ui.modal-confirm name="confirm-reopen-request"
        title="Reopen this request?"
        description="The issue will be re-submitted as Pending and your manager will be notified."
        confirmText="Yes, Reopen" cancelText="Cancel" confirmAction="reopenRequest"/>

    {{-- ══════════════════════════════════════════════
         MAIN DETAIL CONTENT
    ══════════════════════════════════════════════ --}}
    @if($ticket)
        @php
            $urgencyMap = [
                'Level 1' => ['dot' => 'bg-red-400',    'label' => 'Critical'],
                'Level 2' => ['dot' => 'bg-orange-400', 'label' => 'High'],
                'Level 3' => ['dot' => 'bg-yellow-400', 'label' => 'Medium'],
                'Level 4' => ['dot' => 'bg-green-400',  'label' => 'Low'],
            ];
            $uc = $urgencyMap[$ticket->urgency] ?? ['dot' => 'bg-gray-400', 'label' => $ticket->urgency ?? '—'];

            $statusStyles = match($ticket->status) {
                'Completed', 'Resolved'  => 'bg-green-100 text-green-700',
                'Pending'                => 'bg-orange-100 text-orange-700',
                'In Progress', 'Ongoing' => 'bg-yellow-100 text-yellow-800',
                default                  => 'bg-gray-100 text-gray-700'
            };

            $isResolved = in_array($ticket->status, ['Completed', 'Resolved']);

            $leaseInfo = \Illuminate\Support\Facades\DB::table('leases')
                ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
                ->join('units', 'beds.unit_id', '=', 'units.unit_id')
                ->join('properties', 'units.property_id', '=', 'properties.property_id')
                ->join('users', 'leases.tenant_id', '=', 'users.user_id')
                ->where('leases.lease_id', $ticket->lease_id)
                ->select('units.unit_number', 'properties.building_name', 'users.first_name', 'users.last_name')
                ->first();

            $unitDisplay     = $leaseInfo ? 'Unit ' . $leaseInfo->unit_number : 'Unit —';
            $buildingDisplay = $leaseInfo ? $leaseInfo->building_name : '—';
            $tenantName      = $leaseInfo
                ? $leaseInfo->first_name . ' ' . $leaseInfo->last_name
                : ($ticket->logged_by ?? '—');

            $imagePaths = [];
            if (!empty($ticket->image_path)) {
                $decoded = json_decode($ticket->image_path, true);
                $imagePaths = is_array($decoded) ? $decoded : [$ticket->image_path];
            }
        @endphp

        {{-- ── BLUE HEADER ── --}}
        <div class="flex-shrink-0 bg-[#2B66F5] text-white px-6 py-5">
            <div class="flex justify-between items-start gap-4">

                {{-- Left: Tenant / Unit / Building --}}
                <div class="min-w-0">
                    <p class="text-xs text-blue-200 font-medium mb-0.5 truncate">{{ $tenantName }}</p>
                    <h2 class="text-3xl font-bold leading-tight">{{ $unitDisplay }}</h2>
                    <p class="text-sm text-blue-100 mt-0.5 truncate">{{ $buildingDisplay }}</p>
                </div>

                {{-- Right: Action buttons --}}
                <div class="flex-shrink-0 pt-1 flex items-center gap-2">
                    {{-- Edit + Cancel buttons (only Pending) --}}
                    @if($ticket->status === 'Pending')
                        <button
                            wire:click="startEditing"
                            type="button"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-white/20 text-white text-xs font-bold hover:bg-white/30 transition-colors whitespace-nowrap"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit
                        </button>
                        <button
                            x-on:click="$dispatch('open-modal', 'confirm-cancel-request')"
                            type="button"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-red-500/20 text-white text-xs font-bold hover:bg-red-500/40 transition-colors whitespace-nowrap"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Cancel Request
                        </button>
                    @endif

                    {{-- Completed/Resolved actions --}}
                    @if($isResolved)
                        {{-- Reopen button --}}
                        <button
                            x-on:click="$dispatch('open-modal', 'confirm-reopen-request')"
                            type="button"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-orange-500/20 text-white text-xs font-bold hover:bg-orange-500/40 transition-colors whitespace-nowrap"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Reopen
                        </button>

                        {{-- Feedback button --}}
                        @if($feedbackSubmitted)
                            <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-white/20 text-white text-xs font-semibold">
                                <svg class="w-4 h-4 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Feedback Sent
                            </span>
                        @else
                            <button
                                @click="openFeedback()"
                                type="button"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white text-[#2B66F5] text-xs font-bold hover:bg-blue-50 transition-colors shadow-sm whitespace-nowrap"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                </svg>
                                Send Feedback
                            </button>
                        @endif
                    @endif
                </div>
            </div>

            <p class="text-xs text-blue-200 mt-3 pt-3 border-t border-blue-400/30">
                Submitted on
                <span class="font-semibold text-white">
                    {{ \Carbon\Carbon::parse($ticket->created_at)->format('F d, Y \a\t h:i A') }}
                </span>
            </p>
        </div>

        {{-- ── SCROLLABLE BODY ── --}}
        <div class="flex-1 overflow-y-auto bg-white" style="scrollbar-width: thin; scrollbar-color: #e2e8f0 transparent;">
            <div class="p-6 space-y-7">

                {{-- Issue Details --}}
                <div>
                    <h3 class="text-sm font-bold text-[#070642] mb-3 flex items-center gap-2">
                        <span class="w-1 h-4 bg-[#2B66F5] rounded-full"></span>
                        Issue Details
                    </h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-[#F4F7FF] p-4 rounded-xl border border-blue-50">
                            <p class="text-gray-400 text-[11px] uppercase font-bold tracking-wide mb-1">Ticket Number</p>
                            <p class="text-[#070642] font-semibold font-mono text-sm">{{ $ticketIdDisplay }}</p>
                        </div>
                        <div class="bg-[#F4F7FF] p-4 rounded-xl border border-blue-50">
                            <p class="text-gray-400 text-[11px] uppercase font-bold tracking-wide mb-1">Priority Level</p>
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full {{ $uc['dot'] }}"></span>
                                <span class="text-[#070642] font-semibold text-sm">{{ $ticket->urgency }}</span>
                                <span class="text-xs text-gray-500">({{ $uc['label'] }})</span>
                            </div>
                        </div>
                        <div class="bg-[#F4F7FF] p-4 rounded-xl border border-blue-50">
                            <p class="text-gray-400 text-[11px] uppercase font-bold tracking-wide mb-1">Category</p>
                            <p class="text-[#070642] font-semibold text-sm">{{ $ticket->category ?? 'General Maintenance' }}</p>
                        </div>
                        <div class="bg-[#F4F7FF] p-4 rounded-xl border border-blue-50">
                            <p class="text-gray-400 text-[11px] uppercase font-bold tracking-wide mb-1">Status</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $statusStyles }}">
                                {{ $ticket->status }}
                            </span>
                        </div>
                        @if($ticket->assigned_to)
                            <div class="bg-[#F4F7FF] p-4 rounded-xl border border-blue-50">
                                <p class="text-gray-400 text-[11px] uppercase font-bold tracking-wide mb-1">Assigned To</p>
                                <p class="text-[#070642] font-semibold text-sm">{{ $ticket->assigned_to }}</p>
                            </div>
                        @endif
                        @if($ticket->expected_completion_date)
                            <div class="bg-[#F4F7FF] p-4 rounded-xl border border-blue-50">
                                <p class="text-gray-400 text-[11px] uppercase font-bold tracking-wide mb-1">Expected Completion</p>
                                <p class="text-[#070642] font-semibold text-sm">{{ \Carbon\Carbon::parse($ticket->expected_completion_date)->format('M d, Y') }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Edit Form (Pending only) --}}
                @if($editing && $ticket->status === 'Pending')
                    <div class="bg-blue-50/50 border border-blue-200 rounded-2xl p-5 space-y-4">
                        <h3 class="text-sm font-bold text-[#070642] flex items-center gap-2">
                            <span class="w-1 h-4 bg-[#2B66F5] rounded-full"></span>
                            Edit Request
                        </h3>

                        {{-- Category --}}
                        <div>
                            <label class="text-[10px] uppercase font-bold tracking-wide text-gray-400 mb-1.5 block">Category</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach(['Plumbing', 'Electrical', 'Structural', 'Appliance', 'Pest Control'] as $cat)
                                    <button type="button" wire:click="$set('editCategory', '{{ $cat }}')"
                                        class="px-4 py-1.5 rounded-lg border text-xs font-semibold transition-all
                                            {{ $editCategory === $cat
                                                ? 'bg-[#2672EC] border-[#2672EC] text-white'
                                                : 'bg-white border-gray-200 text-[#2672EC] hover:bg-blue-50' }}">
                                        {{ $cat }}
                                    </button>
                                @endforeach
                            </div>
                            @error('editCategory') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Description --}}
                        <div>
                            <label class="text-[10px] uppercase font-bold tracking-wide text-gray-400 mb-1.5 block">Description</label>
                            <textarea wire:model="editDescription" rows="4"
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-700 bg-white focus:outline-none focus:ring-2 focus:ring-[#2672EC] resize-none placeholder-gray-400"
                                placeholder="Describe the issue (min 10 characters)..."></textarea>
                            @error('editDescription') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex justify-end gap-2">
                            <button wire:click="cancelEditing" class="px-4 py-2 rounded-xl text-sm font-semibold bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
                                Cancel
                            </button>
                            <button wire:click="saveEdit" class="px-4 py-2 rounded-xl text-sm font-bold bg-[#2B66F5] text-white hover:bg-[#1a4fd1] transition-colors">
                                Save Changes
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Description --}}
                <div>
                    <h3 class="text-sm font-bold text-[#070642] mb-3 flex items-center gap-2">
                        <span class="w-1 h-4 bg-[#2B66F5] rounded-full"></span>
                        Description
                    </h3>
                    <div class="bg-gray-50 border border-gray-100 p-4 rounded-xl text-gray-700 leading-relaxed text-sm">
                        {{ $ticket->problem }}
                    </div>
                </div>

                {{-- Photos --}}
                @if(!empty($imagePaths))
                    <div>
                        <h3 class="text-sm font-bold text-[#070642] mb-3 flex items-center gap-2">
                            <span class="w-1 h-4 bg-[#2B66F5] rounded-full"></span>
                            Photos
                            <span class="text-[11px] text-gray-400 font-normal ml-1">(click to enlarge)</span>
                        </h3>
                        <div class="grid {{ count($imagePaths) === 1 ? 'grid-cols-1' : 'grid-cols-2' }} gap-3">
                            @foreach($imagePaths as $imgPath)
                                <div
                                    class="rounded-xl overflow-hidden border border-gray-100 shadow-sm cursor-zoom-in group relative"
                                    @click="lightbox = true; lightboxSrc = '{{ asset('storage/' . $imgPath) }}'"
                                >
                                    <img
                                        src="{{ asset('storage/' . $imgPath) }}"
                                        alt="Maintenance issue photo"
                                        class="w-full {{ count($imagePaths) === 1 ? 'max-h-64' : 'h-36' }} object-cover group-hover:scale-105 transition-transform duration-300"
                                    >
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors flex items-center justify-center">
                                        <div class="opacity-0 group-hover:opacity-100 transition-opacity bg-white/80 rounded-full p-1.5">
                                            <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Updates Timeline --}}
                <div>
                    <h3 class="text-sm font-bold text-[#070642] mb-4 flex items-center gap-2">
                        <span class="w-1 h-4 bg-[#2B66F5] rounded-full"></span>
                        Updates
                    </h3>
                    <div class="pl-2">

                        @if(in_array($ticket->status, ['Completed', 'Resolved']))
                            <div class="flex gap-4 relative pb-6">
                                <div class="absolute left-[9px] top-6 bottom-0 w-[2px] bg-gray-200"></div>
                                <div class="flex-shrink-0 w-5 h-5 rounded-full bg-green-500 border-2 border-white shadow z-10 mt-0.5"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-[#070642] text-sm">Issue resolved</p>
                                    <p class="text-xs text-gray-400 mb-2">{{ \Carbon\Carbon::parse($ticket->updated_at)->format('M d, h:i A') }}</p>
                                    <div class="bg-white border border-green-100 shadow-sm p-3 rounded-xl text-sm text-gray-600">
                                        Your maintenance request has been resolved.
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if(in_array($ticket->status, ['Ongoing', 'In Progress', 'Completed', 'Resolved']))
                            <div class="flex gap-4 relative pb-6">
                                <div class="absolute left-[9px] top-6 bottom-0 w-[2px] bg-gray-200"></div>
                                <div class="flex-shrink-0 w-5 h-5 rounded-full bg-[#2B66F5] border-2 border-white shadow z-10 mt-0.5"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-[#070642] text-sm">Technician assigned</p>
                                    <p class="text-xs text-gray-400 mb-2">{{ \Carbon\Carbon::parse($ticket->updated_at)->format('M d, h:i A') }}</p>
                                    <div class="bg-white border border-gray-100 shadow-sm p-3 rounded-xl text-sm text-gray-600">
                                        A technician has been dispatched to check the issue.
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="flex gap-4 relative">
                            <div class="flex-shrink-0 w-5 h-5 rounded-full bg-gray-300 border-2 border-white shadow z-10 mt-0.5"></div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-[#070642] text-sm">Request received</p>
                                <p class="text-xs text-gray-400 mb-2">{{ \Carbon\Carbon::parse($ticket->created_at)->format('M d, h:i A') }}</p>
                                <p class="text-sm text-gray-600">Your maintenance request has been submitted successfully.</p>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>

    @else
        {{-- Empty state --}}
        <div class="flex-1 flex flex-col items-center justify-center text-gray-400 p-6">
            <div class="bg-[#F4F7FF] p-8 rounded-full mb-5">
                <svg class="h-16 w-16 text-[#2B66F5] opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-500 mb-1">No Ticket Selected</h3>
            <p class="text-sm text-center">Select a maintenance request from the list to view details.</p>
        </div>
    @endif
</div>
