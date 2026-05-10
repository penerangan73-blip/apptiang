@extends('layouts.dashboard', ['pageTitle' => 'Aktivitas - Inventarisasi APJ Non APP'])

@section('content')
<div class="card overflow-hidden">
    <div class="flex flex-col gap-4 border-b border-white/10 p-5 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h2 class="flex items-center gap-2 text-lg font-black"><i data-lucide="activity" class="icon text-cyan-400"></i> Aktivitas</h2>
            <p class="text-sm text-slate-400">Rekap kegiatan pendataan dan kerusakan.</p>
        </div>
        <form method="GET" action="{{ route('reports.index') }}" class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <label class="relative block min-w-56">
                <input id="activity-date-filter" class="field w-full pl-3 pr-10" type="date" name="date" value="{{ $date }}">
                <button type="button" class="absolute right-3 top-1/2 z-10 -translate-y-1/2 text-cyan-400 transition hover:text-cyan-300" data-date-trigger="activity-date-filter" aria-label="Buka kalender">
                    <i data-lucide="calendar" class="size-4"></i>
                </button>
            </label>
            <div class="flex gap-2">
                <button class="btn btn-primary" type="submit">
                    <i data-lucide="calendar-search" class="icon"></i>
                    Filter
                </button>
                @if ($date)
                    <a class="btn btn-muted" href="{{ route('reports.index') }}">Reset</a>
                @endif
            </div>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-white/10 text-sm">
            <thead class="bg-slate-900/5 text-left text-xs font-black uppercase tracking-wide text-slate-400">
                <tr>
                    <th class="px-5 py-3">Tanggal</th>
                    <th class="px-5 py-3">ID Pelanggan</th>
                    <th class="px-5 py-3">Jalan</th>
                    <th class="px-5 py-3">Catatan</th>
                    <th class="px-5 py-3">Foto</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse ($reports as $report)
                    <tr>
                        <td class="px-5 py-4 font-semibold">{{ $report->report_date }}</td>
                        <td class="px-5 py-4">{{ $report->pole?->customer_id ?? 'Tiang tidak ditemukan' }}</td>
                        <td class="px-5 py-4">{{ $report->pole?->street_name }}</td>
                        <td class="px-5 py-4">{{ $report->notes }}</td>
                        <td class="px-5 py-4">
                            @if ($report->photo_url)
                                <a class="font-bold text-cyan-400" href="{{ $report->photo_url }}" target="_blank">Lihat</a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400">Belum ada aktivitas untuk filter ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="flex flex-col gap-3 border-t border-white/10 px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
        <p class="text-sm font-semibold text-slate-400">
            Menampilkan {{ $reports->firstItem() ?? 0 }}-{{ $reports->lastItem() ?? 0 }} dari {{ $reports->total() }} aktivitas
        </p>
        @if ($reports->hasPages())
            <div>
                {{ $reports->links() }}
            </div>
        @else
            <p class="text-sm font-semibold text-slate-500">Semua aktivitas tampil di halaman ini.</p>
        @endif
    </div>
</div>
<script>
document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-date-trigger]');
    if (!button) return;

    const input = document.getElementById(button.dataset.dateTrigger);
    if (!input) return;

    if (typeof input.showPicker === 'function') {
        input.showPicker();
    } else {
        input.focus();
    }
});
</script>
@endsection
