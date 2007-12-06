<?php

define("EWIKI_DIE_FOR_SECURITY", 1);

/*

   This tries to fight against variables set from outside (for PHP
   versions where register_globals is still activated). It only checks
   for variables used by ewiki (whose names start with "$ewiki_").
   Warnings are written into the system log, if someone tries to insert
   variables.

*/


if (ini_get("register_globals") == "1") {

   $uu_security_leak = 0;

   define_syslog_variables();
   openlog("ewiki", LOG_PID, LOG_USER);

   foreach ($_REQUEST as $varname => $value) {

      if (isset($GLOBALS[$varname]) && (substr($varname, 0, 5) == "ewiki")) {

         $uu_security_leak = 1;

         unset($GLOBALS[$varname]);

         $err_msg = "ewiki security alert: ".$_SERVER["REMOTE_ADDR"].":".$_SERVER["REMOTE_PORT"]." tried to set the variable \$$varname to '".rawurlencode($value)."'. Please deactivate register_globals!";
         syslog(LOG_CRIT, $err_msg);
         error_log($err_msg, 0);
         error_log($err_msg, 3, "/tmp/ewiki.log");
      }
   }


   if ($uu_security_leak) {

      if (EWIKI_DIE_FOR_SECURITY) {
         die("<h1>Forbidden</h1>\nERROR #0257: For security reasons your request has been cancelled (and logged).");
      }
   }

}


?>