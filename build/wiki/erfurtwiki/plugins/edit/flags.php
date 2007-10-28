<?php
/*
   provides users the ability to set certain page flags on editing;
   for example the _HIDDEN or _MINOR flags could be set (though there
   is a separate plugin for minor edits)
*/

$ewiki_config["user_flags"] = array(
   EWIKI_DB_F_MINOR => "minor edit",
   EWIKI_DB_F_HIDDEN => "hidden page",
#  EWIKI_DB_F_HTML => "html is allowed",
);
$ewiki_plugins["edit_form_append"][] = "ewiki_edit_user_flags";
$ewiki_plugins["edit_save"][] = "ewiki_edit_save_user_flags";

$ewiki_t["de"]["minor edit"] = "kleine Änderung";
$ewiki_t["de"]["hidden page"] = "versteckte Seite";


function ewiki_edit_save_user_flags(&$save, &$old) {
   global $ewiki_config;

   foreach ($ewiki_config["user_flags"] as $FLAG=>$str) {
      $save["flags"] = $save["flags"] & (0xFFFF ^ $FLAG)
        | ($_REQUEST["page_user_flag"][dechex($FLAG)] ? $FLAG : 0x00);
   }
}


function ewiki_edit_user_flags($id, &$data, $action) {
   global $ewiki_config;
   
   $o = "";
   foreach ($ewiki_config as $FLAG => $str) {
      $o .= '<input type="checkbox" name="page_user_flag['.dechex($FLAG).']" value="1"'
         . (($FLAG != EWIKI_DB_F_MINOR) && ($data["flags"] & $FLAG) ? " checked" : "")
         . ' id="page_user_flag_'.$FLAG.'"><label for="page_user_flag_'.$FLAG.'"> '
         . ewiki_t($str) . '</label><br />' . "\n";
   }
   return($o);
}


?>