<?php

/*
   If the _APPENDONLY page flag is set, users can further only append
   text to a page (and not edit the site as whole), while moderators and
   admins always can.
*/


$ewiki_plugins["edit_hook"][] = "ewiki_edit_hook_appendonly";
$ewiki_plugins["edit_save"][] = "ewiki_edit_save_appendonly";




function ewiki_edit_hook_appendonly($id, &$data, &$hpdata) {

   global $ewiki_t, $ewiki_ring;
   
   if ((($data["flags"] & EWIKI_DB_F_ACCESS) == EWIKI_DB_F_APPENDONLY) && (!EWIKI_PROTECTED_MODE || ($ewiki_ring >= 2))) {
      if ($data["version"] && strlen($data["content"])) {
         if (!$_REQUEST["content"] || !$_REQUEST["version"]) {

            #-- only show the editable part in the edit box
            $data["content"] = "----\n\n";

            #-- change "edit" title to "append"
            foreach (array_keys($ewiki_t) as $LANG) {
               if ($ewiki_t[$LANG]["APPENDTOPAGE"]) {
                  $ewiki_t[$LANG]["EDITTHISPAGE"] = &$ewiki_t[$LANG]["APPENDTOPAGE"];
               }
            }

         }
      }  
   }
}



function ewiki_edit_save_appendonly(&$save, &$old) {

   global $ewiki_ring;

   if ((($old["flags"] & EWIKI_DB_F_ACCESS) == EWIKI_DB_F_APPENDONLY) && (!EWIKI_PROTECTED_MODE || ($ewiki_ring >= 2))) {
      if ($old["version"] && ($old_len=strlen($old_str=&$old["content"]))) {

         #-- add newlines and a horizontal bar
         $old_str = rtrim($old_str);
         if (substr($old_str, $old_len-5, 4) == "----") {
            $old_str = rtrim(rtrim($old_str, "-"));
         }
         $old_str .= "\n\n";
         if (!preg_match("/^\n*----/", $save["content"])) {
            $save["content"] = "----\n\n" . $save["content"];
         }

         #-- merge the old not-editable-part with the new append-part
         $save["content"] = $old["content"]
                          . "\n\n"
                          . $save["content"];

      }
   }
}



?>