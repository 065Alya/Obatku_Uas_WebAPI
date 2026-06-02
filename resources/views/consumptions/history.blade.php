@extends('layouts.app')
@section('title', 'Riwayat Konsumsi Obat')

@section('content')
<div class="mb-8 animate-fade-in-up">
    <h1 class="text-3xl font-bold text-[#042C53]">Riwayat Konsumsi</h1>
    <p class="text-gray-500 mt-1">Lacak dan pantau kepatuhan minum obat Anda.</p>
</div>

{{-- Top Stats / Overview --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 animate-fade-in-up" style="animation-delay: 0.1s">
    <div class="bg-gradient-to-br from-[#185FA5] to-[#042C53] rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
        <div class="relative z-10">
            <p class="text-white/80 text-sm font-medium mb-1">Tingkat Kepatuhan ({{ \Carbon\Carbon::parse($from)->format('d M') }} - {{ \Carbon\Carbon::parse($to)->format('d M') }})</p>
            <h2 class="text-4xl font-bold">{{ $adherenceRate }}%</h2>
        </div>
        <i data-lucide="activity" class="w-24 h-24 absolute -bottom-4 -right-4 text-white/10"></i>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6 animate-fade-in-up" style="animation-delay: 0.2s">
    <form action="{{ route('consumptions.history') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
        
        <div class="w-full md:w-1/4">
            <label class="block text-xs font-semibold text-gray-500 mb-1">Dari Tanggal</label>
            <input type="date" name="from" value="{{ $from }}" class="obk-input text-sm">
        </div>
        
        <div class="w-full md:w-1/4">
            <label class="block text-xs font-semibold text-gray-500 mb-1">Sampai Tanggal</label>
            <input type="date" name="to" value="{{ $to }}" class="obk-input text-sm">
        </div>

        <div class="w-full md:w-1/4">
            <label class="block text-xs font-semibold text-gray-500 mb-1">Status</label>
            <select name="status" class="obk-input text-sm">
                <option value="">Semua Status</option>
                <option value="taken" {{ request('status') === 'taken' ? 'selected' : '' }}>Diminum</option>
                <option value="skipped" {{ request('status') === 'skipped' ? 'selected' : '' }}>Dilewati</option>
                <option value="missed" {{ request('status') === 'missed' ? 'selected' : '' }}>Terlewat</option>
            </select>
        </div>

        <div class="w-full md:w-1/4">
            <label class="block text-xs font-semibold text-gray-500 mb-1">Cari Obat</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama obat..." class="obk-input text-sm">
        </div>

        <div class="w-full md:w-auto flex gap-2">
            <button type="submit" class="obk-btn obk-btn-primary w-full md:w-auto">
                <i data-lucide="filter" class="w-4 h-4"></i> Filter
            </button>
            @if(request()->hasAny(['from', 'to', 'status', 'search']))
                <a href="{{ route('consumptions.history') }}" class="obk-btn obk-btn-outline w-full md:w-auto px-3 text-gray-500" title="Reset">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </a>
            @endif
        </div>
    </form>
</div>

{{-- History List --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden animate-fade-in-up" style="animation-delay: 0.3s">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50 border-b border-gray-100">
                    <th class="p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal & Waktu</th>
                    <th class="p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Obat</th>
                    <th class="p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Dosis / Catatan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($consumptions as $log)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="p-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-[#042C53]">{{ \Carbon\Carbon::parse($log->consumed_at)->translatedFormat('d M Y') }}</div>
                            <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($log->consumed_at)->format('H:i') }}</div>
                        </td>
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-[#F0F6FF] flex items-center justify-center shrink-0">
                                    <i data-lucide="pill" class="w-5 h-5 text-[#185FA5]"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-[#042C53]">{{ $log->medicine->medicine_name ?? 'Obat Dihapus' }}</div>
                                    @if($log->schedule)
                                        <div class="text-xs text-gray-400">Jadwal: {{ $log->schedule->schedule_time }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="p-4">
                            @if($log->status === 'taken')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#E1F5EE] text-[#085538] border border-[#1D9E75]/20">
                                    <i data-lucide="check-circle-2" class="w-3 h-3"></i> Diminum
                                </span>
                            @elseif($log->status === 'skipped')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#FFF4E5] text-[#9E650D] border border-[#EF9F27]/20">
                                    <i data-lucide="skip-forward" class="w-3 h-3"></i> Dilewati
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#FDEDED] text-[#891C1B] border border-[#E24B4A]/20">
                                    <i data-lucide="x-circle" class="w-3 h-3"></i> Terlewat
                                </span>
                            @endif
                        </td>
                        <td class="p-4">
                            <div class="text-sm text-gray-700">{{ $log->dosage_taken ?? '-' }}</div>
                            @if($log->notes)
                                <div class="text-xs text-gray-500 mt-1 flex items-start gap-1">
                                    <i data-lucide="message-square" class="w-3 h-3 shrink-0 mt-0.5"></i> {{ $log->notes }}
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-10 text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 mb-4">
                                <i data-lucide="clipboard-list" class="w-8 h-8 text-gray-400"></i>
                            </div>
                            <h3 class="text-lg font-bold text-[#042C53] mb-1">Belum Ada Riwayat</h3>
                            <p class="text-gray-500 text-sm">Tidak ada catatan konsumsi obat untuk filter saat ini.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile Card Layout --}}
    <div class="md:hidden block divide-y divide-gray-100 border-t border-gray-100">
        @foreach($consumptions as $log)
            <div class="p-4">
                <div class="flex justify-between items-start mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#F0F6FF] flex items-center justify-center shrink-0">
                            <i data-lucide="pill" class="w-5 h-5 text-[#185FA5]"></i>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-[#042C53]">{{ $log->medicine->medicine_name ?? 'Obat Dihapus' }}</div>
                            <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($log->consumed_at)->translatedFormat('d M Y, H:i') }}</div>
                        </div>
                    </div>
                    @if($log->status === 'taken')
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#E1F5EE] text-[#085538]">
                            <i data-lucide="check-circle-2" class="w-3 h-3"></i> Diminum
                        </span>
                    @elseif($log->status === 'skipped')
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#FFF4E5] text-[#9E650D]">
                            <i data-lucide="skip-forward" class="w-3 h-3"></i> Dilewati
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#FDEDED] text-[#891C1B]">
                            <i data-lucide="x-circle" class="w-3 h-3"></i> Terlewat
                        </span>
                    @endif
                </div>
                @if($log->notes || $log->dosage_taken)
                    <div class="bg-gray-50 rounded-lg p-3 text-sm text-gray-600 mt-2">
                        @if($log->dosage_taken)
                            <p><strong>Dosis:</strong> {{ $log->dosage_taken }}</p>
                        @endif
                        @if($log->notes)
                            <p class="mt-1 flex items-start gap-1 text-xs"><i data-lucide="message-square" class="w-3 h-3 shrink-0 mt-0.5"></i> {{ $log->notes }}</p>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    @if($consumptions->hasPages())
        <div class="p-4 border-t border-gray-100 bg-gray-50/30">
            {{ $consumptions->links() }}
        </div>
    @endif
</div>
@endsection
