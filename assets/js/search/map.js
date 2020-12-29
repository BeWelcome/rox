function Map() {
    this.map = undefined;
    this.noRefresh = false;
    this.initializing = false;
    this.mapBox = $("#map-box");
}

Map.prototype.showMap = function () {
    if (this.map === undefined) {
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
            const bounds = this.markerClusterGroup.getBounds();

            const latitude = document.getElementById('search_map_location_latitude').value;
            const longitude = document.getElementById('search_map_location_longitude').value;

            this.map.fitBounds(bounds, {zoomSnap: 0.1, padding: [20, 20]});
            this.map.flyTo([latitude, longitude]);
        } else {
            this.map.fitWorld();
        }
    }
};

// Check if there are results to add to the map
Map.prototype.addMarkers = function (map) {
    const markers = new L.markerClusterGroup({
        iconCreateFunction: function iconCreateFunction(cluster) {
            return new L.DivIcon({
                iconSize: [40, 40],
                className: '',
                html: '<div class="cluster_count" style="text-align:center;' + 'color:#fff;' + 'background-color:#69bb11;">' + cluster.getChildCount() + '</div>'
            });
        }
    });

    $.each(mapMembers, function (index, value) {
        let iconFile = 'undefined';

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
        markers.addLayer(marker);
    });

    try {
        map.addLayer(markers);
    } catch (err) {}

    return markers;
};

$(function () {
    var map = new Map();
    map.showMap();
});
