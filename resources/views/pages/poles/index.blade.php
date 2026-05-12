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
    </style>
@endpush

@section('content')
@php
    $label = fn ($value) => ucfirst(str_replace('_', ' ', (string) $value));
    $ownership = fn ($value) => ['dpu_kp' => 'DPU KP', 'dishub' => 'DISHUB', 'pln' => 'PLN', 'lainnya' => 'Lainnya'][$value] ?? $label($value);
    $canDeletePoles = $user->role === 'superadmin';
@endphp

<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h2 class="text-2xl font-bold text-slate-100">Data Tiang PJU</h2>
        <p class="text-sm text-slate-400">Kelola inventaris tiang PJU dari database aplikasi.</p>
    </div>
    <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-row">
        <a id="poles-export-button" class="btn btn-muted min-w-0 px-3 text-xs sm:px-4 sm:text-sm" href="{{ route('poles.export', request()->except('page')) }}">
            <i data-lucide="download" class="icon"></i> Download Data
        </a>
        @if ($canManagePoles)
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

<div class="card p-4">
    <div class="grid gap-3 xl:grid-cols-[minmax(0,844px)_1fr] xl:items-center">
        <div class="grid gap-3 md:grid-cols-[minmax(0,560px)_180px_104px] md:items-center">
            <input class="field" id="poles-datatable-search" value="{{ $query }}" placeholder="Cari semua kolom data tiang...">
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
        <div class="flex justify-start xl:justify-end">
            <div class="inline-flex h-11 items-center gap-2 rounded-lg border border-white/10 bg-white/5 px-4 text-sm font-black text-slate-200">
                <i data-lucide="database" class="icon text-cyan-400"></i>
                <span id="poles-datatable-count">0 data</span>
            </div>
        </div>
    </div>
</div>

<div class="card overflow-hidden">
    <div class="overflow-x-auto p-0">
        <table id="poles-datatable" class="min-w-full divide-y divide-white/10 text-sm">
            <thead class="bg-white/5 text-left text-xs font-black uppercase tracking-wide text-slate-400">
                <tr>
                    @if ($canDeletePoles)
                        <th class="px-5 py-3">
                            <input id="poles-select-all" class="size-4 rounded border-white/20 bg-slate-900 text-cyan-500" type="checkbox" aria-label="Pilih semua data pada halaman ini">
                        </th>
                    @endif
                    <th class="px-5 py-3">Nomor Tiang Listrik</th>
                    <th class="px-5 py-3">Lokasi</th>
                    <th class="px-5 py-3">Daya Lampu</th>
                    <th class="px-5 py-3">Kondisi Tiang</th>
                    <th class="px-5 py-3">Jenis Tiang</th>
                    <th class="px-5 py-3">Tinggi Tiang</th>
                    <th class="px-5 py-3">Tipe Ornamen</th>
                    <th class="px-5 py-3">Jenis Lampu</th>
                    <th class="px-5 py-3">Status Lampu</th>
                    <th class="px-5 py-3">Kondisi Rumah Lampu</th>
                    <th class="px-5 py-3">Jumlah Titik Lampu</th>
                    <th class="px-5 py-3">KWH Meter</th>
                    <th class="px-5 py-3">Diinput</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@section('dialogs')
    @include('partials.pole-dialogs', ['dialogPoles' => collect(), 'allPoles' => $allPoles])

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
        const exportButton = document.getElementById('poles-export-button');
        const selectAllCheckbox = document.getElementById('poles-select-all');
        const bulkDeleteButton = document.getElementById('poles-bulk-delete-button');
        const bulkDeleteCount = document.getElementById('pole-bulk-delete-count');
        const bulkDeleteInputs = document.getElementById('pole-bulk-delete-inputs');
        const selectedPoleIds = new Set();
        const exportUrl = @json(route('poles.export'));
        const canDeletePoles = @json($canDeletePoles);
        const table = jQuery('#poles-datatable').DataTable({
            processing: true,
            serverSide: true,
            scrollX: true,
            searchDelay: 350,
            dom: 'rt<"flex flex-col gap-3 border-t border-white/10 px-5 py-4 md:flex-row md:items-center md:justify-between"lip>',
            ajax: {
                url: @json(route('poles.datatable')),
                data: function (data) {
                    data.search = data.search || {};
                    data.search.value = searchInput?.value || '';
                    data.year = yearSelect?.value || '';
                },
            },
            order: [[canDeletePoles ? 13 : 12, 'desc']],
            pageLength: 15,
            lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
            columns: [
                ...(canDeletePoles ? [{ data: 'select', name: 'select', orderable: false, searchable: false }] : []),
                { data: 'customer', name: 'customer_id', width: '220px' },
                { data: 'street_name', name: 'street_name' },
                { data: 'power_usage', name: 'power_usage' },
                { data: 'condition', name: 'condition' },
                { data: 'pole_type', name: 'pole_type' },
                { data: 'height', name: 'height' },
                { data: 'ornament_type', name: 'ornament_type' },
                { data: 'lamp_type', name: 'lamp_type' },
                { data: 'lamp_status', name: 'lamp_status' },
                { data: 'lamp_house_condition', name: 'lamp_house_condition' },
                { data: 'light_point_count', name: 'light_point_count' },
                { data: 'kwh_meter', name: 'kwh_meter' },
                { data: 'created_at', name: 'created_at' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
            ],
            columnDefs: [
                { targets: '_all', className: 'px-5 py-4 align-top text-slate-300' },
                { targets: [canDeletePoles ? 14 : 13], className: 'px-5 py-4 align-top text-right' },
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
                if (window.lucide) lucide.createIcons();
            },
        });

        const updateExportUrl = () => {
            if (!exportButton) return;
            const params = new URLSearchParams();
            const query = searchInput?.value?.trim() || '';
            if (query) params.set('query', query);
            if (yearSelect?.value) params.set('year', yearSelect.value);
            exportButton.href = `${exportUrl}${params.toString() ? `?${params.toString()}` : ''}`;
        };
        const updateResetButton = () => {
            resetButton?.classList.toggle('hidden', !(searchInput?.value?.trim()) && !yearSelect?.value);
            if (window.lucide) lucide.createIcons();
        };
        const redraw = debounce(() => {
            updateExportUrl();
            updateResetButton();
            table.ajax.reload();
        }, 300);

        searchInput?.addEventListener('input', redraw);
        yearSelect?.addEventListener('change', () => {
            updateExportUrl();
            updateResetButton();
            table.ajax.reload();
        });
        resetButton?.addEventListener('click', () => {
            if (!searchInput) return;
            searchInput.value = '';
            if (yearSelect) yearSelect.value = '';
            updateExportUrl();
            updateResetButton();
            table.ajax.reload();
            searchInput.focus();
        });
        document.getElementById('poles-datatable')?.addEventListener('change', (event) => {
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
    }

    function debounce(callback, delay) {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => callback(...args), delay);
        };
    }
@endsection
