    // Report pole selector
    document.querySelectorAll('[data-report-pole]').forEach((button) => {
        button.addEventListener('click', () => {
            const select = document.getElementById('report-pole-id');
            if (select) select.value = button.dataset.reportPole;
        });
    });

    // Dynamic route fields
    window.setupRouteTypeFields = function setupRouteTypeFields(scope = document) {
        scope.querySelectorAll('[data-route-type-select]').forEach((select) => {
            if (select.dataset.routeInitialized === '1') return;
            select.dataset.routeInitialized = '1';

            const form = select.closest('form') || document;
            const customerWrap = form.querySelector('[data-route-customer-wrap]');
            const subWrap = form.querySelector('[data-route-sub-wrap]');
            const customerInput = form.querySelector('[data-route-customer-input]');
            const subInput = form.querySelector('[data-route-sub-input]');

            const sync = () => {
                const isSource = select.value === 'sumber';
                const isSub = select.value === 'sub';

                customerWrap?.classList.toggle('hidden', !isSource && !isSub);
                subWrap?.classList.toggle('hidden', !isSub);

                if (customerInput) {
                    customerInput.required = isSource || isSub;
                    customerInput.disabled = !isSource && !isSub;
                }

                if (subInput) {
                    subInput.required = isSub;
                    subInput.disabled = !isSub;
                    if (!isSub) subInput.value = '';
                }
            };

            select.addEventListener('change', sync);
            sync();
        });
    };

    window.setupRouteTypeFields();

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
            const streetInput = document.getElementById(container.dataset.streetInput);
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
            const pinIcon = L.divIcon({ className: '', html: '<span style="display:flex;width:24px;height:24px;align-items:center;justify-content:center;border-radius:999px;background:#2563eb;border:3px solid white;box-shadow:0 10px 24px rgba(15,23,42,.35)"><span style="display:block;width:6px;height:6px;border-radius:999px;background:white"></span></span>', iconSize: [24, 24], iconAnchor: [12, 12] });
            const marker = L.marker([initialCoordinates.latitude, initialCoordinates.longitude], { draggable: true, icon: pinIcon }).addTo(map);
            window.pjuCoordinatePickerState[container.id] = {
                draft: initialCoordinates,
                latInput,
                lngInput,
                map,
                marker,
                notice,
                streetInput,
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
        state.latInput.value = latitude;
        state.lngInput.value = longitude;

        if (state.notice) state.notice.textContent = `Koordinat sudah dikirim ke input. Mengambil alamat marker... (${latitude}, ${longitude})`;

        if (state.streetInput) {
            reverseAddress(latitude, longitude).then((address) => {
                if (address) {
                    state.streetInput.value = address;
                    if (state.notice) state.notice.textContent = `Koordinat dan alamat marker sudah diset. (${latitude}, ${longitude})`;
                } else if (state.notice) {
                    state.notice.textContent = `Koordinat sudah diset, tetapi alamat marker tidak ditemukan. (${latitude}, ${longitude})`;
                }
            });
        } else if (state.notice) {
            state.notice.textContent = `Koordinat sudah dikirim ke input Latitude dan Longitude. (${latitude}, ${longitude})`;
        }
    }

    async function reverseAddress(lat, lng) {
        try {
            const params = new URLSearchParams({ format: 'jsonv2', lat, lon: lng, zoom: 18, addressdetails: 1, 'accept-language': 'id' });
            const response = await fetch(`https://nominatim.openstreetmap.org/reverse?${params.toString()}`, { headers: { Accept: 'application/json' } });
            if (!response.ok) return '';
            const data = await response.json();
            return formatReverseAddress(data);
        } catch { return ''; }
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

        return String(data?.display_name || '')
            .split(',')
            .map((part) => part.trim())
            .filter((part) => part && !excludedParts.has(part.toLowerCase()))
            .join(', ');
    }
