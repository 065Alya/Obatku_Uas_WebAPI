{{-- ─── Medicine Card Component ───
     Usage: @include('components.medicine-card', ['medicine' => $medicine])
--}}

<div class="obk-card group">
    <div class="flex items-start justify-between">
        <div class="flex items-start gap-3">
            {{-- Medicine Icon --}}
            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0 group-hover:bg-[#185FA5] transition-colors">
                <i data-lucide="pill" class="w-6 h-6 text-[#185FA5] group-hover:text-white transition-colors"></i>
            </div>
            <div>
                <h3 class="font-bold text-[#042C53]">{{ $medicine->name }}</h3>
                @if($medicine->generic_name)
                    <p class="text-sm text-gray-500">{{ $medicine->generic_name }}</p>
                @endif
                @if($medicine->category)
                    <span class="obk-badge obk-badge-primary mt-1">{{ $medicine->category->name }}</span>
                @endif
            </div>
        </div>

        {{-- Stock Badge --}}
        <div class="text-right">
            @if($medicine->isExpired())
                <span class="obk-badge obk-badge-danger">Kedaluwarsa</span>
            @elseif($medicine->isLowStock())
                <span class="obk-badge obk-badge-warning">Stok Rendah</span>
            @elseif($medicine->isExpiringSoon())
                <span class="obk-badge obk-badge-warning">Segera Exp</span>
            @else
                <span class="obk-badge obk-badge-success">Aktif</span>
            @endif
        </div>
    </div>

    {{-- Details --}}
    <div class="grid grid-cols-3 gap-4 mt-4 pt-4 border-t border-gray-50">
        <div>
            <p class="text-xs text-gray-400 uppercase tracking-wide">Stok</p>
            <p class="text-sm font-semibold text-[#042C53]">{{ $medicine->stock }} {{ $medicine->unit }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-400 uppercase tracking-wide">Dosis</p>
            <p class="text-sm font-semibold text-[#042C53]">{{ $medicine->dosage ?? '-' }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-400 uppercase tracking-wide">Exp</p>
            <p class="text-sm font-semibold {{ $medicine->isExpired() ? 'text-[#E24B4A]' : ($medicine->isExpiringSoon() ? 'text-[#EF9F27]' : 'text-[#042C53]') }}">
                {{ $medicine->expiry_date ? $medicine->expiry_date->format('d M Y') : '-' }}
            </p>
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex items-center gap-2 mt-4 pt-3 border-t border-gray-50">
        <a href="{{ route('medicines.show', $medicine->id) }}" class="obk-btn obk-btn-outline text-xs py-2 px-3 flex-1">
            <i data-lucide="eye" class="w-4 h-4"></i> Detail
        </a>
        <a href="{{ route('medicines.edit', $medicine->id) }}" class="obk-btn obk-btn-primary text-xs py-2 px-3 flex-1">
            <i data-lucide="pencil" class="w-4 h-4"></i> Edit
        </a>
    </div>
</div>
