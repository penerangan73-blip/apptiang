@extends('layouts.app', ['title' => 'Dashboard - PJU Surveyor'])

@section('body')
@php
    $user = auth()->user();
    $canManagePoles = in_array($user->role, ['superadmin', 'surveyor'], true);
    $isSuperadmin = $user->role === 'superadmin';
    $label = fn ($value) => ucfirst(str_replace('_', ' ', (string) $value));
    $ownership = fn ($value) => ['dpu_kp' => 'DPU KP', 'dishub' => 'DISHUB', 'pln' => 'PLN', 'lainnya' => 'Lainnya'][$value] ?? $label($value);
    $today = now()->toDateString();
    $todayReports = $reports->filter(fn ($report) => $report->report_date->format('Y-m-d') === $today);
    $visibleTabs = [
        ['id' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'gauge'],
        ['id' => 'data', 'label' => 'Data Tiang', 'icon' => 'database'],
        ['id' => 'map', 'label' => 'Peta', 'icon' => 'map-pinned'],
        ['id' => 'reports', 'label' => 'Laporan', 'icon' => 'bar-chart-3'],
    ];
    if ($isSuperadmin) {
        $visibleTabs[] = ['id' => 'users', 'label' => 'Manajemen User', 'icon' => 'users'];
    }
@endphp

<div class="min-h-screen pb-24 lg:grid lg:grid-cols-[280px_1fr] lg:pb-0">
    <aside class="fixed inset-y-0 left-0 z-40 hidden w-72 border-r border-white/10 bg-slate-950/50 backdrop-blur-xl text-white lg:flex lg:flex-col">
        <div class="border-b border-white/10 p-6">
            <div class="flex items-center gap-4">
                <div class="size-12 shrink-0 rounded-full border border-white/20 bg-white/5 p-1 shadow-lg">
                    <img src="{{ asset('image/dishub.png') }}" alt="Logo Dishub" class="size-full object-contain">
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-cyan-400 drop-shadow-sm">Dinas Perhubungan</p>
                    <h1 class="text-sm font-bold text-slate-100">Inventarisasi APJ</h1>
                </div>
            </div>
        </div>
        <nav class="flex-1 space-y-1 p-4">
            @foreach ($visibleTabs as $tab)
                <button type="button" class="tab-button flex h-11 w-full items-center gap-3 rounded-lg px-3 text-sm font-semibold transition hover:bg-slate-900/10" data-tab-target="{{ $tab['id'] }}">
                    <i data-lucide="{{ $tab['icon'] }}" class="icon"></i>
                    {{ $tab['label'] }}
                </button>
            @endforeach
        </nav>
    </aside>

    <div class="min-w-0 lg:col-start-2 pt-[64px] lg:pt-0">
        <header class="fixed inset-x-0 top-0 z-50 border-b border-white/10 bg-slate-950/90 backdrop-blur-xl lg:relative lg:w-auto">
            <div class="flex items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
                <div class="flex min-w-0 items-center gap-3">
                    <div class="size-10 shrink-0 rounded-full border border-white/20 bg-white/5 p-1 lg:hidden">
                        <img src="{{ asset('image/dishub.png') }}" alt="Logo Dishub" class="size-full object-contain">
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-black uppercase tracking-widest text-cyan-400 drop-shadow-sm">Dinas Perhubungan</p>
                        <h2 class="truncate text-base font-bold text-slate-100">Inventarisasi APJ Non APP</h2>
                    </div>
                </div>

                <div class="relative">
                    <button type="button" class="btn btn-muted" data-menu-toggle="profile-menu">
                        @if ($user->photo_url)
                            <img src="{{ $user->photo_url }}" alt="{{ $user->name }}" class="size-7 rounded-full object-cover">
                        @else
                            <i data-lucide="user-circle" class="icon text-cyan-400"></i>
                        @endif
                        <span class="hidden max-w-36 truncate sm:inline">{{ $user->name }}</span>
                        <i data-lucide="chevron-down" class="icon"></i>
                    </button>
                    <div id="profile-menu" class="absolute right-0 top-12 z-50 hidden w-72 overflow-hidden rounded-xl border border-white/10 bg-slate-900 shadow-2xl">
                        <div class="border-b border-white/10 p-4">
                            <p class="truncate font-bold text-slate-100">{{ $user->name }}</p>
                            <p class="truncate text-xs font-semibold text-slate-400">{{ $user->email }}</p>
                        </div>
                        <div class="p-2">
                            <button type="button" class="flex h-10 w-full items-center gap-2 rounded-md px-3 text-sm font-semibold text-slate-300 hover:bg-white/5" data-open-dialog="profile-dialog">
                                <i data-lucide="user-circle" class="icon text-cyan-400"></i>
                                Ubah Profil
                            </button>
                            <button type="button" class="flex h-10 w-full items-center gap-2 rounded-md px-3 text-sm font-semibold text-slate-300 hover:bg-white/5" data-open-dialog="password-dialog">
                                <i data-lucide="key-round" class="icon text-cyan-400"></i>
                                Ubah Password
                            </button>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="flex h-10 w-full items-center gap-2 rounded-md px-3 text-sm font-semibold text-red-600 hover:bg-slate-900/5" type="submit">
                                    <i data-lucide="log-out" class="icon"></i>
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-[1500px] space-y-5 px-4 py-5 sm:px-6 lg:px-8">
            @if (session('status'))
                <div id="status-alert" class="rounded-xl border border-cyan-500/30 bg-cyan-500/10 px-5 py-4 text-sm font-semibold text-cyan-400 shadow-[0_0_15px_rgba(6,182,212,0.15)] transition-opacity duration-500">
                    <div class="flex items-center gap-3">
                        <i data-lucide="check-circle-2" class="icon"></i>
                        {{ session('status') }}
                    </div>
                </div>
                <script>
                    setTimeout(() => {
                        const alert = document.getElementById('status-alert');
                        if (alert) {
                            alert.style.opacity = '0';
                            setTimeout(() => alert.remove(), 500);
                        }
                    }, 3000);
                </script>
            @endif

            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <p class="font-bold">Periksa lagi input berikut:</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="tab-panel space-y-5" data-tab-panel="dashboard">
                <div class="dark-card relative overflow-hidden p-5 sm:p-6">
                    <div class="absolute -right-20 -top-20 size-64 rounded-full bg-cyan-500/20 blur-3xl"></div>
                    <div class="absolute -left-20 -bottom-20 size-64 rounded-full bg-blue-600/20 blur-3xl"></div>
                    <div class="relative flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h2 class="text-2xl font-black tracking-tight text-white sm:text-3xl">Dashboard Utama</h2>
                            <p class="mt-2 max-w-2xl text-sm leading-relaxed text-slate-300">Selamat datang kembali, <strong>{{ $user->name }}</strong>. Monitor lokasi, kondisi, daya, dan laporan kerusakan PJU secara real-time.</p>
                        </div>
                        <div class="flex flex-row flex-wrap gap-3 mt-2 lg:mt-0">
                            @if ($canManagePoles)
                                <button type="button" class="btn btn-primary px-4 py-2 text-sm" data-open-dialog="pole-create-dialog">
                                    <i data-lucide="plus-circle" class="icon size-4 sm:size-5"></i>
                                    Tambah Tiang
                                </button>
                            @endif
                            <button type="button" class="btn border border-white/20 bg-slate-900/10 text-white hover:bg-slate-900/15 px-4 py-2 text-sm" data-tab-target="map">
                                <i data-lucide="map-pinned" class="icon size-4 sm:size-5"></i>
                                Buka Peta
                            </button>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                    @include('partials.stat-card', ['label' => 'Total Tiang', 'value' => number_format($stats['total']), 'description' => 'aset terdata', 'icon' => 'database'])
                    @include('partials.stat-card', ['label' => 'Kondisi Baik', 'value' => number_format($stats['baik']), 'description' => 'siap operasi', 'icon' => 'check-circle-2'])
                    @include('partials.stat-card', ['label' => 'Rusak', 'value' => number_format($stats['rusak']), 'description' => 'prioritas tindak lanjut', 'icon' => 'alert-triangle'])
                    @include('partials.stat-card', ['label' => 'Perbaikan', 'value' => number_format($stats['perbaikan']), 'description' => 'sedang diproses', 'icon' => 'activity'])
                    @include('partials.stat-card', ['label' => 'Total Daya', 'value' => number_format($stats['daya']), 'description' => 'watt terdata', 'icon' => 'zap'])
                </div>

                <div class="space-y-5 mt-5">
                    <div class="grid gap-5 xl:grid-cols-2">
                        <div class="card p-5">
                            <h3 class="text-lg font-black">Kondisi Lapangan</h3>
                            <p class="text-sm text-slate-400">Distribusi kondisi aset PJU.</p>
                            <div class="mt-4 space-y-4">
                                @foreach (['baik', 'rusak', 'perbaikan'] as $conditionName)
                                    @php
                                        $count = $stats[$conditionName];
                                        $percent = (int) round(($count / max($stats['total'], 1)) * 100);
                                    @endphp
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="badge badge-{{ $conditionName }}">{{ $label($conditionName) }}</span>
                                            <p class="text-sm font-bold text-slate-400">{{ $percent }}%</p>
                                        </div>
                                        <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                            <div class="h-full rounded-full bg-cyan-600" style="width: {{ $percent }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="card p-5">
                            <h3 class="text-lg font-black">Aktivitas Hari Ini</h3>
                            <p class="text-sm text-slate-400">Rekap singkat pekerjaan surveyor.</p>
                            <div class="mt-4 space-y-3">
                                @forelse ($todayReports->take(5) as $report)
                                    <div class="rounded-lg border bg-slate-900/5 p-3">
                                        <p class="font-bold text-slate-100">{{ $report->pole?->customer_id ?? 'Tiang tidak ditemukan' }}</p>
                                        <p class="text-sm text-slate-400">{{ $report->pole?->street_name }}</p>
                                        <p class="mt-2 text-sm leading-6 text-slate-300">{{ $report->notes }}</p>
                                    </div>
                                @empty
                                    <p class="text-sm text-slate-400">Belum ada aktivitas hari ini.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="flex items-center justify-between gap-4 border-b border-white/10 p-5">
                            <div>
                                <h3 class="flex items-center gap-2 text-lg font-black"><i data-lucide="gauge" class="icon text-cyan-400"></i> Dashboard Operasional</h3>
                                <p class="text-sm text-slate-400">Ringkasan aset, aktivitas harian, dan prioritas tindak lanjut.</p>
                            </div>
                        </div>
                        <div class="p-5">
                            <div id="dashboard-map" class="glass-panel h-[400px] sm:h-[500px] overflow-hidden rounded-lg"></div>
                        </div>
                    </div>

                    <div class="card overflow-hidden">
                        <div class="border-b border-white/10 p-5">
                            <h3 class="text-lg font-black">Aset Terbaru</h3>
                            <p class="text-sm text-slate-400">Data tiang yang siap divalidasi atau diperbarui.</p>
                        </div>
                        @include('partials.poles-table', ['poles' => $allPoles->take(5), 'showTimestamps' => false])
                    </div>
                </div>
            </section>

            <section class="tab-panel space-y-5" data-tab-panel="map" hidden>
                <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_420px]">
                    <div class="card overflow-hidden -mx-4 sm:mx-0 border-x-0 sm:border-x">
                        <div class="flex items-center justify-between gap-4 border-b border-white/10 p-4 sm:p-5">
                            <div>
                                <h3 class="flex items-center gap-2 text-lg font-black"><i data-lucide="map-pinned" class="icon text-cyan-400"></i> Peta Lokasi Tiang</h3>
                                <p class="text-sm text-slate-400">Titik aset berdasarkan filter jalan dan kondisi.</p>
                            </div>
                            <button type="button" id="load-map-layers" class="btn btn-muted hidden px-3 py-2 text-xs sm:inline-flex">
                                <i data-lucide="layers" class="icon"></i>
                                Muat Layer Peta
                            </button>
                        </div>
                        <div class="p-0 sm:p-5">
                            <div id="main-map" class="glass-panel h-[65vh] min-h-[400px] sm:h-[560px] overflow-hidden rounded-none sm:rounded-lg border-x-0 sm:border-x"></div>
                        </div>
                    </div>

                    <div class="card p-5">
                        <h3 class="text-lg font-black">Inspeksi Terpilih</h3>
                        <p class="text-sm text-slate-400">Detail cepat untuk surveyor lapangan.</p>
                        <div id="selected-pole-panel" class="mt-4 text-sm text-slate-400">Pilih titik tiang pada peta atau tabel.</div>
                    </div>
                </div>

                @include('partials.search-filter')
                @include('partials.poles-table', ['poles' => $poles, 'showTimestamps' => false])
            </section>

            <section class="tab-panel space-y-5" data-tab-panel="data" hidden>
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-100">Data Tiang PJU</h2>
                        <p class="text-sm text-slate-400">Kelola inventaris tiang PJU dari database backend.</p>
                    </div>
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <a class="btn btn-muted" href="{{ route('poles.export', request()->only(['query', 'condition'])) }}">
                            <i data-lucide="download" class="icon"></i>
                            Download Data
                        </a>
                        @if ($canManagePoles)
                            <button type="button" class="btn btn-primary" data-open-dialog="pole-create-dialog">
                                <i data-lucide="plus-circle" class="icon"></i>
                                Tambah Tiang Baru
                            </button>
                        @endif
                    </div>
                </div>
                @include('partials.search-filter')
                @include('partials.poles-table', ['poles' => $poles, 'showTimestamps' => true])
            </section>

            <section class="tab-panel" data-tab-panel="reports" hidden>
                <div class="card overflow-hidden">
                    <div class="border-b border-white/10 p-5">
                        <h2 class="flex items-center gap-2 text-lg font-black"><i data-lucide="clipboard-list" class="icon text-cyan-400"></i> Laporan Harian</h2>
                        <p class="text-sm text-slate-400">Rekap aktivitas pendataan dan kerusakan.</p>
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
                                    <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400">Belum ada laporan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            @if ($isSuperadmin)
                <section class="tab-panel space-y-5" data-tab-panel="users" hidden>
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-slate-100">Manajemen User</h2>
                            <p class="text-sm text-slate-400">Kelola akun superadmin, surveyor, dan user read-only.</p>
                        </div>
                        <button type="button" class="btn btn-primary" data-open-dialog="user-create-dialog">
                            <i data-lucide="plus-circle" class="icon"></i>
                            Tambah User
                        </button>
                    </div>
                    <div class="card overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-white/10 text-sm">
                                <thead class="bg-slate-900/5 text-left text-xs font-black uppercase tracking-wide text-slate-400">
                                    <tr>
                                        <th class="px-5 py-3">Nama</th>
                                        <th class="px-5 py-3">Email</th>
                                        <th class="px-5 py-3">Telepon</th>
                                        <th class="px-5 py-3">Role</th>
                                        <th class="px-5 py-3 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/5">
                                    @foreach ($users as $managedUser)
                                        <tr>
                                            <td class="px-5 py-4 font-bold">{{ $managedUser->name }}</td>
                                            <td class="px-5 py-4">{{ $managedUser->email }}</td>
                                            <td class="px-5 py-4">{{ $managedUser->phone ?: '-' }}</td>
                                            <td class="px-5 py-4">{{ $label($managedUser->role) }}</td>
                                            <td class="px-5 py-4">
                                                <div class="flex justify-end gap-2">
                                                    <button type="button" class="btn btn-muted px-3 py-2" data-open-dialog="user-edit-{{ $managedUser->id }}">
                                                        <i data-lucide="edit-3" class="icon"></i>
                                                        Edit
                                                    </button>
                                                    <form method="POST" action="{{ route('users.destroy', $managedUser) }}" onsubmit="return confirm('Hapus user ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-danger px-3 py-2" type="submit" @disabled($managedUser->is($user))>
                                                            <i data-lucide="trash-2" class="icon"></i>
                                                            Hapus
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            @endif
        </main>
    </div>

    <nav class="fixed inset-x-0 bottom-0 z-50 border-t border-white/5 bg-slate-950/95 pb-2 px-2 pt-2 backdrop-blur-2xl lg:hidden shadow-[0_-10px_40px_rgba(0,0,0,0.8)]" style="padding-bottom: max(0.5rem, env(safe-area-inset-bottom));">
        <div class="mx-auto grid max-w-lg gap-1" style="grid-template-columns: repeat({{ count($visibleTabs) }}, minmax(0, 1fr));">
            @foreach ($visibleTabs as $tab)
                <button type="button" class="mobile-tab flex h-14 flex-col items-center justify-center gap-1.5 rounded-xl text-[10px] font-bold text-slate-400 hover:bg-white/5 hover:text-slate-200 transition-all" data-tab-target="{{ $tab['id'] }}">
                    <i data-lucide="{{ $tab['icon'] }}" class="icon"></i>
                    {{ $tab['label'] }}
                </button>
            @endforeach
        </div>
    </nav>
</div>

@include('partials.dialogs')

<script>
    window.pjuPoles = @json($allPoles->values());
    window.pjuCanManagePoles = @json($canManagePoles);
</script>
<script>
    const layerConfigs = [
        { id: 'administrasi-area', label: 'Administrasi Area', url: '/data/peta/administrasi_ar_340320240807001259.zip', style: { color: '#075985', fillColor: '#38bdf8', fillOpacity: 0.48, opacity: 0.95, weight: 1.4 }, hover: { fillOpacity: 0.68, weight: 2.8 } },
        { id: 'administrasi-garis', label: 'Batas Administrasi', url: '/data/peta/administrasi_ln_340320240806233638.zip', style: { color: '#0e7490', fillOpacity: 0, opacity: 0.95, weight: 1.6, dashArray: '4 3' }, hover: { opacity: 1, weight: 2.6 } },
        { id: 'jalan', label: 'Jalan', url: '/data/peta/jalan_ln_340320240820002322.zip', style: { color: '#ef4444', fillOpacity: 0, opacity: 0.9, weight: 1.4 }, hover: { opacity: 1, weight: 2.4 } },
    ];
    const markerColors = { baik: '#059669', rusak: '#e11d48', perbaikan: '#d97706' };
    const maps = {};
    const coordinatePickers = new Set();
    const loadedLayers = [];
    let layerPromise = null;
    let shpScriptPromise = null;

    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
        setupTabs();
        setupDialogs();
        setupProfileMenu();
        setupPhotoPreviews();
        initPoleMap('dashboard-map', window.pjuPoles, false);
        document.getElementById('load-map-layers')?.addEventListener('click', () => loadLayersIntoMaps());
    });

    function setupTabs() {
        const initialTab = localStorage.getItem('pju-active-tab') || 'dashboard';
        document.querySelectorAll('[data-tab-target]').forEach((button) => {
            button.addEventListener('click', () => activateTab(button.dataset.tabTarget));
        });
        activateTab(document.querySelector(`[data-tab-panel="${initialTab}"]`) ? initialTab : 'dashboard');
    }

    function activateTab(tab) {
        document.querySelectorAll('[data-tab-panel]').forEach((panel) => panel.hidden = panel.dataset.tabPanel !== tab);
        document.querySelectorAll('[data-tab-target]').forEach((button) => button.classList.toggle('active', button.dataset.tabTarget === tab));
        localStorage.setItem('pju-active-tab', tab);
        if (tab === 'map') {
            initPoleMap('main-map', window.pjuPoles, true);
        }
        setTimeout(() => Object.values(maps).forEach((map) => map.invalidateSize()), 120);
    }

    function setupDialogs() {
        document.querySelectorAll('[data-open-dialog]').forEach((button) => {
            button.addEventListener('click', () => {
                const dialog = document.getElementById(button.dataset.openDialog);
                if (dialog?.showModal) {
                    dialog.showModal();
                    setupCoordinatePickers(dialog);
                    setTimeout(() => Object.values(maps).forEach((map) => map.invalidateSize()), 120);
                }
            });
        });
        document.querySelectorAll('[data-close-dialog]').forEach((button) => {
            button.addEventListener('click', () => button.closest('dialog')?.close());
        });
        document.querySelectorAll('dialog').forEach((dialog) => {
            dialog.addEventListener('click', (event) => {
                if (event.target === dialog) dialog.close();
            });
        });
        document.querySelectorAll('[data-report-pole]').forEach((button) => {
            button.addEventListener('click', () => {
                const select = document.getElementById('report-pole-id');
                if (select) select.value = button.dataset.reportPole;
            });
        });
    }

    function setupProfileMenu() {
        document.querySelectorAll('[data-menu-toggle]').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.stopPropagation();
                document.getElementById(button.dataset.menuToggle)?.classList.toggle('hidden');
            });
        });
        document.addEventListener('click', () => document.getElementById('profile-menu')?.classList.add('hidden'));
    }

    function setupPhotoPreviews() {
        document.querySelectorAll('[data-photo-input]').forEach((input) => {
            input.addEventListener('change', () => {
                const preview = document.getElementById(input.dataset.photoInput);
                const file = input.files?.[0];
                if (!preview || !file) return;
                preview.innerHTML = `<img src="${URL.createObjectURL(file)}" alt="Preview foto" class="max-h-48 rounded-md object-cover">`;
            });
        });
    }

    async function ensureShpJs() {
        if (window.shp) return window.shp;
        if (!shpScriptPromise) {
            shpScriptPromise = new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = 'https://unpkg.com/shpjs@6.2.0/dist/shp.min.js';
                script.async = true;
                script.onload = () => resolve(window.shp);
                script.onerror = reject;
                document.head.appendChild(script);
            });
        }
        return shpScriptPromise;
    }

    async function loadLayers() {
        if (loadedLayers.length) return loadedLayers;
        if (layerPromise) return layerPromise;

        layerPromise = (async () => {
            const parseShp = await ensureShpJs();
            const settled = await Promise.allSettled(layerConfigs.map(async (config) => {
                const response = await fetch(config.url);
                if (!response.ok) throw new Error(config.label);
                const buffer = await response.arrayBuffer();
                const parsed = await parseShp(buffer);
                return { ...config, data: Array.isArray(parsed) ? { type: 'FeatureCollection', features: parsed.flatMap((item) => item.features) } : parsed };
            }));
            loadedLayers.push(...settled.flatMap((item) => item.status === 'fulfilled' ? [item.value] : []));
            return loadedLayers;
        })();

        return layerPromise;
    }

    async function loadLayersIntoMaps() {
        const button = document.getElementById('load-map-layers');
        if (button) button.textContent = 'Memuat layer...';

        try {
            const layers = await loadLayers();
            Object.values(maps).forEach((map) => addLayersToMap(map, layers));
            if (button) button.textContent = layers.length ? `${layers.length} layer dimuat` : 'Layer gagal dimuat';
        } catch {
            if (button) button.textContent = 'Layer gagal dimuat';
        } finally {
            lucide.createIcons();
        }
    }

    function addLayersToMap(map, layers) {
        if (map._pjuLayersLoaded) return;
        layers.forEach((layer) => {
            L.geoJSON(layer.data, {
                style: (feature) => layer.id === 'administrasi-area' ? { ...layer.style, fillColor: administrationColor(feature) } : layer.style,
                onEachFeature: (feature, featureLayer) => bindMapFeature(layer, feature, featureLayer),
            }).addTo(map);
        });
        map._pjuLayersLoaded = true;
    }

    function initPoleMap(id, poles, interactive) {
        const element = document.getElementById(id);
        if (!element || maps[id]) return;
        const map = L.map(element).setView([-7.965, 110.603], 11);
        maps[id] = map;
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);
        const bounds = [];
        poles.forEach((pole) => {
            const lat = Number(pole.latitude);
            const lng = Number(pole.longitude);
            if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;
            bounds.push([lat, lng]);
            const marker = L.marker([lat, lng], { icon: markerIcon(pole.condition) }).addTo(map);
            marker.bindPopup(`<strong>${escapeHtml(pole.customer_id)}</strong><br>${escapeHtml(pole.street_name)}<br>${pole.power_usage} W · ${escapeHtml(pole.condition)}`);
            marker.on('click', () => renderSelectedPole(pole));
        });
        if (bounds.length) map.fitBounds(bounds, { padding: [36, 36], maxZoom: interactive ? 12 : 13 });
    }

    function setupCoordinatePickers(scope = document) {
        scope.querySelectorAll('[data-coordinate-picker]').forEach((container) => {
            if (coordinatePickers.has(container.id)) return;
            coordinatePickers.add(container.id);

            const latInput = document.getElementById(container.dataset.latInput);
            const lngInput = document.getElementById(container.dataset.lngInput);
            const streetInput = document.getElementById(container.dataset.streetInput);
            const map = L.map(container).setView([Number(latInput.value || -7.965), Number(lngInput.value || 110.603)], 16);
            maps[container.id] = map;
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);
            const marker = L.marker([Number(latInput.value || -7.965), Number(lngInput.value || 110.603)], { draggable: true, icon: pinIcon() }).addTo(map);
            const setPosition = (latlng, shouldResolve = false) => {
                const lat = Number(latlng.lat.toFixed(7));
                const lng = Number(latlng.lng.toFixed(7));
                latInput.value = lat;
                lngInput.value = lng;
                marker.setLatLng([lat, lng]);
                if (shouldResolve && streetInput && !streetInput.value.trim()) reverseAddress(lat, lng).then((address) => { if (address) streetInput.value = address; });
            };
            map.on('click', (event) => setPosition(event.latlng, true));
            marker.on('dragend', () => setPosition(marker.getLatLng(), true));
            container.closest('dialog')?.addEventListener('toggle', () => setTimeout(() => map.invalidateSize(), 150));
        });
    }

    function renderSelectedPole(pole) {
        const panel = document.getElementById('selected-pole-panel');
        if (!panel) return;
        panel.innerHTML = `
            <div class="rounded-lg border border-white/10 bg-slate-900/5 p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xl font-bold text-slate-100">${escapeHtml(pole.customer_id)}</p>
                        <p class="mt-1 text-sm text-slate-400">${escapeHtml(pole.street_name)}</p>
                    </div>
                    <span class="badge badge-${pole.condition}">${formatOption(pole.condition)}</span>
                </div>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-3">
                ${detailBlock('Daya', `${pole.power_usage} W`)}
                ${detailBlock('Koordinat', `${Number(pole.latitude).toFixed(4)}, ${Number(pole.longitude).toFixed(4)}`)}
                ${detailBlock('Jenis', formatOption(pole.pole_type))}
                ${detailBlock('Tinggi', pole.height ? `${pole.height} m` : '-')}
                ${detailBlock('Bentangan', pole.span_length ? `${pole.span_length} m` : '-')}
                ${detailBlock('Pondasi', formatOption(pole.foundation_type))}
                ${detailBlock('Kepemilikan', formatOwnership(pole.ownership))}
                ${detailBlock('Trafo PLN', pole.pln_transformer || '-')}
            </div>
            ${pole.notes ? `<div class="mt-3 rounded-lg border bg-slate-900 p-3"><p class="text-xs font-bold uppercase tracking-wide text-slate-400">Keterangan</p><p class="mt-1 text-sm text-slate-300">${escapeHtml(pole.notes)}</p></div>` : ''}
            ${pole.photo_url ? `<img src="${pole.photo_url}" alt="Foto ${escapeHtml(pole.customer_id)}" class="mt-3 max-h-48 rounded-md border object-cover">` : ''}
        `;
    }

    function detailBlock(label, value) {
        return `<div class="rounded-lg border bg-slate-900 p-3"><p class="text-xs font-bold uppercase tracking-wide text-slate-400">${label}</p><p class="mt-1 text-sm font-bold text-slate-100">${escapeHtml(String(value))}</p></div>`;
    }

    function markerIcon(condition) {
        const color = markerColors[condition] || '#2563eb';
        return L.divIcon({ className: '', html: `<span style="display:block;width:18px;height:18px;border-radius:999px;background:${color};border:3px solid white;box-shadow:0 8px 20px rgba(15,23,42,.22)"></span>`, iconSize: [18, 18], iconAnchor: [9, 9] });
    }

    function pinIcon() {
        return L.divIcon({ className: '', html: '<span style="display:flex;width:24px;height:24px;align-items:center;justify-content:center;border-radius:999px;background:#2563eb;border:3px solid white;box-shadow:0 10px 24px rgba(15,23,42,.35)"><span style="display:block;width:6px;height:6px;border-radius:999px;background:white"></span></span>', iconSize: [24, 24], iconAnchor: [12, 12] });
    }

    async function reverseAddress(lat, lng) {
        try {
            const params = new URLSearchParams({ format: 'jsonv2', lat, lon: lng, zoom: 18, addressdetails: 1, 'accept-language': 'id' });
            const response = await fetch(`https://nominatim.openstreetmap.org/reverse?${params.toString()}`, { headers: { Accept: 'application/json' } });
            if (!response.ok) return '';
            const data = await response.json();
            return data.display_name || '';
        } catch {
            return '';
        }
    }

    function bindMapFeature(mapLayer, feature, layer) {
        if (layer.setStyle) {
            const base = mapLayer.id === 'administrasi-area' ? { ...mapLayer.style, fillColor: administrationColor(feature) } : mapLayer.style;
            const hover = { ...base, ...mapLayer.hover };
            layer.on({ mouseover: () => layer.setStyle(hover), mouseout: () => layer.setStyle(base) });
        }
        layer.bindPopup(createLayerPopup(mapLayer.label, feature.properties || {}));
    }

    function createLayerPopup(label, properties) {
        const name = prop(properties, 'namobj') || prop(properties, 'desa') || prop(properties, 'wadmkd') || label;
        const district = prop(properties, 'wadmkc') || prop(properties, 'kecamata_1');
        const regency = prop(properties, 'wadmkk') || prop(properties, 'kabupaten');
        return `<div class="min-w-48"><p class="text-xs font-bold uppercase tracking-wide text-slate-400">${escapeHtml(label)}</p><p class="text-base font-extrabold text-slate-100">${escapeHtml(name)}</p>${district ? `<p class="text-sm text-slate-700">Kecamatan ${escapeHtml(district)}</p>` : ''}${regency ? `<p class="text-sm text-slate-300">${escapeHtml(regency)}</p>` : ''}</div>`;
    }

    function administrationColor(feature) {
        const key = prop(feature.properties || {}, 'desa') || prop(feature.properties || {}, 'wadmkd') || JSON.stringify(feature.geometry?.type || 'administrasi');
        const palette = ['#7dd3fc','#93c5fd','#a5b4fc','#c4b5fd','#f0abfc','#f9a8d4','#fca5a5','#fdba74','#fde68a','#bef264','#86efac','#5eead4'];
        let hash = 0;
        for (let i = 0; i < key.length; i += 1) hash = (hash * 31 + key.charCodeAt(i)) >>> 0;
        return palette[hash % palette.length];
    }

    function prop(properties, key) {
        const matched = Object.keys(properties).find((item) => item.toLowerCase() === key.toLowerCase());
        return matched && properties[matched] != null ? String(properties[matched]).trim() : '';
    }

    function formatOption(value) {
        return String(value || '').replaceAll('_', ' ');
    }

    function formatOwnership(value) {
        return { dpu_kp: 'DPU KP', dishub: 'DISHUB', pln: 'PLN', lainnya: 'Lainnya' }[value] || formatOption(value);
    }

    function escapeHtml(value) {
        return String(value).replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;');
    }
</script>
@endsection
