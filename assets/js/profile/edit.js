import 'ekko-lightbox';
import MicroModal from 'micromodal';

const L = require("leaflet");

const editLanguages = document.querySelectorAll("[data-edit-language]");

editLanguages.forEach(editLanguage => {
    editLanguage.addEventListener("click", e => {
        editLanguages.forEach(editLanguage => {
            editLanguage.classList.add('btn-outline-primary')
            editLanguage.classList.remove('btn-primary')
        })

        const languages = document.querySelectorAll('[id^=profile-language-]')
        languages.forEach(language => {
            language.classList.add('u:hidden!')
        })

        const language = e.target.dataset.editLanguage
        const activeLanguage = document.getElementById("profile-language-" + language)
        const editLanguageButton = document.querySelector("[data-edit-language=" + language + "]")

        activeLanguage.classList.remove('u:hidden!')
        editLanguageButton.classList.add("btn-primary")
        editLanguageButton.classList.remove("btn-outline-primary")
    })
})

const deleteLanguages = document.querySelectorAll("[data-delete-language]")

deleteLanguages.forEach( deleteLanguage => {
    deleteLanguage.addEventListener('click', (e) => {
        const modalId = 'delete-' + e.target.dataset.deleteLanguage;
        MicroModal.show(modalId);
    })
})

const locationMaps = document.querySelectorAll('[id^=location-map]')

locationMaps.forEach( locationMap => {
    const latitude = document.getElementById('latitude').value;
    const longitude = document.getElementById('longitude').value;

    const map = L.map(locationMap, {
        zoomControl: false,
        boxZoom: false
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

