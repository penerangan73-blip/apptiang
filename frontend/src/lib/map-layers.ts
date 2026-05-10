import type L from "leaflet";

type ParsedShapefile = GeoJSON.FeatureCollection & {
  fileName?: string;
};

export type EmbeddedMapLayerConfig = {
  id: string;
  label: string;
  url: string;
  style: L.PathOptions;
  hoverStyle: L.PathOptions;
};

export type EmbeddedMapLayer = EmbeddedMapLayerConfig & {
  data: GeoJSON.FeatureCollection;
};

export const embeddedMapLayerConfigs: EmbeddedMapLayerConfig[] = [
  {
    id: "administrasi-area",
    label: "Administrasi Area",
    url: "/data/peta/administrasi_ar_340320240807001259.zip",
    style: { color: "#075985", fillColor: "#38bdf8", fillOpacity: 0.48, opacity: 0.95, weight: 1.4 },
    hoverStyle: { fillOpacity: 0.68, weight: 2.8 },
  },
  {
    id: "administrasi-garis",
    label: "Batas Administrasi",
    url: "/data/peta/administrasi_ln_340320240806233638.zip",
    style: { color: "#0e7490", fillOpacity: 0, opacity: 0.95, weight: 1.6, dashArray: "4 3" },
    hoverStyle: { opacity: 1, weight: 2.6 },
  },
  {
    id: "jalan",
    label: "Jalan",
    url: "/data/peta/jalan_ln_340320240820002322.zip",
    style: { color: "#ef4444", fillOpacity: 0, opacity: 0.9, weight: 1.4 },
    hoverStyle: { opacity: 1, weight: 2.4 },
  },
];

export async function loadEmbeddedMapLayers() {
  const { default: parseShapefile } = await import("shpjs");
  const settledLayers = await Promise.allSettled(
    embeddedMapLayerConfigs.map(async (config) => {
      const response = await fetch(config.url);
      if (!response.ok) {
        throw new Error(`Gagal memuat ${config.label} (${response.status}).`);
      }

      const buffer = await response.arrayBuffer();
      const parsed = await parseShapefile(buffer);
      return {
        ...config,
        data: normalizeFeatureCollection(parsed),
      };
    }),
  );

  return {
    layers: settledLayers.flatMap((result) => (result.status === "fulfilled" ? [result.value] : [])),
    failedCount: settledLayers.filter((result) => result.status === "rejected").length,
  };
}

function normalizeFeatureCollection(parsed: ParsedShapefile | ParsedShapefile[]): GeoJSON.FeatureCollection {
  if (Array.isArray(parsed)) {
    return {
      type: "FeatureCollection",
      features: parsed.flatMap((collection) => collection.features),
    };
  }

  return parsed;
}
