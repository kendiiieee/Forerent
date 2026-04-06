<div>
    @if($isOpen)
        {{-- Main Add Unit Modal --}}
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm">
            <div class="relative w-full max-w-4xl bg-gray-50 rounded-2xl shadow-xl overflow-hidden max-h-[90vh] flex flex-col">

                <div class="bg-[#070589] text-white p-6 flex-shrink-0">
    <div class="flex items-start justify-between">
        <div>
            <h2 class="text-xl font-bold uppercase">
                {{ $editingUnitId ? 'EDIT UNIT #' . $editingUnitId : 'ADD NEW UNIT' }}
            </h2>
            <p class="mt-1 text-sm text-blue-100">
                {{ $editingUnitId ? 'Update unit details and specifications' : 'Fill in the details to predict rental price' }}
            </p>
        </div>

        <flux:tooltip :content="'Close unit form without saving'" position="bottom">
            <button
                type="button"
                x-on:click="$dispatch('open-modal', 'discard-unit-confirmation')"
                class="text-white hover:text-blue-200 transition-colors focus:outline-none">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span class="sr-only">Close modal</span>
            </button>
        </flux:tooltip>

        </div>
</div>

                <div
                    class="flex-1 overflow-y-auto"
                    x-data
                    x-on:scroll-to-error.window="
                        $nextTick(() => {
                            const firstError = $el.querySelector('.text-red-500, .text-xs.text-red-500');
                            if (firstError) {
                                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }
                        })
                    "
                >
                    <ol class="flex items-start justify-between w-full pt-4 pb-6 px-6">
                        @foreach ($steps as $step => $label)
                            @php
                                $isActive = $currentStep == $step;
                                $isLast = $step == count($steps);
                            @endphp

                            <li class="flex flex-col items-center flex-shrink-0">
                                <div class="flex items-center justify-center w-8 h-8 rounded-full
                                    {{ $isActive ? 'bg-[#070642] text-white' : 'bg-gray-200 text-gray-700' }}">
                                    <span class="font-medium">{{ $step }}</span>
                                </div>
                                <span class="mt-2 text-sm text-center
                                    {{ $isActive ? 'text-[#070642] font-bold' : 'text-gray-400 font-normal' }}">
                                    {{ $label }}
                                </span>
                            </li>

                            @if(!$isLast)
                                <li class="flex-auto h-0.5 bg-gray-200 mt-4 mx-2"></li>
                            @endif
                        @endforeach
                    </ol>

                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 mx-6 mb-6">
                        @if ($currentStep == 1)
                            @include('livewire.layouts.units.stepper1')
                        @elseif ($currentStep == 2)
                            @include('livewire.layouts.units.stepper2')
                        @elseif ($currentStep == 3)
                            @include('livewire.layouts.units.stepper3')
                        @endif
                    </div>
                </div>
            </div>
        </div>


        <x-ui.modal-cancel
            name="discard-unit-confirmation"
            title="Discard Unsaved Changes?"
            description="Are you sure you want to close? All details entered will be lost."
            discardText="Discard"
            returnText="Keep Editing"
            discardAction="close"
        />

    @endif
</div>
