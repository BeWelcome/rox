import { Toast } from 'bootstrap';
document.querySelectorAll('.p-toast[data-bs-autohide="true"]').forEach(el => new Toast(el).show());

// Profile tabs: horizontal slider with arrows when the tabs don't fit (mobile)
document.querySelectorAll('[data-tabs-scroller]').forEach(scroller => {
    const tabs = scroller.querySelector('.p-hero__tabs');
    const update = () => {
        const maxScroll = tabs.scrollWidth - tabs.clientWidth;
        scroller.classList.toggle('can-scroll-prev', tabs.scrollLeft > 1);
        scroller.classList.toggle('can-scroll-next', tabs.scrollLeft < maxScroll - 1);
    };

    const active = tabs.querySelector('.p-hero__tab--active');
    if (active) {
        tabs.scrollTo({
            left: active.offsetLeft - (tabs.clientWidth - active.offsetWidth) / 2,
            behavior: 'instant',
        });
    }

    scroller.querySelectorAll('[data-tabs-scroll]').forEach(arrow => {
        arrow.addEventListener('click', () => {
            tabs.scrollBy({left: Number(arrow.dataset.tabsScroll) * tabs.clientWidth * 0.7});
        });
    });

    tabs.addEventListener('scroll', update, {passive: true});
    window.addEventListener('resize', update);
    update();
});

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

