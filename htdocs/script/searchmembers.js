var gmarkers = [];
var index = 0 ;
var state = '';
var map = null;
var map_scale;
var geocoder = null;
var map_showing = true;

function load() {
    if (GBrowserIsCompatible()) {
        geocoder = new GClientGeocoder();
        if(!mapoff) {
			loadSearchMapButton();
            map = new GMap2(document.getElementById("map"));
            map.addControl(new GLargeMapControl());
            map.addControl(new GHierarchicalMapTypeControl(), new GControlPosition(G_ANCHOR_BOTTOM_RIGHT, new GSize(7, 10)));
		    map.addControl(new SearchMapButton());
            map.enableDoubleClickZoom();
            map.addMapType(G_PHYSICAL_MAP);
            map.setMapType(G_PHYSICAL_MAP);
            map.setCenter(new GLatLng(25, 10), 1);
            GEvent.addListener(map, "click", function(overlay, point)	{
                if (overlay && overlay.summary) overlay.openInfoWindowHtml(overlay.summary);
            });         
        }
    }
    // if we have vars stored in the session or given by a GET-parameter, perform a search to show the last results
    if (varsOnLoad || varsGet) {
        put_html('loading', loading);
        if (varsGet) searchByText(varsGet, 0);
        else loadMap(0);
    }
    else
        put_html('help_and_markers', searchHelp);

    varsOnLoad = '';
}

function searchGlobal(i) {
    state = 'global';
    put_html('loading', loading);
    $('paging-div').innerHTML = '';
    if ($('second_pager')) $('second_pager').innerHTML = '';
    put_val('mapsearch', 0);
    put_val('CityName', '');
    put_val('IdCountry', '');
    $('accuracy_level').value = '';
    $('CityNameOrg').value = '';
    $('place_coordinates').value = '';
    loadMap(i);
}

// A TextualZoomControl is a GControl that displays textual "Zoom In"
// and "Zoom Out" buttons (as opposed to the iconic buttons used in
// Google Maps).
function SearchMapButton() {
}

function loadSearchMapButton() {
	SearchMapButton.prototype = new GControl();

	// Creates a one DIV for each of the buttons and places them in a container
	// DIV which is returned as our control element. We add the control to
	// to the map container and return the element for the map class to
	// position properly.
	SearchMapButton.prototype.initialize = function(map) {
	  var container = document.createElement("a");

	  var searchInDiv = document.createElement("a");
	  this.setButtonStyle_(searchInDiv);
	  container.appendChild(searchInDiv);
	  searchInDiv.appendChild(document.createTextNode(searchInDivText));
	  GEvent.addDomListener(searchInDiv, "click", function() {
	    searchByMap(0);
	  });

	  map.getContainer().appendChild(container);
	  return container;
	}

	// By default, the control will appear in the top left corner of the
	// map with 7 pixels of padding.
	SearchMapButton.prototype.getDefaultPosition = function() {
	  return new GControlPosition(G_ANCHOR_TOP_RIGHT, new GSize(7, 17));
	}

	// Sets the proper CSS for the given button element.
	SearchMapButton.prototype.setButtonStyle_ = function(button) {
	  button.style.paddingTop = "2px";
	  button.style.marginBottom = "3px";
	  button.style.textAlign = "center";
	  button.style.cursor = "pointer";
	  button.className = "button";
	  button.id = "map_search";
	}
}


function searchByMap(i) {
    state = 'map';
    put_html('loading', loading);
    $('paging-div').innerHTML = '';
    if ($('second_pager')) $('second_pager').innerHTML = '';
    put_val('mapsearch', 1);
    $('accuracy_level').value = '';
    $('place_coordinates').value = '';
    $('CityNameOrg').value = '';
    var bounds = map.getBounds();
    put_val('bounds_zoom', map.getZoom());
    var bounds_center = bounds.getCenter();
    put_val('bounds_center_lat', bounds_center.lat());
    put_val('bounds_center_lng', bounds_center.lng());
    var bounds_sw = bounds.getSouthWest();
    var bounds_ne = bounds.getNorthEast();
    put_val('bounds_sw_lat', bounds_sw.lat());
    put_val('bounds_ne_lat', bounds_ne.lat());
    put_val('bounds_sw_lng', bounds_sw.lng());
    put_val('bounds_ne_lng', bounds_ne.lng());
    put_val('CityName', '');
    put_val('IdCountry', '');
    loadMap(i);
}

function searchByText(address, i) {
    $('CityNameOrg').value = address;
    state = 'text';
    put_html('loading', loading);
    put_val('mapsearch', 0);
    if(geocoder) {
        geocoder.getLocations(
            address,
            function(response) {
                $('paging-div').innerHTML = '';
                if ($('second_pager')) $('second_pager').innerHTML = '';
                if(!response || response.Status.code != 200)
                {
                    put_html('loading', addressNotFound);
                }
                else
                {
                    var place = response.Placemark[0];
                    var point = new GLatLng(place.Point.coordinates[1], place.Point.coordinates[0]);
                    put_val('CityName', '');
                    put_val('IdCountry', '');
                    $('accuracy_level').value = '';
                    $('place_coordinates').value = '';
                    map_scale = 3;
                    extractLocationData(place);
                    if(!mapoff) {
                    	map.setCenter(point, map_scale);
                    	map.addOverlay(new GMarker(point));
                    }
                    loadMap(i);
                }
            }
        );
    }
}

function extractLocationData(geo_object)
{
    if(typeof(geo_object) == 'object' && geo_object.AddressDetails && geo_object.AddressDetails.Accuracy && geo_object.AddressDetails.Country && geo_object.AddressDetails.Country.CountryNameCode && geo_object.Point && geo_object.Point.coordinates)
    {
        $('accuracy_level').value = geo_object.AddressDetails.Accuracy;
        $('place_coordinates').value = geo_object.Point.coordinates;
        $('IdCountry').value = geo_object.AddressDetails.Country.CountryNameCode;
        var location = '';
        if (geo_object.AddressDetails.Country.AdministrativeArea)
        {
            location = geo_object.AddressDetails.Country.AdministrativeArea.AdministrativeAreaName;
            if (geo_object.AddressDetails.Country.AdministrativeArea.SubAdministrativeArea)
            {
                location = geo_object.AddressDetails.Country.AdministrativeArea.SubAdministrativeArea.SubAdministrativeAreaName;
                if (geo_object.AddressDetails.Country.AdministrativeArea.SubAdministrativeArea.Locality)
                {
                    location = geo_object.AddressDetails.Country.AdministrativeArea.SubAdministrativeArea.Locality.LocalityName;
                }
            }
        }
        $('CityName').value = location;
    }
    else
    {
        $('accuracy_level').value = '';
        $('place_coordinates').value = '';
        $('IdCountry').value = '';
    }
    switch (parseInt($('accuracy_level').value))
    {
        case 0:
            map_scale = 3;
            break;
        case 1:
            switch ($('IdCountry').value)
            {
                case 'RU':
                case 'US':
                case 'CA':
                case 'CN':
                case 'BR':
                case 'AU':
                    map_scale = 3;
                    break;
                default:
                    map_scale = 5;
            }
            break;
        case 2:
            map_scale = 7;
            break;
        case 3:
            map_scale = 9;
            break;
        case 4:
            map_scale = 10;
            break;
        default:
            map_scale = 11;
            break;
    }
}

function scanObject(object)
{
    if(typeof(object) != 'object') return;
    for(key in object) {
        var item = object[key];
        if(typeof(item) == "object") scanObject(item);
        else if(key == "ThoroughfareName") map_scale = (map_scale < 11 ? 11 : map_scale);
        else if(key == "LocalityName" || key == "DependentLocalityName") {
            put_val('CityName', item);
            map_scale = (map_scale < 10 ? 10 : map_scale);
        }
        else if(key == "SubAdministrativeAreaName") map_scale = (map_scale < 9 ? 9 : map_scale);
        else if(key == "AdministrativeAreaName") map_scale = (map_scale < 7 ? 7 : map_scale);
        else if(key == "CountryNameCode") {
            put_val('IdCountry', item);
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

function flipSortDirection(e)
{
    var e = e || window.event;
    Event.stop(e);
    var newstate = ($('filterDirection').selectedIndex + 1) % 2;
    $('filterDirection').selectedIndex = newstate;
    loadMap(0);
}

function loadMap(i)
{
    if(!mapoff) map.clearOverlays();
    put_val('start_rec', i);
    new Ajax.Request('searchmembers/ajax'+varsOnLoad+varSortOrder+queries, {
        parameters: $('searchmembers').serialize(true),
        method: 'get',
        onSuccess: function(req) {
            //alert(req.responseText);return;
            if(queries != '') {
                put_html('member_list', req.responseText);
                put_html('loading', '');
                toggle_map();
                return;
            }
            var xmlDoc = req.responseXML;
            var header = getxmlEl(xmlDoc, "header");
            var detail = header[0].getAttribute("header");
            var markers = getxmlEl(xmlDoc, "marker");
            var pager = getxmlEl(xmlDoc, "pager");
            var per_page = pager[0].getAttribute('per_page');
            if (pager[0].firstChild)
            {
                var removed = pager[0].removeChild(pager[0].firstChild);
                var tempdiv = document.createElement('div');
                try
                {
                    // will fail in IE
                    tempdiv.appendChild(removed);
                }
                catch (e)
                {
                    tempdiv.innerHTML = pager[0].getAttribute('paging');
                }
                $('paging-div').innerHTML = tempdiv.innerHTML;
                if (mapoff)
                {
                    $('second_pager').innerHTML = tempdiv.innerHTML;
                    hookUpPager('second_pager', per_page);
                }
                hookUpPager('paging-div', per_page);
            }
            var i, j, marker;
            var point = new Array();
            var accomodation = new Array();
            var summary = new Array();
            
            for(i = 0; i < markers.length; i++) {
                point[i] = new GLatLng(
                    parseFloat(markers[i].getAttribute("Latitude")),
                    parseFloat(markers[i].getAttribute("Longitude"))
                );
                accomodation[i] = markers[i].getAttribute("accomodation");
                summary[i] = markers[i].getAttribute("summary");
            }
            
            // combine marker summaries when coordinates and accomodation is the same,
            // in groups of columns x rows
            var row, column;
            for(i = 0; i < markers.length; i++) {
                if(summary[i] == '') continue;
                column = 0;
                summary[i] = '<table><tr><td>'+summary[i];
                for(j = i + 1; j < markers.length; j++) {
                    if(summary[j] == '') continue;
            // DEACTIVATED - No combination of markers for now - by lupochen
            /*
                    if(point[i].x == point[j].x && point[i].y == point[j].y && accomodation[i] == accomodation[j]) {
                        if(++column >= 3) {
                            summary[i] += '</td></tr>';
                            column = 0;
                            summary[i] += '<tr><td>';
                        }
                        else summary[i] += '</td><td>';
                        summary[i] += summary[j];
                        summary[j] = '';
                    }
                            */
                } 
                summary[i] += '</td></tr></table>';
            }
            
            // space markers that have the same geo-coordinates
            var offset = 1;
            var newpoint = 1;
            var newx = 0;
            var newy = 0;
            for(i = 0; i < markers.length; i++) {
                for(j = i + 1; j < markers.length; j++) {
                    /*if(summary[j] == '') continue; */
                    if(point[i].x == point[j].x && point[i].y == point[j].y) {
                        newx = (0.001*offset)* Math.cos(40*newpoint*Math.PI/180) - (0.001*offset)* Math.sin(40*newpoint*Math.PI/180) + point[i].x;
                        newy = (0.001*offset)* Math.sin(40*newpoint*Math.PI/180) + (0.001*offset)* Math.cos(40*newpoint*Math.PI/180) + point[i].y;
                        point[i] = new GLatLng(newy, newx);
                        ++newpoint;
                        if (newpoint == 9) {
                            newpoint = 1;
                            offset = offset+1;
                        }
                    }
                }
            } 

            for(i = 0; i < markers.length; i++) {
                detail += markers[i].getAttribute("detail");
                if(!mapoff && summary[i] != '') {
                    // track the current result number
                    index = i+1;
                    var latlng2 = new GLatLng(parseFloat(markers[i].getAttribute("Latitude")),parseFloat(markers[i].getAttribute("Longitude")));
                    // check the accomodation and choose the right marker icon
                    if(accomodation[i] == 'anytime') mod_icon = icon;
                    else if(accomodation[i] == 'neverask') mod_icon = icon3;
                    else mod_icon = icon2;
                    
                	var opts = {
                		"icon": mod_icon,
                		"clickable": true,
                		"labelText": index,
                		"labelOffset": new GSize(-23, -27)
                    };

                    var marker = new LabeledMarker(point[i], opts);
                    marker.summary = summary[i];
                    
                    // single event listeners for the markers
                    GEvent.addListener(marker, "click", function() {
                    marker.openInfoWindowHtml(marker.summary);
                    });
                    
                    // add the markers now!
                    map.addOverlay(marker);
                    
                    // make the gmarkers triggable from the links in the list next to the map
                    gmarkers[index] = marker;
                }
            }
            if(!mapoff && state == 'global' && markers.length) {
                var minLat = 90, maxLat = -90;
                var aveLat = 0, delLat, lat, lng;
                for(i = 0; i < markers.length; i++) {
                    lat = parseFloat(markers[i].getAttribute("Latitude"));
                    if(lat > maxLat) maxLat = lat;
                    if(lat < minLat) minLat = lat;
                    aveLat += lat;
                }
                delLat = maxLat - minLat;
                aveLat /= markers.length;
                var minLng1 = 180, maxLng1 = -180, aveLng1 = 0;
                var minLng2 = 360, maxLng2 = 0, aveLng2 = 0;
                for(i = 0; i < markers.length; i++) {
                    lng = parseFloat(markers[i].getAttribute("Longitude"));
                    if(lng > maxLng1) maxLng1 = lng;
                    if(lng < minLng1) minLng1 = lng;
                    aveLng1 += lng;
                    if(lng < 0) lng += 180;
                    else lng -= 180;
                    if(lng > maxLng2) maxLng2 = lng;
                    if(lng < minLng2) minLng2 = lng;
                    aveLng2 += lng;
                }
                var delLng1 = maxLng1 - minLng1;
                var delLng2 = maxLng2 - minLng2;
                aveLng1 /= markers.length;
                aveLng2 /= markers.length;
                if(lng <= 0) aveLng2 -= 180;
                else aveLng2 += 180;
                var aveLng, delLng;
                if(delLng2 < delLng1) {
                    delLng = delLng2;
                    aveLng = aveLng2;
                }
                else {
                     delLng = delLng1;
                     aveLng = aveLng1;
                }
                if(delLat > delLng) delLng = delLat;
                if(delLng > 70) map_scale = 2;
                else if(delLng > 50) map_scale = 3;
                else if(delLng > 25) map_scale = 4;
                else if(delLng > 5) map_scale = 5;
                else map_scale = 6;
                point = new GLatLng(aveLat, aveLng);
               	map.setCenter(point, map_scale);
            }
            var footer = getxmlEl(xmlDoc, "footer");
            detail += footer[0].getAttribute("footer");
            put_html('member_list', detail);
            var results = getxmlEl(xmlDoc, "num_results");
            var num_results = results[0].getAttribute("num_results");
            var num_all_results = results[0].getAttribute("num_all_results");
            var addRes = '';
            if (Number(num_all_results) > Number(num_results)) {
            	addRes = ' (' + num_all_results + ' ' + membersVisibleTo + ' <a href="login/searchmembers#login-widget">' + loggedInMembers + '</a>)'            	          	
        	}
            if (num_results > 0) {            	            	
                put_html('loading', markers.length + ' ' + membersDisplayed + ' ' + wordOf + ' ' + num_results + ' ' + wordFound + addRes);
            } else {
                put_html('loading', noMembersFound + addRes);
            }
            if (num_results == 0) {
                put_html('help_and_markers', searchHelp);
            } else {
                put_html('help_and_markers', '');
            }
        }
    });
}

function hookUpPager(container_id, per_page)
{
    var pager_links = $(container_id).getElementsByTagName('a');
    for (var a = 0; a < pager_links.length; a++)
    {
        $(pager_links[a]).observe('click', function(e){
            var ev = e || windows.event;
            Event.stop(ev);
            var reg = /page=(\d+)/;
            var page = (this.href.match(reg)[1] - 1) * per_page;
            loadMap(page);
        });
    }
}

function toggle_map()
{
    if(map_showing) {
        document.getElementById("MapDisplay").style.display = 'none';
        map_showing = false;
    }
    else {
        document.getElementById("MapDisplay").style.display = '';
        map_showing = true;
    }
}

function getxmlEl(x, s) {return x.documentElement.getElementsByTagName(s);}

function page_navigate(i)
{
    if(state == 'text') searchByText(get_val("Address"), i);
    else if(state == 'map') searchByMap(i);
    else loadMap(i);
}

function get_val(field) {return document.getElementById(field).value;}

function put_val(field, s) {document.getElementById(field).value = s;}

function get_html(field) {return document.getElementById(field).innerHTML;}

function put_html(field, s) {document.getElementById(field).innerHTML = s;}

function getFieldHelp(name)
{
    put_html('help_and_markers', eval('fieldHelp'+name));
}

function chkEnt(field, e)
{
    var keycode;
    if (window.event) keycode = window.event.keyCode;
    else if (e) keycode = e.which;
    else return false;

    if(keycode == 13) return true;
    return false;
}

function newWindow(un)
{
    window.open(http_baseuri+'bw/member.php?cid='+un);
}

// Create our "tiny" marker icon - SMALL VERSION

var icon = new GIcon(); // green - agreeing
icon.image = "images/icons/gicon1_a.png";
icon.shadow = "images/icons/gicon1_a_shadow.png";
icon.iconSize = new GSize(29, 21);
icon.shadowSize = new GSize(38, 21);
icon.iconAnchor = new GPoint(17, 21);
icon.infoWindowAnchor = new GPoint(17, 21);

var icon2 = new GIcon(); // black
icon2.image = "images/icons/gicon2_a.png";
icon2.shadow = "images/icons/gicon1_a_shadow.png";
icon2.iconSize = new GSize(29, 21);
icon2.shadowSize = new GSize(38, 21);
icon2.iconAnchor = new GPoint(17, 21);
icon2.infoWindowAnchor = new GPoint(17, 21);

var icon3 = new GIcon(); // grey - doubting
icon3.image = "images/icons/gicon3_a.png";
icon3.shadow = "images/icons/gicon1_a_shadow.png";
icon3.iconSize = new GSize(29, 21);
icon3.shadowSize = new GSize(38, 21);
icon3.iconAnchor = new GPoint(17, 21);
icon3.infoWindowAnchor = new GPoint(17, 21);

// character array and function. For using characters instead of numbers for the markers
// DEACTIVATED for now
/*
var characters = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z"];

function chooseCharacter(i) {
var index = i;
    if (i <= 26) {
        output = characters[i];
    } else {
        if (i <= 52) {
            var pre = "A"; 
            output = pre+characters[index];
        } else {
            if (i <= 78) {
                output = "B"+characters[i];
            } else {
                output = "C"+characters[i];
            }
        }
    }
    return output;
}
*/

Event.observe(window, "load", load); 
