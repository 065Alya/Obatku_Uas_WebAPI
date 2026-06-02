@extends('layouts.app')
@section('title', 'Edukasi Obat — Literasi Kesehatan')
@section('header', 'Artikel & Edukasi Obat')
@section('subheader', 'Tingkatkan literasi obat keluarga Anda untuk pencegahan interaksi obat dan pemakaian yang aman.')

@section('content')

{{-- Featured Article (Only if articles exist) --}}
@php
    $featured = $articles->first();
    $remainingArticles = $articles->skip(1);
@endphp

@if($featured)
<div class="mb-8 rounded-2xl bg-white border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-300 animate-fade-in-up">
    <div class="grid grid-cols-1 lg:grid-cols-12">
        <div class="lg:col-span-7 p-6 sm:p-8 flex flex-col justify-between space-y-6">
            <div class="space-y-4">
                <div class="flex items-center gap-2">
                    <span class="bg-[#7F77DD]/10 text-[#7F77DD] text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">
                        {{ $featured->category }}
                    </span>
                    <span class="text-xs text-gray-400 font-medium">
                        {{ $featured->published_at ? $featured->published_at->translatedFormat('d M Y') : $featured->created_at->translatedFormat('d M Y') }}
                    </span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-bold text-[#042C53] leading-tight">
                    <a href="{{ route('articles.show', $featured->slug) }}" class="hover:text-[#7F77DD] transition-colors">
                        {{ $featured->title }}
                    </a>
                </h2>
                <p class="text-gray-500 text-sm sm:text-base leading-relaxed">
                    {{ $featured->excerpt ?? Str::limit(strip_tags($featured->content), 180) }}
                </p>
            </div>
            
            <div class="flex items-center justify-between pt-4 border-t border-gray-50">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-[#185FA5] text-white flex items-center justify-center text-xs font-bold">
                        {{ strtoupper(substr($featured->author->name ?? 'A', 0, 1)) }}
                    </div>
                    <span class="text-sm font-semibold text-[#042C53]">{{ $featured->author->name ?? 'Tim Medis ObatKu' }}</span>
                </div>
                <a href="{{ route('articles.show', $featured->slug) }}" class="obk-btn text-white bg-[#7F77DD] hover:bg-[#6860c4] text-sm px-5 py-2">
                    Baca Selengkapnya
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
        </div>
        <div class="lg:col-span-5 h-64 lg:h-auto min-h-[250px] relative bg-purple-50">
            @if($featured->image)
                <img src="{{ asset('storage/' . $featured->image) }}" class="w-full h-full object-cover" alt="{{ $featured->title }}">
            @else
                <div class="w-full h-full flex flex-col items-center justify-center text-[#7F77DD] p-8 text-center bg-gradient-to-br from-[#7F77DD]/10 to-[#185FA5]/10">
                    <i data-lucide="book-open" class="w-16 h-16 mb-2 opacity-55"></i>
                    <span class="text-sm font-bold opacity-60">ObatKu Medicine Literacy</span>
                </div>
            @endif
        </div>
    </div>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 animate-fade-in-up" style="animation-delay: 0.1s">
    
    {{-- Main Articles Grid --}}
    <div class="lg:col-span-2 space-y-6">
        <h3 class="font-bold text-[#042C53] text-xl border-b border-gray-100 pb-3">Semua Edukasi Obat</h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            @forelse($remainingArticles as $art)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col justify-between hover:shadow-md transition-all group">
                    <div>
                        <div class="h-44 bg-purple-50 relative">
                            @if($art->image)
                                <img src="{{ asset('storage/' . $art->image) }}" class="w-full h-full object-cover" alt="{{ $art->title }}">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-[#7F77DD] bg-gradient-to-br from-[#7F77DD]/5 to-[#185FA5]/5">
                                    <i data-lucide="pill" class="w-10 h-10 opacity-40"></i>
                                </div>
                            @endif
                            <span class="absolute top-3 left-3 bg-white/90 backdrop-blur text-[#7F77DD] text-[10px] font-bold px-2 py-0.5 rounded-md uppercase">
                                {{ $art->category }}
                            </span>
                        </div>
                        <div class="p-5 space-y-3">
                            <span class="text-xs text-gray-400 font-medium">
                                {{ $art->published_at ? $art->published_at->translatedFormat('d M Y') : $art->created_at->translatedFormat('d M Y') }}
                            </span>
                            <h4 class="font-bold text-[#042C53] leading-snug group-hover:text-[#7F77DD] transition-colors line-clamp-2">
                                <a href="{{ route('articles.show', $art->slug) }}">{{ $art->title }}</a>
                            </h4>
                            <p class="text-gray-500 text-xs leading-relaxed line-clamp-3">
                                {{ $art->excerpt ?? Str::limit(strip_tags($art->content), 120) }}
                            </p>
                        </div>
                    </div>
                    
                    <div class="px-5 pb-5 pt-3 border-t border-gray-50 flex items-center justify-between text-xs text-gray-400">
                        <span class="flex items-center gap-1">
                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                            {{ $art->views_count }} x dibaca
                        </span>
                        <a href="{{ route('articles.show', $art->slug) }}" class="text-[#7F77DD] font-bold hover:underline inline-flex items-center gap-0.5">
                            Baca →
                        </a>
                    </div>
                </div>
            @empty
                @if(!$featured)
                    <div class="col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 py-16 text-center">
                        <i data-lucide="book-open" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
                        <h4 class="text-lg font-bold text-[#042C53] mb-1">Belum Ada Artikel</h4>
                        <p class="text-gray-500 text-sm">Artikel edukasi obat akan segera ditambahkan oleh admin.</p>
                    </div>
                @endif
            @endforelse
        </div>
    </div>

    {{-- Right Sidebar: Popular Articles & EcoMed Box --}}
    <div class="lg:col-span-1 space-y-6">
        
        {{-- Popular Articles --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-bold text-[#042C53] text-base border-b border-gray-50 pb-3 flex items-center gap-2">
                <i data-lucide="trending-up" class="w-5 h-5 text-[#7F77DD]"></i>
                Artikel Terpopuler
            </h3>
            
            <div class="divide-y divide-gray-50 mt-3">
                @forelse($popular as $pop)
                    <div class="py-3 flex gap-3.5 first:pt-0 last:pb-0 group">
                        <div class="w-12 h-12 bg-purple-50 rounded-lg shrink-0 overflow-hidden">
                            @if($pop->image)
                                <img src="{{ asset('storage/' . $pop->image) }}" class="w-full h-full object-cover" alt="{{ $pop->title }}">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-[#7F77DD]/60">
                                    <i data-lucide="book-open" class="w-5 h-5"></i>
                                </div>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <h4 class="text-sm font-bold text-[#042C53] group-hover:text-[#7F77DD] transition-colors leading-tight line-clamp-2">
                                <a href="{{ route('articles.show', $pop->slug) }}">{{ $pop->title }}</a>
                            </h4>
                            <p class="text-[11px] text-gray-400 mt-1 flex items-center gap-2">
                                <span>{{ $pop->published_at ? $pop->published_at->translatedFormat('d M') : $pop->created_at->translatedFormat('d M') }}</span>
                                <span>·</span>
                                <span class="flex items-center gap-0.5">
                                    <i data-lucide="eye" class="w-3 h-3"></i>
                                    {{ $pop->views_count }} views
                                </span>
                            </p>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 py-4 text-center">Belum ada artikel populer.</p>
                @endforelse
            </div>
        </div>

        {{-- EcoMed SDG 12 Promotion Card --}}
        <div class="bg-gradient-to-br from-[#1D9E75] to-[#178c67] rounded-xl p-5 text-white shadow-sm relative overflow-hidden">
            <div class="absolute -right-8 -bottom-8 opacity-10">
                <i data-lucide="leaf" class="w-32 h-32"></i>
            </div>
            <span class="bg-white/20 text-white text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">EcoMed SDG 12</span>
            <h4 class="font-bold text-base mt-2.5 mb-1.5">Kelola Sampah Obat Anda</h4>
            <p class="text-xs text-white/80 leading-relaxed mb-4">
                Selain membaca edukasi penggunaan obat, mari pelajari cara membuang sisa obat secara bertanggung jawab guna melestarikan lingkungan.
            </p>
            <a href="{{ route('ecomed.index') }}" class="inline-flex items-center gap-1 text-xs font-bold bg-white text-[#1D9E75] px-3.5 py-2 rounded-lg shadow hover:bg-emerald-50 transition-colors">
                Buka EcoMed
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

    </div>
</div>

@endsection
