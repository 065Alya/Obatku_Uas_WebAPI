@extends('layouts.app')
@section('title', 'Admin Dashboard — ObatKu')
@section('header_title', 'Administrasi · Overview')

@section('content')

{{-- Welcome Admin Banner --}}
<div class="mb-8 rounded-2xl bg-gradient-to-r from-[#042C53] to-[#185FA5] p-6 text-white relative overflow-hidden shadow-sm animate-fade-in-up">
    <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 100 100\"><circle cx=\"30\" cy=\"30\" r=\"25\" fill=\"none\" stroke=\"white\" stroke-width=\"1\"/></svg>')"></div>
    <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">Selamat Datang, Admin</h1>
            <p class="text-white/80 text-sm mt-1">Gunakan panel ini untuk mengelola pengguna, memantau audit log, dan mengelola artikel literasi kesehatan ObatKu.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.users.index') }}" class="obk-btn text-xs bg-white/20 text-white hover:bg-white/30 border border-white/15">
                Kelola Pengguna
            </a>
            <a href="{{ route('admin.articles.index') }}" class="obk-btn text-xs bg-white text-[#185FA5] hover:bg-blue-50">
                Kelola Artikel
            </a>
        </div>
    </div>
</div>

{{-- Admin Stat Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8 animate-fade-in-up" style="animation-delay: 0.05s">
    
    {{-- Card 1: Users --}}
    <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm flex items-center gap-4 hover:shadow-md transition-shadow">
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#185FA5] flex items-center justify-center shrink-0">
            <i data-lucide="users" class="w-6 h-6"></i>
        </div>
        <div>
            <div class="text-2xl font-bold text-[#042C53]">{{ $totalUsers }}</div>
            <div class="text-xs text-gray-500 font-semibold uppercase tracking-wider mt-0.5">Total Pengguna</div>
        </div>
    </div>

    {{-- Card 2: Medicines --}}
    <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm flex items-center gap-4 hover:shadow-md transition-shadow">
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-[#1D9E75] flex items-center justify-center shrink-0">
            <i data-lucide="pill" class="w-6 h-6"></i>
        </div>
        <div>
            <div class="text-2xl font-bold text-[#042C53]">{{ $totalMedicines }}</div>
            <div class="text-xs text-gray-500 font-semibold uppercase tracking-wider mt-0.5">Obat Terdaftar</div>
        </div>
    </div>

    {{-- Card 3: Schedules --}}
    <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm flex items-center gap-4 hover:shadow-md transition-shadow">
        <div class="w-12 h-12 rounded-xl bg-[#fef5e6] text-[#EF9F27] flex items-center justify-center shrink-0">
            <i data-lucide="calendar" class="w-6 h-6"></i>
        </div>
        <div>
            <div class="text-2xl font-bold text-[#042C53]">{{ $totalSchedules }}</div>
            <div class="text-xs text-gray-500 font-semibold uppercase tracking-wider mt-0.5">Jadwal Aktif</div>
        </div>
    </div>

    {{-- Card 4: Articles --}}
    <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm flex items-center gap-4 hover:shadow-md transition-shadow">
        <div class="w-12 h-12 rounded-xl bg-purple-50 text-[#7F77DD] flex items-center justify-center shrink-0">
            <i data-lucide="book-open" class="w-6 h-6"></i>
        </div>
        <div>
            <div class="text-2xl font-bold text-[#042C53]">{{ $totalArticles }}</div>
            <div class="text-xs text-gray-500 font-semibold uppercase tracking-wider mt-0.5">Artikel Kesehatan</div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 animate-fade-in-up" style="animation-delay: 0.1s">
    
    {{-- Left: Recent Registered Users --}}
    <div class="lg:col-span-5 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between">
            <h3 class="font-bold text-[#042C53]">Pengguna Terdaftar Baru</h3>
            <a href="{{ route('admin.users.index') }}" class="text-xs text-[#185FA5] font-semibold hover:underline">Lihat Semua →</a>
        </div>
        
        <div class="divide-y divide-gray-50">
            @forelse($recentUsers as $u)
                <div class="px-6 py-3.5 flex items-center justify-between hover:bg-gray-50/50 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-blue-50 text-[#185FA5] flex items-center justify-center text-xs font-bold">
                            {{ strtoupper(substr($u->name, 0, 1)) }}
                        </div>
                        <div>
                            <span class="block text-sm font-semibold text-[#042C53]">{{ $u->name }}</span>
                            <span class="block text-xs text-gray-400">{{ $u->email }}</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="block text-[10px] text-gray-400 font-medium">{{ $u->created_at->format('d M Y') }}</span>
                        @if($u->is_active)
                            <span class="inline-block w-2 h-2 rounded-full bg-[#1D9E75]" title="Aktif"></span>
                        @else
                            <span class="inline-block w-2 h-2 rounded-full bg-[#E24B4A]" title="Nonaktif"></span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-gray-400 text-sm">Belum ada pengguna baru.</div>
            @endforelse
        </div>
    </div>

    {{-- Right: Activity Log Timeline --}}
    <div class="lg:col-span-7 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50">
            <h3 class="font-bold text-[#042C53]">Aktivitas Sistem Terkini (Audit Log)</h3>
        </div>
        
        <div class="p-6 space-y-6 max-h-[420px] overflow-y-auto">
            @forelse($recentActivity as $act)
                <div class="flex gap-4 relative">
                    {{-- Timeline Line --}}
                    @if(!$loop->last)
                        <span class="absolute left-4 top-8 bottom-0 w-0.5 bg-gray-100"></span>
                    @endif
                    
                    <div class="w-8.5 h-8.5 rounded-full bg-gray-50 flex items-center justify-center shrink-0 border border-gray-100 relative z-10">
                        <i data-lucide="activity" class="w-4 h-4 text-gray-400"></i>
                    </div>
                    <div class="pt-0.5">
                        <p class="text-sm font-semibold text-[#042C53]">
                            {{ $act->user->name ?? 'System' }}
                            <span class="font-normal text-gray-500">{{ $act->description }}</span>
                        </p>
                        <p class="text-xs text-gray-400 mt-1 flex items-center gap-1">
                            <i data-lucide="clock" class="w-3 h-3"></i>
                            {{ $act->created_at->diffForHumans() }}
                            @if($act->ip_address)
                                <span class="text-gray-300">·</span>
                                <span>IP: {{ $act->ip_address }}</span>
                            @endif
                        </p>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center text-gray-400 text-sm">Belum ada log aktivitas tercatat.</div>
            @endforelse
        </div>
    </div>

</div>

@endsection
