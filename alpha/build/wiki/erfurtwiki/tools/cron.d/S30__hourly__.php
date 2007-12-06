<?php
/*
   All following actions will be executed every hour. This script just
   injects a break and halts everything else, if there wasn't an hour
   between now and the last run.
*/


#-- stop if no hour elapsed since last activation
if ($anacron["hour"]+3575 >= time()) {
   $GOTO = 50;    //go testing if __daily__ scripts are to be run again
   echo "[$cron]: overstepping parts\n";
}

#-- proceed, update time
else {
   $anacron["hour"] = EWIKI_CRON;
   echo "[$cron]: proceeding\n";
}


?>