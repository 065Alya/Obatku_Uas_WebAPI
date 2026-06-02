@extends('layouts.app')
@section('title', 'Anggota Keluarga')

@section('content')
    <!-- Header -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 animate-fade-in-up">
        <div>
            <h1 class="text-2xl font-bold text-[#042C53]">Anggota Keluarga</h1>
            <p class="text-gray-500 mt-1">Kelola data kesehatan dan rekam medis anggota keluarga Anda.</p>
        </div>
        <div>
            <a href="{{ route('family.create') }}" class="obk-btn obk-btn-primary flex items-center gap-2">
                <i data-lucide="user-plus" class="w-5 h-5"></i>
                Tambah Anggota Keluarga
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

    <!-- Members Grid -->
    @if($members->isEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 animate-fade-in-up" style="animation-delay: 0.1s">
            @include('components.empty-state', [
                'icon' => 'users',
                'title' => 'Belum Ada Anggota Keluarga',
                'description' => 'Mulai daftarkan anggota keluarga Anda untuk memantau konsumsi obat mereka secara bersama.',
                'action' => route('family.create'),
                'actionLabel' => 'Tambah Anggota Keluarga Pertama'
            ])
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 animate-fade-in-up" style="animation-delay: 0.1s">
            @foreach($members as $member)
                <div class="obk-card flex flex-col justify-between group">
                    <div>
                        <!-- Member Meta -->
                        <div class="flex items-start justify-between gap-2 mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-blue-50 text-[#185FA5] rounded-xl flex items-center justify-center shrink-0 font-bold text-lg group-hover:bg-[#185FA5] group-hover:text-white transition-all">
                                    {{ substr($member->name, 0, 1) }}
                                </div>
                                <div>
                                    <h3 class="font-bold text-[#042C53] text-lg">{{ $member->name }}</h3>
                                    <span class="obk-badge obk-badge-primary text-xs mt-0.5">{{ ucfirst($member->relationship) }}</span>
                                </div>
                            </div>
                            
                            <span class="text-xs font-semibold text-gray-400">
                                {{ $member->birth_date ? $member->age . ' Tahun' : 'Umur -' }}
                            </span>
                        </div>

                        <!-- Health Notes -->
                        <div class="mb-4">
                            <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1">Catatan Medis</p>
                            <p class="text-sm text-gray-600 line-clamp-3 bg-gray-50 p-3 rounded-lg border border-gray-100 min-h-[60px]">
                                {{ $member->health_notes ?: 'Tidak ada catatan medis atau alergi khusus.' }}
                            </p>
                        </div>

                        <!-- Stats Counts -->
                        <div class="grid grid-cols-2 gap-4 py-3 border-t border-b border-gray-50 mb-4">
                            <div class="text-center">
                                <p class="text-xs text-gray-400 font-semibold">Obat Terdaftar</p>
                                <p class="text-base font-bold text-[#042C53] mt-0.5">{{ $member->medicines->count() }}</p>
                            </div>
                            <div class="text-center border-l border-gray-100">
                                <p class="text-xs text-gray-400 font-semibold">Jadwal Aktif</p>
                                <p class="text-base font-bold text-[#042C53] mt-0.5">{{ $member->schedules->count() }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col gap-2 pt-2">
                        <a href="{{ route('family.show', $member->id) }}" class="obk-btn obk-btn-primary text-xs py-2 px-3 w-full flex items-center justify-center gap-1.5">
                            <i data-lucide="eye" class="w-3.5 h-3.5"></i> Lihat Detail
                        </a>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('family.edit', $member->id) }}" class="obk-btn obk-btn-outline text-xs py-2 px-3 flex-1 flex items-center justify-center gap-1.5">
                                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i> Edit
                            </a>
                            <form action="{{ route('family.destroy', $member->id) }}" method="POST" class="flex-1" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data anggota keluarga ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="obk-btn obk-btn-danger text-xs py-2 px-3 w-full flex items-center justify-center gap-1.5">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
