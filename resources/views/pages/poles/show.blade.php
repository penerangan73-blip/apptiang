@extends('layouts.dashboard', ['pageTitle' => $pole->displayName().' - Detail Tiang'])

@section('content')
@php
    $label = fn ($value) => [
        'baik' => 'Baik',
        'rusak' => 'Rusak',
        'perbaikan' => 'Berkarat',
        'lurus' => 'Tiang Baja (Galvanis)',
        'lengkung' => 'Tiang Baja (Galvanis)',
        'tanpa_tiang' => 'Menempel Tiang PLN',
        'tunggal' => 'Single Arm',
        'ganda' => 'Double Arm',
        'dekoratif' => 'Single Arm',
        'sodium' => 'HPS (Sodium Tekanan Tinggi)',
        'hilang' => 'Kusam',
        'tiang_baja_galvanis' => 'Tiang Baja (Galvanis)',
        'tiang_beton' => 'Tiang Beton',
        'menempel_tiang_pln' => 'Menempel Tiang PLN',
        'menempel_pohon' => 'Menempel Pohon',
        'single_arm' => 'Single Arm',
        'double_arm' => 'Double Arm',
        'high_mast' => 'High Mast',
        'keropos' => 'Keropos',
        'miring' => 'Miring',
        'berkarat' => 'Berkarat',
        'kokoh' => 'Kokoh',
        'led' => 'LED',
        'hps' => 'HPS (Sodium Tekanan Tinggi)',
        'son_t' => 'SON-T',
        'mercury' => 'Mercury',
        'lainnya' => 'Lainnya',
        'menyala' => 'Menyala',
        'redup' => 'Redup',
        'berkedip' => 'Berkedip',
        'mati' => 'Mati',
        'rusak_pecah' => 'Rusak Pecah',
        'tidak_ada_lampu' => 'Tidak Ada Lampu',
        'caping' => 'Caping',
        'konvensional' => 'Konvensional',
        'tidak_ada' => 'Tidak Ada',
        'pecah' => 'Pecah',
        'kusam' => 'Kusam',
        'kemasukan_air' => 'Kemasukan Air',
        'sudah_ada' => 'Sudah Ada',
        'belum' => 'Belum',
    ][$value] ?? ucfirst(str_replace('_', ' ', (string) $value));
    $ownership = fn ($value) => ['dpu_kp' => 'DPU KP', 'dishub' => 'DISHUB', 'pln' => 'PLN', 'lainnya' => 'Lainnya'][$value] ?? $label($value);
    $poleTitle = $pole->displayName();
    $poleMapData = [
        ...$pole->only(['customer_id', 'street_name', 'address', 'village', 'district', 'regency', 'province', 'latitude', 'longitude', 'power_usage', 'condition']),
        'display_name' => $poleTitle,
        'address_line' => $pole->addressLine(),
        'administrative_address' => $pole->administrativeAddress(),
        'location_label' => $pole->locationLabel(),
    ];
@endphp

<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div class="min-w-0">
        <a href="{{ route('poles.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-cyan-400 hover:text-cyan-300">
            <i data-lucide="arrow-left" class="icon"></i>
            Kembali ke Data Tiang
        </a>
        <div class="mt-3 flex flex-wrap items-center gap-3">
            <h2 class="break-words text-2xl font-black text-slate-100 sm:text-3xl">{{ $poleTitle }}</h2>
            <span class="badge badge-{{ $pole->condition }}">{{ $label($pole->condition) }}</span>
        </div>
        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-400">{{ $pole->locationLabel() }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
        @if ($canCreatePoles ?? false)
            <button type="button" class="btn btn-muted" data-open-dialog="report-dialog" data-report-pole="{{ $pole->id }}">
                <i data-lucide="file-plus-2" class="icon"></i>
                Laporan Kerusakan
            </button>
        @endif
        @if ($canManagePoles)
            <button type="button" class="btn btn-primary" data-open-dialog="pole-edit-{{ $pole->id }}">
                <i data-lucide="edit-3" class="icon"></i>
                Edit Tiang
            </button>
        @endif
    </div>
</div>

<div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_420px]">
    <div class="space-y-5">
        <div class="card p-5">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @include('partials.detail-item', ['label' => 'Daya Lampu', 'value' => number_format($pole->power_usage).' Watt'])
                @include('partials.detail-item', ['label' => 'Jenis Tiang', 'value' => $label($pole->pole_type)])
                @include('partials.detail-item', ['label' => 'Tinggi Tiang', 'value' => $pole->height ? $pole->height.' m' : '-'])
                @include('partials.detail-item', ['label' => 'Kondisi Lampu', 'value' => $label($pole->lamp_status)])
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="border-b border-white/10 p-5">
                <h3 class="text-lg font-black">Data Lengkap</h3>
                <p class="text-sm text-slate-400">Informasi aset, koordinat, dan riwayat input.</p>
            </div>
            <div class="grid gap-5 p-5 md:grid-cols-2">
                @include('partials.detail-item', ['label' => 'Alamat', 'value' => $pole->addressLine(), 'class' => 'md:col-span-2'])
                @include('partials.detail-item', ['label' => 'Kelurahan', 'value' => $pole->village ?: '-'])
                @include('partials.detail-item', ['label' => 'Kecamatan', 'value' => $pole->district ?: '-'])
                @include('partials.detail-item', ['label' => 'Kabupaten', 'value' => $pole->regency ?: '-'])
                @include('partials.detail-item', ['label' => 'Provinsi', 'value' => $pole->province ?: '-'])
                @include('partials.detail-item', ['label' => 'Latitude', 'value' => number_format((float) $pole->latitude, 7)])
                @include('partials.detail-item', ['label' => 'Longitude', 'value' => number_format((float) $pole->longitude, 7)])
                @include('partials.detail-item', ['label' => 'Tipe Ornamen', 'value' => $label($pole->ornament_type)])
                @include('partials.detail-item', ['label' => 'Jenis Lampu', 'value' => $label($pole->lamp_type)])
                @include('partials.detail-item', ['label' => 'Jenis Rumah Lampu', 'value' => $label($pole->lamp_house_type)])
                @include('partials.detail-item', ['label' => 'Kondisi Rumah Lampu', 'value' => $label($pole->lamp_house_condition)])
                @include('partials.detail-item', ['label' => 'Jumlah Titik Lampu', 'value' => number_format((int) $pole->light_point_count)])
                @include('partials.detail-item', ['label' => 'KWH Meter', 'value' => $label($pole->kwh_meter) ?: '-'])
                @include('partials.detail-item', ['label' => 'Kepemilikan', 'value' => $ownership($pole->ownership)])
                @include('partials.detail-item', ['label' => 'Pertama Diinput', 'value' => optional($pole->created_at)->format('H:i d-m-Y') ?: '-'])
                @include('partials.detail-item', ['label' => 'Diinput Oleh', 'value' => $pole->createdBy?->name ?? '-'])
                @include('partials.detail-item', ['label' => 'Update Terakhir', 'value' => optional($pole->updated_at)->format('H:i d-m-Y') ?: '-'])
                @include('partials.detail-item', ['label' => 'Update Oleh', 'value' => $pole->updatedBy?->name ?? '-'])
                @include('partials.detail-item', ['label' => 'Verifikasi', 'value' => $pole->is_verified ? 'Terverifikasi' : 'Belum diverifikasi'])
                @include('partials.detail-item', ['label' => 'Diverifikasi Oleh', 'value' => $pole->is_verified ? (($pole->verifiedBy?->name ?? '-').' / '.(optional($pole->verified_at)->format('H:i d-m-Y') ?: '-')) : '-'])
                @include('partials.detail-item', ['label' => 'Keterangan', 'value' => $pole->notes ?: '-', 'class' => 'md:col-span-2'])
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="flex flex-col gap-3 border-b border-white/10 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-black">Histori Aktivitas</h3>
                    <p class="text-sm text-slate-400">{{ $reports->total() }} aktivitas tercatat untuk tiang ini.</p>
                </div>
                @if ($canCreatePoles ?? false)
                    <button type="button" class="btn btn-muted" data-open-dialog="report-dialog" data-report-pole="{{ $pole->id }}">
                        <i data-lucide="plus-circle" class="icon"></i>
                        Tambah Laporan
                    </button>
                @endif
            </div>
            <div class="divide-y divide-white/5">
                @forelse ($reports as $report)
                    <div class="grid gap-4 p-5 md:grid-cols-[minmax(0,1fr)_180px]">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-black text-slate-100">{{ optional($report->report_date)->format('d-m-Y') }}</p>
                                <span class="rounded-full border border-white/10 bg-white/5 px-2 py-1 text-[10px] font-black uppercase tracking-widest text-slate-400">{{ $report->user?->name ?? 'User tidak ditemukan' }}</span>
                            </div>
                            <p class="mt-2 text-sm leading-6 text-slate-300">{{ $report->notes }}</p>
                        </div>
                        <div>
                            @php
                                $reportPhotos = match (true) {
                                    $report->photos->isNotEmpty() => $report->photos,
                                    (bool) $report->photo_url => collect([(object) ['photo_url' => $report->photo_url]]),
                                    $pole->photos->isNotEmpty() => $pole->photos,
                                    (bool) $pole->photo_url => collect([(object) ['photo_url' => $pole->photo_url]]),
                                    default => collect(),
                                };
                            @endphp
                            @if ($reportPhotos->isNotEmpty())
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach ($reportPhotos->take(4) as $photo)
                                        <div class="group relative overflow-hidden rounded-lg border border-white/10 bg-white/5">
                                            <a href="{{ $photo->photo_url }}" target="_blank" class="block">
                                                <img src="{{ $photo->photo_url }}" alt="Foto laporan {{ $poleTitle }}" class="h-20 w-full object-cover">
                                            </a>
                                            @if ($canManagePoles && isset($photo->id))
                                                <form method="POST" action="{{ route('daily-report-photos.destroy', $photo) }}" class="absolute right-1.5 top-1.5">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="flex size-7 items-center justify-center rounded-md bg-red-600/90 text-white shadow-lg transition hover:bg-red-500" onclick="return confirm('Hapus foto laporan ini?')" aria-label="Hapus foto laporan">
                                                        <i data-lucide="trash-2" class="size-4"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                @if ($reportPhotos->count() > 4)
                                    <p class="mt-2 text-xs font-bold text-slate-400">+{{ $reportPhotos->count() - 4 }} foto lainnya</p>
                                @endif
                            @else
                                <div class="flex h-28 items-center justify-center rounded-lg border border-dashed border-white/15 text-sm font-semibold text-slate-500">Tanpa foto</div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-sm font-semibold text-slate-400">Belum ada histori aktivitas untuk tiang ini.</div>
                @endforelse
            </div>
            <div class="border-t border-white/10 px-5 py-4">
                {{ $reports->links() }}
            </div>
        </div>
    </div>

    <aside class="space-y-5">
        <div class="card overflow-hidden">
            <div class="border-b border-white/10 p-5">
                <h3 class="text-lg font-black">Foto Tiang</h3>
                <p class="text-sm text-slate-400">Dokumentasi aset lapangan.</p>
            </div>
            <div class="p-5">
                @php
                    $polePhotos = $pole->photos->isNotEmpty()
                        ? $pole->photos
                        : collect($pole->photo_url ? [(object) ['photo_url' => $pole->photo_url]] : []);
                @endphp
                @if ($polePhotos->isNotEmpty())
                    <div class="grid gap-3">
                        @foreach ($polePhotos as $photo)
                            <div class="group relative overflow-hidden rounded-lg border border-white/10 bg-white/5">
                                <a href="{{ $photo->photo_url }}" target="_blank" class="block">
                                    <img src="{{ $photo->photo_url }}" alt="Foto {{ $poleTitle }}" class="max-h-[320px] w-full object-cover">
                                </a>
                                @if ($canManagePoles && isset($photo->id))
                                    <form method="POST" action="{{ route('pole-photos.destroy', $photo) }}" class="absolute right-3 top-3">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger bg-red-600/90 px-3 py-2 text-white shadow-lg transition hover:bg-red-500" onclick="return confirm('Hapus foto tiang ini?')" aria-label="Hapus foto tiang">
                                            <i data-lucide="trash-2" class="icon"></i>
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex min-h-64 flex-col items-center justify-center rounded-lg border border-dashed border-white/20 text-slate-500">
                        <i data-lucide="image-off" class="mb-2 size-8 opacity-60"></i>
                        <span class="text-sm font-semibold">Belum ada foto tiang</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="border-b border-white/10 p-5">
                <h3 class="text-lg font-black">Lokasi</h3>
                <p class="text-sm text-slate-400">{{ number_format((float) $pole->latitude, 5) }}, {{ number_format((float) $pole->longitude, 5) }}</p>
            </div>
            <div class="p-5">
                <div id="pole-detail-map" class="glass-panel h-80 overflow-hidden rounded-lg"></div>
            </div>
        </div>
    </aside>
</div>
@endsection

@section('dialogs')
    @include('partials.pole-dialogs', ['dialogPoles' => collect([$pole]), 'reportPoles' => collect([$pole]), 'sourcePoles' => $sourcePoles])
@endsection

@section('page-scripts')
    window.pjuCanManagePoles = @json($canManagePoles);
    @include('partials.pole-scripts')

    initPoleDetailMap();

    function initPoleDetailMap() {
        const el = document.getElementById('pole-detail-map');
        if (!el) return;

        const pole = @json($poleMapData);
        const lat = Number(pole.latitude);
        const lng = Number(pole.longitude);
        if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;

        const map = L.map(el).setView([lat, lng], 17);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);

        const colors = { kokoh: '#059669', keropos: '#e11d48', miring: '#d97706', berkarat: '#b45309', lainnya: '#64748b' };
        L.marker([lat, lng], {
            icon: L.divIcon({
                className: '',
                html: `<span style="display:block;width:22px;height:22px;border-radius:999px;background:${colors[pole.condition] || '#2563eb'};border:3px solid white;box-shadow:0 8px 20px rgba(15,23,42,.3)"></span>`,
                iconSize: [22, 22],
                iconAnchor: [11, 11],
            }),
        }).addTo(map).bindPopup(`
            <div class="p-3">
                <div class="flex items-start justify-between gap-3 pr-7">
                    <p class="truncate text-lg font-black text-slate-100">${escapeHtml(pole.display_name || pole.customer_id)}</p>
                    <span class="badge badge-${pole.condition} mt-5 shrink-0">${escapeHtml(formatOption(pole.condition))}</span>
                </div>
                <p class="mt-1 text-sm leading-5 text-slate-300">${escapeHtml(pole.location_label || pole.address_line || pole.street_name)}</p>
                <p class="mt-3 text-sm font-semibold text-slate-100">Daya lampu ${Number(pole.power_usage || 0).toLocaleString('id-ID')} W</p>
            </div>
        `, { maxWidth: 300, className: 'pju-marker-popup' });
    }
@endsection
