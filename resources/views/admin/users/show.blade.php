@extends('layouts.app')

@section('title', 'Detail Pengguna — Admin')

@section('content')
    <!-- Header -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 animate-fade-in-up">
        <div>
            <div class="flex items-center gap-2 text-sm text-[#185FA5] font-semibold mb-1">
                <a href="{{ route('admin.users.index') }}" class="hover:underline flex items-center gap-1">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i> Pengguna
                </a>
                <span>/</span>
                <span>Detail Profil</span>
            </div>
            <h1 class="text-2xl font-bold text-[#042C53]">Detail Pengguna: {{ $user->name }}</h1>
        </div>
        <div class="flex gap-2">
            <form action="{{ route('admin.users.toggle-status', $user->id) }}" method="POST" class="inline">
                @csrf
                @method('PATCH')
                <button type="submit" 
                        class="obk-btn text-sm {{ $user->is_active ? 'bg-amber-600 text-white hover:bg-amber-700' : 'bg-emerald-600 text-white hover:bg-emerald-700' }}">
                    <i data-lucide="{{ $user->is_active ? 'user-x' : 'user-check' }}" class="w-4 h-4"></i>
                    {{ $user->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}
                </button>
            </form>
        </div>
    </div>

    <!-- Alert Flash Message -->
    @if(session('success'))
        <div class="mb-6 obk-alert obk-alert-success animate-fade-in-up" data-flash>
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 animate-fade-in-up" style="animation-delay: 0.05s">
        <!-- Left: User Overview & Health Profile -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Basic Info Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 text-center">
                <div class="w-24 h-24 rounded-full bg-blue-50 text-[#185FA5] flex items-center justify-center font-bold text-4xl mx-auto mb-4 border-2 border-white shadow-md">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <h3 class="text-xl font-bold text-[#042C53]">{{ $user->name }}</h3>
                <p class="text-sm text-gray-500 mt-0.5">{{ $user->email }}</p>
                <div class="mt-4 flex justify-center gap-2">
                    @if($user->role === 'admin')
                        <span class="px-2.5 py-1 text-xs font-bold text-[#7F77DD] bg-purple-50 rounded-md border border-purple-100">Admin</span>
                    @else
                        <span class="px-2.5 py-1 text-xs font-medium text-gray-600 bg-gray-100 rounded-md">User</span>
                    @endif
                    
                    @if($user->is_active)
                        <span class="px-2.5 py-1 text-xs font-bold text-[#1D9E75] bg-[#1D9E75]/10 rounded-md">Aktif</span>
                    @else
                        <span class="px-2.5 py-1 text-xs font-bold text-[#E24B4A] bg-[#E24B4A]/10 rounded-md">Nonaktif</span>
                    @endif
                </div>

                <div class="border-t border-gray-100 mt-6 pt-4 text-left space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Telepon</span>
                        <span class="font-semibold text-gray-700">{{ $user->phone ?? '–' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Tgl Lahir</span>
                        <span class="font-semibold text-gray-700">{{ $user->date_of_birth ? $user->date_of_birth->format('d M Y') : '–' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Terdaftar</span>
                        <span class="font-semibold text-gray-700">{{ $user->created_at->format('d M Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Health Profile Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
                <h3 class="text-base font-bold text-[#042C53] border-b border-gray-50 pb-2 flex items-center gap-2">
                    <i data-lucide="heart-pulse" class="w-5 h-5 text-[#E24B4A]"></i>
                    Profil Medis Pengguna
                </h3>
                
                @if($user->profile)
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <span class="block text-xs text-gray-400">Golongan Darah</span>
                            <span class="font-bold text-[#042C53] text-base">{{ $user->profile->blood_type ?? '–' }}</span>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <span class="block text-xs text-gray-400">BMI (Kategori)</span>
                            <span class="font-bold text-[#042C53] text-sm truncate block" title="{{ $user->profile->bmi_category }}">
                                {{ $user->profile->bmi ?? '–' }} ({{ $user->profile->bmi_category }})
                            </span>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <span class="block text-xs text-gray-400">Tinggi Badan</span>
                            <span class="font-bold text-[#042C53] text-sm">{{ $user->profile->height_cm ? $user->profile->height_cm . ' cm' : '–' }}</span>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <span class="block text-xs text-gray-400">Berat Badan</span>
                            <span class="font-bold text-[#042C53] text-sm">{{ $user->profile->weight_kg ? $user->profile->weight_kg . ' kg' : '–' }}</span>
                        </div>
                    </div>
                    <div class="text-sm space-y-3 pt-2">
                        <div>
                            <span class="block text-xs text-gray-400 font-medium">Alergi</span>
                            <p class="font-semibold text-gray-800">{{ $user->profile->allergies ?? 'Tidak ada riwayat alergi' }}</p>
                        </div>
                        <div>
                            <span class="block text-xs text-gray-400 font-medium">Penyakit Kronis</span>
                            <p class="font-semibold text-gray-800">{{ $user->profile->chronic_diseases ?? 'Tidak ada riwayat penyakit kronis' }}</p>
                        </div>
                        <div class="border-t border-gray-50 pt-3">
                            <span class="block text-xs text-gray-400 font-bold uppercase tracking-wider mb-2">Kontak Darurat</span>
                            <div class="bg-red-50/50 border border-red-100/50 p-3 rounded-xl">
                                <p class="text-sm font-bold text-gray-900">{{ $user->profile->emergency_contact_name ?? '–' }}</p>
                                <p class="text-xs text-gray-600 mt-1 flex items-center gap-1">
                                    <i data-lucide="phone" class="w-3.5 h-3.5 text-gray-400"></i>
                                    {{ $user->profile->emergency_contact_phone ?? '–' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-gray-400 text-center py-4">Profil medis belum dilengkapi oleh pengguna.</p>
                @endif
            </div>
        </div>

        <!-- Right: Family & Medicines Overview -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Family Members Box -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-[#042C53] mb-4 flex items-center gap-2">
                    <i data-lucide="users" class="w-5 h-5 text-[#185FA5]"></i>
                    Anggota Keluarga Terdaftar
                </h3>

                @if($user->familyMembers->isEmpty())
                    <p class="text-sm text-gray-400 py-4 text-center">Pengguna belum menambahkan anggota keluarga.</p>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($user->familyMembers as $member)
                            <div class="p-4 rounded-xl border border-gray-100 hover:border-blue-100 hover:bg-blue-50/20 transition-all flex gap-3">
                                <div class="w-10 h-10 rounded-full bg-blue-50 text-[#185FA5] flex items-center justify-center shrink-0 font-bold text-sm">
                                    {{ strtoupper(substr($member->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900 text-sm">{{ $member->name }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">Hubungan: {{ $member->relationship }}</p>
                                    @if($member->birth_date)
                                        <p class="text-[11px] text-gray-400 mt-1">Umur: {{ $member->birth_date->age }} tahun</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Medicines Box -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-[#042C53] mb-4 flex items-center gap-2">
                    <i data-lucide="pill" class="w-5 h-5 text-[#1D9E75]"></i>
                    Kotak Obat Pengguna
                </h3>

                @if($user->medicines->isEmpty())
                    <p class="text-sm text-gray-400 py-4 text-center">Belum ada obat di kotak obat pengguna ini.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-600 whitespace-nowrap">
                            <thead class="text-xs text-gray-500 uppercase bg-gray-50/50 border-b border-gray-100">
                                <tr>
                                    <th class="px-4 py-3 font-semibold">Nama Obat</th>
                                    <th class="px-4 py-3 font-semibold">Bentuk</th>
                                    <th class="px-4 py-3 font-semibold">Stok</th>
                                    <th class="px-4 py-3 font-semibold">Tgl Kedaluwarsa</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($user->medicines as $med)
                                    <tr>
                                        <td class="px-4 py-3 font-bold text-gray-900 text-sm">
                                            {{ $med->name }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-500 text-sm">
                                            {{ $med->unit ?? 'Tablet' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="inline-block px-2.5 py-0.5 rounded text-xs font-semibold {{ $med->stock <= 5 ? 'text-amber-800 bg-amber-100' : 'text-emerald-800 bg-emerald-100' }}">
                                                {{ $med->stock }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            {{ $med->expiry_date ? $med->expiry_date->format('d M Y') : '–' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
