/**
 * Reverse geolocator using GoogleMap.
 *
 */
var BWGoogleMapReverseGeolocator = Class.create({
  /**
   * constructor
   */
  initialize : function() {
    this.geocoder = new google.maps.Geocoder();
  },

  buildAddressPoint: function (place){

    bwrox.info("Building address point '%s'", place.formatted_address);

    var addressPoint = new BWMapAddressPoint(place.geometry.location.lat(),
          place.geometry.location.lng(), place.formatted_address);

    if (typeof (place) == 'object'
           && place.address_components
          // && place.AddressDetails.Accuracy
          //&& place.AddressDetails.Country
          //&& place.AddressDetails.Country.CountryNameCode
          //&& place.Point && place.Point.coordinates
          ) {
      // addressPoint.accuracy = place.AddressDetails.Accuracy;
      // addressPoint.coordinates = place.Point.coordinates;
      var nbAddressComponents = place.address_components.length;
      if (nbAddressComponents != 0){
    	  
    	  addressPoint.countryNameCode = '';
    	  
    	  for (var i = 0; i < nbAddressComponents; i++){
    		  if (jQuery.inArray('country', place.address_components[i].types) != -1){
    			  addressPoint.countryNameCode = place.address_components[i].short_name;
    			  break;
    		  }
    	  }
    	  
      }else{
        addressPoint.countryNameCode = '';
      }
      addressPoint.location = place.formatted_address;
      bwrox.debug("Location: " + addressPoint.location);
    } else {
      // addressPoint.accuracy = '';
      // addressPoint.coordinates = '';
      addressPoint.countryNameCode = '';
    }

    if (place.geometry.location){
      addressPoint.coordinates = new Array();
      addressPoint.coordinates[0] = place.geometry.location.lng();
      addressPoint.coordinates[1] = place.geometry.location.lat();
      addressPoint.coordinates[2] = 0;
    }

        // calculate zoom level
        if (place.geometry.viewport){
                var ne = place.geometry.viewport.getNorthEast();
                var sw = place.geometry.viewport.getSouthWest();
                var distance = calculateDistance(ne.lat(), sw.lat(), ne.lng(), sw.lng());
                addressPoint.zoomLevel = calculateZoomLevel(distance);
        }else{
                addressPoint.zoomLevel = 3;
        }

    if (addressPoint.zoomLevel < 3){
      addressPoint.accuracy = 0;
    }else if (addressPoint.zoomLevel < 7){
      addressPoint.accuracy = 1;
    }else if (addressPoint.zoomLevel < 9){
      addressPoint.accuracy = 2;
    }else if (addressPoint.zoomLevel < 10){
      addressPoint.accuracy = 3;
    }else{
      addressPoint.accuracy = 4;
    }
//    switch (parseInt(addressPoint.accuracy)) {
//      case 0:
//        addressPoint.zoomLevel = 3;
//        break;
//      case 1:
//        switch (addressPoint.countryNameCode) {
//        case 'RU':
//        case 'US':
//        case 'CA':
//        case 'CN':
//        case 'BR':
//        case 'AU':
//          addressPoint.zoomLevel = 3;
//          break;
//        default:
//          addressPoint.zoomLevel = 5;
//        }
//        break;
//      case 2:
//        addressPoint.zoomLevel = 7;
//        break;
//      case 3:
//        addressPoint.zoomLevel = 9;
//        break;
//      case 4:
//        addressPoint.zoomLevel = 10;
//        break;
//      default:
//        addressPoint.zoomLevel = 11;
//        break;
//    }

    bwrox.debug("Zoom level is %s (accuraci = %s)", addressPoint.zoomLevel, addressPoint.accuracy);

    return addressPoint;
  },
  getLocationsForAutocompletion: function(searchText, successCallBackFunction){
    bwrox.debug('Start autocomplete');
    this.getLocations(searchText, function(results){
      results = jQuery.map(results, function( item ) {
        if (item){
          return {
            label: item.formatted_address,
            value: item.formatted_address,
            place: item
          };
        }else{
          bwrox.error("Error: item is null.");
        }
      });
      successCallBackFunction(results);
    } );
  },
  /**
   * load the icons
   */
  getLocation : function(address, successCallBackFunction, errorCallBackFunction) {
    bwrox.debug("Try to reverse geolocate address '%s'.", address);
    var thisObject = this;
    this.getLocations(address, function(results) {
        if (results && results.length > 0){
          if (results.length > 1){
            // the first result is used
          bwrox.warn("Reverse geolocation of address '%s' returned %d results: use first one '%s'.", address, results.length, results[0].formatted_address);
        }
        var place = results[0];
        var addressPoint = thisObject.buildAddressPoint(place);

        successCallBackFunction(addressPoint);
        }else{
          // not fount
          bwrox.warn("Address not fount...");
        errorCallBackFunction();
        }
      }, function(){
        // not fount
        bwrox.warn("Error while searching place...");
      errorCallBackFunction();
      });
  },
  getLocations: function(searchText, successCallBackFunction, errorCallBackFunction){
    bwrox.debug('Search places containing text "%s".', searchText);

    this.geocoder.geocode( { 'address': searchText}, function(results, status) {
          if (status == google.maps.GeocoderStatus.OK) {
        bwrox.debug('Search places containing text "%s" returned %d results.', searchText, results.length);
            successCallBackFunction(results);

          } else {
            bwrox.error("Geocode was not successful for the following reason: " + status);
            errorCallBackFunction();
          }
    });
  }

});
