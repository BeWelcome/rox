/*
 * main.js
 *
 * Sets helper globals, creates BWRox namespace, creates bwrox singleton
 * and includes scripts based on current URL.
 *
 * Note: If you make changes here make sure to increment query string in array
 *       $_early_scriptfiles in <rox>/tools/page/html.page.php so browsers
 *       reload main.js
 */

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
function BWRox() {
};

/**
 * Select scripts to include on current page.
 * @param {array} scripts Object list with available scripts, object properties:
 *     - file: Script file name, relative to scripts folder
 *     - pages: Array with names of pages the script should be included on.
 *         Matches if request path starts with given page name (pattern:
 *         "^name.*"). If pages property is omitted the script will be included
 *         on every page.
 */
BWRox.prototype.selectScripts = function (scripts) {
    for (var i = 0; i < scripts.length; i++) {
        var script = scripts[i];

        // No script prefix if remote location
        if (typeof script.remote === "boolean" && script.remote === true) {
            var prefix = '';
        } else {
            var prefix = 'script/';
        }
        // add min.js if we have a min version
        if (typeof script.min === "boolean" && script.min === true && typeof bwroxConfig != 'undefined' && bwroxConfig.uncompress_js == '1') {
            script.file = script.file.replace('.js', '.min.js');
        } else {
            script.file;
        }
        var src = prefix + script.file;

        // Loop through pages array, if it exists
        if (typeof(script.pages) == 'object' && script.pages.length > 0) {
            for (var j = 0; j < script.pages.length; j++) {
                // Trim leading slash
                var currentPage = req.substring(1, req.length);

                // Include script if path starts with page name
                if (currentPage.indexOf(script.pages[j]) == 0) {
                    this.includeScript(src);
                }
            }
        } else {
            this.includeScript(src);
        }
    }
};

/**
 * Include a JavaScript file.
 * @param {string} src Location of script file.
 */
BWRox.prototype.includeScript = function (src) {
    document.write('<script type="text/javascript" src="' + src + '"></script>');
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
 *         browsers reload the file (e.g. "gallery.js?2" -> "gallery.js?3")
 */
bwrox.selectScripts([
    {
        // complete jquery ui with theme smoothness
        file: "jquery-ui-1.11.2/jquery-ui.min.js",
        pages: [
            "search",
            "admin/rights",
            "admin/flags",
        ]
    },
    {
        file: "jquery-hashchange-1.4.min.js",
        pages: [
            "about/faq",
            "faq"
        ]
    },
    {
        file: "leaflet/1.0.0-master/leaflet.js",
        pages: [
            "activities"
        ]
    },
    {
        file: "leaflet.markercluster/leaflet.markercluster.js",
        pages: [
            "activities"
        ]
    },
    {
        file: "act_suggest.js?1",
        pages: [
            "activities"
        ]
    },
    {
        file: "adminrightstooltip.js",
        pages: [
            "admin/rights/list"
        ]
    },
    {
        file: "fieldset.js?1",
        pages: [
            "admin/massmail",
        ]
    },
    {
        file: "util/console.js?1"
    },
    {
        file: "map/activities/activities_map.js?4",
        pages: ["activities"]
    }
]);

