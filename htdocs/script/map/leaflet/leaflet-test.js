
 jQuery(document).ready(function(){
   var apiKey = 'f18f7e7fa8014d8ab1379c78df29f5c6';
   var url = 'http://otile2.mqcdn.com/tiles/1.0.0/map//{z}/{x}/{y}.jpg';
   var mapAttribution = 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery courtesy of <a href="http://www.mapquest.com/" target="_blank">MapQuest</a>';
   var layer = new L.TileLayer(url, {maxZoom: 18, attribution: mapAttribution});

   var map = new L.Map('map');
   map.setView(new L.LatLng(51.505, -0.09), 13).addLayer(layer);
 });
 //var marker = new L.Marker(new L.LatLng(51.5, -0.09));
//map.addLayer(marker);
//
//marker.bindPopup('A pretty CSS3 popup.<br />Easily customizable.').openPopup();
//var mapHtmlId = 'map';
//
//// configure the tiles provider
//var mapquestUrl = 'http://{s}.mqcdn.com/tiles/1.0.0/osm/{z}/{x}/{y}.png';
//
//// subdomains
//var subDomains = ['otile1','otile2','otile3','otile4'];
//
//// legal mentions (required by OSM and MapQuest licences)
//var mapquestAttrib = 'Data, imagery and map information provided by <a href="http://open.mapquest.co.uk" target="_blank">MapQuest</a>,<a href="http://www.openstreetmap.org/" target="_blank">OpenStreetMap</a> and contributors.';
//
////create the map
//var osmMap = new L.Map(mapHtmlId, {attributionControl: true});
//
//// OSM layer
//var osmLayer = new L.TileLayer(mapquestUrl, {
//  maxZoom : 18,
//  attribution : mapquestAttrib,
//  subdomains : subDomains
//});
//
//osmMap.addLayer(osmLayer);
//
//var baseMaps = {
//  'Open Steet Map' : osmLayer
// //,'Google Map': googleLayer
//};
//
////layerGroups = {
////    'OpenSteetMap' : osmLayer
////  // ,'GoogleMap': googleLayer
////  };
////
////initLayersGroups();
//
//// center the map
//console.debug('Set default center and zoom.');
//osmMap.setView([25, 10], 8);
//
//// add scale
//console.debug('Add scale.');
//// FIXME L.control.scale({position: 'bottomright'}).addTo(osmMap);
//
//// mark map as initialized
//isInitialized = true;
//
//console.debug('Map initialized!');