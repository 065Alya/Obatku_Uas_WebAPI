@extends('layouts.app')
@section('title', 'Jadwal Obat')

@section('content')
    <!-- Header -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 animate-fade-in-up">
        <div>
            <h1 class="text-2xl font-bold text-[#042C53]">Jadwal Konsumsi Obat</h1>
            <p class="text-gray-500 mt-1">Pantau waktu minum obat harian Anda dan keluarga tercinta.</p>
        </div>
        <div>
            <a href="{{ route('schedules.create') }}" class="obk-btn obk-btn-primary flex items-center gap-2">
                <i data-lucide="plus" class="w-5 h-5"></i>
                Tambah Jadwal Baru
            </a>
        </div>
    </div>

    <!-- Alert Success -->
    @if(session('success'))
        <div class="mb-6 obk-alert obk-alert-success animate-fade-in-up" data-flash>
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    <!-- Schedules Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 animate-fade-in-up" style="animation-delay: 0.1s">
        
        <!-- Left: Today's Timeline list -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between border-b border-gray-50 pb-3 mb-6">
                    <h3 class="text-lg font-bold text-[#042C53] flex items-center gap-2">
                        <i data-lucide="calendar-check" class="w-5 h-5 text-[#185FA5]"></i>
                        Jadwal Hari Ini
                    </h3>
                    <span class="bg-[#e8f0fa] text-[#185FA5] text-xs font-bold px-3 py-1 rounded-full">
                        {{ now()->translatedFormat('l, d F Y') }}
                    </span>
                </div>

                @if($schedules->isEmpty())
                    @include('components.empty-state', [
                        'icon' => 'clock',
                        'title' => 'Belum Ada Jadwal Dibuat',
                        'description' => 'Mulai buat pengingat jadwal minum obat agar dosis tidak terlewat.',
                        'action' => route('schedules.create'),
                        'actionLabel' => 'Tambah Jadwal Pertama'
                    ])
                @else
                    <div class="space-y-4">
                        @foreach($schedules as $schedule)
                            @include('components.schedule-card', ['schedule' => $schedule])
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6">
                        {{ $schedules->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Right: Compliance & Adherence Tips -->
        <div class="space-y-6">
            <!-- Adherence Widget -->
            <div class="bg-[#185FA5] rounded-xl shadow-sm p-6 text-white relative overflow-hidden">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-10 rounded-full blur-xl"></div>
                
                <h3 class="text-lg font-bold mb-1">Kepatuhan Minum Obat</h3>
                <p class="text-white/80 text-xs mb-4">Akurasi minum obat tepat waktu minggu ini.</p>
                
                <div class="flex items-end gap-3">
                    <div class="text-4xl font-extrabold">92%</div>
                    <div class="text-xs text-white/80 pb-1">Sangat Baik</div>
                </div>

                <div class="w-full bg-black/20 rounded-full h-1.5 mt-3">
                    <div class="bg-[#1D9E75] h-1.5 rounded-full" style="width: 92%"></div>
                </div>
            </div>

            <!-- Reminder Tips -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
                <h3 class="text-base font-bold text-[#042C53] border-b border-gray-50 pb-2 flex items-center gap-2">
                    <i data-lucide="heart" class="w-5 h-5 text-[#E24B4A]"></i>
                    Tips Kepatuhan Obat
                </h3>
                <ul class="space-y-3 text-sm text-gray-600">
                    <li class="flex items-start gap-2">
                        <i data-lucide="check" class="w-4 h-4 text-[#1D9E75] shrink-0 mt-0.5"></i>
                        <span>Minum obat pada jam yang sama setiap hari agar tubuh mempertahankan kadar obat yang stabil.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i data-lucide="check" class="w-4 h-4 text-[#1D9E75] shrink-0 mt-0.5"></i>
                        <span>Selalu baca label botol obat atau ikuti deskripsi anjuran makan yang diatur di detail obat ObatKu.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i data-lucide="check" class="w-4 h-4 text-[#1D9E75] shrink-0 mt-0.5"></i>
                        <span>Jika ragu tentang interaksi antar obat, baca peringatan di detail obat atau hubungi dokter Anda.</span>
                    </li>
                </ul>
            </div>
        </div>

    </div>
@endsection
