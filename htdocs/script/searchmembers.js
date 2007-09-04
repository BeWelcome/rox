var state = 0;
var map = null;
var map_scale;
var geocoder = null;

function load() {
  if (GBrowserIsCompatible()) {
		geocoder = new GClientGeocoder();
    map = new GMap2(document.getElementById("map"));
		map.addControl(new GLargeMapControl());
		map.addControl(new GMapTypeControl());
		map.enableDoubleClickZoom();
	  map.setCenter(new GLatLng(15, 10), 2);
		GEvent.addListener(map, "click", function(overlay, point)	{
			if (overlay && overlay.summary) overlay.openInfoWindowHtml(overlay.summary);
		});
	}
}
function searchByMap() {
	state = 2;
	document.getElementById('map_search').value = loading;
	var bounds = map.getBounds();
	document.getElementById('bounds_zoom').value = map.getZoom();
	var bounds_center = bounds.getCenter();
	var bounds_center_lat = bounds_center.lat();
	document.getElementById('bounds_center_lat').value = bounds_center_lat;
	var bounds_center_lng = bounds_center.lng();
	document.getElementById('bounds_center_lng').value = bounds_center_lng;
	var bounds_sw = bounds.getSouthWest();
	var bounds_ne = bounds.getNorthEast();
	var bounds_sw_lat = bounds_sw.lat();
	document.getElementById('bounds_sw_lat').value = bounds_sw_lat;
	var bounds_ne_lat = bounds_ne.lat();
	document.getElementById('bounds_ne_lat').value = bounds_ne_lat;
	var bounds_sw_lng = bounds_sw.lng();
	document.getElementById('bounds_sw_lng').value = bounds_sw_lng;
	var bounds_ne_lng = bounds_ne.lng();
	document.getElementById('bounds_ne_lng').value = bounds_ne_lng;
	document.getElementById('CityName').value = '';
	document.getElementById('IdCountry').value = '';
	map.clearOverlays();
	loadMap();
}

function searchByText(address) {
  state = 1;
	document.getElementById('text_search').value = loading;
  if (geocoder) {
		geocoder.getLocations(
			address,
		  function(response) {
			  if (!response || response.Status.code != 200) {
  				alert("address not found.");
			  }
				else {
			  	var place = response.Placemark[0];
					var point = new GLatLng(place.Point.coordinates[1], place.Point.coordinates[0]);
					document.getElementById('CityName').value = '';
					document.getElementById('IdCountry').value = '';
					 map_scale = 3;
                     scanObject(place, 0);
          if(!mapoff) {
						map.clearOverlays();
						map.setCenter(point, map_scale);
						map.addOverlay(new GMarker(point));
					}
					loadMap();
				}
			}
		);
  }
}

function scanObject(object, i)
{
	if(typeof(object) != 'object') return;
	for (key in object) {
		var item = object[key];
		if(typeof(item) == "object") scanObject(item, i+1);
		else if(key == "ThoroughfareName") map_scale = (map_scale < 11 ? 11 : map_scale);
		else if(key == "LocalityName" || key == "DependentLocalityName") {
			document.getElementById('CityName').value = item;
			map_scale = (map_scale < 10 ? 10 : map_scale);
		}
		else if(key == "SubAdministrativeAreaName") map_scale = (map_scale < 9 ? 9 : map_scale);
		else if(key == "AdministrativeAreaName") map_scale = (map_scale < 7 ? 7 : map_scale);
		else if(key == "CountryNameCode") {
			document.getElementById('IdCountry').value = item;
			map_scale = (map_scale < 5 ? 5 : map_scale);
			if(map_scale <= 5) switch(item) {
			  case 'RU': map_scale = 3; break;
			  case 'US': map_scale = 3; break;
			  case 'CA': map_scale = 3; break;
			  case 'CN': map_scale = 3; break;
			  case 'BR': map_scale = 3; break;
			  case 'AU': map_scale = 3; break;
			}
		}
	}
}

function loadMap()
{
	new Ajax.Request('rox/searchmembers_ajax', {
			parameters: $('searchmembers').serialize(true),
			onSuccess: function(req) {
//alert(req.responseText);return;
				var xmlDoc = req.responseXML;
				var header = xmlDoc.documentElement.getElementsByTagName("header");
				var detail = header[0].getAttribute("header");
				var markers = xmlDoc.documentElement.getElementsByTagName("marker");
				for (var i = 0; i < markers.length; i++) {
					var lat = parseFloat(markers[i].getAttribute("Latitude"));
					var lng = parseFloat(markers[i].getAttribute("Longitude"));
					var lat = parseFloat(lat);
					var lng = parseFloat(lng);
					var point = new GPoint(lng, lat);
					var marker = new GMarker(point, icon);
					marker.summary = markers[i].getAttribute("summary");
					detail += markers[i].getAttribute("detail");
					if(!mapoff) map.addOverlay(marker);
				}
				var footer = xmlDoc.documentElement.getElementsByTagName("footer");
				detail += footer[0].getAttribute("footer");
				var page = xmlDoc.documentElement.getElementsByTagName("page");
				detail += page[0].getAttribute("page");
				document.getElementById("member_list").innerHTML = detail;
				if(state == 1) document.getElementById('text_search').value = text_search;
				else if(state == 2) document.getElementById('map_search').value = map_search;
			}
	});
}

function page_navigate(i1)
{
  reset_start_rec(i1);
  if(state == 1) searchByText(document.getElementById("address").value);
  else if(state == 2) searchByMap();
}

function reset_start_rec(rec)
{
  document.getElementById("start_rec").value = rec;
}

// Create our "tiny" marker icon
var icon = new GIcon();
icon.image = "images/icons/gicon1.png";
icon.shadow = "images/icons/gicon1_shadow.png";
icon.iconSize = new GSize(18, 27);
icon.shadowSize = new GSize(18, 27);
icon.iconAnchor = new GPoint(8, 27);
icon.infoWindowAnchor = new GPoint(5, 1);

window.onload = load;
