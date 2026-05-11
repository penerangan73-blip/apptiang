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
    <input id="{{ $prefix }}-latitude" class="field no-spinner" name="latitude" type="number" step="0.0000001" value="{{ $poleValue('latitude', -7.965) }}" required>
</div>
<div>
    <label class="label">Longitude</label>
    <input id="{{ $prefix }}-longitude" class="field no-spinner" name="longitude" type="number" step="0.0000001" value="{{ $poleValue('longitude', 110.603) }}" required>
</div>
<div class="md:col-span-2 rounded-xl border border-white/10 bg-white/5 p-4">
    <div class="mb-4">
        <h3 class="text-sm font-black uppercase tracking-wide text-cyan-400">Parameter Teknis Lampu (Luminer)</h3>
    </div>
    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="label">Daya Lampu</label>
            <input class="field" name="power_usage" type="number" min="1" value="{{ $poleValue('power_usage', 40) }}" required>
        </div>
        <div>
            <label class="label">Jenis Lampu</label>
            <select class="field" name="lamp_type" required>
                <option value="" @selected($poleValue('lamp_type') === '')>Pilihan</option>
                @foreach (['led' => 'LED', 'hps' => 'HPS (Sodium Tekanan Tinggi)', 'son_t' => 'SON-T', 'mercury' => 'Mercury', 'lainnya' => 'Lainnya'] as $value => $text)
                    <option value="{{ $value }}" @selected($poleValue('lamp_type') === $value)>{{ $text }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label">Status Lampu</label>
            <select class="field" name="lamp_status" required>
                <option value="" @selected($poleValue('lamp_status') === '')>Pilihan</option>
                @foreach (['menyala' => 'Menyala', 'redup' => 'Redup', 'berkedip' => 'Berkedip', 'mati' => 'Mati'] as $value => $text)
                    <option value="{{ $value }}" @selected($poleValue('lamp_status') === $value)>{{ $text }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label">Kondisi Rumah Lampu</label>
            <select class="field" name="lamp_house_condition" required>
                <option value="" @selected($poleValue('lamp_house_condition') === '')>Pilihan</option>
                @foreach (['pecah' => 'Pecah', 'kusam' => 'Kusam', 'kemasukan_air' => 'Kemasukan Air'] as $value => $text)
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
                @foreach (['tiang_baja_galvanis' => 'Tiang Baja (Galvanis)', 'tiang_beton' => 'Tiang Beton', 'menempel_tiang_pln' => 'Menempel Tiang PLN'] as $value => $text)
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
                @foreach (['single_arm' => 'Single Arm', 'double_arm' => 'Double Arm', 'high_mast' => 'High Mast'] as $value => $text)
                    <option value="{{ $value }}" @selected($poleValue('ornament_type') === $value)>{{ $text }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label">Kondisi Tiang</label>
            <select class="field" name="condition" required>
                <option value="" @selected($poleValue('condition') === '')>Pilihan</option>
                @foreach (['keropos' => 'Keropos', 'miring' => 'Miring', 'berkarat' => 'Berkarat', 'kokoh' => 'Kokoh'] as $value => $text)
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
