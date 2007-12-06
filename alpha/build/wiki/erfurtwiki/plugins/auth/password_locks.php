<?php

/*
   This plugin allows to lock individual pages with a password, so they
   will be editable only when this is given again. You can't use this in
   conjunction to another auth plugin, this one is standalone.
   It will probably be of little use for most Wiki setups, and certainly
   requires an adminstrator to manage it with 'ewikictl' from time to time.
*/

#-- switch ewiki into virtual paging mode ;)
define("EWIKI_PROTECTED_MODE", 1);

#-- _auth glue
$ewiki_plugins["auth_perm"][] = "ewiki_auth_perm_password_locks";
$ewiki_plugins["edit_form_append"][] = "ewiki_edit_field_pwlock";
$ewiki_plugins["edit_save"][] = "ewiki_edit_save_pwlock";

#-- text/translations
$ewiki_t["de"]["lock page with"] = "Seite schützen mit";
$ewiki_t["de"]["password"] = "Paßwort";


#-- this prints the password query <form>
function ewiki_auth_login_password(&$data, &$author, &$ring, $force) {

   global $ewiki_errmsg;
   static $cookie=0;

   #-- set cookie
   if ($pw = $_POST["unlock_password"]) {
      if (!$cookie) {
         setcookie("unlock_password", $pw, time()+7*24*3600, "/", $_SERVER["SERVER_NAME"]);
         $cookie++;
      }
   }
   #-- get cookie
   else {
      $pw = $_REQUEST["unlock_password"];
   }

   #-- compare
   $ok = ($pw == $data["meta"]["password"]);

   #-- print login form
   if ($force && !$ok) {
      $ewiki_errmsg = ewiki_t( <<<END
<h2>_{This page is locked}</h2>
<form action="{$_SERVER['REQUEST_URI']}" method="POST" enctype="multipart/form-data">
_{password} <input type="password" size="16" name="unlock_password">
<br />
<input type="submit" value="_{access}">
</form><br /><br />
END
      );
   }

   return($ok);
}


#-- this separates password-protected pages from all others
function ewiki_auth_perm_password_locks($id, &$data, $action, $ring, $force) {

   $PROTECT = array(
      "edit", "delete", "export", "any_other_action",
   );

   #-- only protect pages with passwords for editing
   if (in_array($action, $PROTECT)) {
      if (empty($data["meta"]["password"])) {
         return(true);
      }
      else {
         $ok = ewiki_auth_login_password($data, $ewiki_author, $ewiki_ring,
$force=1);
         return($ok);
      }
   }
   #-- strip password here, so it won't display accidently
   elseif ($action == "info") {
      unset($data["meta"]["password"]);
      return(true);
   }
   #-- pages we don't care
   else {
      return(true);
      // return(NULL);  // for tri-state boolean ["auth_perm"]
   }
}


#-- add password field on edit/ page
function ewiki_edit_field_pwlock($id, &$data, $action) {
   $o = ewiki_t('<br /><br />_{lock page with} <b>_{password}</b> <input type="password" name="lock_password" size="16" value="').htmlentities($data["meta"]["password"]).'"><br />';
   return($o);
}


#-- store password into page db entry {meta} field
function ewiki_edit_save_pwlock(&$save, &$old_data) {

   $pw = $_REQUEST["lock_password"];
   if (true) {

      #-- keep in {meta} field
      $save["meta"]["password"] = $pw;

      #-- copy it immediately
      if ($_REQUEST["unlock_password"] != $pw) {
         setcookie("unlock_password", $pw, time()+7*24*3600, "/");
         $_COOKIE["unlock_password"] = $pw;
         $_REQUEST["unlock_password"] = $pw;
      }
   }
} 


?>