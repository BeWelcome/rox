/**
 * extend marker class in order to store an "index" attribute inside
 */
var IndexedMarker = L.Marker.extend({
  initialize : function(latlng, options, markerIndex) {
    this.setLatLng(latlng);
    L.Util.setOptions(this, options);
    this.index = markerIndex;
  },
});