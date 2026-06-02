@extends('layouts.app')
@section('title', 'Edit Profil Personal')

@section('content')

{{-- Header --}}
<div class="mb-8 animate-fade-in-up">
    <a href="{{ route('personal.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-[#185FA5] hover:text-[#042C53] mb-3 transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Dashboard Personal
    </a>
    <h1 class="text-3xl font-bold text-[#042C53]">Edit Profil Personal</h1>
    <p class="text-gray-500 mt-1">Perbarui data diri, kondisi medis, dan kontak darurat Anda.</p>
</div>

<form action="{{ route('personal.update') }}" method="POST" class="animate-fade-in-up" style="animation-delay: 0.05s">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- ── Left: Avatar Preview ────────────────────────────────────── --}}
        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 text-center">
                <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=185FA5&color=fff&size=128&rounded=true&bold=true' }}"
                     alt="Avatar"
                     class="w-24 h-24 rounded-full border-4 border-white shadow-md mx-auto mb-3 object-cover">
                <h3 class="font-bold text-[#042C53]">{{ $user->name }}</h3>
                <p class="text-xs text-gray-400">{{ $user->email }}</p>
                <p class="text-xs text-gray-400 mt-1">Ganti foto di <a href="{{ route('profile.index') }}" class="text-[#185FA5] hover:underline">Pengaturan Akun</a></p>
            </div>

            <div class="bg-[#E1F5EE] rounded-xl p-5 border border-[#1D9E75]/20">
                <div class="flex items-start gap-3">
                    <i data-lucide="info" class="w-5 h-5 text-[#1D9E75] shrink-0 mt-0.5"></i>
                    <div class="text-sm text-[#085538]">
                        <p class="font-semibold mb-1">Mode Personal</p>
                        <p class="text-xs">Data ini digunakan untuk memantau konsumsi obat dan kondisi kesehatan Anda secara pribadi, terpisah dari anggota keluarga.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Right: Forms ────────────────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Basic Info --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-5">
                <h3 class="text-lg font-bold text-[#042C53] border-b border-gray-50 pb-3 flex items-center gap-2">
                    <i data-lucide="user" class="w-5 h-5 text-[#185FA5]"></i>
                    Informasi Dasar
                </h3>

                @if($errors->any())
                    <div class="p-4 bg-red-50 border border-red-100 rounded-xl text-sm text-red-700">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap <span class="text-[#E24B4A]">*</span></label>
                        <input type="text" name="name" id="name" required
                               value="{{ old('name', $user->name) }}"
                               class="obk-input @error('name') border-[#E24B4A] @enderror"
                               placeholder="Nama lengkap Anda">
                        @error('name')<p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-semibold text-gray-700 mb-1">Nomor Telepon</label>
                        <input type="text" name="phone" id="phone"
                               value="{{ old('phone', $user->phone) }}"
                               class="obk-input @error('phone') border-[#E24B4A] @enderror"
                               placeholder="08xxxxxxxxxx">
                        @error('phone')<p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label for="date_of_birth" class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Lahir</label>
                    <input type="date" name="date_of_birth" id="date_of_birth"
                           value="{{ old('date_of_birth', $user->date_of_birth?->toDateString()) }}"
                           class="obk-input @error('date_of_birth') border-[#E24B4A] @enderror">
                    @error('date_of_birth')<p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Health Data --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-5">
                <h3 class="text-lg font-bold text-[#042C53] border-b border-gray-50 pb-3 flex items-center gap-2">
                    <i data-lucide="heart-pulse" class="w-5 h-5 text-[#1D9E75]"></i>
                    Data Kesehatan
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label for="blood_type" class="block text-sm font-semibold text-gray-700 mb-1">Golongan Darah</label>
                        <select name="blood_type" id="blood_type" class="obk-input text-sm">
                            <option value="">-- Pilih --</option>
                            @foreach(['A','B','AB','O','A+','A-','B+','B-','AB+','AB-','O+','O-'] as $type)
                                <option value="{{ $type }}" {{ old('blood_type', $profile->blood_type) == $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="height_cm" class="block text-sm font-semibold text-gray-700 mb-1">Tinggi Badan (cm)</label>
                        <input type="number" name="height_cm" id="height_cm" min="50" max="300"
                               value="{{ old('height_cm', $profile->height_cm) }}"
                               class="obk-input @error('height_cm') border-[#E24B4A] @enderror" placeholder="170">
                        @error('height_cm')<p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="weight_kg" class="block text-sm font-semibold text-gray-700 mb-1">Berat Badan (kg)</label>
                        <input type="number" name="weight_kg" id="weight_kg" min="1" max="500"
                               value="{{ old('weight_kg', $profile->weight_kg) }}"
                               class="obk-input @error('weight_kg') border-[#E24B4A] @enderror" placeholder="65">
                        @error('weight_kg')<p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="allergies" class="block text-sm font-semibold text-gray-700 mb-1">Alergi Obat / Makanan</label>
                        <textarea name="allergies" id="allergies" rows="3"
                                  class="obk-input text-sm @error('allergies') border-[#E24B4A] @enderror"
                                  placeholder="Contoh: Paracetamol, Seafood">{{ old('allergies', $profile->allergies) }}</textarea>
                        @error('allergies')<p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="chronic_diseases" class="block text-sm font-semibold text-gray-700 mb-1">Penyakit Kronis / Riwayat Medis</label>
                        <textarea name="chronic_diseases" id="chronic_diseases" rows="3"
                                  class="obk-input text-sm @error('chronic_diseases') border-[#E24B4A] @enderror"
                                  placeholder="Contoh: Hipertensi, Diabetes">{{ old('chronic_diseases', $profile->chronic_diseases) }}</textarea>
                        @error('chronic_diseases')<p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Emergency Contact --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-5">
                <h3 class="text-lg font-bold text-[#042C53] border-b border-gray-50 pb-3 flex items-center gap-2">
                    <i data-lucide="phone-call" class="w-5 h-5 text-[#1D9E75]"></i>
                    Kontak Darurat
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="emergency_contact_name" class="block text-sm font-semibold text-gray-700 mb-1">Nama Kontak Darurat</label>
                        <input type="text" name="emergency_contact_name" id="emergency_contact_name"
                               value="{{ old('emergency_contact_name', $profile->emergency_contact_name) }}"
                               class="obk-input @error('emergency_contact_name') border-[#E24B4A] @enderror"
                               placeholder="Nama wali / kerabat dekat">
                        @error('emergency_contact_name')<p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="emergency_contact_phone" class="block text-sm font-semibold text-gray-700 mb-1">Nomor Telepon Darurat</label>
                        <input type="text" name="emergency_contact_phone" id="emergency_contact_phone"
                               value="{{ old('emergency_contact_phone', $profile->emergency_contact_phone) }}"
                               class="obk-input @error('emergency_contact_phone') border-[#E24B4A] @enderror"
                               placeholder="08xxxxxxxxxx">
                        @error('emergency_contact_phone')<p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('personal.index') }}" class="obk-btn obk-btn-outline">Batal</a>
                <button type="submit" class="obk-btn obk-btn-primary px-8">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Simpan Perubahan
                </button>
            </div>

        </div>
    </div>
</form>

@endsection
