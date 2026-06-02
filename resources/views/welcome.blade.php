<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>ObatKu — Asisten Kesehatan & Kepatuhan Obat Keluarga</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Lucide Icons -->
        <script src="https://unpkg.com/lucide@latest"></script>

        <style>
            body {
                font-family: 'Inter', sans-serif;
                background-color: #F8FAFF;
            }
        </style>
    </head>
    <body class="antialiased text-gray-900 bg-[#F8FAFF]">
        <!-- Navbar -->
        <nav class="w-full bg-white/80 backdrop-blur-md sticky top-0 z-50 border-b border-gray-100/80">
            <div class="max-w-7xl mx-auto px-6 sm:px-8 h-18 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 bg-gradient-to-br from-[#185FA5] to-[#378ADD] rounded-xl flex items-center justify-center shadow-md shadow-blue-200">
                        <i data-lucide="pill" class="w-5.5 h-5.5 text-white"></i>
                    </div>
                    <div>
                        <span class="text-xl font-bold tracking-tight text-[#042C53]">Obat<span class="text-[#185FA5]">Ku</span></span>
                        <span class="block text-[9px] font-bold text-[#1D9E75] uppercase tracking-wider -mt-1">EcoHealth Hub</span>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-[#185FA5] to-[#378ADD] hover:from-[#042C53] hover:to-[#185FA5] text-white text-sm font-bold rounded-xl shadow-md shadow-blue-100 transition-all hover:-translate-y-0.5 duration-200">
                                Dashboard
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-bold text-gray-600 hover:text-[#185FA5] px-3 py-2 transition-colors">
                                Masuk
                            </a>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-[#185FA5] to-[#378ADD] hover:from-[#042C53] hover:to-[#185FA5] text-white text-sm font-bold rounded-xl shadow-md shadow-blue-100 transition-all hover:-translate-y-0.5 duration-200">
                                    Daftar Gratis
                                </a>
                            @endif
                        @endauth
                    @endif
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="max-w-7xl mx-auto px-6 sm:px-8 py-16 lg:py-24 grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-emerald-50 text-[#1D9E75] text-xs font-bold rounded-full border border-emerald-100">
                    <span class="w-2 h-2 rounded-full bg-[#1D9E75] animate-ping"></span>
                    Mendukung SDG 12: Konsumsi & Produksi Bertanggung Jawab
                </div>
                
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight text-[#042C53] leading-[1.15]">
                    Asisten Kesehatan & <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#185FA5] to-[#378ADD]">Kepatuhan Obat</span> Keluarga
                </h1>
                
                <p class="text-gray-500 text-lg leading-relaxed max-w-xl">
                    Pantau jadwal konsumsi obat, kelola kotak obat keluarga, baca literasi medis bersertifikasi, dan kelola limbah obat secara berkelanjutan bersama ObatKu.
                </p>

                <div class="flex flex-wrap gap-4 pt-2">
                    <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-7 py-3.5 bg-gradient-to-r from-[#185FA5] to-[#378ADD] hover:from-[#042C53] hover:to-[#185FA5] text-white font-bold rounded-xl shadow-lg shadow-blue-100 transition-all hover:-translate-y-0.5 duration-200">
                        Mulai Sekarang
                        <i data-lucide="chevron-right" class="w-5 h-5"></i>
                    </a>
                    <a href="#features" class="inline-flex items-center gap-2 px-7 py-3.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-bold rounded-xl shadow-sm transition-all hover:-translate-y-0.5 duration-200">
                        Pelajari Fitur
                    </a>
                </div>
            </div>

            <!-- Hero Image / Visual Representation -->
            <div class="lg:col-span-5 relative flex justify-center">
                <!-- Glowing backgrounds -->
                <div class="absolute -inset-4 bg-gradient-to-tr from-blue-100 to-emerald-100 rounded-3xl blur-3xl opacity-60 -z-10"></div>
                
                <!-- Mockup Card -->
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100/50 p-6 w-full max-w-sm space-y-6 transition-all hover:scale-[1.02] duration-300">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-blue-50 text-[#185FA5] flex items-center justify-center">
                                <i data-lucide="user" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-gray-900">Budi Santoso</h4>
                                <p class="text-[10px] text-gray-400">Jadwal Hari Ini</p>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 text-[9px] font-bold text-white bg-[#1D9E75] rounded-full">92% Patuh</span>
                    </div>

                    <!-- Mini schedule list -->
                    <div class="space-y-3">
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-100/50">
                            <div class="w-8 h-8 bg-blue-100 text-[#185FA5] rounded-lg flex items-center justify-center">
                                <i data-lucide="pill" class="w-4 h-4"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-gray-900 truncate">Paracetamol</p>
                                <p class="text-[10px] text-gray-400">08:00 WIB · Sesudah Makan</p>
                            </div>
                            <i data-lucide="check-circle" class="w-5 h-5 text-[#1D9E75]"></i>
                        </div>

                        <div class="flex items-center gap-3 p-3 bg-blue-50/50 rounded-xl border border-blue-100/30">
                            <div class="w-8 h-8 bg-amber-100 text-[#EF9F27] rounded-lg flex items-center justify-center">
                                <i data-lucide="droplet" class="w-4 h-4"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-gray-900 truncate">Sirup Batuk</p>
                                <p class="text-[10px] text-gray-400">13:00 WIB · 1 Sendok Makan</p>
                            </div>
                            <span class="w-5 h-5 rounded-full bg-blue-100 text-[#185FA5] flex items-center justify-center">
                                <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                            </span>
                        </div>
                    </div>

                    <!-- SDG 12 Alert -->
                    <div class="p-3.5 bg-emerald-50 rounded-xl border border-emerald-100 flex items-start gap-2.5">
                        <i data-lucide="leaf" class="w-4 h-4 text-[#1D9E75] shrink-0 mt-0.5"></i>
                        <div class="text-[10px] leading-relaxed text-emerald-800">
                            <p class="font-bold">Peringatan EcoMed</p>
                            <p class="mt-0.5 text-emerald-700/80">Amoxicillin Anda akan kedaluwarsa dalam 5 hari. Donasikan atau buang dengan panduan ramah lingkungan.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="bg-white border-y border-gray-100 py-20 lg:py-28">
            <div class="max-w-7xl mx-auto px-6 sm:px-8">
                <div class="text-center max-w-xl mx-auto mb-16 space-y-3">
                    <h2 class="text-xs font-bold text-[#185FA5] uppercase tracking-wider">Modul Terintegrasi</h2>
                    <h3 class="text-3xl font-bold text-[#042C53]">Layanan Kesehatan Modern & Berkelanjutan</h3>
                    <p class="text-gray-500 text-sm">Satu platform terpadu untuk kepatuhan obat harian dan pengelolaan limbah medis rumah tangga.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <!-- Card 1 -->
                    <div class="p-6 rounded-2xl bg-gray-50 hover:bg-white hover:shadow-xl hover:shadow-gray-100 border border-transparent hover:border-gray-100 transition-all duration-300 group">
                        <div class="w-12 h-12 bg-blue-100 text-[#185FA5] rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                            <i data-lucide="calendar-heart" class="w-6 h-6"></i>
                        </div>
                        <h4 class="text-lg font-bold text-[#042C53] mb-2">Penjadwalan Obat</h4>
                        <p class="text-gray-500 text-sm leading-relaxed">Pengingat jadwal minum obat harian otomatis yang mudah digunakan, ramah lansia, dan presisi.</p>
                    </div>

                    <!-- Card 2 -->
                    <div class="p-6 rounded-2xl bg-gray-50 hover:bg-white hover:shadow-xl hover:shadow-gray-100 border border-transparent hover:border-gray-100 transition-all duration-300 group">
                        <div class="w-12 h-12 bg-emerald-100 text-[#1D9E75] rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                            <i data-lucide="leaf" class="w-6 h-6"></i>
                        </div>
                        <h4 class="text-lg font-bold text-[#042C53] mb-2">EcoMed (SDG 12)</h4>
                        <p class="text-gray-500 text-sm leading-relaxed">Kelola obat kedaluwarsa, lacak limbah medis, dan buang obat dengan aman sesuai standar lingkungan.</p>
                    </div>

                    <!-- Card 3 -->
                    <div class="p-6 rounded-2xl bg-gray-50 hover:bg-white hover:shadow-xl hover:shadow-gray-100 border border-transparent hover:border-gray-100 transition-all duration-300 group">
                        <div class="w-12 h-12 bg-purple-100 text-[#7F77DD] rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                            <i data-lucide="book-open" class="w-6 h-6"></i>
                        </div>
                        <h4 class="text-lg font-bold text-[#042C53] mb-2">Literasi Obat</h4>
                        <p class="text-gray-500 text-sm leading-relaxed">Akses artikel edukasi kesehatan dan informasi penggunaan obat bersertifikasi dari profesional medis.</p>
                    </div>

                    <!-- Card 4 -->
                    <div class="p-6 rounded-2xl bg-gray-50 hover:bg-white hover:shadow-xl hover:shadow-gray-100 border border-transparent hover:border-gray-100 transition-all duration-300 group">
                        <div class="w-12 h-12 bg-red-100 text-[#E24B4A] rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                            <i data-lucide="shield-alert" class="w-6 h-6"></i>
                        </div>
                        <h4 class="text-lg font-bold text-[#042C53] mb-2">Deteksi Interaksi</h4>
                        <p class="text-gray-500 text-sm leading-relaxed">Dapatkan alarm peringatan instan jika terdapat kontraindikasi antar obat yang dikonsumsi bersamaan.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-[#042C53] text-white py-12">
            <div class="max-w-7xl mx-auto px-6 sm:px-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6 border-b border-white/10 pb-8">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 bg-white/10 rounded-lg flex items-center justify-center">
                        <i data-lucide="pill" class="w-5 h-5 text-white"></i>
                    </div>
                    <span class="text-lg font-bold tracking-tight">ObatKu</span>
                </div>
                <div class="text-sm text-gray-400">
                    &copy; 2026 ObatKu. All rights reserved.
                </div>
            </div>
            <div class="max-w-7xl mx-auto px-6 sm:px-8 pt-6 text-xs text-gray-500 text-center sm:text-left">
                Dikembangkan untuk mendukung kepatuhan medis rumah tangga dan kelestarian ekologi (EcoMed Hub).
            </div>
        </footer>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                lucide.createIcons();
            });
        </script>
    </body>
</html>
