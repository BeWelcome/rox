<?php

/*
   "Anonymous" login (like in FTP). Users can login using their email
   address as password. In this implementation we do not depend upon
   the username being "anonymous" (any string will do).

   This plugin itself implements a partial ["auth_query"] plugin, which
   evaluates the HTTP From: request header (see RFC2616 section 14.22),
   which however isn't supported by the mainstream browsers. The value
   of this header is already evealuated by the ewiki core script and
   stored into the {author} field - if you use this plugin or not. The
   only difference is, that the From: header now grants edit/ permission
   in EWIKI_PROTECTED_MODE.
*/

$ewiki_config["login_notice"] = "Anonymous login (use your email address as password).";
$ewiki_config["http_auth_add"] = "ANONYMOUS";


define("EWIKI_AUTH_ANONYMOUS_RING", 2);     // permission ring level
define("EWIKI_AUTH_ANONYMOUS_VERIFY", 0);   // verify given email address


$ewiki_plugins["auth_userdb"][] = "ewiki_auth_userdb_anonymous";



function ewiki_auth_userdb_anonymous($username, $password) {

   global $ewiki_author;

   #-- get params
   if (!$name = $username) {
      $name = "anonymous";
   }
   if (strpos($password, "@")) {
      $email = trim($password);
   }
   elseif (strpos($username, "@")) {
      $email = trim($username);
      $name = "anonymous";
   }
   else {
      $email = "";
   }

   #-- HTTP header field "From: joe@example.com"
   if (empty($email)) {
      $email = trim($_SERVER["HTTP_FROM"]);
   }

   #-- check for valid address (the-non-4000-chars-regex-check)
   $c = EWIKI_CHARS;
   if ($email && preg_match("/^[-!%&~+.$c]+@([-$c]{2,}\.)+[-$c]{2,9}$/i", $email)) {

      return(array($password, EWIKI_AUTH_ANONYMOUS_RING, $ewiki_author="anonymous|$email"));
   }

   return(false);   
}


?>