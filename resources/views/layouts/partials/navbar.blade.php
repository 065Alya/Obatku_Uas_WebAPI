{{-- ─── Top Navbar ─── --}}
<header class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-gray-100">
    <div class="flex items-center justify-between px-4 lg:px-8 h-16">

        {{-- Left: Mobile menu toggle + Page Title --}}
        <div class="flex items-center gap-3">
            <button class="lg:hidden p-2 rounded-xl hover:bg-gray-100 transition" onclick="toggleSidebar()" id="btn-toggle-sidebar">
                <i data-lucide="menu" class="w-5 h-5 text-gray-600"></i>
            </button>

            <div>
                <h2 class="text-lg font-bold text-[#042C53]">@yield('page-title', 'Dashboard')</h2>
            </div>
        </div>

        {{-- Right: Actions --}}
        <div class="flex items-center gap-3">
            {{-- Search (Desktop) --}}
            <div class="hidden md:flex items-center bg-[#F8FAFF] rounded-xl px-3 py-2 border border-gray-100">
                <i data-lucide="search" class="w-4 h-4 text-gray-400 mr-2"></i>
                <input type="text"
                       placeholder="Cari obat..."
                       class="bg-transparent border-none outline-none text-sm w-48 placeholder-gray-400"
                       id="global-search">
            </div>

            {{-- Notifications Bell --}}
            <button class="relative p-2 rounded-xl hover:bg-gray-100 transition" id="btn-notifications">
                <i data-lucide="bell" class="w-5 h-5 text-gray-500"></i>
                @php
                    $alertCount = 0;
                    if(auth()->check()) {
                        $alertCount = auth()->user()->medicines()
                            ->where('is_active', true)
                            ->where(function($q) {
                                $q->whereColumn('stock', '<=', 'stock_alert_threshold')
                                  ->orWhere(function($q2) {
                                      $q2->whereNotNull('expiry_date')
                                         ->where('expiry_date', '<=', now()->addDays(30));
                                  });
                            })->count();
                    }
                @endphp
                @if($alertCount > 0)
                    <span class="absolute -top-0.5 -right-0.5 w-5 h-5 bg-[#E24B4A] text-white text-[10px] font-bold rounded-full flex items-center justify-center animate-pulse-soft">
                        {{ $alertCount > 9 ? '9+' : $alertCount }}
                    </span>
                @endif
            </button>

            {{-- User Avatar --}}
            <div class="hidden sm:flex items-center gap-2 pl-2 border-l border-gray-100">
                <div class="w-8 h-8 bg-[#185FA5] rounded-full flex items-center justify-center text-white text-xs font-bold">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <span class="text-sm font-medium text-gray-700 hidden lg:block">{{ auth()->user()->name }}</span>
            </div>
        </div>
    </div>
</header>
