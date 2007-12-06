<?php
/*
   Graphical captchas help against automated bot attacks. This is almost
   as user-frustrating as forced account registrations, but can be half
   disabled after the first successful check.
   
   It will embed the to-solve test directly into the .html page by using
   a data:-URI for the image. This will disturb MSIE, but a workaround is
   embeded.
*/


#-- show <checkbox>
$ewiki_plugins["edit_form_append"][] = "ewiki_aedit_antibot_checkbox";
function ewiki_aedit_antibot_checkbox($id, &$data, $action) {
   if (!$GLOBALS["ewiki_no_bot"]) {
      include_once("plugins/lib/captcha.php");
      return captcha::form();
   }
}

#-- reject if not checked
$ewiki_plugins["edit_save"][] = "ewiki_edit_save_antibot_checkbox";
function ewiki_edit_save_antibot_checkbox(&$save, &$data) {
   global $ewiki_errmsg;

   if (!$GLOBALS["ewiki_no_bot"]) {
      include_once("plugins/lib/captcha.php");
      if (!captcha::check()) {
         $save = NULL;
         $ewiki_errmsg = "Access Forbidden. You did not successfully pass the captcha.";
      }
      else {
         $GLOBALS["ewiki_no_bot"] = 1;
      }
   }
}

?>