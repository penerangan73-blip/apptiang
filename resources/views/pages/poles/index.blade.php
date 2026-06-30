@extends('layouts.dashboard', ['pageTitle' => 'Data Tiang - Inventarisasi APJ Non APP'])

@push('head')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <script defer src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script defer src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <style>
        .dataTables_wrapper { color: #cbd5e1; }
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate { color: #94a3b8 !important; font-size: 13px; font-weight: 700; }
        .dataTables_wrapper .dataTables_length select {
            border: 1px solid rgba(148, 163, 184, 0.34);
            border-radius: 8px;
            background: rgba(15, 23, 42, 0.92);
            color: #f8fafc;
            padding: 6px 28px 6px 10px;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border: 1px solid rgba(255,255,255,0.08) !important;
            border-radius: 8px !important;
            color: #cbd5e1 !important;
            margin-inline: 2px;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: rgba(6, 182, 212, 0.16) !important;
            color: #67e8f9 !important;
            border-color: rgba(34, 211, 238, 0.35) !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: rgba(255,255,255,0.08) !important;
            color: #f8fafc !important;
        }
        table.dataTable.no-footer { border-bottom: 0; }
        table.dataTable thead th,
        table.dataTable thead td { border-bottom: 1px solid rgba(255,255,255,0.1); }
        table.dataTable tbody tr { background: transparent; }
        #poles-datatable,
        .dataTables_scrollHeadInner,
        .dataTables_scrollHeadInner table { min-width: 2180px; }
        #poles-datatable { width: 100% !important; }
        #poles-datatable th,
        #poles-datatable td { white-space: nowrap; }
        #poles-datatable .pju-location-cell {
            display: block;
            min-width: 240px;
            max-width: 340px;
            white-space: normal;
            line-height: 1.35;
        }
        #poles-datatable .pju-verification-cell { min-width: 132px; }
        #poles-datatable .pju-actions-cell {
            min-width: 194px;
            white-space: nowrap;
        }
        #poles-datatable .pju-action-button { white-space: nowrap; }
    </style>
@endpush

@section('content')
@php
    $label = fn ($value) => ucfirst(str_replace('_', ' ', (string) $value));
    $ownership = fn ($value) => ['dpu_kp' => 'DPU KP', 'dishub' => 'DISHUB', 'pln' => 'PLN', 'lainnya' => 'Lainnya'][$value] ?? $label($value);
    $canCreatePoles = $canCreatePoles ?? in_array($user->role, ['superadmin', 'surveyor'], true);
    $canDeletePoles = in_array($user->role, ['superadmin', 'surveyor', 'verifikator'], true);
    $optionLabel = fn ($value) => [
        'keropos' => 'Keropos',
        'miring' => 'Miring',
        'berkarat' => 'Berkarat',
        'kokoh' => 'Kokoh',
        'lainnya' => 'Lainnya',
        'tiang_baja_galvanis' => 'Tiang Baja (Galvanis)',
        'tiang_beton' => 'Tiang Beton',
        'tiang_kayu' => 'Kayu',
        'tiang_bambu' => 'Bambu',
        'menempel_tiang_pln' => 'Menempel Tiang PLN',
        'menempel_pohon' => 'Menempel Pohon',
        'tanpa_penyangga' => 'Tanpa Penyangga',
        'led' => 'LED',
        'cfl' => 'CFL',
        'hps' => 'HPS (Sodium Tekanan Tinggi)',
        'son_t' => 'SON-T',
        'mercury' => 'Mercury',
        'menyala' => 'Menyala',
        'redup' => 'Redup',
        'berkedip' => 'Berkedip',
        'mati' => 'Mati',
        'rusak_pecah' => 'Rusak Pecah',
        'tidak_ada_lampu' => 'Tidak Ada Lampu',
        'caping' => 'Caping',
        'konvensional' => 'Konvensional',
        'tidak_ada' => 'Tidak Ada',
        'baik' => 'Baik',
        'rusak' => 'Rusak',
        'sudah_ada' => 'Sudah Ada',
        'belum' => 'Belum',
        'dpu_kp' => 'DPU KP',
        'dishub' => 'DISHUB',
        'pln' => 'PLN',
    ][$value] ?? $label($value);
    $attributeFilters = [
        'condition' => ['label' => 'Kondisi', 'options' => ['keropos', 'miring', 'berkarat', 'kokoh', 'lainnya']],
        'village' => ['label' => 'Desa', 'options' => $attributeOptions['village'] ?? collect()],
        'district' => ['label' => 'Kecamatan', 'options' => $attributeOptions['district'] ?? collect()],
        'pole_type' => ['label' => 'Jenis Tiang', 'options' => ['tiang_baja_galvanis', 'tiang_beton', 'tiang_kayu', 'tiang_bambu', 'menempel_tiang_pln', 'menempel_pohon', 'tanpa_penyangga']],
        'lamp_type' => ['label' => 'Jenis Lampu', 'options' => ['led', 'cfl', 'hps', 'son_t', 'mercury', 'lainnya']],
        'lamp_status' => ['label' => 'Status Lampu', 'options' => ['menyala', 'redup', 'berkedip', 'mati', 'rusak_pecah', 'tidak_ada_lampu']],
        'ownership' => ['label' => 'Kepemilikan', 'options' => ['dpu_kp', 'dishub', 'pln', 'lainnya']],
        'kwh_meter' => ['label' => 'KWH Meter', 'options' => ['sudah_ada', 'belum']],
    ];
    $filterColumns = [
        'customer_id' => 'Nomor Tiang',
        'address' => 'Lokasi',
        'village' => 'Desa',
        'district' => 'Kecamatan',
        'regency' => 'Kabupaten',
        'province' => 'Provinsi',
        'latitude' => 'Latitude',
        'longitude' => 'Longitude',
        'power_usage' => 'Daya Lampu',
        'condition' => 'Kondisi Tiang',
        'pole_type' => 'Jenis Tiang',
        'height' => 'Tinggi Tiang',
        'ornament_type' => 'Tipe Ornamen',
        'lamp_type' => 'Jenis Lampu',
        'lamp_status' => 'Kondisi Lampu',
        'lamp_house_type' => 'Jenis Rumah Lampu',
        'lamp_house_condition' => 'Kondisi Rumah Lampu',
        'light_point_count' => 'Jumlah Titik Lampu',
        'kwh_meter' => 'KWH Meter',
        'ownership' => 'Kepemilikan',
        'created_at' => 'Tanggal Input',
        'created_by' => 'Diinput Oleh',
        'updated_at' => 'Tanggal Update',
        'updated_by' => 'Update Oleh',
        'notes' => 'Catatan',
    ];
@endphp

<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h2 class="text-2xl font-bold text-slate-100">Data Tiang PJU</h2>
        <p class="text-sm text-slate-400">Kelola inventaris tiang PJU dari database aplikasi.</p>
    </div>
    <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-row sm:items-center">
        <div class="col-span-2 inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-cyan-400/20 bg-cyan-400/10 px-4 text-sm font-black text-cyan-100 sm:col-span-1">
            <i data-lucide="database" class="icon text-cyan-300"></i>
            <span id="poles-datatable-count">0 data</span>
        </div>
        <div class="relative">
            <button type="button" id="poles-download-toggle" class="btn btn-muted min-w-0 px-3 text-xs sm:px-4 sm:text-sm" aria-expanded="false" aria-controls="poles-download-menu">
                <i data-lucide="download" class="icon"></i>
                Download
                <i data-lucide="chevron-down" class="icon"></i>
            </button>
            <div id="poles-download-menu" class="absolute left-0 z-[1000] mt-2 hidden w-52 overflow-hidden rounded-lg border border-white/10 bg-slate-950/95 p-1 shadow-2xl backdrop-blur sm:left-auto sm:right-0">
                <a id="poles-export-xls-link" class="flex items-center gap-2 rounded-md px-3 py-2 text-xs font-bold text-slate-200 transition hover:bg-white/10" href="{{ route('poles.export', request()->except('page')) }}">
                    <i data-lucide="file-spreadsheet" class="icon"></i>
                    Excel (.xls)
                </a>
                <a id="poles-export-geojson-link" class="flex items-center gap-2 rounded-md px-3 py-2 text-xs font-bold text-slate-200 transition hover:bg-white/10" href="{{ route('map.export', ['format' => 'geojson'] + request()->only(['query', 'condition'])) }}">
                    <i data-lucide="braces" class="icon"></i>
                    GeoJSON
                </a>
                <a id="poles-export-shp-link" class="flex items-center gap-2 rounded-md px-3 py-2 text-xs font-bold text-slate-200 transition hover:bg-white/10" href="{{ route('map.export', ['format' => 'shp'] + request()->only(['query', 'condition'])) }}">
                    <i data-lucide="package" class="icon"></i>
                    SHP (.zip)
                </a>
                <a id="poles-export-kml-link" class="flex items-center gap-2 rounded-md px-3 py-2 text-xs font-bold text-slate-200 transition hover:bg-white/10" href="{{ route('map.export', ['format' => 'kml'] + request()->only(['query', 'condition'])) }}">
                    <i data-lucide="file-code-2" class="icon"></i>
                    KML
                </a>
            </div>
        </div>
        @if ($canCreatePoles)
            @if ($canDeletePoles)
                <button type="button" id="poles-bulk-delete-button" class="btn btn-danger hidden min-w-0 px-3 text-xs sm:px-4 sm:text-sm" data-open-dialog="pole-bulk-delete-dialog">
                    <i data-lucide="trash-2" class="icon"></i>
                    Hapus Terpilih
                </button>
            @endif
            <button type="button" class="btn btn-primary min-w-0 px-3 text-xs sm:px-4 sm:text-sm" data-open-dialog="pole-create-dialog">
                <i data-lucide="plus-circle" class="icon"></i> Tambah Tiang Baru
            </button>
        @endif
    </div>
</div>

<div class="card" style="overflow: visible;">
    <button type="button" id="poles-filter-toggle" class="flex w-full items-center justify-between gap-3 p-4 text-left font-black text-slate-100 md:hidden" aria-expanded="false" aria-controls="poles-filter-panel">
        <span class="flex items-center gap-2">
            <i data-lucide="sliders-horizontal" class="icon text-cyan-400"></i>
            Filter
        </span>
        <i data-lucide="chevron-down" class="icon" id="poles-filter-toggle-icon"></i>
    </button>
    <div id="poles-filter-panel" class="hidden grid gap-3 border-t border-white/10 p-4 md:grid md:border-t-0">
        <div class="space-y-3">
            <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_220px_180px_104px] md:items-center">
                <input class="field" id="poles-datatable-search" value="{{ $query }}" placeholder="Cari semua kolom data tiang...">
                <div class="relative" id="poles-column-filter-wrap">
                    <button type="button" id="poles-column-filter-toggle" class="field flex h-11 w-full items-center justify-between gap-2 text-left text-sm font-bold" aria-expanded="false" aria-controls="poles-column-filter-menu">
                        <span id="poles-column-filter-label">Semua kolom</span>
                        <i data-lucide="chevron-down" class="size-4"></i>
                    </button>
                    <div id="poles-column-filter-menu" class="absolute left-0 right-0 z-[1000] mt-2 hidden max-h-[min(320px,55vh)] overflow-y-auto rounded-lg border border-white/10 bg-slate-950 p-3 shadow-2xl sm:right-auto sm:w-80">
                        <div class="grid gap-2">
                            <label class="flex cursor-pointer items-center gap-2 rounded-md px-3 py-2 text-sm font-bold text-slate-200 transition hover:bg-white/10">
                                <input type="checkbox" class="size-4 rounded border-white/20 bg-slate-900 text-cyan-500" id="poles-column-filter-all" checked>
                                Semua kolom
                            </label>
                            <div class="border-t border-white/10"></div>
                            @foreach ($filterColumns as $value => $text)
                                <label class="flex cursor-pointer items-center gap-2 rounded-md px-3 py-2 text-sm font-semibold text-slate-300 transition hover:bg-white/10">
                                    <input type="checkbox" class="poles-column-filter-option size-4 rounded border-white/20 bg-slate-900 text-cyan-500" value="{{ $value }}">
                                    {{ $text }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <select class="field" id="poles-datatable-year">
                    <option value="">Semua tahun</option>
                    @foreach ($years as $optionYear)
                        <option value="{{ $optionYear }}" @selected((string) $year === (string) $optionYear)>{{ $optionYear }}</option>
                    @endforeach
                </select>
                <button type="button" id="poles-datatable-reset" class="btn btn-muted hidden h-11 w-full justify-center px-3 text-xs md:w-[104px]">
                    <i data-lucide="x-circle" class="icon"></i>
                    Reset
                </button>
            </div>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                @foreach ($attributeFilters as $name => $filter)
                    <select class="field h-11 text-sm" name="{{ $name }}" data-poles-attribute-filter>
                        <option value="">Semua {{ $filter['label'] }}</option>
                        @foreach ($filter['options'] as $option)
                            <option value="{{ $option }}" @selected((string) request($name) === (string) $option)>{{ $optionLabel($option) }}</option>
                        @endforeach
                    </select>
                @endforeach
            </div>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-white/10 pt-3">
            <div class="flex min-w-0 items-center gap-2 text-sm font-bold text-slate-400">
                <i data-lucide="sliders-horizontal" class="icon text-cyan-400"></i>
                <span id="poles-filter-summary">Semua data</span>
            </div>
        </div>
    </div>
</div>

<div class="card overflow-hidden">
    <div class="overflow-x-auto p-0">
        <table id="poles-datatable" class="min-w-[2180px] divide-y divide-white/10 text-sm">
            <thead class="bg-white/5 text-left text-xs font-black uppercase tracking-wide text-slate-400">
                <tr>
                    @if ($canDeletePoles)
                        <th class="px-5 py-3">
                            <input id="poles-select-all" class="size-4 rounded border-white/20 bg-slate-900 text-cyan-500" type="checkbox" aria-label="Pilih semua data pada halaman ini">
                        </th>
                    @endif
                    <th class="px-5 py-3">Nomor Tiang Listrik</th>
                    <th class="px-5 py-3">Lokasi</th>
                    <th class="px-5 py-3">Desa</th>
                    <th class="px-5 py-3">Kecamatan</th>
                    <th class="px-5 py-3">Kabupaten</th>
                    <th class="px-5 py-3">Daya Lampu</th>
                    <th class="px-5 py-3">Kondisi Tiang</th>
                    <th class="px-5 py-3">Jenis Tiang</th>
                    <th class="px-5 py-3">Tinggi Tiang</th>
                    <th class="px-5 py-3">Tipe Ornamen</th>
                    <th class="px-5 py-3">Jenis Lampu</th>
                    <th class="px-5 py-3">Kondisi Lampu</th>
                    <th class="px-5 py-3">Jenis Rumah Lampu</th>
                    <th class="px-5 py-3">Kondisi Rumah Lampu</th>
                    <th class="px-5 py-3">Jumlah Titik Lampu</th>
                    <th class="px-5 py-3">KWH Meter</th>
                    <th class="px-5 py-3">Verifikasi</th>
                    <th class="px-5 py-3">Diinput</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@section('dialogs')
    @include('partials.pole-dialogs', ['dialogPoles' => collect(), 'allPoles' => $allPoles, 'sourcePoles' => $sourcePoles])

    @if ($canDeletePoles)
        <dialog id="pole-datatable-delete-dialog">
            <div class="modal-card max-w-lg">
                <div class="border-b border-white/10 p-5">
                    <div class="flex items-start gap-4">
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-xl border border-red-500/30 bg-red-500/10 text-red-400">
                            <i data-lucide="alert-triangle" class="icon"></i>
                        </div>
                        <div class="min-w-0">
                            <h2 class="text-xl font-black text-slate-100">Hapus Data Tiang?</h2>
                            <p class="mt-1 text-sm leading-6 text-slate-400">Data nomor tiang <span id="pole-datatable-delete-code" class="font-bold text-slate-200">-</span> akan dihapus. Laporan yang terkait dengan tiang ini juga dapat ikut terhapus.</p>
                        </div>
                    </div>
                </div>
                <div class="p-5">
                    <div class="rounded-xl border border-white/10 bg-white/5 p-4 text-sm text-slate-300">
                        <p id="pole-datatable-delete-number" class="font-bold text-slate-100">-</p>
                        <p id="pole-datatable-delete-street" class="mt-1">-</p>
                    </div>
                    <div class="mt-5 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button type="button" class="btn btn-muted" data-close-dialog>Batal</button>
                        <form id="pole-datatable-delete-form" method="POST" action="#">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger w-full sm:w-auto" type="submit">
                                <i data-lucide="trash-2" class="icon"></i>
                                Ya, Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </dialog>

        <dialog id="pole-bulk-delete-dialog">
            <div class="modal-card max-w-lg">
                <div class="border-b border-white/10 p-5">
                    <div class="flex items-start gap-4">
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-xl border border-red-500/30 bg-red-500/10 text-red-400">
                            <i data-lucide="alert-triangle" class="icon"></i>
                        </div>
                        <div class="min-w-0">
                            <h2 class="text-xl font-black text-slate-100">Hapus Data Terpilih?</h2>
                            <p class="mt-1 text-sm leading-6 text-slate-400"><span id="pole-bulk-delete-count" class="font-bold text-slate-200">0</span> data tiang akan dihapus. Semua laporan dan foto yang terkait juga ikut terhapus.</p>
                        </div>
                    </div>
                </div>
                <div class="p-5">
                    <div class="rounded-xl border border-red-500/20 bg-red-500/10 p-4 text-sm leading-6 text-red-100">
                        Pastikan data yang dipilih sudah benar. Proses ini tidak bisa dibatalkan dari aplikasi.
                    </div>
                    <div class="mt-5 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button type="button" class="btn btn-muted" data-close-dialog>Batal</button>
                        <form id="pole-bulk-delete-form" method="POST" action="{{ route('poles.bulk-destroy') }}">
                            @csrf
                            @method('DELETE')
                            <div id="pole-bulk-delete-inputs"></div>
                            <button class="btn btn-danger w-full sm:w-auto" type="submit">
                                <i data-lucide="trash-2" class="icon"></i>
                                Ya, Hapus Semua
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </dialog>
    @endif
@endsection

@section('page-scripts')
    window.pjuCanManagePoles = @json($canManagePoles);
    @include('partials.pole-scripts')

    initPolesDataTable();

    function initPolesDataTable() {
        if (!window.jQuery || !jQuery.fn?.DataTable) {
            console.error('DataTables belum dimuat.');
            return;
        }

        const searchInput = document.getElementById('poles-datatable-search');
        const yearSelect = document.getElementById('poles-datatable-year');
        const resetButton = document.getElementById('poles-datatable-reset');
        const countLabel = document.getElementById('poles-datatable-count');
        const filterSummary = document.getElementById('poles-filter-summary');
        const filterToggle = document.getElementById('poles-filter-toggle');
        const filterPanel = document.getElementById('poles-filter-panel');
        const filterToggleIcon = document.getElementById('poles-filter-toggle-icon');
        const downloadToggle = document.getElementById('poles-download-toggle');
        const downloadMenu = document.getElementById('poles-download-menu');
        const exportLinks = {
            xls: document.getElementById('poles-export-xls-link'),
            geojson: document.getElementById('poles-export-geojson-link'),
            shp: document.getElementById('poles-export-shp-link'),
            kml: document.getElementById('poles-export-kml-link'),
        };
        const columnFilterToggle = document.getElementById('poles-column-filter-toggle');
        const columnFilterMenu = document.getElementById('poles-column-filter-menu');
        const columnFilterLabel = document.getElementById('poles-column-filter-label');
        const columnFilterAll = document.getElementById('poles-column-filter-all');
        const columnFilterOptions = Array.from(document.querySelectorAll('.poles-column-filter-option'));
        const attributeFilterInputs = Array.from(document.querySelectorAll('[data-poles-attribute-filter]'));
        const selectAllCheckbox = document.getElementById('poles-select-all');
        const bulkDeleteButton = document.getElementById('poles-bulk-delete-button');
        const bulkDeleteCount = document.getElementById('pole-bulk-delete-count');
        const bulkDeleteInputs = document.getElementById('pole-bulk-delete-inputs');
        const selectedPoleIds = new Set();
        const exportUrls = {
            xls: @json(route('poles.export')),
            geojson: @json(route('map.export', ['format' => 'geojson'])),
            shp: @json(route('map.export', ['format' => 'shp'])),
            kml: @json(route('map.export', ['format' => 'kml'])),
        };
        const canDeletePoles = @json($canDeletePoles);
        const table = jQuery('#poles-datatable').DataTable({
            processing: true,
            serverSide: true,
            scrollX: true,
            autoWidth: false,
            searchDelay: 350,
            dom: 'rt<"flex flex-col gap-3 border-t border-white/10 px-5 py-4 md:flex-row md:items-center md:justify-between"lip>',
            ajax: {
                url: @json(route('poles.datatable')),
                data: function (data) {
                    data.search = data.search || {};
                    data.search.value = searchInput?.value || '';
                    data.search_columns = getSelectedSearchColumns();
                    data.year = yearSelect?.value || '';
                    Object.assign(data, getAttributeFilters());
                },
            },
            order: [[canDeletePoles ? 18 : 17, 'desc']],
            pageLength: 15,
            lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
            columns: [
                ...(canDeletePoles ? [{ data: 'select', name: 'select', orderable: false, searchable: false }] : []),
                { data: 'customer', name: 'customer_id', width: '220px' },
                { data: 'street_name', name: 'street_name', width: '280px' },
                { data: 'village', name: 'village', width: '130px' },
                { data: 'district', name: 'district', width: '140px' },
                { data: 'regency', name: 'regency', width: '140px' },
                { data: 'power_usage', name: 'power_usage', width: '120px' },
                { data: 'condition', name: 'condition', width: '130px' },
                { data: 'pole_type', name: 'pole_type', width: '160px' },
                { data: 'height', name: 'height', width: '110px' },
                { data: 'ornament_type', name: 'ornament_type', width: '150px' },
                { data: 'lamp_type', name: 'lamp_type', width: '130px' },
                { data: 'lamp_status', name: 'lamp_status', width: '150px' },
                { data: 'lamp_house_type', name: 'lamp_house_type', width: '160px' },
                { data: 'lamp_house_condition', name: 'lamp_house_condition', width: '170px' },
                { data: 'light_point_count', name: 'light_point_count', width: '150px' },
                { data: 'kwh_meter', name: 'kwh_meter', width: '120px' },
                { data: 'verification', name: 'is_verified', width: '150px' },
                { data: 'created_at', name: 'created_at', width: '140px' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false, width: '210px' },
            ],
            columnDefs: [
                { targets: '_all', className: 'px-5 py-4 align-top text-slate-300' },
                { targets: [canDeletePoles ? 19 : 18], className: 'px-5 py-4 align-top text-right' },
            ],
            language: {
                emptyTable: 'Tidak ada tiang untuk filter ini.',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                infoEmpty: 'Menampilkan 0 data',
                infoFiltered: '(difilter dari _MAX_ total data)',
                lengthMenu: 'Tampilkan _MENU_',
                loadingRecords: 'Memuat...',
                processing: 'Memuat data...',
                zeroRecords: 'Tidak ada data yang cocok.',
                paginate: {
                    first: 'Pertama',
                    last: 'Terakhir',
                    next: 'Berikutnya',
                    previous: 'Sebelumnya',
                },
            },
            drawCallback: function () {
                const info = this.api().page.info();
                if (countLabel) countLabel.textContent = `${Number(info.recordsDisplay || 0).toLocaleString('id-ID')} data`;
                syncVisibleSelection();
                this.api().columns.adjust();
                if (window.lucide) lucide.createIcons();
            },
            initComplete: function () {
                this.api().columns.adjust();
            },
        });

        const updateExportUrl = () => {
            const params = new URLSearchParams();
            const query = searchInput?.value?.trim() || '';
            const selectedColumns = getSelectedSearchColumns();
            if (query) params.set('query', query);
            selectedColumns.forEach((column) => params.append('search_columns[]', column));
            if (yearSelect?.value) params.set('year', yearSelect.value);
            Object.entries(getAttributeFilters()).forEach(([field, value]) => params.set(field, value));

            Object.entries(exportLinks).forEach(([format, link]) => {
                if (!link) return;
                link.href = `${exportUrls[format]}${params.toString() ? `?${params.toString()}` : ''}`;
            });
        };
        const updateResetButton = () => {
            resetButton?.classList.toggle('hidden', !(searchInput?.value?.trim()) && !yearSelect?.value && getSelectedSearchColumns().length === 0 && Object.keys(getAttributeFilters()).length === 0);
            if (window.lucide) lucide.createIcons();
        };
        const updateFilterSummary = () => {
            if (!filterSummary) return;
            const activeFilters = [
                searchInput?.value?.trim() ? 'pencarian' : '',
                yearSelect?.value ? 'tahun' : '',
                getSelectedSearchColumns().length ? 'kolom' : '',
                ...attributeFilterInputs.filter((input) => input.value).map((input) => input.options[input.selectedIndex]?.text || input.name),
            ].filter(Boolean);

            filterSummary.textContent = activeFilters.length
                ? `${activeFilters.length} filter aktif`
                : 'Semua data';
        };
        const redraw = debounce(() => {
            updateExportUrl();
            updateResetButton();
            updateFilterSummary();
            table.ajax.reload();
        }, 300);

        searchInput?.addEventListener('input', redraw);
        attributeFilterInputs.forEach((input) => input.addEventListener('change', () => {
            updateExportUrl();
            updateResetButton();
            updateFilterSummary();
            table.ajax.reload();
        }));
        setupFilterPanel();
        setupDownloadMenu();
        setupColumnFilter();
        yearSelect?.addEventListener('change', () => {
            updateExportUrl();
            updateResetButton();
            updateFilterSummary();
            table.ajax.reload();
        });
        resetButton?.addEventListener('click', () => {
            if (!searchInput) return;
            searchInput.value = '';
            if (yearSelect) yearSelect.value = '';
            attributeFilterInputs.forEach((input) => {
                input.value = '';
            });
            resetColumnFilter();
            updateExportUrl();
            updateResetButton();
            updateFilterSummary();
            table.ajax.reload();
            searchInput.focus();
        });
        document.getElementById('poles-datatable')?.addEventListener('change', (event) => {
            const verificationCheckbox = event.target.closest('[data-pole-verification-url]');
            if (verificationCheckbox) {
                updatePoleVerification(verificationCheckbox);
                return;
            }

            const checkbox = event.target.closest('.pju-pole-select');
            if (!checkbox) return;

            if (checkbox.checked) {
                selectedPoleIds.add(checkbox.value);
            } else {
                selectedPoleIds.delete(checkbox.value);
            }

            refreshBulkDeleteState();
        });
        selectAllCheckbox?.addEventListener('change', () => {
            document.querySelectorAll('.pju-pole-select').forEach((checkbox) => {
                checkbox.checked = selectAllCheckbox.checked;
                if (checkbox.checked) {
                    selectedPoleIds.add(checkbox.value);
                } else {
                    selectedPoleIds.delete(checkbox.value);
                }
            });
            refreshBulkDeleteState();
        });
        bulkDeleteButton?.addEventListener('click', (event) => {
            if (!selectedPoleIds.size) {
                event.preventDefault();
                event.stopPropagation();
                return;
            }

            if (bulkDeleteCount) bulkDeleteCount.textContent = Number(selectedPoleIds.size).toLocaleString('id-ID');
            if (bulkDeleteInputs) {
                bulkDeleteInputs.innerHTML = Array.from(selectedPoleIds)
                    .map((id) => `<input type="hidden" name="ids[]" value="${escapeHtml(id)}">`)
                    .join('');
            }
        });
        updateExportUrl();
        updateResetButton();
        updateFilterSummary();
        syncColumnFilterLabel();

        function setupFilterPanel() {
            filterToggle?.addEventListener('click', () => {
                const isOpen = !filterPanel?.classList.contains('hidden');
                filterPanel?.classList.toggle('hidden', isOpen);
                filterToggle.setAttribute('aria-expanded', String(!isOpen));
                filterToggleIcon?.classList.toggle('rotate-180', !isOpen);
            });
        }

        function setupDownloadMenu() {
            downloadToggle?.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                const isOpen = !downloadMenu?.classList.contains('hidden');
                downloadMenu?.classList.toggle('hidden', isOpen);
                downloadToggle.setAttribute('aria-expanded', String(!isOpen));
            });

            downloadMenu?.addEventListener('click', (event) => {
                event.stopPropagation();
            });

            document.addEventListener('click', (event) => {
                if (!downloadMenu || !downloadToggle) return;
                if (downloadToggle.contains(event.target) || downloadMenu.contains(event.target)) return;
                downloadMenu.classList.add('hidden');
                downloadToggle.setAttribute('aria-expanded', 'false');
            });
        }

        function setupColumnFilter() {
            columnFilterToggle?.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                const isOpen = !columnFilterMenu?.classList.contains('hidden');
                columnFilterMenu?.classList.toggle('hidden', isOpen);
                columnFilterToggle.setAttribute('aria-expanded', String(!isOpen));
            });

            columnFilterMenu?.addEventListener('click', (event) => {
                event.stopPropagation();
            });

            document.addEventListener('click', (event) => {
                if (!columnFilterMenu || !columnFilterToggle) return;
                if (columnFilterToggle.contains(event.target) || columnFilterMenu.contains(event.target)) return;
                columnFilterMenu.classList.add('hidden');
                columnFilterToggle.setAttribute('aria-expanded', 'false');
            });

            columnFilterAll?.addEventListener('change', () => {
                if (columnFilterAll.checked) {
                    columnFilterOptions.forEach((option) => {
                        option.checked = false;
                    });
                } else if (!getSelectedSearchColumns().length) {
                    columnFilterAll.checked = true;
                }

                syncColumnFilterLabel();
                updateFilterSummary();
                redraw();
            });

            columnFilterOptions.forEach((option) => {
                option.addEventListener('change', () => {
                    if (option.checked && columnFilterAll) {
                        columnFilterAll.checked = false;
                    }

                    if (!getSelectedSearchColumns().length && columnFilterAll) {
                        columnFilterAll.checked = true;
                    }

                    syncColumnFilterLabel();
                    updateFilterSummary();
                    redraw();
                });
            });
        }

        function getSelectedSearchColumns() {
            if (columnFilterAll?.checked) return [];

            return columnFilterOptions
                .filter((option) => option.checked)
                .map((option) => option.value);
        }

        function getAttributeFilters() {
            return Object.fromEntries(attributeFilterInputs
                .map((input) => [input.name, input.value])
                .filter(([, value]) => value));
        }

        function syncColumnFilterLabel() {
            if (!columnFilterLabel) return;
            const selectedColumns = getSelectedSearchColumns();
            columnFilterLabel.textContent = selectedColumns.length
                ? `${selectedColumns.length} kolom`
                : 'Semua kolom';
        }

        function resetColumnFilter() {
            if (columnFilterAll) columnFilterAll.checked = true;
            columnFilterOptions.forEach((option) => {
                option.checked = false;
            });
            syncColumnFilterLabel();
        }

        function syncVisibleSelection() {
            document.querySelectorAll('.pju-pole-select').forEach((checkbox) => {
                checkbox.checked = selectedPoleIds.has(checkbox.value);
            });
            refreshBulkDeleteState();
        }

        function refreshBulkDeleteState() {
            bulkDeleteButton?.classList.toggle('hidden', selectedPoleIds.size === 0);
            if (bulkDeleteButton) {
                bulkDeleteButton.innerHTML = `<i data-lucide="trash-2" class="icon"></i> Hapus ${Number(selectedPoleIds.size).toLocaleString('id-ID')}`;
            }

            const visibleCheckboxes = Array.from(document.querySelectorAll('.pju-pole-select'));
            const checkedVisibleCount = visibleCheckboxes.filter((checkbox) => checkbox.checked).length;

            if (selectAllCheckbox) {
                selectAllCheckbox.checked = visibleCheckboxes.length > 0 && checkedVisibleCount === visibleCheckboxes.length;
                selectAllCheckbox.indeterminate = checkedVisibleCount > 0 && checkedVisibleCount < visibleCheckboxes.length;
            }

            if (window.lucide) lucide.createIcons();
        }

        async function updatePoleVerification(checkbox) {
            checkbox.disabled = true;
            const isVerified = checkbox.checked;

            try {
                const response = await fetch(checkbox.dataset.poleVerificationUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify({ is_verified: isVerified }),
                });

                if (!response.ok) throw new Error('Verifikasi gagal disimpan.');
                table.ajax.reload(null, false);
            } catch (error) {
                checkbox.checked = !isVerified;
                checkbox.disabled = false;
                alert(error.message || 'Verifikasi gagal disimpan.');
            }
        }
    }

    function debounce(callback, delay) {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => callback(...args), delay);
        };
    }
@endsection
