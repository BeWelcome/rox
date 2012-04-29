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
        }
      }
    } else {
      this.includeScript(script.file);
    }
  }
};

/**
 * Select scripts to include on current page.
 * @param {string} file Name of script file, relative to scripts folder.
 */
BWRox.prototype.includeScript = function(file) {
  document.write('<script type="text/javascript" src="script/' + file
    + '"></script>');
};

/*
 * Creating "bwrox" singleton for global use.
 */
var bwrox = new BWRox;

/*
 * Including JavaScript files, depending on current URL.
 * Extend selectScripts() first parameter array to load more files.
 */
bwrox.selectScripts([
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
    file: "geo_suggest.js",
    pages: [
      "signup"
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
    file: "labeled_marker.js",
    pages: [
      "searchmembers/quicksearch"
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
  }
]);
