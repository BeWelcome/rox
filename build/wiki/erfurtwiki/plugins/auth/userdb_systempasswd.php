<?php

/*
  This plugin provides a user 'database' by using the internal/system
  page "system/passwd". You first must create that page (to do so, please
  enter a URL like "?id=edit/system/passwd" manually), and initially
  create one user at least. Inside of this page you should list one
  user per line, in the following format (without spaces!):

     username:password:ringlevel
     user2:password

  Where the ringlevel is optional and the passwords can be in cleartext(),
  crypt(), md5() or sha1() encoding. The privilege ("ring") levels have
  following meaning:
    0 - superuser
    1 - moderator (delete, ...)
    2 - ordinary user (edit, ...)
    3 - browsing only (view, info, ...)
  The level 1 is the default for everyone you'll notice inside of the
  "system/passwd" control page. First create a superuser (ring level 0),
  because only this person can edit _SYSTEM pages (the "system/passwd"
  will get automatically this flag).

  You'll also need:
    - EWIKI_PROTECTED_MODE
    - plugins/auth_perm_ring.php (or _perm_unix.php)
    - plugins/auth_method_http.php (or another one)
*/


#-- game
define("EWIKI_USERDB_SYSTEMPASSWD", "system/passwd");


#-- glue
$ewiki_plugins["auth_userdb"][] = "ewiki_auth_userdb_systempasswd";


#-- code
function ewiki_auth_userdb_systempasswd($username, $password) {

   global $ewiki_config;

   #-- bad
   if (empty($username)) return;

   #-- get pw list
   $data = ewiki_db::GET(EWIKI_USERDB_SYSTEMPASSWD);

   #-- check page flags
   if (($data["version"]) && !($data["flags"] & EWIKI_DB_F_SYSTEM)) {
      $data["flags"] |= EWIKI_DB_F_SYSTEM;
      $data["version"]++;
      ewiki_db::WRITE($data);
   }
   
   #-- search user
   $entry = array();
   foreach (explode("\n",$data["content"]) as $line) {

      $line = trim($line);

      #-- user found?
      if (strtok($line, ":") == $username) {

         #-- split entry line
         $entry = explode(":", strtok("\377"));

         #-- add default ring level
         if (!isset($entry[1])) {
            $entry[1] = 1;
         }

         break;
      }
   }

   return($entry);
}


?>