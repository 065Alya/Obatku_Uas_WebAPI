@extends('layouts.app')
@section('title', 'Buat Jadwal Baru')

@section('content')
    <!-- Header -->
    <div class="mb-8 animate-fade-in-up">
        <a href="{{ route('schedules.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-[#185FA5] hover:text-[#042C53] mb-3 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Jadwal
        </a>
        <h1 class="text-2xl font-bold text-[#042C53]">Buat Jadwal Obat Baru</h1>
        <p class="text-gray-500 mt-1">Atur jam minum obat beserta instruksi dosis untuk pengingat otomatis.</p>
    </div>

    <!-- Form -->
    <div class="max-w-3xl animate-fade-in-up" style="animation-delay: 0.1s">
        <form action="{{ route('schedules.store') }}" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
            @csrf

            <!-- Medicine Selection -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="medicine_id" class="block text-sm font-semibold text-gray-700 mb-1">Pilih Obat <span class="text-[#E24B4A]">*</span></label>
                    <select name="medicine_id" id="medicine_id" class="obk-input @error('medicine_id') border-[#E24B4A] @enderror" required>
                        <option value="">-- Pilih Obat --</option>
                        @foreach($medicines as $med)
                            <option value="{{ $med->id }}" {{ old('medicine_id', request('medicine_id')) == $med->id ? 'selected' : '' }}>
                                {{ $med->name }} ({{ $med->dosage ?: '-' }} - Stok: {{ $med->stock }})
                            </option>
                        @endforeach
                    </select>
                    @error('medicine_id')
                        <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="family_member_id" class="block text-sm font-semibold text-gray-700 mb-1">Jadwal Untuk (Penerima)</label>
                    <select name="family_member_id" id="family_member_id" class="obk-input @error('family_member_id') border-[#E24B4A] @enderror">
                        <option value="">Diri Sendiri (Utama)</option>
                        @foreach($familyMembers as $member)
                            <option value="{{ $member->id }}" {{ old('family_member_id') == $member->id ? 'selected' : '' }}>
                                {{ $member->name }} ({{ $member->relationship }})
                            </option>
                        @endforeach
                    </select>
                    @error('family_member_id')
                        <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Time & Frequency -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="schedule_time" class="block text-sm font-semibold text-gray-700 mb-1">Waktu Konsumsi <span class="text-[#E24B4A]">*</span></label>
                    <input type="time" name="schedule_time" id="schedule_time" value="{{ old('schedule_time', '08:00') }}" class="obk-input @error('schedule_time') border-[#E24B4A] @enderror" required>
                    @error('schedule_time')
                        <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="frequency" class="block text-sm font-semibold text-gray-700 mb-1">Frekuensi Pengulangan <span class="text-[#E24B4A]">*</span></label>
                    <select name="frequency" id="frequency" class="obk-input @error('frequency') border-[#E24B4A] @enderror" required>
                        <option value="daily" {{ old('frequency') == 'daily' ? 'selected' : '' }}>Setiap Hari (1x Sehari)</option>
                        <option value="twice_daily" {{ old('frequency') == 'twice_daily' ? 'selected' : '' }}>2 Kali Sehari</option>
                        <option value="three_daily" {{ old('frequency') == 'three_daily' ? 'selected' : '' }}>3 Kali Sehari</option>
                        <option value="weekly" {{ old('frequency') == 'weekly' ? 'selected' : '' }}>Mingguan</option>
                        <option value="monthly" {{ old('frequency') == 'monthly' ? 'selected' : '' }}>Bulanan</option>
                        <option value="as_needed" {{ old('frequency') == 'as_needed' ? 'selected' : '' }}>Bila Perlu / Sesuai Gejala</option>
                    </select>
                    @error('frequency')
                        <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Dosage Amount & Instructions -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="dosage_amount" class="block text-sm font-semibold text-gray-700 mb-1">Dosis per Konsumsi</label>
                    <input type="text" name="dosage_amount" id="dosage_amount" value="{{ old('dosage_amount', '1 Tablet') }}" class="obk-input @error('dosage_amount') border-[#E24B4A] @enderror" placeholder="Contoh: 1 Tablet, 5ml, 2 Kapsul">
                    @error('dosage_amount')
                        <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="notes" class="block text-sm font-semibold text-gray-700 mb-1">Petunjuk / Catatan Khusus</label>
                    <input type="text" name="notes" id="notes" value="{{ old('notes') }}" class="obk-input @error('notes') border-[#E24B4A] @enderror" placeholder="Contoh: Sesudah makan, Sebelum tidur">
                    @error('notes')
                        <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Start & End Date -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="start_date" class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Mulai <span class="text-[#E24B4A]">*</span></label>
                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date', date('Y-m-d')) }}" class="obk-input @error('start_date') border-[#E24B4A] @enderror" required>
                    @error('start_date')
                        <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Berakhir (Opsional)</label>
                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}" class="obk-input @error('end_date') border-[#E24B4A] @enderror">
                    <p class="text-xs text-gray-400 mt-1">Kosongkan jika jadwal berlaku tanpa batas waktu tertentu.</p>
                    @error('end_date')
                        <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-50">
                <a href="{{ route('schedules.index') }}" class="obk-btn obk-btn-outline">Batal</a>
                <button type="submit" class="obk-btn obk-btn-primary flex items-center gap-2">
                    <i data-lucide="save" class="w-5 h-5"></i>
                    Simpan Jadwal
                </button>
            </div>
        </form>
    </div>
@endsection
