@extends('layouts.app')
@section('title', 'Pengaturan — ObatKu')

@section('content')

{{-- Header --}}
<div class="mb-8 animate-fade-in-up">
    <a href="{{ route('profile.index') }}"
       class="inline-flex items-center gap-2 text-sm font-semibold text-[#185FA5] hover:text-[#042C53] mb-3 transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Profil
    </a>
    <h1 class="text-3xl font-bold text-[#042C53]">Pengaturan</h1>
    <p class="text-gray-500 mt-1">Kelola preferensi notifikasi dan aksesibilitas Anda.</p>
</div>

{{-- Flash --}}
@if(session('success'))
    <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-100 flex items-center gap-3 animate-fade-in-up">
        <i data-lucide="check-circle-2" class="w-5 h-5 text-green-600 shrink-0"></i>
        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
    </div>
@endif

<form action="{{ route('profile.settings.update') }}" method="POST" class="animate-fade-in-up" style="animation-delay: 0.05s">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- ── Left Nav ────────────────────────────────────────────────── --}}
        <div class="space-y-4">
            {{-- User Card --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 text-center">
                <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=185FA5&color=fff&size=96&rounded=true&bold=true' }}"
                     alt="Avatar" class="w-16 h-16 rounded-full mx-auto mb-2 border-2 border-white shadow object-cover">
                <p class="font-bold text-[#042C53]">{{ $user->name }}</p>
                <p class="text-xs text-gray-400">{{ $user->email }}</p>
            </div>

            {{-- Quick Links --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 divide-y divide-gray-50 overflow-hidden">
                <a href="{{ route('profile.index') }}"
                   class="flex items-center gap-3 p-4 hover:bg-gray-50 transition-colors text-sm text-gray-600">
                    <i data-lucide="user" class="w-4 h-4 text-[#185FA5]"></i> Profil Akun
                </a>
                <a href="{{ route('personal.index') }}"
                   class="flex items-center gap-3 p-4 hover:bg-gray-50 transition-colors text-sm text-gray-600">
                    <i data-lucide="user-circle" class="w-4 h-4 text-[#185FA5]"></i> Mode Personal
                </a>
                <a href="{{ route('profile.settings') }}"
                   class="flex items-center gap-3 p-4 bg-[#F0F6FF] text-sm font-bold text-[#185FA5]">
                    <i data-lucide="settings" class="w-4 h-4"></i> Pengaturan
                </a>
            </div>
        </div>

        {{-- ── Right Settings ──────────────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- ── Notification Settings ─────────────────────────────── --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-[#042C53] border-b border-gray-50 pb-3 mb-5 flex items-center gap-2">
                    <i data-lucide="bell" class="w-5 h-5 text-[#185FA5]"></i>
                    Preferensi Notifikasi
                </h3>

                <div class="space-y-4">
                    @php
                        $toggles = [
                            ['key' => 'notify_stock_alert',        'label' => 'Peringatan Stok Menipis',
                             'desc' => 'Terima notifikasi saat stok obat hampir habis.',
                             'icon' => 'alert-circle', 'color' => 'text-[#E24B4A]'],
                            ['key' => 'notify_expiry_alert',       'label' => 'Peringatan Kedaluwarsa',
                             'desc' => 'Terima notifikasi saat obat mendekati tanggal kedaluwarsa.',
                             'icon' => 'calendar-x', 'color' => 'text-[#EF9F27]'],
                            ['key' => 'notify_interaction_alert',  'label' => 'Peringatan Interaksi Obat',
                             'desc' => 'Terima notifikasi saat terdeteksi potensi interaksi berbahaya.',
                             'icon' => 'git-compare', 'color' => 'text-[#7F77DD]'],
                            ['key' => 'notify_schedule_reminder',  'label' => 'Pengingat Jadwal Konsumsi',
                             'desc' => 'Terima pengingat sesuai jadwal minum obat Anda.',
                             'icon' => 'clock', 'color' => 'text-[#185FA5]'],
                        ];
                    @endphp

                    @foreach($toggles as $toggle)
                        <div class="flex items-center justify-between p-4 rounded-xl bg-gray-50 border border-gray-100">
                            <div class="flex items-start gap-3">
                                <i data-lucide="{{ $toggle['icon'] }}" class="w-5 h-5 {{ $toggle['color'] }} mt-0.5 shrink-0"></i>
                                <div>
                                    <p class="text-sm font-semibold text-[#042C53]">{{ $toggle['label'] }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $toggle['desc'] }}</p>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer shrink-0 ml-3">
                                <input type="checkbox"
                                       name="{{ $toggle['key'] }}"
                                       value="1"
                                       class="sr-only peer"
                                       id="{{ $toggle['key'] }}"
                                       {{ $prefs[$toggle['key']] ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer
                                            peer-checked:after:translate-x-full peer-checked:after:border-white
                                            after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                            after:bg-white after:border-gray-300 after:border after:rounded-full
                                            after:h-5 after:w-5 after:transition-all
                                            peer-checked:bg-[#185FA5]">
                                </div>
                            </label>
                        </div>
                    @endforeach
                </div>

                {{-- Delivery Channels --}}
                <div class="mt-5 pt-5 border-t border-gray-50">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">Saluran Pengiriman</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="flex items-center justify-between p-4 rounded-xl bg-gray-50 border border-gray-100">
                            <div class="flex items-center gap-3">
                                <i data-lucide="bell-ring" class="w-5 h-5 text-[#185FA5]"></i>
                                <div>
                                    <p class="text-sm font-semibold text-[#042C53]">Push Notification</p>
                                    <p class="text-xs text-gray-400">Notifikasi di browser</p>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer shrink-0 ml-3">
                                <input type="checkbox" name="notify_push" value="1" class="sr-only peer"
                                       {{ $prefs['notify_push'] ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-[#185FA5]
                                            after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                            after:bg-white after:border-gray-300 after:border after:rounded-full
                                            after:h-5 after:w-5 after:transition-all
                                            peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                            </label>
                        </div>
                        <div class="flex items-center justify-between p-4 rounded-xl bg-gray-50 border border-gray-100 opacity-60">
                            <div class="flex items-center gap-3">
                                <i data-lucide="message-square" class="w-5 h-5 text-gray-400"></i>
                                <div>
                                    <p class="text-sm font-semibold text-gray-500">SMS/WhatsApp</p>
                                    <p class="text-xs text-gray-400">Segera hadir</p>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-not-allowed shrink-0 ml-3">
                                <input type="checkbox" name="notify_sms" value="1" class="sr-only peer" disabled>
                                <div class="w-11 h-6 bg-gray-200 rounded-full after:content-[''] after:absolute
                                            after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300
                                            after:border after:rounded-full after:h-5 after:w-5"></div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Accessibility Settings ─────────────────────────────── --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-[#042C53] border-b border-gray-50 pb-3 mb-5 flex items-center gap-2">
                    <i data-lucide="zoom-in" class="w-5 h-5 text-[#1D9E75]"></i>
                    Aksesibilitas
                </h3>

                <div class="flex items-center justify-between p-4 rounded-xl bg-[#E1F5EE] border border-[#1D9E75]/20">
                    <div class="flex items-start gap-3">
                        <i data-lucide="type" class="w-5 h-5 text-[#1D9E75] mt-0.5 shrink-0"></i>
                        <div>
                            <p class="text-sm font-semibold text-[#085538]">Mode Huruf Besar</p>
                            <p class="text-xs text-[#1D9E75] mt-0.5">Membesarkan ukuran teks di seluruh aplikasi untuk kemudahan baca lansia.</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer shrink-0 ml-3">
                        <input type="checkbox"
                               name="accessibility_large_font"
                               value="1"
                               id="accessibility_large_font"
                               class="sr-only peer"
                               {{ $prefs['accessibility_large_font'] ? 'checked' : '' }}
                               onchange="previewFontSize(this.checked)">
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-[#1D9E75]
                                    after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                    after:bg-white after:border-gray-300 after:border after:rounded-full
                                    after:h-5 after:w-5 after:transition-all
                                    peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                    </label>
                </div>

                <div id="font-preview" class="mt-4 p-4 rounded-xl bg-gray-50 border border-gray-100 transition-all">
                    <p class="text-[#042C53] font-semibold" id="preview-text">Contoh tampilan teks normal ObatKu.</p>
                    <p class="text-gray-400 text-sm mt-1" id="preview-sub">Pastikan semua obat diminum sesuai jadwal.</p>
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('profile.index') }}" class="obk-btn obk-btn-outline">Batal</a>
                <button type="submit" class="obk-btn obk-btn-primary px-8">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Simpan Pengaturan
                </button>
            </div>

        </div>
    </div>
</form>

@push('scripts')
<script>
function previewFontSize(large) {
    const preview  = document.getElementById('preview-text');
    const previewSub = document.getElementById('preview-sub');
    if (large) {
        preview.style.fontSize    = '1.125rem';
        previewSub.style.fontSize = '0.9375rem';
    } else {
        preview.style.fontSize    = '';
        previewSub.style.fontSize = '';
    }
}

// Apply on page load if already enabled
document.addEventListener('DOMContentLoaded', () => {
    const cb = document.getElementById('accessibility_large_font');
    if (cb && cb.checked) previewFontSize(true);
});
</script>
@endpush

@endsection
