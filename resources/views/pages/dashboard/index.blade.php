@extends('layouts.dashboard', ['pageTitle' => 'Dashboard - Inventarisasi APJ Non APP'])

@section('content')
@php
    $today = now()->toDateString();
    $todayReports = $reports->filter(fn ($report) => $report->report_date->format('Y-m-d') === $today);
    $label = fn ($value) => [
        'keropos' => 'Keropos',
        'miring' => 'Miring',
        'berkarat' => 'Berkarat',
        'kokoh' => 'Kokoh',
    ][$value] ?? ucfirst(str_replace('_', ' ', (string) $value));
@endphp

<style>
    @keyframes dashboardLogoFloat {
        0%, 100% { transform: translateY(0) scale(1); }
        50% { transform: translateY(-4px) scale(1.03); }
    }

    @keyframes dashboardLogoGlow {
        0%, 100% { opacity: 0.32; transform: scale(0.94); }
        50% { opacity: 0.72; transform: scale(1.08); }
    }

    .dashboard-logo-mark {
        animation: dashboardLogoFloat 3.6s ease-in-out infinite;
    }

    .dashboard-logo-glow {
        animation: dashboardLogoGlow 2.8s ease-in-out infinite;
    }

    @media (prefers-reduced-motion: reduce) {
        .dashboard-logo-mark,
        .dashboard-logo-glow {
            animation: none;
        }
    }
</style>

{{-- Welcome Banner --}}
<div class="dark-card relative overflow-hidden p-5 sm:p-6">
    <div class="absolute -right-20 -top-20 size-64 rounded-full bg-cyan-500/20 blur-3xl"></div>
    <div class="absolute -left-20 -bottom-20 size-64 rounded-full bg-blue-600/20 blur-3xl"></div>
    <div class="relative flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-start gap-4">
            <div class="relative shrink-0">
                <span class="dashboard-logo-glow absolute inset-0 rounded-2xl bg-cyan-300/40 blur-md"></span>
                <img src="{{ asset('image/dishub.png') }}" alt="Logo Dishub" class="dashboard-logo-mark relative size-16 rounded-xl bg-white/90 p-2 shadow-lg ring-1 ring-white/40 sm:size-20">
            </div>
            <div>
                <h2 class="text-2xl font-black tracking-tight text-white sm:text-3xl">Selamat datang, {{ $user->name }}</h2>
                <p class="mt-2 max-w-2xl text-sm leading-relaxed text-slate-300">Pantau inventaris APJ dengan lebih tertata, mulai dari sebaran lokasi, kondisi aset, penggunaan daya, hingga laporan lapangan dalam satu ruang kerja yang nyaman.</p>
            </div>
        </div>
        <div class="flex flex-row flex-wrap gap-3 mt-2 lg:mt-0">
            @if ($canManagePoles)
                <a href="{{ route('poles.index') }}" class="btn btn-primary px-4 py-2 text-sm">
                    <i data-lucide="plus-circle" class="icon size-4 sm:size-5"></i>
                    Tambah Tiang
                </a>
            @endif
            <a href="{{ route('map.index') }}" class="btn btn-muted px-4 py-2 text-sm">
                <i data-lucide="map-pinned" class="icon size-4 sm:size-5"></i>
                Buka Peta
            </a>
        </div>
    </div>
</div>

{{-- Ringkasan Di Atas Peta --}}
<div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_360px]">
    <div class="space-y-5">
        <div class="grid gap-4 sm:grid-cols-2">
            @include('partials.stat-card', ['label' => 'Total Tiang', 'value' => number_format($stats['total']), 'description' => 'aset terdata', 'icon' => 'database'])
            @include('partials.stat-card', ['label' => 'Total Daya Lampu', 'value' => number_format($stats['daya']), 'description' => 'watt terdata', 'icon' => 'zap'])
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @include('partials.stat-card', ['label' => 'Lampu Menyala', 'value' => number_format($stats['lamp_menyala']), 'description' => 'status normal', 'icon' => 'lightbulb'])
            @include('partials.stat-card', ['label' => 'Lampu Redup', 'value' => number_format($stats['lamp_redup']), 'description' => 'perlu cek daya', 'icon' => 'sun-dim'])
            @include('partials.stat-card', ['label' => 'Lampu Berkedip', 'value' => number_format($stats['lamp_berkedip']), 'description' => 'perlu pemeriksaan', 'icon' => 'activity'])
            @include('partials.stat-card', ['label' => 'Lampu Mati', 'value' => number_format($stats['lamp_mati']), 'description' => 'prioritas tindak lanjut', 'icon' => 'power-off'])
        </div>

        <div class="card p-5">
            <h3 class="text-lg font-black">Kondisi Tiang</h3>
            <p class="text-sm text-slate-400">Distribusi kondisi aset PJU.</p>
            <div class="mt-4 space-y-4">
                @foreach (['kokoh', 'keropos', 'miring', 'berkarat'] as $conditionName)
                    @php
                        $count = $stats[$conditionName];
                        $percent = (int) round(($count / max($stats['total'], 1)) * 100);
                    @endphp
                    <div class="space-y-2">
                        <div class="flex items-center justify-between gap-3">
                            <span class="badge badge-{{ $conditionName }}">{{ $label($conditionName) }}</span>
                            <p class="text-sm font-bold text-slate-400">{{ number_format($count) }} data - {{ $percent }}%</p>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-800">
                            <div class="h-full rounded-full bg-cyan-600" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card p-5 xl:min-h-[430px]">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h3 class="text-lg font-black">Aktivitas Hari Ini</h3>
                <p class="text-sm text-slate-400">Rekap singkat pekerjaan surveyor.</p>
            </div>
            <div class="rounded-xl border border-cyan-400/20 bg-cyan-400/10 px-3 py-2 text-right">
                <p class="text-2xl font-black text-cyan-300">{{ $todayReports->count() }}</p>
                <p class="text-[10px] font-black uppercase tracking-widest text-cyan-400">Aktivitas</p>
            </div>
        </div>
        <div class="mt-4 max-h-[340px] space-y-3 overflow-y-auto pr-2 xl:max-h-[520px]">
            @forelse ($todayReports as $report)
                <div class="rounded-lg border bg-slate-900/5 p-3">
                    <p class="font-bold text-slate-100">{{ $report->pole?->displayName() ?? 'Tiang tidak ditemukan' }}</p>
                    <p class="text-sm text-slate-400">{{ $report->pole?->locationLabel() }}</p>
                    <p class="mt-2 text-sm leading-6 text-slate-300">{{ $report->notes }}</p>
                </div>
            @empty
                <p class="text-sm text-slate-400">Belum ada aktivitas hari ini.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- Dashboard Map --}}
<div class="card">
    <div class="flex items-center justify-between gap-4 border-b border-white/10 p-5">
        <div>
            <h3 class="flex items-center gap-2 text-lg font-black"><i data-lucide="gauge" class="icon text-cyan-400"></i> Dashboard Operasional</h3>
            <p class="text-sm text-slate-400">Ringkasan aset, aktivitas harian, dan prioritas tindak lanjut.</p>
        </div>
    </div>
    <div class="p-5">
        <div class="relative">
            <div id="dashboard-map" class="glass-panel h-[400px] sm:h-[500px] overflow-hidden rounded-lg"></div>
            @include('partials.marker-status-legend')
        </div>
    </div>
</div>

{{-- Recent Poles --}}
<div class="card overflow-hidden">
    <div class="border-b border-white/10 p-5">
        <h3 class="text-lg font-black">Aset Terbaru</h3>
        <p class="text-sm text-slate-400">Data tiang yang siap divalidasi atau diperbarui.</p>
    </div>
    @include('partials.poles-table', ['poles' => $recentPoles, 'showTimestamps' => false, 'showManageActions' => false])
</div>
@endsection

@section('dialogs')
    @include('partials.pole-dialogs', ['dialogPoles' => $recentPoles])
@endsection

@section('page-scripts')
    initDashboardMap();

    function initDashboardMap() {
        const el = document.getElementById('dashboard-map');
        if (!el) return;
        const map = L.map(el).setView([-7.965, 110.603], 11);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);
        loadDashboardMarkers(map);
    }

    async function loadDashboardMarkers(map) {
        try {
            const response = await fetch(@json(route('map.markers', ['summary' => 1])), { headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error('Marker tidak bisa dimuat.');
            const payload = await response.json();
            addDashboardMarkers(map, payload.data || []);
        } catch (error) {
            console.error(error);
        }
    }

    function addDashboardMarkers(map, poles) {
        const bounds = [];
        const markerColors = { kokoh: '#059669', keropos: '#e11d48', miring: '#d97706', berkarat: '#b45309' };
        poles.forEach((pole) => {
            const lat = Number(pole.latitude), lng = Number(pole.longitude);
            if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;
            bounds.push([lat, lng]);
            L.marker([lat, lng], {
                icon: L.divIcon({ className: '', html: `<span style="display:block;width:18px;height:18px;border-radius:999px;background:${markerColors[pole.condition] || '#2563eb'};border:3px solid white;box-shadow:0 8px 20px rgba(15,23,42,.22)"></span>`, iconSize: [18, 18], iconAnchor: [9, 9] })
            }).addTo(map).bindPopup(createDashboardMarkerPopup(pole), { maxWidth: 300, className: 'pju-marker-popup' });
        });
        if (bounds.length) map.fitBounds(bounds, { padding: [36, 36], maxZoom: 13 });
    }

    function createDashboardMarkerPopup(pole) {
        return `
            <div class="p-3">
                <div class="flex items-start justify-between gap-3 pr-7">
                    <div class="min-w-0">
                        <p class="truncate text-lg font-black text-slate-100">${escapeHtml(pole.display_name || pole.customer_id)}</p>
                    </div>
                    <span class="badge badge-${pole.condition} mt-5 shrink-0">${escapeHtml(formatOption(pole.condition))}</span>
                </div>
                <p class="mt-1 text-sm leading-5 text-slate-300">${escapeHtml(pole.location_label || pole.address_line || pole.street_name)}</p>
                <dl class="mt-3 grid grid-cols-[74px_1fr] gap-x-3 gap-y-1.5 text-sm">
                    <dt class="font-bold text-slate-500">Daya Lampu</dt>
                    <dd class="font-semibold text-slate-100">${Number(pole.power_usage || 0).toLocaleString('id-ID')} W</dd>
                    <dt class="font-bold text-slate-500">Koordinat</dt>
                    <dd class="font-semibold text-slate-100">${Number(pole.latitude).toFixed(5)}, ${Number(pole.longitude).toFixed(5)}</dd>
                </dl>
            </div>
        `;
    }
@endsection
