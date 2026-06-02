@extends('layouts.app')

@section('title', 'Data Obat')
@section('header', 'Manajemen Obat')
@section('subheader', 'Kelola daftar obat, stok, dan tanggal kedaluwarsa (EcoMed).')

@section('content')

    <!-- Top Action Bar -->
    <div class="flex flex-col gap-4 mb-6 md:flex-row md:items-center md:justify-between">
        
        <!-- Search and Filter -->
        <!-- Search and Filter -->
        <form action="{{ route('medicines.index') }}" method="GET" class="flex flex-col flex-1 gap-3 sm:flex-row">
            <div class="relative w-full sm:max-w-xs">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i data-lucide="search" class="w-5 h-5 text-gray-400"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="block w-full py-2.5 pl-10 pr-3 text-sm border border-gray-200 rounded-xl focus:ring-[#185FA5] focus:border-[#185FA5] bg-white transition-colors placeholder-gray-400" 
                       placeholder="Cari nama obat..." onchange="this.form.submit()">
            </div>

            <div class="relative w-full sm:w-48">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i data-lucide="filter" class="w-4 h-4 text-gray-400"></i>
                </div>
                <select name="status" onchange="this.form.submit()" class="block w-full py-2.5 pl-10 pr-10 text-sm border border-gray-200 rounded-xl focus:ring-[#185FA5] focus:border-[#185FA5] bg-white appearance-none cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="low_stock" {{ request('status') === 'low_stock' ? 'selected' : '' }}>Stok Menipis</option>
                    <option value="expiring_soon" {{ request('status') === 'expiring_soon' ? 'selected' : '' }}>Hampir Kedaluwarsa</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Telah Kedaluwarsa</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                    <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400"></i>
                </div>
            </div>
        </form>

        <!-- Add Button -->
        <a href="{{ route('medicines.create') }}" class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-semibold text-white transition-all bg-[#185FA5] rounded-xl hover:bg-[#042C53] hover:shadow-md focus:ring-2 focus:ring-offset-2 focus:ring-[#185FA5]">
            <i data-lucide="plus" class="w-5 h-5 mr-2 -ml-1"></i>
            Tambah Obat
        </a>
    </div>

    <!-- Table Container -->
    <div class="bg-white border border-gray-100 rounded-xl shadow-[0_4px_20px_rgb(0,0,0,0.03)] overflow-hidden">
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600 whitespace-nowrap">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50/50 border-b border-gray-100">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Nama Obat</th>
                        <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Bentuk</th>
                        <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Stok</th>
                        <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Kedaluwarsa</th>
                        <th scope="col" class="px-6 py-4 font-semibold tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($medicines as $medicine)
                    @php
                        $isExpired = $medicine->expiry_date && $medicine->expiry_date < now();
                        $isLowStock = $medicine->stock <= $medicine->stock_alert;
                        $rowClass = $isExpired ? 'bg-red-50/30 hover:bg-red-50' : 'hover:bg-blue-50/50';
                    @endphp
                    <tr class="transition-colors {{ $rowClass }} group">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center w-10 h-10 rounded-lg {{ $isExpired ? 'bg-red-100 text-[#E24B4A]' : ($isLowStock ? 'bg-orange-100 text-[#EF9F27]' : 'bg-blue-100 text-[#185FA5]') }}">
                                    <i data-lucide="{{ $isExpired ? 'alert-triangle' : 'pill' }}" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900">{{ $medicine->medicine_name }}</p>
                                    <p class="text-xs text-gray-500">{{ $medicine->generic_name ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            {{ ucfirst($medicine->form ?? 'Tablet') }}
                        </td>
                        <td class="px-6 py-4">
                            @if($isLowStock)
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold text-amber-800 bg-amber-100 rounded-md border border-amber-200">
                                <i data-lucide="alert-triangle" class="w-3.5 h-3.5 mr-1.5"></i>
                                Sisa {{ $medicine->stock }} {{ ucfirst($medicine->unit ?? 'Tablet') }}
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-emerald-800 bg-emerald-100 rounded-md">
                                {{ $medicine->stock }} {{ ucfirst($medicine->unit ?? 'Tablet') }}
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($isExpired)
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold text-red-700 bg-red-100 rounded-md border border-red-200">
                                <i data-lucide="alert-circle" class="w-3.5 h-3.5 mr-1.5"></i>
                                Kedaluwarsa (SDG 12)
                            </span>
                            @elseif($medicine->expiry_date)
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-gray-700 bg-gray-100 rounded-md">
                                <i data-lucide="calendar" class="w-3.5 h-3.5 mr-1.5"></i>
                                {{ $medicine->expiry_date->format('d M Y') }}
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-gray-500 bg-gray-50 rounded-md">
                                Tidak ada data
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="{{ route('medicines.show', $medicine->id) }}" class="p-2 text-gray-400 hover:text-[#185FA5] transition-colors rounded-lg hover:bg-blue-50" title="Detail">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                @can('update', $medicine)
                                <a href="{{ route('medicines.edit', $medicine->id) }}" class="p-2 text-gray-400 hover:text-amber-500 transition-colors rounded-lg hover:bg-amber-50" title="Edit">
                                    <i data-lucide="edit-2" class="w-4 h-4"></i>
                                </a>
                                @endcan
                                @can('delete', $medicine)
                                <form action="{{ route('medicines.destroy', $medicine->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus obat ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-gray-400 hover:text-red-500 transition-colors rounded-lg hover:bg-red-50" title="Hapus">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                    <i data-lucide="box" class="w-8 h-8 text-gray-400"></i>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 mb-1">Belum Ada Obat</h3>
                                <p class="text-sm text-gray-500 max-w-sm">Anda belum menambahkan obat apa pun, atau tidak ada yang cocok dengan pencarian Anda.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

        <!-- Pagination Footer -->
        @if($medicines->hasPages())
        <div class="px-6 py-4 bg-white border-t border-gray-100">
            {{ $medicines->links() }}
        </div>
        @endif
    </div>

@endsection
