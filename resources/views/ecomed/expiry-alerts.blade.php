@extends('layouts.app')
@section('title', 'Expiry Alerts — EcoMed')
@section('header_title', 'EcoMed · Peringatan Kedaluwarsa')

@section('content')

{{-- Page Header --}}
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 animate-fade-in-up">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <a href="{{ route('ecomed.index') }}" class="text-gray-400 hover:text-[#185FA5] text-sm transition-colors">EcoMed</a>
            <span class="text-gray-300">/</span>
            <span class="text-sm font-medium text-[#042C53]">Expiry Alerts</span>
        </div>
        <h1 class="text-2xl font-bold text-[#042C53]">Peringatan Kedaluwarsa</h1>
        <p class="text-gray-500 mt-1">Daftar obat yang sudah atau akan segera kedaluwarsa.</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('ecomed.disposal-guide') }}" class="obk-btn obk-btn-outline text-sm flex items-center gap-2">
            <i data-lucide="book-open" class="w-4 h-4"></i> Panduan Buang
        </a>
        <button id="btnCheckExpiry" class="obk-btn obk-btn-success text-sm flex items-center gap-2">
            <i data-lucide="refresh-cw" class="w-4 h-4"></i> Cek Ulang
        </button>
    </div>
</div>

{{-- Stats Strip --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8 animate-fade-in-up" style="animation-delay:0.05s">
    <div class="bg-[#fde9e9] rounded-xl p-4 border border-[#E24B4A]/10">
        <div class="text-2xl font-bold text-[#E24B4A]">{{ $stats['expired'] ?? 0 }}</div>
        <div class="text-xs text-gray-500 mt-1 font-semibold uppercase">Kedaluwarsa</div>
    </div>
    <div class="bg-[#fef5e6] rounded-xl p-4 border border-[#EF9F27]/10">
        <div class="text-2xl font-bold text-[#EF9F27]">{{ $stats['expiring_7d'] ?? 0 }}</div>
        <div class="text-xs text-gray-500 mt-1 font-semibold uppercase">&lt; 7 Hari</div>
    </div>
    <div class="bg-blue-50 rounded-xl p-4 border border-blue-100">
        <div class="text-2xl font-bold text-[#185FA5]">{{ $stats['expiring_30d'] ?? 0 }}</div>
        <div class="text-xs text-gray-500 mt-1 font-semibold uppercase">&lt; 30 Hari</div>
    </div>
    <div class="bg-[#e6f7f1] rounded-xl p-4 border border-[#1D9E75]/10">
        <div class="text-2xl font-bold text-[#1D9E75]">{{ $stats['expiring_90d'] ?? 0 }}</div>
        <div class="text-xs text-gray-500 mt-1 font-semibold uppercase">&lt; 90 Hari</div>
    </div>
</div>

{{-- Notification Stats --}}
@if(isset($notifStats) && ($notifStats['total'] ?? 0) > 0)
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6 flex flex-wrap gap-6 items-center animate-fade-in-up" style="animation-delay:0.08s">
    <i data-lucide="bell" class="w-5 h-5 text-[#7F77DD] shrink-0"></i>
    <div class="text-sm text-gray-600">
        <span class="font-bold text-[#042C53]">{{ $notifStats['total'] ?? 0 }}</span> notifikasi total —
        <span class="font-bold text-[#185FA5]">{{ $notifStats['this_week'] ?? 0 }}</span> minggu ini.
        <a href="{{ route('ecomed.notifications') }}" class="ml-2 text-[#185FA5] hover:underline">Lihat Riwayat →</a>
    </div>
</div>
@endif

{{-- Expired Section --}}
@if(($categorised['expired'] ?? collect())->count() > 0)
<div class="mb-6 animate-fade-in-up" style="animation-delay:0.1s" id="expired">
    <div class="flex items-center gap-2 mb-3">
        <span class="w-3 h-3 rounded-full bg-[#E24B4A] animate-pulse-soft"></span>
        <h2 class="font-bold text-[#E24B4A] text-lg">Sudah Kedaluwarsa</h2>
        <span class="ml-1 bg-[#fde9e9] text-[#E24B4A] text-xs font-bold px-2 py-0.5 rounded-full">{{ $categorised['expired']->count() }}</span>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-[#E24B4A]/20 overflow-hidden">
        <table class="obk-table">
            <thead>
                <tr>
                    <th>Nama Obat</th>
                    <th>Bentuk</th>
                    <th>Tgl Kedaluwarsa</th>
                    <th>Stok</th>
                    <th>Untuk</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categorised['expired'] as $med)
                <tr>
                    <td>
                        <div class="font-semibold text-[#042C53]">{{ $med->medicine_name }}</div>
                        @if($med->generic_name)<div class="text-xs text-gray-400">{{ $med->generic_name }}</div>@endif
                    </td>
                    <td><span class="obk-badge obk-badge-warning capitalize">{{ $med->form ?? '-' }}</span></td>
                    <td>
                        <span class="text-[#E24B4A] font-semibold">{{ $med->expiry_date->format('d M Y') }}</span>
                        <div class="text-xs text-gray-400">{{ abs(now()->diffInDays($med->expiry_date)) }} hari lalu</div>
                    </td>
                    <td class="font-medium">{{ $med->stock }} {{ $med->unit }}</td>
                    <td class="text-sm text-gray-500">
                        @if($med->owner_type === \App\Models\FamilyMember::class)
                            <span class="text-[#7F77DD] font-medium">{{ $med->owner->name ?? 'Anggota' }}</span>
                        @else
                            <span class="text-[#185FA5] font-medium">Saya</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('ecomed.disposal-guide') }}?form={{ $med->form ?? 'tablet' }}" class="obk-btn obk-btn-danger text-xs px-3 py-1.5">
                            <i data-lucide="trash-2" class="w-3 h-3"></i> Panduan Buang
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Urgent (≤7d) --}}
@if(($categorised['urgent'] ?? collect())->count() > 0)
<div class="mb-6 animate-fade-in-up" style="animation-delay:0.15s" id="urgent">
    <div class="flex items-center gap-2 mb-3">
        <span class="w-3 h-3 rounded-full bg-[#EF9F27]"></span>
        <h2 class="font-bold text-[#EF9F27] text-lg">Kedaluwarsa ≤ 7 Hari</h2>
        <span class="ml-1 bg-[#fef5e6] text-[#EF9F27] text-xs font-bold px-2 py-0.5 rounded-full">{{ $categorised['urgent']->count() }}</span>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-[#EF9F27]/20 overflow-hidden">
        @include('ecomed._medicine_table', ['medicines' => $categorised['urgent'], 'urgency' => 'urgent'])
    </div>
</div>
@endif

{{-- Warning (≤30d) --}}
@if(($categorised['warning'] ?? collect())->count() > 0)
<div class="mb-6 animate-fade-in-up" style="animation-delay:0.2s" id="warning">
    <div class="flex items-center gap-2 mb-3">
        <span class="w-3 h-3 rounded-full bg-[#185FA5]"></span>
        <h2 class="font-bold text-[#185FA5] text-lg">Kedaluwarsa ≤ 30 Hari</h2>
        <span class="ml-1 bg-blue-50 text-[#185FA5] text-xs font-bold px-2 py-0.5 rounded-full">{{ $categorised['warning']->count() }}</span>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-blue-100 overflow-hidden">
        @include('ecomed._medicine_table', ['medicines' => $categorised['warning'], 'urgency' => 'warning'])
    </div>
</div>
@endif

{{-- Notice (≤90d) --}}
@if(($categorised['notice'] ?? collect())->count() > 0)
<div class="mb-6 animate-fade-in-up" style="animation-delay:0.25s" id="notice">
    <div class="flex items-center gap-2 mb-3">
        <span class="w-3 h-3 rounded-full bg-[#1D9E75]"></span>
        <h2 class="font-bold text-[#1D9E75] text-lg">Kedaluwarsa ≤ 90 Hari</h2>
        <span class="ml-1 bg-[#e6f7f1] text-[#1D9E75] text-xs font-bold px-2 py-0.5 rounded-full">{{ $categorised['notice']->count() }}</span>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-[#1D9E75]/20 overflow-hidden">
        @include('ecomed._medicine_table', ['medicines' => $categorised['notice'], 'urgency' => 'notice'])
    </div>
</div>
@endif

@if(collect($categorised)->flatten()->isEmpty())
<div class="bg-white rounded-xl shadow-sm border border-gray-100 py-16 text-center animate-fade-in-up">
    <i data-lucide="check-circle-2" class="w-16 h-16 text-[#1D9E75] mx-auto mb-4"></i>
    <h2 class="text-xl font-bold text-[#042C53] mb-2">Semua Obat Masih Aman!</h2>
    <p class="text-gray-500 max-w-md mx-auto">Tidak ada obat yang kedaluwarsa atau mendekati kedaluwarsa dalam 90 hari ke depan. Terus pantau secara rutin.</p>
</div>
@endif

@endsection

@push('head')
<script>
document.getElementById('btnCheckExpiry')?.addEventListener('click', async function () {
    this.disabled = true;
    this.textContent = 'Memeriksa...';
    const r = await fetch('{{ route("ecomed.check-expiry") }}', {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json'}
    });
    const d = await r.json();
    alert(d.message);
    if (d.dispatched > 0) location.reload();
    this.disabled = false;
    this.textContent = 'Cek Ulang';
});
</script>
@endpush
