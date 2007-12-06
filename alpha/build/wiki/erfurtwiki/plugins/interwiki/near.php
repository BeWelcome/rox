<?php
/*
   If a page does not exist in the local Wiki, this plugin tries to link
   the WikiWord to somewhere else in InterWiki space (directly, without
   intermediate "this-page-only-exists-on-..." page).
   - still requires CSS and title= integration

   @feature: near
   @depends: metadb
*/


$ewiki_plugins["format_prepare_linking"][] = "ewiki_linking_near1";


function ewiki_linking_near1(&$wsrc) {

    global $ewiki_metadb, $ewiki_links;

    #-- select not-found links
    $nf = array();
    foreach ($ewiki_links as $id=>$state) {
       if (!$state) {
          $nf[] = $id;
       }
    }
    
    #-- load metadb, inject URLs into $ewiki_links
    if (count($nf) && ewiki_metadb::LOAD()) {
       $nf = ewiki_metadb::FIND($nf);
       foreach ($nf as $id=>$found) {
          if ($found) {
             $ewiki_links[$id] = ewiki_interwiki($found[0]);
          }
       }
       ewiki_metadb::UNLOAD();
    }
}


?>