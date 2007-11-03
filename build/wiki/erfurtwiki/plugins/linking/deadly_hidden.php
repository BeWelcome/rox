<?php
/*
   Links to _HIDDEN pages will appear as QuestionMarkLinks (not-existant
   pages) if this plugin is loaded.
*/

$ewiki_plugins["format_prepare_linking"][] = "ewiki_deadly_hidden_pages";
function ewiki_deadly_hidden_pages(&$src) {

   global $ewiki_links;
   
   foreach ($ewiki_links as $id=>$v) {
      if (is_array($v) && ($v["flags"]&EWIKI_DB_F_HIDDEN) || ($v&EWIKI_DB_F_HIDDEN)) {
         $ewiki_links[$id] = 0;
   }  }
}

?>