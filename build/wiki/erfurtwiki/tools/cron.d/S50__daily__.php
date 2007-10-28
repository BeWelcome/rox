<?php
/*
   Checks if a day has elapsed since last run, and stops following
   script parts if not.
*/

#-- stop processing
if ($anacron["day"] + 24 * 3575 >= time()) {
   $GOTO = 70;    //skips to __weekly__ parts, instead of doing hard $HALT
   echo "[$cron]: not running again\n";
}

#-- or update time
else {
   $anacron["day"] = EWIKI_CRON;
   echo "[$cron]: proceeding\n";
}

?>