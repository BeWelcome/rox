<?php
/*
   If this plugin is loaded, it automatically fetches the contents of
   "EditingHelp" and makes that display instead of the default editing
   note.
   
   That page can contain one or two text snippets (then separated by
   a horizontal line). Currently only works for English text.
*/


$ewiki_plugins["edit_hook"][] = "ewiki_edit_helptext";
function ewiki_edit_helptext($id, &$data, &$hpost) {
   global $ewiki_t;
   if ($help = ewiki_db::GET("EditingHelp")) {
      $help = ewiki_format( $help["content"] );
      if ($l = strpos($help, "<hr")) {
         $ewiki_t["en"]["EDIT_FORM_1"] = substr($help, 0, $l);
         $l = strpos($help, ">", $l);
         $help = substr($help, $l + 1);
      }
      $ewiki_t["en"]["EDIT_FORM_2"] = $help;
   }
}

?>