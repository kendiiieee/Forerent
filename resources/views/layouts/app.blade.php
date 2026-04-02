<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'ForeRent') }}</title>
    <link rel="icon" href="{{ asset('images/favicon.svg') }}" type="image/svg+xml">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="font-sans antialiased h-screen overflow-hidden bg-white">

    <div class="flex flex-row h-full">

        <div class="flex-shrink-0">
            @auth
             <livewire:navbars.side-bar />
             @endauth
        </div>

        <section class="flex-1 flex flex-col h-full overflow-hidden relative">

            {{-- Top bar with notification bell --}}
            <div class="flex-shrink-0 h-20 bg-white flex items-center justify-end px-8">
                @auth
                    <livewire:navbars.notification-bell />
                @endauth
            </div>

            <main class="flex-1 overflow-y-auto ml-8 bg-[#F4F7FC] rounded-tl-4xl [&::-webkit-scrollbar]:w-0">

                <div class="w-full min-h-full flex flex-col px-4 md:px-8 lg:px-18 pb-16 gap-6">

                    @hasSection('header-title')
                        <div class="sticky top-0 z-20 bg-[#F4F7FC] pb-1 pt-5">
                            <div class="flex flex-col gap-1">
                                <h1 class="font-sans font-bold text-4xl tracking-[-0.04em] text-blue-900">
                                    @yield('header-title')
                                </h1>
                                @hasSection('header-subtitle')
                                    <p class="font-sans font-medium text-xl tracking-tighter text-[#0C0C0C]">
                                        @yield('header-subtitle')
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endif

                    @yield('content')
                    {{ $slot ?? '' }}
                </div>
            </main>
        </section>
    </div>

    {{-- Toast Notifications Container --}}
    <livewire:components.notification-container />

    {{-- Logout Confirmation Modal --}}
    <div id="logout-confirm-overlay" class="fixed inset-0 z-[9998] hidden bg-black/40" aria-hidden="true"></div>
    <div id="logout-confirm-modal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="logout-confirm-title">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-[0_10px_30px_rgba(0,0,0,0.15)]">
            <div class="mb-4 flex items-start justify-between gap-3">
                <h2 id="logout-confirm-title" class="text-xl font-bold text-[#070589]">Confirm Logout</h2>
                <button id="logout-confirm-close" type="button" class="rounded-md p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-700" aria-label="Close logout dialog">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <p class="mb-6 text-sm text-gray-600">Are you sure you want to log out of your account?</p>
            <div class="flex justify-end gap-3">
                <button id="logout-confirm-cancel" type="button" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</button>
                <button id="logout-confirm-continue" type="button" class="rounded-lg bg-[#070589] px-4 py-2 text-sm font-semibold text-white hover:bg-[#001445]">Yes, Log Out</button>
            </div>
        </div>
    </div>

    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
    {{-- <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        (function () {
            const modal = document.getElementById('logout-confirm-modal');
            const overlay = document.getElementById('logout-confirm-overlay');
            const confirmBtn = document.getElementById('logout-confirm-continue');
            const cancelBtn = document.getElementById('logout-confirm-cancel');
            const closeBtn = document.getElementById('logout-confirm-close');

            if (!modal || !overlay || !confirmBtn || !cancelBtn || !closeBtn) {
                return;
            }

            let pendingAction = null;

            const closeModal = () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                overlay.classList.add('hidden');
                pendingAction = null;
            };

            const openModal = (action) => {
                pendingAction = action;
                overlay.classList.remove('hidden');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            };

            document.addEventListener('click', (event) => {
                const trigger = event.target.closest('[data-logout-trigger]');

                if (!trigger) {
                    return;
                }

                event.preventDefault();

                const form = trigger.closest('form');
                if (form) {
                    openModal({ type: 'form', value: form });
                    return;
                }

                const url = trigger.getAttribute('href') || trigger.dataset.logoutUrl;
                if (url) {
                    openModal({ type: 'url', value: url });
                }
            });

            confirmBtn.addEventListener('click', () => {
                if (!pendingAction) {
                    closeModal();
                    return;
                }

                if (pendingAction.type === 'form') {
                    pendingAction.value.submit();
                    return;
                }

                if (pendingAction.type === 'url') {
                    window.location.href = pendingAction.value;
                }
            });

            cancelBtn.addEventListener('click', closeModal);
            closeBtn.addEventListener('click', closeModal);
            overlay.addEventListener('click', closeModal);
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                    closeModal();
                }
            });
        })();
    </script>

    @stack('scripts')
</body>
</html>
