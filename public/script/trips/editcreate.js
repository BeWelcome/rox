var bwTripsLocations;

/**
 * Checks if all rows are filled now. If so adds a new row
 * @returns {boolean}
 */
function checkLocationRows() {
    complete = true;
    var locations = $('div[id^=div-location]');
    locations.find('.validate').each( function() {
        var value = $(this).val();
        complete &= (value != '');
    });
    if (complete) {
        var next = locations.length + 1;
        $('#location-loading').show();
        var url = '/trips/addlocation/' + next;
        var newLocation = $('<div id="div-location-' + next + '">').load(url,
            function () {
                $('#empty-location').before(newLocation);
                setTimeout(enableSelect2, 100);
                setTimeout(enableAutoComplete(addMarker), 100);
                setTimeout(enableDatePicker(), 100);
                $('#location-loading').hide();
            });
    }
    return false;
}

function enableDatePicker() {
    $( ".date-picker-start" ).datepicker({
        changeMonth: true,
        numberOfMonths: 1,
        dateFormat: "yy-mm-dd",
        onClose: function( selectedDate ) {
            var id = this.id.replace('arrival', 'departure');
            var that = $( '#' + id );
            that.datepicker( "option", "minDate", selectedDate );
            var date = that.val();
            if (typeof date === 'undefined' || date == '') {
                that.val(selectedDate);
            }
            checkLocationRows();
        }

    });
    $( ".date-picker-end" ).datepicker({
        changeMonth: true,
        numberOfMonths: 1,
        dateFormat: "yy-mm-dd",
        onClose: function( selectedDate ) {
            var id = this.id.replace('departure', 'arrival');
            $( '#'+ id ).datepicker( "option", "maxDate", selectedDate );
            checkLocationRows();
        }
    });
}

/**
 * enableSelect2
 */
function enableSelect2() {
    $(".select2").select2({
        dropdownAutoWidth: true,
        width: '100%',
        minimumResultsForSearch: Infinity,
        theme: "bootstrap"
    });

    $( ".select2-allow-clear" ).select2( {
        allowClear: true,
        dropdownAutoWidth: true,
        width: '100%',
        minimumResultsForSearch: Infinity,
        theme: "bootstrap"
    } );
    // copy Bootstrap validation states to Select2 dropdown
    //
    // add .has-waring, .has-error, .has-succes to the Select2 dropdown
    // (was #select2-drop in Select2 v3.x, in Select2 v4 can be selected via
    // body > .select2-container) if _any_ of the opened Select2's parents
    // has one of these forementioned classes (YUCK! ;-))
    $("select[class^=select2]").on( "select2:open", function() {
        if ( $( this ).parents( "[class*='has-']" ).length ) {
            var classNames = $( this ).parents( "[class*='has-']" )[ 0 ].className.split( /\s+/ );

            for ( var i = 0; i < classNames.length; ++i ) {
                if ( classNames[ i ].match( "has-" ) ) {
                    $( "body > .select2-container" ).addClass( classNames[ i ] );
                }
            }
        }
    });

}

/**
 *
 * @constructor
 */
function BWTripsLocations(htmlMapId) // Constructor
{
    this.addMarkerCallback = function(label, geonameId, latitude, longitude) {
        BWTripsLocations.addMarker(label, geonameId, latitude, longitude);
    };

    var instance = this;
    instance.map = initMap(htmlMapId).fitWorld();
    instance.latLngs = [];
    instance.polyline = null;
    instance.addMarker = function(id, label, geonameId, latitude, longitude) {
        // get current location number
        var parts = id.split("-");
        var current = parts[1] - 1;
        var that = $('#remove-' + parts[1]);
        that.removeAttr('disabled');
        that.click( instance.removeRow );
        if (current == instance.latLngs.length) {
            instance.latLngs.push(new L.LatLng(latitude, longitude));
        } else {
            instance.latLngs[current] = new L.LatLng(latitude, longitude);
        }
        if (instance.polyline != null) {
            instance.map.removeLayer(instance.polyline);
        }
        instance.polyline = new L.polyline(instance.latLngs, {color: 'red'}).addTo(instance.map);
        instance.map.fitBounds(instance.polyline.getBounds());
        checkLocationRows();
    };

    instance.removeMarker = function(id) {

    };

    instance.removeRow = function( e ) {
        var parts = this.id.split("-");
        var current = parts[1] - 1;

        return false;
    }
}

$( document ).ready(function() {
    enableDatePicker();
    enableSelect2();

    bwTripsLocations = new BWTripsLocations('trips-map');

    addMarker = bwTripsLocations.addMarker;
    // get currently available lat/long pairs.
    var latLon = [];
    $('.collect').each(function() {
        id = $(this).attr('id');
        var parts = id.split("-");
        var current = parts[1] - 1;
        if (latLon.length == current) {
            latLon[current] = {label: null, geonameId: NaN, lat: NaN, lon: NaN};
        }
        ll = latLon[current];
        if (parts.length == 2) {
            ll.label = $(this).val();
        } else {
            switch(parts[2]) {
                case 'geoname':
                    ll.geonameId = $(this).val();
                    break;
                case 'latitude':
                    ll.lat = $(this).val();
                    break;
                case 'longitude':
                    ll.lon = $(this).val();
                    break;
            }
        }
        latLon[current] = ll;
    });

    for(i= 0; i < latLon.length; i++) {
        label = latLon[i].label;
        geonameId = latLon[i].geonameId;
        lat = latLon[i].lat;
        lon = latLon[i].lon;
        if (label != "") {
            bwTripsLocations.addMarker('location-' + (i + 1), label, geonameId, lat, lon);
        }
    }

});