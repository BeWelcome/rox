
// Code partly taken from Google Maps examples: 
// http://gmaps-utility-library.googlecode.com/svn/trunk/markermanager/release/examples/google_northamerica_offices.html

    var map;
    var mgr;
    var icons = {};
    var allmarkers = [];
    var bounds = new GLatLngBounds();
    
    function load() {
      if (GBrowserIsCompatible()) {
        map = new GMap2(document.getElementById("map"));
        var mapTypeControl = new GSmallMapControl();
        var topRight = new GControlPosition(G_ANCHOR_TOP_RIGHT, new GSize(30,50));
        map.addControl(mapTypeControl,topRight);
        map.addControl(new GMapTypeControl());
        map.setCenter(new GLatLng(25, -10), 2);
        map.addMapType(G_PHYSICAL_MAP);
        map.setMapType(G_PHYSICAL_MAP); 
        map.enableDoubleClickZoom();
        mgr = new MarkerManager(map, {trackMarkers:true});
        window.setTimeout(setupOfficeMarkers, 0);
        zoomfit(map);
      }
    }
    
    function zoomfit(map)
    {
        map.setZoom(map.getBoundsZoomLevel(bounds));
        map.setCenter(bounds.getCenter());
    }

    function getIcon(images) {
      var icon = null;
      if (images) {
        if (icons[images[0]]) {
          icon = icons[images[0]];
        } else {
          icon = new GIcon();
          icon.image = "images/icons/" 
              + images[0] + ".png";
          var size = iconData[images[0]];
          icon.iconSize = new GSize(size.width, size.height);
          icon.iconAnchor = new GPoint(size.width >> 6, size.height >> 0);
          icon.shadow = "images/icons/" 
              + images[1] + ".png";
          size = iconData[images[1]];
          icon.shadowSize = new GSize(size.width, size.height);
          icons[images[0]] = icon;
        }
      }
      return icon;
    }

    function setupOfficeMarkers() {
      allmarkers.length = 0;
      for (var i=0; i<officeLayer.length; i++) {
        var layer = officeLayer[i];
        var markers = [];
        for (var j=0; j<layer["places"].length; j++) {
          var place = layer["places"][j];
          var icon = getIcon(place["icon"]);
          var title = place["name"];
          var posn = new GLatLng(place["posn"][0], place["posn"][1]);
          var marker = createMarker(posn,title,icon); 
          bounds.extend(posn);
          markers.push(marker);
          allmarkers.push(marker);
        }
        mgr.addMarkers(markers, layer["zoom"][0], layer["zoom"][1]);
      }
      mgr.refresh();
    }
  
    function createMarker(posn, title, icon) {
      var marker = new GMarker(posn, {title: title, icon: icon, draggable:true });
      GEvent.addListener(marker, 'dblclick', function() { mgr.removeMarker(marker) } ); 
      return marker;
    }

    function deleteMarker() {
      var markerNum = parseInt(document.getElementById("markerNum").value);
      mgr.removeMarker(allmarkers[markerNum]);
    }
   
    function clearMarkers() {
      mgr.clearMarkers();
    }
   
    function reloadMarkers() {
      setupOfficeMarkers();
    }
