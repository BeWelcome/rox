function Map() {
    this.map = undefined;
    this.noRefresh = false;
    this.initializing = false;
    this.mapBox = $("#map-box");
}

Map.prototype.showMap = function () {
    if (this.map === undefined) {
        this.initializing = true;
        // add the container hosting the map

        this.mapBox.toggleClass("map-box");
        this.mapBox.append('<div id="map" class="map p-2 framed w-100"></div>');
        this.map = L.map('map', {
            center: [15, 0],
            zoomSnap: 0.25,
            zoomDelta: 0.25,
            maxZoom: 18,
            minZoom: 1,
            zoom: 2
        });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap contributors</a>',
            subdomains: ['a', 'b', 'c']
        }).addTo(this.map);
        L.control.scale().addTo(this.map);
        this.noRefresh = false;
        this.markerClusterGroup = this.addMarkers(this.map);

        if (this.markerClusterGroup.getLayers().length > 0) {
            // Check if a rectangle is set if so use this for the bounds else fit the bounds to the markerClusterGroup
            var query = this.getQueryStrings($(".search_form").serialize());

            // Distinguish between /search/members and /search/map
            if (query["map[distance]"] !== undefined) {
                this.noRefresh = true;
                this.map.fitBounds(this.boundingBox(query["map[location_latitude]"], query["map[location_longitude]"], query["map[distance]"]));
            } else {
                if (query["search[distance]"] === -1) {
                    this.noRefresh = true;
                    this.map.fitBounds([[query["search[ne_latitude]"], query["search[ne_longitude]"]], [query["search[sw_latitude]"], query["search[sw_longitude]"]]]);
                } else {
                    this.noRefresh = true;
                    this.map.fitBounds(this.boundingBox(query["search[location_latitude]"], query["search[location_longitude]"], query["search[distance]"]));
                }
            }
        }
        that = this;
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

Map.prototype.hideMap = function () {
    if (this.map !== undefined) {
        // remove the container hosting the map
        this.mapBox.toggleClass("map-box").empty(); // get rid of the map

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
    var query = this.getQueryStrings($("[name=search]").serialize());
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
}; // http://stackoverflow.com/questions/2907482

Map.prototype.getQueryStrings = function (url) {
    var assoc = {};

    var decode = function decode(s) {
        return decodeURIComponent(s.replace(/\+/g, " "));
    };

    var keyValues = url.split('&');

    for (var i in keyValues) {
        var key = keyValues[i].split('=');

        if (key.length > 1) {
            assoc[decode(key[0]).toLowerCase()] = decode(key[1]);
        }
    }
    return assoc;
};

Map.prototype.createQueryString = function (queryDict) {
    var queryStringBits = [];

    for (var key in queryDict) {
        if (queryDict.hasOwnProperty(key)) {
            queryStringBits.push(key + "=" + queryDict[key]);
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

    $.each(mapMembers, function (index, value) {
        var iconFile = 'undefined';

        switch (value.Accommodation) {
            case 'anytime':
                iconFile = 'anytime';
                break;

            case 'neverask':
                iconFile = 'neverask';
                break;

            default:
                iconFile = value.Accommodation;
                break;
        }

        var icon = new L.DivIcon({
            html: '<div><img src="/images/icons/' + iconFile + '.png" class="mapicon"></div>',
            className: '',
            iconSize: new L.Point(17, 17)
        });
        const latlng = new L.LatLng(value.latitude, value.longitude);
        var marker = new L.marker([value.latitude, value.longitude], {
            icon: icon,
            className: 'marker-cluster marker-cluster-unique'
        });

        if (value.Username) {
            var popupContent = '<div class="d-flex">';
            popupContent = popupContent + '<div><img src="/members/avatar/' + value.Username + '?size=50" width="50" height="50"></div>';
            popupContent = popupContent + '<div class="hosticon nowrap"><img src="/images/icons/' + iconFile + '.png"><i class="fa fa-2x fa-bed p-1"></i><span class="h4">' + value.CanHost + '</span></div></div>';
            popupContent = popupContent + '<div class="d-flex"><h5 class="nowrap"><a href="/members/' + value.Username + '" target="_blank">' + value.Username + '</a></h5></div>';

            marker.bindPopup(popupContent).openPopup(); // groups[accommodation].addLayer(marker);
        }

        markers.addLayer(marker);
    });

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

$(function () {
    var map = new Map();
    $(".img-check").click(function(){
        $(this).toggleClass("checked").toggleClass("not_checked");
    });
    $(".show_options").click(function(){
        $("#search_options").toggleClass("d-block").toggleClass("d-none");
    });

    if ($(".show_map").is(":checked")) {
        map.showMap();
    }
    $(".show_map").click(function(){
        if ($(this).is(":checked")) {
            map.showMap();
        } else {
            map.hideMap();
        }
    });
});
