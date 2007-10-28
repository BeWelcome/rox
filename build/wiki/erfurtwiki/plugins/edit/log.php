<?php

/*
   Adds an changelog input box below the edit/ screen. This will be
   used in the RecentChanges plugin and can be filled by users, if
   they wish to denote important changes or additions to a page.
*/


define("EWIKI_UP_CHANGELOG", "change_log");
$ewiki_t["en"]["CHANGELOG"] = "shortly describe your changes/additions";
$ewiki_t["de"]["CHANGELOG"] = "beschreibe kurz deine Änderungen/Ergänzungen";

#-- <input> 
$ewiki_plugins["edit_form_append"][] = "ewiki_aedit_changelog";
function ewiki_aedit_changelog($id, &$data, $action) {

   $var = EWIKI_UP_CHANGELOG;
   $val = $_REQUEST[EWIKI_UP_CHANGELOG];
   return(ewiki_t(<<< EOT
<br />
 _{CHANGELOG}:<br /><input size="50" name="$var" value="$val">
<br />
EOT
   ));
}


#-- save into db {meta} field
$ewiki_plugins["edit_save"][] = "ewiki_edit_save_changelog";
function ewiki_edit_save_changelog(&$save, &$old_data) {
   $log = trim($_REQUEST[EWIKI_UP_CHANGELOG]);
   $save["meta"]["log"] = $log;
}


?>