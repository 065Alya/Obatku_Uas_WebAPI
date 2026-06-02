@extends('layouts.app')

@section('title', 'Kelola Pengguna — Admin')

@section('content')
    <!-- Header -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 animate-fade-in-up">
        <div>
            <h1 class="text-2xl font-bold text-[#042C53]">Kelola Pengguna</h1>
            <p class="text-gray-500 mt-1">Pantau dan kelola seluruh akun pengguna ObatKu di platform ini.</p>
        </div>
        <div>
            <a href="{{ route('admin.dashboard') }}" class="obk-btn obk-btn-outline flex items-center gap-2 text-sm bg-white">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Kembali ke Dashboard
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

    <!-- Table Section -->
    <div class="bg-white border border-gray-100 rounded-xl shadow-sm overflow-hidden animate-fade-in-up" style="animation-delay: 0.05s">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600 whitespace-nowrap">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50/50 border-b border-gray-100">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Nama & Email</th>
                        <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Telepon</th>
                        <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Peran</th>
                        <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Terdaftar Pada</th>
                        <th scope="col" class="px-6 py-4 font-semibold tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($users as $user)
                        <tr class="transition-colors hover:bg-blue-50/20 group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-blue-50 text-[#185FA5] flex items-center justify-center font-bold text-sm">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                {{ $user->phone ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                @if($user->role === 'admin')
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold text-[#7F77DD] bg-purple-50 rounded-md border border-purple-100">
                                        Admin
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-gray-600 bg-gray-100 rounded-md">
                                        User
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($user->is_active)
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold text-[#1D9E75] bg-[#1D9E75]/10 rounded-md">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#1D9E75] mr-1.5"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold text-[#E24B4A] bg-[#E24B4A]/10 rounded-md">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#E24B4A] mr-1.5"></span>
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-500 text-xs">
                                {{ $user->created_at->format('d M Y, H:i') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.users.show', $user->id) }}" class="p-2 text-gray-400 hover:text-[#185FA5] transition-colors rounded-lg hover:bg-blue-50" title="Detail Pengguna">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                    @if(auth()->id() !== $user->id)
                                        <form action="{{ route('admin.users.toggle-status', $user->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="p-2 transition-colors rounded-lg {{ $user->is_active ? 'text-amber-600 hover:text-amber-700 hover:bg-amber-50' : 'text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50' }}" 
                                                    title="{{ $user->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}">
                                                <i data-lucide="{{ $user->is_active ? 'user-x' : 'user-check' }}" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                Belum ada data pengguna terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-6 py-4 bg-white border-t border-gray-100">
                {{ $users->links() }}
            </div>
        @endif
    </div>
@endsection
