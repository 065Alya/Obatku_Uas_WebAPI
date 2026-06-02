@extends('layouts.app')
@section('title', 'Tambah Anggota Keluarga')

@section('content')
    <!-- Header -->
    <div class="mb-8 animate-fade-in-up">
        <a href="{{ route('family.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-[#185FA5] hover:text-[#042C53] mb-3 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Daftar Keluarga
        </a>
        <h1 class="text-2xl font-bold text-[#042C53]">Tambah Anggota Keluarga Baru</h1>
        <p class="text-gray-500 mt-1">Daftarkan anggota keluarga untuk mulai mencatat jadwal obat dan kondisi kesehatan mereka.</p>
    </div>

    <!-- Form -->
    <div class="max-w-2xl animate-fade-in-up" style="animation-delay: 0.1s">
        <form action="{{ route('family.store') }}" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
            @csrf

            <!-- Name & Relation -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap <span class="text-[#E24B4A]">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" class="obk-input @error('name') border-[#E24B4A] @enderror" placeholder="Contoh: Ibu Fatimah" required>
                    @error('name')
                        <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="relationship" class="block text-sm font-semibold text-gray-700 mb-1">Hubungan Keluarga <span class="text-[#E24B4A]">*</span></label>
                    <select name="relationship" id="relationship" class="obk-input @error('relationship') border-[#E24B4A] @enderror" required>
                        <option value="">-- Pilih Hubungan --</option>
                        <option value="suami" {{ old('relationship') == 'suami' ? 'selected' : '' }}>Suami</option>
                        <option value="istri" {{ old('relationship') == 'istri' ? 'selected' : '' }}>Istri</option>
                        <option value="anak" {{ old('relationship') == 'anak' ? 'selected' : '' }}>Anak</option>
                        <option value="ibu" {{ old('relationship') == 'ibu' ? 'selected' : '' }}>Ibu</option>
                        <option value="ayah" {{ old('relationship') == 'ayah' ? 'selected' : '' }}>Ayah</option>
                        <option value="kakek" {{ old('relationship') == 'kakek' ? 'selected' : '' }}>Kakek</option>
                        <option value="nenek" {{ old('relationship') == 'nenek' ? 'selected' : '' }}>Nenek</option>
                        <option value="saudara" {{ old('relationship') == 'saudara' ? 'selected' : '' }}>Saudara Kandung</option>
                    </select>
                    @error('relationship')
                        <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Date of Birth -->
            <div>
                <label for="date_of_birth" class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Lahir</label>
                <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth') }}" class="obk-input @error('date_of_birth') border-[#E24B4A] @enderror">
                <p class="text-xs text-gray-400 mt-1">Digunakan untuk menghitung estimasi usia dan menyesuaikan kecocokan dosis obat jika diperlukan.</p>
                @error('date_of_birth')
                    <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Health Notes -->
            <div>
                <label for="health_notes" class="block text-sm font-semibold text-gray-700 mb-1">Catatan Medis & Alergi</label>
                <textarea name="health_notes" id="health_notes" rows="4" class="obk-input @error('health_notes') border-[#E24B4A] @enderror" placeholder="Contoh: Alergi obat penisilin, memiliki riwayat darah tinggi... (Kosongkan jika tidak ada)">{{ old('health_notes') }}</textarea>
                @error('health_notes')
                    <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-50">
                <a href="{{ route('family.index') }}" class="obk-btn obk-btn-outline">Batal</a>
                <button type="submit" class="obk-btn obk-btn-primary flex items-center gap-2">
                    <i data-lucide="save" class="w-5 h-5"></i>
                    Simpan Anggota Keluarga
                </button>
            </div>
        </form>
    </div>
@endsection
