@extends('layouts.dashboard', ['pageTitle' => 'Aktivitas - Inventarisasi APJ Non APP'])

@section('content')
<div class="card overflow-hidden">
    <div class="flex flex-col gap-4 border-b border-white/10 p-5 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h2 class="flex items-center gap-2 text-lg font-black"><i data-lucide="activity" class="icon text-cyan-400"></i> Aktivitas</h2>
            <p class="text-sm text-slate-400">Rekap kegiatan pendataan dan kerusakan.</p>
        </div>
        <form method="GET" action="{{ route('reports.index') }}" class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <input id="activity-date-filter" class="field min-w-56" type="date" name="date" value="{{ $date }}">
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
                    <th class="px-5 py-3">Tanggal & Waktu</th>
                    <th class="px-5 py-3">ID Pelanggan</th>
                    <th class="px-5 py-3">Jalan</th>
                    <th class="px-5 py-3">Catatan</th>
                    <th class="px-5 py-3">Foto</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse ($reports as $report)
                    <tr>
                        <td class="px-5 py-4">
                            <p class="font-semibold text-slate-100">{{ $report->report_date?->format('d-m-Y') }}</p>
                            <p class="mt-1 text-xs font-bold text-slate-400">{{ $report->created_at?->format('H:i') }} WIB</p>
                        </td>
                        <td class="px-5 py-4">{{ $report->pole?->displayName() ?? 'Tiang tidak ditemukan' }}</td>
                        <td class="px-5 py-4">{{ $report->pole?->street_name }}</td>
                        <td class="px-5 py-4">{{ $report->notes }}</td>
                        <td class="px-5 py-4">
                            @php
                                $reportPhotos = match (true) {
                                    $report->photos->isNotEmpty() => $report->photos,
                                    (bool) $report->photo_url => collect([(object) ['photo_url' => $report->photo_url]]),
                                    $report->pole?->photos?->isNotEmpty() => $report->pole->photos,
                                    (bool) $report->pole?->photo_url => collect([(object) ['photo_url' => $report->pole->photo_url]]),
                                    default => collect(),
                                };
                            @endphp
                            @if ($reportPhotos->isNotEmpty())
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($reportPhotos as $index => $photo)
                                        <span class="relative inline-flex overflow-hidden rounded-lg border border-white/10 bg-white/5">
                                            <a class="block" href="{{ $photo->photo_url }}" target="_blank" rel="noopener noreferrer" aria-label="Buka foto laporan {{ $index + 1 }}">
                                                <img src="{{ $photo->photo_url }}" alt="Foto laporan {{ $index + 1 }}" class="h-16 w-16 object-cover transition hover:opacity-80">
                                            </a>
                                        </span>
                                    @endforeach
                                </div>
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
@endsection
