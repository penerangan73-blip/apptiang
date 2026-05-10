import type { EmbeddedMapLayer } from "@/lib/map-layers";

export type Coordinates = {
  latitude: number;
  longitude: number;
};

type AddressParts = {
  road?: string;
  village?: string;
  district?: string;
  regency?: string;
};

type NominatimResponse = {
  display_name?: string;
  address?: Record<string, string | undefined>;
};

const EARTH_RADIUS_METERS = 6371008.8;
const ROAD_MATCH_THRESHOLD_METERS = 500;
const nominatimCache = new Map<string, string>();

export async function resolveAddressFromPin(coordinates: Coordinates, mapLayers: EmbeddedMapLayer[]) {
  const localAddress = resolveLocalAddress(coordinates, mapLayers);

  if (isAddressComplete(localAddress)) {
    return formatAddress(localAddress);
  }

  const onlineAddress = await resolveNominatimAddress(coordinates);
  const mergedAddress = mergeAddressParts(localAddress, onlineAddress);

  return formatAddress(mergedAddress) || onlineAddress?.displayName || formatAddress(localAddress);
}

function resolveLocalAddress(coordinates: Coordinates, mapLayers: EmbeddedMapLayer[]): AddressParts {
  return {
    ...findAdministrationAddress(coordinates, mapLayers),
    road: findNearestRoadName(coordinates, mapLayers),
  };
}

function findAdministrationAddress(coordinates: Coordinates, mapLayers: EmbeddedMapLayer[]): AddressParts {
  const administrationLayer = mapLayers.find((layer) => layer.id === "administrasi-area");

  if (!administrationLayer) {
    return {};
  }

  const point: [number, number] = [coordinates.longitude, coordinates.latitude];

  for (const feature of administrationLayer.data.features) {
    if (!feature.geometry || !isPointInGeometry(point, feature.geometry)) {
      continue;
    }

    return {
      village: getProperty(feature.properties, "wadmkd") || getProperty(feature.properties, "desa"),
      district: getProperty(feature.properties, "wadmkc") || getProperty(feature.properties, "kecamata_1"),
      regency: getProperty(feature.properties, "wadmkk") || getProperty(feature.properties, "kabupaten"),
    };
  }

  return {};
}

function findNearestRoadName(coordinates: Coordinates, mapLayers: EmbeddedMapLayer[]) {
  const roadLayer = mapLayers.find((layer) => layer.id === "jalan");

  if (!roadLayer) {
    return "";
  }

  const point: [number, number] = [coordinates.longitude, coordinates.latitude];
  let nearestRoad = "";
  let nearestDistance = Number.POSITIVE_INFINITY;

  for (const feature of roadLayer.data.features) {
    const roadName = getProperty(feature.properties, "remark") || getProperty(feature.properties, "namobj");

    if (!feature.geometry || !roadName) {
      continue;
    }

    const distance = getGeometryDistance(point, feature.geometry);
    if (distance < nearestDistance) {
      nearestDistance = distance;
      nearestRoad = roadName;
    }
  }

  if (nearestDistance > ROAD_MATCH_THRESHOLD_METERS) {
    return "";
  }

  return formatRoadName(nearestRoad);
}

function isPointInGeometry(point: [number, number], geometry: GeoJSON.Geometry) {
  if (geometry.type === "Polygon") {
    return isPointInPolygon(point, geometry.coordinates);
  }

  if (geometry.type === "MultiPolygon") {
    return geometry.coordinates.some((polygon) => isPointInPolygon(point, polygon));
  }

  return false;
}

function isPointInPolygon(point: [number, number], polygon: GeoJSON.Position[][]) {
  const [outerRing, ...holes] = polygon;

  if (!outerRing || !isPointInRing(point, outerRing)) {
    return false;
  }

  return !holes.some((ring) => isPointInRing(point, ring));
}

function isPointInRing(point: [number, number], ring: GeoJSON.Position[]) {
  let isInside = false;
  const [pointLng, pointLat] = point;

  for (let current = 0, previous = ring.length - 1; current < ring.length; previous = current++) {
    const [currentLng, currentLat] = ring[current];
    const [previousLng, previousLat] = ring[previous];
    const crossesLatitude = currentLat > pointLat !== previousLat > pointLat;
    const intersectionLng = ((previousLng - currentLng) * (pointLat - currentLat)) / (previousLat - currentLat || Number.EPSILON) + currentLng;

    if (crossesLatitude && pointLng < intersectionLng) {
      isInside = !isInside;
    }
  }

  return isInside;
}

function getGeometryDistance(point: [number, number], geometry: GeoJSON.Geometry) {
  if (geometry.type === "LineString") {
    return getLineDistance(point, geometry.coordinates);
  }

  if (geometry.type === "MultiLineString") {
    return Math.min(...geometry.coordinates.map((line) => getLineDistance(point, line)));
  }

  return Number.POSITIVE_INFINITY;
}

function getLineDistance(point: [number, number], line: GeoJSON.Position[]) {
  let nearestDistance = Number.POSITIVE_INFINITY;

  for (let index = 0; index < line.length - 1; index += 1) {
    nearestDistance = Math.min(nearestDistance, getSegmentDistance(point, line[index], line[index + 1]));
  }

  return nearestDistance;
}

function getSegmentDistance(point: [number, number], start: GeoJSON.Position, end: GeoJSON.Position) {
  const originLatitude = point[1];
  const projectedPoint = projectCoordinate(point, originLatitude);
  const projectedStart = projectCoordinate([start[0], start[1]], originLatitude);
  const projectedEnd = projectCoordinate([end[0], end[1]], originLatitude);
  const segmentX = projectedEnd.x - projectedStart.x;
  const segmentY = projectedEnd.y - projectedStart.y;
  const segmentLengthSquared = segmentX * segmentX + segmentY * segmentY;

  if (segmentLengthSquared === 0) {
    return Math.hypot(projectedPoint.x - projectedStart.x, projectedPoint.y - projectedStart.y);
  }

  const progress = Math.max(
    0,
    Math.min(
      1,
      ((projectedPoint.x - projectedStart.x) * segmentX + (projectedPoint.y - projectedStart.y) * segmentY) / segmentLengthSquared,
    ),
  );
  const closestX = projectedStart.x + progress * segmentX;
  const closestY = projectedStart.y + progress * segmentY;

  return Math.hypot(projectedPoint.x - closestX, projectedPoint.y - closestY);
}

function projectCoordinate(coordinate: [number, number], originLatitude: number) {
  const [longitude, latitude] = coordinate;

  return {
    x: degreesToRadians(longitude) * EARTH_RADIUS_METERS * Math.cos(degreesToRadians(originLatitude)),
    y: degreesToRadians(latitude) * EARTH_RADIUS_METERS,
  };
}

async function resolveNominatimAddress(coordinates: Coordinates) {
  const cacheKey = `${coordinates.latitude.toFixed(5)},${coordinates.longitude.toFixed(5)}`;
  const cachedAddress = nominatimCache.get(cacheKey);

  if (cachedAddress) {
    return parseNominatimAddress({ display_name: cachedAddress });
  }

  const controller = new AbortController();
  const timeout = window.setTimeout(() => controller.abort(), 4000);

  try {
    const params = new URLSearchParams({
      format: "jsonv2",
      lat: String(coordinates.latitude),
      lon: String(coordinates.longitude),
      zoom: "18",
      addressdetails: "1",
      "accept-language": "id",
    });
    const response = await fetch(`https://nominatim.openstreetmap.org/reverse?${params.toString()}`, {
      headers: {
        Accept: "application/json",
      },
      signal: controller.signal,
    });

    if (!response.ok) {
      return null;
    }

    const data = (await response.json()) as NominatimResponse;
    if (data.display_name) {
      nominatimCache.set(cacheKey, data.display_name);
    }

    return parseNominatimAddress(data);
  } catch {
    return null;
  } finally {
    window.clearTimeout(timeout);
  }
}

function parseNominatimAddress(data: NominatimResponse | null) {
  if (!data) {
    return null;
  }

  const address = data.address ?? {};

  return {
    displayName: data.display_name ?? "",
    parts: {
      road: address.road || address.pedestrian || address.footway || address.path || address.neighbourhood,
      village: address.village || address.suburb || address.hamlet || address.town,
      district: address.city_district || address.district || address.county,
      regency: address.city || address.municipality || address.state,
    } satisfies AddressParts,
  };
}

function mergeAddressParts(localAddress: AddressParts, onlineAddress: ReturnType<typeof parseNominatimAddress>): AddressParts {
  return {
    road: localAddress.road || onlineAddress?.parts.road,
    village: localAddress.village || onlineAddress?.parts.village,
    district: localAddress.district || onlineAddress?.parts.district,
    regency: localAddress.regency || onlineAddress?.parts.regency,
  };
}

function isAddressComplete(address: AddressParts) {
  return Boolean(address.road && address.village && address.district && address.regency);
}

function formatAddress(address: AddressParts) {
  return [address.road, prefixValue("Desa", address.village), prefixValue("Kecamatan", address.district), address.regency]
    .filter(Boolean)
    .join(", ");
}

function formatRoadName(value: string) {
  const normalized = value.replace(/\?/g, "-").replace(/\s+-\s+/g, " - ").trim();

  if (/^(jalan|jl\.|ruas)\b/i.test(normalized)) {
    return normalized;
  }

  return `Ruas ${normalized}`;
}

function prefixValue(prefix: string, value?: string) {
  if (!value || new RegExp(`^${prefix}\\b`, "i").test(value)) {
    return value;
  }

  return `${prefix} ${value}`;
}

function getProperty(properties: GeoJSON.GeoJsonProperties, key: string): string {
  if (!properties) {
    return "";
  }

  const matchedKey = Object.keys(properties).find((propertyKey) => propertyKey.toLowerCase() === key.toLowerCase());
  const value = matchedKey ? properties[matchedKey] : undefined;

  return value === null || value === undefined ? "" : String(value).trim();
}

function degreesToRadians(value: number) {
  return (value * Math.PI) / 180;
}
