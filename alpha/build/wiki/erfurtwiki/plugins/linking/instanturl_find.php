<?php

/*
   This plugin enables the [find:] moniker, which searches (for partial
   matches) in "instant" and "interwiki" URLs, and alternatively selects
   a similiar wiki page. As last resort falls back on google search.
*/


$ewiki_plugins["intermap"]["find"] = "ewiki_linking_findany";

function ewiki_linking_findany($moniker, $page) {

   global $ewiki_config;

   #-- lists
   $page_i = strtolower($page);
   $search = array_merge(
      $ewiki_config["instant"],
      $ewiki_config["interwiki"]
   );
   foreach ($search as $pn=>$url) {
      if (strtolower($pn) == $page_i) {
         return($url);
      }
   }

   #-- find a page
   $result = ewiki_db::SEARCH("id",$page_i);
   while ($row = $result->get()) {
      if (($row["flags"] & EWIKI_DB_F_TYPE) == EWIKI_DB_F_TEXT) {
         return(ewiki_script("", $row["id"]));
      }
   }   

   #-- Google saves the day!
   return("http://www.google.com/search?q=".urlencode($page));
}


?>