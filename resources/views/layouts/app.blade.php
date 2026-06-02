<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#185FA5">
    <meta name="application-name" content="ObatKu">
    <meta name="description" content="Manajemen obat keluarga — lacak jadwal, stok, dan kedaluwarsa obat Anda.">

    {{-- PWA: VAPID public key for push subscriptions --}}
    <meta name="vapid-public-key" content="{{ config('pwa.vapid_public_key', '') }}">

    <title>{{ config('app.name', 'ObatKu') }} - @yield('title', 'Dashboard')</title>

    {{-- PWA Manifest & Icons --}}
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32"   href="/icons/icon-96x96.png">
    <link rel="shortcut icon" href="/favicon.ico">

    {{-- Apple PWA (iOS) --}}
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="ObatKu">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">

    {{-- Microsoft Tiles --}}
    <meta name="msapplication-TileColor" content="#185FA5">
    <meta name="msapplication-TileImage" content="/icons/icon-144x144.png">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">

    {{-- Scripts / Styles --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        // Pre-detect accessibility mode to prevent visual flash
        if (localStorage.getItem('accessible-mode') === 'true') {
            document.documentElement.classList.add('accessibility-large');
        }
    </script>


    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Lucide Icons --}}
    <script src="https://unpkg.com/lucide@latest"></script>

    @stack('head')
</head>
<body class="font-sans antialiased text-gray-900 bg-[#F8FAFF] selection:bg-[#185FA5] selection:text-white">

    <div class="flex h-screen overflow-hidden bg-[#F8FAFF]" x-data="{ sidebarOpen: false }">
        <!-- Sidebar Component -->
        @include('components.sidebar')

        <!-- Main Content Wrapper -->
        <div class="flex flex-col flex-1 w-full overflow-hidden">
            
            <!-- Top Navbar Component -->
            @include('components.navbar')

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto overflow-x-hidden bg-[#F8FAFF] focus:outline-none">
                <div class="px-4 py-8 sm:px-6 lg:px-8 max-w-7xl mx-auto min-h-screen">
                    
                    <!-- Global Alert Messages (Optional) -->
                    @if(session('success'))
                    <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-100 flex items-start gap-3 shadow-sm">
                        <i data-lucide="check-circle-2" class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5"></i>
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 flex items-start gap-3 shadow-sm">
                        <i data-lucide="x-circle" class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5"></i>
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                    @endif

                    <!-- Content Header -->
                    @hasSection('header')
                        <div class="mb-8">
                            <h1 class="text-3xl font-bold text-[#042C53] tracking-tight">@yield('header')</h1>
                            @hasSection('subheader')
                                <p class="mt-2 text-base text-gray-500">@yield('subheader')</p>
                            @endif
                        </div>
                    @endif

                    <!-- Content Body -->
                    <div class="animate-in fade-in slide-in-from-bottom-4 duration-500">
                        @yield('content')
                    </div>
                    
                </div>
            </main>
        </div>
    </div>

    <!-- Initialize Alpine and Lucide -->
    <script>
        function toggleAccessibilityMode() {
            const isLarge = document.documentElement.classList.toggle('accessibility-large');
            localStorage.setItem('accessible-mode', isLarge ? 'true' : 'false');
            updateZoomIcon();
        }
        function updateZoomIcon() {
            const icon = document.getElementById('zoom-icon');
            if (icon) {
                if (document.documentElement.classList.contains('accessibility-large')) {
                    icon.setAttribute('data-lucide', 'zoom-out');
                } else {
                    icon.setAttribute('data-lucide', 'zoom-in');
                }
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            }
        }
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
            updateZoomIcon();
        });
        document.addEventListener('livewire:navigated', () => {
            lucide.createIcons();
            updateZoomIcon();
        });
    </script>

</body>
</html>
