/*
 * Setting helper globals
 */
var agt = navigator.userAgent.toLowerCase();

var is_op = (agt.indexOf("opera") != -1);
var is_ie = (agt.indexOf("msie") != -1) && document.all && !is_op;
var is_ie5 = (agt.indexOf("msie 5") != -1) && document.all && !is_op;
var is_mac = (agt.indexOf("mac") != -1);
var is_gk = (agt.indexOf("gecko") != -1);
var is_sf = (agt.indexOf("safari") != -1);
var is_kq = (agt.indexOf("konqueror") != -1);

var http_baseuri = new String(document.getElementById('baseuri').href);
var req = new String(location.pathname).toLowerCase();
var loc = new String(location);

/**
 * Creating "BWRox" namespace.
 */
function BWRox() {};

/**
 * Select scripts to include on current page.
 * @param {array} scripts Object list with available scripts, object properties:
 *     - file: Script file name, relative to scripts folder
 *     - pages: Array with names of pages the script should be included on.
 *         Matches if request path starts with given page name (pattern:
 *         "^name.*"). If pages property is omitted the script will be included
 *         on every page.
 */
BWRox.prototype.selectScripts = function(scripts) {
  for (var i = 0; i < scripts.length; i++) {
    var script = scripts[i];

    // Loop through pages array, if it exists
    if (typeof(script.pages) == 'object' && script.pages.length > 0) {
      for (var j = 0; j < script.pages.length; j++) {
        // Trim leading slash
        var currentPage = req.substring(1, req.length);

        // Include script if path starts with page name
        if (currentPage.indexOf(script.pages[j]) == 0) {
          this.includeScript(script.file);
          this.includeStyle(script.style);
          this.includeStyleIE8(script.styleIE8);
        }
      }
    } else {
      this.includeScript(script.file);
      this.includeStyle(script.style);
      this.includeStyleIE8(script.styleIE8);
    }
  }
};

/**
 * Select script to include on current page.
 * @param {string} file Name of css file, relative to scripts folder or absolute.
 */
BWRox.prototype.includeScript = function(file) {
  if (file){
    var src;
    if (file.substring(0,4) == 'http') {
      src = file;
    } else {
      src = 'script/' + file;
    }
    document.write('<script type="text/javascript" src="' + src + '"></script>');
  }
};

/**
 * Select style to include on current page for IE8 only.
 * @param {string} file Name of script file, relative to styles folder or absolute.
 */
BWRox.prototype.includeStyleIE8 = function(file) {
  if (file){
    document.write('<!--[if lte IE 8]>');
    this.includeStyle(file);
    document.write('<![endif]-->');
  }
};

/**
 * Select style to include on current page.
 * @param {string} file Name of script file, relative to styles folder or absolute.
 */
BWRox.prototype.includeStyle = function(file) {
  if (file){
    var src;
    if (file.substring(0,4) == 'http') {
      src = file;
    } else {
      src = 'styles/' + file;
    }
    document.write('<link media="all" type="text/css" href="' + src + '" rel="stylesheet">');
  }
};

/*
 * Creating "bwrox" singleton for global use.
 */
var bwrox = new BWRox;

/*
 * Including JavaScript files, depending on current URL.
 * Extend selectScripts() first parameter array to load more files.
 *
 *   Note: Add or increment query string if a JS file changes to make sure
 *         browsers reload the file (e.g. "gallery.js?1" -> "gallery.js?2")
 */
bwrox.selectScripts([
  {
    //JQuery has to be defined before prototype to avoid conflicts
    file: "http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js",
    pages: ["searchmembers", "signup/3", "setlocation", "blog", "trip"]
  },
  {
    file: "3rdparty/leaflet/0.4.4/leaflet.js",
    style: "css/3rdparty/leaflet/0.4.4/leaflet.css",
    styleIE8: "css/3rdparty/leaflet/0.4.4/leaflet.ie.css",
    pages: ["searchmembers", "signup/3", "setlocation", "blog", "trip"]
  },
  {
    file: "http://maps.googleapis.com/maps/api/js?sensor=false",
    pages: ["searchmembers", "signup/3", "setlocation", "blog", "trip"]
  },
  {
    file: "3rdparty/leaflet/plugins/Google.js",
    pages: ["searchmembers", "signup/3", "setlocation", "blog", "trip"]
  },
  {
    file: "prototype162.js"
  },
  {
    file: "fabtabulous.js"
  },
  {
    file: "scriptaculous18/scriptaculous.js?load=effects,controls,builder,dragdrop"
  },
  {
    file: "registerrox.js",
    pages: [
      "signup"
    ]
  },
  {
    file: "geo_suggest.js?1",
    pages: [
      "signup", "setlocation"
    ]
  },
  {
    file: "tiny_mce/tiny_mce.js",
    pages: [
      "blog",
      "forums",
      "trip"
    ]
  },
  {
    file: "blog_suggest.js",
    pages: [
      "blog",
      "trip"
    ]
  },
  {
    file: "datepicker.js",
    pages: [
      "blog",
      "trip"
    ]
  },
  {
    file: "fieldset.js",
    pages: [
      "blog/create",
      "blog/edit",
      "user/settings",
      "trip/create",
      "trip/edit",
      "gallery/show/image",
      "message/write",
      "editmyprofile"
    ]
  },
  {
    file: "gallery.js",
    pages: [
      "blog/create",
      "editmyprofile"
    ]
  },
  {
    file: "uploader.js",
    pages: [
      "blog/create",
      "editmyprofile"
    ]
  },
  {
    file: "lightview.js",
    pages: [
      "gallery",
      "tour/meet"
    ]
  },
  {
    file: "transition.js",
    pages: [
      "thepeople"
    ]
  },
  {
    file: "prototip.js",
    pages: [
      "searchmembers"
    ]
  },
  {
    file: "fancyzoom.js",
    pages: [
      "members",
      "editmyprofile",
      "mypreferences",
      "myvisitors",
      "deleteprofile",
      "people"
    ]
  },
  {
    file: "util/console.js"
  },
  {
    file: "map/geolocation/BWGoogleMapReverseGeolocator.js",
    pages: ["searchmembers", "signup/3", "setlocation", "blog", "trip"]
  },
  {
    file: "map/leaflet/LeafletFlagIcon.js",
    pages: ["signup/3", "setlocation", "blog", "trip"]
  },
  {
    file: "map/builder/BWSimpleMapBuilder.js",
    pages: ["signup/3", "setlocation", "blog", "trip"]
  },
  {
    file: "map/builder/BWGeosearchMapBuilder.js",
    pages: ["searchmembers"]
  },
  {
    file: "map/BWMapMaths.js",
    pages: ["searchmembers", "signup/3", "setlocation", "blog", "trip"]
  },
  {
    file: "map/BWMapAddressPoint.js",
    pages: ["searchmembers", "signup/3", "setlocation", "blog", "trip"]
  },
  {
    file: "map/BWMapHostPoint.js",
    pages: ["searchmembers"]
  },
  {
    file: "map/BWMapSearchResult.js",
    pages: ["searchmembers"]
  },
  {
    file: "map/small/smallMapGeoLocation.js",
    pages: ["signup/3", "setlocation"]
  },
  {
    file: "map/small/blogSmallMapGeoLocation.js",
    pages: ["blog", "trip"]
  },
  {
    file: "map/small/blogMap.js",
    pages: ["blog"]
  },
  {
    file: "map/small/singlePost.js",
    pages: ["blog"]
  },
  {
    file: "map/small/tripMap.js",
    pages: ["trip"]
  },
  {
    file: "searchmembers.js?1",
    pages: ["searchmembers"]
  }
]);
