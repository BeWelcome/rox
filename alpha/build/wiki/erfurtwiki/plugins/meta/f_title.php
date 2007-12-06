<?php

/*
   Evaluates the "title: " given in the {meta}{meta} field and uses
   this for the current page.
*/

$ewiki_plugins["handler"][] = "ewiki_meta_f_title";
function ewiki_meta_f_title($id, &$data, $action) {
   global $ewiki_title;
   if ($t = @$data["meta"]["meta"]["title"]) {
      $ewiki_title = htmlentities($t);
   }
}

?>