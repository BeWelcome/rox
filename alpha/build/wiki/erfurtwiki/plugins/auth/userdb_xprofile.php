<?php
/*
   Allows access using XML User Profiles (XUP/XProfile). Requires $_SESSION support.
*/

#-- init hack (runs slow without)
if (!constant("SESSION_ID") && !session_id()) { 
   session_start();
}


$ewiki_plugins["auth_userdb"][] = "ewiki_auth_userdb_xprofile";

function ewiki_auth_userdb_xprofile($username, $password) {
   global $ewiki_author;
   
   #-- already logged in
   if ($_SESSION["xprofile"]) {
      $ewiki_author = $_SESSION["ewiki_author"];
      return($true);
   }

   #-- fetch profile
   $xpro = new xprofile($username);  // URL or email-like shortcut
   if ($xpro->control) {
  
      #-- validate
      if ($xpro->login() ) {
      
         #-- save data
         $_SESSION["ewiki_author"] = $ewiki_author = $xpro->info["nickname"];
         $_SESSION["xprofile"] = $xpro->url;
      
         return(true);
      }
      else {
         // misuse (already logged by XProfile manager)
         ewiki_log("userdb_xprofile: wrong password '...' for remote account on '$username'", 1);
      }
   }

   return(false);   
}

?>