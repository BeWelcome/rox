<?php
/*
   With this extension loaded you can inject a custom message on top
   of the edit box. You simply set it by giving an "edit: ...." entry
   within the meta data field (see plugins/meta/).
*/


$ewiki_plugins["edit_form_final"][] = "ewiki_meta_edit_message";
function ewiki_meta_edit_message(&$o, $id, &$data, $action) {
   if ($msg = $data["meta"]["meta"]["edit"][0]) {
      $o = "<div class=\"system-message\">$msg</div>\n"
         . $o;
   }
}


?>