<?php
/*
   Adds additional links behind any page that also exists on foreign
   Wikis. The links could also be icons, but this wasn't implemented
   here.
*/


$ewiki_plugins["link_final"][] = "ewiki_neighbor_links";

function ewiki_neighbor_links(&$str, &$type, &$href, &$title) {

   global $ewiki_metadb;
   if (!isset($ewiki_metadb)) {
      ewiki_metadb::LOAD();
   }
   if (!$ewiki_metadb) {
      return;
   }
   
   #-- check for alternate universes
   if ($alt = $ewiki_metadb[strtolower($href)]) {

      $inj = array();
      $real = $alt[0];
      foreach ($alt[1] as $iw) {
         $url = ewiki_interwiki("$iw:$real");
         $inj[] = "<a href=\"$url\">$iw:</a>";
      }
      
      $str .= " (" . implode(", ", $inj). ")";
   }
   
}


?>