<?php
/*
   Removes the lock set by the S18lock.php script.
*/

if (defined("CRON_LOCK")) {
   echo "[$cron]: removing lock file again\n";
   unlink(CRON_LOCK);
}
?>