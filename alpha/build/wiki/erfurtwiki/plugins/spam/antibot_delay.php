<?php
/*
   Most automated link/spambots load the edit screen and try to send a
   submit response immediately after that. This plugin enforces a short
   delay of 5 seconds before save submissions are accepted - an error
   message is displayed to users who hit [save] too quickly after the
   edit page was loaded.
   This will defend the majority of current bots and zombie computers.
   And never forget: 'time is money' even more so counts for spammers.
   A later version may need to hash-assert the timestamp (with nonce).
   Idea taken from phpBB2 "disable_spambots" by <magenta*trikuare.cx>.
*/


#-- config
define("EWIKI_UP_SAVE_DELAY", "e_g_t");
define("EWIKI_EDIT_SAVE_DELAY", 5);


#-- embed timestamp
$ewiki_plugins["edit_form_append"][] = "ewiki_aedit_antibot_delay";
function ewiki_aedit_antibot_delay($id, &$data, $action) {
   return('<input type="hidden" name="'.EWIKI_UP_SAVE_DELAY.'" value="'.time().'" />');
}


#-- check timespan
$ewiki_plugins["edit_save"][] = "ewiki_edit_save_antibot_delay";
function ewiki_edit_save_antibot_delay(&$save, &$data) {
   global $ewiki_errmsg;

   if (!isset($GLOBALS["ewiki_no_bot"])) {
      if (time() < $_REQUEST[EWIKI_UP_SAVE_DELAY] + EWIKI_EDIT_SAVE_DELAY) {
         $save = NULL;
         $ewiki_errmsg = ewiki_t("Too hasty saving rejected. Please go back, wait 3 seconds and hit [save] again.");
      }
   }
}


?>