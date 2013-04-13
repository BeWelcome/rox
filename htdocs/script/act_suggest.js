var ActivityGeoSuggest = {
    form: false,
    elements: false,
    
    initialize: function(form) {
        if (!$(form) || !$(form).id) {
            throw 'specified form not found!';
        }
        this.form = $(form);
        var elements = $A(Form.getElements(this.form));
        this.elements = elements.findAll(function(e) {return e.id;});
        this.elements = this.elements.inject([], function(n, v) {
            v.locations = ActivityGeoSuggest.locations.bind(ActivityGeoSuggest);
            Event.observe(v, 'keydown', function(ev) {Event.element(ev).locations(Event.element(ev), ev);});
            n.push(v);
            return n;
        });
        $('activity-location-button').onclick = function() {
            ActivityGeoSuggest.ajaxSearch($('activity-location'));
            return false;
        };
    },
    
    locations: function(e, event) {
        if (e.name == 'activity-location') {
            if (event && event.keyCode && event.keyCode == Event.KEY_RETURN) {
                this.ajaxSearch(e);
                Event.stop(event);
            }
        }
    },
    
    ajaxSearch: function(e) {
        var address = $F(e);

        var url = http_baseuri+'geo/suggestLocation/'+address+'/city/activities';
        new Ajax.Request(url, 
        {
            method:'get', 
            onSuccess: function(req) {
              jQuery('#activity-location-suggestion').show();
              ActivityGeoSuggest.displaySuggestion('activity-location-suggestion', req.responseText);
            }
        });
    },

    displaySuggestion: function(suggestionId, suggestion) {
        Element.update(suggestionId, suggestion);
    }
}

/**
 * called when an user clicks on a result in the suggestion list.
 *
 */
function setActivityLocation(geonameid, latitude, longitude, zoom, geonamename, countryname, countrycode, admincode) {
    jQuery('#activity-location-id').val(geonameid);
    jQuery('#activity-location').val(decodeURI(geonamename) + ", " + decodeURI(countryname));
    jQuery('#activity-location-suggestion').hide();
}