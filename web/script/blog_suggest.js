var BlogSuggest = {
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
			v.tags = BlogSuggest.tags.bind(BlogSuggest);
			v.locations = BlogSuggest.locations.bind(BlogSuggest);
			Event.observe(v, 'keyup', function(ev) {Event.element(ev).tags(Event.element(ev));});
			Event.observe(v, 'keydown', function(ev) {Event.element(ev).locations(Event.element(ev), ev);});
			n.push(v);
			return n;
		});
		$('btn-create-location').onclick = function() {
			BlogSuggest.ajaxSearch($('create-location'));
		};
	},

	tags: function(e) {
		if (e.name == 'tags') {
			var textValue = $F(e);
			textValue = textValue.replace(/\n/g, ', ');
			var url = http_baseuri+'blog/suggestTags/'+textValue;
			new Ajax.Request(url, 
			{
				method:'get', 
				onSuccess: function(req) {
					BlogSuggest.displaySuggestion('suggestion', req.responseText);
				}
			});
		}
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
		var textValue = $F(e);
		var url = http_baseuri+'geo/suggestLocation/'+textValue+'/blog';
		new Ajax.Request(url, 
		{
			method:'get', 
			onSuccess: function(req) {
				BlogSuggest.displaySuggestion('location-suggestion', req.responseText);
			}
		});
	},

	displaySuggestion: function(suggestionId, suggestion) {
		Element.update(suggestionId, suggestion);
	},

	updateForm: function(text) {
		var tagForm = document.getElementById('create-tags');
		tagForm.value = text + ', ';
		tagForm.focus();
	}
}
