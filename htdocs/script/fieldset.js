var FieldsetMenu = Class.create();
Object.extend(FieldsetMenu.prototype, {
	initialize: function(id, options) {
		this.id = id;
		this.options = {
			active: false,
			onclick: false
		}
		Object.extend(this.options, options || {});
		if (this.id) {
			this.element = $(this.id);
		}
		this.fieldsets = $A(document.getElementsByTagName('fieldset', this.id));
		this.fieldsets = this.fieldsets.findAll(function(e) {
			return e.getElementsByTagName('legend').length;
		});
		if (this.fieldsets.length == 0)
			return false;
		this.setIt = this.set.bind(this);
		this.fieldsets.each(this.setIt);
		var htmlSrc = this.getMenuHTML();
		new Insertion.Before(this.fieldsets[0], htmlSrc);
		this.fieldsets.each(function(e) {
			Event.observe('a'+e.id, 'click', e.eventClickMenu);
		});
		if (this.options.active) {
			this.toggleDo(this.options.active);
		}
	},
	
	getMenuHTML: function() {
		var htmlSrc = new String;
		htmlSrc += '<ul class="fieldset-menu">';
		this.fieldsets.each(function(e) {
			var l = e.getElementsByTagName('legend');
			htmlSrc += '<li id="li'+e.id+'"><a href="#" id="a'+e.id+'" onclick="return false;">';
			htmlSrc += l[0].innerHTML;
			htmlSrc += '</a></li>';
		});
		htmlSrc += '</ul>';
		return htmlSrc;
	},
	
	hideAll: function(except) {
		this.fieldsets.each(function(e) {
			if (except && e.id == except)
				return true;
			Element.hide(e);
		});
	},
	
	set: function(e) {
		Element.hide(e);
		e.eventClickMenu = this.toggle.bindAsEventListener(this);
	},
	
	setMenuActive: function() {
		this.fieldsets.each(function(e) {
			if (Element.visible(e)) {
				Element.addClassName('li'+e.id, 'active');
			} else {
				Element.removeClassName('li'+e.id, 'active');
			}
		});
	},
	
	toggle: function(ev) {
		var id = new String(Event.element(ev).id);
		id = id.substring(1);
		if (this.options.onclick) {
			this.options.onclick(id);
		}
		this.toggleDo(id);
	},
	
	toggleDo: function(id) {
		if (!$(id))
			return false;
		this.hideAll(id);
		Element.show(id);
		this.setMenuActive();
	}
});

/*
 * update fieldsets
 */
function createFieldsetMenu() 
{
	var fieldsets = $A(document.getElementsByTagName('fieldset'));
	// filtering
	// fieldsets must have legend tag
	fieldsets = fieldsets.findAll(function(e) {
		return e.getElementsByTagName('legend').length;
	});
	fieldsets.each(function(e) {
		Element.hide(e);
	});
	var htmlSrc = new String;
	htmlSrc += '<ul class="fieldset-menu">';
	fieldsets.each(function(e) {
		var l = e.getElementsByTagName('legend');
		htmlSrc += '<li id="li_'+e.id+'"><a href="#" onclick="setFieldsetMenu(\''+e.id+'\');return false;">';
		htmlSrc += l[0].innerHTML;
		htmlSrc += '</a></li>';
	});
	htmlSrc += '</ul>';
	new Insertion.Before(fieldsets[0], htmlSrc);
}
function setFieldsetMenu(e) {
	var fieldsets = $A(document.getElementsByTagName('fieldset')).findAll(function(e) {return e.getElementsByTagName('legend').length;});
	var show = !Element.visible(e);
	fieldsets.each(function(el) {
		Element.hide(el);
        Element.removeClassName('li_'+el.id, 'fielset-menu-active');
	});
	if (show) {
		Element.show(e);
        Element.addClassName('li_'+e, 'fielset-menu-active');
	}
	return false;
}
