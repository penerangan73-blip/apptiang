@php
    $markerLegendItems = $markerLegendItems ?? [
        ['label' => 'Baik', 'color' => '#059669'],
        ['label' => 'Rusak', 'color' => '#e11d48'],
        ['label' => 'Perbaikan', 'color' => '#d97706'],
    ];
@endphp

<div class="pointer-events-none absolute bottom-3 left-3 z-[500] rounded-lg border border-white/15 bg-slate-950/90 px-3 py-2 shadow-[0_12px_30px_rgba(2,6,23,0.28)] backdrop-blur sm:bottom-4 sm:left-4">
    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Keterangan</p>
    <div class="mt-2 flex flex-wrap gap-2.5">
        @foreach ($markerLegendItems as $item)
            <div class="flex items-center gap-1.5 text-xs font-bold text-slate-200">
                <span class="size-3 rounded-full border-2 border-white shadow" style="background: {{ $item['color'] }}"></span>
                <span>{{ $item['label'] }}</span>
            </div>
        @endforeach
    </div>
</div>
