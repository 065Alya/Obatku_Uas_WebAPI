@extends('layouts.app')
@section('title', 'Profil Pengguna')
@section('header', 'Profil Saya')
@section('subheader', 'Kelola informasi pribadi, data kesehatan, dan keamanan akun Anda.')

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-4 gap-8 animate-fade-in-up" x-data="{ activeTab: 'account' }">
    
    {{-- Left Column: Avatar & Profile Card --}}
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 text-center space-y-4">
            <div class="relative w-28 h-28 mx-auto group">
                <img class="w-full h-full object-cover rounded-full border-4 border-white shadow-md" 
                     src="{{ $user->avatar ? asset('storage/' . $user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=185FA5&color=fff&size=128&rounded=true&bold=true' }}" 
                     alt="Profile Avatar">
            </div>
            
            <div>
                <h2 class="text-lg font-bold text-[#042C53]">{{ $user->name }}</h2>
                <p class="text-sm text-gray-500">{{ $user->email }}</p>
                <div class="mt-2 inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $user->is_active ? 'bg-emerald-50 text-[#1D9E75]' : 'bg-red-50 text-[#E24B4A]' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $user->is_active ? 'bg-[#1D9E75]' : 'bg-[#E24B4A]' }}"></span>
                    {{ $user->is_active ? 'Akun Aktif' : 'Akun Nonaktif' }}
                </div>
            </div>

            {{-- Tab List Navigation --}}
            <div class="pt-4 border-t border-gray-50 text-left space-y-1">
                <button @click="activeTab = 'account'" 
                        class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all"
                        :class="activeTab === 'account' ? 'bg-blue-50 text-[#185FA5]' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'">
                    <i data-lucide="user" class="w-4.5 h-4.5"></i>
                    Informasi Akun
                </button>
                <button @click="activeTab = 'health'" 
                        class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all"
                        :class="activeTab === 'health' ? 'bg-emerald-50 text-[#1D9E75]' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'">
                    <i data-lucide="heart-pulse" class="w-4.5 h-4.5"></i>
                    Data Kesehatan
                </button>
                <button @click="activeTab = 'security'" 
                        class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all"
                        :class="activeTab === 'security' ? 'bg-purple-50 text-[#7F77DD]' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'">
                    <i data-lucide="shield-check" class="w-4.5 h-4.5"></i>
                    Keamanan Sandi
                </button>
            </div>
        </div>
    </div>

    {{-- Right Column: Forms Panels --}}
    <div class="lg:col-span-3">
        
        {{-- TAB 1: Account Information --}}
        <div x-show="activeTab === 'account'" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
            <h3 class="text-lg font-bold text-[#042C53] border-b border-gray-50 pb-3 flex items-center gap-2">
                <i data-lucide="user" class="w-5 h-5 text-[#185FA5]"></i>
                Informasi Umum & Kontak
            </h3>

            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap <span class="text-[#E24B4A]">*</span></label>
                        <input type="text" name="name" id="name" required value="{{ old('name', $user->name) }}" class="obk-input @error('name') border-[#E24B4A] @enderror" placeholder="Contoh: Budi Santoso">
                        @error('name')
                            <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Alamat Email <span class="text-[#E24B4A]">*</span></label>
                        <input type="email" name="email" id="email" required value="{{ old('email', $user->email) }}" class="obk-input @error('email') border-[#E24B4A] @enderror" placeholder="budi@example.com">
                        @error('email')
                            <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="phone" class="block text-sm font-semibold text-gray-700 mb-1">Nomor Telepon</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" class="obk-input @error('phone') border-[#E24B4A] @enderror" placeholder="Contoh: 081234567890">
                        @error('phone')
                            <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="date_of_birth" class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Lahir</label>
                        <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth', $user->date_of_birth ? $user->date_of_birth->toDateString() : '') }}" class="obk-input @error('date_of_birth') border-[#E24B4A] @enderror">
                        @error('date_of_birth')
                            <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="avatar" class="block text-sm font-semibold text-gray-700 mb-1">Ganti Foto Profil (Avatar)</label>
                    <input type="file" name="avatar" id="avatar" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-[#185FA5] hover:file:bg-blue-100 transition-colors">
                    <p class="text-xs text-gray-400 mt-1.5">Mendukung JPG, JPEG, PNG, atau WebP (maksimal 2MB).</p>
                    @error('avatar')
                        <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-4 border-t border-gray-50 flex justify-end">
                    <button type="submit" class="obk-btn obk-btn-primary px-6">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Simpan Profil
                    </button>
                </div>
            </form>
        </div>

        {{-- TAB 2: Health Details --}}
        <div x-show="activeTab === 'health'" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6" style="display: none;">
            <h3 class="text-lg font-bold text-[#042C53] border-b border-gray-50 pb-3 flex items-center gap-2">
                <i data-lucide="heart-pulse" class="w-5 h-5 text-[#1D9E75]"></i>
                Data Kesehatan Pribadi
            </h3>

            <form action="{{ route('profile.health') }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="blood_type" class="block text-sm font-semibold text-gray-700 mb-1">Golongan Darah</label>
                        <select name="blood_type" id="blood_type" class="obk-input text-sm @error('blood_type') border-[#E24B4A] @enderror">
                            <option value="">-- Pilih --</option>
                            @foreach(['A','B','AB','O','A+','A-','B+','B-','AB+','AB-','O+','O-'] as $type)
                                <option value="{{ $type }}" {{ old('blood_type', $profile->blood_type) == $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                        @error('blood_type')
                            <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="height_cm" class="block text-sm font-semibold text-gray-700 mb-1">Tinggi Badan (cm)</label>
                        <input type="number" name="height_cm" id="height_cm" min="50" max="300" value="{{ old('height_cm', $profile->height_cm) }}" class="obk-input @error('height_cm') border-[#E24B4A] @enderror" placeholder="Contoh: 170">
                        @error('height_cm')
                            <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="weight_kg" class="block text-sm font-semibold text-gray-700 mb-1">Berat Badan (kg)</label>
                        <input type="number" name="weight_kg" id="weight_kg" min="1" max="500" value="{{ old('weight_kg', $profile->weight_kg) }}" class="obk-input @error('weight_kg') border-[#E24B4A] @enderror" placeholder="Contoh: 65">
                        @error('weight_kg')
                            <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="allergies" class="block text-sm font-semibold text-gray-700 mb-1">Alergi Obat / Makanan</label>
                        <textarea name="allergies" id="allergies" rows="2" class="obk-input text-sm @error('allergies') border-[#E24B4A] @enderror" placeholder="Tuliskan jika memiliki alergi seperti Paracetamol, Seafood, dll.">{{ old('allergies', $profile->allergies) }}</textarea>
                        @error('allergies')
                            <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="chronic_diseases" class="block text-sm font-semibold text-gray-700 mb-1">Riwayat Penyakit Kronis / Bawaan</label>
                        <textarea name="chronic_diseases" id="chronic_diseases" rows="2" class="obk-input text-sm @error('chronic_diseases') border-[#E24B4A] @enderror" placeholder="Contoh: Hipertensi, Diabetes Melitus, Asma, dll.">{{ old('chronic_diseases', $profile->chronic_diseases) }}</textarea>
                        @error('chronic_diseases')
                            <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="bg-gray-50/50 rounded-xl border border-gray-100 p-5 space-y-4">
                    <h4 class="font-bold text-sm text-[#042C53] flex items-center gap-1.5">
                        <i data-lucide="phone-call" class="w-4 h-4 text-[#1D9E75]"></i>
                        Kontak Darurat
                    </h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="emergency_contact_name" class="block text-sm font-semibold text-gray-700 mb-1">Nama Kontak Darurat</label>
                            <input type="text" name="emergency_contact_name" id="emergency_contact_name" value="{{ old('emergency_contact_name', $profile->emergency_contact_name) }}" class="obk-input text-sm @error('emergency_contact_name') border-[#E24B4A] @enderror" placeholder="Nama wali/kerabat dekat">
                            @error('emergency_contact_name')
                                <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="emergency_contact_phone" class="block text-sm font-semibold text-gray-700 mb-1">Nomor Telepon Darurat</label>
                            <input type="text" name="emergency_contact_phone" id="emergency_contact_phone" value="{{ old('emergency_contact_phone', $profile->emergency_contact_phone) }}" class="obk-input text-sm @error('emergency_contact_phone') border-[#E24B4A] @enderror" placeholder="Contoh: 0812XXXXXXXX">
                            @error('emergency_contact_phone')
                                <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-50 flex justify-end">
                    <button type="submit" class="obk-btn obk-btn-success px-6">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Simpan Data Kesehatan
                    </button>
                </div>
            </form>
        </div>

        {{-- TAB 3: Account Security --}}
        <div x-show="activeTab === 'security'" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6" style="display: none;">
            <h3 class="text-lg font-bold text-[#042C53] border-b border-gray-50 pb-3 flex items-center gap-2">
                <i data-lucide="shield-check" class="w-5 h-5 text-[#7F77DD]"></i>
                Keamanan & Kata Sandi
            </h3>

            <form action="{{ route('profile.password') }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="block text-sm font-semibold text-gray-700 mb-1">Kata Sandi Sekarang <span class="text-[#E24B4A]">*</span></label>
                    <input type="password" name="current_password" id="current_password" required class="obk-input @error('current_password') border-[#E24B4A] @enderror" placeholder="••••••••">
                    @error('current_password')
                        <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Kata Sandi Baru <span class="text-[#E24B4A]">*</span></label>
                        <input type="password" name="password" id="password" required class="obk-input @error('password') border-[#E24B4A] @enderror" placeholder="Minimal 8 karakter">
                        @error('password')
                            <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1">Konfirmasi Kata Sandi Baru <span class="text-[#E24B4A]">*</span></label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required class="obk-input" placeholder="Tulis ulang kata sandi baru">
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-50 flex justify-end">
                    <button type="submit" class="obk-btn obk-btn-primary bg-[#7F77DD] hover:bg-[#6860c4] hover:shadow-purple-200 px-6">
                        <i data-lucide="key" class="w-4 h-4"></i>
                        Perbarui Sandi
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

@endsection
