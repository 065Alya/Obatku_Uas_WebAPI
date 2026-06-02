@extends('layouts.app')
@section('title', 'Laporan Pembuangan Obat — EcoMed')
@section('header_title', 'EcoMed · Laporan Pembuangan')

@section('content')

{{-- Page Header --}}
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 animate-fade-in-up">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <a href="{{ route('ecomed.index') }}" class="text-gray-400 hover:text-[#185FA5] text-sm transition-colors">EcoMed</a>
            <span class="text-gray-300">/</span>
            <span class="text-sm font-medium text-[#042C53]">Laporan Pembuangan</span>
        </div>
        <h1 class="text-2xl font-bold text-[#042C53]">Laporan Pembuangan Obat</h1>
        <p class="text-gray-500 mt-1">Catat aktivitas pembuangan obat Anda untuk mendukung pelacakan limbah medis yang aman bagi lingkungan.</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('ecomed.disposal-guide') }}" class="obk-btn obk-btn-outline text-sm flex items-center gap-2">
            <i data-lucide="book-open" class="w-4 h-4"></i> Panduan Pembuangan
        </a>
    </div>
</div>

{{-- Stats Strip --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8 animate-fade-in-up" style="animation-delay: 0.05s">
    <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm text-center">
        <div class="text-3xl font-bold text-[#042C53]">{{ $wasteStats['total'] ?? 0 }}</div>
        <div class="text-xs text-gray-500 mt-1 font-semibold uppercase">Total Laporan</div>
    </div>
    <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm text-center">
        <div class="text-3xl font-bold text-[#1D9E75]">{{ $wasteStats['verified'] ?? 0 }}</div>
        <div class="text-xs text-gray-500 mt-1 font-semibold uppercase">Terverifikasi</div>
    </div>
    <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm text-center">
        <div class="text-3xl font-bold text-[#EF9F27]">{{ $wasteStats['pending'] ?? 0 }}</div>
        <div class="text-xs text-gray-500 mt-1 font-semibold uppercase">Menunggu</div>
    </div>
    <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm text-center">
        <div class="text-3xl font-bold text-[#7F77DD]">{{ number_format($wasteStats['total_quantity'] ?? 0, 1) }}</div>
        <div class="text-xs text-gray-500 mt-1 font-semibold uppercase">Unit Dibuang</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 animate-fade-in-up" style="animation-delay: 0.1s">
    
    {{-- Left Column: Form to log disposal --}}
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-4">
            <h3 class="font-bold text-[#042C53] text-lg border-b border-gray-50 pb-3 flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-5 h-5 text-[#1D9E75]"></i>
                Catat Pembuangan Baru
            </h3>

            <form action="{{ route('ecomed.waste-reports.store') }}" method="POST" class="space-y-4">
                @csrf

                {{-- Select Medicine --}}
                <div>
                    <label for="select_medicine" class="block text-sm font-semibold text-gray-700 mb-1">Pilih dari Obat Aktif (Opsional)</label>
                    <select id="select_medicine" name="medicine_id" class="obk-input text-sm">
                        <option value="">-- Isi Manual atau Pilih Obat --</option>
                        @foreach($medicines as $med)
                            <option value="{{ $med->id }}" data-name="{{ $med->medicine_name }}" data-form="{{ $med->form }}" data-unit="{{ $med->unit }}">
                                {{ $med->medicine_name }} (Sisa: {{ $med->stock }} {{ $med->unit }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Medicine Name --}}
                <div>
                    <label for="medicine_name" class="block text-sm font-semibold text-gray-700 mb-1">Nama Obat <span class="text-[#E24B4A]">*</span></label>
                    <input type="text" name="medicine_name" id="medicine_name" required value="{{ old('medicine_name') }}" class="obk-input text-sm" placeholder="Nama obat yang dibuang">
                    @error('medicine_name')
                        <p class="text-xs text-[#E24B4A] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Medicine Form --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="medicine_form" class="block text-sm font-semibold text-gray-700 mb-1">Bentuk <span class="text-[#E24B4A]">*</span></label>
                        <select name="medicine_form" id="medicine_form" required class="obk-input text-sm">
                            <option value="tablet" {{ old('medicine_form') == 'tablet' ? 'selected' : '' }}>Tablet</option>
                            <option value="sirup" {{ old('medicine_form') == 'sirup' ? 'selected' : '' }}>Sirup</option>
                            <option value="kapsul" {{ old('medicine_form') == 'kapsul' ? 'selected' : '' }}>Kapsul</option>
                            <option value="salep" {{ old('medicine_form') == 'salep' ? 'selected' : '' }}>Salep / Krim</option>
                            <option value="injeksi" {{ old('medicine_form') == 'injeksi' ? 'selected' : '' }}>Injeksi / Suntik</option>
                            <option value="tetes" {{ old('medicine_form') == 'tetes' ? 'selected' : '' }}>Obat Tetes</option>
                            <option value="inhaler" {{ old('medicine_form') == 'inhaler' ? 'selected' : '' }}>Inhaler</option>
                        </select>
                    </div>
                    <div>
                        <label for="unit" class="block text-sm font-semibold text-gray-700 mb-1">Satuan <span class="text-[#E24B4A]">*</span></label>
                        <input type="text" name="unit" id="unit" required value="{{ old('unit', 'tablet') }}" class="obk-input text-sm" placeholder="tablet, kapsul, ml, dll">
                    </div>
                </div>

                {{-- Quantity --}}
                <div>
                    <label for="quantity" class="block text-sm font-semibold text-gray-700 mb-1">Jumlah Dibuang <span class="text-[#E24B4A]">*</span></label>
                    <input type="number" name="quantity" id="quantity" step="0.01" min="0.01" required value="{{ old('quantity') }}" class="obk-input text-sm" placeholder="0">
                </div>

                {{-- Disposal Method --}}
                <div>
                    <label for="disposal_method" class="block text-sm font-semibold text-gray-700 mb-1">Metode Pembuangan <span class="text-[#E24B4A]">*</span></label>
                    <select name="disposal_method" id="disposal_method" required class="obk-input text-sm">
                        <option value="pharmacy_return" {{ old('disposal_method') == 'pharmacy_return' ? 'selected' : '' }}>Kembalikan ke Apotek</option>
                        <option value="household_trash" {{ old('disposal_method') == 'household_trash' ? 'selected' : '' }}>Sampah Rumah Tangga (Dihancurkan)</option>
                        <option value="collection_point" {{ old('disposal_method') == 'collection_point' ? 'selected' : '' }}>Titik Pengumpulan Limbah Obat</option>
                        <option value="flush" {{ old('disposal_method') == 'flush' ? 'selected' : '' }}>Siram ke Wastafel/Toilet</option>
                        <option value="bury" {{ old('disposal_method') == 'bury' ? 'selected' : '' }}>Bury (Dikubur di Tanah)</option>
                    </select>
                </div>

                {{-- Disposed At --}}
                <div>
                    <label for="disposed_at" class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Pembuangan <span class="text-[#E24B4A]">*</span></label>
                    <input type="date" name="disposed_at" id="disposed_at" required value="{{ old('disposed_at', now()->toDateString()) }}" class="obk-input text-sm">
                </div>

                {{-- Notes --}}
                <div>
                    <label for="notes" class="block text-sm font-semibold text-gray-700 mb-1">Catatan Tambahan (Opsional)</label>
                    <textarea name="notes" id="notes" rows="2" class="obk-input text-sm" placeholder="Contoh: Obat kedaluwarsa digerus dan dicampur tanah.">{{ old('notes') }}</textarea>
                </div>

                <button type="submit" class="obk-btn obk-btn-success w-full py-3">
                    <i data-lucide="check" class="w-5 h-5"></i> Simpan Laporan
                </button>
            </form>
        </div>
    </div>

    {{-- Right Column: Past Reports --}}
    <div class="lg:col-span-2 space-y-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-bold text-[#042C53] text-lg">Riwayat Pembuangan</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="obk-table w-full">
                    <thead>
                        <tr>
                            <th>Obat</th>
                            <th>Metode</th>
                            <th>Jumlah</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $rep)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td>
                                    <div class="font-semibold text-[#042C53]">{{ $rep->medicine_name }}</div>
                                    <div class="text-xs text-gray-400 capitalize">{{ $rep->medicine_form }}</div>
                                </td>
                                <td>
                                    <div class="text-sm font-medium text-gray-700">{{ $rep->disposal_method_label }}</div>
                                    @if($rep->notes)
                                        <div class="text-xs text-gray-400 mt-0.5 max-w-[200px] truncate" title="{{ $rep->notes }}">{{ $rep->notes }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="font-bold text-gray-700">{{ number_format($rep->quantity, 1) }}</span> 
                                    <span class="text-xs text-gray-500">{{ $rep->unit }}</span>
                                </td>
                                <td>
                                    <div class="text-sm font-semibold text-gray-700">{{ $rep->disposed_at->format('d M Y') }}</div>
                                </td>
                                <td>
                                    @php
                                        $statusColor = $rep->status === 'verified' ? 'bg-[#e6f7f1] text-[#1D9E75]' 
                                                     : ($rep->status === 'rejected' ? 'bg-[#fde9e9] text-[#E24B4A]' : 'bg-[#fef5e6] text-[#EF9F27]');
                                    @endphp
                                    <span class="text-xs font-bold px-2.5 py-1 rounded-lg {{ $statusColor }}">
                                        {{ $rep->status_label }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-gray-500">
                                    <i data-lucide="clipboard" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
                                    <p class="font-semibold">Belum ada laporan pembuangan.</p>
                                    <p class="text-xs text-gray-400 mt-1">Catat pembuangan obat pertama Anda menggunakan form di sebelah kiri.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($reports->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                    {{ $reports->links() }}
                </div>
            @endif
        </div>
    </div>

</div>

@endsection

@push('head')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selector = document.getElementById('select_medicine');
    const nameInput = document.getElementById('medicine_name');
    const formInput = document.getElementById('medicine_form');
    const unitInput = document.getElementById('unit');

    selector?.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        if (option.value) {
            nameInput.value = option.getAttribute('data-name') || '';
            
            // Map form value
            const formVal = option.getAttribute('data-form') || 'tablet';
            // ensure value matches select option
            for (let i = 0; i < formInput.options.length; i++) {
                if (formInput.options[i].value === formVal) {
                    formInput.selectedIndex = i;
                    break;
                }
            }
            
            unitInput.value = option.getAttribute('data-unit') || 'tablet';
        }
    });
});
</script>
@endpush
