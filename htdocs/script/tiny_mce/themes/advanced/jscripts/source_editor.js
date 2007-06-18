function saveContent() {
	tinyMCE.setContent(document.getElementById('htmlSource').value);
	tinyMCE.closeWindow(window);
}

// Fixes some charcode issues
function fixContent(html) {
/*	html = html.replace(new RegExp('<(p|hr|table|tr|td|ol|ul|object|embed|li|blockquote)', 'gi'),'\n<$1');
	html = html.replace(new RegExp('<\/(p|ol|ul|li|table|tr|td|blockquote|object)>', 'gi'),'</$1>\n');
	html = tinyMCE.regexpReplace(html, '<br />','<br />\n','gi');
	html = tinyMCE.regexpReplace(html, '\n\n','\n','gi');*/
	return html;
}

function onLoadInit() {
	tinyMCEPopup.resizeToInnerSize();

	document.forms[0].htmlSource.value = fixContent(tinyMCE.getContent(tinyMCE.getWindowArg('editor_id')));
	resizeInputs();

	if (tinyMCE.getParam("theme_advanced_source_editor_wrap", true)) {
		setWrap('soft');
		document.forms[0].wraped.checked = true;
	}
}

function setWrap(val) {
	var s = document.forms[0].htmlSource;

	s.wrap = val;

	if (tinyMCE.isGecko) {
		var v = s.value;
		var n = s.cloneNode(false);
		n.setAttribute("wrap", val);
		s.parentNode.replaceChild(n, s);
		n.value = v;
	}
}

function toggleWordWrap(elm) {
	if (elm.checked)
		setWrap('soft');
	else
		setWrap('off');
}

var wHeight=0, wWidth=0, owHeight=0, owWidth=0;

function resizeInputs() {
	if (!tinyMCE.isMSIE) {
		 wHeight = self.innerHeight-80;
		 wWidth = self.innerWidth-16;
	} else {
		 wHeight = document.body.clientHeight - 80;
		 wWidth = document.body.clientWidth - 16;
	}

	document.forms[0].htmlSource.style.height = Math.abs(wHeight) + 'px';
	document.forms[0].htmlSource.style.width  = Math.abs(wWidth) + 'px';
}

function renderWordWrap() {
	if (tinyMCE.isMSIE || tinyMCE.isGecko)
		document.write('<input type="checkbox" name="wraped" id="wraped" onclick="toggleWordWrap(this);" class="wordWrapCode" /><label for="wraped">{$lang_theme_code_wordwrap}</label>');
}
