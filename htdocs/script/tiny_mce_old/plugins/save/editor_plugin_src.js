/**
 * $RCSfile: editor_plugin_src.js,v $
 * $Revision: 1.26 $
 * $Date: 2006/02/13 15:09:28 $
 *
 * @author Moxiecode
 * @copyright Copyright � 2004-2006, Moxiecode Systems AB, All rights reserved.
 */

/* Import plugin specific language pack */
tinyMCE.importPluginLanguagePack('save', 'en,tr,sv,zh_cn,cs,fa,fr_ca,fr,de,pl,pt_br,nl,he,nb,hu,ru,ru_KOI8-R,ru_UTF-8,nn,fi,da,es,cy,is,zh_tw,zh_tw_utf8,sk');

var TinyMCE_SavePlugin = {
	getInfo : function() {
		return {
			longname : 'Save',
			author : 'Moxiecode Systems',
			authorurl : 'http://tinymce.moxiecode.com',
			infourl : 'http://tinymce.moxiecode.com/tinymce/docs/plugin_save.html',
			version : tinyMCE.majorVersion + "." + tinyMCE.minorVersion
		};
	},

	initInstance : function(inst) {
		inst.addShortcut('ctrl', 's', 'lang_save_desc', 'mceSave');
	},

	/**
	 * Returns the HTML contents of the save control.
	 */
	getControlHTML : function(cn) {
		switch (cn) {
			case "save":
				return tinyMCE.getButtonHTML(cn, 'lang_save_desc', '{$pluginurl}/images/save.gif', 'mceSave');
		}

		return "";
	},

	/**
	 * Executes the save command.
	 */
	execCommand : function(editor_id, element, command, user_interface, value) {
		// Handle commands
		switch (command) {
			case "mceSave":
				if (tinyMCE.getParam("fullscreen_is_enabled"))
					return true;

				var inst = tinyMCE.selectedInstance;
				var formObj = inst.formElement.form;

				if (tinyMCE.getParam("save_enablewhendirty") && !inst.isDirty())
					return true;

				if (formObj) {
					tinyMCE.triggerSave();

					// Use callback instead
					var os;
					if ((os = tinyMCE.getParam("save_onsavecallback"))) {
						if (eval(os + '(inst);')) {
							inst.startContent = tinyMCE.trim(inst.getBody().innerHTML);
							/*inst.undoLevels = new Array();
							inst.undoIndex = 0;
							inst.typingUndoIndex = -1;
							inst.undoRedo = true;
							inst.undoLevels[inst.undoLevels.length] = inst.startContent;*/
							tinyMCE.triggerNodeChange(false, true);
						}

						return true;
					}

					// Disable all UI form elements that TinyMCE created
					for (var i=0; i<formObj.elements.length; i++) {
						var elementId = formObj.elements[i].name ? formObj.elements[i].name : formObj.elements[i].id;

						if (elementId.indexOf('mce_editor_') == 0)
							formObj.elements[i].disabled = true;
					}

					tinyMCE.isNotDirty = true;

					if (formObj.onsubmit == null || formObj.onsubmit() != false)
						inst.formElement.form.submit();
				} else
					alert("Error: No form element found.");

				return true;
		}
		// Pass to next handler in chain
		return false;
	},

	handleNodeChange : function(editor_id, node, undo_index, undo_levels, visual_aid, any_selection) {
		if (tinyMCE.getParam("fullscreen_is_enabled")) {
			tinyMCE.switchClass(editor_id + '_save', 'mceButtonDisabled');
			return true;
		}

		if (tinyMCE.getParam("save_enablewhendirty")) {
			var inst = tinyMCE.getInstanceById(editor_id);

			if (inst.isDirty()) {
				tinyMCE.switchClass(editor_id + '_save', 'mceButtonNormal');
				return true;
			}

			tinyMCE.switchClass(editor_id + '_save', 'mceButtonDisabled');
		}

		return true;
	}
};

tinyMCE.addPlugin("save", TinyMCE_SavePlugin);
