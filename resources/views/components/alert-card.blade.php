{{-- ─── Alert Card Component ───
     Usage: @include('components.alert-card', [
         'type' => 'warning', // success, warning, danger, info
         'icon' => 'alert-triangle',
         'title' => 'Stok Rendah',
         'message' => '5 obat perlu ditambah stoknya.',
         'action' => route('medicines.index'),
         'actionLabel' => 'Lihat Detail',
     ])
--}}

@php
    $typeClasses = [
        'success' => 'border-l-[#1D9E75] bg-[#e6f7f1]',
        'warning' => 'border-l-[#EF9F27] bg-[#fef5e6]',
        'danger' => 'border-l-[#E24B4A] bg-[#fde9e9]',
        'info' => 'border-l-[#185FA5] bg-[#e8f0fa]',
    ];
    $iconColors = [
        'success' => 'text-[#1D9E75]',
        'warning' => 'text-[#EF9F27]',
        'danger' => 'text-[#E24B4A]',
        'info' => 'text-[#185FA5]',
    ];
@endphp

<div class="flex items-start gap-3 p-4 rounded-xl border-l-4 {{ $typeClasses[$type ?? 'info'] }} animate-fade-in-up">
    <i data-lucide="{{ $icon ?? 'info' }}" class="w-5 h-5 flex-shrink-0 mt-0.5 {{ $iconColors[$type ?? 'info'] }}"></i>
    <div class="flex-1">
        <p class="font-semibold text-sm text-gray-800">{{ $title ?? '' }}</p>
        <p class="text-sm text-gray-600 mt-0.5">{{ $message ?? '' }}</p>
    </div>
    @if(isset($action))
        <a href="{{ $action }}" class="text-sm font-medium {{ $iconColors[$type ?? 'info'] }} hover:underline whitespace-nowrap">
            {{ $actionLabel ?? 'Lihat' }} →
        </a>
    @endif
</div>
