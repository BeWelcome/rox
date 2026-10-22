import Autocomplete from '@tomickigrzegorz/autocomplete';

export function initializeSingleAutoComplete(url, cssClass = "js-location-picker", onChange = function(){}) {
    const locationSuggests = document.getElementsByClassName(cssClass);
    return new LocationSuggest(locationSuggests.item(0), url, onChange);
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
                                <div class="u:flex u:flex-row u:justify-between align-items-center">
                                    <div>
                                        <div class="suggest-name">
                                            ${parts[0]}
                                        </div>
                                        <div class="suggest-country">
                                            ${adminUnitAndCountry}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="suggest-map suggest-flag" aria-hidden="true">${countryFlag(el.countryCode)}</div>
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
                element.value = object.name.replaceAll("#", ", ");
                this.onChange(element, object);
            },
            // the method presents no results element
            noResults: ({ currentValue, template }) =>
                template(`<li>No results found: "${currentValue}"</li>`),
        });
    }
}

// The country's flag as an emoji (two regional indicator letters), or a pin when there is no country.
function countryFlag(code) {
    if (!code || !/^[A-Za-z]{2}$/.test(code)) {
        return '<i class="fas fa-map-marker-alt"></i>';
    }

    return String.fromCodePoint(...[...code.toUpperCase()].map((letter) => 0x1F1A5 + letter.charCodeAt(0)));
}
