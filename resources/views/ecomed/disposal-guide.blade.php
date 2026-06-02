@extends('layouts.app')
@section('title', 'Panduan Pembuangan Obat — EcoMed')
@section('header_title', 'EcoMed · Panduan Pembuangan')

@section('content')

{{-- Page Header --}}
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 animate-fade-in-up">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <a href="{{ route('ecomed.index') }}" class="text-gray-400 hover:text-[#185FA5] text-sm transition-colors">EcoMed</a>
            <span class="text-gray-300">/</span>
            <span class="text-sm font-medium text-[#042C53]">Panduan Pembuangan</span>
        </div>
        <h1 class="text-2xl font-bold text-[#042C53]">Panduan Pembuangan Obat</h1>
        <p class="text-gray-500 mt-1">Pelajari cara aman membuang sisa atau obat kedaluwarsa sesuai bentuk sediaannya demi menjaga lingkungan.</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('ecomed.waste-reports') }}" class="obk-btn obk-btn-success text-sm flex items-center gap-2">
            <i data-lucide="clipboard-list" class="w-4 h-4"></i> Catat Pembuangan
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 animate-fade-in-up" style="animation-delay: 0.1s">
    
    {{-- Left Column: Available Forms --}}
    <div class="lg:col-span-1 space-y-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-bold text-[#042C53] mb-4 flex items-center gap-2">
                <i data-lucide="shapes" class="w-5 h-5 text-[#185FA5]"></i>
                Bentuk Sediaan Obat
            </h3>
            <div class="space-y-2">
                @forelse($guides as $g)
                    @php
                        $isSelected = ($form === $g->medicine_form) || (!$form && $loop->first && !$guide);
                        if (!$form && $loop->first) {
                            $guide = $g; // Default to first guide if none selected
                        }
                    @endphp
                    <a href="{{ route('ecomed.disposal-guide') }}?form={{ $g->medicine_form }}" 
                       class="flex items-center gap-3 p-3.5 rounded-xl border transition-all group {{ $isSelected ? 'bg-emerald-50/50 border-[#1D9E75] text-[#1D9E75]' : 'bg-white border-gray-100 hover:border-gray-300 text-gray-700 hover:bg-gray-50' }}">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 transition-colors {{ $isSelected ? 'bg-[#1D9E75]/10 text-[#1D9E75]' : 'bg-gray-100 text-gray-500 group-hover:bg-gray-200' }}">
                            <i data-lucide="{{ $g->icon ?? 'recycle' }}" class="w-5 h-5"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="block text-sm font-semibold capitalize {{ $isSelected ? 'text-[#1D9E75]' : 'text-[#042C53]' }}">{{ $g->medicine_form }}</span>
                            <span class="block text-xs text-gray-400 truncate mt-0.5">{{ $g->title }}</span>
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 ml-auto transition-transform group-hover:translate-x-0.5 {{ $isSelected ? 'text-[#1D9E75]' : 'text-gray-300' }}"></i>
                    </a>
                @empty
                    <p class="text-sm text-gray-400 text-center py-4">Belum ada panduan tersedia.</p>
                @endforelse
            </div>
        </div>

        {{-- Educational box --}}
        <div class="bg-gradient-to-br from-[#042C53] to-[#185FA5] rounded-xl p-5 text-white shadow-sm relative overflow-hidden">
            <div class="absolute -right-8 -bottom-8 opacity-10">
                <i data-lucide="leaf" class="w-32 h-32"></i>
            </div>
            <h4 class="font-bold text-base mb-2">🌿 Mengapa Penting?</h4>
            <p class="text-xs text-white/80 leading-relaxed mb-3">
                Membuang obat sembarangan (seperti membuang ke wastafel atau tempat sampah tanpa diolah) dapat mencemari air tanah, meracuni satwa liar, dan memicu resistensi antibiotik di lingkungan sekitar kita.
            </p>
            <div class="text-[10px] bg-white/10 px-2.5 py-1.5 rounded-lg border border-white/20 inline-block font-semibold">
                Mendukung SDG 12: Konsumsi & Produksi Bertanggung Jawab
            </div>
        </div>
    </div>

    {{-- Right Column: Guide Details --}}
    <div class="lg:col-span-2">
        @if($guide)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
                
                {{-- Active Guide Title --}}
                <div class="border-b border-gray-100 pb-5">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-12 h-12 rounded-xl bg-[#1D9E75]/10 text-[#1D9E75] flex items-center justify-center">
                            <i data-lucide="{{ $guide->icon ?? 'recycle' }}" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-[#1D9E75] bg-[#e6f7f1] px-2.5 py-1 rounded-full uppercase">Sediaan {{ $guide->medicine_form }}</span>
                            <h2 class="text-xl font-bold text-[#042C53] mt-1">{{ $guide->title }}</h2>
                        </div>
                    </div>
                    <p class="text-gray-600 text-sm mt-3 leading-relaxed">{{ $guide->description }}</p>
                </div>

                {{-- Steps --}}
                <div>
                    <h3 class="font-bold text-[#042C53] text-base mb-4 flex items-center gap-2">
                        <i data-lucide="list-ordered" class="w-5 h-5 text-[#1D9E75]"></i>
                        Langkah-Langkah Pembuangan
                    </h3>
                    
                    <div class="space-y-4">
                        @if(is_array($guide->steps))
                            @foreach($guide->steps as $index => $step)
                                <div class="flex items-start gap-4 p-4 rounded-xl bg-gray-50 border border-gray-100/50 hover:border-gray-200 transition-colors">
                                    <div class="w-7 h-7 rounded-full bg-[#1D9E75] text-white flex items-center justify-center text-sm font-bold shrink-0 mt-0.5 shadow-sm">
                                        {{ $index + 1 }}
                                    </div>
                                    <div class="text-sm text-gray-700 leading-relaxed pt-0.5">
                                        {!! nl2br(e($step)) !!}
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-sm text-gray-500">Detail langkah pembuangan tidak tersedia.</p>
                        @endif
                    </div>
                </div>

                {{-- Warning Box --}}
                <div class="bg-amber-50/50 border border-[#EF9F27]/20 rounded-xl p-4 flex gap-3.5">
                    <i data-lucide="alert-circle" class="w-6 h-6 text-[#EF9F27] shrink-0 mt-0.5"></i>
                    <div>
                        <h4 class="font-bold text-[#EF9F27] text-sm">Penting Sebelum Membuang</h4>
                        <ul class="list-disc list-inside text-xs text-gray-600 mt-1 space-y-1 leading-relaxed">
                            <li>Selalu hilangkan informasi pribadi (nama, nomor resep) dari label kemasan obat.</li>
                            <li>Rusak kemasan asli (seperti botol atau strip obat) agar tidak disalahgunakan.</li>
                            <li>Campurkan obat tablet/sirup dengan bahan yang tidak menarik seperti ampas kopi atau tanah jika dibuang ke tempat sampah rumah tangga.</li>
                        </ul>
                    </div>
                </div>

            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                <i data-lucide="file-text" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
                <h3 class="text-lg font-bold text-[#042C53] mb-1">Panduan Tidak Ditemukan</h3>
                <p class="text-gray-500 text-sm max-w-md mx-auto">Silakan pilih salah satu bentuk sediaan obat di kolom sebelah kiri untuk memuat panduan pembuangan yang tepat.</p>
            </div>
        @endif
    </div>

</div>

@endsection
