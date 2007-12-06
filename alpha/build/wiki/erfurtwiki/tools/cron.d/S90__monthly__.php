<?php
/*
   Stops following parts from execution if not a month has elapsed
   since last run.
*/

if ($anacron["month"] + 29 * 24 * 3599 >= time()) {
   $GOTO = 98;   // skip most __monthly__ scripts without '$HALT'ing rest
   echo "[$cron]: no month has elapsed since last run yet, not running any script now\n";
}
else {
   $anacron["month"] = EWIKI_CRON;
   echo "[$cron]: proceeding\n";
}

?>