<?php

/*
   If a page has the _APPENDONLY and _WRITEABLE flags set, then this plugin
   allows users to edit anything after the 16+-minus-signs or double
   horizontal bar. Moderators and admins can however still edit the full
   page.
   (One could also call this plugin the "PartialLock support".)
*/

define("EWIKI_APPENDWRITE_AUTOLOCK", 1);

$ewiki_plugins["handler"][] = "ewiki_handler_appendwrite";
$ewiki_plugins["edit_hook"][] = "ewiki_edit_hook_appendwrite";
$ewiki_plugins["edit_save"][] = "ewiki_edit_save_appendwrite";



/* 
   merges the double horizontal bar,
   this was too late for ["format_source"]
*/
function ewiki_handler_appendwrite($id, &$data, $action) {

   define("EWIKI_DB_F_APPENDWRITE", EWIKI_DB_F_APPENDONLY|EWIKI_DB_F_WRITEABLE);

   if (($action=="view") && ($data["flags"] & EWIKI_DB_F_APPENDWRITE)) {
      $data["content"] = preg_replace("/----\n----/", "--------", $data["content"], 1);
   }
}



/*
   searches for the page separator and returns its position,
   and eventually adds one
*/
function ewiki_appendwrite_split(&$content) {
   
   if ($end = strpos($content, "----------------")) {
      $end += 10;
   }
   if ($end1 = strpos($content, "----\n----")) {
      $end1 += 5;
      if ($end1 < $end) {
         $end = $end1;
      }
   }

   if ($end !== false) {
      if (!($end = strpos($content, "\n", $end))) {
         $content .= "\n";
         $end = strlen($content);
      }
   }
   elseif (EWIKI_APPENDWRITE_AUTOLOCK) {
      $content .= "\n----------------\n\n";
      $end = strlen($content);
   }

   return($end);
}


/*
   makes users only see the editable part in
   edit/ pages
*/
function ewiki_edit_hook_appendwrite($id, &$data, &$hpdata) {

   global $ewiki_t, $ewiki_ring;
   
   if (($data["flags"] & EWIKI_DB_F_APPENDWRITE) && (!EWIKI_PROTECTED_MODE || ($ewiki_ring >= 2))) {
      if ($data["version"] && strlen($data["content"]) && !$_REQUEST["content"]) {
         if ($end = ewiki_appendwrite_split($data["content"])) {

            #-- only show the editable part in the edit box
            $data["content"] = substr($data["content"], $end);

            # change "edit" title to "append"
            foreach (array_keys($ewiki_t) as $LANG) {
               if ($ewiki_t[$LANG]["APPENDTOPAGE"]) {
                  $ewiki_t[$LANG]["EDITTHISPAGE"] = &$ewiki_t[$LANG]["APPENDTOPAGE"];
               }
            }
   }  }  }
}



/*
   merges the not-editable part, with the submitted
   changes to the appendonly/writable part
*/
function ewiki_edit_save_appendwrite(&$save, &$old) {

   global $ewiki_ring;

   if (($old["flags"] & EWIKI_DB_F_APPENDWRITE) && (!EWIKI_PROTECTED_MODE || ($ewiki_ring >= 2))) {
      if ($old["version"] && strlen($old["content"])) {
         if ($end = ewiki_appendwrite_split($old["content"])) {

            #-- merge the old not-editable-part with the new append-part
            $save["content"] = substr($old["content"], 0, $end+1)
                             . $save["content"];

   }  }  }
}



?>