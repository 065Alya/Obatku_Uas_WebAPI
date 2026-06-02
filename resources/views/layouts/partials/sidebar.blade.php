{{-- ─── Sidebar Navigation ─── --}}
<aside id="obk-sidebar" class="fixed inset-y-0 left-0 z-50 w-72 bg-[#042C53] text-white flex flex-col transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">

    {{-- Logo --}}
    <div class="flex items-center gap-3 px-6 py-6 border-b border-white/10">
        <div class="w-10 h-10 bg-[#185FA5] rounded-xl flex items-center justify-center">
            <i data-lucide="heart-pulse" class="w-6 h-6 text-white"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold tracking-tight">ObatKu</h1>
            <p class="text-xs text-white/50">Manajemen Obat Keluarga</p>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">

        {{-- Main Menu --}}
        <p class="px-3 mb-3 text-xs font-semibold uppercase tracking-wider text-white/40">Menu Utama</p>

        <a href="{{ route('dashboard') }}"
           class="obk-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('medicines.index') }}"
           class="obk-nav-item {{ request()->routeIs('medicines.*') ? 'active' : '' }}">
            <i data-lucide="pill" class="w-5 h-5"></i>
            <span>Obat Saya</span>
        </a>

        <a href="{{ route('schedules.index') }}"
           class="obk-nav-item {{ request()->routeIs('schedules.*') ? 'active' : '' }}">
            <i data-lucide="clock" class="w-5 h-5"></i>
            <span>Jadwal Obat</span>
        </a>

        <a href="{{ route('family.index') }}"
           class="obk-nav-item {{ request()->routeIs('family.*') ? 'active' : '' }}">
            <i data-lucide="users" class="w-5 h-5"></i>
            <span>Keluarga</span>
        </a>

        {{-- Information --}}
        <p class="px-3 mt-6 mb-3 text-xs font-semibold uppercase tracking-wider text-white/40">Informasi</p>

        <a href="{{ route('articles.index') }}"
           class="obk-nav-item {{ request()->routeIs('articles.*') ? 'active' : '' }}">
            <i data-lucide="book-open" class="w-5 h-5"></i>
            <span>Edukasi Kesehatan</span>
        </a>

        {{-- Admin Section --}}
        @if(auth()->user()->isAdmin())
            <p class="px-3 mt-6 mb-3 text-xs font-semibold uppercase tracking-wider text-white/40">Administrator</p>

            <a href="{{ route('admin.dashboard') }}"
               class="obk-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                <span>Admin Dashboard</span>
            </a>

            <a href="{{ route('admin.users.index') }}"
               class="obk-nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
                <span>Kelola Pengguna</span>
            </a>

            <a href="{{ route('admin.articles.index') }}"
               class="obk-nav-item {{ request()->routeIs('admin.articles.*') ? 'active' : '' }}">
                <i data-lucide="file-text" class="w-5 h-5"></i>
                <span>Kelola Artikel</span>
            </a>
        @endif
    </nav>

    {{-- User Info (Bottom) --}}
    <div class="px-4 py-4 border-t border-white/10">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-[#185FA5] rounded-full flex items-center justify-center text-sm font-bold">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs text-white/50 truncate">{{ auth()->user()->email }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="p-2 rounded-lg hover:bg-white/10 transition" title="Keluar">
                    <i data-lucide="log-out" class="w-4 h-4 text-white/70"></i>
                </button>
            </form>
        </div>
    </div>
</aside>
