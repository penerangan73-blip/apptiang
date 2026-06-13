@php
    $poleValue = fn ($field, $fallback = '') => old($field, $pole?->{$field} ?? $fallback);
    $prefix = $prefix ?? 'pole';
    $routeType = old('route_type', $pole?->sub_name ? 'sub' : ($pole?->customer_id ? 'sumber' : ''));
    $sourcePoles = $sourcePoles ?? collect();
    $selectedCustomerId = old('customer_id', $pole?->customer_id ?? '');
    $selectedSubName = old('sub_name', $pole?->sub_name ?? '');
    $availableSourcePoles = $sourcePoles;
    $hasSelectedSourceOption = $availableSourcePoles->contains(fn ($sourcePole) => $sourcePole->customer_id === $selectedCustomerId);
@endphp

<div class="md:col-span-2" data-route-type-group>
    <label class="label">Tipe Jalur</label>
    <div class="grid grid-cols-2 gap-2 rounded-lg border border-white/10 bg-slate-950/60 p-1">
        @foreach (['sumber' => 'Sumber', 'sub' => 'Sub'] as $value => $text)
            <label class="cursor-pointer">
                <input class="peer sr-only" type="radio" name="route_type" value="{{ $value }}" required data-route-type-option @checked($routeType === $value)>
                <span class="flex h-11 items-center justify-center rounded-md border border-transparent px-3 text-sm font-black text-slate-300 transition peer-checked:border-cyan-400/70 peer-checked:bg-cyan-500/20 peer-checked:text-cyan-100 peer-focus-visible:ring-2 peer-focus-visible:ring-cyan-400/60">
                    {{ $text }}
                </span>
            </label>
        @endforeach
    </div>
</div>
<div id="{{ $prefix }}-source-input-wrap" data-route-source-input-wrap>
    <label class="label">Nomor Tiang (Sumber)</label>
    <input class="field" name="customer_id" value="{{ $selectedCustomerId }}" required data-route-source-input>
</div>
<div id="{{ $prefix }}-source-select-wrap" data-route-source-select-wrap>
    <label class="label">Nomor Tiang Sumber</label>
    <div class="relative" data-route-source-combobox>
        <button type="button" class="field flex h-11 items-center justify-between gap-3 text-left" data-route-source-toggle aria-expanded="false">
            <span class="min-w-0 truncate" data-route-source-label>Pilih sumber</span>
            <i data-lucide="chevron-down" class="size-4 shrink-0 text-cyan-300"></i>
        </button>
        <div class="absolute left-0 right-0 z-[1200] mt-1 hidden overflow-hidden rounded-lg border border-white/10 bg-slate-950 shadow-2xl" data-route-source-menu>
            <div class="border-b border-white/10 p-2">
                <div class="relative">
                    <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-500"></i>
                    <input class="field h-10 py-2 pl-10" type="search" autocomplete="off" placeholder="Search..." data-route-source-search>
                </div>
            </div>
            <div class="max-h-56 overflow-y-auto py-1" data-route-source-options></div>
            <p class="hidden px-3 py-3 text-sm font-semibold text-amber-300" data-route-source-empty>Tidak ada nomor tiang yang cocok.</p>
        </div>
    </div>
    <select class="sr-only" name="customer_id" required disabled data-route-source-select>
        <option value="">Pilih sumber</option>
        @if ($selectedCustomerId && ! $hasSelectedSourceOption)
            <option value="{{ $selectedCustomerId }}" data-next-sub="{{ $selectedSubName ?: \App\Models\Pole::nextSubNameForCustomer($selectedCustomerId) }}" selected>
                {{ $selectedCustomerId }}
            </option>
        @endif
        @foreach ($availableSourcePoles as $sourcePole)
            <option value="{{ $sourcePole->customer_id }}" data-next-sub="{{ \App\Models\Pole::nextSubNameForCustomer($sourcePole->customer_id) }}" @selected($selectedCustomerId === $sourcePole->customer_id)>
                {{ $sourcePole->customer_id }}
            </option>
        @endforeach
    </select>
</div>
<div id="{{ $prefix }}-sub-wrap" data-route-sub-wrap>
    <label class="label">Nomor Sub</label>
    <input class="field" id="{{ $prefix }}-sub-name-preview" value="{{ $selectedSubName ?: 'Pilih sumber' }}" readonly data-route-sub-preview data-current-source="{{ $selectedCustomerId }}" data-current-sub="{{ $selectedSubName }}">
</div>
<div class="md:col-span-2">
    <div class="rounded-xl border border-cyan-400/20 bg-slate-900/70 p-3">
        <div class="mb-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-black text-slate-100">Pinpoint Koordinat</p>
                <p id="{{ $prefix }}-coordinate-notice" class="text-xs font-semibold text-slate-400">Klik peta atau geser pin, lalu tekan Set Lokasi.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" class="btn btn-muted px-3 py-2 text-xs" data-location-button data-map-id="{{ $prefix }}-coordinate-map">
                    <i data-lucide="crosshair" class="icon"></i>
                    Ambil Lokasi
                </button>
                <button type="button" class="btn btn-primary px-3 py-2 text-xs" data-set-location-button data-map-id="{{ $prefix }}-coordinate-map">
                    <i data-lucide="check" class="icon"></i>
                    Set Lokasi
                </button>
            </div>
        </div>
        <div id="{{ $prefix }}-coordinate-map" class="h-72 overflow-hidden rounded-lg border border-white/10 bg-slate-800" data-coordinate-picker data-lat-input="{{ $prefix }}-latitude" data-lng-input="{{ $prefix }}-longitude" data-address-input="{{ $prefix }}-address" data-village-input="{{ $prefix }}-village" data-district-input="{{ $prefix }}-district" data-regency-input="{{ $prefix }}-regency" data-province-input="{{ $prefix }}-province" data-notice-id="{{ $prefix }}-coordinate-notice"></div>
    </div>
</div>
<div class="md:col-span-2">
    <label class="label">Alamat</label>
    <textarea id="{{ $prefix }}-address" class="field" name="address" rows="3" required placeholder="Contoh: Jalan Brigjen Katamso Wonosari - Titik 1">{{ old('address', $pole?->addressLine() ?? '') }}</textarea>
</div>
<div>
    <label class="label">Kelurahan</label>
    <input id="{{ $prefix }}-village" class="field" name="village" value="{{ $poleValue('village') }}">
</div>
<div>
    <label class="label">Kecamatan</label>
    <input id="{{ $prefix }}-district" class="field" name="district" value="{{ $poleValue('district') }}">
</div>
<div>
    <label class="label">Kabupaten</label>
    <input id="{{ $prefix }}-regency" class="field" name="regency" value="{{ $poleValue('regency') }}">
</div>
<div>
    <label class="label">Provinsi</label>
    <input id="{{ $prefix }}-province" class="field" name="province" value="{{ $poleValue('province') }}">
</div>
<div>
    <label class="label">Latitude</label>
    <input id="{{ $prefix }}-latitude" class="field no-spinner" name="latitude" type="number" step="0.0000001" value="{{ $poleValue('latitude', -7.965) }}" required>
</div>
<div>
    <label class="label">Longitude</label>
    <input id="{{ $prefix }}-longitude" class="field no-spinner" name="longitude" type="number" step="0.0000001" value="{{ $poleValue('longitude', 110.603) }}" required>
</div>
<div class="md:col-span-2 rounded-xl border border-white/10 bg-white/5 p-4" data-lamp-technical-group>
    <div class="mb-4">
        <h3 class="text-sm font-black uppercase tracking-wide text-cyan-400">Parameter Teknis Lampu (Luminer)</h3>
    </div>
    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="label">Kondisi Lampu</label>
            <select class="field" name="lamp_status" required data-lamp-status-select>
                <option value="" @selected($poleValue('lamp_status') === '')>Pilihan</option>
                @foreach (['menyala' => 'Menyala', 'redup' => 'Redup', 'berkedip' => 'Berkedip', 'mati' => 'Mati', 'rusak_pecah' => 'Rusak Pecah', 'tidak_ada_lampu' => 'Tidak Ada Lampu'] as $value => $text)
                    <option value="{{ $value }}" @selected($poleValue('lamp_status') === $value)>{{ $text }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label">Daya Lampu</label>
            <input class="field" name="power_usage" type="number" min="0" value="{{ $poleValue('power_usage', 40) }}" required data-power-usage-input data-default-power="{{ $poleValue('power_usage', 40) ?: 40 }}">
        </div>
        <div>
            <label class="label">Jenis Lampu</label>
            <select class="field" name="lamp_type" required>
                <option value="" @selected($poleValue('lamp_type') === '')>Pilihan</option>
                @foreach (['led' => 'LED', 'cfl' => 'CFL', 'hps' => 'HPS (Sodium Tekanan Tinggi)', 'son_t' => 'SON-T', 'mercury' => 'Mercury', 'lainnya' => 'Lainnya'] as $value => $text)
                    <option value="{{ $value }}" @selected($poleValue('lamp_type') === $value)>{{ $text }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label">Jenis Rumah Lampu</label>
            <select class="field" name="lamp_house_type" required>
                <option value="" @selected($poleValue('lamp_house_type') === '')>Pilihan</option>
                @foreach (['caping' => 'Caping', 'konvensional' => 'Konvensional', 'tidak_ada' => 'Tidak Ada'] as $value => $text)
                    <option value="{{ $value }}" @selected($poleValue('lamp_house_type') === $value)>{{ $text }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label">Kondisi Rumah Lampu</label>
            <select class="field" name="lamp_house_condition" required>
                <option value="" @selected($poleValue('lamp_house_condition') === '')>Pilihan</option>
                @foreach (['baik' => 'Baik', 'rusak' => 'Rusak'] as $value => $text)
                    <option value="{{ $value }}" @selected($poleValue('lamp_house_condition') === $value)>{{ $text }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label">Jumlah Titik Lampu</label>
            <input class="field" name="light_point_count" type="number" min="1" value="{{ $poleValue('light_point_count', 1) }}" required>
        </div>
    </div>
</div>
<div class="md:col-span-2 rounded-xl border border-white/10 bg-white/5 p-4">
    <div class="mb-4">
        <h3 class="text-sm font-black uppercase tracking-wide text-cyan-400">Parameter Fisik (Konstruksi)</h3>
    </div>
    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="label">Jenis Tiang</label>
            <select class="field" name="pole_type" required>
                <option value="" @selected($poleValue('pole_type') === '')>Pilihan</option>
                @foreach (['tiang_baja_galvanis' => 'Tiang Baja (Galvanis)', 'tiang_beton' => 'Tiang Beton', 'menempel_tiang_pln' => 'Menempel Tiang PLN', 'menempel_pohon' => 'Menempel Pohon', 'tanpa_penyangga' => 'Tanpa Penyangga'] as $value => $text)
                    <option value="{{ $value }}" @selected($poleValue('pole_type') === $value)>{{ $text }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label">Tinggi Tiang</label>
            <input class="field" name="height" type="number" min="0" step="0.01" value="{{ $poleValue('height') }}">
        </div>
        <div>
            <label class="label">Tipe Ornamen</label>
            <select class="field" name="ornament_type" required>
                <option value="" @selected($poleValue('ornament_type') === '')>Pilihan</option>
                @foreach (['single_arm' => 'Single Arm', 'double_arm' => 'Double Arm', 'high_mast' => 'High Mast', 'lainnya' => 'Lainnya'] as $value => $text)
                    <option value="{{ $value }}" @selected($poleValue('ornament_type') === $value)>{{ $text }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label">Kondisi Tiang</label>
            <select class="field" name="condition" required>
                <option value="" @selected($poleValue('condition') === '')>Pilihan</option>
                @foreach (['keropos' => 'Keropos', 'miring' => 'Miring', 'berkarat' => 'Berkarat', 'kokoh' => 'Kokoh', 'lainnya' => 'Lainnya'] as $value => $text)
                    <option value="{{ $value }}" @selected($poleValue('condition') === $value)>{{ $text }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
<div>
    <label class="label">Kepemilikan</label>
    <select class="field" name="ownership" required>
        <option value="" @selected($poleValue('ownership') === '')>Pilihan</option>
        @foreach (['dpu_kp' => 'DPU KP', 'dishub' => 'Dishub', 'pln' => 'PLN', 'lainnya' => 'Lainnya'] as $value => $text)
            <option value="{{ $value }}" @selected($poleValue('ownership') === $value)>{{ $text }}</option>
        @endforeach
    </select>
</div>
<div>
    <label class="label">KWH Meter</label>
    <select class="field" name="kwh_meter" required>
        <option value="" @selected($poleValue('kwh_meter') === '')>Pilihan</option>
        @foreach (['sudah_ada' => 'Sudah Ada', 'belum' => 'Belum'] as $value => $text)
            <option value="{{ $value }}" @selected($poleValue('kwh_meter') === $value)>{{ $text }}</option>
        @endforeach
    </select>
</div>
<div>
    <label class="label">Foto</label>
    <label class="flex min-h-36 cursor-pointer flex-col items-center justify-center rounded-lg border border-dashed bg-slate-50 p-4 text-center">
        <span id="{{ $prefix }}-photo-preview">
            @if ($pole?->photo_url)
                <img src="{{ $pole->photo_url }}" alt="Preview tiang" class="max-h-48 rounded-md object-cover">
            @else
                <span class="flex flex-col items-center gap-2 text-sm font-semibold text-slate-500">
                    <i data-lucide="camera" class="size-8 text-cyan-700"></i>
                    Ambil atau unggah beberapa foto tiang
                </span>
            @endif
        </span>
        <input class="sr-only" name="photos[]" type="file" accept="image/*" capture="environment" multiple data-photo-input="{{ $prefix }}-photo-preview">
    </label>
</div>
<div class="md:col-span-2">
    <label class="label">Catatan</label>
    <textarea class="field" name="notes" rows="3">{{ $poleValue('notes') }}</textarea>
</div>
