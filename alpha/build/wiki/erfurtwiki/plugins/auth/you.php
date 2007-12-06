<?php

/*
   Use this plugin, if you just want to make your Wiki readonly and have
   a possibility to 'login' as superuser, when needed.

   When you are queried for your login data, you can use whatever you want
   for user/login name, but the following will be your master-password:
*/

#-- your password:
define("YOUR_PASSWORD", "*");
define("YOUR_LEVEL", 0);                 //  0 = superuser

#-- change to read-only mode per default:
define("EWIKI_PROTECTED_MODE", 1);
define("EWIKI_AUTH_DEFAULT_RING", 3);    //  3 = read/view/browse-only

#-- your prefered login plugin:
include("plugins/auth/auth_method_http.php");
//include("plugins/auth/auth_method_form.php");

#-- you'll also want this:
include("plugins/auth/auth_perm_ring.php");


#-- glue
$ewiki_plugins["auth_userdb"][] = "ewiki_auth_userdb_your_password";


#-- sorry, nothing impressive here
function ewiki_auth_userdb_your_password($username, $password=false) {
   return(array(YOUR_PASSWORD, YOUR_LEVEL, "the great superuser 2.0", $_SERVER["SERVER_ADMIN"]));
}


?>