@extends('layouts.app')

@section('title', 'Pusat Notifikasi')

@section('content')
    <!-- Header -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 animate-fade-in-up">
        <div>
            <h1 class="text-2xl font-bold text-[#042C53]">Pusat Notifikasi</h1>
            <p class="text-gray-500 mt-1">Lacak dan pantau peringatan penting tentang stok obat, jadwal, dan interaksi kesehatan Anda.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            @if($unreadCount > 0)
                <form action="{{ route('alerts.mark-all-read') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="obk-btn obk-btn-outline flex items-center gap-2 text-sm py-2.5 px-4 bg-white">
                        <i data-lucide="check-check" class="w-4 h-4"></i>
                        Tandai Semua Dibaca
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Alert Success / Error Flash Messages -->
    @if(session('success'))
        <div class="mb-6 obk-alert obk-alert-success animate-fade-in-up" data-flash>
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 animate-fade-in-up" style="animation-delay: 0.1s">
        <!-- Left: Filters & Info -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
                <h3 class="text-base font-bold text-[#042C53] border-b border-gray-50 pb-2">Status Notifikasi</h3>
                
                <div class="space-y-2">
                    <div class="flex items-center justify-between p-3 bg-blue-50/50 rounded-xl">
                        <span class="text-sm font-medium text-gray-700 flex items-center gap-2">
                            <i data-lucide="bell" class="w-4 h-4 text-[#185FA5]"></i> Belum Dibaca
                        </span>
                        <span class="bg-[#185FA5] text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $unreadCount }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl border border-gray-50 text-gray-500">
                        <span class="text-sm font-medium flex items-center gap-2">
                            <i data-lucide="bell-off" class="w-4 h-4"></i> Total Riwayat
                        </span>
                        <span class="text-xs font-semibold">{{ $alerts->total() }}</span>
                    </div>
                </div>

                <div class="pt-2">
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Petunjuk Warna</h4>
                    <ul class="space-y-2 text-xs">
                        <li class="flex items-center gap-2 text-gray-600">
                            <span class="w-3 h-3 rounded-full bg-[#E24B4A] inline-block"></span>
                            <span>Interaksi Obat (Bahaya)</span>
                        </li>
                        <li class="flex items-center gap-2 text-gray-600">
                            <span class="w-3 h-3 rounded-full bg-[#EF9F27] inline-block"></span>
                            <span>Stok Menipis (Peringatan)</span>
                        </li>
                        <li class="flex items-center gap-2 text-gray-600">
                            <span class="w-3 h-3 rounded-full bg-[#185FA5] inline-block"></span>
                            <span>Pengingat Jadwal (Informasi)</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Right: Notifications List -->
        <div class="lg:col-span-3">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-50">
                    <h3 class="text-lg font-bold text-[#042C53] flex items-center gap-2">
                        <i data-lucide="inbox" class="w-5 h-5 text-[#185FA5]"></i>
                        Semua Notifikasi
                    </h3>
                </div>

                @if($alerts->isEmpty())
                    <div class="p-12 text-center">
                        <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="bell-off" class="w-8 h-8 text-[#185FA5]"></i>
                        </div>
                        <h4 class="text-lg font-bold text-[#042C53]">Kotak Masuk Bersih</h4>
                        <p class="text-gray-500 mt-1 max-w-sm mx-auto">Saat ini Anda tidak memiliki peringatan atau notifikasi aktif.</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach($alerts as $alert)
                            @php
                                $severityClasses = [
                                    'danger' => ['bg' => 'bg-[#fde9e9]', 'text' => 'text-[#E24B4A]', 'icon' => 'alert-triangle', 'border' => 'border-l-4 border-[#E24B4A]'],
                                    'warning' => ['bg' => 'bg-[#fef5e6]', 'text' => 'text-[#EF9F27]', 'icon' => 'alert-circle', 'border' => 'border-l-4 border-[#EF9F27]'],
                                    'info' => ['bg' => 'bg-[#e8f0fa]', 'text' => 'text-[#185FA5]', 'icon' => 'info', 'border' => 'border-l-4 border-[#185FA5]']
                                ];
                                $cfg = $severityClasses[$alert->severity] ?? $severityClasses['info'];
                            @endphp
                            <div class="p-6 flex items-start gap-4 transition-colors hover:bg-gray-50/50 {{ $cfg['border'] }} {{ !$alert->is_read ? 'bg-[#F8FAFF]' : '' }}">
                                <div class="w-10 h-10 rounded-xl {{ $cfg['bg'] }} {{ $cfg['text'] }} flex items-center justify-center shrink-0">
                                    <i data-lucide="{{ $cfg['icon'] }}" class="w-5 h-5"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xs font-bold uppercase tracking-wider {{ $cfg['text'] }}">
                                            {{ $alert->type_label }}
                                        </span>
                                        <span class="text-xs text-gray-400">·</span>
                                        <span class="text-xs text-gray-500" title="{{ $alert->created_at }}">
                                            {{ $alert->created_at->diffForHumans() }}
                                        </span>
                                        @if(!$alert->is_read)
                                            <span class="w-2 h-2 rounded-full bg-[#185FA5]" title="Baru"></span>
                                        @endif
                                    </div>
                                    <p class="text-sm font-semibold text-gray-900 leading-relaxed">{{ $alert->message }}</p>
                                </div>
                                <div class="flex items-center gap-1 shrink-0 ml-4">
                                    @if(!$alert->is_read)
                                        <form action="{{ route('alerts.read', $alert->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="p-2 text-gray-400 hover:text-[#185FA5] transition-colors rounded-lg hover:bg-blue-50" title="Tandai telah dibaca">
                                                <i data-lucide="check" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('alerts.destroy', $alert->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus notifikasi ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-gray-400 hover:text-[#E24B4A] transition-colors rounded-lg hover:bg-red-50" title="Hapus notifikasi">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if($alerts->hasPages())
                        <div class="p-6 border-t border-gray-100 bg-white">
                            {{ $alerts->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
@endsection
