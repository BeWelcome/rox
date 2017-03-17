var gmarkers = [];
var index = 0;
var state = '';

var map_scale;
var geocoder = null;
var map_showing = true;

var geosearchMapBuilder;
var reverseGeolocator;

BWRox.prototype.initSearchMembers = function() {
	
	// create a new map builder and init the map
	 geosearchMapBuilder = new BWGeosearchMapBuilder("map", mapoff);

	// if we have vars stored in the session or given by a GET-parameter,
	// perform a search to show the last results
	if (varsOnLoad || varsGet) {
		put_html('loading', loading);
		if (varsGet) {
			searchByText(varsGet, 0);
		} else {
			loadMap(0);
		}

	} else {
		put_html('help_and_markers', searchHelp);
	}
	varsOnLoad = '';

	// init geolocator
	reverseGeolocator = new BWGoogleMapReverseGeolocator();
	
	// init autocomplete
	// TODO uncomment to activate autocompletion
	// bwrox.initSearchAutocomplete('#Address');
	
	bwrox.debug("Page loaded.");
};

function searchGlobal(i) {
	state = 'global';
	put_html('loading', loading);
	$('paging-div').innerHTML = '';
	if ($('second_pager'))
		$('second_pager').innerHTML = '';
	put_val('mapsearch', 0);
	put_val('CityName', '');
	put_val('IdCountry', '');
	$('accuracy_level').value = '';
	$('CityNameOrg').value = '';
	$('place_coordinates').value = '';
	loadMap(i);
}

function searchByMap(i) {
	state = 'map';
	put_html('loading', loading);
	$('paging-div').innerHTML = '';
	if ($('second_pager'))
		$('second_pager').innerHTML = '';
	put_val('mapsearch', 1);
	$('accuracy_level').value = '';
	$('place_coordinates').value = '';
	$('CityNameOrg').value = '';
	var bounds = geosearchMapBuilder.getBounds();
	put_val('bounds_zoom', geosearchMapBuilder.getZoom());
	var bounds_center = bounds.getCenter();
	put_val('bounds_center_lat', bounds_center.lat);
	put_val('bounds_center_lng', bounds_center.lng);
	var bounds_sw = bounds.getSouthWest();
	var bounds_ne = bounds.getNorthEast();
	put_val('bounds_sw_lat', bounds_sw.lat);
	put_val('bounds_ne_lat', bounds_ne.lat);
	put_val('bounds_sw_lng', bounds_sw.lng);
	put_val('bounds_ne_lng', bounds_ne.lng);
	put_val('CityName', '');
	put_val('IdCountry', '');
	loadMap(i);
}

function searchByText(address, i) {
	bwrox.info("Running text search (address='" + address + "', i=" + i
			+ ")...");
	$('CityNameOrg').value = address;
	state = 'text';
	put_html('loading', loading);
	put_val('mapsearch', 0);

	$('paging-div').innerHTML = '';
	if ($('second_pager')){
		$('second_pager').innerHTML = '';
	}
	reverseGeolocator.getLocation(address, function(addressPoint) {
		searchByAddressPoint(addressPoint, i);
	}, function() {
		// address not fount
		put_html('loading', addressNotFound);
	});
}

// TODO not used, to remove
function searchByAddressPoint(addressPoint){
	jQuery("#Address").val(addressPoint.location);
	geosearchMapBuilder.setCenter(addressPoint.latitude, addressPoint.longitude, addressPoint.zoomLevel);

    $('accuracy_level').value = addressPoint.accuracy;
    $('place_coordinates').value = addressPoint.coordinates;
    $('IdCountry').value = addressPoint.countryNameCode;
    $('CityName').value = addressPoint.location;
	
	loadMap(0);
}

function searchByAddressPoint(addressPoint, i){
	
	bwrox.debug("Search by address point '%s': %s,%s", addressPoint.address,
			addressPoint.latitude, addressPoint.longitude);
	$('paging-div').innerHTML = '';
	if ($('second_pager')) {
		$('second_pager').innerHTML = '';
	}
	$('accuracy_level').value = addressPoint.accuracy;
	$('place_coordinates').value = addressPoint.coordinates;
	$('IdCountry').value = addressPoint.countryNameCode;
	$('CityName').value = addressPoint.location;

	map_scale = addressPoint.zoomLevel;
	// center the map
	geosearchMapBuilder.setCenter(addressPoint.latitude, addressPoint.longitude, map_scale);
	// load the map
	loadMap(i);
}

function flipSortDirection(e) {
	var e = e || window.event;
	Event.stop(e);
	var newstate = ($('filterDirection').selectedIndex + 1) % 2;
	$('filterDirection').selectedIndex = newstate;
	loadMap(0);
}

function loadMap(i) {
	bwrox.debug("Loading map (" + i + ")...");
	geosearchMapBuilder.clearMap();
	put_val('start_rec', i);
	// send search request
	bwrox.debug("Search members (varsOnLoad=" + varsOnLoad
			+ ", varSortOrder=" + varSortOrder + ", queries=" + queries + ")");

	new Ajax.Request(
			'searchmembers/ajax' + varsOnLoad + varSortOrder + queries,
			{
				parameters : $('searchmembers').serialize(true),
				method : 'get',
				onSuccess : function(req) {
					bwrox.debug("Search success: processing the results...");
					if (queries != '') {
						bwrox.info("Show member list (map is hidden)");
						// load members list result (no map)
						put_html('member_list', req.responseText);
						put_html('loading', '');
						toggle_map();
						return;
					}
					bwrox.debug("Parsing results...");
					// parse results
					var mapSearchResult = new BWMapSearchResult(req.responseXML);

					if (mapSearchResult.hasResults()) {
						bwrox.log("Adding the pager...");
						// remove the first child of first page
						var removedFirstPageChild = mapSearchResult.removeFirstPageChild();
						// create temporary div
						var tempdiv = document.createElement('div');
						try {
							// will fail in IE
							tempdiv.appendChild(removedFirstPageChild);
						} catch (e) {
							// IE hack
							tempdiv.innerHTML = mapSearchResult.getPaging();
						}
						$('paging-div').innerHTML = tempdiv.innerHTML;
						if (mapoff) {
							$('second_pager').innerHTML = tempdiv.innerHTML;
							hookUpPager('second_pager',
									mapSearchResult.per_page);
						}
						hookUpPager('paging-div', mapSearchResult.per_page);
					}
					var i;

					// reading the points
					mapSearchResult.readPoints();
					
					bwrox.debug("Adding the %d markers...", mapSearchResult.points.length);
					
					var detail = mapSearchResult.detailHeader;
					var index = 0;

					for (i = 0; i < mapSearchResult.points.length; i++) {
						if (mapSearchResult.points[i].summary != '') {
							// track the current result number
							index = i + 1;

							// add the marker and make the gmarkers
							// triggable from the links in the list next to
							// the map
							if (!mapoff) {
								gmarkers[index] = geosearchMapBuilder.addHostMarker(
								mapSearchResult.points[i], index);
							}
							detail += mapSearchResult.points[i].detail;
						}else{
							bwrox.warn("Summary missing for point %d.", i);
						}
					}
					// remove the unused layers control (associated with the layers without any markers)
					geosearchMapBuilder.removeUnusedLayersControls();

					if (!mapoff && state == 'global'
							&& mapSearchResult.points.length) {
						bwrox.debug("Calculate the center and zoom of the map...");
						// find min and max lat
						var minLat = 90, maxLat = -90;
						var aveLat = 0, delLat, lat, lng;
						for (i = 0; i < mapSearchResult.points.length; i++) {
							lat = parseFloat(mapSearchResult.points[i].latitude);
							if (lat > maxLat)
								maxLat = lat;
							if (lat < minLat)
								minLat = lat;
							aveLat += lat;
						}
						// find min and max long
						delLat = maxLat - minLat;
						aveLat /= mapSearchResult.points.length;
						var minLng1 = 180, maxLng1 = -180, aveLng1 = 0;
						var minLng2 = 360, maxLng2 = 0, aveLng2 = 0;
						for (i = 0; i < mapSearchResult.points.length; i++) {
							lng = parseFloat(mapSearchResult.points[i].longitude);
							if (lng > maxLng1)
								maxLng1 = lng;
							if (lng < minLng1)
								minLng1 = lng;
							aveLng1 += lng;
							if (lng < 0)
								lng += 180;
							else
								lng -= 180;
							if (lng > maxLng2)
								maxLng2 = lng;
							if (lng < minLng2)
								minLng2 = lng;
							aveLng2 += lng;
						}
						// calculate center
						var delLng1 = maxLng1 - minLng1;
						var delLng2 = maxLng2 - minLng2;
						aveLng1 /= mapSearchResult.points.length;
						aveLng2 /= mapSearchResult.points.length;
						// FIXME lng is not defined!
						if (lng <= 0)
							aveLng2 -= 180;
						else
							aveLng2 += 180;
						var aveLng, delLng;
						if (delLng2 < delLng1) {
							delLng = delLng2;
							aveLng = aveLng2;
						} else {
							delLng = delLng1;
							aveLng = aveLng1;
						}
						if (delLat > delLng)
							delLng = delLat;
						if (delLng > 70)
							map_scale = 2;
						else if (delLng > 50)
							map_scale = 3;
						else if (delLng > 25)
							map_scale = 4;
						else if (delLng > 5)
							map_scale = 5;
						else
							map_scale = 6;
						
						var distance = calculateDistance(minLat, maxLat, minLng1, maxLng1);
						map_scale = calculateZoomLevel(distance);
						
						geosearchMapBuilder.setCenter(aveLat, aveLng, map_scale);
					}

					if (mapSearchResult.footerDetail){
						detail += mapSearchResult.footerDetail;
					}
					put_html('member_list', detail);
					var num_results = mapSearchResult.numResults;
					var num_all_results = mapSearchResult.numAllResults;
					var addRes = '';
		            if (num_all_results > num_results) {
		            	addRes = ' (' + searchShowMore + ')'            	          	
		        	}
		            if (num_results > 0) {            	            	
		                put_html('loading', num_results + ' ' + wordFound + addRes);
		            } else {
		                put_html('loading', noMembersFound + addRes);
		            }
					if (num_results == 0) {
						put_html('help_and_markers', searchHelp);
					} else {
						put_html('help_and_markers', '');
					}
					bwrox.info("Map succesfully loaded");
					jQuery(".ui-autocomplete").hide();
				}
			});
	
	// reset criterias immediately after request has been sent
	$('accuracy_level').value = null;
    $('place_coordinates').value = null;
    $('IdCountry').value = null;
    $('CityName').value = null;
}

function hookUpPager(container_id, per_page) {
	var pager_links = $(container_id).getElementsByTagName('a');
	for ( var a = 0; a < pager_links.length; a++) {
		$(pager_links[a]).observe('click', function(e) {
			var ev = e || windows.event;
			Event.stop(ev);
			var reg = /page=(\d+)/;
			var page = (this.href.match(reg)[1] - 1) * per_page;
            if (state == 'text')
                searchByText(get_val("Address"), page);
            else if (state == 'map')
                searchByMap(page);
            else
                loadMap(page);
		});
	}
}

function toggle_map() {
	if (map_showing) {
		document.getElementById("MapDisplay").style.display = 'none';
		map_showing = false;
	} else {
		document.getElementById("MapDisplay").style.display = '';
		map_showing = true;
	}
}

function getxmlEl(x, s) {
	return x.documentElement.getElementsByTagName(s);
}

function page_navigate(i) {
	if (state == 'text')
		searchByText(get_val("Address"), i);
	else if (state == 'map')
		searchByMap(i);
	else
		loadMap(i);
}

function get_val(field) {
	return document.getElementById(field).value;
}

function put_val(field, s) {
	document.getElementById(field).value = s;
}

function get_html(field) {
	return document.getElementById(field).innerHTML;
}

function put_html(field, s) {
	document.getElementById(field).innerHTML = s;
}

function getFieldHelp(name) {
	put_html('help_and_markers', eval('fieldHelp' + name));
}

function chkEnt(field, e) {
	var keycode;
	if (window.event)
		keycode = window.event.keyCode;
	else if (e)
		keycode = e.which;
	else
		return false;

	if (keycode == 13)
		return true;
	return false;
}

function newWindow(un) {
	window.open(http_baseuri + 'bw/member.php?cid=' + un);
}

jQuery(function() {
	// document loaded
	bwrox.initSearchMembers();
});