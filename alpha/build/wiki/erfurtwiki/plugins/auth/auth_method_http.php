<?php

/*
   This auth plugin queries authentication data via the HTTP Basic AUTH
   method, which usually popups the ugly-looking browser login boxes. This
   is more professional than login <forms> and has the advantage, that the
   authentication infos aren't stored by browsers (unless you use a TCPA-
   enabled IE which may of course transmit authentication data to third
   parties).

   you'll need:
    - EWIKI_PROTECTED_MODE
    - plugins/auth_perm_ring.php (or another one)
    - plugins/userdb_array.php (or others)
    - a binary-safe ewiki/yoursite setup  (see the
      section on uploads and images in the README)

   You can only load __one__ auth method plugin!

   Note: if you want your Wiki to be accessible to a small group of
   people only, then you should favour the http authentication mechanism
   of your webserver! This is just a very poor implementation of the HTTP
   BASIC AUTH scheme.
   (all in here borrowed from the fragments/auth.php)
*/


#-- glue
$ewiki_plugins["auth_query"][0] = "ewiki_auth_query_http";
define("EWIKI_AUTH_QUERY_SAFE", "always");


#-- text data
$ewiki_t["en"]["RESTRICTED_ACCESS"] = "You must be authenticated to use this part of the wiki.";


#-- code
function ewiki_auth_query_http(&$data, $force_query=0) {

   global $ewiki_plugins, $ewiki_errmsg, $ewiki_author, $ewiki_ring;

   #-- fetch user:password
   if ($uu = trim($_SERVER["HTTP_AUTHORIZATION"])) {
      $auth_method = strtolower(strtok($uu, " "));
      if ($auth_method=="basic") {
         $uu = strtok(" ;,");
         $uu = base64_decode($uu);
         list($_a_u, $_a_p) = explode(":", $uu, 2);
      }
      else {
         #-- invalid response, ignore
      }
   }
   elseif (strlen($_a_u = trim($_SERVER["PHP_AUTH_USER"]))) {
      $_a_p = trim($_SERVER["PHP_AUTH_PW"]);
   }

   #-- check password
   $_success = ewiki_auth_user($_a_u, $_a_p);

   #-- request HTTP Basic authentication otherwise
   if (!$_success && $force_query || ($force_query >= 2)) {
      $realm = ewiki_t("RESTRICTED_ACCESS");
      $addmethod = "";
      if ($uu = $ewiki_config["login_notice"]) {
         $realm .= " " . $uu;
      }
      if ($uu = $ewiki_config["http_auth_add"]) {
         $addmethod = ", $uu realm=\"$realm\"";
      }
      header('HTTP/1.1 401 Authentication Required');
      header('Status: 401 Authentication Required');
      header('WWW-Authenticate: Basic realm="'.$realm.'"'.$addmethod);
   }

   #-- fin
   return($_success);
}


?>