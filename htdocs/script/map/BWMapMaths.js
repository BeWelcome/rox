/** Converts numeric degrees to radians */
if (typeof(Number.prototype.toRad) === "undefined") {
  Number.prototype.toRad = function() {
    return this * Math.PI / 180;
  };
}

/**
 *  Calculate the distance (in km) between 2 points.
 *
 */
function calculateDistance(lat1, lat2, lon1, lon2){
  var R = 6371; // km
  var dLat = (lat2-lat1).toRad();
  var dLon = (lon2-lon1).toRad();

  var lat1Rad = parseInt(lat1).toRad();
  var lat2Rad = parseInt(lat2).toRad();

  var a = Math.sin(dLat/2) * Math.sin(dLat/2) +
          Math.sin(dLon/2) * Math.sin(dLon/2) * Math.cos(lat1Rad) * Math.cos(lat2Rad);
  var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
  var d = R * c;
  bwrox.debug('Distance from %s,%s to %s,%s is ' + d, lon1, lat1, lon2, lat2 );
  return d;
}

/**
 *  TODO : test and optimize this algorithm
 *  Determine the best zoom level for distance
 *  @see http://wiki.openstreetmap.org/wiki/Zoom_levels
 *
 **/
function calculateZoomLevel(distance){
  // village or town
  var zoomLevel = 13;

  if (distance > 13000){
    // whole world (or Australia including islands...)
    zoomLevel = 0;
  }else if (distance > 7000){
    // very very big country (usa including islands...)
    zoomLevel = 1;
  }else if (distance > 5000){
    // very big country (Canada)
    zoomLevel = 2;
  }else if (distance > 3000){
    // big country (Russia)
    zoomLevel = 3;
  }else if (distance > 2000){
    // country (Spain including islands)
    zoomLevel = 4;
  }else if (distance > 1000){
    // country (France, UK)
    zoomLevel = 5;
  }else if (distance > 500){
    zoomLevel = 6;
  }else if (distance > 200){
    zoomLevel = 7;
  }else if (distance > 100){
    // area (Ile de France)
    zoomLevel = 8;
  }else if (distance > 50){
    // city (Berlin, London)
    zoomLevel = 9;
  }else if (distance > 30){
    zoomLevel = 10;
  }else if (distance > 10){
    // city (paris)
    zoomLevel = 11;
  }else if (distance > 1){
    zoomLevel = 12;
  }
  return zoomLevel;
}