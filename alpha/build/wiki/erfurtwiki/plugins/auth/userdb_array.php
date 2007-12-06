<?php

/*
  This authentication/permission plugin, implements a user database
  via an internal array (most simple approach).

  You can insert passwords in cleartext(), crypt(), md5() or sha1();
  Privilege levels ("rings") are:
    0 - administrator rights
    1 - privileged user (advanced functionality: uploading)
    2 - standard services (edit, view, info, ...)
    3 - unprivileged users (only browsing),
        which is also default if not logged in (see EWIKI_AUTH_DEFAULT_RING)
  Usernames and passwords are __always and everywhere__ case-sensitive!

  To use authentication you need also:
    - EWIKI_PROTECTED_MODE=1
    - an auth_perm plugin
    - an auth_method plugin
  There may be multiple "auth_userdb" plugins (like this one) enabled.
*/


#-- here!
$ewiki_auth_user_array = array(

  // "username"	=> array("password", $RING_LEVEL=2),
  // "user2"	=> array("sU7oi30Zmf2KTr4", 1),

);


#-- glue
$ewiki_plugins["auth_userdb"][] = "ewiki_auth_user_array";

#-- code
function ewiki_auth_user_array($username, $password) {
   return($GLOBALS["ewiki_auth_user_array"][$username]);
}


?>