var Uploader = Class.create();
Uploader.prototype = {
	form: false,
	parameters: false,
		
	initialize: function(form, parameters) {
		if (!$(form))
			return false;
		this.form = form;
		this.parameters = {
			iframeAfter:this.form,
			hideOnUpload:true,
			getter: form+'-getter',
			oncomplete: this.uploaded,
			notify_heading: 'h2'
		};
		Object.extend(this.parameters, parameters || {});
		if (!$(this.parameters.getter)) {
			var htmlSrc = new String;
			htmlSrc += '<iframe id="'+this.parameters.getter+'" name="'+this.parameters.getter+'" class="hidden"></iframe>';
			new Insertion.After(this.parameters.iframeAfter, htmlSrc);
		}
		$(this.form).target = this.parameters.getter;
		Event.observe(this.form, 'submit', this.upload.bindAsEventListener(this), true);
	},
	
	upload: function() {
		var htmlSrc = new String;
		if (this.parameters.submit_title || this.parameters.submit_text) {
			htmlSrc += '<div class="notfiybox">';
			htmlSrc += '<img src="images/misc/loading.gif" alt="loading..."/>';
			if (this.parameters.submit_title)
				htmlSrc += '<'+this.parameters.notify_heading+'>'+this.parameters.submit_title+'</'+this.parameters.notify_heading+'>';
			if (this.parameters.submit_text)
				htmlSrc += '<p>'+this.parameters.submit_text+'</p>';
			htmlSrc += '</div>';
		}
		new Insertion.Before(this.form, htmlSrc);
		if (this.parameters.hideOnUpload)
			Element.hide(this.form);
		Event.observe(this.parameters.getter, 'load', this.parameters.oncomplete.bind(this));
		return true;
	},
	
	uploaded: function() {
		var htmlSrc = new String;
		if (this.parameters.finfish_title || this.parameters.finish_text) {
			htmlSrc += '<div class="notfiybox">';
			htmlSrc += '<img src="images/misc/loading.gif" alt="loading..."/>';
			if (this.parameters.submit_title)
				htmlSrc += '<'+this.parameters.notify_heading+'>'+this.parameters.submit_title+'</'+this.parameters.notify_heading+'>';
			if (this.parameters.submit_text)
				htmlSrc += '<p>'+this.parameters.submit_text+'</p>';
			htmlSrc += '</div>';
		}
		new Insertion.Before(this.form, htmlSrc);
		Element.remove(this.form)
	}
}