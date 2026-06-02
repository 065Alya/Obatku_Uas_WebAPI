{{-- ─── Schedule Card Component ───
     Usage: @include('components.schedule-card', ['schedule' => $schedule])
--}}

@php
    $isTaken = $schedule->logs->where('status', 'taken')->isNotEmpty();
    $isSkipped = $schedule->logs->where('status', 'skipped')->isNotEmpty();
    $isPast = \Carbon\Carbon::parse($schedule->schedule_time)->lt(now());
@endphp

<div class="obk-card {{ $isTaken ? 'border-l-4 border-[#1D9E75]' : ($isPast && !$isTaken && !$isSkipped ? 'border-l-4 border-[#E24B4A]' : 'border-l-4 border-[#185FA5]') }}">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            {{-- Time --}}
            <div class="text-center min-w-[56px]">
                <p class="text-2xl font-bold text-[#042C53]">{{ \Carbon\Carbon::parse($schedule->schedule_time)->format('H:i') }}</p>
                <p class="text-xs text-gray-400">{{ $schedule->frequency_label }}</p>
            </div>

            {{-- Divider --}}
            <div class="w-px h-12 bg-gray-200"></div>

            {{-- Medicine Info --}}
            <div>
                <p class="font-semibold text-[#042C53]">{{ $schedule->medicine->name }}</p>
                @if($schedule->dosage_amount)
                    <p class="text-sm text-gray-500">Dosis: {{ $schedule->dosage_amount }}</p>
                @endif
                @if($schedule->familyMember)
                    <p class="text-xs text-gray-400 mt-0.5">
                        <i data-lucide="user" class="w-3 h-3 inline"></i>
                        {{ $schedule->familyMember->name }}
                    </p>
                @endif
            </div>
        </div>

        {{-- Status / Actions --}}
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2">
                @if($isTaken)
                    <span class="obk-badge obk-badge-success">
                        <i data-lucide="check" class="w-3 h-3 mr-1"></i> Sudah
                    </span>
                @elseif($isSkipped)
                    <span class="obk-badge obk-badge-warning">
                        <i data-lucide="skip-forward" class="w-3 h-3 mr-1"></i> Dilewati
                    </span>
                @else
                    <form method="POST" action="{{ route('schedules.log', $schedule->id) }}" class="inline">
                        @csrf
                        <input type="hidden" name="status" value="taken">
                        <button type="submit" class="obk-btn obk-btn-success text-xs py-2 px-3" title="Tandai sudah diminum">
                            <i data-lucide="check" class="w-4 h-4"></i>
                        </button>
                    </form>
                    <form method="POST" action="{{ route('schedules.log', $schedule->id) }}" class="inline">
                        @csrf
                        <input type="hidden" name="status" value="skipped">
                        <button type="submit" class="obk-btn obk-btn-outline text-xs py-2 px-3" title="Lewati">
                            <i data-lucide="skip-forward" class="w-4 h-4"></i>
                        </button>
                    </form>
                @endif
            </div>
            
            <div class="flex items-center gap-1 border-l border-gray-200 pl-2">
                <a href="{{ route('schedules.edit', $schedule->id) }}" class="p-1 text-gray-400 hover:text-[#185FA5] transition-colors" title="Ubah Jadwal">
                    <i data-lucide="edit-2" class="w-4 h-4"></i>
                </a>
                <form action="{{ route('schedules.destroy', $schedule->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal obat ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-1 text-gray-400 hover:text-[#E24B4A] transition-colors" title="Hapus Jadwal">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
