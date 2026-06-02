<!-- Sidebar Wrapper -->
<div 
    class="fixed inset-y-0 left-0 z-50 w-72 bg-white shadow-xl lg:static lg:block transition-transform duration-300 transform lg:translate-x-0"
    :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}"
>
    <!-- Logo & Close Button -->
    <div class="flex items-center justify-between h-20 px-6 bg-white border-b border-gray-100">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-[#185FA5] text-white shadow-md">
                <i data-lucide="pill" class="w-6 h-6"></i>
            </div>
            <span class="text-2xl font-bold text-[#042C53]">ObatKu</span>
        </a>
        <!-- Mobile close button -->
        <button @click="sidebarOpen = false" class="p-2 text-gray-500 rounded-lg lg:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-[#185FA5]">
            <i data-lucide="x" class="w-6 h-6"></i>
        </button>
    </div>

    <!-- Navigation Links -->
    <nav class="p-4 space-y-2 overflow-y-auto h-[calc(100vh-5rem)]">
        
        <p class="px-4 mt-4 mb-2 text-xs font-semibold tracking-wider text-gray-400 uppercase">Utama</p>

        <a href="{{ route('dashboard') }}" 
           class="flex items-center px-4 py-3.5 text-base font-medium transition-colors rounded-xl {{ request()->routeIs('dashboard') ? 'bg-[#185FA5] text-white shadow-md' : 'text-gray-600 hover:bg-blue-50 hover:text-[#185FA5]' }}">
            <i data-lucide="layout-dashboard" class="w-6 h-6 mr-3"></i>
            Dashboard
        </a>

        <a href="{{ route('medicines.index') }}" 
           class="flex items-center px-4 py-3.5 text-base font-medium transition-colors rounded-xl {{ request()->routeIs('medicines.*') ? 'bg-[#185FA5] text-white shadow-md' : 'text-gray-600 hover:bg-blue-50 hover:text-[#185FA5]' }}">
            <i data-lucide="package" class="w-6 h-6 mr-3"></i>
            Data Obat
        </a>

        <a href="{{ route('schedules.index') }}" 
           class="flex items-center px-4 py-3.5 text-base font-medium transition-colors rounded-xl {{ request()->routeIs('schedules.*') ? 'bg-[#185FA5] text-white shadow-md' : 'text-gray-600 hover:bg-blue-50 hover:text-[#185FA5]' }}">
            <i data-lucide="calendar-clock" class="w-6 h-6 mr-3"></i>
            Jadwal Minum
        </a>

        <a href="{{ route('consumptions.history') }}" 
           class="flex items-center px-4 py-3.5 text-base font-medium transition-colors rounded-xl {{ request()->routeIs('consumptions.history') ? 'bg-[#185FA5] text-white shadow-md' : 'text-gray-600 hover:bg-blue-50 hover:text-[#185FA5]' }}">
            <i data-lucide="history" class="w-6 h-6 mr-3"></i>
            Riwayat Konsumsi
        </a>

        <p class="px-4 mt-6 mb-2 text-xs font-semibold tracking-wider text-gray-400 uppercase">Keluarga & Literasi</p>

        <a href="{{ route('family.index') }}" 
           class="flex items-center px-4 py-3.5 text-base font-medium transition-colors rounded-xl {{ request()->routeIs('family.*') ? 'bg-[#185FA5] text-white shadow-md' : 'text-gray-600 hover:bg-blue-50 hover:text-[#185FA5]' }}">
            <i data-lucide="users" class="w-6 h-6 mr-3"></i>
            Anggota Keluarga
        </a>

        <a href="{{ route('profile.index') }}" 
           class="flex items-center px-4 py-3.5 text-base font-medium transition-colors rounded-xl {{ request()->routeIs('profile.*') ? 'bg-[#185FA5] text-white shadow-md' : 'text-gray-600 hover:bg-blue-50 hover:text-[#185FA5]' }}">
            <i data-lucide="user-circle" class="w-6 h-6 mr-3"></i>
            Profil Saya
        </a>

        <a href="{{ route('articles.index') }}" 
           class="flex items-center px-4 py-3.5 text-base font-medium transition-colors rounded-xl {{ request()->routeIs('articles.*') ? 'bg-[#185FA5] text-white shadow-md' : 'text-gray-600 hover:bg-blue-50 hover:text-[#185FA5]' }}">
            <i data-lucide="book-open" class="w-6 h-6 mr-3"></i>
            Edukasi Obat
        </a>

        <p class="px-4 mt-6 mb-2 text-xs font-semibold tracking-wider text-gray-400 uppercase">Sistem Keberlanjutan</p>

        <a href="{{ route('ecomed.index') }}" 
           class="flex items-center px-4 py-3.5 text-base font-medium transition-colors rounded-xl {{ request()->routeIs('ecomed.*') ? 'bg-[#1D9E75] text-white shadow-md' : 'text-gray-600 hover:bg-emerald-50 hover:text-[#1D9E75]' }}">
            <i data-lucide="leaf" class="w-6 h-6 mr-3"></i>
            EcoMed
        </a>
        
    </nav>
</div>

<!-- Mobile Overlay -->
<div 
    x-show="sidebarOpen" 
    @click="sidebarOpen = false"
    x-transition.opacity
    class="fixed inset-0 z-40 bg-gray-900/50 lg:hidden backdrop-blur-sm"
></div>
