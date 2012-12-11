/**
 * Reverse geolocator using OpenStreetMap.
 *
 */
var BWNominatimMapReverseGeolocator = Class.create({
  /**
   * constructor
   */
  initialize : function() {
    this.hostUrl = 'http://open.mapquestapi.com';
    this.queryBaseUrl = this.hostUrl + '/nominatim/v1/search';
    // this.hostUrl = 'http://nominatim.openstreetmap.org';
    // this.queryBaseUrl = this.hostUrl + '/search';
  },
  buildAddressPoint: function (place){
    var addressPoint = new BWMapAddressPoint(place.lat,
        place.lon, place.display_name);

    bwrox.info("[OSM] Building address point '%s': %s, %s", addressPoint.address,
          addressPoint.latitude, addressPoint.longitude);

    addressPoint.countryNameCode = "";
    addressPoint.boundingBox = place.boundingbox;
    addressPoint.coordinates = new Array(addressPoint.longitude, addressPoint.latitude, 0);
    addressPoint.location = place.display_name;
    bwrox.debug("[OSM] Location: " + addressPoint.location);

    // calculate zoom level
    addressPoint.distance = calculateDistance(addressPoint.boundingBox[0], addressPoint.boundingBox[1]
    , addressPoint.boundingBox[2], addressPoint.boundingBox[3]);

    addressPoint.zoomLevel = calculateZoomLevel(addressPoint.distance);

    // FIXME: update the php service in order to use the bounds instead!
    addressPoint.accuracy = 1;

    bwrox.debug("[OSM] Zoom level is %s (distance=%d)", addressPoint.zoomLevel, addressPoint.distance);

    return addressPoint;
  },
  /**
   * load the icons
   */
  getLocation : function(address, successCallBackFunction, errorCallBackFunction) {
    bwrox.debug("[OSM] Try to reverse geolocate address '%s'.", address);
    var thisObject = this;
    this.getLocations(address, function(results) {
        if (results && results.length > 0){
          if (results.length > 1){
            // the first result is used
          bwrox.warn("[OSM] Reverse geolocation of address '%s' returned %d results: use first one '%s'.", address, results.length, results[0].display_name);
        }
        var place = results[0];
        var addressPoint = thisObject.buildAddressPoint(place);

        successCallBackFunction(addressPoint);
        }else{
          // not fount
          bwrox.warn("[OSM] Address not fount...");
        errorCallBackFunction();
        }
      });
  },
  getLocationsForAutocompletion: function(searchText, successCallBackFunction){
    this.getLocations(searchText, function(results){
      results = jQuery.map(results, function( item ) {
        if (item){
          return {
            label: item.display_name,
            value: item.display_name,
            place: item
          };
        }else{
          bwrox.error("Error: item is null.");
        }
      });
      successCallBackFunction(results);
    } );
  },
  getLocations: function(searchText, successCallBackFunction){
    bwrox.debug('[OSM] Search places containing text "%s".', searchText);
    jQuery.ajax({
      url: this.queryBaseUrl,
      dataType: 'json',
      data: {
        format: 'json',
        q: searchText,
        osm_type: 'N',
        // 10 is the max value
        limit:'10',
      },
      success: function( data ) {
        bwrox.debug('[OSM] Search places containing text "%s" returned %d results.', searchText, data.length);
        // data = jQuery(data).filter(function(index) {
        //    return this.type == 'administrative';
        // });
        // TODO: mapquest limit to 10 results, and no way to filter by type: we have to host our own nominativ server
        // bwrox.debug('[OSM] Search places containing text "%s" returned %d results (after administrative filter).', searchText, data.length);
        successCallBackFunction(data);
      }
    });
  }
});

