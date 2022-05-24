import Autocomplete from "@trevoreyre/autocomplete-js";
const L = require('leaflet');

export function initializeSingleAutoComplete(url, cssClass = "js-search-picker", identifier = "_name") {
    const locationSuggests = document.getElementsByClassName(cssClass);
    new LocationSuggest(locationSuggests.item(0), url, identifier);
}

export function initializeMultipleAutoCompletes(url, cssClass = "js-search-picker", identifier = "_name") {
    Array.from(document.getElementsByClassName(cssClass)).forEach(
        (locationSuggest) => new LocationSuggest(locationSuggest, url, identifier)
    );
}

let lastGroup = '';
function initializeAutoComplete(element, searchUrl) {
    new Autocomplete(element, {
        // Search function can return a promise
        // which resolves with an array of
        // results. In this case we're using
        // the Wikipedia search API.
        search: input => {
            const url = searchUrl + `?term=${encodeURI(input)}`

            return new Promise(resolve => {
                if (input.length < 1) {
                    return resolve([])
                }

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        const places = data.locations.map((result, index) => {
                            return {...result, index}
                        })
                        resolve(places)
                    })
            })
        },

        debounceTime: 1000,

        // Control the rendering of result items.
        // Let's show the title and snippet
        // from the Wikipedia results
        renderResult: (result, props) => {
            let group = ''
            if (result.type === "refine") {
                return `
                <li class="suggest-group">${result.title}</li>
                    <li ${props}>
                        <div class="wiki-title">
                            ${result.text}
                        </div>
                </li>
                `
            }
            if (result.type !== lastGroup) {
                group = `<li class="suggest-group">${result.type}</li>`
                lastGroup = result.type
            }
            return `
      ${group}
      <li ${props}>
      <div class="u-flex u-flex-row u-justify-between align-items-center">
        <div>
            <div class="suggest-name">
              ${result.name}
            </div>
            <div class="suggest-country">
              ${result.admin1 ? result.admin1 + ', ' : ''}${result.country}
            </div>
            </div>
        <div>
            <div id="suggest-map-${result.id}" class="suggest-map"></div>
            <input type="hidden" id="latitude-${result.id}" value="${result.latitude}"><input type="hidden" id="longitude-${result.id}" value="${result.longitude}">
        </div>
        </div>
      </li>
    `
        },

        getResultValue: result => result.title,

        onUpdate: (results, selectedIndex) => {
            if (results.length !== 0) {
                initializeSuggestionMaps()
            } else {

            }
        },

        onSubmit: (result) => {
            id = id.replace(this.identifier, '');
            document.getElementById("new_suggest_geoname_id").value = result.id;
            document.getElementById("new_suggest_latitude").value = result.latitude;
            document.getElementById("new_suggest_longitude").value = result.longitude;
            destroySuggestionMaps()
        }
    })
}

class LocationSuggest {
    constructor(element, url, identifier) {
        this.id = element.id;
        this.searchUrl = url;
        this.identifier = identifier;
        this.autoComplete = new Autocomplete(element, {
            // Search function can return a promise
            // which resolves with an array of
            // results. In this case we're using
            // the Wikipedia search API.
            search: input => {
                const url = this.searchUrl + `?term=${encodeURI(input)}`

                return new Promise(resolve => {
                    if (input.length < 1) {
                        return resolve([])
                    }

                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            const places = data.locations.map((result, index) => {
                                return {...result, index}
                            })
                            resolve(places)
                        })
                })
            },

            debounceTime: 1000,

            // Control the rendering of result items.
            // Let's show the title and snippet
            // from the Wikipedia results
            renderResult: (result, props) => {
                let group = ''
                if (result.type === "refine") {
                    return `
                <li class="suggest-group">${result.type}</li>
                    <li ${props}>
                        <div class="wiki-title">
                            ${result.text}
                        </div>
                </li>
                `
                }
                if (result.type !== lastGroup) {
                    group = `<li class="suggest-group">${result.type}</li>`
                    lastGroup = result.type
                }
                return `
      ${group}
      <li ${props}>
      <div class="u-flex u-flex-row u-justify-between align-items-center">
        <div>
            <div class="suggest-name">
              ${result.name}
            </div>
            <div class="suggest-country">
              ${result.admin1 ? result.admin1 + ', ' : ''}${result.country}
            </div>
            </div>
        <div>
            <div id="suggest-map-${result.id}" class="suggest-map"></div>
            <input type="hidden" id="latitude-${result.id}" value="${result.latitude}"><input type="hidden" id="longitude-${result.id}" value="${result.longitude}">
        </div>
        </div>
      </li>
    `
            },

            getResultValue: result => result.name + ', ' + (result.admin1 ? result.admin1 + ', ' : '') + result.country,

            onUpdate: (results, selectedIndex) => {
                if (results.length !== 0) {
                    initializeSuggestionMaps()
                } else {

                }
            },

            onSubmit: (result) => {
                const id = this.id.replace(this.identifier, '');
                document.getElementById(id + "_fullname").value = result.name + ', ' + (result.admin1 ? result.admin1 + ', ' : '') + result.country;
                document.getElementById(id + "_name").value = result.name + ', ' + (result.admin1 ? result.admin1 + ', ' : '') + result.country;
                document.getElementById(id + "_geoname_id").value = result.id;
                document.getElementById(id + "_latitude").value = result.latitude;
                document.getElementById(id + "_longitude").value = result.longitude;
                destroySuggestionMaps()
            }
        })
    }
}


function initializeSuggestionMaps() {
    const maps = document.querySelectorAll('[id^="suggest-map-"]');

    maps.forEach(initializeMap);

    function initializeMap(value) {
        const geonameId = value.id.replace('suggest-map-', '');
        const latitude = document.getElementById('latitude-' + geonameId).value;
        const longitude = document.getElementById('longitude-' + geonameId).value;
        const map = L.map(value.id, {
            zoomControl: false,
            boxZoom: false
        }).setView([latitude, longitude], 10);

        map.attributionControl.setPrefix(false);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            subdomains: ['a', 'b', 'c']
        }).addTo(map);
    }
}

function destroySuggestionMaps() {
    const maps = document.querySelectorAll('[id^="suggest-map-"]');

    maps.forEach(destroyMap);

    function destroyMap(value) {
        let map = document.getElementById(value.id);
        map.off();
        map.remove();
    }
}
