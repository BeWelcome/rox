<?php
/*
   If you are having trouble with blog/comment spam, which originates
   from hijacked Windows zombie computers this plugin can help. It adds
   a simple [x] checkbox to the edit screen, which most bots cannot
   overcome without being specifically retrained to target your Wiki.
   This is just the simplest form of anti-bot captcha; something more
   sophisticated is required as soon as the attackers adapt.
*/

#-- text
$ewiki_t["en"]["ANTIBOT_CHECKBOX"] = "allow saving of page, I'm not a spambot";
$ewiki_t["en"]["ANTIBOT_FAILED"] = "Rejected. Go back and check the box below the [save] button.";

#-- show <checkbox>
$ewiki_plugins["edit_form_append"][] = "ewiki_aedit_antibot_checkbox";
function ewiki_aedit_antibot_checkbox($id, &$data, $action) {
   if (!isset($GLOBALS["ewiki_no_bot"])) {
      return(ewiki_t('<input type="checkbox" name="antibot_check" id="antibot_checkbox" value="1" /><label for="antibot_checkbox"> _{ANTIBOT_CHECKBOX}</label><br/>'));
   }
}

#-- reject if not checked
$ewiki_plugins["edit_save"][] = "ewiki_edit_save_antibot_checkbox";
function ewiki_edit_save_antibot_checkbox(&$save, &$data) {
   global $ewiki_errmsg;

   if ((!$_REQUEST["antibot_check"]) && !isset($GLOBALS["ewiki_no_bot"])) {
      $save = NULL;
      $ewiki_errmsg = ewiki_t("ANTIBOT_FAILED");
   }
}

?>