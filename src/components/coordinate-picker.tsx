"use client";

import L from "leaflet";
import { useEffect, useMemo, useState } from "react";
import { GeoJSON as GeoJSONLayer, MapContainer, Marker, TileLayer, useMap, useMapEvents } from "react-leaflet";
import { Check, Crosshair, MapPin } from "lucide-react";
import { Button } from "@/components/ui/button";
import { loadEmbeddedMapLayers, type EmbeddedMapLayer } from "@/lib/map-layers";
import { resolveAddressFromPin } from "@/lib/reverse-address";

type Coordinates = {
  latitude: number;
  longitude: number;
};

type CoordinatePickerProps = {
  value: Coordinates;
  onSet: (coordinates: Coordinates, address?: string) => void;
};

type CenterRequest = Coordinates & {
  id: number;
};

const pinIcon = L.divIcon({
  className: "",
  html: '<span style="display:flex;width:24px;height:24px;align-items:center;justify-content:center;border-radius:999px;background:#2563eb;border:3px solid white;box-shadow:0 10px 24px rgba(15,23,42,.35)"><span style="display:block;width:6px;height:6px;border-radius:999px;background:white"></span></span>',
  iconSize: [24, 24],
  iconAnchor: [12, 12],
});

function MapViewport({ centerRequest }: { centerRequest: CenterRequest }) {
  const map = useMap();

  useEffect(() => {
    map.setView([centerRequest.latitude, centerRequest.longitude], map.getZoom(), { animate: true });
  }, [centerRequest, map]);

  useEffect(() => {
    const id = window.setTimeout(() => map.invalidateSize(), 120);
    return () => window.clearTimeout(id);
  }, [map]);

  return null;
}

function ClickHandler({ onPick }: { onPick: (coordinates: Coordinates) => void }) {
  useMapEvents({
    click(event) {
      onPick({
        latitude: Number(event.latlng.lat.toFixed(7)),
        longitude: Number(event.latlng.lng.toFixed(7)),
      });
    },
  });

  return null;
}

export function CoordinatePicker({ value, onSet }: CoordinatePickerProps) {
  const [draft, setDraft] = useState(value);
  const [centerRequest, setCenterRequest] = useState<CenterRequest>({ ...value, id: 0 });
  const [mapLayers, setMapLayers] = useState<EmbeddedMapLayer[]>([]);
  const [notice, setNotice] = useState("Klik peta untuk memilih titik tiang.");
  const [isResolvingAddress, setIsResolvingAddress] = useState(false);

  const markerPosition = useMemo<[number, number]>(() => [draft.latitude, draft.longitude], [draft]);

  useEffect(() => {
    setDraft(value);
    setCenterRequest((current) => ({ ...value, id: current.id + 1 }));
  }, [value]);

  useEffect(() => {
    let isMounted = true;

    async function loadMapLayers() {
      try {
        const { layers } = await loadEmbeddedMapLayers();
        if (isMounted) {
          setMapLayers(layers);
        }
      } catch (error) {
        console.warn("Layer peta koordinat gagal dimuat.", error);
        if (isMounted) {
          setMapLayers([]);
        }
      }
    }

    void loadMapLayers();

    return () => {
      isMounted = false;
    };
  }, []);

  function setFromDeviceLocation() {
    if (!navigator.geolocation) {
      setNotice("Browser tidak mendukung lokasi perangkat.");
      return;
    }

    setNotice("Mengambil lokasi perangkat...");
    navigator.geolocation.getCurrentPosition(
      (position) => {
        const coordinates = {
          latitude: Number(position.coords.latitude.toFixed(7)),
          longitude: Number(position.coords.longitude.toFixed(7)),
        };
        setDraft(coordinates);
        setCenterRequest((current) => ({ ...coordinates, id: current.id + 1 }));
        onSet(coordinates);
        setNotice("Koordinat perangkat berhasil digunakan.");
      },
      () => setNotice("Gagal mengambil lokasi. Pastikan izin lokasi browser aktif."),
      {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 30000,
      },
    );
  }

  async function setCoordinatesFromPin() {
    setIsResolvingAddress(true);
    setNotice("Mencari alamat dari posisi pin...");

    try {
      const address = await resolveAddressFromPin(draft, mapLayers);
      onSet(draft, address || undefined);
      setNotice(address ? "Koordinat dan lokasi berhasil diset dari pin." : "Koordinat berhasil diset. Alamat tidak ditemukan.");
    } catch {
      onSet(draft);
      setNotice("Koordinat berhasil diset. Alamat gagal diambil.");
    } finally {
      setIsResolvingAddress(false);
    }
  }

  return (
    <div className="space-y-3 rounded-lg border bg-background/55 p-3">
      <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p className="text-sm font-bold text-foreground">Pinpoint koordinat</p>
          <p className="text-xs text-muted-foreground">{notice}</p>
        </div>
        <div className="flex gap-2">
          <Button type="button" variant="outline" size="sm" onClick={setFromDeviceLocation}>
            <Crosshair className="size-4" />
            Lokasi Saya
          </Button>
          <Button
            type="button"
            size="sm"
            disabled={isResolvingAddress}
            onClick={() => void setCoordinatesFromPin()}
          >
            <Check className="size-4" />
            {isResolvingAddress ? "Mencari..." : "Set Koordinat"}
          </Button>
        </div>
      </div>

      <div className="glass-panel h-64 overflow-hidden rounded-lg">
        <MapContainer center={[value.latitude, value.longitude]} zoom={17} scrollWheelZoom className="h-full min-h-64">
          <MapViewport centerRequest={centerRequest} />
          <ClickHandler
            onPick={(coordinates) => {
              setDraft(coordinates);
              setNotice("Titik dipilih. Tekan Set Koordinat untuk memasukkan ke form.");
            }}
          />
          <TileLayer
            attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
          />
          {mapLayers.map((mapLayer) => (
            <GeoJSONLayer
              key={mapLayer.id}
              data={mapLayer.data}
              interactive={false}
              style={{
                ...mapLayer.style,
                opacity: Math.min(Number(mapLayer.style.opacity ?? 1), 0.65),
                weight: Math.max(Number(mapLayer.style.weight ?? 1), 1),
              }}
            />
          ))}
          <Marker
            draggable
            position={markerPosition}
            icon={pinIcon}
            eventHandlers={{
              dragend(event) {
                const marker = event.target;
                const position = marker.getLatLng();
                setDraft({
                  latitude: Number(position.lat.toFixed(7)),
                  longitude: Number(position.lng.toFixed(7)),
                });
                setNotice("Pin digeser. Tekan Set Koordinat untuk memasukkan ke form.");
              },
            }}
          />
        </MapContainer>
      </div>

      <div className="flex items-center gap-2 text-xs font-semibold text-muted-foreground">
        <MapPin className="size-4 text-primary" />
        {draft.latitude.toFixed(7)}, {draft.longitude.toFixed(7)}
      </div>
    </div>
  );
}
