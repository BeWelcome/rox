/**
 * Represent a host point on the map.
 *
 * @member accomodation
 * @member summary
 * @member latitude
 * @member longitude
 * @member detail
 *
 */
var BWMapHostPoint = Class.create({
  /**
   * constructor
   */
  initialize : function(marker) {
    this.accomodation = marker.getAttribute("accomodation");
    this.summary = marker.getAttribute("summary");
    this.latitude = parseFloat(marker.getAttribute("Latitude"));
    this.longitude = parseFloat(marker.getAttribute("Longitude"));
    this.detail = marker.getAttribute("detail");
    this.username = marker.getAttribute("username");
  }
});