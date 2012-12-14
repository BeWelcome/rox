/**
 * Represent an address point.
 *
 * @member latitude
 * @member longitude
 * @member address
 *
 */
var BWMapAddressPoint = Class.create({
  /**
   * constructor
   */
  initialize : function(latitude, longitude, address) {
    this.latitude = latitude;
    this.longitude = longitude;
    this.address = address;
  }
});