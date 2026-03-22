<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'ForeRent') }}</title>

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

            {{-- Top spacer --}}
            <div class="flex-shrink-0 h-20 bg-white"></div>

            <main class="flex-1 overflow-y-auto ml-8 bg-white">

                <div class="w-full min-h-full rounded-tl-4xl bg-[#F4F7FC] flex flex-col px-4 md:px-8 lg:px-18 pt-9 pb-16 gap-6">

                    @hasSection('header-title')
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
                    @endif

                    @yield('content')
                    {{ $slot ?? '' }}
                </div>
            </main>
        </section>
    </div>

    {{-- Toast Notifications Container --}}
    <livewire:components.notification-container />

    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
    {{-- <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    @stack('scripts')
</body>
</html>
