@props([
    'title',
    'value',
    'subtitle' => null,
    'icon' => null, // e.g. 'pill', 'calendar', etc for lucide icons
    'color' => 'primary', // primary, success, warning, danger
    'trend' => null, // 'up', 'down', 'neutral'
    'trendValue' => null
])

@php
    $colors = [
        'primary' => [
            'bg' => 'bg-white',
            'text' => 'text-[#185FA5]',
            'icon_bg' => 'bg-[#185FA5]/10',
            'border' => 'border-gray-100',
        ],
        'success' => [
            'bg' => 'bg-white',
            'text' => 'text-[#1D9E75]',
            'icon_bg' => 'bg-[#1D9E75]/10',
            'border' => 'border-gray-100',
        ],
        'warning' => [
            'bg' => 'bg-white',
            'text' => 'text-[#EF9F27]',
            'icon_bg' => 'bg-[#EF9F27]/10',
            'border' => 'border-gray-100',
        ],
        'danger' => [
            'bg' => 'bg-white',
            'text' => 'text-[#E24B4A]',
            'icon_bg' => 'bg-[#E24B4A]/10',
            'border' => 'border-gray-100',
        ],
    ];

    $colorSet = $colors[$color] ?? $colors['primary'];
@endphp

<div class="{{ $colorSet['bg'] }} {{ $colorSet['border'] }} border rounded-xl p-6 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 group">
    <div class="flex items-start justify-between">
        <div class="flex-1">
            <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2 group-hover:text-gray-700 transition-colors">{{ $title }}</h4>
            <div class="text-3xl font-extrabold text-[#042C53] tracking-tight">{{ $value }}</div>
        </div>
        
        @if($icon)
        <div class="w-14 h-14 rounded-xl {{ $colorSet['icon_bg'] }} {{ $colorSet['text'] }} flex items-center justify-center flex-shrink-0 transition-transform duration-300 group-hover:scale-110">
            <i data-lucide="{{ $icon }}" class="w-7 h-7"></i>
        </div>
        @endif
    </div>

    @if($subtitle || $trend)
    <div class="mt-5 flex items-center text-sm">
        @if($trend)
            @if($trend === 'up')
                <span class="flex items-center text-[#1D9E75] font-semibold bg-[#1D9E75]/10 px-2 py-0.5 rounded-md">
                    <i data-lucide="trending-up" class="w-4 h-4 mr-1"></i>
                    {{ $trendValue }}
                </span>
            @elseif($trend === 'down')
                <span class="flex items-center text-[#E24B4A] font-semibold bg-[#E24B4A]/10 px-2 py-0.5 rounded-md">
                    <i data-lucide="trending-down" class="w-4 h-4 mr-1"></i>
                    {{ $trendValue }}
                </span>
            @elseif($trend === 'neutral')
                <span class="flex items-center text-gray-500 font-semibold bg-gray-100 px-2 py-0.5 rounded-md">
                    <i data-lucide="minus" class="w-4 h-4 mr-1"></i>
                    {{ $trendValue }}
                </span>
            @endif
        @endif
        
        @if($subtitle)
            <span class="text-gray-500 font-medium {{ $trend ? 'ml-3' : '' }}">{{ $subtitle }}</span>
        @endif
    </div>
    @endif
</div>
