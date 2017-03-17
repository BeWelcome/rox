var Cal = {
	monthTarget: false,
	
	doUpdateMonth: function(req) {
		Element.update(Cal.monthTarget, req.responseText);
	},
	
	updateMonth: function(y, m, target) {
		if (!$(target))
			return false;
		this.monthTarget = target;
		var url = http_baseuri+'cal/'+y+m;
		new Ajax.Request(url, {method:'get',parameters:'raw=1',onSuccess:Cal.doUpdateMonth});
	},
		
	initMonthSelector: function(y, m, target) {
		if (!$(y) || !$(m) || !$(target))
			return false;
		
	},
	
	aCalTarget: function(y, m, d) {
		if ($(y))
			this.aCalTargetY = y;
		if ($(m))
			this.aCalTargetM = m;
		if ($(d))
			this.aCalTargetD = d;
	},
	
	aCalSet: function(y, m, d) {
		if (y && this.aCalTargetY && $(this.aCalTargetY))
			$(this.aCalTargetY).value = y;
		if (m && this.aCalTargetM && $(this.aCalTargetM))
			$(this.aCalTargetM).value = m;
		if (d && this.aCalTargetD && $(this.aCalTargetD))
			$(this.aCalTargetD).value = d;
		this.aCalRmv();
		if (y && this.aCalTargetY && $(this.aCalTargetY))
			$(this.aCalTargetY).focus();
		else if (m && this.aCalTargetM && $(this.aCalTargetM))
			$(this.aCalTargetM).focus();
		if (d && this.aCalTargetD && $(this.aCalTargetD))
			$(this.aCalTargetD).focus();
	},
	
	aCal: function(e, y, m) {
		var d = new Date;
		if (!y) {
			y = d.getFullYear();
		} else {
			d.setFullYear(y);
		}
		if (!m) {
			m = d.getMonth()+1;
		} else {
			d.setMonth(m);
		}
		m = new String(m);
		if (m.length == 1) {
			m = '0'+m;	
		}
		var url = http_baseuri+'cal/acal/'+y+m;
		if (!$('cal-acal')) {
			new Insertion.Bottom(document.body, '<div id="cal-acal" style="position:absolute;background:#FFF"></div>');
		}
		if (e)
			Position.clone(e, 'cal-acal');
		new Ajax.Updater('cal-acal', url, {method:'get', parameters:(this.pid ? '&pid='+this.pid : '')});
	},
	
	aCalUpdate: function(y, m) {
		var d = new Date;
		if (!y) {
			y = d.getFullYear();
		} else {
			d.setFullYear(y);
		}
		if (!m) {
			m = d.getMonth()+1;
		} else {
			d.setMonth(m);
		}
		m = new String(m);
		if (m.length == 1) {
			m = '0'+m;	
		}
		var url = http_baseuri+'cal/acal/'+y+m;
		new Ajax.Updater('cal-acal', url, {method:'get', parameters:(this.pid ? '&pid='+this.pid : '')});
	},
	
	aCalRmv: function() {
		if (!$('cal-acal'))
			return true;
		Element.remove('cal-acal');
	},
	
	setDateSE: function(sy, sm, sd, stf, ey, em, ed, etf) {
		if (!$(sy) || !$(sm) || !$(sd) || !$(ey) || !$(em) || !$(ed))
			return false;
		if ($(stf)) {
			var st = new String($F(stf));
			if (st.indexOf(':') > 0) {
				var sh = st.substr(0, st.indexOf(':'));
				var smin = st.substr(st.indexOf(':')+1);
			} else {
				var sh = st;
				var smin = 0;
			}
			var D = new Date;
			D.setHours(sh);
			D.setMinutes(smin);
			if (D)
				$(stf).value = D.getHours()+':'+'00';
			else
				$(stf).value = '00:00';
		} else {
			var sh = 0;
			var smin = 0;
		}

		if ($(etf)) {
			var et = new String($F(etf));
			if (et.indexOf(':') > 0) {
				var eh = et.substr(0, et.indexOf(':'));
				var emin = et.substr(et.indexOf(':')+1);
			} else {
				var eh = et;
				var emin = 0;
			}
			var D = new Date;
			D.setHours(eh);
			D.setMinutes(emin);
			if (D)
				$(etf).value = D.getHours()+':'+'00';
			else
				$(etf).value = '00:00';
		} else {
			var eh = 0;
			var emin = 0;
		}

		var StartDate = new Date($F(sy), $F(sm) - 1, $F(sd), sh, smin);
		StartDate = StartDate.getTime();
		var EndDate   = new Date($F(ey), $F(em) - 1, $F(ed), eh, emin);
		EndDate = EndDate.getTime();
		if (StartDate && EndDate && EndDate < StartDate) {
			$(ey).value = $F(sy);
			$(em).value = $F(sm);
			$(ed).value = $F(sd);
			if ($(etf) && $(stf)) {
				$(etf).value = $F(stf);
			}
		}
		return true;
	}
}