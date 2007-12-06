<?php
/*
   Automatically deletes historic page versions, but leaves out the first,
   a few versions from the end and every Nth version (to be configured)
   also. DOES NOT ENGAGE without configuring it first.
*/


// define("VERHOLES_INTERLEAVE", 10);   // keep every Nth version, off==0
// define("VERHOLES_KEEP_END", 3);      // always keep last num versions
// define("VERHOLES_KEEP_START", 1);    // you SHOULD keep .1 for flat_files!
// define("VERHOLES_NOTOUCH_TIME", 30); // keep versions younger than 30 days


#-- start, if
if (defined("VERHOLES_INTERLEAVE") and defined("VERHOLES_KEEP_END")) {

   #-- built-in defaultz for these two
   define("VERHOLES_KEEP_START", 1);
   if (defined("KEPTPAGES")) {
      define("VERHOLES_NOTOUCH_TIME", KEPTPAGES);
   }
   else {
      define("VERHOLES_NOTOUCH_TIME", 30);
   }
   define("EWIKI_DB_F_ARCHIVE", 1<<11);
   $start = VERHOLES_KEEP_START;
   $end = VERHOLES_KEEP_END;
   $leave = VERHOLES_INTERLEAVE;
   $ignore = VERHOLES_NOTOUCH_TIME;
   echo "[$cron]: deleting all page versions $start..-$end with interleave of $leave, without touching versions younger than $ignore days\n";
   $t_skip = time() - $ignore*24*2600;

   #-- visit each page
   $result = ewiki_db::GETALL(array("id", "version"));
   while ($row = $result->get()) {

      $id = $row["id"];
      if ($row["flags"] & EWIKI_DB_F_ARCHIVE) { continue; }

      $verZ = $row["version"] - $end;
      $verA = $start;

      #-- walk versions (top..down)
      for ($ver=$verZ; $ver>=$verA; $v--) {

         #-- interleave      
         if ($leave && !($ver % $leave)) {
            continue;
         }

         #-- skip if too fresh
         $row = ewiki_db::GET($id, $ver);
         if ($row["lastmodified"] >= $t_skip) {
            continue;
         }
         
         #-- check flags
         if ($row["flags"] & EWIKI_DB_F_ARCHIVE) {
            continue;
         }
         if ($row["flags"] & EWIKI_DB_F_READONLY) {
            // has no meaning here(?!)
         }

         #-- else really delete it
         ewiki_db::DELETE($id, $ver);
         echo "   $id[$ver]\n";
         
      }

   } // GETALL
}

?>