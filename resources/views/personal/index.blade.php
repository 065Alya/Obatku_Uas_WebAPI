@extends('layouts.app')
@section('title', 'Mode Personal — Profil Saya')

@section('content')

{{-- ── Header ─────────────────────────────────────────────────────────── --}}
<div class="mb-8 animate-fade-in-up">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <span class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-widest text-[#185FA5] mb-2">
                <i data-lucide="user-circle" class="w-3.5 h-3.5"></i> Mode Personal
            </span>
            <h1 class="text-3xl font-bold text-[#042C53]">Halo, {{ $user->name }} 👋</h1>
            <p class="text-gray-500 mt-1">Pantau kesehatan pribadi dan kepatuhan minum obat Anda hari ini.</p>
        </div>
        <a href="{{ route('personal.edit') }}" class="obk-btn obk-btn-outline flex items-center gap-2 self-start sm:self-center">
            <i data-lucide="edit-3" class="w-4 h-4"></i> Edit Profil Personal
        </a>
    </div>
</div>

{{-- ── Stat Cards ───────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8 animate-fade-in-up" style="animation-delay: 0.05s">
    {{-- Adherence Rate --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col gap-1">
        <div class="w-10 h-10 rounded-xl bg-[#185FA5]/10 flex items-center justify-center mb-2">
            <i data-lucide="trending-up" class="w-5 h-5 text-[#185FA5]"></i>
        </div>
        <p class="text-xs font-semibold text-gray-400 uppercase">Kepatuhan Minggu Ini</p>
        <p class="text-2xl font-extrabold text-[#042C53]">{{ $adherenceRate }}%</p>
    </div>

    {{-- Active medicines --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col gap-1">
        <div class="w-10 h-10 rounded-xl bg-[#1D9E75]/10 flex items-center justify-center mb-2">
            <i data-lucide="pill" class="w-5 h-5 text-[#1D9E75]"></i>
        </div>
        <p class="text-xs font-semibold text-gray-400 uppercase">Obat Aktif</p>
        <p class="text-2xl font-extrabold text-[#042C53]">{{ $medicines->total() }}</p>
    </div>

    {{-- Schedules today --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col gap-1">
        <div class="w-10 h-10 rounded-xl bg-[#EF9F27]/10 flex items-center justify-center mb-2">
            <i data-lucide="calendar-clock" class="w-5 h-5 text-[#EF9F27]"></i>
        </div>
        <p class="text-xs font-semibold text-gray-400 uppercase">Jadwal Hari Ini</p>
        <p class="text-2xl font-extrabold text-[#042C53]">{{ count($todaySchedules) }}</p>
    </div>

    {{-- Low stock --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col gap-1">
        <div class="w-10 h-10 rounded-xl bg-[#E24B4A]/10 flex items-center justify-center mb-2">
            <i data-lucide="alert-circle" class="w-5 h-5 text-[#E24B4A]"></i>
        </div>
        <p class="text-xs font-semibold text-gray-400 uppercase">Stok Menipis</p>
        <p class="text-2xl font-extrabold text-[#042C53]">{{ $alerts['low_stock']->count() }}</p>
    </div>
</div>

{{-- ── Main Grid ────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 animate-fade-in-up" style="animation-delay: 0.1s">

    {{-- Left: Profile Card ------------------------------------------- --}}
    <div class="space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center gap-4 mb-5 pb-5 border-b border-gray-50">
                <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=185FA5&color=fff&size=96&rounded=true&bold=true' }}"
                     alt="Avatar"
                     class="w-16 h-16 rounded-full border-2 border-[#185FA5]/20 object-cover">
                <div>
                    <h3 class="font-bold text-[#042C53] text-lg leading-tight">{{ $user->name }}</h3>
                    <p class="text-xs text-gray-400">{{ $user->email }}</p>
                    @if($user->date_of_birth)
                        <p class="text-xs text-gray-400 mt-0.5">{{ $user->date_of_birth->age }} tahun</p>
                    @endif
                </div>
            </div>

            <div class="space-y-3 text-sm">
                @php $p = $profile; @endphp

                <div class="flex justify-between">
                    <span class="text-gray-400">Golongan Darah</span>
                    <span class="font-semibold text-[#042C53]">{{ $p->blood_type ?: '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Tinggi / Berat</span>
                    <span class="font-semibold text-[#042C53]">
                        {{ $p->height_cm ? $p->height_cm . ' cm' : '—' }} /
                        {{ $p->weight_kg ? $p->weight_kg . ' kg' : '—' }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Alergi</span>
                    <span class="font-semibold text-[#042C53] text-right max-w-[55%] truncate">{{ $p->allergies ?: '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Penyakit Kronis</span>
                    <span class="font-semibold text-[#042C53] text-right max-w-[55%] truncate">{{ $p->chronic_diseases ?: '—' }}</span>
                </div>

                @if($p->emergency_contact_name)
                    <div class="mt-3 pt-3 border-t border-gray-50">
                        <p class="text-xs text-gray-400 font-semibold uppercase mb-1">Kontak Darurat</p>
                        <p class="font-bold text-[#042C53]">{{ $p->emergency_contact_name }}</p>
                        <p class="text-xs text-gray-500">{{ $p->emergency_contact_phone }}</p>
                    </div>
                @endif
            </div>

            <div class="mt-5 pt-5 border-t border-gray-50 flex gap-2">
                <a href="{{ route('personal.edit') }}" class="obk-btn obk-btn-outline text-xs py-2 px-3 flex-1 flex items-center justify-center gap-1">
                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i> Edit Data
                </a>
                <a href="{{ route('medicines.create') }}" class="obk-btn obk-btn-primary text-xs py-2 px-3 flex-1 flex items-center justify-center gap-1">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Obat
                </a>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-bold text-[#042C53] mb-3">Aksi Cepat</h3>
            <div class="space-y-2">
                <a href="{{ route('schedules.index') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-[#F8FAFF] transition-colors group">
                    <div class="w-8 h-8 bg-[#185FA5]/10 rounded-lg flex items-center justify-center group-hover:bg-[#185FA5] group-hover:text-white text-[#185FA5] transition-colors">
                        <i data-lucide="calendar" class="w-4 h-4"></i>
                    </div>
                    <span class="text-sm font-semibold text-gray-700">Lihat Jadwal Konsumsi</span>
                </a>
                <a href="{{ route('medicines.index') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-[#F8FAFF] transition-colors group">
                    <div class="w-8 h-8 bg-[#1D9E75]/10 rounded-lg flex items-center justify-center group-hover:bg-[#1D9E75] group-hover:text-white text-[#1D9E75] transition-colors">
                        <i data-lucide="pill" class="w-4 h-4"></i>
                    </div>
                    <span class="text-sm font-semibold text-gray-700">Daftar Obat Saya</span>
                </a>
                <a href="{{ route('ecomed.index') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-[#F8FAFF] transition-colors group">
                    <div class="w-8 h-8 bg-[#1D9E75]/10 rounded-lg flex items-center justify-center group-hover:bg-[#1D9E75] group-hover:text-white text-[#1D9E75] transition-colors">
                        <i data-lucide="leaf" class="w-4 h-4"></i>
                    </div>
                    <span class="text-sm font-semibold text-gray-700">Modul EcoMed</span>
                </a>
                <a href="{{ route('apotek.index') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-[#F8FAFF] transition-colors group">
                    <div class="w-8 h-8 bg-[#EF9F27]/10 rounded-lg flex items-center justify-center group-hover:bg-[#EF9F27] group-hover:text-white text-[#EF9F27] transition-colors">
                        <i data-lucide="map-pin" class="w-4 h-4"></i>
                    </div>
                    <span class="text-sm font-semibold text-gray-700">Cari Apotek Terdekat</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Right: Schedules + Medicines --------------------------------- --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Today's Schedules --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-50">
                <h3 class="text-lg font-bold text-[#042C53] flex items-center gap-2">
                    <i data-lucide="calendar-check" class="w-5 h-5 text-[#185FA5]"></i>
                    Jadwal Hari Ini
                </h3>
                <a href="{{ route('schedules.index') }}" class="text-xs font-semibold text-[#185FA5] hover:text-[#042C53] transition-colors">Lihat Semua →</a>
            </div>

            @if(empty($todaySchedules) || count($todaySchedules) === 0)
                <div class="text-center py-8 text-gray-400">
                    <i data-lucide="check-circle-2" class="w-10 h-10 mx-auto mb-2 text-[#1D9E75]"></i>
                    <p class="text-sm font-semibold">Tidak ada jadwal konsumsi hari ini</p>
                    <a href="{{ route('schedules.create') }}" class="text-xs text-[#185FA5] mt-1 inline-block hover:underline">+ Buat jadwal baru</a>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($todaySchedules as $schedule)
                        <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl border border-gray-100 hover:bg-[#F8FAFF] transition-colors">
                            <div class="w-10 h-10 rounded-xl bg-[#185FA5] flex items-center justify-center text-white shrink-0 text-xs font-bold">
                                {{ \Carbon\Carbon::parse($schedule->schedule_time)->format('H:i') }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-[#042C53] truncate">
                                    {{ $schedule->medicine?->name ?? '—' }}
                                </p>
                                <p class="text-xs text-gray-400 capitalize">
                                    {{ str_replace('_', ' ', $schedule->frequency) }}
                                    @if($schedule->dosage_amount) · {{ $schedule->dosage_amount }}@endif
                                </p>
                            </div>
                            <form action="{{ route('schedules.log', $schedule->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="taken">
                                <button type="submit" class="text-xs font-bold px-3 py-1.5 rounded-lg bg-[#1D9E75] text-white hover:bg-[#18855f] transition-colors">
                                    Sudah Minum
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Active Medicines --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-50">
                <h3 class="text-lg font-bold text-[#042C53] flex items-center gap-2">
                    <i data-lucide="pill" class="w-5 h-5 text-[#1D9E75]"></i>
                    Obat Aktif Saya
                </h3>
                <a href="{{ route('medicines.index') }}" class="text-xs font-semibold text-[#185FA5] hover:text-[#042C53] transition-colors">Lihat Semua →</a>
            </div>

            @if($medicines->isEmpty())
                <div class="text-center py-8 text-gray-400">
                    <i data-lucide="pill" class="w-10 h-10 mx-auto mb-2"></i>
                    <p class="text-sm font-semibold">Belum ada obat terdaftar</p>
                    <a href="{{ route('medicines.create') }}" class="text-xs text-[#185FA5] mt-1 inline-block hover:underline">+ Tambah obat pertama</a>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($medicines as $med)
                        @php
                            $expiryClass = $med->isExpired()
                                ? 'border-l-[#E24B4A] bg-red-50'
                                : ($med->isExpiringSoon(30) ? 'border-l-[#EF9F27] bg-amber-50' : 'border-l-[#1D9E75] bg-green-50/50');
                        @endphp
                        <a href="{{ route('medicines.show', $med->id) }}"
                           class="flex items-start gap-3 p-4 rounded-xl border border-l-4 {{ $expiryClass }} hover:shadow-sm transition-shadow">
                            <div class="w-9 h-9 rounded-lg bg-white shadow-sm flex items-center justify-center shrink-0">
                                <i data-lucide="pill" class="w-4 h-4 text-[#185FA5]"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-[#042C53] truncate">{{ $med->name }}</p>
                                <p class="text-xs text-gray-400">{{ $med->stock }} {{ $med->unit }} tersisa</p>
                                @if($med->expiry_date)
                                    <p class="text-xs {{ $med->isExpired() ? 'text-[#E24B4A]' : ($med->isExpiringSoon(30) ? 'text-[#EF9F27]' : 'text-gray-400') }}">
                                        Exp: {{ $med->expiry_date->format('d M Y') }}
                                    </p>
                                @endif
                            </div>
                            @if($med->isLowStock())
                                <span class="ml-auto shrink-0 text-xs font-bold text-[#E24B4A] bg-red-50 border border-red-100 px-2 py-0.5 rounded-full">Stok Menipis</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Expiry Alerts --}}
        @if($alerts['expiring_soon']->count() > 0 || $alerts['expired']->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-[#EF9F27]/30 p-6">
            <h3 class="text-lg font-bold text-[#042C53] flex items-center gap-2 mb-4 pb-3 border-b border-gray-50">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-[#EF9F27]"></i>
                Peringatan Kedaluwarsa
            </h3>
            <div class="space-y-2">
                @foreach($alerts['expired'] as $med)
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-red-50 border border-red-100">
                        <i data-lucide="x-circle" class="w-4 h-4 text-[#E24B4A] shrink-0"></i>
                        <span class="text-sm font-semibold text-[#E24B4A]">{{ $med->name }}</span>
                        <span class="ml-auto text-xs text-[#E24B4A]">Sudah Kedaluwarsa</span>
                    </div>
                @endforeach
                @foreach($alerts['expiring_soon'] as $med)
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-amber-50 border border-amber-100">
                        <i data-lucide="alert-circle" class="w-4 h-4 text-[#EF9F27] shrink-0"></i>
                        <span class="text-sm font-semibold text-[#EF9F27]">{{ $med->name }}</span>
                        <span class="ml-auto text-xs text-[#EF9F27]">{{ now()->diffInDays($med->expiry_date) }} hari lagi</span>
                    </div>
                @endforeach
            </div>
            <a href="{{ route('ecomed.expiry-alerts') }}" class="mt-4 inline-flex items-center gap-1 text-xs font-semibold text-[#1D9E75] hover:underline">
                <i data-lucide="leaf" class="w-3.5 h-3.5"></i> Lihat panduan disposal EcoMed →
            </a>
        </div>
        @endif

    </div>
</div>

@endsection
