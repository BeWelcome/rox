import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Map of the activities on the page (templates/activity/_macros.html.twig, map_data): one marker per
// .js-activity-marker, the map framed around them. The map stays hidden when no activity has a place.
const container = document.querySelector('.js-activities-map');
const markers = container ? Array.from(container.querySelectorAll('.js-activity-marker')) : [];

if (markers.length > 0) {
    container.hidden = false;

    const map = L.map('activity-map', { scrollWheelZoom: false });
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="/about/credits#OSM">OpenStreetMap contributors</a>',
        subdomains: ['a', 'b', 'c'],
    }).addTo(map);

    const icon = L.icon({
        iconUrl: '/images/marker.png',
        iconRetinaUrl: '/images/marker-2x.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
    });

    const points = markers.map((element) => {
        const point = [parseFloat(element.dataset.latitude), parseFloat(element.dataset.longitude)];
        L.marker(point, { icon }).addTo(map).bindPopup(popup(element.dataset));
        return point;
    });

    if (points.length === 1) {
        map.setView(points[0], 12);
    } else {
        map.fitBounds(points, { padding: [24, 24], maxZoom: 12 });
    }
}

// The popup content as elements, so titles written by members are never parsed as HTML
function popup(data) {
    const content = document.createElement('div');
    const title = document.createElement('a');
    title.href = data.url;
    title.textContent = data.title;
    title.style.fontWeight = '600';
    const details = document.createElement('div');
    details.textContent = data.when + ' · ' + data.place;
    content.append(title, details);
    return content;
}
