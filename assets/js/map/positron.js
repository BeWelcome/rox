import L from 'leaflet';
import 'maplibre-gl/dist/maplibre-gl.css';
import '@maplibre/maplibre-gl-leaflet';

// The site's base map: OpenFreeMap "Positron", light and plain (free, no key), drawn by
// MapLibre inside a Leaflet map.
export const POSITRON_ATTRIBUTION = '<a href="https://openfreemap.org" target="_blank">OpenFreeMap</a> &copy; <a href="https://www.openmaptiles.org/" target="_blank">OpenMapTiles</a> Data from <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a>';

export function addPositronLayer(map) {
    return L.maplibreGL({
        style: 'https://tiles.openfreemap.org/styles/positron',
        attribution: POSITRON_ATTRIBUTION,
    }).addTo(map);
}
