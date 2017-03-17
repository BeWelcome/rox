function addMarkers(map) {
    var latLngs = [];
    var markers = new L.markerClusterGroup();

    var icon = new L.DivIcon({
        html: '<div><span>1</span></div>',
        iconSize: new L.Point(40, 40)
    });


    $("#progress").progressbar({value: false});

    // calculate 'all' URL
    req = location.pathname.toLowerCase();
    pos = req.indexOf('/page');
    if (pos >= 0) {
        // remove page part
        req = req.substr(0, pos);
    }
    req = req + '/all';

    // get markers using AJAX so that the page loads faster
    $.ajax({
        url: req,
        dataType: "json",
        complete: function () {
            $("#progress").hide();
        },
        success: function (data) {
            data.trips.forEach( function(trip){
                trip.subtrips.forEach( function(subtrip)
                {
                    var marker = new L.Marker([
                        subtrip.latitude,
                        subtrip.longitude
                    ], {icon: icon});
                    var popupContent = '<h4><a href="trips/' + trip.id + '">' + trip.title + '</a></h4>';
                    popupContent += '<p>' + trip.username + '<br>';
                    popupContent += subtrip.arrival;
                    if (subtrip.arrival != subtrip.departure) {
                        popupContent += ' - ' + subtrip.departure;
                    }
                    popupContent += '</p>';
                    marker.bindPopup(popupContent).openPopup();

                    markers.addLayer(marker);
                    latLngs[latLngs.length] = [subtrip.latitude, subtrip.longitude];
                });
            });
        }
    });

    map.addLayer(markers);

    bwrox.debug('Added markers to trips map.');

    return latLngs;
}

var map = initMap('trips-map');
var latLngs = addMarkers(map);
if (latLngs.length == 0) {
    map.fitWorld();
} else {
    map.fitBounds(latLngs);
}
