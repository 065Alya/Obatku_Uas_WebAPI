<table class="obk-table w-full">
    <thead>
        <tr>
            <th>Nama Obat</th>
            <th>Bentuk</th>
            <th>Tgl Kedaluwarsa</th>
            <th>Sisa Hari</th>
            <th>Stok</th>
            <th>Untuk</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @foreach($medicines as $med)
        @php
            $daysLeft = now()->diffInDays($med->expiry_date, false);
            $isExpired = $daysLeft < 0;
            $urgencyColor = $urgency === 'urgent' ? 'text-[#EF9F27]' : ($urgency === 'warning' ? 'text-[#185FA5]' : 'text-[#1D9E75]');
        @endphp
        <tr class="hover:bg-gray-50/50 transition-colors">
            <td>
                <div class="font-semibold text-[#042C53]">{{ $med->medicine_name }}</div>
                @if($med->generic_name)<div class="text-xs text-gray-400">{{ $med->generic_name }}</div>@endif
            </td>
            <td>
                <span class="obk-badge obk-badge-primary capitalize">{{ $med->form ?? 'tablet' }}</span>
            </td>
            <td>
                <span class="font-semibold text-gray-700">{{ $med->expiry_date->format('d M Y') }}</span>
            </td>
            <td>
                <span class="font-bold {{ $urgencyColor }}">
                    {{ $isExpired ? 'Kedaluwarsa' : ($daysLeft == 0 ? 'Hari ini' : $daysLeft . ' hari') }}
                </span>
            </td>
            <td class="font-medium text-gray-700">{{ $med->stock }} {{ $med->unit }}</td>
            <td class="text-sm text-gray-500">
                @if($med->owner_type === \App\Models\FamilyMember::class)
                    <span class="text-[#7F77DD] font-medium">{{ $med->owner->name ?? 'Anggota' }}</span>
                @else
                    <span class="text-[#185FA5] font-medium">Saya</span>
                @endif
            </td>
            <td>
                <div class="flex items-center gap-2">
                    <a href="{{ route('medicines.show', $med->id) }}" class="obk-btn obk-btn-outline text-xs px-2.5 py-1.5 border-gray-200 text-gray-600 hover:bg-gray-50 hover:text-[#185FA5]">
                        Detail
                    </a>
                    @if($isExpired || $daysLeft <= 30)
                        <a href="{{ route('ecomed.disposal-guide') }}?form={{ $med->form ?? 'tablet' }}" class="obk-btn obk-btn-success text-xs px-2.5 py-1.5">
                            <i data-lucide="recycle" class="w-3.5 h-3.5"></i> Buang
                        </a>
                    @endif
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
