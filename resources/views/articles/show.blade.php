@extends('layouts.app')
@section('title', $article->title . ' — Edukasi ObatKu')
@section('header', 'Edukasi Obat')

@section('content')

{{-- Back Button --}}
<div class="mb-6 animate-fade-in-up">
    <a href="{{ route('articles.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-[#7F77DD] hover:text-[#5a51c4] transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Daftar Artikel
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 animate-fade-in-up" style="animation-delay: 0.05s">
    
    {{-- Main Article Content Column --}}
    <article class="lg:col-span-8 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden p-6 sm:p-8 space-y-6">
        
        {{-- Metadata Header --}}
        <div class="space-y-4">
            <div class="flex items-center gap-2">
                <span class="bg-[#7F77DD]/10 text-[#7F77DD] text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">
                    {{ $article->category }}
                </span>
                <span class="text-xs text-gray-400 font-medium">
                    Dipublikasikan: {{ $article->published_at ? $article->published_at->translatedFormat('d F Y') : $article->created_at->translatedFormat('d F Y') }}
                </span>
                <span class="text-xs text-gray-300">|</span>
                <span class="text-xs text-gray-400 font-medium flex items-center gap-1">
                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                    {{ $article->views_count }} x dibaca
                </span>
            </div>
            
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-[#042C53] leading-tight">
                {{ $article->title }}
            </h1>

            <div class="flex items-center gap-3 pt-2">
                <div class="w-10 h-10 rounded-full bg-[#185FA5] text-white flex items-center justify-center text-sm font-bold shadow-sm">
                    {{ strtoupper(substr($article->author->name ?? 'A', 0, 1)) }}
                </div>
                <div>
                    <span class="block text-sm font-bold text-[#042C53]">{{ $article->author->name ?? 'Tim Medis ObatKu' }}</span>
                    <span class="block text-xs text-gray-400">Penulis Medis</span>
                </div>
            </div>
        </div>

        {{-- Cover Image --}}
        <div class="h-64 sm:h-[400px] w-full rounded-xl overflow-hidden bg-purple-50">
            @if($article->image)
                <img src="{{ asset('storage/' . $article->image) }}" class="w-full h-full object-cover" alt="{{ $article->title }}">
            @else
                <div class="w-full h-full flex flex-col items-center justify-center text-[#7F77DD] p-8 text-center bg-gradient-to-br from-[#7F77DD]/10 to-[#185FA5]/10">
                    <i data-lucide="book-open" class="w-20 h-20 mb-3 opacity-55"></i>
                    <span class="text-sm font-bold opacity-60">ObatKu Medicine Literacy Series</span>
                </div>
            @endif
        </div>

        {{-- Excerpt --}}
        @if($article->excerpt)
            <div class="border-l-4 border-[#7F77DD] bg-purple-50/50 p-4 rounded-r-xl text-gray-600 italic text-sm sm:text-base leading-relaxed">
                {{ $article->excerpt }}
            </div>
        @endif

        {{-- Body Content --}}
        <div class="text-[#042C53] leading-relaxed text-base sm:text-lg space-y-6 pt-2">
            {!! nl2br(e($article->content)) !!}
        </div>

        {{-- Tags & Footer --}}
        @if(is_array($article->tags) && count($article->tags) > 0)
            <div class="pt-6 border-t border-gray-100">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-2">Tags:</span>
                <div class="flex flex-wrap gap-2">
                    @foreach($article->tags as $tag)
                        <span class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-semibold px-2.5 py-1 rounded-lg transition-colors cursor-pointer">
                            #{{ $tag }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

    </article>

    {{-- Right Column: Side suggestions --}}
    <div class="lg:col-span-4 space-y-6">
        
        {{-- Health disclaimer --}}
        <div class="bg-amber-50/50 border border-[#EF9F27]/25 rounded-2xl p-5">
            <h4 class="font-bold text-[#EF9F27] text-sm flex items-center gap-1.5 mb-2">
                <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                Pemberitahuan Medis
            </h4>
            <p class="text-xs text-gray-600 leading-relaxed">
                Informasi dalam artikel ini bersifat edukatif saja dan tidak dapat menggantikan saran medis, diagnosis, atau pengobatan profesional dari dokter. Selalu konsultasikan masalah kesehatan Anda langsung kepada tenaga medis terpercaya.
            </p>
        </div>

        {{-- EcoMed quick promotion --}}
        <div class="bg-[#F8FAFF] rounded-2xl border border-gray-100 p-5 space-y-3">
            <h4 class="font-bold text-[#042C53] text-sm flex items-center gap-2">
                <i data-lucide="leaf" class="w-4.5 h-4.5 text-[#1D9E75]"></i>
                Tanggung Jawab Lingkungan
            </h4>
            <p class="text-xs text-gray-500 leading-relaxed">
                Punya obat sisa yang kedaluwarsa atau tidak terpakai lagi? Jangan dibuang sembarangan. Gunakan fitur EcoMed untuk mencari tahu cara pembuangan yang aman.
            </p>
            <a href="{{ route('ecomed.disposal-guide') }}" class="obk-btn obk-btn-success text-xs py-2 w-full">
                Lihat Panduan Pembuangan
            </a>
        </div>

    </div>

</div>

@endsection
