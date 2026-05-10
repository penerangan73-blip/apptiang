@php
    $poleValue = fn ($field, $fallback = '') => old($field, $pole?->{$field} ?? $fallback);
    $prefix = $prefix ?? 'pole';
@endphp

<div>
    <label class="label">ID Pelanggan</label>
    <input class="field" name="customer_id" value="{{ $poleValue('customer_id') }}" required>
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
        <div id="{{ $prefix }}-coordinate-map" class="h-72 overflow-hidden rounded-lg border border-white/10 bg-slate-800" data-coordinate-picker data-lat-input="{{ $prefix }}-latitude" data-lng-input="{{ $prefix }}-longitude" data-street-input="{{ $prefix }}-street" data-notice-id="{{ $prefix }}-coordinate-notice"></div>
    </div>
</div>
<div class="md:col-span-2">
    <label class="label">Alamat</label>
    <textarea id="{{ $prefix }}-street" class="field" name="street_name" rows="3" required placeholder="Contoh: Jalan Brigjen Katamso Wonosari - Titik 1">{{ $poleValue('street_name') }}</textarea>
</div>
<div>
    <label class="label">Latitude</label>
    <input id="{{ $prefix }}-latitude" class="field" name="latitude" type="number" step="0.0000001" value="{{ $poleValue('latitude', -7.965) }}" required>
</div>
<div>
    <label class="label">Longitude</label>
    <input id="{{ $prefix }}-longitude" class="field" name="longitude" type="number" step="0.0000001" value="{{ $poleValue('longitude', 110.603) }}" required>
</div>
<div>
    <label class="label">Daya Pakai</label>
    <input class="field" name="power_usage" type="number" min="1" value="{{ $poleValue('power_usage', 40) }}" required>
</div>
<div>
    <label class="label">Kondisi</label>
    <select class="field" name="condition" required>
        @foreach (['baik' => 'Baik', 'rusak' => 'Rusak', 'perbaikan' => 'Perbaikan'] as $value => $text)
            <option value="{{ $value }}" @selected($poleValue('condition', 'baik') === $value)>{{ $text }}</option>
        @endforeach
    </select>
</div>
<div>
    <label class="label">Jenis Tiang</label>
    <select class="field" name="pole_type" required>
        @foreach (['lurus' => 'Lurus', 'lengkung' => 'Lengkung', 'tanpa_tiang' => 'Tanpa tiang', 'lainnya' => 'Lainnya'] as $value => $text)
            <option value="{{ $value }}" @selected($poleValue('pole_type', 'lurus') === $value)>{{ $text }}</option>
        @endforeach
    </select>
</div>
<div>
    <label class="label">Jenis Fondasi</label>
    <select class="field" name="foundation_type" required>
        @foreach (['beton' => 'Beton', 'tanam' => 'Tanam', 'base_plate' => 'Base plate', 'lainnya' => 'Lainnya'] as $value => $text)
            <option value="{{ $value }}" @selected($poleValue('foundation_type', 'beton') === $value)>{{ $text }}</option>
        @endforeach
    </select>
</div>
<div>
    <label class="label">Kepemilikan</label>
    <select class="field" name="ownership" required>
        @foreach (['dpu_kp' => 'DPU KP', 'dishub' => 'Dishub', 'pln' => 'PLN', 'lainnya' => 'Lainnya'] as $value => $text)
            <option value="{{ $value }}" @selected($poleValue('ownership', 'dpu_kp') === $value)>{{ $text }}</option>
        @endforeach
    </select>
</div>
<div>
    <label class="label">Tinggi</label>
    <input class="field" name="height" type="number" min="0" step="0.01" value="{{ $poleValue('height') }}">
</div>
<div>
    <label class="label">Jarak Bentang</label>
    <input class="field" name="span_length" type="number" min="0" step="0.01" value="{{ $poleValue('span_length') }}">
</div>
<div>
    <label class="label">Trafo PLN</label>
    <input class="field" name="pln_transformer" value="{{ $poleValue('pln_transformer') }}">
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
