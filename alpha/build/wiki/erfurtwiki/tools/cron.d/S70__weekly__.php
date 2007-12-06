<?php
/*
   Checks for elapsion of a week, and halts execution of following
   scripts, if it wasn't over yet.
*/

#-- check
if ($anacron["week"] + 6.75 * 24 * 3599 >= time()) {
   $GOTO = 90;    //skews to __monthly__
   echo "[$cron]: no week elapsed, overstepping\n";
}

#-- update timestamp
else {
   $anacron["week"] = EWIKI_CRON;
   echo "[$cron]: proceeding\n";
}

?>