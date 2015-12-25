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
            "activities",
            "search",
            "admin/rights",
            "admin/flags",
            /*,
             "blog",
             "trip",
             "admin/treasurer" */
        ]
    },
    {
        file: "jquery-ui-timepicker-addon.js?2",
        pages: [
            "activities"
            /*,
             "blog",
             "trip",
             "admin/treasurer" */
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
        file: "jquery.multiselect.min.js",
        pages: [
            "search"
        ]
    },
    {
        file: "leaflet/0.7.3/leaflet.js",
        pages: [
            "searchmembers",
            "blog",
            "activities"
        ]
    },
    {
        file: "leaflet/plugins/Leaflet.markercluster/0.4.0/leaflet.markercluster.js",
        pages: [
            "activities"
        ]
    },
    {
        file: "leaflet/plugins/Leaflet.markercluster/0.4.0/leaflet.markercluster.include-css.js",
        pages: ["activities"]
    },
    {
        file: "//maps.googleapis.com/maps/api/js?sensor=false",
        remote: true,
        pages: [
            "searchmembers",
            "blog"
        ]
    },
    {
        file: "leaflet/plugins/shramov-leaflet-plugins/1.1.0/layer/tile/Google.js",
        pages: [
            "searchmembers",
            "blog"
        ]
    },
    {
        file: "tinymce-4.3.2/tinymce.min.js",
        pages: [
            "activities",
            "blog",
            "forums",
            "groups",
            "suggestions",
//            "trip",
            "messages/",
            "invite"
        ]
    },
    {
        file: "blog_suggest.js?1",
        pages: [
            "blog",
//            "trip"
        ]
    },
    {
        file: "act_suggest.js?1",
        pages: [
            "activities"
        ]
    },
    {
        file: "datepicker.js?1",
        pages: [
//            "activities",
            "blog",
//            "trip",
            "admin/treasurer"
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
            "blog/create",
            "blog/edit",
            "user/settings",
//            "trip/create",
//            "trip/edit",
//            "trip",
            "gallery/show/image",
            "message/write",
            "editmyprofile",
            "admin/massmail",
            "members/"
        ]
    },
    {
        file: "gallery.js?1",
        pages: [
            "blog/create",
            "editmyprofile"
        ]
    },
    {
        file: "uploader.js?1",
        pages: [
            "blog/create",
            "editmyprofile"
        ]
    },
    {
        file: "lightview.js?1",
        pages: [
            "gallery",
            "tour/meet"
        ]
    },
    {
        file: "transition.js?1",
        pages: [
            "thepeople"
        ]
    },
    {
        file: "prototip.js?2",
        pages: [
            "searchmembers"
        ]
    },
    {
        file: "fancyzoom.js?1",
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
        file: "util/console.js?1"
    },
    {
        file: "map/geolocation/BWGoogleMapReverseGeolocator.js?2",
        pages: [
            "searchmembers",
            "blog",
            //    "trip"
        ]
    },
    {
        file: "map/leaflet/LeafletFlagIcon.js?1",
        pages: [
            "blog"
        ]
    },
    {
        file: "map/builder/BWSimpleMapBuilder.js?4",
        pages: [
            "blog"
        ]
    },
    {
        file: "map/builder/BWGeosearchMapBuilder.js?4",
        pages: ["searchmembers"]
    },
    {
        file: "map/BWMapMaths.js?1",
        pages: [
            "searchmembers",
            "blog",
            //    "trip"
        ]
    },
    {
        file: "map/BWMapAddressPoint.js?1",
        pages: [
            "searchmembers",
            "blog",
            //    "trip"
        ]
    },
    {
        file: "map/BWMapHostPoint.js?1",
        pages: ["searchmembers"]
    },
    {
        file: "map/BWMapSearchResult.js?1",
        pages: ["searchmembers"]
    },
    {
        file: "map/small/blogSmallMapGeoLocation.js?4",
        pages: [
            "blog"
            //, "trip"
        ]
    },
    {
        file: "map/small/blogMap.js?4",
        pages: ["blog"]
    },
    {
        file: "map/small/singlePost.js?4",
        pages: ["blog"]
    },
    {
        file: "searchmembers.js?4",
        pages: ["searchmembers"]
    },
    {
        file: "map/activities/activities_map.js?4",
        pages: ["activities"]
    }
]);

