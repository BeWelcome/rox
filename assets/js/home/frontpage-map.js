import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { addPositronLayer } from '../map/positron';

// The drawing of the map search on the front page: a real map of Berlin (Kreuzberg) under the pins, that
// doesn't move.
export function showFrontpageMap(element) {
    const map = L.map(element, {
        center: [52.4983, 13.4040],
        zoom: 13,
        zoomControl: false,
        dragging: false,
        scrollWheelZoom: false,
        doubleClickZoom: false,
        boxZoom: false,
        keyboard: false,
        touchZoom: false,
        attributionControl: true,
    });
    map.attributionControl.setPrefix(false);
    addPositronLayer(map);
}
