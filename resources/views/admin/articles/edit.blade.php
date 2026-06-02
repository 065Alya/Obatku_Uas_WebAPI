@extends('layouts.app')

@section('title', 'Ubah Artikel — Admin')

@section('content')
    <!-- Header -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 animate-fade-in-up">
        <div>
            <div class="flex items-center gap-2 text-sm text-[#185FA5] font-semibold mb-1">
                <a href="{{ route('admin.articles.index') }}" class="hover:underline flex items-center gap-1">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i> Artikel
                </a>
                <span>/</span>
                <span>Ubah Artikel</span>
            </div>
            <h1 class="text-2xl font-bold text-[#042C53]">Ubah Artikel: {{ $article->title }}</h1>
        </div>
    </div>

    <!-- Error Alerts -->
    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 space-y-1 animate-fade-in-up">
            <h4 class="text-sm font-bold text-red-800">Mohon perbaiki kesalahan berikut:</h4>
            <ul class="list-disc list-inside text-xs text-red-700">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form Container -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 max-w-4xl animate-fade-in-up" style="animation-delay: 0.05s">
        <form action="{{ route('admin.articles.update', $article->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Title -->
                <div class="md:col-span-2">
                    <label for="title" class="block text-sm font-bold text-[#042C53] mb-2">Judul Artikel</label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           value="{{ old('title', $article->title) }}" 
                           class="obk-input" 
                           placeholder="Contoh: 5 Tips Mengonsumsi Antibiotik dengan Benar" 
                           required>
                </div>

                <!-- Category -->
                <div>
                    <label for="category" class="block text-sm font-bold text-[#042C53] mb-2">Kategori</label>
                    <div class="relative">
                        <select id="category" name="category" class="obk-input appearance-none cursor-pointer" required>
                            <option value="">Pilih Kategori</option>
                            <option value="Tips Kesehatan" {{ old('category', $article->category) === 'Tips Kesehatan' ? 'selected' : '' }}>Tips Kesehatan</option>
                            <option value="Panduan Obat" {{ old('category', $article->category) === 'Panduan Obat' ? 'selected' : '' }}>Panduan Obat</option>
                            <option value="Gaya Hidup" {{ old('category', $article->category) === 'Gaya Hidup' ? 'selected' : '' }}>Gaya Hidup</option>
                            <option value="Informasi Medis" {{ old('category', $article->category) === 'Informasi Medis' ? 'selected' : '' }}>Informasi Medis</option>
                            <option value="EcoMed SDG 12" {{ old('category', $article->category) === 'EcoMed SDG 12' ? 'selected' : '' }}>EcoMed (SDG 12)</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                            <i data-lucide="chevron-down" class="w-4 h-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Excerpt -->
            <div>
                <label for="excerpt" class="block text-sm font-bold text-[#042C53] mb-2">Kutipan Singkat (Excerpt)</label>
                <textarea id="excerpt" 
                          name="excerpt" 
                          rows="2" 
                          class="obk-input" 
                          placeholder="Ringkasan singkat tentang isi artikel (maksimal 500 karakter)...">{{ old('excerpt', $article->excerpt) }}</textarea>
            </div>

            <!-- Content -->
            <div>
                <label for="content" class="block text-sm font-bold text-[#042C53] mb-2">Konten Lengkap</label>
                <textarea id="content" 
                          name="content" 
                          rows="10" 
                          class="obk-input font-sans text-sm leading-relaxed" 
                          placeholder="Tuliskan artikel lengkap Anda di sini..." 
                          required>{{ old('content', $article->content) }}</textarea>
            </div>

            <!-- Publish Switch & Action Buttons -->
            <div class="border-t border-gray-100 pt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="relative flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" 
                                   id="is_published" 
                                   name="is_published" 
                                   value="1" 
                                   class="w-5 h-5 text-[#185FA5] border-gray-300 rounded focus:ring-[#185FA5]"
                                   {{ old('is_published', $article->is_published) ? 'checked' : '' }}>
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="is_published" class="font-bold text-[#042C53]">Diterbitkan</label>
                            <p class="text-gray-500 text-xs">Jika dicentang, artikel langsung aktif dan dapat diakses publik.</p>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 justify-end">
                    <a href="{{ route('admin.articles.index') }}" class="obk-btn obk-btn-outline text-sm">
                        Batal
                    </a>
                    <button type="submit" class="obk-btn obk-btn-primary text-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
