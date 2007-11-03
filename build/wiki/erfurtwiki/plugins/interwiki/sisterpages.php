<?php
/*
   Puts links to same-named pages on foreign Wikis on top of every page.

   @feature: sisterpages
   @depends: metadb
*/


define("EWIKI_SISTER_ONLY_NONEXIST", 0);   // SisterPages only for edit/ screen
$ewiki_t["en"]["SISTER"] = "SisterPages exist as ";


$ewiki_plugins["page_final"][] = "ewiki_page_final_sisterpages";
function ewiki_page_final_sisterpages(&$o, $id, &$data, $action) {

    global $ewiki_metadb, $ewiki_links;
    
    #-- only on edit/ pages?
    if (EWIKI_SISTER_ONLY_NONEXIST && ($action!="edit")) {
       return;
    }

    #-- load metadb, inject URLs into $ewiki_links
    if (ewiki_metadb::LOAD()) {

       #-- search for alternatives
       if ($alt = $ewiki_metadb[strtolower($id)]) {

          $inj = array();
          $real = $alt[0];
          foreach ($alt[1] as $iw) {
             $href = ewiki_interwiki("$iw:$real");
             $inj[] = "<a href=\"$href\">$iw:$id</a>";
          }
          
          if ($inj) {
             $o = (($action!="edit") ? ewiki_t("SISTER") : "")
                . implode(", ", $inj)
                . "<br />\n" . $o;
          }
       }
       
       ewiki_metadb::UNLOAD();
    }
}


?>