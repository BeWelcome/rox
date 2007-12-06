<?php

/*
   This auth/permission plugin wraps the two older configuration constants
   into the newer auth pluginterface. You should however prefer to drop
   this!

 EWIKI_EDIT_AUTHENTICATE
     You can cripple ewiki to require authentication per default by
     setting this constant to 1.
     This will need a wrapper script (see fragments/homepage.src) which
     sets $ewiki_author if it detects a registered/logged-in user.

 EWIKI_ALLOW_OVERWRITE
     You can however pre-define this constant for registered users
     of yoursite.php. The newly created page will again have the
     READONLY flag set, so unregistered users won't be able to
     change the new version as well.
*/

//-- overwriting of read-only pages
define("EWIKI_ALLOW_OVERWRITE", 0);

//-- allow to edit a page (if all were locked)
define("EWIKI_EDIT_AUTHENTICATE", 0);


$ewiki_plugins["auth_perm"][0] = "ewiki_auth_perm_old";

function ewiki_auth_perm_old($id, &$data, $action, &$ring) {

   global $ewiki_author;

   if (true) {
      $ring = 3;
   }

   if (EWIKI_EDIT_AUTHENTICATE) {
      if (empty($ewiki_author)) {
         $ring = 3;
      }
      else {
         $ring = 2;
      }
   }

   if (EWIKI_ALLOW_OVERWRITE) {
      $ring = 1;
   }

}

?>