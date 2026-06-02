@extends('layouts.app')
@section('title', 'Ubah Jadwal')

@section('content')
    <!-- Header -->
    <div class="mb-8 animate-fade-in-up">
        <a href="{{ route('schedules.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-[#185FA5] hover:text-[#042C53] mb-3 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Jadwal
        </a>
        <h1 class="text-2xl font-bold text-[#042C53]">Ubah Jadwal Obat</h1>
        <p class="text-gray-500 mt-1">Sesuaikan kembali waktu, dosis, atau tanggal pengulangan jadwal obat.</p>
    </div>

    <!-- Form -->
    <div class="max-w-3xl animate-fade-in-up" style="animation-delay: 0.1s">
        <form action="{{ route('schedules.update', $schedule->id) }}" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
            @csrf
            @method('PUT')

            <!-- Medicine Selection -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="medicine_id" class="block text-sm font-semibold text-gray-700 mb-1">Obat <span class="text-[#E24B4A]">*</span></label>
                    <select name="medicine_id" id="medicine_id" class="obk-input @error('medicine_id') border-[#E24B4A] @enderror" required>
                        @foreach($medicines as $med)
                            <option value="{{ $med->id }}" {{ old('medicine_id', $schedule->medicine_id) == $med->id ? 'selected' : '' }}>
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
                            <option value="{{ $member->id }}" {{ old('family_member_id', $schedule->family_member_id) == $member->id ? 'selected' : '' }}>
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
                    <input type="time" name="schedule_time" id="schedule_time" value="{{ old('schedule_time', \Carbon\Carbon::parse($schedule->schedule_time)->format('H:i')) }}" class="obk-input @error('schedule_time') border-[#E24B4A] @enderror" required>
                    @error('schedule_time')
                        <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="frequency" class="block text-sm font-semibold text-gray-700 mb-1">Frekuensi Pengulangan <span class="text-[#E24B4A]">*</span></label>
                    <select name="frequency" id="frequency" class="obk-input @error('frequency') border-[#E24B4A] @enderror" required>
                        <option value="daily" {{ old('frequency', $schedule->frequency) == 'daily' ? 'selected' : '' }}>Setiap Hari (1x Sehari)</option>
                        <option value="twice_daily" {{ old('frequency', $schedule->frequency) == 'twice_daily' ? 'selected' : '' }}>2 Kali Sehari</option>
                        <option value="three_daily" {{ old('frequency', $schedule->frequency) == 'three_daily' ? 'selected' : '' }}>3 Kali Sehari</option>
                        <option value="weekly" {{ old('frequency', $schedule->frequency) == 'weekly' ? 'selected' : '' }}>Mingguan</option>
                        <option value="monthly" {{ old('frequency', $schedule->frequency) == 'monthly' ? 'selected' : '' }}>Bulanan</option>
                        <option value="as_needed" {{ old('frequency', $schedule->frequency) == 'as_needed' ? 'selected' : '' }}>Bila Perlu / Sesuai Gejala</option>
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
                    <input type="text" name="dosage_amount" id="dosage_amount" value="{{ old('dosage_amount', $schedule->dosage_amount) }}" class="obk-input @error('dosage_amount') border-[#E24B4A] @enderror">
                    @error('dosage_amount')
                        <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="notes" class="block text-sm font-semibold text-gray-700 mb-1">Petunjuk / Catatan Khusus</label>
                    <input type="text" name="notes" id="notes" value="{{ old('notes', $schedule->notes) }}" class="obk-input @error('notes') border-[#E24B4A] @enderror">
                    @error('notes')
                        <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Start & End Date -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="start_date" class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Mulai <span class="text-[#E24B4A]">*</span></label>
                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $schedule->start_date ? \Carbon\Carbon::parse($schedule->start_date)->format('Y-m-d') : '') }}" class="obk-input @error('start_date') border-[#E24B4A] @enderror" required>
                    @error('start_date')
                        <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Berakhir (Opsional)</label>
                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $schedule->end_date ? \Carbon\Carbon::parse($schedule->end_date)->format('Y-m-d') : '') }}" class="obk-input @error('end_date') border-[#E24B4A] @enderror">
                    @error('end_date')
                        <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Active Checkbox -->
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $schedule->is_active) ? 'checked' : '' }} class="w-5 h-5 text-[#185FA5] rounded border-gray-300 focus:ring-[#185FA5]">
                <label for="is_active" class="text-sm font-semibold text-gray-700">Jadwal Aktif</label>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-50">
                <a href="{{ route('schedules.index') }}" class="obk-btn obk-btn-outline">Batal</a>
                <button type="submit" class="obk-btn obk-btn-primary flex items-center gap-2">
                    <i data-lucide="save" class="w-5 h-5"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
@endsection
