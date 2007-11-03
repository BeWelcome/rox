<?php

/*
   http user space authentication
   ¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯
   can be used with the tools/, if you don't want to fiddle
   with the .htaccess and .htpasswd files for your webserver
*/


 #-- (pw array - I have such one in an external config file)
 $passwords = (array)$passwords + array(
//   "user" => "password",
//   "u2" => "password",
 );
 if (defined("EWIKI_ADMIN_PW")) {
    $passwords["admin"] = EWIKI_ADMIN_PW;
 }
 if (empty($passwords)) {
    die("<h1>Restricted Access</h1>\nPlease first create an admin account in '<tt>.../fragments/funcs/auth.php</tt>' or define() the '<tt>EWIKI_ADMIN_PW</tt>' constant.");
 }


 #-- fetch user:password
 if ($uu = $_SERVER["HTTP_AUTHORIZATION"]) {
    foreach (explode(",", $uu) as $uu) {
       $uu = trim($uu);
       if (strtoupper(strtok($uu, " ")) == "BASIC") {
          $uu = strtok(" ");
          $uu = base64_decode($uu);
          list($_a_user, $_a_password) = explode(":", $uu, 2);
       }
    }
 }
 elseif (strlen($_a_user = trim($_SERVER["PHP_AUTH_USER"]))) {
    $_a_password = trim($_SERVER["PHP_AUTH_PW"]);
 }

 #-- check password
 $_success = false;
 if ($_a_user && $_a_password && ($_a_password==@$passwords[$_a_user])) {
    $_success = $_a_user; 
 }

 #-- request HTTP Basic authentication otherwise
 if (!$_success) {
    header('HTTP/1.1 401 Authentication Required');
    header('Status: 401 Authentication Required');
    header('WWW-Authenticate: Basic realm="restricted access"');
    die();
 }

?>