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

/**
 *
 * @constructor
 */
function BWTripsLocations() {

    this.addMarker = function(label, geonameid, latitude, longitude) {
        alert(label + " " + geonameid + " " + latitude + " " + longitude);
        var marker = L.marker([latitude, longitude]).addTo(map);
    }
}


jQuery(function() {
    enableDatePicker();

    var bwTripsLocations = new BWTripsLocations();

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