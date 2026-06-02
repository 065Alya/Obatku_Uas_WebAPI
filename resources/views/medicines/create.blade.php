@extends('layouts.app')
@section('title', 'Tambah Obat Baru')

@section('content')
    <!-- Header -->
    <div class="mb-8 animate-fade-in-up">
        <a href="{{ route('medicines.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-[#185FA5] hover:text-[#042C53] mb-3 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Daftar Obat
        </a>
        <h1 class="text-2xl font-bold text-[#042C53]">Tambah Obat Baru</h1>
        <p class="text-gray-500 mt-1">Masukkan informasi detail obat untuk mulai pelacakan.</p>
    </div>

    <!-- Form Section -->
    <form action="{{ route('medicines.store') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-8 animate-fade-in-up" style="animation-delay: 0.1s">
        @csrf

        <!-- Left column: Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
                <h3 class="text-lg font-bold text-[#042C53] border-b border-gray-50 pb-3">Informasi Umum</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">Nama Obat <span class="text-[#E24B4A]">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" class="obk-input @error('name') border-[#E24B4A] @enderror" placeholder="Contoh: Paracetamol, Amoxicillin" required>
                        @error('name')
                            <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="generic_name" class="block text-sm font-semibold text-gray-700 mb-1">Nama Generik</label>
                        <input type="text" name="generic_name" id="generic_name" value="{{ old('generic_name') }}" class="obk-input @error('generic_name') border-[#E24B4A] @enderror" placeholder="Contoh: Acetaminophen">
                        @error('generic_name')
                            <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="category_id" class="block text-sm font-semibold text-gray-700 mb-1">Kategori Obat</label>
                        <select name="category_id" id="category_id" class="obk-input @error('category_id') border-[#E24B4A] @enderror">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="family_member_id" class="block text-sm font-semibold text-gray-700 mb-1">Diberikan Kepada (Anggota Keluarga)</label>
                        <select name="family_member_id" id="family_member_id" class="obk-input @error('family_member_id') border-[#E24B4A] @enderror">
                            <option value="">Diri Sendiri (Utama)</option>
                            @foreach($familyMembers as $member)
                                <option value="{{ $member->id }}" {{ old('family_member_id') == $member->id ? 'selected' : '' }}>{{ $member->name }} ({{ $member->relationship }})</option>
                            @endforeach
                        </select>
                        @error('family_member_id')
                            <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="dosage" class="block text-sm font-semibold text-gray-700 mb-1">Dosis Obat</label>
                        <input type="text" name="dosage" id="dosage" value="{{ old('dosage') }}" class="obk-input @error('dosage') border-[#E24B4A] @enderror" placeholder="Contoh: 500mg, 10ml">
                        @error('dosage')
                            <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="unit" class="block text-sm font-semibold text-gray-700 mb-1">Satuan <span class="text-[#E24B4A]">*</span></label>
                        <select name="unit" id="unit" class="obk-input @error('unit') border-[#E24B4A] @enderror" required>
                            <option value="tablet" {{ old('unit', 'tablet') == 'tablet' ? 'selected' : '' }}>Tablet</option>
                            <option value="kapsul" {{ old('unit') == 'kapsul' ? 'selected' : '' }}>Kapsul</option>
                            <option value="botol" {{ old('unit') == 'botol' ? 'selected' : '' }}>Botol / Sirup</option>
                            <option value="ml" {{ old('unit') == 'ml' ? 'selected' : '' }}>ml</option>
                            <option value="gram" {{ old('unit') == 'gram' ? 'selected' : '' }}>Gram (Salep)</option>
                            <option value="sachet" {{ old('unit') == 'sachet' ? 'selected' : '' }}>Sachet</option>
                        </select>
                        @error('unit')
                            <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="form" class="block text-sm font-semibold text-gray-700 mb-1">Bentuk Sediaan <span class="text-[#E24B4A]">*</span></label>
                        <select name="form" id="form" class="obk-input @error('form') border-[#E24B4A] @enderror" required>
                            <option value="oral" {{ old('form', 'oral') == 'oral' ? 'selected' : '' }}>Oral (Diminum)</option>
                            <option value="luar" {{ old('form') == 'luar' ? 'selected' : '' }}>Obat Luar (Salep/Krim)</option>
                            <option value="tetes" {{ old('form') == 'tetes' ? 'selected' : '' }}>Tetes (Mata/Telinga)</option>
                            <option value="inhaler" {{ old('form') == 'inhaler' ? 'selected' : '' }}>Inhaler / Hisap</option>
                            <option value="suntik" {{ old('form') == 'suntik' ? 'selected' : '' }}>Suntik (Injeksi)</option>
                        </select>
                        @error('form')
                            <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="manufacturer" class="block text-sm font-semibold text-gray-700 mb-1">Produsen / Pabrik</label>
                    <input type="text" name="manufacturer" id="manufacturer" value="{{ old('manufacturer') }}" class="obk-input @error('manufacturer') border-[#E24B4A] @enderror" placeholder="Contoh: Kalbe Farma, Kimia Farma">
                    @error('manufacturer')
                        <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
                <h3 class="text-lg font-bold text-[#042C53] border-b border-gray-50 pb-3">Informasi Tambahan</h3>

                <div>
                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi / Petunjuk Penggunaan</label>
                    <textarea name="description" id="description" rows="3" class="obk-input @error('description') border-[#E24B4A] @enderror" placeholder="Tuliskan petunjuk penyimpanan atau catatan dokter...">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="side_effects" class="block text-sm font-semibold text-gray-700 mb-1">Efek Samping (Jika Ada)</label>
                    <textarea name="side_effects" id="side_effects" rows="2" class="obk-input @error('side_effects') border-[#E24B4A] @enderror" placeholder="Contoh: Menyebabkan kantuk, hindari menyetir setelah minum obat...">{{ old('side_effects') }}</textarea>
                    @error('side_effects')
                        <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Right column: Stock & Expiry -->
        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
                <h3 class="text-lg font-bold text-[#042C53] border-b border-gray-50 pb-3">Manajemen Stok</h3>

                <div>
                    <label for="stock" class="block text-sm font-semibold text-gray-700 mb-1">Jumlah Stok Saat Ini <span class="text-[#E24B4A]">*</span></label>
                    <input type="number" name="stock" id="stock" value="{{ old('stock', 0) }}" min="0" class="obk-input @error('stock') border-[#E24B4A] @enderror" required>
                    @error('stock')
                        <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="stock_alert_threshold" class="block text-sm font-semibold text-gray-700 mb-1">Batas Minimum Stok (Alert) <span class="text-[#E24B4A]">*</span></label>
                    <input type="number" name="stock_alert_threshold" id="stock_alert_threshold" value="{{ old('stock_alert_threshold', 5) }}" min="0" class="obk-input @error('stock_alert_threshold') border-[#E24B4A] @enderror" required>
                    <p class="text-xs text-gray-400 mt-1">Kami akan mengirimkan notifikasi peringatan jika stok mencapai atau di bawah batas ini.</p>
                    @error('stock_alert_threshold')
                        <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
                <h3 class="text-lg font-bold text-[#042C53] border-b border-gray-50 pb-3">Tanggal & Harga</h3>

                <div>
                    <label for="expiry_date" class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Kedaluwarsa <span class="text-[#E24B4A]">*</span></label>
                    <input type="date" name="expiry_date" id="expiry_date" value="{{ old('expiry_date') }}" class="obk-input @error('expiry_date') border-[#E24B4A] @enderror" required>
                    @error('expiry_date')
                        <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="price" class="block text-sm font-semibold text-gray-700 mb-1">Harga Pembelian (Per Unit)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-400 text-sm font-semibold">Rp</span>
                        <input type="number" name="price" id="price" value="{{ old('price') }}" min="0" step="0.01" class="obk-input pl-10 @error('price') border-[#E24B4A] @enderror" placeholder="0">
                    </div>
                    @error('price')
                        <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex flex-col gap-3">
                <button type="submit" class="obk-btn obk-btn-primary w-full py-3">
                    <i data-lucide="save" class="w-5 h-5"></i> Simpan Obat
                </button>
                <a href="{{ route('medicines.index') }}" class="obk-btn obk-btn-outline w-full py-3 text-center">
                    Batal
                </a>
            </div>
        </div>
    </form>
@endsection
