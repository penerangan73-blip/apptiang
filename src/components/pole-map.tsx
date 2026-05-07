"use client";

import L from "leaflet";
import { useEffect, useMemo, useState } from "react";
import { GeoJSON as GeoJSONLayer, MapContainer, Marker, Popup, TileLayer, useMap } from "react-leaflet";
import { StatusBadge } from "@/components/status-badge";
import { embeddedMapLayerConfigs, loadEmbeddedMapLayers, type EmbeddedMapLayer } from "@/lib/map-layers";
import type { Pole, PoleCondition } from "@/lib/types";

type PoleMapProps = {
  poles: Pole[];
  onSelect: (pole: Pole) => void;
};

const markerColors: Record<PoleCondition, string> = {
  baik: "#059669",
  rusak: "#e11d48",
  perbaikan: "#d97706",
};

function createIcon(condition: PoleCondition) {
  return L.divIcon({
    className: "",
    html: `<span style="display:block;width:18px;height:18px;border-radius:999px;background:${markerColors[condition]};border:3px solid white;box-shadow:0 8px 20px rgba(15,23,42,.22)"></span>`,
    iconSize: [18, 18],
    iconAnchor: [9, 9],
  });
}

function MapViewport({ mapLayers, poles }: { mapLayers: EmbeddedMapLayer[]; poles: Pole[] }) {
  const map = useMap();

  useEffect(() => {
    if (!poles.length) {
      if (mapLayers.length) {
        const bounds = L.latLngBounds([]);
        mapLayers.forEach((mapLayer) => {
          const layerBounds = L.geoJSON(mapLayer.data).getBounds();
          if (layerBounds.isValid()) {
            bounds.extend(layerBounds);
          }
        });

        if (bounds.isValid()) {
          map.fitBounds(bounds, {
            animate: true,
            maxZoom: 12,
            padding: [36, 36],
          });
          return;
        }
      }

      map.setView([-7.965, 110.603], 11, { animate: true });
      return;
    }

    if (poles.length === 1) {
      map.setView([poles[0].latitude, poles[0].longitude], 14, { animate: true });
      return;
    }

    const bounds = L.latLngBounds(poles.map((pole) => [pole.latitude, pole.longitude]));
    map.fitBounds(bounds, {
      animate: true,
      maxZoom: 12,
      padding: [36, 36],
    });
  }, [mapLayers, poles, map]);

  return null;
}

export function PoleMap({ poles, onSelect }: PoleMapProps) {
  const [mapLayers, setMapLayers] = useState<EmbeddedMapLayer[]>([]);
  const [mapLayerStatus, setMapLayerStatus] = useState<"loading" | "ready" | "error">("loading");
  const [failedLayerCount, setFailedLayerCount] = useState(0);

  useEffect(() => {
    let isMounted = true;

    async function loadMapLayers() {
      try {
        const { failedCount, layers } = await loadEmbeddedMapLayers();

        if (isMounted) {
          setMapLayers(layers);
          setFailedLayerCount(failedCount);
          setMapLayerStatus(layers.length ? "ready" : "error");
        }
      } catch (error) {
        console.warn("Layer peta gagal dimuat.", error);
        if (isMounted) {
          setMapLayers([]);
          setFailedLayerCount(embeddedMapLayerConfigs.length);
          setMapLayerStatus("error");
        }
      }
    }

    void loadMapLayers();

    return () => {
      isMounted = false;
    };
  }, []);

  const center = useMemo<[number, number]>(() => {
    if (!poles.length) {
      return [-7.965, 110.603];
    }

    return [poles[0].latitude, poles[0].longitude];
  }, [poles]);

  return (
    <div className="relative h-full min-h-[360px]">
      <MapContainer center={center} zoom={11} scrollWheelZoom className="h-full min-h-[360px]">
        <MapViewport mapLayers={mapLayers} poles={poles} />
        <TileLayer
          attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
          url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
        />
        {mapLayers.map((mapLayer) => (
          <GeoJSONLayer
            key={mapLayer.id}
            data={mapLayer.data}
            style={mapLayer.style}
            onEachFeature={(feature, layer) => bindEmbeddedMapFeature(mapLayer, feature, layer)}
          />
        ))}
        {poles.map((pole) => (
          <Marker
            key={pole.id}
            position={[pole.latitude, pole.longitude]}
            icon={createIcon(pole.condition)}
            eventHandlers={{ click: () => onSelect(pole) }}
          >
            <Popup>
              <div className="min-w-44 space-y-2">
                <p className="text-base font-extrabold text-slate-950">{pole.customer_id}</p>
                <p className="text-sm font-semibold leading-5 text-slate-700">{pole.street_name}</p>
                <StatusBadge condition={pole.condition} />
              </div>
            </Popup>
          </Marker>
        ))}
      </MapContainer>
      {(mapLayerStatus !== "ready" || failedLayerCount > 0) && (
        <div className="pointer-events-none absolute left-3 top-3 z-[400] rounded-md bg-white/95 px-3 py-2 text-xs font-bold text-slate-700 shadow-sm ring-1 ring-slate-200">
          {mapLayerStatus === "loading"
            ? "Memuat folder peta..."
            : mapLayerStatus === "error"
              ? "Layer folder peta gagal dimuat"
              : `${mapLayers.length} layer peta dimuat, ${failedLayerCount} gagal`}
        </div>
      )}
    </div>
  );
}

function bindEmbeddedMapFeature(mapLayer: EmbeddedMapLayer, feature: GeoJSON.Feature, layer: L.Layer) {
  if (layer instanceof L.Path) {
    const baseStyle = getFeatureStyle(mapLayer, feature);
    const hoverStyle = { ...baseStyle, ...mapLayer.hoverStyle };

    layer.setStyle(baseStyle);
    layer.bringToFront();
    layer.on({
      mouseover() {
        layer.setStyle(hoverStyle);
      },
      mouseout() {
        layer.setStyle(baseStyle);
      },
    });
  }

  layer.bindPopup(createEmbeddedMapPopup(mapLayer.label, feature.properties));
}

function getFeatureStyle(mapLayer: EmbeddedMapLayer, feature: GeoJSON.Feature): L.PathOptions {
  if (mapLayer.id !== "administrasi-area") {
    return mapLayer.style;
  }

  const color = getAdministrationColor(feature);

  return {
    ...mapLayer.style,
    color: "#075985",
    fillColor: color,
  };
}

function getAdministrationColor(feature: GeoJSON.Feature) {
  const key =
    getProperty(feature.properties, "desa") ||
    getProperty(feature.properties, "wadmkd") ||
    getProperty(feature.properties, "kecamata_1") ||
    getProperty(feature.properties, "fid_admin_") ||
    JSON.stringify(feature.geometry?.bbox ?? feature.geometry?.type ?? "administrasi");
  const palette = [
    "#7dd3fc",
    "#93c5fd",
    "#a5b4fc",
    "#c4b5fd",
    "#f0abfc",
    "#f9a8d4",
    "#fca5a5",
    "#fdba74",
    "#fde68a",
    "#bef264",
    "#86efac",
    "#5eead4",
    "#99f6e4",
    "#bae6fd",
    "#ddd6fe",
    "#fecdd3",
    "#bbf7d0",
    "#d9f99d",
  ];
  let hash = 0;

  for (let index = 0; index < key.length; index += 1) {
    hash = (hash * 31 + key.charCodeAt(index)) >>> 0;
  }

  return palette[hash % palette.length];
}

function createEmbeddedMapPopup(layerLabel: string, properties: GeoJSON.GeoJsonProperties): string {
  const name = getProperty(properties, "namobj") || getProperty(properties, "desa") || getProperty(properties, "wadmkd") || layerLabel;
  const district = getProperty(properties, "wadmkc") || getProperty(properties, "kecamata_1");
  const regency = getProperty(properties, "wadmkk") || getProperty(properties, "kabupaten");
  const area = getProperty(properties, "luas") || getProperty(properties, "shape_area");
  const remark = getProperty(properties, "remark");
  const fcode = getProperty(properties, "fcode");

  return `
    <div class="min-w-48 space-y-1">
      <p class="text-xs font-bold uppercase tracking-wide text-slate-500">${escapeHtml(layerLabel)}</p>
      <p class="text-base font-extrabold text-slate-950">${escapeHtml(name)}</p>
      ${district ? `<p class="text-sm font-semibold text-slate-700">Kecamatan ${escapeHtml(district)}</p>` : ""}
      ${regency ? `<p class="text-sm text-slate-600">${escapeHtml(regency)}</p>` : ""}
      ${remark ? `<p class="text-sm text-slate-600">${escapeHtml(remark)}</p>` : ""}
      ${area ? `<p class="pt-1 text-xs font-bold uppercase tracking-wide text-slate-500">Luas: ${escapeHtml(formatArea(area))}</p>` : ""}
      ${fcode ? `<p class="text-xs font-semibold text-slate-500">Kode: ${escapeHtml(fcode)}</p>` : ""}
    </div>
  `;
}

function getProperty(properties: GeoJSON.GeoJsonProperties, key: string): string {
  if (!properties) {
    return "";
  }

  const matchedKey = Object.keys(properties).find((propertyKey) => propertyKey.toLowerCase() === key.toLowerCase());
  const value = matchedKey ? properties[matchedKey] : undefined;

  return value === null || value === undefined ? "" : String(value).trim();
}

function formatArea(value: string): string {
  const parsed = Number(value);

  if (!Number.isFinite(parsed)) {
    return value;
  }

  return `${parsed.toLocaleString("id-ID", { maximumFractionDigits: 2 })} ha`;
}

function escapeHtml(value: string): string {
  return value
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}
