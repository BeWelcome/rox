/**
 * Reverse geolocator using GoogleMap.
 *
 */
var BWGeonameMapReverseGeolocator = Class.create({
  /**
   * constructor
   */
  initialize : function() {
  },

  getLocations: function(searchText, successCallBackFunction, errorCallBackFunction){
    bwrox.debug('[Geoname] Search places containing text "%s".', searchText);

    jQuery.ajax({
      url: "http://ws.geonames.org/searchJSON",
      dataType: "jsonp",
      data: {
        featureClass: "P",
        style: "full",
        maxRows: 10,
        lang:'fr',
        style:'MEDIUM',
        name_startsWith: searchText
      },
      success: function( data ) {
        bwrox.debug('[Geoname] Search places containing text "%s" returned %d results.', searchText, data.totalResultsCount);
        var results = jQuery.map( data.geonames, function( item ) {
          if (item){
            var label = item.name;
            if (item.adminName1 != null && item.adminName1 != ''){
              label += ', ' + item.adminName1;
            }
            if (item.countryName != null && item.countryName != ''){
              label += ', ' + item.countryName ;
            }
            return {
              //with nominatim: label: item.display_name,
              //with nominatim: value: item.display_name,
              //with gmap: label: item.address,
              //with gmap: value: item.address,
              label: label,
              value: label,
              place: item
            };
          }else{
            bwrox.error("Error: item is null.");
          }
        });
        successCallBackFunction(results);
      }
    });
  }

});
