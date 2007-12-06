<?php
/*
   Any page that has "DeletedPage" as content will be removed if that wasn't
   changed in a given timeframe (typically a month or two weeks). If a page
   is completely empty that will also work. Other aliases to engage this
   action are:
    - "deleted" (or "DeletedPage")
    - "DeletePage" or "delete"
    - simply "trash" or a link to "TrashCan"
    - "RemovePage", "remove"
    - "RemovedPage", "removed"
   
   This feature is also known as MeatBall:KeptPages.
*/


// define("DELETEPAGES", 1);


#-- proceed
if (defined("DELETEPAGES") && DELETEPAGES && $keptpages) {

   echo "[$cron]: Scanning for pages to kill...\n";

   $triggers = array(
      "delete",
      "deleted",
      "del",
      "remove",
      "removed",
      "kill",
      "unlink",
      "unlink()",
      "unlink();",
      "trash",
      "rm",
      "rm -f",
      "DeletePage",
      "DeletedPage",
      "RemovePage",
      "KillPage",
      "UnlinkPage",
      "TrashCan",
   );

   #-- list all
   $all = ewiki_db::GETALL("id", "version", "lastmodified");
   while ($row = $all->get()) {
   
      #-- check that it wasn't modified lately
      if (time() >= $row["lastmodified"] + $keptpages) {

         #-- check page content for trigger words
         $id = $row["id"];
         $row = ewiki_db::GET($id);
         $text = strtolower(trim($row["content"]));
         $refs = trim($row["refs"]);
         if (in_array($text, $triggers)
         or ewiki_in_array($refs, $triggers))
         {
            #-- purge it, no mercy!!!!!
            echo "   $id";
            for ($v=$row["version"]; $v>=1; $v--) {
               if (ewiki_db::GET($id, $v)) {
                  echo " [$v]";
                  ewiki_db::DELETE($id, $v);
               }
            }
            echo "\n";
         }
      }
      
   }
}


?>