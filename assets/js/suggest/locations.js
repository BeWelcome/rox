import Autocomplete from '@tomickigrzegorz/autocomplete/sources/js/script';
const L = require('leaflet');

export function initializeSingleAutoComplete(url, cssClass = "js-location-picker", onChange = function(){}) {
    const locationSuggests = document.getElementsByClassName(cssClass);
    new LocationSuggest(locationSuggests.item(0), url, onChange);
}

export function initializeMultipleAutoCompletes(url, cssClass = "js-location-picker", onChange = function(){}) {
    Array.from(document.getElementsByClassName(cssClass)).forEach(
        (locationSuggest) => new LocationSuggest(locationSuggest, url, onChange)
    );
}

let lastGroup = '';

class LocationSuggest {
    constructor(element, url, onChange) {
        this.url = url;
        this.element = element;
        this.onChange = onChange;
        this.autoComplete = new Autocomplete(element.id, {
            // The number of characters entered should start searching
            howManyCharacters: 1,
            classGroup: 'suggest-group',
            onSearch: ({ currentValue, template }) => {
                const api = this.url + `?term=${encodeURI(
                    currentValue
                )}`;

                /**
                 * Promise
                 */
                return new Promise((resolve) => {
                    fetch(api)
                        .then((response) => response.json())
                        .then((data) => {
                            resolve(data.locations);
                        })
                        .catch((error) => {
                            console.log(error);
                            return template;
                        });
                });
            },
            onOpened: ({results}) => {
                initializeSuggestionMaps();
                return results;
            },
            onReset: () => {
                destroySuggestionMaps();
            },
            onClose: () => {
                destroySuggestionMaps();
            },
            onResults: ({ currentValue, matches, template, classGroup }) => {
                if (matches === 0) {
                    return template;
                }

                return matches.map(
                    (el, index, array) => {
                            // we create an element of the group
                        let group = "";
                        if (undefined !== array[index - 1]?.type) {
                            group =
                                el.type !== array[index - 1]?.type
                                    ? `<li class="${classGroup}">${el.type}</li>`
                                    : "";
                        }
                        const parts = el.name.split('#');
                        let adminUnitAndCountry = '';
                        if (parts.length > 1) {
                            adminUnitAndCountry = parts.slice(1).join(", ");
                        }

                        return `
                            ${group}
                            <li>
                                <div class="u-flex u-flex-row u-justify-between align-items-center">
                                    <div>
                                        <div class="suggest-name">
                                            ${parts[0]}
                                        </div>
                                        <div class="suggest-country">
                                            ${adminUnitAndCountry}
                                        </div>
                                    </div>
                                    <div>
                                        <div id="suggest-map-${el.id}" class="suggest-map"></div>
                                        <input type="hidden" id="latitude-${el.id}" value="${el.latitude}"><input type="hidden" id="longitude-${el.id}" value="${el.longitude}">
                                    </div>
                                </div>
                            </li>
                          `;
                    }
                ).join("");
            },
            // add text to the input field as you move through
            // the results with the up/down cursors
            insertToInput: true,
            onSubmit: ({element, object}) => {
                destroySuggestionMaps();
                element.value = object.name.replaceAll("#", ", ");
                this.onChange(element, object);
            },
            // the method presents no results element
            noResults: ({ currentValue, template }) =>
                template(`<li>No results found: "${currentValue}"</li>`),
        });
    }
}

function initializeSuggestionMaps() {
        const maps= document.querySelectorAll('[id^="suggest-map-"]');

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
            map.remove();
        }
    }
