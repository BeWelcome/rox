	var state = 0;
	var map = null;
	var geocoder = null;

  function load() {
    if (GBrowserIsCompatible()) {
			geocoder = new GClientGeocoder();
      map = new GMap2(document.getElementById("map"));
			map.addControl(new GLargeMapControl());
			map.addControl(new GMapTypeControl());
			map.enableDoubleClickZoom();
		  map.setCenter(new GLatLng(0, 0), 1);
			GEvent.addListener(map, "click", function(overlay, point)	{
				if (overlay && overlay.summary) overlay.openInfoWindowHtml(overlay.summary);
			});
		}
  }
	function update_map_loc() {
		state = 2;
		document.getElementById('map_search').value = 'Loading...';
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
		document.getElementById('MapSearch').value = 'on';
		document.getElementById('CityName').value = '';
		document.getElementById('IdCountry').value = '';
		LoadMap();
	}
	function showAddress(address) {
	  state = 1;
		document.getElementById('text_search').value = 'Loading...';
		document.getElementById('MapSearch').value = 'off';
	  if (geocoder) {
			geocoder.getLocations(
				address,
			  function(response) {
				  if (!response || response.Status.code != 200) {
    				alert("\"" + address + "\" not found");
				  }
					else {
//for (key in response.Placemark[0].AddressDetails.Country.AdministrativeArea) {
//   alert(key+' = '+response.Placemark[0].AddressDetails.Country.AdministrativeArea[key]);
//}
				  	place = response.Placemark[0];
  					point = new GLatLng(place.Point.coordinates[1], place.Point.coordinates[0]);
						var map_scale = 5;
    				with(place.AddressDetails.Country) {
							if(typeof(CountryNameCode) != "undefined") {
								switch(CountryNameCode) {
								  case 'RU': map_scale = 2; break;
								  case 'US': map_scale = 3; break;
								  case 'CA': map_scale = 3; break;
								  case 'CN': map_scale = 3; break;
								  case 'BR': map_scale = 3; break;
								  case 'AU': map_scale = 3; break;
								}
								document.getElementById('IdCountry').value = CountryNameCode;
							}
							var CityPath = null;
							if(typeof(AdministrativeArea) != "undefined") {
								map_scale = 5;
								CityPath = AdministrativeArea;
								if(typeof(CityPath.SubAdministrativeArea) != "undefined") {
								  CityPath = CityPath.SubAdministrativeArea;
								}
							  if(typeof(CityPath.Locality) != "undefined") CityPath = CityPath.Locality;
							  else CityPath = null;
							}
							else if(typeof(Locality) != "undefined") CityPath = Locality;
							if(CityPath != null && typeof(CityPath.LocalityName) != "undefined") {
								map_scale = 7;
								document.getElementById('CityName').value = CityPath.LocalityName;
	  					}
	  					else {
								document.getElementById('CityName').value = '';
							}
						}
	          map.setCenter(point, map_scale);
						LoadMap();
					}
				}
			);
	  }
	}
	function LoadMap()
	{
		new Ajax.Request('findpeople_ajax.php', {
				parameters: $('findpeopleform').serialize(true),
				onSuccess: function(req) {
//									alert(req.responseText);
//									return;

					var detail = '<table><tr><th>Country</th><th>About Me</th><th>Accommodation</th><th>Last login</th><th># comments</th><th>Age</th></tr>';
					var xmlDoc = req.responseXML;
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
 						map.addOverlay(marker);
					}
					detail += '</table>';
					var page = xmlDoc.documentElement.getElementsByTagName("page");
					detail += page[0].getAttribute("page");
					document.getElementById("member_list").innerHTML = detail;
					if(state == 1) document.getElementById('text_search').value = 'Search using text';
					else if(state == 2) document.getElementById('map_search').value = 'Search using map boundaries';
				}
		});
	}
	function page_navigate(i1)
	{
    document.getElementById("start_rec").value = i1;
    if(state == 1) showAddress(document.getElementById("address").value);
    else if(state == 2) update_map_loc();
	}

	// Create our "tiny" marker icon
	var icon = new GIcon();
	icon.image = "images/gicon1.png";
	icon.shadow = "images/gicon1_shadow.png";
	icon.iconSize = new GSize(18, 27);
	icon.shadowSize = new GSize(18, 27);
	icon.iconAnchor = new GPoint(8, 27);
	icon.infoWindowAnchor = new GPoint(5, 1);

	window.onload = load;
