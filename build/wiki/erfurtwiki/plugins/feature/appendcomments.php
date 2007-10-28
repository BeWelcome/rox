<?php

/*
   If you activate this plugin, then additions for pages with the
   _DB_F_APPENDONLY flag will get saved into a secondary page with a
   name like "CurrentPage/Comments" if an ordinary user attempts to
   edit the "CurrentPage" (which remains editable for moderators and
   admins).
   This plugin has the drawback, that moderators cannot edit the
   /Comments page part for brevity - they first needed to log out
   (a drawback of the _DB_F_PART type setting for the /Comments part).

   Warning: this is hack in progress!
*/


$ewiki_plugins["edit_hook"][] = "ewiki_edit_hook_appendcomments";
$ewiki_plugins["edit_save"][] = "ewiki_edit_save_appendcomments";
$ewiki_plugins["handler"][] = "ewiki_aview_appendcomments";

define("EWIKI_APPENDONLY_COMMENTSPART", "/Comments");



/*
   Add the /Comments entries contents to the current pages
   $data["content"] for rendering.
*/
function ewiki_aview_appendcomments($id, &$data, $action) {

   if (($action=="view") && ($data["flags"] & EWIKI_DB_F_APPENDONLY)) {

      #-- fetch /Comments part
      $c_id = $id.EWIKI_APPENDONLY_COMMENTSPART;
      $row = ewiki_db::GET($c_id);

      #-- add to current pages content
      if ($row && (($row["flags"] & EWIKI_DB_F_TYPE) == EWIKI_DB_F_PART|EWIKI_DB_F_TYPE)) {
         $data["content"] .= "\n" . $row["content"];
      }
   }
}


/*
   swaps currently edit/ed pages content with that from the /Coments
   page part
*/
function ewiki_edit_hook_appendcomments(&$id, &$data, &$hpdata) {

   global $ewiki_t;

   if ($data["flags"] & EWIKI_DB_F_APPENDONLY) {
      if (!EWIKI_PROTECTED_MODE || ($ewiki_ring >= 2)) {

         #-- fill edit box with contents from "$id/Content" page
         $c_id = $id.EWIKI_APPENDONLY_COMMENTSPART;
         $data = ewiki_db::GET($c_id);
         if (!$data["version"]) {
            $data = array(
               "id" => $c_id,
               "version" => NULL,
               "flags" => EWIKI_DB_F_PART|EWIKI_DB_F_TEXT,
               "created" => time(),
               "lastmodified" => time(),
               "hits" => 0,
               "meta" => array("parent" => $id),
               "content" => "",
               "refs" => "",
            );
         }
         $data["flags"] = EWIKI_DB_F_TEXT;   # another ugly workaround

         #-- change "edit" title to "append"
         foreach (array_keys($ewiki_t) as $LANG) {
            if ($ewiki_t[$LANG]["APPENDTOPAGE"]) {
               $ewiki_t[$LANG]["EDITTHISPAGE"] = &$ewiki_t[$LANG]["APPENDTOPAGE"];
            }
         }

      }
   }
}


/*
   manages ewiki_edit_page() to store the currently edited /Comments
   part back into the correct database entry (and not the main page)
*/
function ewiki_edit_save_appendcomments(&$save, &$old) {

   if ($old["meta"]["parent"] == $save["id"]) {
      if (!EWIKI_PROTECTED_MODE || ($ewiki_ring >= 2)) {

         #-- transform $save entry into "/Comments" page request
         $save["id"] = $old["id"];
         $save["flags"] = EWIKI_DB_F_TEXT|EWIKI_DB_F_PART;
         $save["created"] = $old["created"];
         $save["hits"] = $old["hits"];
         $save["meta"] = $old["meta"];
         # leaving as is: content, refs, author, lastmodified

      }
   }
}



?>