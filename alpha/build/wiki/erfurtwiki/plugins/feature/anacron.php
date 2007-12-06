<?php
/*
   Load this script to make use of the tools/cron.d/ utilities without
   having to set up a real cron(8) daemon rule. But please use this
   only if you have no other choice, as it could slow down your Web
   server. This snippet activates the run-parts in PHP shutdown mode
   around every second hour (depends on how many users/bots walk over
   your server).
   You must run the cron.d/ run-parts script _ONCE_ before this works,
   because this does not create the required+special database entry.
*/

define("EWIKI_ANACRON_INTERLEAVE", 2*3600);   // in seconds
define("EWIKI_ANACRON_ID", "etc/anacron/last-run");   // a _SYSTEM entry


#-- checks elapsed timeframe, and installs _runparts for shutdown action
$ewiki_plugins["init"][] = "ewiki_anacron_checktime";
function ewiki_anacron_checktime() {
   global $ewiki_plugins;
   if ( ($d = ewiki_db::GET(EWIKI_ANACRON_ID))
    and ($d = unserialize($d["content"]))
    and ($d["last"] >= UNIX_MILLENNIUM)
    and (time() >= $d["last"] + EWIKI_ANACRON_INTERLEAVE) )
   {
      register_shutdown_function("ewiki_anacron_runparts");
   }
}


#-- every two hours: starts run-parts
function ewiki_anacron_runparts() {

   // clean up env, because we've been triggered by someone innocent
   $_SERVER["REMOTE_ADDR"] = "0.0.0.0";
   $_SERVER["REMOTE_PORT"] = "-";
   $ewiki_author = "ewiki_anacron_runparts";

   // only works for more or less standard installations
   include(EWIKI_BASE_DIR."/tools/cron.d/run-parts.php");
}

?>