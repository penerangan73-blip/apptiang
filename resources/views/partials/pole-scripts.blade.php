    // Report pole selector
    document.querySelectorAll('[data-report-pole]').forEach((button) => {
        button.addEventListener('click', () => {
            const select = document.getElementById('report-pole-id');
            if (select) select.value = button.dataset.reportPole;
        });
    });

    // Dynamic route fields
    window.setupRouteTypeFields = function setupRouteTypeFields(scope = document) {
        scope.querySelectorAll('[data-route-type-group]').forEach((group) => {
            if (group.dataset.routeInitialized === '1') return;
            group.dataset.routeInitialized = '1';

            const form = group.closest('form') || document;
            const options = [...group.querySelectorAll('[data-route-type-option]')];
            const sourceInputWrap = form.querySelector('[data-route-source-input-wrap]');
            const sourceSelectWrap = form.querySelector('[data-route-source-select-wrap]');
            const subWrap = form.querySelector('[data-route-sub-wrap]');
            const sourceInput = form.querySelector('[data-route-source-input]');
            const sourceSelect = form.querySelector('[data-route-source-select]');
            const sourceCombo = form.querySelector('[data-route-source-combobox]');
            const sourceToggle = form.querySelector('[data-route-source-toggle]');
            const sourceLabel = form.querySelector('[data-route-source-label]');
            const sourceMenu = form.querySelector('[data-route-source-menu]');
            const sourceSearch = form.querySelector('[data-route-source-search]');
            const sourceOptionList = form.querySelector('[data-route-source-options]');
            const sourceEmpty = form.querySelector('[data-route-source-empty]');
            const subPreview = form.querySelector('[data-route-sub-preview]');
            const normalizeSearch = (value) => (value || '').toString().trim().toLowerCase();
            const closeSourceMenu = () => {
                sourceMenu?.classList.add('hidden');
                sourceToggle?.setAttribute('aria-expanded', 'false');
            };
            const updateSourceLabel = () => {
                if (!sourceSelect || !sourceLabel) return;

                const selectedOption = sourceSelect.selectedOptions?.[0];
                sourceLabel.textContent = selectedOption?.value ? selectedOption.textContent.trim() : 'Pilih sumber';

                sourceOptionList?.querySelectorAll('[data-route-source-option]').forEach((button) => {
                    const isSelected = button.dataset.value === sourceSelect.value;
                    button.classList.toggle('bg-cyan-500/15', isSelected);
                    button.classList.toggle('text-cyan-100', isSelected);
                    button.classList.toggle('text-slate-200', !isSelected);
                });
            };
            const createSourceOptions = () => {
                if (!sourceSelect || !sourceOptionList || sourceOptionList.dataset.rendered === '1') return;

                [...sourceSelect.options].forEach((option) => {
                    if (!option.value) return;

                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'flex w-full items-center px-3 py-2 text-left text-sm font-semibold text-slate-200 transition hover:bg-white/10';
                    button.dataset.routeSourceOption = '1';
                    button.dataset.value = option.value;
                    button.textContent = option.textContent.trim();
                    button.addEventListener('click', () => {
                        sourceSelect.value = option.value;
                        sourceSelect.dispatchEvent(new Event('change', { bubbles: true }));
                        closeSourceMenu();
                    });
                    sourceOptionList.appendChild(button);
                });

                sourceOptionList.dataset.rendered = '1';
                updateSourceLabel();
            };
            const filterSourceOptions = () => {
                createSourceOptions();
                if (!sourceOptionList) return;

                const keyword = normalizeSearch(sourceSearch?.value);
                let visibleOptions = 0;

                sourceOptionList.querySelectorAll('[data-route-source-option]').forEach((button) => {
                    const text = normalizeSearch(`${button.textContent} ${button.dataset.value}`);
                    const isVisible = !keyword || text.includes(keyword);
                    button.classList.toggle('hidden', !isVisible);
                    if (isVisible) visibleOptions += 1;
                });

                sourceEmpty?.classList.toggle('hidden', !keyword || visibleOptions > 0);
            };
            const openSourceMenu = () => {
                if (!sourceMenu || sourceSelect?.disabled) return;

                sourceMenu.classList.remove('hidden');
                sourceToggle?.setAttribute('aria-expanded', 'true');
                if (sourceSearch) {
                    sourceSearch.value = '';
                    filterSourceOptions();
                    setTimeout(() => sourceSearch.focus(), 0);
                }
            };
            const syncSubPreview = () => {
                if (!sourceSelect || !subPreview) return;

                const selectedOption = sourceSelect.selectedOptions?.[0];
                const nextSubName = selectedOption?.dataset?.nextSub || '';
                const currentSource = subPreview.dataset.currentSource || '';
                const currentSub = subPreview.dataset.currentSub || '';
                const previewValue = currentSub && sourceSelect.value === currentSource
                    ? currentSub
                    : nextSubName || 'Pilih sumber';
                subPreview.value = previewValue;
            };

            const sync = () => {
                const routeType = options.find((option) => option.checked)?.value || '';
                const isSource = routeType === 'sumber';
                const isSub = routeType === 'sub';

                sourceInputWrap?.classList.toggle('hidden', !isSource);
                sourceSelectWrap?.classList.toggle('hidden', !isSub);
                subWrap?.classList.toggle('hidden', !isSub);

                if (sourceInput) {
                    sourceInput.required = isSource;
                    sourceInput.disabled = !isSource;
                }

                if (sourceSelect) {
                    sourceSelect.required = isSub;
                    sourceSelect.disabled = !isSub;
                }

                if (sourceToggle) {
                    sourceToggle.disabled = !isSub;
                    sourceToggle.classList.toggle('opacity-70', !isSub);
                }

                if (sourceSearch) {
                    sourceSearch.disabled = !isSub;
                }

                if (isSub) {
                    createSourceOptions();
                    filterSourceOptions();
                    updateSourceLabel();
                    syncSubPreview();
                } else {
                    closeSourceMenu();
                    if (subPreview) {
                        subPreview.value = '';
                    }
                }
            };

            options.forEach((option) => option.addEventListener('change', sync));
            sourceToggle?.addEventListener('click', () => {
                if (sourceMenu?.classList.contains('hidden')) {
                    openSourceMenu();
                } else {
                    closeSourceMenu();
                }
            });
            sourceSearch?.addEventListener('input', filterSourceOptions);
            sourceSearch?.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') closeSourceMenu();
            });
            document.addEventListener('click', (event) => {
                if (sourceCombo && !sourceCombo.contains(event.target)) closeSourceMenu();
            });
            sourceSelect?.addEventListener('change', () => {
                updateSourceLabel();
                syncSubPreview();
            });
            sync();
        });
    };

    window.setupRouteTypeFields();

    // Lamp technical fields
    window.setupLampTechnicalFields = function setupLampTechnicalFields(scope = document) {
        scope.querySelectorAll('[data-lamp-technical-group]').forEach((group) => {
            if (group.dataset.lampTechnicalInitialized === '1') return;
            group.dataset.lampTechnicalInitialized = '1';

            const statusSelect = group.querySelector('[data-lamp-status-select]');
            const powerInput = group.querySelector('[data-power-usage-input]');
            if (!statusSelect || !powerInput) return;

            const defaultPower = Number(powerInput.dataset.defaultPower || 40) || 40;
            const sync = () => {
                const hasNoLamp = statusSelect.value === 'tidak_ada_lampu';
                if (hasNoLamp) {
                    powerInput.value = 0;
                    powerInput.min = 0;
                    powerInput.readOnly = true;
                    powerInput.classList.add('opacity-70');
                    return;
                }

                powerInput.min = 1;
                powerInput.readOnly = false;
                powerInput.classList.remove('opacity-70');
                if (Number(powerInput.value || 0) <= 0) {
                    powerInput.value = defaultPower;
                }
            };

            statusSelect.addEventListener('change', sync);
            sync();
        });
    };

    window.setupLampTechnicalFields();

    // Coordinate picker maps
    window.pjuMaps = window.pjuMaps || {};
    window.pjuCoordinatePickerState = window.pjuCoordinatePickerState || {};
    window.pjuCoordinatePickers = window.pjuCoordinatePickers || new Set();

    window.setupCoordinatePickers = function setupCoordinatePickers(scope = document) {
        scope.querySelectorAll('[data-coordinate-picker]').forEach((container) => {
            if (window.pjuCoordinatePickers.has(container.id)) {
                const existingMap = window.pjuMaps[container.id];
                if (existingMap) {
                    setTimeout(() => existingMap.invalidateSize(), 120);
                    setTimeout(() => existingMap.invalidateSize(), 350);
                }
                return;
            }

            const latInput = document.getElementById(container.dataset.latInput);
            const lngInput = document.getElementById(container.dataset.lngInput);
            const addressInput = document.getElementById(container.dataset.addressInput || container.dataset.streetInput);
            const adminInputs = {
                village: document.getElementById(container.dataset.villageInput),
                district: document.getElementById(container.dataset.districtInput),
                regency: document.getElementById(container.dataset.regencyInput),
                province: document.getElementById(container.dataset.provinceInput),
            };
            const notice = document.getElementById(container.dataset.noticeId);

            if (!window.L || !latInput || !lngInput) {
                container.innerHTML = '<div class="flex h-full min-h-64 items-center justify-center rounded-lg border border-dashed border-white/20 p-4 text-center text-sm font-semibold text-slate-400">Peta belum bisa dimuat. Pastikan koneksi internet aktif karena Leaflet diambil dari CDN.</div>';
                return;
            }

            window.pjuCoordinatePickers.add(container.id);
            const initialCoordinates = {
                latitude: Number(latInput.value || -7.965),
                longitude: Number(lngInput.value || 110.603),
            };
            const map = L.map(container).setView([initialCoordinates.latitude, initialCoordinates.longitude], 16);
            window.pjuMaps[container.id] = map;
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);
            window.pjuAddAdministrativeLayersToMap?.(map);
            const pinIcon = L.divIcon({ className: '', html: '<span style="display:flex;width:24px;height:24px;align-items:center;justify-content:center;border-radius:999px;background:#2563eb;border:3px solid white;box-shadow:0 10px 24px rgba(15,23,42,.35)"><span style="display:block;width:6px;height:6px;border-radius:999px;background:white"></span></span>', iconSize: [24, 24], iconAnchor: [12, 12] });
            const marker = L.marker([initialCoordinates.latitude, initialCoordinates.longitude], { draggable: true, icon: pinIcon }).addTo(map);
            window.pjuCoordinatePickerState[container.id] = {
                draft: initialCoordinates,
                latInput,
                lngInput,
                map,
                marker,
                notice,
                addressInput,
                adminInputs,
            };

            const updateDraft = (latlng, message = 'Titik dipilih. Tekan Set Lokasi untuk memasukkan ke input.') => {
                const latitude = Number(latlng.lat.toFixed(7));
                const longitude = Number(latlng.lng.toFixed(7));
                window.pjuCoordinatePickerState[container.id].draft = { latitude, longitude };
                marker.setLatLng([latitude, longitude]);
                if (notice) notice.textContent = `${message} (${latitude}, ${longitude})`;
            };

            map.on('click', (event) => updateDraft(event.latlng));
            marker.on('dragend', () => updateDraft(marker.getLatLng(), 'Pin digeser. Tekan Set Lokasi untuk memasukkan ke input.'));
            setTimeout(() => map.invalidateSize(), 120);
            setTimeout(() => map.invalidateSize(), 350);
            container.closest('dialog')?.addEventListener('toggle', () => {
                setTimeout(() => map.invalidateSize(), 120);
                setTimeout(() => map.invalidateSize(), 350);
            });
        });
    };

    document.addEventListener('click', (event) => {
        const locationButton = event.target.closest('[data-location-button]');
        if (locationButton) {
            getDeviceLocation(locationButton.dataset.mapId);
            return;
        }

        const setLocationButton = event.target.closest('[data-set-location-button]');
        if (setLocationButton) {
            setDraftLocationToInputs(setLocationButton.dataset.mapId);
        }
    });

    function getDeviceLocation(mapId) {
        const state = window.pjuCoordinatePickerState?.[mapId];
        if (!state) return;

        if (!navigator.geolocation) {
            if (state.notice) state.notice.textContent = 'Browser tidak mendukung fitur lokasi perangkat.';
            return;
        }

        if (state.notice) state.notice.textContent = 'Mengambil lokasi perangkat...';

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const latitude = Number(position.coords.latitude.toFixed(7));
                const longitude = Number(position.coords.longitude.toFixed(7));
                state.draft = { latitude, longitude };
                state.marker.setLatLng([latitude, longitude]);
                state.map.setView([latitude, longitude], 18, { animate: true });
                if (state.notice) state.notice.textContent = `Lokasi perangkat didapat. Tekan Set Lokasi untuk memasukkan ke input. (${latitude}, ${longitude})`;
            },
            () => {
                if (state.notice) state.notice.textContent = 'Gagal mengambil lokasi. Pastikan izin lokasi browser aktif.';
            },
            {
                enableHighAccuracy: true,
                maximumAge: 30000,
                timeout: 10000,
            },
        );
    }

    function setDraftLocationToInputs(mapId) {
        const state = window.pjuCoordinatePickerState?.[mapId];
        if (!state) return;

        const { latitude, longitude } = state.draft;
        const lookupRequestId = (state.lookupRequestId || 0) + 1;
        state.lookupRequestId = lookupRequestId;
        state.latInput.value = latitude;
        state.lngInput.value = longitude;
        if (state.addressInput) state.addressInput.value = '';
        Object.values(state.adminInputs || {}).forEach((input) => {
            if (input) input.value = '';
        });

        if (state.notice) state.notice.textContent = `Koordinat sudah dikirim ke input. Membaca layer administrasi... (${latitude}, ${longitude})`;

        Promise.all([
            (window.pjuAdministrativeAddressAtPoint?.(latitude, longitude) || Promise.resolve({})).catch(() => ({})),
            reverseAddress(latitude, longitude).catch(() => ({})),
        ]).then(([layerAddress, reverseAddressData]) => {
            if (state.lookupRequestId !== lookupRequestId) return;

            const mergedAddress = {
                ...reverseAddressData,
                ...Object.fromEntries(Object.entries(layerAddress || {}).filter(([, value]) => String(value || '').trim() !== '')),
            };

            if (state.addressInput) {
                state.addressInput.value = reverseAddressData.address || '';
            }

            Object.entries(state.adminInputs || {}).forEach(([key, input]) => {
                if (input) input.value = mergedAddress[key] || '';
            });

            const hasLayerAddress = ['village', 'district', 'regency', 'province'].some((key) => mergedAddress[key]);
            if (state.notice) {
                state.notice.textContent = hasLayerAddress
                    ? `Koordinat dan administrasi sudah diset dari layer. (${latitude}, ${longitude})`
                    : `Koordinat sudah diset, tetapi administrasi tidak ditemukan pada layer. (${latitude}, ${longitude})`;
            }
        });
    }

    async function reverseAddress(lat, lng) {
        try {
            const params = new URLSearchParams({ format: 'jsonv2', lat, lon: lng, zoom: 18, addressdetails: 1, 'accept-language': 'id' });
            const response = await fetch(`https://nominatim.openstreetmap.org/reverse?${params.toString()}`, { headers: { Accept: 'application/json' } });
            if (!response.ok) return {};
            const data = await response.json();
            return formatReverseAddress(data);
        } catch { return {}; }
    }

    function formatReverseAddress(data) {
        const address = data?.address || {};
        const excludedParts = new Set([
            String(address.postcode || '').trim().toLowerCase(),
            String(address.country || '').trim().toLowerCase(),
            String(address.country_code || '').trim().toLowerCase(),
            String(address.island || '').trim().toLowerCase(),
            'jawa',
            'java',
            'sumatra',
            'sumatera',
            'kalimantan',
            'sulawesi',
            'papua',
            'bali',
            'lombok',
        ].filter(Boolean));

        return {
            address: compactAddressFromGeocode(data, excludedParts),
            village: address.village || address.suburb || address.neighbourhood || '',
            district: address.district || address.city_district || '',
            regency: address.city || address.town || address.municipality || address.county || '',
            province: address.state || '',
        };
    }

    function compactAddressFromGeocode(data, excludedParts) {
        const address = data?.address || {};
        const firstPart = firstFilled([
            address.road,
            address.pedestrian,
            address.footway,
            address.path,
            address.residential,
            address.cycleway,
            data?.name,
        ]);
        const secondPart = firstFilled([
            address.hamlet,
            address.neighbourhood,
            address.suburb,
            address.village,
        ]);

        return [firstPart, secondPart]
            .map((part) => String(part || '').trim())
            .filter((part, index, parts) => part && !excludedParts.has(part.toLowerCase()) && parts.indexOf(part) === index)
            .join(', ');
    }

    function firstFilled(values) {
        return values.find((value) => String(value || '').trim() !== '') || '';
    }

    // Administrative layer support for coordinate pickers
    window.pjuAdministrativeLayerConfigs = window.pjuAdministrativeLayerConfigs || [
        { id: 'administrasi-area', label: 'Administrasi Area', url: '/data/peta/administrasi_ar_340320240807001259.zip', style: { color: '#075985', fillColor: '#38bdf8', fillOpacity: 0.16, opacity: 0.95, weight: 1.2 }, hover: { fillOpacity: 0.28, weight: 2.2 } },
        { id: 'administrasi-garis', label: 'Batas Administrasi', url: '/data/peta/administrasi_ln_340320240806233638.zip', style: { color: '#0e7490', fillOpacity: 0, opacity: 0.95, weight: 1.5, dashArray: '4 3' }, hover: { opacity: 1, weight: 2.4 } },
    ];
    window.pjuAdministrativeLayers = window.pjuAdministrativeLayers || [];
    window.pjuAdministrativeLayerPromise = window.pjuAdministrativeLayerPromise || null;
    window.pjuShpScriptPromise = window.pjuShpScriptPromise || null;

    window.pjuEnsureShpJs = async function pjuEnsureShpJs() {
        if (window.shp) return window.shp;
        if (!window.pjuShpScriptPromise) {
            window.pjuShpScriptPromise = new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = 'https://unpkg.com/shpjs@6.2.0/dist/shp.min.js';
                script.async = true;
                script.onload = () => resolve(window.shp);
                script.onerror = reject;
                document.head.appendChild(script);
            });
        }

        return window.pjuShpScriptPromise;
    };

    window.pjuLoadAdministrativeLayers = async function pjuLoadAdministrativeLayers() {
        if (window.pjuAdministrativeLayers.length) return window.pjuAdministrativeLayers;
        if (window.pjuAdministrativeLayerPromise) return window.pjuAdministrativeLayerPromise;

        window.pjuAdministrativeLayerPromise = (async () => {
            const parseShp = await window.pjuEnsureShpJs();
            const settled = await Promise.allSettled(window.pjuAdministrativeLayerConfigs.map(async (config) => {
                const response = await fetch(config.url);
                if (!response.ok) throw new Error(config.label);
                const payload = await parseShp(await response.arrayBuffer());
                return {
                    ...config,
                    data: Array.isArray(payload)
                        ? { type: 'FeatureCollection', features: payload.flatMap((item) => item.features || []) }
                        : payload,
                };
            }));

            window.pjuAdministrativeLayers.push(...settled.flatMap((item) => item.status === 'fulfilled' ? [item.value] : []));
            return window.pjuAdministrativeLayers;
        })();

        return window.pjuAdministrativeLayerPromise;
    };

    window.pjuAddAdministrativeLayersToMap = function pjuAddAdministrativeLayersToMap(map) {
        if (!map || map._pjuCoordinateAdminLayerGroup) return;

        window.pjuLoadAdministrativeLayers().then((layers) => {
            if (!layers.length || map._pjuCoordinateAdminLayerGroup) return;
            const group = L.layerGroup();

            layers.forEach((layerConfig) => {
                group.addLayer(L.geoJSON(layerConfig.data, {
                    style: (feature) => layerConfig.id === 'administrasi-area'
                        ? { ...layerConfig.style, fillColor: adminColor(feature) }
                        : layerConfig.style,
                    onEachFeature: (feature, featureLayer) => bindAdminFeature(layerConfig, feature, featureLayer),
                }));
            });

            group.addTo(map);
            map._pjuCoordinateAdminLayerGroup = group;
        }).catch(() => {});
    };

    window.pjuAdministrativeAddressAtPoint = async function pjuAdministrativeAddressAtPoint(latitude, longitude) {
        const layers = await window.pjuLoadAdministrativeLayers();
        const areaLayer = layers.find((layer) => layer.id === 'administrasi-area');
        const features = areaLayer?.data?.features || [];
        const point = [Number(longitude), Number(latitude)];
        const feature = features.find((item) => pointInGeometry(point, item.geometry));

        return feature ? adminAddressFromProperties(feature.properties || {}) : {};
    };

    function bindAdminFeature(layerConfig, feature, layer) {
        if (layer.setStyle) {
            const base = layerConfig.id === 'administrasi-area' ? { ...layerConfig.style, fillColor: adminColor(feature) } : layerConfig.style;
            layer.on({
                mouseover: () => layer.setStyle({ ...base, ...layerConfig.hover }),
                mouseout: () => layer.setStyle(base),
            });
        }

        const admin = adminAddressFromProperties(feature.properties || {});
        const name = admin.village || prop(feature.properties || {}, 'namobj') || layerConfig.label;
        layer.bindTooltip(name, { sticky: true, direction: 'top', opacity: 0.92 });
    }

    function adminAddressFromProperties(properties) {
        return {
            village: propAny(properties, ['wadmkd', 'desa', 'kelurahan', 'kalurahan', 'namobj']),
            district: propAny(properties, ['wadmkc', 'kecamatan', 'kapanewon', 'kecamata_1']),
            regency: propAny(properties, ['wadmkk', 'kabupaten', 'kab_kota', 'kabkot']),
            province: propAny(properties, ['wadmpr', 'provinsi', 'province', 'prov']),
        };
    }

    function pointInGeometry(point, geometry) {
        if (!geometry) return false;
        if (geometry.type === 'Polygon') return pointInPolygon(point, geometry.coordinates || []);
        if (geometry.type === 'MultiPolygon') return (geometry.coordinates || []).some((polygon) => pointInPolygon(point, polygon));

        return false;
    }

    function pointInPolygon(point, polygon) {
        if (!polygon.length || !pointInRing(point, polygon[0])) return false;

        return !polygon.slice(1).some((ring) => pointInRing(point, ring));
    }

    function pointInRing(point, ring) {
        const [x, y] = point;
        let inside = false;

        for (let i = 0, j = ring.length - 1; i < ring.length; j = i++) {
            const xi = Number(ring[i][0]);
            const yi = Number(ring[i][1]);
            const xj = Number(ring[j][0]);
            const yj = Number(ring[j][1]);
            const intersects = ((yi > y) !== (yj > y)) && (x < ((xj - xi) * (y - yi)) / ((yj - yi) || Number.EPSILON) + xi);
            if (intersects) inside = !inside;
        }

        return inside;
    }

    function adminColor(feature) {
        const key = propAny(feature.properties || {}, ['wadmkd', 'desa', 'kelurahan', 'kalurahan', 'namobj']) || JSON.stringify(feature.geometry?.type || 'area');
        const palette = ['#7dd3fc','#93c5fd','#a5b4fc','#c4b5fd','#f0abfc','#f9a8d4','#fca5a5','#fdba74','#fde68a','#bef264','#86efac','#5eead4'];
        let hash = 0;
        for (let index = 0; index < key.length; index++) hash = (hash * 31 + key.charCodeAt(index)) >>> 0;

        return palette[hash % palette.length];
    }

    function propAny(properties, keys) {
        for (const key of keys) {
            const value = prop(properties, key);
            if (value) return value;
        }

        return '';
    }

    function prop(properties, key) {
        const match = Object.keys(properties).find((item) => item.toLowerCase() === key.toLowerCase());

        return match && properties[match] != null ? String(properties[match]).trim() : '';
    }
