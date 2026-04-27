import {initializeSingleAutoComplete} from '../suggest/locations';
import {initializeTomSelects} from '../tom-select';

import L from 'leaflet';
import 'leaflet.fullscreen';
import 'leaflet.fullscreen/Control.FullScreen.css';
import 'leaflet.markercluster';
import 'leaflet.markercluster/dist/MarkerCluster.Default.css';
import 'leaflet.markercluster/dist/MarkerCluster.css';
import 'leaflet/dist/leaflet.css';

function onChange(element, result) {
    const locationFullName = document.getElementById('search_location_fullname');
    const locationName = document.getElementById('search_location_name');
    const locationGeonameId = document.getElementById('search_location_geoname_id');
    const locationLatitude = document.getElementById('search_location_latitude');
    const locationLongitude = document.getElementById('search_location_longitude');
    const locationIsAdminUnit = document.getElementById('search_location_admin_unit');
    locationFullName.value = result.name.replaceAll("#", ", ");
    locationName.value = result.name.split("#")[0];
    locationIsAdminUnit.value = result.isAdminUnit;
    locationGeonameId.value = result.id;
    locationLatitude.value = result.latitude;
    locationLongitude.value = result.longitude;
}

initializeSingleAutoComplete("/suggest/locations/all", 'js-location-picker', onChange);

function Map() {
    this.map = undefined;
    this.noRefresh = false;
    this.initializing = false;
    this.mapBox = document.getElementById("map-box");
}

Map.prototype.showMap = function () {
    if (this.map === undefined) {
        this.initializing = true;

        // add the container hosting the map
        this.mapBox.classList.toggle("map-box");
        
        const mapDiv = document.createElement('div');
        mapDiv.id = 'map';
        mapDiv.className = 'map p-2 framed w-100';
        this.mapBox.appendChild(mapDiv);
        
        this.map = L.map('map', {
            center: [15, 0],
            zoomSnap: 0.25,
            zoomDelta: 0.25,
            maxZoom: 18,
            minZoom: 1,
            zoom: 2,
            fullscreenControl: true,
            fullscreenControlOptions: {
                position: 'topleft'
            }
        });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap contributors</a>',
            subdomains: ['a', 'b', 'c']
        }).addTo(this.map);
        L.control.scale().addTo(this.map);
        this.noRefresh = false;
        this.markerClusterGroup = this.addMarkers(this.map);

        this.noRefresh = true;
        if (this.markerClusterGroup.getLayers().length > 0) {
            // Check if a rectangle is set if so use this for the bounds else fit the bounds to the markerClusterGroup
            var query = this.getQueryStrings(new FormData(document.querySelector(".search_form")));

            // Distinguish between /search/members and /search/map
            if (query["search[distance]"] === -1) {
                this.map.fitBounds([[query["search[ne_latitude]"], query["search[ne_longitude]"]], [query["search[sw_latitude]"], query["search[sw_longitude]"]]]);
            } else {
                const isAdminUnit = document.getElementById('search_location_admin_unit').value;
console.log(isAdminUnit);
                if ("1" === isAdminUnit) {
                    const bounds = this.markerClusterGroup.getBounds();
                    this.map.fitBounds(bounds, {zoomSnap: 0.25});
                } else {
                    this.centerMap();
                }
            }
        } else {
            this.centerMap();
        }
        let that = this;
        this.map.on("dragend", function () {
            if (!that.noRefresh && !that.initializing) {
                that.refreshMap();
                that.noRefresh = false;
            }
        }); // Avoid refreshing on dragend if the map has just been fit to bounds (infinite loop)

        this.map.on("zoomend", function () {
            if (!that.noRefresh && !that.initializing) {
                that.refreshMap();
                that.noRefresh = false;
            }
        }); // Avoid refreshing on zoomend if the map has just been fit to bounds (infinite loop)
        this.initializing = false;
    }
};

Map.prototype.centerMap = function () {
    // get bounding box from the hidden fields
    const latitude = document.getElementById('search_location_latitude').value;
    const longitude = document.getElementById('search_location_longitude').value;
    if ("" === latitude) {
        return;
    }

    const min_latitude = document.getElementById('min_latitude').value;
    const max_latitude = document.getElementById('max_latitude').value;
    const min_longitude = document.getElementById('min_longitude').value;
    const max_longitude = document.getElementById('max_longitude').value;
    const sw = L.latLng(min_latitude, min_longitude);
    const ne = L.latLng(max_latitude, max_longitude);
    const bounds = new L.latLngBounds(sw, ne);

    let mapMarkerIcon = L.icon({
        iconUrl: '/images/icons/marker_drop.png',
        iconSize: [29, 24],
        iconAnchor: [9, 21],
        popupAnchor: [0, -14]
    });

    L.marker([latitude, longitude], {
        icon: mapMarkerIcon,
        draggable: false
    }).addTo(this.map);

    this.map.fitBounds(bounds, {zoomSnap: 0.25});
    console.log(bounds);
}

Map.prototype.hideMap = function () {
    if (this.map !== undefined) {
        // remove the container hosting the map
        this.mapBox.classList.toggle("map-box");
        this.mapBox.innerHTML = ''; // get rid of the map

        this.map.remove();
        this.map = undefined;
    }
};

Map.prototype.refreshMap = function () {
    var lat = this.map.getCenter().lat;
    var lng = this.map.getCenter().lng; // get current form values

    var bounds = this.map.getBounds();
    var ne = bounds.getNorthEast();
    var sw = bounds.getSouthWest();
    
    var searchForm = document.querySelector("[name='search']");
    if (!searchForm) {
        // If the form isn't found by name, it might be the top-level form. 
        // We'll construct the query parameters manually.
        var query = {};
    } else {
        var formData = new FormData(searchForm);
        var query = this.getQueryStrings(formData);
    }
    
    query["search[location_latitude]"] = lat;
    query["search[location_longitude]"] = lng;
    query["search[distance]"] = -1;
    query["search[ne_latitude]"] = ne.lat;
    query["search[ne_longitude]"] = ne.lng;
    query["search[sw_latitude]"] = sw.lat;
    query["search[sw_longitude]"] = sw.lng;
    query["search[showOnMap]"] = 1;
    query["search[updateMap]"] = 1;

    window.location.href =
        window.location.protocol + "//" +
        window.location.host +
        window.location.pathname + this.createQueryString(query)
    ;
}; 

Map.prototype.getQueryStrings = function (formData) {
    var assoc = {};
    for (const [key, value] of formData.entries()) {
        assoc[key] = value;
    }
    return assoc;
};

Map.prototype.createQueryString = function (queryDict) {
    var queryStringBits = [];

    for (var key in queryDict) {
        if (queryDict.hasOwnProperty(key)) {
            queryStringBits.push(encodeURIComponent(key) + "=" + encodeURIComponent(queryDict[key]));
        }
    }

    return queryStringBits.length > 0 ? "?" + queryStringBits.join("&") : "";
};

// Check if there are results to add to the map

Map.prototype.addMarkers = function (map) {
    /* var groups = {
        anytime: makeGroup('anytime', '#69bb11'),
        dependonrequest: makeGroup('dependonrequest', '#0099ea'),
        dontask: makeGroup('dontask', '#666')
    }; */
    var markers = new L.markerClusterGroup({
        iconCreateFunction: function iconCreateFunction(cluster) {
            return new L.DivIcon({
                iconSize: [40, 40],
                className: '',
                html: '<div class="cluster_count" style="text-align:center;' + 'color:#fff;' + 'background-color:#69bb11;">' + cluster.getChildCount() + '</div>'
            });
        }
    });
    /**
     * @param value.Accommodation Accommodation status enum
     * @param value.Username
     * @param value.latitude
     * @param value.longitude
     */

    if (typeof mapMembers !== 'undefined' && mapMembers !== null) {
        mapMembers.forEach(function(value) {
            const icon = new L.DivIcon({
                html: '<div><img src="/images/icons/' + value.Accommodation + '.png" class="mapicon"></div>',
                className: '',
                iconSize: new L.Point(17, 17)
            });

            const marker = new L.marker([value.latitude, value.longitude], {
                icon: icon,
                className: 'marker-cluster marker-cluster-unique'
            });

            if (value.Username) {
                let popupContent = '<div class="d-flex flex-column">';
                popupContent += '<div class="d-flex flex-row">'
                popupContent += '<div><img class="profileimg avatar-48" src="/members/avatar/' + value.Username + '/48" width="48" height="48"></div>';
                popupContent += '<div class="d-flex flex-column justify-content-between">';
                popupContent += '<div><img src="/images/icons/' + value.Accommodation + '.png" width="22"></div>';
                popupContent += '<div class="text-nowrap" style="font-size: 16px">';
                popupContent += '<i class="fa fa-bed fa-lg p-1"></i>' + value.MaxGuests + '';
                popupContent += '</div>';
                popupContent += '</div>';
                popupContent += '</div>';
                popupContent += '<div class="d-flex"><strong><a href="/members/' + value.Username + '" target="_blank" class="mt-1">' + value.Username + '</a></strong></div>';
                popupContent += '</div>';
                marker.bindPopup(popupContent, {
                    'closeButton': false,
                    'maxWidth': 200,
                    'minWidth': 90,
                }); // groups[accommodation].addLayer(marker);
            }

            markers.addLayer(marker);
        });
    }

    try {
        map.addLayer(markers);
    } catch (err) {}

    return markers;
};

Map.prototype.boundingBox = function(latitude, longitude, distance) {
    const approx = distance * 1.569612305760477e-2;
    var ne = L.latLng(parseFloat(latitude) - approx / 2, parseFloat(longitude) - approx);
    var sw = L.latLng(parseFloat(latitude) + approx / 2, parseFloat(longitude) + approx);
    return L.latLngBounds( ne, sw);
};

document.addEventListener('DOMContentLoaded', function () {
    var map = new Map();
    
    const showOptionsElements = document.querySelectorAll('.show_options');
    showOptionsElements.forEach(function(element) {
        element.addEventListener('click', function() {
            const searchOptions = document.getElementById("search_options");
            if (searchOptions) {
                searchOptions.classList.toggle("d-block");
                searchOptions.classList.toggle("d-none");
            }
            const searches = document.querySelectorAll(".search");
            searches.forEach(function(search) {
                search.classList.toggle("d-block");
                search.classList.toggle("d-none");
            });
        });
    });

    const showMapCheckbox = document.querySelector(".show_map");
    if (showMapCheckbox) {
        if (showMapCheckbox.checked) {
            map.showMap();
        }
        showMapCheckbox.addEventListener('click', function() {
            if (this.checked) {
                map.showMap();
            } else {
                map.hideMap();
            }
        });
    }
});

initializeTomSelects();