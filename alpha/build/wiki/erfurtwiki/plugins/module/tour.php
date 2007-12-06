<?php

/*
   Provides a fancy "quick view" (fragments) of the current and all
   associated (linked and backlinked) pages.

   - idea borought from a PhpWiki: discussion.
   - obviously needs improvement (all links should be tour:Links ?)
*/



$ewiki_plugins["action"]["tour"] = "ewiki_tour";
$ewiki_config["action_links"]["view"]["tour"] = "PageTour";
$ewiki_config["action_links"]["tour"]["view"] = "ViewFullPage";
$ewiki_config["action_links"]["tour"]["links"] = "BACKLINKS";
$ewiki_config["action_links"]["tour"]["info"] = "PAGEHISTORY";


function ewiki_tour($id, &$data, $action) {

   $o = "\n";

   $page_lists = array(
      array($id),
      explode("\n", trim($data["refs"])),
      ewiki_get_backlinks($id),
   );

   foreach ($page_lists as $pages) {
      foreach ($pages as $page) {

         $row = ewiki_db::GET($page);
         if (EWIKI_DB_F_TEXT == $row["flags"] & EWIKI_DB_F_TYPE) {

            $add = substr($row["content"], 0, 333);
            $add = substr($add, 0, strrpos($add, " "));
            $add = preg_replace("/^[!*#-:;>]+/m", "", $add);
            $add = strtr($add, "\n\t", "  ");

            $o .= "!!! [tour:{$row[id]}]\n"
                . "@@tour-page-fragment $add ...\n\n";
         }
      }

   }

   $o = ewiki_format($o);
   $o .= ewiki_control_links($id, $data, $action);
   return($o);
}


?>