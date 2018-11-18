$(function () {

    if ($('#map').length) {
        var map = L.map('map', {
            center: [15, 0],
            minZoom: 2,
            zoom: 2
        });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="/copyright">OpenStreetMap contributors</a>',
            subdomains: ['a', 'b', 'c']
        }).addTo(map);

        var noRefresh = false;
        var markerClusterGroup = addMarkers(map);

        if (markerClusterGroup.getLayers().length > 0) {
            // Check if a rectangle is set if so use this for the bounds else fit the bounds to the markerClusterGroup
            var query= getQueryStrings($("[name=search_form]").serialize());
            if (query["search_form[distance]"] == -1) {
                noRefresh = true;
                map.fitBounds([
                    [query["search_form[ne_latitude]"], query["search_form[ne_longitude]"]],
                    [query["search_form[sw_latitude]"], query["search_form[sw_longitude]"]]
                ]);
            } else {
                noRefresh = true;
                map.fitBounds(markerClusterGroup.getBounds());
            }

        }

        map.on("dragend", function () {
            refreshMap();
        });

        // Avoid refreshing on zoomend if the map has just been fit to bounds (infinite loop)
        map.on("zoomend", function () {
            if (!noRefresh) {
                refreshMap();
            }
            noRefresh = false;
        });

    }
});

function refreshMap() {
    var lat = map.getCenter().lat;
    var lng = map.getCenter().lng; // get current form values

    var bounds = map.getBounds();
    var ne = bounds.getNorthEast();
    var sw = bounds.getSouthWest();
    var query= getQueryStrings($("[name=search_form]").serialize());

    query["search_form[location_latitude]"] = lat;
    query["search_form[location_longitude]"] = lng;
    query["search_form[distance]"] = -1;
    query["search_form[ne_latitude]"] = ne.lat;
    query["search_form[ne_longitude]"] = ne.lng;
    query["search_form[sw_latitude]"] = sw.lat;
    query["search_form[sw_longitude]"] = sw.lng;

    window.location.href =
        window.location.protocol + "//" +
        window.location.host +
        window.location.pathname +
        createQueryString(query);
}

// http://stackoverflow.com/questions/2907482
// Gets Querystring from window.location and converts all keys to lowercase
function getQueryStrings(url) {
    var assoc = {};
    var decode = function (s) { return decodeURIComponent(s.replace(/\+/g, " ")); };
    var keyValues = url.split('&');

    for (var i in keyValues) {
        var key = keyValues[i].split('=');
        if (key.length > 1) {
            assoc[decode(key[0]).toLowerCase()] = decode(key[1]);
        }
    }

    return assoc;
}

function createQueryString(queryDict) {
    var queryStringBits = [];
    for (var key in queryDict) {
        if (queryDict.hasOwnProperty(key)) {
            queryStringBits.push(key + "=" + queryDict[key]);
        }
    }
    return queryStringBits.length > 0
        ? "?" + queryStringBits.join("&")
        : "";
}

function replaceElementInUrl(url, needle, value)
{
    let e = "search_form%5B" + needle + "%5D=";
    let regex = new RegExp( e + "(.*?)&");
    return url.replace(regex, e + value + "&");
}

// Check if there are results to add to the map

function addMarkers(map) {
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
        // TODO the icons might be easier to see on the map if they had a drop shadow.
        // Add a class to the img tag and css eg. box-shadow: 10px 10px 5px #888888;
        var iconFile;

        switch (value.Accommodation) {
            case 'anytime':
                iconFile = 'anytime';
                break;

            case 'dependonrequest':
                iconFile = 'dependonrequest';
                break;

            case 'dontask':
                iconFile = 'neverask';
                break;
        }

        var icon = new L.DivIcon({
            html: '<div><img src="/images/icons/' + iconFile + '.png" class="mapicon"></div>',
            className: '',
            iconSize: new L.Point(17, 17)
        });
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
}
