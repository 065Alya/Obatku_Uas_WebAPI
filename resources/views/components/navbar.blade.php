<header class="sticky top-0 z-30 px-4 pt-4 lg:px-6">
    <div
    class="flex items-center justify-between
           h-20 px-6
           bg-white/90
           backdrop-blur-xl
           border border-white
           rounded-3xl
           shadow-[0_10px_35px_rgba(0,0,0,0.08)]">
        
        <!-- Mobile Menu Button & Search Placeholder -->
        <div class="flex items-center flex-1">
            <button @click="sidebarOpen = !sidebarOpen" class="p-2 mr-4 text-gray-500 rounded-xl lg:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-[#185FA5]">
                <i data-lucide="menu" class="w-7 h-7"></i>
            </button>

            <!-- Search (Optional) -->
            <div class="hidden md:block relative w-full max-w-lg">
                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                    <i data-lucide="search" class="w-5 h-5 text-gray-400"></i>
                </div>
                <input type="text" class="block w-full py-2.5 pl-11 pr-4 text-base text-gray-900 bg-gray-50 border-transparent rounded-xl focus:bg-white focus:border-[#185FA5] focus:ring-2 focus:ring-blue-100 placeholder-gray-400 transition-all" placeholder="Cari obat atau jadwal...">
            </div>
        </div>

        <!-- Right Side Nav -->
        <div class="flex items-center gap-4 sm:gap-6">
            
            <!-- Accessibility Toggle -->
            <button onclick="toggleAccessibilityMode()" class="p-2.5 text-gray-500 transition-colors bg-gray-50 rounded-xl hover:text-[#185FA5] hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-100" title="Ubah Ukuran Teks (Aksesibilitas)">
                <i data-lucide="zoom-in" class="w-6 h-6" id="zoom-icon"></i>
            </button>

            <!-- Notifications Dropdown -->
            <div x-data="{ open: false }" class="relative">

                <button @click="open = !open" @click.away="open = false" class="relative p-2.5 text-gray-500 transition-colors bg-gray-50 rounded-xl hover:text-[#185FA5] hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    <i data-lucide="bell" class="w-6 h-6"></i>
                    <!-- Notification Badge -->
                    <span class="absolute top-1.5 right-1.5 flex w-3 h-3">
                        <span class="absolute inline-flex w-full h-full bg-red-400 rounded-full opacity-75 animate-ping"></span>
                        <span class="relative inline-flex w-3 h-3 bg-red-500 rounded-full border-2 border-white"></span>
                    </span>
                </button>

                <!-- Dropdown Menu -->
                <div x-show="open" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute right-0 w-80 mt-3 origin-top-right bg-white border border-gray-100 rounded-xl shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
                     style="display: none;">
                    <div class="p-4 border-b border-gray-50 flex justify-between items-center">
                        <h3 class="text-base font-semibold text-[#042C53]">Notifikasi</h3>
                        <button class="text-sm font-medium text-[#185FA5] hover:underline">Tandai semua dibaca</button>
                    </div>
                    <div class="max-h-96 overflow-y-auto">
                        <!-- Alert Item -->
                        <div class="p-4 border-b border-gray-50 hover:bg-gray-50 cursor-pointer transition-colors flex gap-3">
                            <div class="mt-1 flex-shrink-0 w-10 h-10 bg-red-100 text-red-600 rounded-xl flex items-center justify-center">
                                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">Stok Obat Menipis</p>
                                <p class="text-sm text-gray-500 mt-0.5">Paracetamol sisa 3 tablet.</p>
                                <p class="text-xs text-gray-400 mt-1">10 menit yang lalu</p>
                            </div>
                        </div>
                        <!-- Alert Item -->
                        <div class="p-4 hover:bg-gray-50 cursor-pointer transition-colors flex gap-3">
                            <div class="mt-1 flex-shrink-0 w-10 h-10 bg-[#1D9E75]/10 text-[#1D9E75] rounded-xl flex items-center justify-center">
                                <i data-lucide="leaf" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">Kedaluwarsa Dekat</p>
                                <p class="text-sm text-gray-500 mt-0.5">Amoxicillin kedaluwarsa 2 hari lagi.</p>
                                <p class="text-xs text-gray-400 mt-1">1 jam yang lalu</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-3 border-t border-gray-50 text-center">
                        <a href="{{ route('alerts.index') }}" class="text-sm font-medium text-[#185FA5] hover:underline">Lihat Semua Notifikasi</a>
                    </div>
                </div>
            </div>

            <!-- Profile Dropdown -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @click.away="open = false" class="flex items-center gap-3 p-1.5 pr-4 transition-colors rounded-full hover:bg-gray-50 focus:outline-none focus:bg-gray-50 focus:ring-2 focus:ring-[#185FA5] focus:ring-offset-2">
                    <img class="object-cover w-10 h-10 rounded-full border-2 border-white shadow-sm" src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'User') }}&background=185FA5&color=fff&rounded=true&bold=true" alt="Profile avatar">
                    <div class="hidden text-left md:block">
                        <p class="text-sm font-semibold text-[#042C53]">{{ auth()->user()->name ?? 'Budi Santoso' }}</p>
                        <p class="text-xs font-medium text-gray-500">{{ auth()->user()->email ?? 'budi@example.com' }}</p>
                    </div>
                    <i data-lucide="chevron-down" class="hidden w-4 h-4 text-gray-400 md:block"></i>
                </button>

                <!-- Dropdown Menu -->
                <div x-show="open" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute right-0 w-56 mt-3 origin-top-right bg-white border border-gray-100 rounded-xl shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
                     style="display: none;">
                    
                    <div class="px-4 py-3 border-b border-gray-50 md:hidden">
                        <p class="text-sm font-semibold text-[#042C53] truncate">{{ auth()->user()->name ?? 'Budi Santoso' }}</p>
                        <p class="text-xs font-medium text-gray-500 truncate">{{ auth()->user()->email ?? 'budi@example.com' }}</p>
                    </div>

                    <div class="py-2">
                        <a href="{{ route('profile.settings') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 hover:text-[#185FA5]">
                            <i data-lucide="user" class="w-4 h-4 mr-3"></i>
                            Pengaturan Profil
                        </a>
                        <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 hover:text-[#185FA5]">
                            <i data-lucide="settings" class="w-4 h-4 mr-3"></i>
                            Preferensi Aplikasi
                        </a>
                    </div>
                    <div class="py-2 border-t border-gray-50">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 hover:text-red-700">
                                <i data-lucide="log-out" class="w-4 h-4 mr-3"></i>
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</header>
