@extends('layouts.app')
@section('title', 'Detail Obat')

@section('content')
    <!-- Header -->
    <div class="mb-8 animate-fade-in-up">
        <a href="{{ route('medicines.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-[#185FA5] hover:text-[#042C53] mb-3 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Daftar Obat
        </a>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <span class="obk-badge obk-badge-primary mb-2">
                    {{ $medicine->category ? $medicine->category->name : 'Tanpa Kategori' }}
                </span>
                <h1 class="text-3xl font-bold text-[#042C53]">{{ $medicine->name }}</h1>
                @if($medicine->generic_name)
                    <p class="text-sm text-gray-500 mt-1 italic">Nama Generik: {{ $medicine->generic_name }}</p>
                @endif
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('medicines.edit', $medicine->id) }}" class="obk-btn obk-btn-outline flex items-center gap-2">
                    <i data-lucide="edit-3" class="w-4 h-4"></i> Edit Obat
                </a>
                <form action="{{ route('medicines.destroy', $medicine->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus obat ini beserta seluruh jadwalnya?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="obk-btn obk-btn-danger flex items-center gap-2">
                        <i data-lucide="trash-2" class="w-4 h-4"></i> Hapus Obat
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 animate-fade-in-up" style="animation-delay: 0.1s">
        <!-- Main Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Medical Info Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
                <div>
                    <h3 class="text-lg font-bold text-[#042C53] border-b border-gray-50 pb-2 mb-3">Deskripsi & Petunjuk</h3>
                    <p class="text-gray-600 leading-relaxed whitespace-pre-line">{{ $medicine->description ?: 'Tidak ada deskripsi atau petunjuk penggunaan khusus.' }}</p>
                </div>

                @if($medicine->side_effects)
                    <div>
                        <h3 class="text-lg font-bold text-[#042C53] border-b border-gray-50 pb-2 mb-3">Efek Samping</h3>
                        <div class="p-4 bg-[#fde9e9] border-l-4 border-[#E24B4A] rounded-r-lg text-[#7e1d1d] text-sm">
                            <p class="leading-relaxed whitespace-pre-line">{{ $medicine->side_effects }}</p>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 pt-4 border-t border-gray-50">
                    <div>
                        <p class="text-xs text-gray-400 font-semibold uppercase">Bentuk Sediaan</p>
                        <p class="text-sm font-semibold text-[#042C53] capitalize">{{ $medicine->form }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 font-semibold uppercase">Produsen</p>
                        <p class="text-sm font-semibold text-[#042C53]">{{ $medicine->manufacturer ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 font-semibold uppercase">Kepemilikan</p>
                        <p class="text-sm font-semibold text-[#042C53]">
                            @if($medicine->owner_type === \App\Models\User::class)
                                Diri Sendiri (Utama)
                            @else
                                {{ $medicine->owner ? $medicine->owner->name : 'Anggota Keluarga' }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Schedules Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between border-b border-gray-50 pb-3 mb-4">
                    <h3 class="text-lg font-bold text-[#042C53]">Jadwal Penggunaan</h3>
                    <a href="{{ route('schedules.create', ['medicine_id' => $medicine->id]) }}" class="text-sm font-bold text-[#185FA5] hover:text-[#042C53] flex items-center gap-1">
                        <i data-lucide="plus" class="w-4 h-4"></i> Buat Jadwal
                    </a>
                </div>

                @if($medicine->schedules->isEmpty())
                    <p class="text-gray-500 text-sm text-center py-6">Belum ada jadwal konsumsi untuk obat ini.</p>
                @else
                    <div class="space-y-3">
                        @foreach($medicine->schedules as $sched)
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl hover:bg-[#F8FAFF] transition-colors border border-gray-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-[#185FA5]/10 flex items-center justify-center text-[#185FA5]">
                                        <i data-lucide="clock" class="w-5 h-5"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-[#042C53]">{{ $sched->schedule_time->format('H:i') }} WIB</p>
                                        <p class="text-xs text-gray-500 capitalize">Frekuensi: {{ str_replace('_', ' ', $sched->frequency) }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    @if($sched->is_active)
                                        <span class="obk-badge obk-badge-success">Aktif</span>
                                    @else
                                        <span class="obk-badge obk-badge-danger">Nonaktif</span>
                                    @endif
                                    <a href="{{ route('schedules.edit', $sched->id) }}" class="p-1.5 text-gray-400 hover:text-[#185FA5] hover:bg-white rounded-lg transition-colors">
                                        <i data-lucide="edit" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar Details -->
        <div class="space-y-6">
            <!-- Stock Widget -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
                <h3 class="text-lg font-bold text-[#042C53] border-b border-gray-50 pb-2">Status Stok</h3>
                
                <div class="flex items-baseline justify-between">
                    <p class="text-4xl font-extrabold text-[#042C53]">{{ $medicine->stock }}</p>
                    <p class="text-sm font-semibold text-gray-400 uppercase tracking-wide">{{ $medicine->unit }} tersisa</p>
                </div>

                <!-- Progress Bar -->
                <div class="w-full bg-gray-100 rounded-full h-2">
                    @php
                        $percentage = min(100, $medicine->stock > 0 ? ($medicine->stock / max(1, $medicine->stock_alert * 4)) * 100 : 0);
                        $barColor = $medicine->isLowStock() ? 'bg-[#E24B4A]' : 'bg-[#1D9E75]';
                    @endphp
                    <div class="{{ $barColor }} h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                </div>

                @if($medicine->isLowStock())
                    <div class="p-3 bg-[#fde9e9] text-[#7e1d1d] text-xs font-semibold rounded-lg flex items-center gap-2">
                        <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                        Peringatan: Stok sudah di bawah batas minimum ({{ $medicine->stock_alert }} {{ $medicine->unit }}).
                    </div>
                @else
                    <div class="p-3 bg-[#e6f7f1] text-[#085538] text-xs font-semibold rounded-lg flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4 shrink-0"></i>
                        Stok dalam kondisi aman.
                    </div>
                @endif
            </div>

            <!-- Expiry & Price Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
                <h3 class="text-lg font-bold text-[#042C53] border-b border-gray-50 pb-2">Tanggal Kedaluwarsa</h3>
                <div>
                    <div class="flex items-center gap-2">
                        <i data-lucide="calendar" class="w-5 h-5 text-gray-400"></i>
                        <span class="text-sm font-bold {{ $medicine->isExpired() ? 'text-[#E24B4A]' : ($medicine->isExpiringSoon() ? 'text-[#EF9F27]' : 'text-[#042C53]') }}">
                            {{ $medicine->expiry_date ? $medicine->expiry_date->format('d M Y') : 'Tidak diatur' }}
                        </span>
                    </div>
                    @if($medicine->expiry_date)
                        <p class="text-xs text-gray-400 mt-1">
                            @if($medicine->isExpired())
                                Obat ini sudah kedaluwarsa! Jangan dikonsumsi.
                            @elseif($medicine->isExpiringSoon())
                                Segera kedaluwarsa dalam {{ now()->diffInDays($medicine->expiry_date) }} hari.
                            @else
                                Kedaluwarsa dalam {{ now()->diffInDays($medicine->expiry_date) }} hari.
                            @endif
                        </p>
                    @endif
                </div>

                <div class="pt-4 border-t border-gray-50">
                    <p class="text-xs text-gray-400 font-semibold uppercase">Estimasi Harga</p>
                    <p class="text-lg font-bold text-[#042C53] mt-1">Rp {{ number_format($medicine->price, 0, ',', '.') }} <span class="text-xs text-gray-400 font-normal">/ {{ $medicine->unit }}</span></p>
                </div>
            </div>
        </div>
    </div>
@endsection
