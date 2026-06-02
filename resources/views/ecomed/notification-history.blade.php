@extends('layouts.app')
@section('title', 'Riwayat Notifikasi Kedaluwarsa — EcoMed')
@section('header_title', 'EcoMed · Riwayat Notifikasi')

@section('content')

{{-- Page Header --}}
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 animate-fade-in-up">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <a href="{{ route('ecomed.index') }}" class="text-gray-400 hover:text-[#185FA5] text-sm transition-colors">EcoMed</a>
            <span class="text-gray-300">/</span>
            <span class="text-sm font-medium text-[#042C53]">Riwayat Notifikasi</span>
        </div>
        <h1 class="text-2xl font-bold text-[#042C53]">Riwayat Notifikasi Kedaluwarsa</h1>
        <p class="text-gray-500 mt-1">Lacak riwayat pengiriman peringatan obat mendekati atau sudah kedaluwarsa.</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('ecomed.expiry-alerts') }}" class="obk-btn obk-btn-outline text-sm flex items-center gap-2">
            <i data-lucide="bell" class="w-4 h-4"></i> Expiry Alerts
        </a>
    </div>
</div>

{{-- Notification Stats Dashboard --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 animate-fade-in-up" style="animation-delay: 0.05s">
    
    {{-- Card 1: Total Sent --}}
    <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-purple-50 text-[#7F77DD] flex items-center justify-center shrink-0">
            <i data-lucide="send" class="w-6 h-6"></i>
        </div>
        <div>
            <div class="text-2xl font-bold text-[#042C53]">{{ $stats['total_sent'] ?? 0 }}</div>
            <div class="text-xs text-gray-500 font-semibold uppercase tracking-wider mt-0.5">Total Notifikasi Dikirim</div>
        </div>
    </div>

    {{-- Card 2: Last Sent At --}}
    <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#185FA5] flex items-center justify-center shrink-0">
            <i data-lucide="clock" class="w-6 h-6"></i>
        </div>
        <div>
            <div class="text-sm font-bold text-[#042C53]">
                {{ $stats['last_sent_at'] ? \Carbon\Carbon::parse($stats['last_sent_at'])->translatedFormat('d M Y H:i') : '-' }}
            </div>
            <div class="text-xs text-gray-500 font-semibold uppercase tracking-wider mt-1">Pengiriman Terakhir</div>
        </div>
    </div>

    {{-- Card 3: Threshold Breakdown --}}
    <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Berdasarkan Ambang Hari</h4>
        <div class="flex items-center gap-3">
            @foreach([0 => 'Expired', 7 => 'H-7', 30 => 'H-30', 90 => 'H-90'] as $days => $label)
                <div class="flex-1 text-center bg-gray-50 rounded-lg py-1">
                    <span class="block text-xs font-bold text-[#042C53]">{{ $stats['by_threshold'][$days] ?? 0 }}</span>
                    <span class="text-[10px] text-gray-400 font-medium">{{ $label }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Main History Table --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden animate-fade-in-up" style="animation-delay: 0.1s">
    <div class="px-6 py-4 border-b border-gray-100">
        <h3 class="font-bold text-[#042C53] text-lg">Catatan Pengiriman Notifikasi</h3>
    </div>
    
    <div class="overflow-x-auto">
        <table class="obk-table w-full">
            <thead>
                <tr>
                    <th>Obat</th>
                    <th>Tanggal Kedaluwarsa</th>
                    <th>Ambang Batas</th>
                    <th>Saluran (Channel)</th>
                    <th>Terkirim Pada</th>
                    <th>Jadwal Kirim Ulang</th>
                </tr>
            </thead>
            <tbody>
                @forelse($history as $log)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td>
                            @if($log->medicine)
                                <a href="{{ route('medicines.show', $log->medicine_id) }}" class="font-semibold text-[#185FA5] hover:underline">
                                    {{ $log->medicine->medicine_name }}
                                </a>
                                @if($log->medicine->generic_name)
                                    <div class="text-xs text-gray-400">{{ $log->medicine->generic_name }}</div>
                                @endif
                            @else
                                <span class="text-gray-400 italic">Obat Telah Dihapus</span>
                            @endif
                        </td>
                        <td>
                            <span class="font-medium text-gray-700">
                                {{ $log->expiry_date ? $log->expiry_date->format('d M Y') : '-' }}
                            </span>
                        </td>
                        <td>
                            @if($log->days_threshold == 0)
                                <span class="obk-badge obk-badge-danger">KEDALUWARSA</span>
                            @elseif($log->days_threshold == 7)
                                <span class="obk-badge obk-badge-danger">Ambang H-7</span>
                            @elseif($log->days_threshold == 30)
                                <span class="obk-badge obk-badge-warning">Ambang H-30</span>
                            @else
                                <span class="obk-badge obk-badge-primary">Ambang H-90</span>
                            @endif
                        </td>
                        <td>
                            <span class="text-xs font-semibold px-2 py-1 rounded bg-gray-100 text-gray-700 uppercase">
                                {{ $log->channel }}
                            </span>
                        </td>
                        <td>
                            <div class="text-sm text-gray-800 font-medium">
                                {{ $log->sent_at ? $log->sent_at->translatedFormat('d M Y, H:i') : '-' }}
                            </div>
                            <div class="text-xs text-gray-400 mt-0.5">
                                {{ $log->sent_at ? $log->sent_at->diffForHumans() : '-' }}
                            </div>
                        </td>
                        <td>
                            <div class="text-xs text-gray-500 font-medium">
                                {{ $log->resend_after ? $log->resend_after->translatedFormat('d M Y') : 'Hanya sekali' }}
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-gray-500">
                            <i data-lucide="bell-off" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
                            <p class="font-semibold">Belum ada riwayat notifikasi kedaluwarsa.</p>
                            <p class="text-xs text-gray-400 mt-1">Sistem akan mencatat riwayat secara otomatis setelah notifikasi kedaluwarsa dipicu.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($history->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $history->links() }}
        </div>
    @endif
</div>

@endsection
