<?php
/*
   This part writes back the $anacron[] timestamps, so we can keep track
   of when we run which parts in here.
*/

#-- update
if ($anacron && ANACRON_ID) {
   echo "[$cron]: writing back anacron timestamps\n";

   #-- get old
   ($data = ewiki_db::GET(ANACRON_ID))
   or ($data = ewiki_db::CREATE(ANACRON_ID));

   #-- vars
   $data["flags"] = EWIKI_DB_F_SYSTEM;
   ewiki_db::UPDATE($data);
   $data["lastmodified"] = $anacron["last"] = EWIKI_CRON;
   $data["content"] = serialize($anacron);

   #-- overwrite
   ewiki_db::WRITE($data, 1)
   or ($data["version"]++ && ewiki_db::WRITE($data));
}

?>