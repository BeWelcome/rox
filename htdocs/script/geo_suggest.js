var GeoSuggest = {
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
            // v.tags = GeoSuggest.tags.bind(GeoSuggest);
            v.locations = GeoSuggest.locations.bind(GeoSuggest);
            // Event.observe(v, 'keyup', function(ev) {Event.element(ev).tags(Event.element(ev));});
            Event.observe(v, 'keydown', function(ev) {Event.element(ev).locations(Event.element(ev), ev);});
            n.push(v);
            return n;
        });
        $('btn-create-location').onclick = function() {
            GeoSuggest.ajaxSearch($('create-location'));
            return false;
        };
    },
    
    locations: function(e, event) {
        if (e.name == 'create-location') {
            if (event && event.keyCode && event.keyCode == Event.KEY_RETURN) {
                this.ajaxSearch(e);
                Event.stop(event);
            }
        }
    },
    
    ajaxSearch: function(e) {
        var address = $F(e);
        
     // init geolocator
//    	var reverseGeolocator = new BWGoogleMapReverseGeolocator();
//        
//    	GeoSuggest.displaySuggestion('location-status', '<img src="images/misc/loading.gif">');
//    	reverseGeolocator.getLocation(address, function(addressPoint) {
//    		GeoSuggest.displaySuggestion('location-suggestion', addressPoint.location);
//    		GeoSuggest.displaySuggestion('location-status', '');
//    	}, function() {
//    		// address not fount
//    		bwrox.error('Address "%s" not fount.', address);
//    	});
    	
        var url = http_baseuri+'geo/suggestLocation/'+address+'/city';
        GeoSuggest.displaySuggestion('location-status', '<img src="images/misc/loading.gif">');
        new Ajax.Request(url, 
        {
            method:'get', 
            onSuccess: function(req) {
                GeoSuggest.displaySuggestion('location-suggestion', req.responseText);
                GeoSuggest.displaySuggestion('location-status', '');
            }
        });
    },

    displaySuggestion: function(suggestionId, suggestion) {
        Element.update(suggestionId, suggestion);
    }

}
