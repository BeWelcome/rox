<?php
/*
   Any database entry (_TEXT and _BINARY) that is linked on "TrashCan"
   will be deleted after it hasn't been removed in over a month from there.
   
   This is more useful for Intranats, for public Wikis is makes more sense
   to stick to the "DeletedPage" page election method, because the TrashCan
   entries get invalid if an entry was removed for even for only one version
   in the configured timespam (OTH this may be a good voting method).
   
   You can however use the TrashCan for unwanted contributions and things
   like clearing the SandBox from time to time automatically. But don't
   forget that only the versions up to the $keptpages timeframe get purged.
*/

// define("TRASHCAN_ENGAGE", 14);   // in days, how long something must be listed on the special "TrashCan" page before that deletion request is valid


#-- ok, let's go
if (defined("TRASHCAN_ENGAGE") && ($last = TRASHCAN_ENGAGE)) {

   echo "[$cron]: checking out lasted entries from TrashCan for deletion:\n";

   #-- get TrashCan page latest version
   $data = ewiki_db::GET($id="TrashCan");
   $version = $row["version"];
   $listed = explode("\n", trim($data["refs"]));

   #-- look trough all previous revisions until $last timeframe,
   #   and compare {refs} for constant listing of all entries
   while ($data = ewiki_db::GET($id, --$version)) {

      #-- timeframe
      if (time() >= $row["lastmodified"] + $last) {
         break;  // done with version comparisions
      }

      #-- remove anything that isn't listed in all TrashCan page versions
      $cmplist = explode("\n", trim($data["refs"]));
      $listed = array_intersect($listed, $cmplist);
   }

   #-- delete anything that's still in the purge list
   foreach ($listed as $id) {

      #-- walk through all versions
      if ($row = ewiki_db::GET($id)) {
         echo "   $id";
      
         $version = $row["version"];
         for ($version; $version >= 1; $version--) {

            #-- don't kill revisions that have borought lifetime ($keptpages)
            if ($row["lastmodified"] >= $keepuntil) {
               continue;
            }
            
            #-- oh, so sad!
            echo " [$version]";
            ewiki_db::DELETE($id, $version);

         }
         
         echo "\n";
      }
   }
}


?>