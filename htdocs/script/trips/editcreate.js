var bwTripsLocations;

function enableDatePicker() {
    jQuery( ".date-picker-start" ).datepicker({
        changeMonth: true,
        numberOfMonths: 1,
        onClose: function( selectedDate ) {
            var id = this.id.replace('startdate', 'enddate');
            jQuery( '#' + id ).datepicker( "option", "minDate", selectedDate );
        }
    });
    jQuery( ".date-picker-end" ).datepicker({
        changeMonth: true,
        numberOfMonths: 1,
        onClose: function( selectedDate ) {
            var id = this.id.replace('enddate', 'startdate');
            jQuery( '#'+ id ).datepicker( "option", "maxDate", selectedDate );
        }
    });
}

var arrowPolyline = L.Polyline.extend({
    addArrows: function(map){
        var points = this.getLatLngs()
        for (var p = 0; p +1 < points.length; p++){
            var diffLat = points[p+1]["lat"] - points[p]["lat"]
            var diffLng = points[p+1]["lng"] - points[p]["lng"]
            var center = [points[p]["lat"] + diffLat/2,points[p]["lng"] + diffLng/2]
            var angle = 360 - (Math.atan2(diffLat, diffLng)*57.295779513082)
            var arrowM = new L.marker(center,{
                icon: new L.divIcon({
                    className : "arrowIcon",
                    iconSize: new L.Point(30,30),
                    iconAnchor: new L.Point(15,15),
                    html : "<div style = 'font-size: 20px; -webkit-transform: rotate("+ angle +"deg)'>&#10152;</div>"
                })
            }).addTo(map);
        }
    }
})
/**
 *
 * @constructor
 */
function BWTripsLocations(htmlMapId) // Constructor
{
    this.addMarkerCallback = function(label, geonameid, latitude, longitude) {
        BWTripsLocations.addMarker(label, geonameid, latitude, longitude);
    };

    var instance = this;
    instance.map = initMap(htmlMapId).fitWorld();
    instance.latLngs = [];
    instance.polyline = null;
    instance.addMarker = function(id, label, geonameid, latitude, longitude) {
        // get current location number
        var parts = id.split("-");
        var current = parts[1] - 1;
        jQuery('#remove-' + parts[1]).attr('disabled', '');
        jQuery('#remove-' + parts[1]).click( instance.removeRow );
        if (current == instance.latLngs.length) {
            instance.latLngs.push(new L.LatLng(latitude, longitude));
        } else {
            instance.latLngs[current] = new L.LatLng(latitude, longitude);
        }
        if (instance.polyline != null) {
            instance.map.removeLayer(instance.polyline);
        }
        instance.polyline = new arrowPolyline(instance.latLngs, {color: 'red'}).addTo(instance.map);
        instance.polyline.addArrows(instance.map)
        instance.map.fitBounds(instance.polyline.getBounds());
    }

    instance.removeMarker = function(id) {

    }

    instance.removeRow = function( e ) {
        var parts = this.id.split("-");
        var current = parts[1] - 1;

        return false;
    }
}

jQuery(function() {
    enableDatePicker();

    bwTripsLocations = new BWTripsLocations('trips-map');
//    bwTripsLocations.initMap('trips-map');

    addMarker = bwTripsLocations.addMarker;

    jQuery( "#trip-add-location").click(function( e ) {
        // e.PreventDefault();
        var next = jQuery('div[name^=div-location]').length + 1;
        jQuery('#location-loading').show();
        var url = '/trips/addlocation/' + next;
        var newLocation = jQuery('<div id="div-location-' + next + '" name="div-location-' + next + '" class="row">').load(url,
            function() {
                jQuery('#empty-location').before( newLocation );
                setTimeout(enableAutoComplete(addMarker), 500);
                setTimeout(enableDatePicker(), 500);
                jQuery('#location-loading').hide();
            });
        return false;
    });

    //jQuery('.tripseditcreate').bootstrapValidator({
    //    message: 'This value is not valid',
    //    feedbackIcons: {
    //        valid: 'glyphicon glyphicon-ok',
    //        invalid: 'glyphicon glyphicon-remove',
    //        validating: 'glyphicon glyphicon-refresh'
    //    },
    //    fields: {
    //        tripname: {
    //            validators: {
    //                notEmpty: { }
    //            }
    //        },
    //        tripdescription: {
    //            validators: {
    //                notEmpty: { }
    //            }
    //        }
    //    }
    //});
});