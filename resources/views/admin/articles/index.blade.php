@extends('layouts.app')

@section('title', 'Kelola Artikel — Admin')

@section('content')
    <!-- Header -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 animate-fade-in-up">
        <div>
            <h1 class="text-2xl font-bold text-[#042C53]">Kelola Artikel Edukasi</h1>
            <p class="text-gray-500 mt-1">Tulis, terbitkan, dan kelola konten literasi obat untuk mencerdaskan masyarakat.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.articles.create') }}" class="obk-btn obk-btn-primary text-sm flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Tulis Artikel Baru
            </a>
        </div>
    </div>

    <!-- Alert Flash Message -->
    @if(session('success'))
        <div class="mb-6 obk-alert obk-alert-success animate-fade-in-up" data-flash>
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    <!-- Articles Table Box -->
    <div class="bg-white border border-gray-100 rounded-xl shadow-sm overflow-hidden animate-fade-in-up" style="animation-delay: 0.05s">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600 whitespace-nowrap">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50/50 border-b border-gray-100">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Judul & Kategori</th>
                        <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Penulis</th>
                        <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Dibaca</th>
                        <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Diterbitkan Pada</th>
                        <th scope="col" class="px-6 py-4 font-semibold tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($articles as $art)
                        <tr class="transition-colors hover:bg-blue-50/20 group">
                            <td class="px-6 py-4">
                                <div class="max-w-md truncate">
                                    <p class="font-bold text-gray-900 truncate" title="{{ $art->title }}">{{ $art->title }}</p>
                                    <span class="inline-block mt-1 px-2 py-0.5 text-[10px] font-bold text-[#7F77DD] bg-purple-50 rounded border border-purple-100 uppercase">
                                        {{ $art->category }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-500 text-sm">
                                {{ $art->author->name ?? 'Admin' }}
                            </td>
                            <td class="px-6 py-4 text-gray-500 text-sm">
                                <span class="flex items-center gap-1">
                                    <i data-lucide="eye" class="w-4 h-4 text-gray-400"></i>
                                    {{ $art->views_count }} x
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($art->is_published)
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold text-[#1D9E75] bg-[#1D9E75]/10 rounded-md">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#1D9E75] mr-1.5"></span>
                                        Diterbitkan
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold text-gray-500 bg-gray-100 rounded-md">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400 mr-1.5"></span>
                                        Draft
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-500 text-xs">
                                {{ $art->published_at ? $art->published_at->format('d M Y, H:i') : '–' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('articles.show', $art->slug) }}" target="_blank" class="p-2 text-gray-400 hover:text-[#185FA5] transition-colors rounded-lg hover:bg-blue-50" title="Pratinjau Artikel">
                                        <i data-lucide="external-link" class="w-4 h-4"></i>
                                    </a>
                                    <a href="{{ route('admin.articles.edit', $art->id) }}" class="p-2 text-gray-400 hover:text-[#EF9F27] transition-colors rounded-lg hover:bg-amber-50" title="Ubah Artikel">
                                        <i data-lucide="edit-2" class="w-4 h-4"></i>
                                    </a>
                                    <form action="{{ route('admin.articles.destroy', $art->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus artikel ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-gray-400 hover:text-[#E24B4A] transition-colors rounded-lg hover:bg-red-50" title="Hapus Artikel">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                Belum ada artikel ditulis. Mulai tulis artikel pertama Anda!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($articles->hasPages())
            <div class="px-6 py-4 bg-white border-t border-gray-100">
                {{ $articles->links() }}
            </div>
        @endif
    </div>
@endsection
