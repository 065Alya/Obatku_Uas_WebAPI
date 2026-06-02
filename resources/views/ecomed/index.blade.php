@extends('layouts.app')
@section('title', 'EcoMed')
@section('header_title', 'EcoMed · Pengelolaan Obat Ramah Lingkungan')

@section('content')

{{-- Page Header --}}
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 animate-fade-in-up">
    <div>
        <div class="flex items-center gap-3 mb-1">
            <div class="w-10 h-10 rounded-xl bg-[#1D9E75]/10 flex items-center justify-center">
                <i data-lucide="leaf" class="w-5 h-5 text-[#1D9E75]"></i>
            </div>
            <h1 class="text-2xl font-bold text-[#042C53]">EcoMed Dashboard</h1>
        </div>
        <p class="text-gray-500 mt-1 ml-13">Pantau kedaluwarsa, panduan pembuangan, dan rekam jejak limbah obat Anda — demi lingkungan yang lebih sehat.</p>
    </div>
    <div class="flex gap-3 flex-wrap">
        <a href="{{ route('ecomed.expiry-alerts') }}" class="obk-btn obk-btn-outline flex items-center gap-2 text-sm">
            <i data-lucide="bell" class="w-4 h-4"></i>
            Expiry Alerts
        </a>
        <button id="btnCheckExpiry" class="obk-btn obk-btn-success flex items-center gap-2 text-sm">
            <i data-lucide="refresh-cw" class="w-4 h-4"></i>
            Cek Sekarang
        </button>
    </div>
</div>

{{-- SDG Banner --}}
<div class="mb-8 rounded-2xl overflow-hidden bg-gradient-to-r from-[#042C53] to-[#185FA5] p-6 text-white relative animate-fade-in-up" style="animation-delay:0.05s">
    <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 100 100\"><circle cx=\"20\" cy=\"20\" r=\"15\" fill=\"none\" stroke=\"white\" stroke-width=\"1\"/><circle cx=\"80\" cy=\"80\" r=\"20\" fill=\"none\" stroke=\"white\" stroke-width=\"1\"/></svg>')"></div>
    <div class="relative flex flex-col md:flex-row md:items-center gap-6">
        <div class="flex-1">
            <div class="flex items-center gap-2 mb-2">
                <span class="bg-[#1D9E75] text-white text-xs font-bold px-3 py-1 rounded-full">SDG 12</span>
                <span class="bg-white/20 text-white text-xs font-bold px-3 py-1 rounded-full">Konsumsi & Produksi Bertanggung Jawab</span>
            </div>
            <h2 class="text-xl font-bold mb-1">Kurangi Limbah Obat, Jaga Lingkungan</h2>
            <p class="text-white/80 text-sm leading-relaxed max-w-xl">Modul EcoMed membantu Anda memantau obat kedaluwarsa dan membuangnya dengan cara yang aman dan ramah lingkungan. Setiap tindakan kecil berkontribusi pada SDG 12.</p>
        </div>
        <div class="grid grid-cols-2 gap-3 md:w-64">
            <div class="bg-white/10 rounded-xl p-3 text-center backdrop-blur">
                <div class="text-2xl font-bold">{{ $stats['expiring_90d'] ?? 0 }}</div>
                <div class="text-xs text-white/70 mt-0.5">Akan Kedaluwarsa</div>
            </div>
            <div class="bg-white/10 rounded-xl p-3 text-center backdrop-blur">
                <div class="text-2xl font-bold text-[#EF9F27]">{{ $stats['expired'] ?? 0 }}</div>
                <div class="text-xs text-white/70 mt-0.5">Sudah Kedaluwarsa</div>
            </div>
            <div class="bg-white/10 rounded-xl p-3 text-center backdrop-blur">
                <div class="text-2xl font-bold text-[#1D9E75]">{{ $stats['waste_verified'] ?? 0 }}</div>
                <div class="text-xs text-white/70 mt-0.5">Laporan Terverifikasi</div>
            </div>
            <div class="bg-white/10 rounded-xl p-3 text-center backdrop-blur">
                <div class="text-2xl font-bold">{{ number_format($stats['waste_quantity'] ?? 0, 1) }}</div>
                <div class="text-xs text-white/70 mt-0.5">Unit Dibuang</div>
            </div>
        </div>
    </div>
</div>

{{-- Expiry Category Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8 animate-fade-in-up" style="animation-delay:0.1s">

    {{-- Expired --}}
    <a href="{{ route('ecomed.expiry-alerts') }}?band=expired" class="bg-white rounded-xl p-5 border border-[#E24B4A]/20 shadow-sm hover:shadow-md transition-all group">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-lg bg-[#E24B4A]/10 flex items-center justify-center group-hover:bg-[#E24B4A]/20 transition-colors">
                <i data-lucide="x-circle" class="w-5 h-5 text-[#E24B4A]"></i>
            </div>
            <span class="text-xs font-semibold text-[#E24B4A] bg-[#fde9e9] px-2 py-0.5 rounded-full">KRITIS</span>
        </div>
        <div class="text-3xl font-bold text-[#042C53] mb-1">{{ $stats['expired'] ?? 0 }}</div>
        <div class="text-sm text-gray-500">Sudah Kedaluwarsa</div>
        <div class="mt-3 text-xs text-[#E24B4A] font-medium flex items-center gap-1">
            <i data-lucide="arrow-right" class="w-3 h-3"></i>Buang Segera
        </div>
    </a>

    {{-- Expiring 7d --}}
    <a href="{{ route('ecomed.expiry-alerts') }}?band=urgent" class="bg-white rounded-xl p-5 border border-[#EF9F27]/20 shadow-sm hover:shadow-md transition-all group">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-lg bg-[#EF9F27]/10 flex items-center justify-center group-hover:bg-[#EF9F27]/20 transition-colors">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-[#EF9F27]"></i>
            </div>
            <span class="text-xs font-semibold text-[#EF9F27] bg-[#fef5e6] px-2 py-0.5 rounded-full">≤7 HARI</span>
        </div>
        <div class="text-3xl font-bold text-[#042C53] mb-1">{{ $stats['expiring_7d'] ?? 0 }}</div>
        <div class="text-sm text-gray-500">Kedaluwarsa &lt; 7 Hari</div>
        <div class="mt-3 text-xs text-[#EF9F27] font-medium flex items-center gap-1">
            <i data-lucide="arrow-right" class="w-3 h-3"></i>Perlu Perhatian
        </div>
    </a>

    {{-- Expiring 30d --}}
    <a href="{{ route('ecomed.expiry-alerts') }}?band=warning" class="bg-white rounded-xl p-5 border border-blue-100 shadow-sm hover:shadow-md transition-all group">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center group-hover:bg-blue-100 transition-colors">
                <i data-lucide="clock" class="w-5 h-5 text-[#185FA5]"></i>
            </div>
            <span class="text-xs font-semibold text-[#185FA5] bg-blue-50 px-2 py-0.5 rounded-full">≤30 HARI</span>
        </div>
        <div class="text-3xl font-bold text-[#042C53] mb-1">{{ $stats['expiring_30d'] ?? 0 }}</div>
        <div class="text-sm text-gray-500">Kedaluwarsa &lt; 30 Hari</div>
        <div class="mt-3 text-xs text-[#185FA5] font-medium flex items-center gap-1">
            <i data-lucide="arrow-right" class="w-3 h-3"></i>Pantau Terus
        </div>
    </a>

    {{-- Expiring 90d --}}
    <a href="{{ route('ecomed.expiry-alerts') }}?band=notice" class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-all group">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-lg bg-[#1D9E75]/10 flex items-center justify-center group-hover:bg-[#1D9E75]/20 transition-colors">
                <i data-lucide="calendar" class="w-5 h-5 text-[#1D9E75]"></i>
            </div>
            <span class="text-xs font-semibold text-[#1D9E75] bg-[#e6f7f1] px-2 py-0.5 rounded-full">≤90 HARI</span>
        </div>
        <div class="text-3xl font-bold text-[#042C53] mb-1">{{ $stats['expiring_90d'] ?? 0 }}</div>
        <div class="text-sm text-gray-500">Kedaluwarsa &lt; 90 Hari</div>
        <div class="mt-3 text-xs text-[#1D9E75] font-medium flex items-center gap-1">
            <i data-lucide="arrow-right" class="w-3 h-3"></i>Informasi
        </div>
    </a>
</div>

{{-- Main Grid: Expiring + Disposal Guides --}}
<div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mb-8">

    {{-- Expiring Soon List --}}
    <div class="lg:col-span-3 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden animate-fade-in-up" style="animation-delay:0.15s">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-bold text-[#042C53]">Obat Mendekati Kedaluwarsa</h3>
            <a href="{{ route('ecomed.expiry-alerts') }}" class="text-sm text-[#185FA5] font-medium hover:underline">Lihat Semua →</a>
        </div>
        <div class="divide-y divide-gray-50">
            @php
                $urgentList  = $categorised['urgent']  ?? collect();
                $warningList = $categorised['warning'] ?? collect();
                $expiredList = $categorised['expired'] ?? collect();
                $displayList = $expiredList->concat($urgentList)->concat($warningList)->take(6);
            @endphp

            @forelse($displayList as $med)
            @php
                $daysLeft = now()->diffInDays($med->expiry_date, false);
                $isExpired = $daysLeft < 0;
                $colorClass = $isExpired ? 'text-[#E24B4A] bg-[#fde9e9]'
                    : ($daysLeft <= 7 ? 'text-[#EF9F27] bg-[#fef5e6]'
                    : 'text-[#185FA5] bg-blue-50');
                $labelText = $isExpired ? 'KEDALUWARSA' : "H-{$daysLeft}";
            @endphp
            <div class="px-6 py-4 flex items-center gap-4 hover:bg-gray-50 transition-colors">
                <div class="w-10 h-10 rounded-lg {{ $isExpired ? 'bg-[#fde9e9]' : 'bg-blue-50' }} flex items-center justify-center shrink-0">
                    <i data-lucide="{{ $isExpired ? 'x-circle' : 'pill' }}" class="w-5 h-5 {{ $isExpired ? 'text-[#E24B4A]' : 'text-[#185FA5]' }}"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-[#042C53] truncate">{{ $med->medicine_name }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        Exp: {{ $med->expiry_date->format('d M Y') }}
                        @if($med->owner_type === \App\Models\FamilyMember::class)
                            · <span class="text-[#7F77DD]">{{ $med->owner->name ?? 'Anggota' }}</span>
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <span class="text-xs font-bold px-2 py-1 rounded-lg {{ $colorClass }}">{{ $labelText }}</span>
                    @if($isExpired)
                        <a href="{{ route('ecomed.disposal-guide') }}?form={{ $med->form ?? 'tablet' }}" class="text-xs text-[#1D9E75] font-medium hover:underline">Panduan →</a>
                    @endif
                </div>
            </div>
            @empty
            <div class="px-6 py-12 text-center">
                <i data-lucide="check-circle-2" class="w-12 h-12 text-[#1D9E75] mx-auto mb-3"></i>
                <p class="font-semibold text-gray-700">Semua obat masih aman!</p>
                <p class="text-sm text-gray-400 mt-1">Tidak ada obat yang mendekati kedaluwarsa dalam 90 hari.</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Quick Actions + Disposal Guides --}}
    <div class="lg:col-span-2 space-y-4 animate-fade-in-up" style="animation-delay:0.2s">

        {{-- Quick Actions --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-bold text-[#042C53] mb-4">Aksi Cepat</h3>
            <div class="space-y-2">
                <a href="{{ route('ecomed.waste-reports') }}" class="flex items-center gap-3 p-3 rounded-xl bg-[#1D9E75]/5 hover:bg-[#1D9E75]/10 transition-colors group">
                    <div class="w-9 h-9 rounded-lg bg-[#1D9E75]/10 flex items-center justify-center group-hover:bg-[#1D9E75]/20 transition-colors">
                        <i data-lucide="clipboard-list" class="w-4 h-4 text-[#1D9E75]"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-[#042C53]">Rekam Pembuangan</p>
                        <p class="text-xs text-gray-400">Catat obat yang sudah dibuang</p>
                    </div>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-gray-300 ml-auto"></i>
                </a>
                <a href="{{ route('ecomed.disposal-guide') }}" class="flex items-center gap-3 p-3 rounded-xl bg-blue-50/50 hover:bg-blue-50 transition-colors group">
                    <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center">
                        <i data-lucide="book-open" class="w-4 h-4 text-[#185FA5]"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-[#042C53]">Panduan Pembuangan</p>
                        <p class="text-xs text-gray-400">Cara buang obat yang benar</p>
                    </div>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-gray-300 ml-auto"></i>
                </a>
                <a href="{{ route('ecomed.notifications') }}" class="flex items-center gap-3 p-3 rounded-xl bg-purple-50/50 hover:bg-purple-50 transition-colors group">
                    <div class="w-9 h-9 rounded-lg bg-purple-100 flex items-center justify-center">
                        <i data-lucide="bell-ring" class="w-4 h-4 text-[#7F77DD]"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-[#042C53]">Riwayat Notifikasi</p>
                        <p class="text-xs text-gray-400">Notifikasi kedaluwarsa terkirim</p>
                    </div>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-gray-300 ml-auto"></i>
                </a>
            </div>
        </div>

        {{-- Disposal Guide Snippets --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-bold text-[#042C53] mb-4">Panduan Pembuangan</h3>
            <div class="space-y-3">
                @forelse($guides->take(4) as $guide)
                <a href="{{ route('ecomed.disposal-guide') }}?form={{ $guide->medicine_form }}" class="flex items-start gap-3 p-3 rounded-xl hover:bg-gray-50 transition-colors group">
                    <div class="w-8 h-8 rounded-lg bg-[#1D9E75]/10 flex items-center justify-center shrink-0 mt-0.5">
                        <i data-lucide="recycle" class="w-4 h-4 text-[#1D9E75]"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-[#042C53] capitalize">{{ $guide->medicine_form }}</p>
                        <p class="text-xs text-gray-400 mt-0.5 line-clamp-2">{{ Str::limit($guide->disposal_steps ?? 'Lihat panduan lengkap...', 60) }}</p>
                    </div>
                </a>
                @empty
                <p class="text-sm text-gray-400 text-center py-4">Belum ada panduan tersedia.</p>
                @endforelse
                <a href="{{ route('ecomed.disposal-guide') }}" class="block text-center text-sm text-[#185FA5] font-medium py-2 hover:underline">
                    Lihat Semua Panduan →
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Waste Stats --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 animate-fade-in-up" style="animation-delay:0.25s">
    <div class="flex items-center justify-between mb-5">
        <h3 class="font-bold text-[#042C53]">Statistik Pembuangan Limbah Obat</h3>
        <a href="{{ route('ecomed.waste-reports') }}" class="text-sm text-[#185FA5] font-medium hover:underline">Lihat Detail →</a>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="text-center p-4 rounded-xl bg-[#e6f7f1]">
            <div class="text-2xl font-bold text-[#1D9E75]">{{ $stats['waste_total'] ?? 0 }}</div>
            <div class="text-xs text-gray-500 mt-1 font-medium">Total Laporan</div>
        </div>
        <div class="text-center p-4 rounded-xl bg-blue-50">
            <div class="text-2xl font-bold text-[#185FA5]">{{ $stats['waste_verified'] ?? 0 }}</div>
            <div class="text-xs text-gray-500 mt-1 font-medium">Terverifikasi</div>
        </div>
        <div class="text-center p-4 rounded-xl bg-[#fef5e6]">
            <div class="text-2xl font-bold text-[#EF9F27]">{{ $stats['waste_pending'] ?? 0 }}</div>
            <div class="text-xs text-gray-500 mt-1 font-medium">Menunggu</div>
        </div>
        <div class="text-center p-4 rounded-xl bg-purple-50">
            <div class="text-2xl font-bold text-[#7F77DD]">{{ number_format($stats['waste_quantity'] ?? 0, 1) }}</div>
            <div class="text-xs text-gray-500 mt-1 font-medium">Total Unit Dibuang</div>
        </div>
    </div>
</div>

@endsection

@push('head')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('btnCheckExpiry');
    if (btn) {
        btn.addEventListener('click', async function () {
            btn.disabled = true;
            btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg> Memeriksa...';
            try {
                const resp = await fetch('{{ route("ecomed.check-expiry") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                });
                const data = await resp.json();
                alert(data.message ?? 'Selesai.');
                if (data.dispatched > 0) location.reload();
            } catch (e) {
                alert('Gagal memeriksa. Silakan coba lagi.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i data-lucide="refresh-cw" class="w-4 h-4"></i> Cek Sekarang';
                lucide.createIcons();
            }
        });
    }
});
</script>
@endpush
