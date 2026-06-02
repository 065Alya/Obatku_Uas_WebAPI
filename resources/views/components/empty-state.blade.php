{{-- ─── Empty State Component ───
     Usage: @include('components.empty-state', [
         'icon' => 'inbox',
         'title' => 'Belum ada data',
         'description' => 'Mulai dengan menambahkan item baru.',
         'action' => route('medicines.create'),
         'actionLabel' => 'Tambah Obat',
     ])
--}}

<div class="flex flex-col items-center justify-center py-16 px-6 text-center">
    <div class="w-20 h-20 bg-blue-50 rounded-2xl flex items-center justify-center mb-6">
        <i data-lucide="{{ $icon ?? 'inbox' }}" class="w-10 h-10 text-[#185FA5]/50"></i>
    </div>
    <h3 class="text-lg font-bold text-[#042C53]">{{ $title ?? 'Belum ada data' }}</h3>
    <p class="text-sm text-gray-500 mt-2 max-w-sm">{{ $description ?? 'Data yang Anda cari belum tersedia.' }}</p>
    @if(isset($action))
        <a href="{{ $action }}" class="obk-btn obk-btn-primary mt-6">
            <i data-lucide="plus" class="w-4 h-4"></i>
            {{ $actionLabel ?? 'Tambah Baru' }}
        </a>
    @endif
</div>
