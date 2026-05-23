@extends('layouts.dashboard', ['pageTitle' => 'Peta - Inventarisasi APJ Non APP'])

@section('content')
<div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_420px]">
    <div class="card overflow-hidden -mx-4 sm:mx-0 border-x-0 sm:border-x">
        <div class="flex flex-col gap-3 border-b border-white/10 p-4 sm:flex-row sm:items-center sm:justify-between sm:gap-4 sm:p-5">
            <div>
                <h3 class="flex items-center gap-2 text-lg font-black"><i data-lucide="map-pinned" class="icon text-cyan-400"></i> Peta Lokasi Tiang</h3>
                <p class="text-sm text-slate-400">Titik aset berdasarkan filter jalan dan kondisi.</p>
            </div>
            <div class="flex flex-wrap gap-2 sm:items-center sm:justify-end">
                <div class="relative">
                    <button type="button" id="map-style-toggle" class="btn btn-muted inline-flex px-3 py-2 text-xs" aria-expanded="false" aria-controls="map-style-menu">
                        <i data-lucide="map" class="icon"></i>
                        <span id="map-style-label">Satelit</span>
                        <i data-lucide="chevron-down" class="icon"></i>
                    </button>
                    <div id="map-style-menu" class="absolute left-0 z-[1000] mt-2 hidden w-56 overflow-hidden rounded-lg border border-white/10 bg-slate-950/95 p-1 shadow-2xl backdrop-blur sm:left-auto sm:right-0">
                        <button type="button" class="w-full rounded-md px-3 py-2 text-left text-xs font-bold text-slate-200 transition hover:bg-white/10" data-map-style="standard">Standar</button>
                        <button type="button" class="w-full rounded-md px-3 py-2 text-left text-xs font-bold text-slate-200 transition hover:bg-white/10" data-map-style="satellite">Satelit</button>
                        <button type="button" class="w-full rounded-md px-3 py-2 text-left text-xs font-bold text-slate-200 transition hover:bg-white/10" data-map-style="topography">Topografi</button>
                        <button type="button" class="w-full rounded-md px-3 py-2 text-left text-xs font-bold text-slate-200 transition hover:bg-white/10" data-map-style="streets">Jalan Detail</button>
                    </div>
                </div>
                <button type="button" id="toggle-map-layers" class="btn btn-muted inline-flex px-3 py-2 text-xs">
                    <i data-lucide="layers" class="icon"></i> Tampilkan 2 Layer
                </button>
                <div class="relative">
                    <button type="button" id="map-download-toggle" class="btn btn-muted inline-flex px-3 py-2 text-xs" aria-expanded="false" aria-controls="map-download-menu">
                        <i data-lucide="download" class="icon"></i>
                        Download
                        <i data-lucide="chevron-down" class="icon"></i>
                    </button>
                    <div id="map-download-menu" class="absolute left-0 z-[1000] mt-2 hidden w-44 overflow-hidden rounded-lg border border-white/10 bg-slate-950/95 p-1 shadow-2xl backdrop-blur sm:left-auto sm:right-0">
                        <a class="flex items-center gap-2 rounded-md px-3 py-2 text-xs font-bold text-slate-200 transition hover:bg-white/10" href="{{ route('map.export', ['format' => 'shp'] + request()->only(['query', 'condition'])) }}" data-map-download-link>
                            <i data-lucide="download" class="icon"></i>
                            SHP
                        </a>
                        <a class="flex items-center gap-2 rounded-md px-3 py-2 text-xs font-bold text-slate-200 transition hover:bg-white/10" href="{{ route('map.export', ['format' => 'geojson'] + request()->only(['query', 'condition'])) }}" data-map-download-link>
                            <i data-lucide="braces" class="icon"></i>
                            GeoJSON
                        </a>
                        <a class="flex items-center gap-2 rounded-md px-3 py-2 text-xs font-bold text-slate-200 transition hover:bg-white/10" href="{{ route('map.export', ['format' => 'kml'] + request()->only(['query', 'condition'])) }}" data-map-download-link>
                            <i data-lucide="file-code-2" class="icon"></i>
                            KML
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="border-b border-white/10 p-4 sm:p-5">
            <form id="map-search-filter-form" method="GET" action="{{ route('map.index') }}" class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_220px_auto_auto]">
                <input class="field" name="query" value="{{ $query }}" placeholder="Cari nomor tiang atau alamat...">
                <select class="field" id="map-search-condition" name="condition">
                    <option value="">Semua kondisi tiang</option>
                    @foreach (['kokoh' => 'Kokoh', 'keropos' => 'Keropos', 'miring' => 'Miring', 'berkarat' => 'Berkarat'] as $option => $text)
                        <option value="{{ $option }}" @selected($condition === $option)>{{ $text }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary justify-self-start" type="submit">
                    <i data-lucide="search" class="icon"></i>
                    Cari
                </button>
                @if ($query || $condition)
                    <a class="btn btn-muted justify-self-start" href="{{ route('map.index') }}">
                        <i data-lucide="x-circle" class="icon"></i>
                        Reset
                    </a>
                @endif
            </form>
        </div>
        <div class="p-0 sm:p-5">
            <div class="relative">
                <div id="main-map" class="glass-panel h-[65vh] min-h-[400px] sm:h-[560px] overflow-hidden rounded-none sm:rounded-lg border-x-0 sm:border-x"></div>
                @include('partials.marker-status-legend')
            </div>
        </div>
    </div>

    <div class="card p-5">
        <h3 class="text-lg font-black">Inspeksi Terpilih</h3>
        <p class="text-sm text-slate-400">Detail cepat untuk surveyor lapangan.</p>
        <div id="selected-pole-panel" class="mt-4 text-sm text-slate-400">Pilih titik tiang pada peta atau tabel.</div>
    </div>
</div>

<div class="card overflow-hidden">
    <button type="button" id="toggle-map-pole-list" class="flex w-full items-center justify-between gap-3 p-5 text-left transition hover:bg-white/5" aria-expanded="false" aria-controls="map-pole-list-panel">
        <span>
            <span class="flex items-center gap-2 text-lg font-black text-slate-100">
                <i data-lucide="table-2" class="icon text-cyan-400"></i>
                Daftar Tiang
            </span>
            <span class="mt-1 block text-sm text-slate-400">Lihat data hasil filter dalam bentuk tabel.</span>
        </span>
        <span class="btn btn-muted px-3 py-2 text-xs">
            <i data-lucide="chevron-down" class="icon" id="map-pole-list-icon"></i>
            <span id="map-pole-list-label">Tampilkan</span>
        </span>
    </button>
    <div id="map-pole-list-panel" class="hidden border-t border-white/10">
        @include('partials.poles-table', ['poles' => $poles, 'showTimestamps' => false, 'showDirectionActions' => true])
    </div>
</div>
@endsection

@section('dialogs')
    @include('partials.pole-dialogs', ['dialogPoles' => $dialogPoles])
@endsection

@section('page-scripts')
    window.pjuCanManagePoles = @json($canManagePoles);
    @include('partials.pole-scripts')

    const markerColors = { kokoh: '#059669', keropos: '#e11d48', miring: '#d97706', berkarat: '#b45309' };
    const mainMaps = {};
    const mapMarkerUrl = @json(route('map.markers', request()->only(['query', 'condition'])));
    const mapBaseLayers = {
        standard: {
            label: 'Standar',
            url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            options: { maxZoom: 19, attribution: '&copy; OpenStreetMap' },
        },
        satellite: {
            label: 'Satelit',
            url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
            options: { maxZoom: 19, attribution: 'Tiles &copy; Esri' },
        },
        topography: {
            label: 'Topografi',
            url: 'https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png',
            options: { maxZoom: 17, attribution: '&copy; OpenTopoMap &copy; OpenStreetMap' },
        },
        streets: {
            label: 'Jalan Detail',
            url: 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png',
            options: { maxZoom: 19, attribution: '&copy; OpenStreetMap &copy; CARTO' },
        },
    };

    initPoleMap('main-map', true);
    document.getElementById('toggle-map-layers')?.addEventListener('click', () => toggleMapLayers());
    setupMapStyleMenu();
    setupMapDownloadMenu();
    setupMapPoleListToggle();
    setupMapSearchFilter();

    function initPoleMap(id, interactive) {
        const el = document.getElementById(id);
        if (!el || mainMaps[id]) return;
        const map = L.map(el).setView([-7.965, 110.603], 11);
        mainMaps[id] = map;
        map._pjuBaseStyle = 'satellite';
        map._pjuBaseLayer = createBaseLayer('satellite').addTo(map);
        loadPoleMarkers(map, interactive);
    }

    function createBaseLayer(style) {
        const config = mapBaseLayers[style] || mapBaseLayers.standard;

        return L.tileLayer(config.url, config.options);
    }

    function setupMapStyleMenu() {
        const toggle = document.getElementById('map-style-toggle');
        const menu = document.getElementById('map-style-menu');
        const label = document.getElementById('map-style-label');

        if (!toggle || !menu || !label) return;

        toggle.addEventListener('click', () => {
            const isOpen = !menu.classList.contains('hidden');
            menu.classList.toggle('hidden', isOpen);
            toggle.setAttribute('aria-expanded', String(!isOpen));
        });

        menu.querySelectorAll('[data-map-style]').forEach((button) => {
            button.addEventListener('click', () => {
                const style = button.dataset.mapStyle || 'standard';
                setMapStyle(style);
                label.textContent = mapBaseLayers[style]?.label || mapBaseLayers.standard.label;
                menu.classList.add('hidden');
                toggle.setAttribute('aria-expanded', 'false');
            });
        });

        document.addEventListener('click', (event) => {
            if (toggle.contains(event.target) || menu.contains(event.target)) return;
            menu.classList.add('hidden');
            toggle.setAttribute('aria-expanded', 'false');
        });
    }

    function setMapStyle(style) {
        Object.values(mainMaps).forEach((map) => {
            if (map._pjuBaseStyle === style) return;
            if (map._pjuBaseLayer) map.removeLayer(map._pjuBaseLayer);
            map._pjuBaseLayer = createBaseLayer(style).addTo(map);
            map._pjuBaseStyle = style;
        });
    }

    function setupMapDownloadMenu() {
        const toggle = document.getElementById('map-download-toggle');
        const menu = document.getElementById('map-download-menu');

        if (!toggle || !menu) return;

        toggle.addEventListener('click', () => {
            const isOpen = !menu.classList.contains('hidden');
            menu.classList.toggle('hidden', isOpen);
            toggle.setAttribute('aria-expanded', String(!isOpen));
        });

        document.addEventListener('click', (event) => {
            if (toggle.contains(event.target) || menu.contains(event.target)) return;
            menu.classList.add('hidden');
            toggle.setAttribute('aria-expanded', 'false');
        });
    }

    function setupMapPoleListToggle() {
        const toggle = document.getElementById('toggle-map-pole-list');
        const panel = document.getElementById('map-pole-list-panel');
        const label = document.getElementById('map-pole-list-label');
        const icon = document.getElementById('map-pole-list-icon');

        if (!toggle || !panel || !label || !icon) return;

        toggle.addEventListener('click', () => {
            const willOpen = panel.classList.contains('hidden');
            panel.classList.toggle('hidden', !willOpen);
            toggle.setAttribute('aria-expanded', String(willOpen));
            label.textContent = willOpen ? 'Sembunyikan' : 'Tampilkan';
            icon.setAttribute('data-lucide', willOpen ? 'chevron-up' : 'chevron-down');
            window.lucide?.createIcons();
        });
    }

    function setupMapSearchFilter() {
        const form = document.getElementById('map-search-filter-form');
        const conditionSelect = document.getElementById('map-search-condition');

        conditionSelect?.addEventListener('change', () => form?.submit());
    }

    async function loadPoleMarkers(map, interactive) {
        try {
            const response = await fetch(mapMarkerUrl, { headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error('Marker tidak bisa dimuat.');
            const payload = await response.json();
            addPoleMarkers(map, payload.data || [], interactive);
        } catch (error) {
            console.error(error);
        }
    }

    function addPoleMarkers(map, poles, interactive) {
        const bounds = [];
        poles.forEach((pole) => {
            const lat = Number(pole.latitude), lng = Number(pole.longitude);
            if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;
            bounds.push([lat, lng]);
            const marker = L.marker([lat, lng], {
                icon: L.divIcon({ className: '', html: `<span style="display:block;width:18px;height:18px;border-radius:999px;background:${markerColors[pole.condition] || '#2563eb'};border:3px solid white;box-shadow:0 8px 20px rgba(15,23,42,.22)"></span>`, iconSize: [18, 18], iconAnchor: [9, 9] })
            }).addTo(map);
            marker.bindPopup(createMarkerPopup(pole), { maxWidth: 300, className: 'pju-marker-popup' });
            marker.on('click', () => {
                renderSelectedPole(pole);
                setTimeout(() => window.lucide?.createIcons(), 0);
            });
        });
        if (bounds.length) map.fitBounds(bounds, { padding: [36, 36], maxZoom: interactive ? 12 : 13 });
    }

    function renderSelectedPole(pole) {
        const panel = document.getElementById('selected-pole-panel');
        if (!panel) return;
        const mapsUrl = googleMapsDirectionsUrl(pole);
        panel.innerHTML = `
            <div class="overflow-hidden rounded-2xl border border-cyan-400/20 bg-gradient-to-br from-slate-900 to-slate-950 shadow-[0_14px_40px_rgba(6,182,212,0.08)]">
                ${pole.photo_url ? `<img src="${pole.photo_url}" alt="Foto ${escapeHtml(pole.display_name || pole.customer_id)}" class="h-36 w-full object-cover">` : ''}
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-black uppercase tracking-widest text-cyan-400">Tiang Terpilih</p>
                            <p class="mt-1 truncate text-2xl font-black text-slate-100">${escapeHtml(pole.display_name || pole.customer_id)}</p>
                            <p class="mt-2 text-sm leading-6 text-slate-400">${escapeHtml(pole.location_label || pole.address_line || pole.street_name)}</p>
                        </div>
                        <span class="badge badge-${pole.condition} shrink-0">${formatOption(pole.condition)}</span>
                    </div>
                    <a href="${mapsUrl}" target="_blank" rel="noopener noreferrer" class="btn btn-primary mt-4 w-full justify-center px-3 py-2 text-xs">
                        <i data-lucide="navigation" class="icon"></i>
                        Menuju Lokasi
                    </a>
                </div>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-3">
                ${detailBlock('Daya Lampu', `${pole.power_usage} W`)}
                ${detailBlock('Koordinat', `${Number(pole.latitude).toFixed(4)}, ${Number(pole.longitude).toFixed(4)}`)}
                ${detailBlock('Jenis Tiang', formatOption(pole.pole_type))}
                ${detailBlock('Tinggi Tiang', pole.height ? `${pole.height} m` : '-')}
                ${detailBlock('Tipe Ornamen', formatOption(pole.ornament_type))}
                ${detailBlock('Jenis Lampu', formatLampType(pole.lamp_type))}
                ${detailBlock('Kondisi Lampu', formatOption(pole.lamp_status))}
                ${detailBlock('Jenis Rumah Lampu', formatOption(pole.lamp_house_type))}
                ${detailBlock('Kondisi Rumah Lampu', formatOption(pole.lamp_house_condition))}
                ${detailBlock('Jumlah Titik Lampu', pole.light_point_count || '-')}
                ${detailBlock('KWH Meter', pole.kwh_meter ? formatOption(pole.kwh_meter) : '-')}
                ${detailBlock('Kepemilikan', formatOwnership(pole.ownership))}
            </div>
            ${pole.notes ? `<div class="mt-3 rounded-xl border border-white/10 bg-white/5 p-4"><p class="text-xs font-black uppercase tracking-widest text-slate-400">Keterangan</p><p class="mt-2 text-sm leading-6 text-slate-300">${escapeHtml(pole.notes)}</p></div>` : ''}`;
    }

    function detailBlock(label, value) {
        return `<div class="rounded-xl border border-white/10 bg-white/5 p-3 transition hover:border-cyan-400/30"><p class="text-[10px] font-black uppercase tracking-widest text-slate-400">${label}</p><p class="mt-1 break-words text-sm font-extrabold text-slate-100">${escapeHtml(String(value))}</p></div>`;
    }

    function createMarkerPopup(pole) {
        const mapsUrl = googleMapsDirectionsUrl(pole);
        return `
            <div class="p-3">
                <div class="flex items-start justify-between gap-3 pr-7">
                    <div class="min-w-0">
                        <p class="text-lg font-black text-slate-100">${escapeHtml(pole.display_name || pole.customer_id)}</p>
                    </div>
                    <span class="badge badge-${pole.condition} mt-5 shrink-0">${formatOption(pole.condition)}</span>
                </div>
                <p class="mt-1 text-sm leading-5 text-slate-300">${escapeHtml(pole.location_label || pole.address_line || pole.street_name)}</p>
                <dl class="mt-3 grid grid-cols-[72px_1fr] gap-x-3 gap-y-1.5 text-sm">
                    <dt class="font-bold text-slate-500">Daya Lampu</dt>
                    <dd class="font-semibold text-slate-100">${pole.power_usage} W</dd>
                    <dt class="font-bold text-slate-500">Jenis</dt>
                    <dd class="font-semibold text-slate-100">${escapeHtml(formatOption(pole.pole_type))}</dd>
                    <dt class="font-bold text-slate-500">Lampu</dt>
                    <dd class="font-semibold text-slate-100">${escapeHtml(formatLampType(pole.lamp_type))} - ${escapeHtml(formatOption(pole.lamp_status))}</dd>
                    <dt class="font-bold text-slate-500">Koordinat</dt>
                    <dd class="font-semibold text-slate-100">${Number(pole.latitude).toFixed(5)}, ${Number(pole.longitude).toFixed(5)}</dd>
                </dl>
                <a href="${mapsUrl}" target="_blank" rel="noopener noreferrer" class="btn btn-primary mt-3 w-full justify-center px-3 py-2 text-xs">
                    <i data-lucide="navigation" class="icon"></i>
                    Menuju Lokasi
                </a>
            </div>
        `;
    }

    function googleMapsDirectionsUrl(pole) {
        const lat = Number(pole.latitude);
        const lng = Number(pole.longitude);

        return `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`;
    }

    function formatLampType(value) {
        return formatOption(value);
    }

    // Shapefile layers
    const layerConfigs = [
        { id: 'administrasi-area', label: 'Administrasi Area', url: '/data/peta/administrasi_ar_340320240807001259.zip', style: { color: '#075985', fillColor: '#38bdf8', fillOpacity: 0.48, opacity: 0.95, weight: 1.4 }, hover: { fillOpacity: 0.68, weight: 2.8 } },
        { id: 'administrasi-garis', label: 'Batas Administrasi', url: '/data/peta/administrasi_ln_340320240806233638.zip', style: { color: '#0e7490', fillOpacity: 0, opacity: 0.95, weight: 1.6, dashArray: '4 3' }, hover: { opacity: 1, weight: 2.6 } },
    ];
    const loadedLayers = [];
    let layerPromise = null, shpScriptPromise = null;

    async function ensureShpJs() {
        if (window.shp) return window.shp;
        if (!shpScriptPromise) {
            shpScriptPromise = new Promise((resolve, reject) => {
                const s = document.createElement('script'); s.src = 'https://unpkg.com/shpjs@6.2.0/dist/shp.min.js'; s.async = true;
                s.onload = () => resolve(window.shp); s.onerror = reject; document.head.appendChild(s);
            });
        }
        return shpScriptPromise;
    }

    async function loadLayers() {
        if (loadedLayers.length) return loadedLayers;
        if (layerPromise) return layerPromise;
        layerPromise = (async () => {
            const parseShp = await ensureShpJs();
            const settled = await Promise.allSettled(layerConfigs.map(async (c) => {
                const r = await fetch(c.url); if (!r.ok) throw new Error(c.label);
                const b = await r.arrayBuffer(), p = await parseShp(b);
                return { ...c, data: Array.isArray(p) ? { type: 'FeatureCollection', features: p.flatMap(i => i.features) } : p };
            }));
            loadedLayers.push(...settled.flatMap(i => i.status === 'fulfilled' ? [i.value] : []));
            return loadedLayers;
        })();
        return layerPromise;
    }

    async function toggleMapLayers() {
        const btn = document.getElementById('toggle-map-layers');
        const hasActiveLayer = Object.values(mainMaps).some((map) => map._pjuLayerGroup && map.hasLayer(map._pjuLayerGroup));

        if (hasActiveLayer) {
            closeLayersFromMaps();
            return;
        }

        if (btn) btn.innerHTML = '<i data-lucide="loader-2" class="icon"></i> Memuat layer...';

        try {
            const layers = await loadLayers();
            Object.values(mainMaps).forEach(m => addLayersToMap(m, layers));
            syncLayerDownloadLinks(layers.length > 0);
            if (btn) btn.innerHTML = layers.length ? '<i data-lucide="eye-off" class="icon"></i> Tutup 2 Layer' : '<i data-lucide="layers" class="icon"></i> Layer gagal';
            btn?.classList.toggle('btn-danger', layers.length > 0);
            btn?.classList.toggle('btn-muted', layers.length === 0);
        } catch {
            if (btn) btn.innerHTML = '<i data-lucide="layers" class="icon"></i> Layer gagal';
        }
        finally {
            if (window.lucide) {
                lucide.createIcons();
            }
        }
    }

    function addLayersToMap(map, layers) {
        if (map._pjuLayerGroup) {
            map.addLayer(map._pjuLayerGroup);
            return;
        }
        const group = L.layerGroup();
        layers.forEach(layer => {
            const geoLayer = L.geoJSON(layer.data, {
                style: f => layer.id === 'administrasi-area' ? { ...layer.style, fillColor: adminColor(f) } : layer.style,
                onEachFeature: (f, fl) => bindFeature(layer, f, fl),
            });
            group.addLayer(geoLayer);
        });
        group.addTo(map);
        map._pjuLayerGroup = group;
    }

    function closeLayersFromMaps() {
        Object.values(mainMaps).forEach((map) => {
            if (map._pjuLayerGroup && map.hasLayer(map._pjuLayerGroup)) {
                map.removeLayer(map._pjuLayerGroup);
            }
        });
        const btn = document.getElementById('toggle-map-layers');
        if (btn) {
            btn.innerHTML = '<i data-lucide="layers" class="icon"></i> Tampilkan 2 Layer';
            btn.classList.remove('btn-danger');
            btn.classList.add('btn-muted');
        }
        syncLayerDownloadLinks(false);
        if (window.lucide) lucide.createIcons();
    }

    function syncLayerDownloadLinks(includeLayers) {
        document.querySelectorAll('[data-map-download-link]').forEach((link) => {
            const url = new URL(link.href, window.location.origin);

            if (includeLayers) {
                url.searchParams.set('include_layers', '1');
            } else {
                url.searchParams.delete('include_layers');
            }

            link.href = url.toString();
        });
    }

    function bindFeature(ml, feature, layer) {
        if (layer.setStyle) {
            const base = ml.id === 'administrasi-area' ? { ...ml.style, fillColor: adminColor(feature) } : ml.style;
            layer.on({ mouseover: () => layer.setStyle({ ...base, ...ml.hover }), mouseout: () => layer.setStyle(base) });
        }
        const p = feature.properties || {};
        const name = prop(p,'namobj')||prop(p,'desa')||prop(p,'wadmkd')||ml.label;
        const village = prop(p,'desa')||prop(p,'wadmkd')||prop(p,'namobj')||'-';
        const dist = prop(p,'wadmkc')||prop(p,'kecamata_1');
        const reg = prop(p,'wadmkk')||prop(p,'kabupaten');
        layer.bindPopup(`
            <div class="p-3" style="width:min(320px, calc(100vw - 64px));">
                <div class="border-b border-white/10 pb-3">
                    <p class="text-xs font-black uppercase tracking-widest text-cyan-400">${escapeHtml(ml.label)}</p>
                    <p class="mt-1 text-lg font-black leading-tight text-slate-100">${escapeHtml(name)}</p>
                </div>
                <dl class="mt-3 grid gap-2 text-sm">
                    ${featureInfoRow('Desa', village)}
                    ${featureInfoRow('Kecamatan', dist || '-')}
                    ${featureInfoRow('Kabupaten', reg || '-')}
                </dl>
            </div>
        `, { maxWidth: 380, className: 'pju-marker-popup' });
    }

    function featureInfoRow(label, value) {
        return `
            <div class="rounded-lg border border-white/10 bg-white/5 p-3">
                <dt class="text-xs font-black uppercase tracking-widest text-slate-400">${escapeHtml(label)}</dt>
                <dd class="mt-1 break-words font-extrabold text-slate-100">${escapeHtml(value)}</dd>
            </div>
        `;
    }

    function adminColor(f) {
        const k = prop(f.properties||{},'desa')||prop(f.properties||{},'wadmkd')||JSON.stringify(f.geometry?.type||'a');
        const palette = ['#7dd3fc','#93c5fd','#a5b4fc','#c4b5fd','#f0abfc','#f9a8d4','#fca5a5','#fdba74','#fde68a','#bef264','#86efac','#5eead4'];
        let h=0; for(let i=0;i<k.length;i++) h=(h*31+k.charCodeAt(i))>>>0;
        return palette[h%palette.length];
    }

    function prop(p,k) { const m=Object.keys(p).find(i=>i.toLowerCase()===k.toLowerCase()); return m&&p[m]!=null?String(p[m]).trim():''; }
@endsection
