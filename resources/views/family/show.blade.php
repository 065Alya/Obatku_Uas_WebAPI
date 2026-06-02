@extends('layouts.app')
@section('title', 'Detail Anggota — ' . $member->name)

@section('content')

{{-- Header --}}
<div class="mb-8 animate-fade-in-up">
    <a href="{{ route('family.index') }}"
       class="inline-flex items-center gap-2 text-sm font-semibold text-[#185FA5] hover:text-[#042C53] mb-3 transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Daftar Keluarga
    </a>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-[#185FA5] text-white rounded-xl flex items-center justify-center text-xl font-bold shrink-0">
                {{ substr($member->name, 0, 1) }}
            </div>
            <div>
                <h1 class="text-3xl font-bold text-[#042C53]">{{ $member->name }}</h1>
                <p class="text-gray-500 mt-0.5">
                    {{ ucfirst($member->relationship) }}
                    @if($member->birth_date) · {{ $member->age }} tahun @endif
                </p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('family.edit', $member->id) }}" class="obk-btn obk-btn-outline flex items-center gap-2">
                <i data-lucide="edit-3" class="w-4 h-4"></i> Edit Data
            </a>
            <a href="{{ route('medicines.create', ['family_member_id' => $member->id]) }}" class="obk-btn obk-btn-primary flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i> Tambah Obat
            </a>
        </div>
    </div>
</div>

{{-- Flash --}}
@if(session('success'))
    <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-100 flex items-center gap-3">
        <i data-lucide="check-circle-2" class="w-5 h-5 text-green-600 shrink-0"></i>
        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 animate-fade-in-up" style="animation-delay: 0.05s">

    {{-- ── Left Column: Profile & Expiry ───────────────────────────────── --}}
    <div class="space-y-6">

        {{-- Health Profile Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-bold text-[#042C53] uppercase tracking-wide mb-4 flex items-center gap-2">
                <i data-lucide="heart-pulse" class="w-4 h-4 text-[#1D9E75]"></i> Profil Kesehatan
            </h3>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-400">Hubungan</span>
                    <span class="font-semibold text-[#042C53]">{{ ucfirst($member->relationship) }}</span>
                </div>
                @if($member->birth_date)
                <div class="flex justify-between">
                    <span class="text-gray-400">Tanggal Lahir</span>
                    <span class="font-semibold text-[#042C53]">{{ $member->birth_date->format('d M Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Usia</span>
                    <span class="font-semibold text-[#042C53]">{{ $member->age }} tahun</span>
                </div>
                @endif
                <div class="pt-3 border-t border-gray-50">
                    <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Catatan Kesehatan</p>
                    <p class="text-sm text-gray-600 bg-gray-50 p-3 rounded-lg border border-gray-100">
                        {{ $member->health_notes ?: 'Tidak ada catatan medis khusus.' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                <p class="text-2xl font-extrabold text-[#042C53]">{{ $medicines->count() }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Obat Terdaftar</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                <p class="text-2xl font-extrabold text-[#042C53]">{{ $schedules->count() }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Jadwal Aktif</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                <p class="text-2xl font-extrabold {{ $expiringCount > 0 ? 'text-[#EF9F27]' : 'text-[#042C53]' }}">{{ $expiringCount }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Hampir Kedaluwarsa</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                <p class="text-2xl font-extrabold {{ $expiredCount > 0 ? 'text-[#E24B4A]' : 'text-[#042C53]' }}">{{ $expiredCount }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Sudah Kedaluwarsa</p>
            </div>
        </div>

        {{-- EcoMed Tip --}}
        @if($expiringCount > 0 || $expiredCount > 0)
        <div class="bg-[#E1F5EE] rounded-xl p-4 border border-[#1D9E75]/20">
            <div class="flex items-start gap-3">
                <i data-lucide="leaf" class="w-5 h-5 text-[#1D9E75] shrink-0 mt-0.5"></i>
                <div>
                    <p class="text-sm font-bold text-[#085538]">Peringatan EcoMed</p>
                    <p class="text-xs text-[#1D9E75] mt-0.5">
                        {{ $expiringCount + $expiredCount }} obat memerlukan perhatian.
                    </p>
                    <a href="{{ route('ecomed.expiry-alerts') }}" class="text-xs font-semibold text-[#1D9E75] underline mt-1 inline-block">
                        Lihat panduan kedaluwarsa →
                    </a>
                </div>
            </div>
        </div>
        @endif

    </div>

    {{-- ── Right Column: Medicines + Schedules + History ───────────────── --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Medicines --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-50">
                <h3 class="text-lg font-bold text-[#042C53] flex items-center gap-2">
                    <i data-lucide="pill" class="w-5 h-5 text-[#185FA5]"></i>
                    Obat Terdaftar
                </h3>
                <a href="{{ route('medicines.create', ['family_member_id' => $member->id]) }}"
                   class="text-sm font-bold text-[#185FA5] hover:text-[#042C53] flex items-center gap-1 transition-colors">
                    <i data-lucide="plus" class="w-4 h-4"></i> Tambah
                </a>
            </div>

            @if($medicines->isEmpty())
                <div class="text-center py-8 text-gray-400">
                    <i data-lucide="pill" class="w-10 h-10 mx-auto mb-2"></i>
                    <p class="text-sm font-semibold">Belum ada obat terdaftar</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($medicines as $med)
                        @php
                            $borderColor = $med->isExpired()
                                ? 'border-l-[#E24B4A]'
                                : ($med->isExpiringSoon(30) ? 'border-l-[#EF9F27]' : 'border-l-[#1D9E75]');
                        @endphp
                        <div class="flex items-start gap-4 p-4 rounded-xl border border-l-4 {{ $borderColor }} bg-gray-50 hover:bg-[#F8FAFF] transition-colors">
                            <div class="w-9 h-9 rounded-lg bg-white shadow-sm flex items-center justify-center shrink-0">
                                <i data-lucide="pill" class="w-4 h-4 text-[#185FA5]"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="text-sm font-bold text-[#042C53]">{{ $med->name }}</p>
                                    @if($med->isExpired())
                                        <span class="text-xs font-bold text-[#E24B4A] bg-red-50 px-2 py-0.5 rounded-full">Kedaluwarsa</span>
                                    @elseif($med->isExpiringSoon(30))
                                        <span class="text-xs font-bold text-[#EF9F27] bg-amber-50 px-2 py-0.5 rounded-full">{{ now()->diffInDays($med->expiry_date) }}h lagi</span>
                                    @endif
                                    @if($med->isLowStock())
                                        <span class="text-xs font-bold text-[#E24B4A] bg-red-50 px-2 py-0.5 rounded-full">Stok Menipis</span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $med->stock }} {{ $med->unit }} · {{ $med->form }}</p>
                                @if($med->expiry_date)
                                    <p class="text-xs {{ $med->isExpired() ? 'text-[#E24B4A]' : 'text-gray-400' }}">
                                        Exp: {{ $med->expiry_date->format('d M Y') }}
                                    </p>
                                @endif
                            </div>
                            <div class="flex items-center gap-1 shrink-0">
                                <a href="{{ route('medicines.show', $med->id) }}"
                                   class="p-1.5 rounded-lg text-[#185FA5] hover:bg-blue-50 transition-colors" title="Lihat Detail">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                <a href="{{ route('medicines.literasi', $med->id) }}"
                                   class="p-1.5 rounded-lg text-[#7F77DD] hover:bg-purple-50 transition-colors" title="Kartu Literasi Obat">
                                    <i data-lucide="book-open" class="w-4 h-4"></i>
                                </a>
                                <a href="{{ route('medicines.edit', $med->id) }}"
                                   class="p-1.5 rounded-lg text-gray-400 hover:text-[#185FA5] hover:bg-blue-50 transition-colors" title="Edit Obat">
                                    <i data-lucide="edit" class="w-4 h-4"></i>
                                </a>
                                <form action="{{ route('medicines.destroy', $med->id) }}" method="POST"
                                      onsubmit="return confirm('Hapus obat {{ addslashes($med->name) }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-gray-400 hover:text-[#E24B4A] hover:bg-red-50 transition-colors" title="Hapus Obat">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Active Schedules --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-50">
                <h3 class="text-lg font-bold text-[#042C53] flex items-center gap-2">
                    <i data-lucide="clock" class="w-5 h-5 text-[#EF9F27]"></i>
                    Jadwal Konsumsi
                </h3>
                <a href="{{ route('schedules.create') }}" class="text-sm font-bold text-[#185FA5] hover:text-[#042C53] flex items-center gap-1 transition-colors">
                    <i data-lucide="plus" class="w-4 h-4"></i> Buat
                </a>
            </div>

            @if($schedules->isEmpty())
                <p class="text-sm text-gray-400 text-center py-6">Belum ada jadwal konsumsi aktif.</p>
            @else
                <div class="space-y-3">
                    @foreach($schedules as $sched)
                        <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl border border-gray-100 hover:bg-[#F8FAFF] transition-colors">
                            <div class="w-10 h-10 rounded-xl bg-[#EF9F27]/10 flex items-center justify-center text-[#EF9F27] shrink-0 text-xs font-bold">
                                {{ \Carbon\Carbon::parse($sched->schedule_time)->format('H:i') }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-[#042C53] truncate">
                                    {{ $sched->medicine?->name ?? '—' }}
                                </p>
                                <p class="text-xs text-gray-400 capitalize">
                                    {{ str_replace('_', ' ', $sched->frequency) }}
                                    @if($sched->dosage_amount) · {{ $sched->dosage_amount }}@endif
                                </p>
                            </div>
                            @if($sched->is_active)
                                <span class="text-xs font-bold text-[#1D9E75] bg-green-50 px-2 py-1 rounded-full">Aktif</span>
                            @else
                                <span class="text-xs font-bold text-gray-400 bg-gray-100 px-2 py-1 rounded-full">Nonaktif</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Recent Consumptions --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-[#042C53] flex items-center gap-2 mb-4 pb-3 border-b border-gray-50">
                <i data-lucide="history" class="w-5 h-5 text-gray-400"></i>
                Riwayat Konsumsi (7 Hari Terakhir)
            </h3>

            @if($recentConsumptions->isEmpty())
                <p class="text-sm text-gray-400 text-center py-6">Belum ada riwayat konsumsi tercatat.</p>
            @else
                <div class="space-y-2">
                    @foreach($recentConsumptions as $c)
                        @php
                            $statusClass = match($c->status) {
                                'taken'   => ['dot' => 'bg-[#1D9E75]', 'badge' => 'text-[#085538] bg-green-50'],
                                'skipped' => ['dot' => 'bg-[#EF9F27]', 'badge' => 'text-amber-800 bg-amber-50'],
                                default   => ['dot' => 'bg-[#E24B4A]', 'badge' => 'text-red-800 bg-red-50'],
                            };
                            $statusLabel = match($c->status) {
                                'taken'   => 'Sudah Minum',
                                'skipped' => 'Dilewati',
                                default   => 'Terlewat',
                            };
                        @endphp
                        <div class="flex items-center gap-3 p-3 rounded-lg border border-gray-100 hover:bg-gray-50 transition-colors">
                            <span class="w-2 h-2 rounded-full {{ $statusClass['dot'] }} shrink-0"></span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-[#042C53] truncate">
                                    {{ $c->medicine?->name ?? '—' }}
                                </p>
                                <p class="text-xs text-gray-400">
                                    {{ $c->consumed_at ? $c->consumed_at->format('d M Y, H:i') : '—' }}
                                </p>
                            </div>
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $statusClass['badge'] }}">{{ $statusLabel }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</div>

@endsection
