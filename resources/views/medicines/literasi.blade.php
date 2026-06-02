@extends('layouts.app')
@section('title', 'Kartu Literasi Obat — ' . $medicine->name)

@push('head')
<style>
    .literacy-card { transition: box-shadow 0.2s; }
    .literacy-card:hover { box-shadow: 0 8px 24px -4px rgba(0,0,0,0.10); }
    .html.accessibility-large .text-literasi-body { font-size: 1.0625rem !important; }
</style>
@endpush

@section('content')

{{-- Header --}}
<div class="mb-8 animate-fade-in-up">
    <a href="{{ route('medicines.show', $medicine->id) }}"
       class="inline-flex items-center gap-2 text-sm font-semibold text-[#185FA5] hover:text-[#042C53] mb-3 transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Detail Obat
    </a>

    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <span class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-widest text-[#7F77DD] mb-2">
                <i data-lucide="book-open" class="w-3.5 h-3.5"></i> Kartu Literasi Obat
            </span>
            <h1 class="text-3xl font-bold text-[#042C53]">{{ $medicine->name }}</h1>
            @if($medicine->generic_name)
                <p class="text-sm text-gray-400 italic mt-0.5">Nama Generik: {{ $medicine->generic_name }}</p>
            @endif
        </div>

        {{-- Accessibility toggle --}}
        <button onclick="toggleAccessibilityMode()"
                id="accessibility-toggle"
                class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-sm font-semibold text-gray-600 hover:border-[#185FA5] hover:text-[#185FA5] transition-colors self-start">
            <i data-lucide="zoom-in" id="zoom-icon" class="w-4 h-4"></i>
            <span>Mode Huruf Besar</span>
        </button>
    </div>
</div>

{{-- Source notice --}}
@if(isset($card) && $card)
    <div class="mb-6 p-3 rounded-xl bg-blue-50 border border-blue-100 flex items-center gap-2 text-xs text-blue-700 animate-fade-in-up" style="animation-delay: 0.05s">
        <i data-lucide="info" class="w-4 h-4 shrink-0"></i>
        <span>Informasi di bawah bersumber dari <strong>Open FDA Drug Label API</strong>. Selalu konsultasikan dengan dokter atau apoteker Anda sebelum mengubah dosis atau menghentikan penggunaan obat.</span>
    </div>
@endif

@if(!isset($card) || !$card)
    {{-- No FDA data --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-10 text-center animate-fade-in-up" style="animation-delay: 0.1s">
        <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
            <i data-lucide="search-x" class="w-8 h-8 text-gray-400"></i>
        </div>
        <h3 class="text-lg font-bold text-[#042C53] mb-2">Data Literasi Tidak Ditemukan</h3>
        <p class="text-gray-500 text-sm max-w-md mx-auto mb-6">
            Informasi literasi untuk <strong>{{ $medicine->name }}</strong>
            @if($medicine->generic_name) / <strong>{{ $medicine->generic_name }}</strong>@endif
            tidak tersedia di database Open FDA.
            Hal ini umum untuk obat-obatan produksi dalam negeri atau generik non-FDA.
        </p>
        <div class="flex flex-wrap gap-3 justify-center">
            <a href="{{ route('medicines.show', $medicine->id) }}" class="obk-btn obk-btn-outline">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Detail Obat
            </a>
        </div>
    </div>
@else
    {{-- ── 4-Category Literacy Cards ─────────────────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 animate-fade-in-up" style="animation-delay: 0.1s">

        {{-- 🔵 KEGUNAAN (Blue) --}}
        <div class="literacy-card bg-white rounded-xl shadow-sm border-t-4 border-[#185FA5] p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-[#185FA5]/10 flex items-center justify-center">
                    <i data-lucide="stethoscope" class="w-5 h-5 text-[#185FA5]"></i>
                </div>
                <div>
                    <h2 class="font-bold text-[#185FA5] text-base">🔵 Kegunaan</h2>
                    <p class="text-xs text-gray-400">Indikasi & manfaat obat</p>
                </div>
            </div>
            <div class="text-sm text-gray-700 leading-relaxed text-literasi-body space-y-2">
                @if(!empty($card['indications']))
                    <p>{{ $card['indications'] }}</p>
                @else
                    <p class="text-gray-400 italic">Informasi kegunaan tidak tersedia untuk obat ini di database FDA.</p>
                @endif
            </div>
        </div>

        {{-- 🟡 EFEK SAMPING (Yellow) --}}
        <div class="literacy-card bg-white rounded-xl shadow-sm border-t-4 border-[#EF9F27] p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-[#EF9F27]/10 flex items-center justify-center">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-[#EF9F27]"></i>
                </div>
                <div>
                    <h2 class="font-bold text-[#EF9F27] text-base">🟡 Efek Samping</h2>
                    <p class="text-xs text-gray-400">Reaksi yang mungkin terjadi</p>
                </div>
            </div>
            <div class="text-sm text-gray-700 leading-relaxed text-literasi-body space-y-2">
                @if(!empty($card['adverse_reactions']))
                    <p>{{ $card['adverse_reactions'] }}</p>
                @else
                    <p class="text-gray-400 italic">Data efek samping tidak tersedia di database FDA untuk obat ini.</p>
                @endif
            </div>
        </div>

        {{-- 🔴 LARANGAN (Red) --}}
        <div class="literacy-card bg-white rounded-xl shadow-sm border-t-4 border-[#E24B4A] p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-[#E24B4A]/10 flex items-center justify-center">
                    <i data-lucide="ban" class="w-5 h-5 text-[#E24B4A]"></i>
                </div>
                <div>
                    <h2 class="font-bold text-[#E24B4A] text-base">🔴 Larangan</h2>
                    <p class="text-xs text-gray-400">Kontraindikasi & peringatan</p>
                </div>
            </div>
            <div class="text-sm text-gray-700 leading-relaxed text-literasi-body space-y-2">
                @if(!empty($card['contraindications']))
                    <p class="mb-2">{{ $card['contraindications'] }}</p>
                @endif
                @if(!empty($card['warnings']))
                    <div class="p-3 bg-red-50 border border-red-100 rounded-lg text-xs text-red-700">
                        <strong>Peringatan:</strong> {{ $card['warnings'] }}
                    </div>
                @endif
                @if(empty($card['contraindications']) && empty($card['warnings']))
                    <p class="text-gray-400 italic">Data larangan & kontraindikasi tidak tersedia di database FDA.</p>
                @endif
            </div>
        </div>

        {{-- 🟣 INTERAKSI OBAT (Purple) --}}
        <div class="literacy-card bg-white rounded-xl shadow-sm border-t-4 border-[#7F77DD] p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-[#7F77DD]/10 flex items-center justify-center">
                    <i data-lucide="git-compare" class="w-5 h-5 text-[#7F77DD]"></i>
                </div>
                <div>
                    <h2 class="font-bold text-[#7F77DD] text-base">🟣 Interaksi Obat</h2>
                    <p class="text-xs text-gray-400">Kombinasi yang perlu diwaspadai</p>
                </div>
            </div>
            <div class="text-sm text-gray-700 leading-relaxed text-literasi-body space-y-2">
                @if(!empty($card['drug_interactions']))
                    <p>{{ $card['drug_interactions'] }}</p>
                @else
                    <p class="text-gray-400 italic">Data interaksi obat tidak tersedia di database FDA untuk obat ini.</p>
                @endif

                {{-- Link to check interactions with user's other medicines --}}
                <div class="pt-3 border-t border-gray-50 mt-3">
                    <p class="text-xs text-gray-500 mb-2">Periksa interaksi dengan obat Anda lainnya:</p>
                    <a href="{{ route('medicines.index') }}"
                       class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#7F77DD] hover:underline">
                        <i data-lucide="search" class="w-3.5 h-3.5"></i>
                        Cek Interaksi di Daftar Obat →
                    </a>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Additional Info ─────────────────────────────────────────────── --}}
    @if(!empty($card['dosage_administration']) || !empty($card['storage_conditions']) || !empty($card['brand_name']))
    <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-100 p-6 animate-fade-in-up" style="animation-delay: 0.2s">
        <h3 class="text-lg font-bold text-[#042C53] border-b border-gray-50 pb-3 mb-4 flex items-center gap-2">
            <i data-lucide="clipboard-list" class="w-5 h-5 text-[#185FA5]"></i>
            Informasi Tambahan
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @if(!empty($card['brand_name']))
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Nama Brand (FDA)</p>
                <p class="text-sm font-bold text-[#042C53]">{{ $card['brand_name'] }}</p>
            </div>
            @endif
            @if(!empty($card['dosage_administration']))
            <div class="md:col-span-2">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Cara Penggunaan & Dosis</p>
                <p class="text-sm text-gray-700 leading-relaxed text-literasi-body">{{ Str::limit($card['dosage_administration'], 300) }}</p>
            </div>
            @endif
            @if(!empty($card['storage_conditions']))
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Penyimpanan</p>
                <p class="text-sm text-gray-700 text-literasi-body">{{ $card['storage_conditions'] }}</p>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- ── EcoMed Link ─────────────────────────────────────────────────── --}}
    <div class="mt-6 p-5 bg-[#E1F5EE] rounded-xl border border-[#1D9E75]/20 flex items-center gap-4 animate-fade-in-up" style="animation-delay: 0.25s">
        <div class="w-12 h-12 bg-[#1D9E75] rounded-xl flex items-center justify-center shrink-0">
            <i data-lucide="leaf" class="w-6 h-6 text-white"></i>
        </div>
        <div class="flex-1">
            <p class="font-bold text-[#085538] text-sm">Obat mendekati kedaluwarsa?</p>
            <p class="text-xs text-[#1D9E75] mt-0.5">Pelajari cara membuang obat dengan aman — ikuti panduan EcoMed kami (SDG 12).</p>
        </div>
        <a href="{{ route('ecomed.disposal-guide') }}" class="shrink-0 px-4 py-2 bg-[#1D9E75] text-white text-xs font-bold rounded-lg hover:bg-[#18855f] transition-colors">
            Panduan Disposal →
        </a>
    </div>

@endif

@endsection
