@extends('layouts.dashboard', ['pageTitle' => 'Data Tiang - Inventarisasi APJ Non APP'])

@section('content')
@php
    $label = fn ($value) => ucfirst(str_replace('_', ' ', (string) $value));
    $ownership = fn ($value) => ['dpu_kp' => 'DPU KP', 'dishub' => 'DISHUB', 'pln' => 'PLN', 'lainnya' => 'Lainnya'][$value] ?? $label($value);
@endphp

<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h2 class="text-2xl font-bold text-slate-100">Data Tiang PJU</h2>
        <p class="text-sm text-slate-400">Kelola inventaris tiang PJU dari database aplikasi.</p>
    </div>
    <div class="flex flex-col gap-2 sm:flex-row">
        <a class="btn btn-muted" href="{{ route('poles.export', request()->except('page')) }}">
            <i data-lucide="download" class="icon"></i> Download Data
        </a>
        @if ($canManagePoles)
            <button type="button" class="btn btn-primary" data-open-dialog="pole-create-dialog">
                <i data-lucide="plus-circle" class="icon"></i> Tambah Tiang Baru
            </button>
        @endif
    </div>
</div>

@include('partials.search-filter', ['actionRoute' => 'poles.index'])
@include('partials.poles-table', ['poles' => $poles, 'showTimestamps' => true])
@endsection

@section('dialogs')
    @include('partials.pole-dialogs', ['dialogPoles' => $dialogPoles, 'allPoles' => $allPoles])
@endsection

@section('page-scripts')
    window.pjuCanManagePoles = @json($canManagePoles);
    @include('partials.pole-scripts')
@endsection
