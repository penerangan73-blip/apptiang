@php
    $poleValue = fn ($field, $fallback = '') => old($field, $pole?->{$field} ?? $fallback);
    $prefix = $prefix ?? 'pole';
@endphp

<div>
    <label class="label">ID Pelanggan</label>
    <input class="field" name="customer_id" value="{{ $poleValue('customer_id') }}" required>
</div>
<div class="md:col-span-2">
    <div id="{{ $prefix }}-coordinate-map" class="glass-panel h-64 overflow-hidden rounded-lg" data-coordinate-picker data-lat-input="{{ $prefix }}-latitude" data-lng-input="{{ $prefix }}-longitude" data-street-input="{{ $prefix }}-street"></div>
    <p class="mt-2 text-xs font-semibold text-slate-500">Klik peta atau geser pin untuk mengisi koordinat. Jika nama jalan kosong, aplikasi akan mencoba mengambil alamat dari peta online.</p>
</div>
<div class="md:col-span-2">
    <label class="label">Lokasi</label>
    <input id="{{ $prefix }}-street" class="field" name="street_name" value="{{ $poleValue('street_name') }}" required placeholder="Contoh: Jalan Brigjen Katamso Wonosari - Titik 1">
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
                    Ambil atau unggah foto tiang
                </span>
            @endif
        </span>
        <input class="sr-only" name="photo" type="file" accept="image/*" capture="environment" data-photo-input="{{ $prefix }}-photo-preview">
    </label>
</div>
<div class="md:col-span-2">
    <label class="label">Catatan</label>
    <textarea class="field" name="notes" rows="3">{{ $poleValue('notes') }}</textarea>
</div>
