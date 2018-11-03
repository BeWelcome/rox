var ForumsSuggest = {
	form: false,
	elements: false,
	
	initialize: function() {
		var form = 'forumsform';
		if (!$(form) || !$(form).id) {
			throw 'specified form not found!';
		}
		this.form = $(form);
		var elements = $A(Form.getElements(this.form));
		this.elements = elements.findAll(function(e) {return e.id;});
		this.elements = this.elements.inject([], function(n, v) {
			v.tags = ForumsSuggest.tags.bind(BlogSuggest);
			Event.observe(v, 'keyup', function(ev) {Event.element(ev).tags(Event.element(ev));});
			n.push(v);
			return n;
		});
	},

	tags: function(e) {
		if (e.name == 'tags') {
			var textValue = $F(e);
			textValue = textValue.replace(/\n/g, ', ');
			var url = http_baseuri+'forums/suggestTags/'+textValue;
			new Ajax.Request(url, 
			{
				method:'get', 
				onSuccess: function(req) {
					ForumsSuggest.displaySuggestion('suggestion', req.responseText);
				}
			});
		}
	},

	displaySuggestion: function(suggestionId, suggestion) {
		Element.update(suggestionId, suggestion);
	},

	updateForm: function(text) {
		var tagForm = document.getElementById('create-tags');
		tagForm.value = text;
		tagForm.focus();
	}
}
