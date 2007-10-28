<?php
/*
   Creates a lock file, so the cron scripts aren't run two times at
   once.
*/

#-- cfg
define("CRON_LOCK_STALE", 1000);  // in seconds, remove any lock after that

#-- file name
if (!defined("CRON_LOCK")) {
   define("CRON_LOCK", EWIKI_TMP . "/ewiki-crond-runparts.$_SERVER[SERVER_NAME].lock");
}

#-- check for existence
if (file_exists(CRON_LOCK)) {
   echo "[$cron]: cron lock file detected\n";
   if ((filemtime(CRON_LOCK) + CRON_LOCK_STALE) < time()) {
      echo "[$cron]: was stale lock file, removing\n";
      unlink(CRON_LOCK);
      echo "[$cron]: creating fresh lock file '".CRON_LOCK."'\n";
      if (!touch(CRON_LOCK)) {
         $HALT = 1;
      }
   }
   else {
      echo "[$cron]: you must delete '".CRON_LOCK."' manually to overcome this\n";
      echo "[$cron]: shutting down run-parts\n";
      $HALT = 2;
   }
}
else {
   echo "[$cron]: creating lock file '".CRON_LOCK."'\n";
   touch(CRON_LOCK);
}

?>