import { Toast } from 'bootstrap';
document.querySelectorAll('.p-toast[data-bs-autohide="true"]').forEach(el => new Toast(el).show());

const L = require("leaflet");

const locationMaps = document.querySelectorAll('[id^=location-map]')

locationMaps.forEach( locationMap => {
    const latitude = document.getElementById('latitude').value;
    const longitude = document.getElementById('longitude').value;

    const map = L.map(locationMap, {
        zoomControl: false,
        boxZoom: false,
        dragging: false,
        scrollWheelZoom: false,
        touchZoom: false,
        doubleClickZoom: false,
        keyboard: false,
    }).setView([latitude, longitude], 10)

    map.attributionControl.setPrefix(false)
    const markerIcon = L.icon({
        iconUrl: 'images/icons/marker_drop.png',
        iconShadowUrl: 'images/icons/marker_drop_shadow.png',
        iconSize: [25, 25],
        iconAnchor: [13, 0],
    });

    L.marker(new L.LatLng(latitude, longitude), {icon: markerIcon}).addTo(map)

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        subdomains: ['a', 'b', 'c']
    }).addTo(map)
})

