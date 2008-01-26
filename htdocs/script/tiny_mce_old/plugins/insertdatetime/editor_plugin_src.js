/**
 * $RCSfile: editor_plugin_src.js,v $
 * $Revision: 1.22 $
 * $Date: 2006/02/10 16:29:39 $
 *
 * @author Moxiecode
 * @copyright Copyright � 2004-2006, Moxiecode Systems AB, All rights reserved.
 */

/* Import plugin specific language pack */
tinyMCE.importPluginLanguagePack('insertdatetime', 'en,tr,cs,el,fr_ca,it,ko,sv,zh_cn,fa,fr,de,pl,pt_br,nl,da,he,nb,ru,ru_KOI8-R,ru_UTF-8,nn,fi,es,cy,is,pl');

var TinyMCE_InsertDateTimePlugin = {
	getInfo : function() {
		return {
			longname : 'Insert date/time',
			author : 'Moxiecode Systems',
			authorurl : 'http://tinymce.moxiecode.com',
			infourl : 'http://tinymce.moxiecode.com/tinymce/docs/plugin_insertdatetime.html',
			version : tinyMCE.majorVersion + "." + tinyMCE.minorVersion
		};
	},

	/**
	 * Returns the HTML contents of the insertdate, inserttime controls.
	 */
	getControlHTML : function(cn) {
		switch (cn) {
			case "insertdate":
				return tinyMCE.getButtonHTML(cn, 'lang_insertdate_desc', '{$pluginurl}/images/insertdate.gif', 'mceInsertDate');

			case "inserttime":
				return tinyMCE.getButtonHTML(cn, 'lang_inserttime_desc', '{$pluginurl}/images/inserttime.gif', 'mceInsertTime');
		}

		return "";
	},

	/**
	 * Executes the mceInsertDate command.
	 */
	execCommand : function(editor_id, element, command, user_interface, value) {
		/* Adds zeros infront of value */
		function addZeros(value, len) {
			value = "" + value;

			if (value.length < len) {
				for (var i=0; i<(len-value.length); i++)
					value = "0" + value;
			}

			return value;
		}

		/* Returns the date object in the specified format */
		function getDateTime(date, format) {
			format = tinyMCE.regexpReplace(format, "%D", "%m/%d/%y");
			format = tinyMCE.regexpReplace(format, "%r", "%I:%M:%S %p");
			format = tinyMCE.regexpReplace(format, "%Y", "" + date.getFullYear());
			format = tinyMCE.regexpReplace(format, "%y", "" + date.getYear());
			format = tinyMCE.regexpReplace(format, "%m", addZeros(date.getMonth()+1, 2));
			format = tinyMCE.regexpReplace(format, "%d", addZeros(date.getDate(), 2));
			format = tinyMCE.regexpReplace(format, "%H", "" + addZeros(date.getHours(), 2));
			format = tinyMCE.regexpReplace(format, "%M", "" + addZeros(date.getMinutes(), 2));
			format = tinyMCE.regexpReplace(format, "%S", "" + addZeros(date.getSeconds(), 2));
			format = tinyMCE.regexpReplace(format, "%I", "" + ((date.getHours() + 11) % 12 + 1));
			format = tinyMCE.regexpReplace(format, "%p", "" + (date.getHours() < 12 ? "AM" : "PM"));
			format = tinyMCE.regexpReplace(format, "%B", "" + tinyMCE.getLang("lang_inserttime_months_long")[date.getMonth()]);
			format = tinyMCE.regexpReplace(format, "%b", "" + tinyMCE.getLang("lang_inserttime_months_short")[date.getMonth()]);
			format = tinyMCE.regexpReplace(format, "%A", "" + tinyMCE.getLang("lang_inserttime_day_long")[date.getDay()]);
			format = tinyMCE.regexpReplace(format, "%a", "" + tinyMCE.getLang("lang_inserttime_day_short")[date.getDay()]);
			format = tinyMCE.regexpReplace(format, "%%", "%");

			return format;
		}

		// Handle commands
		switch (command) {
			case "mceInsertDate":
				tinyMCE.execInstanceCommand(editor_id, 'mceInsertContent', false, getDateTime(new Date(), tinyMCE.getParam("plugin_insertdate_dateFormat", tinyMCE.getLang('lang_insertdate_def_fmt'))));
				return true;

			case "mceInsertTime":
				tinyMCE.execInstanceCommand(editor_id, 'mceInsertContent', false, getDateTime(new Date(), tinyMCE.getParam("plugin_insertdate_timeFormat", tinyMCE.getLang('lang_inserttime_def_fmt'))));
				return true;
		}

		// Pass to next handler in chain
		return false;
	}
};

tinyMCE.addPlugin("insertdatetime", TinyMCE_InsertDateTimePlugin);
