/**
* Configure search auto complete.
*/
BWRox.prototype.initSearchAutocomplete = function(inputFieldSelector){
  jQuery(inputFieldSelector).autocomplete({
    source: function( request, response ) {
      reverseGeolocator.getLocationsForAutocompletion(request.term, function(results){
        response(results);
      });
    },
    minLength: 2,
    select: function( event, ui ) {
      bwrox.log( ui.item ?
        "Autocomplete result selected: " + ui.item.label + ' (' + ui.item.place.lat + ',' + ui.item.place.lon + ')' :
        "Nothing selected, input was " + this.value);
      if (ui.item){
        // re-use the geolocation results of geonames geolocation to launch the OSM geolocation
        searchByAddressPoint(ui.item.place, 0);
      }
    },
    open: function() {
      // hide tooltip
      jQuery(".tooltip").hide();
      jQuery( this ).removeClass( "ui-corner-all" ).addClass( "ui-corner-top" );
    },
    close: function() {
      jQuery( this ).removeClass( "ui-corner-top" ).addClass( "ui-corner-all" );
    }
  });
};
/// TODO: the following code is a try to implement autocomplete without JQuery-UI (to be continued)
///**
// *  Delay an action.
// *
// */
//var delay = (function(){
//    var timer = 0;
//    return function(callback, ms){
//      clearTimeout (timer);
//      timer = setTimeout(callback, ms);
//    };
//  })();
//
//
///**
// *  Configure search auto complete.
// *
// */
//BWRox.prototype.initSearchAutocomplete = function(inputFieldSelector){
//  var inputField = jQuery(inputFieldSelector);
//
//  // on text change
//  inputField.keyup(function(event) {
//
//    // wait 200ms without new change
//    delay(function(){
//       var searchText = event.target.value;
//        if (searchText.length > 3){
//          // text length > 3
//          reverseGeolocator.getLocations(searchText, function(results){
//            bwrox.debug('=> Search places containing text "%s" returned %d results.', searchText, results.length);
//          });
//        }
//      }, 200 );
//  });
//};