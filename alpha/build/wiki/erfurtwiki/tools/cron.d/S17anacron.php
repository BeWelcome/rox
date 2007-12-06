<?php
/*
   This part ensures that we don't get run TOO often. It implements 
   a delay and looks up when we actually ran the last time. 
*/

define("ANACRON_ID", "etc/anacron/last-run");
define("ANACRON_PAUSE", 20);  // in seconds, minimum delay between runs


#-- get entry
$data = ewiki_db::GET(ANACRON_ID);
if ($data && ($data["flags"] & EWIKI_DB_F_SYSTEM)) {
   echo "[$cron]: reading in anacron timestamps\n";
   $anacron = unserialize($data["content"]);
}
else {
   echo "[$cron]: first run ever\n";
   $anacron = array(
      "last" => 0,
      "minute" => 0,
      "hour" => 0,
      "day" => 0,
      "week" => 0,
      "month" => 0,
   );
}

#-- check _PAUSE
if ($anacron["last"]+ANACRON_PAUSE >= time()) {
   echo "[$cron]: oooops, we're beeing called to often, the minimum interleave is " . ANACRON_PAUSE . " seconds\n";
   $HALT = 2;
}

#-- prepare state flags?
else {
   // ...
   
   // we define this a third time here, in case it really was missed
   // somehow (?)
   @define("EWIKI_CRON", time());
}

?>