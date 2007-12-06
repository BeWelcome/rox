<?php
/*
   Puts virtual links to SisterPages at the bottom of every page (only
   when viewed).

   @feature: virtualsister
   @depends: metadb
*/


$ewiki_t["en"]["SISTER"] = "SisterPages exist as ";


$ewiki_plugins["handler"][] = "ewiki_virtual_sisterpages";
function ewiki_virtual_sisterpages($id, &$data, $action) {

    global $ewiki_metadb;

    
    #-- load metadb, inject URLs into $ewiki_links
    if (ewiki_metadb::LOAD()) {

       #-- search for alternatives
       if ($alt = $ewiki_metadb[strtolower($id)]) {

          $virt = "";
          $real = $alt[0];
          foreach ($alt[1] as $iw) {
             if (!strpos($data["content"], "$iw:$real")) {
                $virt .= "* $iw:$real\n";
             }
          }
          
          if ($virt) {
             $data["content"] .= "\n\n" . ewiki_t("SISTER") . "\n" . $virt . "\n";
          }
       }
       
       ewiki_metadb::UNLOAD();
    }
}


?>