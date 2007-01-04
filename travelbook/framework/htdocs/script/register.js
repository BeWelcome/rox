var Register = {
	form: false,
	elements: false,
	submits: false,
	
	initialize: function(form) {
		if (!$(form) || !$(form).id)
			throw 'specified form not found!';
		this.form = $(form);
		this.submits = $A(Form.getInputs(this.form, 'submit'));
		if (!is_op) {
			this.submits.each(function(e) {
				e.disabled = 'disabled';
			});
		}
		var elements = $A(Form.getElements(this.form));
		this.elements = elements.findAll(function(e) {return e.id;});
		this.elements = this.elements.inject([], function(n, v) {
			v.check = Register.check.bind(Register);
			v.checked = false;
			v.check(v);
			Event.observe(v, 'keyup', function(ev) {Event.element(ev).check(Event.element(ev));});
//			new Form.Element.EventObserver(v, function(ev) {
//				var e = Event.element(ev);
//				if (!e)
//					return false;
//				e.check(e);
//			});
			n.push(v);
			return n;
		});
	},
	
	check: function(e) {
		switch(e.name) {
		case 'u':
			if (!$F(e).match(/^[a-z][a-z0-9_\-.]{3,}$/i)) {
				Register.setError(e);
			} else {
				Register.setError(e);
				var url = http_baseuri+'user/checkhandle/'+$F(e);
				new Ajax.Request(url, 
				{
					method:'get', 
					onSuccess: function(req) {
						if (req.responseText == '1') { 
							Register.setClear(Register.elements.detect( function(e) { return (e.name == 'u'); } ));
						}
					}
				});
			}
			break;
		case 'e':
			if (!$F(e).match(/^(([A-Za-z0-9]+_+)|([A-Za-z0-9]+\-+)|([A-Za-z0-9]+\.+)|([A-Za-z0-9]+\++))*[A-Za-z0-9]+@((\w+\-+)|(\w+\.))*\w{1,63}\.[a-zA-Z]{2,6}$/)) {
				Register.setError(e);
			} else {
				Register.setError(e);
				var url = http_baseuri+'user/checkemail';
				new Ajax.Request(url, 
				{
					method:'get', 
					parameters:Form.Element.serialize(e),
					onSuccess: function(req) {
						if (req.responseText == '1') { 
							Register.setClear(Register.elements.detect( function(e) { return (e.name == 'e'); } ));
						}
					}
				});
			}
			break;
		case 'p':
		case 'pc':
			if ($F(e).length < 8) {
				Register.setError(e);
				break;
			}
			var p = this.elements.detect(function(e) {return (e.name == 'p');}); 
			var pc = this.elements.detect(function(e) {return (e.name == 'pc');});
			if ($F(p) != $F(pc)) {
				Register.setError(e);
				break;
			}
			this.setClear(p);
			this.setClear(pc);
			break;
		}
	},
	
	checkHandle: function(ev) {
		var h = Event.element(ev);
	},
	
	checkPassword: function(ev) {
		var p = Event.element(ev);
		if ($F(p).length < 8) {
			Register.setError(p);
			return false;
		}
	},
	
	insButton: function(e, img, alt) {
		if (!$(e).id)
			return false;
		if (!alt)
			alt = 'button';
		var imgId = 'b'+$(e).id;
		if (!$(imgId)) {
			new Insertion.After(e, '<div id="'+imgId+'" class="statbtn"></div>');
		}
		var htmlSrc = '<img src="'+http_baseuri+img+'" alt="'+alt+'"/>';
		Element.update(imgId, htmlSrc);
		return true;
	},
	
	setError: function(e) {
		if (!$(e))
			return false;
		$(e).checked = false;
		this.insButton(e, 'images/btns/rdot16x16.png', 'problem');
		if (!is_op) {
			var errors = this.elements.findAll(function(e) {return (!e.checked)});
			if (errors.length > 0) {
				this.submits.each(function(e) {e.disabled = 'disabled'});
			} else {
				this.submits.each(function(e) {e.disabled = ''});
			}
		}
	},
	
	setClear: function(e) {
		if (!$(e))
			return false;
		$(e).checked = true;
		this.insButton(e, 'images/btns/gdot16x16.png', 'ok');
		var errors = this.elements.findAll(function(e) {return (!e.checked)});
		if (!is_op) {
			if (errors.length > 0) {
				this.submits.each(function(e) {e.disabled = 'disabled'});
			} else {
				this.submits.each(function(e) {e.disabled = ''});
			}
		}
	}
}
