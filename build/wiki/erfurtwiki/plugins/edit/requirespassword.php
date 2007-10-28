<?php
/*
   This is a very light alternative to powering up the _PROTECTED_MODE.
   It simply shows up a password dialog when you click on EditThisPage.
   Either define("EWIKI_ADMIN_PW", "..."), edit "fragments/funcs/auth.php",
   or add a "$passwords[...]=..." line here.
*/

#-- you could comment out the first line here, then the password box would
#   popup first when saving an edited page
$ewiki_plugins["edit_hook"] =
$ewiki_plugins["edit_save"] = "ewiki_edit_requires_password";


function ewiki_edit_requires_password(&$uu1, &$uu2, &$uu3) {

   #-- just set your password here
   // with
   // define("EWIKI_ADMIN_PW", "password");
   // or
   // $passwords["username"] = "password";

   require(EWIKI_BASE_DIR."/fragments/funcs/auth.php");
}

?>