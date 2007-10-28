<?php

/*
   this emits a randomly selected page
   if "RandomPage" is requested
*/


#-- glue, init
define("EWIKI_PAGE_RANDOMPAGE", "RandomPage");
$ewiki_plugins["page"][EWIKI_PAGE_RANDOMPAGE] = "ewiki_page_random";
srand(time()-microtime()*1000);


#-- redirection
function ewiki_page_random(&$id, &$data, $action) {

   global $ewiki_plugins;

   $result = ewiki_db::GETALL(array("flags"));
   while ($row = $result->get()) {
        if (EWIKI_PROTECTED_MODE && EWIKI_PROTECTED_MODE_HIDING && !ewiki_auth($row["id"], $uu, "view")) {
            continue;
        }   
        if (($row["flags"] & EWIKI_DB_F_TYPE) == EWIKI_DB_F_TEXT) {
            $pages[] = $row["id"];
        }
   }

   $pages = array_merge($pages, $ewiki_plugins["page"]);

   $n = rand(0, count($pages));
   $id = $pages[$n];

   return(ewiki_page($id));
}


?>