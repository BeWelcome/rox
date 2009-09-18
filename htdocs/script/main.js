var http_baseuri = new String(document.getElementById('baseuri').href);
var agt = navigator.userAgent.toLowerCase();
var is_op = (agt.indexOf("opera") != -1);
var is_ie = (agt.indexOf("msie") != -1) && document.all && !is_op;
var is_ie5 = (agt.indexOf("msie 5") != -1) && document.all && !is_op;
var is_mac = (agt.indexOf("mac") != -1);
var is_gk = (agt.indexOf("gecko") != -1);
var is_sf = (agt.indexOf("safari") != -1);
var is_kq = (agt.indexOf("konqueror") != -1);

document.write('<script type="text/javascript" src="script/prototype162.js"></script>');
document.write('<script type="text/javascript" src="script/scriptaculous18/scriptaculous.js?load=effects,controls,builder"></script>');

// load common stuff, like small shared functions + the late load object
document.write('<script type="text/javascript" src="script/common.js"></script>');

var req = new String(location.pathname).toLowerCase();
var loc = new String(location);

// Needed for the dynamic tabs on personal startpage, only 1KB:
document.write('<script type="text/javascript" src="script/fabtabulous.js"></script>');

if (req.indexOf('signup') != -1) {
	document.write('<script type="text/javascript" src="script/registerrox.js"></script>');
    document.write('<script type="text/javascript" src="script/geo_suggest.js"></script>');
}
if (req.indexOf('blog/create') != -1 || req.indexOf('blog') != -1 || req.indexOf('forums') != -1 || req.indexOf('trip') != -1 ) {
    	document.write('<script type="text/javascript" src="script/tiny_mce/tiny_mce.js"></script>');
}
if (req.indexOf('blog/create') != -1 || req.indexOf('blog') != -1 || req.indexOf('trip') != -1 ) {
        document.write('<script type="text/javascript" src="script/blog_suggest.js"></script>');
		document.write('<script type="text/javascript" src="script/scriptaculous18/scriptaculous.js?load=dragdrop"></script>');
		document.write('<script type="text/javascript" src="script/datepicker.js"></script>');
}
if (
		req.indexOf('blog/create') != -1
		|| req.indexOf('blog/edit') != -1
		|| req.indexOf('user/settings') != -1
		|| req.indexOf('trip/create') != -1
		|| req.indexOf('trip/edit') != -1
		|| req.indexOf('gallery/show/image') != -1
		|| req.indexOf('message/write') != -1
		|| req.indexOf('editmyprofile') != -1
	) {
	document.write('<script type="text/javascript" src="script/fieldset.js"></script>');
}
if (req.indexOf('gallery') != -1 || req.indexOf('tour/meet') != -1) {
	document.write('<script type="text/javascript" src="script/lightview.js"></script>');
}
if (req.indexOf('gallery/upload') != -1) {
	document.write('<script type="text/javascript" src="script/uploader.js"></script>');
	document.write('<script type="text/javascript" src="script/gallery.js"></script>');
}
if (req.indexOf('thepeople') != -1) {
	document.write('<script type="text/javascript" src="script/transition.js"></script>');
}
if (req.indexOf('searchmembers') != -1) {
	document.write('<script type="text/javascript" src="script/prototip.js"></script>');
    if (req.indexOf('searchmembers/quicksearch') == -1)
        document.write('<script type="text/javascript" src="script/labeled_marker.js"></script>');
}
if (req.indexOf('explore') != -1 || req.indexOf('about') != -1) {
	document.write(' <!--[if IE 6]><script type="text/javascript" src="script/shop.js"></script><![endif]--> ');
}
