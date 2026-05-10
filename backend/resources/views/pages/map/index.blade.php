@extends('layouts.dashboard', ['pageTitle' => 'Peta - Inventarisasi APJ Non APP'])

@section('content')
<div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_420px]">
    <div class="card overflow-hidden -mx-4 sm:mx-0 border-x-0 sm:border-x">
        <div class="flex items-center justify-between gap-4 border-b border-white/10 p-4 sm:p-5">
            <div>
                <h3 class="flex items-center gap-2 text-lg font-black"><i data-lucide="map-pinned" class="icon text-cyan-400"></i> Peta Lokasi Tiang</h3>
                <p class="text-sm text-slate-400">Titik aset berdasarkan filter jalan dan kondisi.</p>
            </div>
            <button type="button" id="toggle-map-layers" class="btn btn-muted hidden px-3 py-2 text-xs sm:inline-flex">
                <i data-lucide="layers" class="icon"></i> Tampilkan 3 Layer
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

@include('partials.search-filter', ['actionRoute' => 'map.index'])
@include('partials.poles-table', ['poles' => $poles, 'showTimestamps' => false])
@endsection

@section('dialogs')
    @include('partials.pole-dialogs', ['dialogPoles' => $dialogPoles])
@endsection

@section('page-scripts')
    window.pjuCanManagePoles = @json($canManagePoles);
    @include('partials.pole-scripts')

    const markerColors = { baik: '#059669', rusak: '#e11d48', perbaikan: '#d97706' };
    const mainMaps = {};
    const mapMarkerUrl = @json(route('map.markers', request()->only(['query', 'condition'])));

    initPoleMap('main-map', true);
    document.getElementById('toggle-map-layers')?.addEventListener('click', () => toggleMapLayers());

    function initPoleMap(id, interactive) {
        const el = document.getElementById(id);
        if (!el || mainMaps[id]) return;
        const map = L.map(el).setView([-7.965, 110.603], 11);
        mainMaps[id] = map;
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);
        loadPoleMarkers(map, interactive);
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
            marker.on('click', () => renderSelectedPole(pole));
        });
        if (bounds.length) map.fitBounds(bounds, { padding: [36, 36], maxZoom: interactive ? 12 : 13 });
    }

    function renderSelectedPole(pole) {
        const panel = document.getElementById('selected-pole-panel');
        if (!panel) return;
        panel.innerHTML = `
            <div class="overflow-hidden rounded-2xl border border-cyan-400/20 bg-gradient-to-br from-slate-900 to-slate-950 shadow-[0_14px_40px_rgba(6,182,212,0.08)]">
                ${pole.photo_url ? `<img src="${pole.photo_url}" alt="Foto ${escapeHtml(pole.customer_id)}" class="h-36 w-full object-cover">` : ''}
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-black uppercase tracking-widest text-cyan-400">Tiang Terpilih</p>
                            <p class="mt-1 truncate text-2xl font-black text-slate-100">${escapeHtml(pole.customer_id)}</p>
                            <p class="mt-2 text-sm leading-6 text-slate-400">${escapeHtml(pole.street_name)}</p>
                        </div>
                        <span class="badge badge-${pole.condition} shrink-0">${formatOption(pole.condition)}</span>
                    </div>
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
            ${pole.notes ? `<div class="mt-3 rounded-xl border border-white/10 bg-white/5 p-4"><p class="text-xs font-black uppercase tracking-widest text-slate-400">Keterangan</p><p class="mt-2 text-sm leading-6 text-slate-300">${escapeHtml(pole.notes)}</p></div>` : ''}`;
    }

    function detailBlock(label, value) {
        return `<div class="rounded-xl border border-white/10 bg-white/5 p-3 transition hover:border-cyan-400/30"><p class="text-[10px] font-black uppercase tracking-widest text-slate-400">${label}</p><p class="mt-1 break-words text-sm font-extrabold text-slate-100">${escapeHtml(String(value))}</p></div>`;
    }

    function createMarkerPopup(pole) {
        return `
            <div class="p-3">
                <div class="flex items-start justify-between gap-3 pr-7">
                    <div class="min-w-0">
                        <p class="text-lg font-black text-slate-100">${escapeHtml(pole.customer_id)}</p>
                    </div>
                    <span class="badge badge-${pole.condition} mt-5 shrink-0">${formatOption(pole.condition)}</span>
                </div>
                <p class="mt-1 text-sm leading-5 text-slate-300">${escapeHtml(pole.street_name)}</p>
                <dl class="mt-3 grid grid-cols-[72px_1fr] gap-x-3 gap-y-1.5 text-sm">
                    <dt class="font-bold text-slate-500">Daya</dt>
                    <dd class="font-semibold text-slate-100">${pole.power_usage} W</dd>
                    <dt class="font-bold text-slate-500">Jenis</dt>
                    <dd class="font-semibold text-slate-100">${escapeHtml(formatOption(pole.pole_type))}</dd>
                    <dt class="font-bold text-slate-500">Koordinat</dt>
                    <dd class="font-semibold text-slate-100">${Number(pole.latitude).toFixed(5)}, ${Number(pole.longitude).toFixed(5)}</dd>
                </dl>
            </div>
        `;
    }

    // Shapefile layers
    const layerConfigs = [
        { id: 'administrasi-area', label: 'Administrasi Area', url: '/data/peta/administrasi_ar_340320240807001259.zip', style: { color: '#075985', fillColor: '#38bdf8', fillOpacity: 0.48, opacity: 0.95, weight: 1.4 }, hover: { fillOpacity: 0.68, weight: 2.8 } },
        { id: 'administrasi-garis', label: 'Batas Administrasi', url: '/data/peta/administrasi_ln_340320240806233638.zip', style: { color: '#0e7490', fillOpacity: 0, opacity: 0.95, weight: 1.6, dashArray: '4 3' }, hover: { opacity: 1, weight: 2.6 } },
        { id: 'jalan', label: 'Jalan', url: '/data/peta/jalan_ln_340320240820002322.zip', style: { color: '#ef4444', fillOpacity: 0, opacity: 0.9, weight: 1.4 }, hover: { opacity: 1, weight: 2.4 } },
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
            if (btn) btn.innerHTML = layers.length ? '<i data-lucide="eye-off" class="icon"></i> Tutup 3 Layer' : '<i data-lucide="layers" class="icon"></i> Layer gagal';
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
            btn.innerHTML = '<i data-lucide="layers" class="icon"></i> Tampilkan 3 Layer';
            btn.classList.remove('btn-danger');
            btn.classList.add('btn-muted');
        }
        if (window.lucide) lucide.createIcons();
    }

    function bindFeature(ml, feature, layer) {
        if (layer.setStyle) {
            const base = ml.id === 'administrasi-area' ? { ...ml.style, fillColor: adminColor(feature) } : ml.style;
            layer.on({ mouseover: () => layer.setStyle({ ...base, ...ml.hover }), mouseout: () => layer.setStyle(base) });
        }
        const p = feature.properties || {};
        const name = prop(p,'namobj')||prop(p,'desa')||prop(p,'wadmkd')||ml.label;
        const dist = prop(p,'wadmkc')||prop(p,'kecamata_1');
        const reg = prop(p,'wadmkk')||prop(p,'kabupaten');
        layer.bindPopup(`<div class="min-w-48"><p class="text-xs font-bold uppercase tracking-wide text-slate-400">${escapeHtml(ml.label)}</p><p class="text-base font-extrabold text-slate-100">${escapeHtml(name)}</p>${dist?`<p class="text-sm text-slate-700">Kecamatan ${escapeHtml(dist)}</p>`:''}${reg?`<p class="text-sm text-slate-300">${escapeHtml(reg)}</p>`:''}</div>`);
    }

    function adminColor(f) {
        const k = prop(f.properties||{},'desa')||prop(f.properties||{},'wadmkd')||JSON.stringify(f.geometry?.type||'a');
        const palette = ['#7dd3fc','#93c5fd','#a5b4fc','#c4b5fd','#f0abfc','#f9a8d4','#fca5a5','#fdba74','#fde68a','#bef264','#86efac','#5eead4'];
        let h=0; for(let i=0;i<k.length;i++) h=(h*31+k.charCodeAt(i))>>>0;
        return palette[h%palette.length];
    }

    function prop(p,k) { const m=Object.keys(p).find(i=>i.toLowerCase()===k.toLowerCase()); return m&&p[m]!=null?String(p[m]).trim():''; }
@endsection
