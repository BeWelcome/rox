<?php

/*
   This filter plugin converts pages content into all-uppercase, if someone
   edits a page and leaves at least one line of uppercase characters (a
   persistent cookie is set).
*/


define("EWIKI_UP_SCREAMOMATIC", "screamomatic");

$ewiki_plugins["edit_save"][] = "ewiki_edit_save_fun_screamomatic";
$ewiki_plugins["page_final"][] = "ewiki_page_final_fun_screamomatic";


function ewiki_edit_save_fun_screamomatic(&$save, &$old) {

   #-- count lines of yelling
   preg_match_all("/^[^a-z\340-\377_\n]{10,}$/m", $old["content"], $uu);
   $old_screaming = count($uu[0]);
   preg_match_all("/^[^a-z\340-\377_\n]{10,}$/m", $save["content"], $uu);
   $new_screaming = count($uu[0]);

   #-- trapped!
   if ($new_screaming > $old_screaming) {
      setcookie(EWIKI_UP_SCREAMOMATIC, "true", time()+7*24*3600, "/");
   }
}


function ewiki_page_final_fun_screamomatic(&$html, $id, &$data, $action) {
   if ($_COOKIE[EWIKI_UP_SCREAMOMATIC]) {
      $html = preg_replace('/>([^<>]+)</e',
              '">".strtoupper(stripslashes("\\1"))."<"', $html);
   }
}


?>