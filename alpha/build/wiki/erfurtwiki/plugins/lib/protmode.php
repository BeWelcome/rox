<?php

/*
   This file holds utility functions (not core, but required by multiple
   plugins) for use with the EWIKI_PROTECTED_MODE.
*/

//Set to pass binary data to auth plugins
define('EWIKI_BINARY_DATA_SAFE_AUTH',0);

$ewiki_plugins["handler_binary"][0] = "ewiki2_binary_page_handler";


/*  this function reimplements some of the ewiki_page() functionality and
    is automatically invoked for pages with the _BINARY flag set
*/
function ewiki2_binary_page_handler($id, &$data, $action) {

   global $ewiki_plugins, $ewiki_errmsg;

    //echo("running handler");

   #-- however we do not handle _DISABLED or _SYSTEM entries
   if (empty($data["flags"]) || !($data["flags"] & EWIKI_DB_F_BINARY)
   || ($data["flags"] & EWIKI_DB_F_SYSTEM)
   || ($data["flags"] & EWIKI_DB_F_DISABLED))
   {
      return(ewiki_t("DISABLED"));
   }

   #-- _PROTECTED_MODE
   # (Andy: can we print a login <form> once we reached here???)   
   if (!ewiki_auth($id, $data, $action, $ring=false, $force=EWIKI_AUTO_LOGIN)) {
       //echo("not authenticated id".$id." action ".$action);   
      return($ewiki_errmsg);
   }
   //echo("authenticated  id:".$id." action ".$action);
   #-- chain to one of the action_BINARY plugins
   if ($pf = $ewiki_plugins["action_binary"][$action]) {
       //echo("running ".$pf."()");
      return($pf($id, $data, $action));
   }

   #-- else let ?binary= return the requested 'page' entry
   //Consider binary view here
   //ewiki_binary($id);
}

?>