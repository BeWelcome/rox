<?php
/*
   This replaces Admin/PrepareAutolinking and enables itself if you
   have the "plugins/linking/autolinking.php" enabled.

   -> creates a cache entry for pages with single-word and non-wiki names
*/

#-- cfg (already in the according plugin)
// define("EWIKI_AUTOLINKING_CACHE", "system/tmp/autolinking");


#-- start if plugin loaded / constant defined
if (defined("EWIKI_AUTOLINKING_CACHE")) {

   #-- start list
   $pages = array();

   #-- find AllPages
   $result = ewiki_db::GETALL(array("id", "flags"));
   while ($row = $result->get()) {

      if (EWIKI_DB_F_TEXT != ($row["flags"] & EWIKI_DB_F_TYPE)) {
         continue;
      }
      $id = $row["id"];

      #-- only care about pagenames, which are words but no WikiWords
      if (!strpos($id, " ") && preg_match('/^\w+$/', $id)
      && !preg_match('/^(['.EWIKI_CHARS_U.']+['.EWIKI_CHARS_L.']+){2,}[\w\d]*$/', $id))
      {
         $pages[] = $id;
      }

   }

   #-- save found pages in cache entry
   $DEST = EWIKI_AUTOLINKING_CACHE;
   $save = array(
      "id" => $DEST,
      "version" => 1,
      "flags" => EWIKI_DB_F_SYSTEM,
      "created" => time(),
      "lastmodified" => time(),
      "author" => ewiki_author("PrepareAutolinking"),
      "content" => "",
      "meta" => "",
      "refs" => "\n\n" . implode("\n", $pages) . "\n\n",
   );
   $ok = ewiki_db::WRITE($save, true);

   #-- output results
   if ($ok) {
      echo "[$cron]: Written informations about ".count($pages)." pages into the database cache entry '$DEST'"
         . "\n   These pages will then get autolinked by the according plugin.\n";
   }
   else {
      echo "[$cron]: Error writing the database cache entry '$DEST'. Autolinking pages won't work now.\n";
   }

}

?>