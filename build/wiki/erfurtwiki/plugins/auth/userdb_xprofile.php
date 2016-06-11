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
   if ($this->_session->get("xprofile")) {
      $ewiki_author = $this->_session->get("ewiki_author");
      return($true);
   }

   #-- fetch profile
   $xpro = new xprofile($username);  // URL or email-like shortcut
   if ($xpro->control) {
  
      #-- validate
      if ($xpro->login() ) {
      
         #-- save data
         $this->_session->set( "ewiki_author", $ewiki_author = $xpro->info["nickname"] )
         $this->_session->set( "xprofile", $xpro->url )
      
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