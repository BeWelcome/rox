<?php

/*
   This plugin provides the virtual "CreatePage" and "AddNewPage" pages to
   allow immediately adding of a new page. It also automatically inserts
   a link to the new page from another (pre-defined) Root page, so the
   Wiki won't end up with lots of not interconnected pages (which used
   to be the drawback of a plugin like this).
*/


$ewiki_plugins["page"]["AddNewPage"] = "ewiki_addpage";
$ewiki_plugins["page"]["CreatePage"] = "ewiki_addpage";
$ewiki_plugins["page"]["EineSeiteHinzufügen"] = "ewiki_addpage";

$ewiki_t["de"]["name of the new page"] = "Name der neuen Seite";
$ewiki_t["de"]["link it from"] = "verlinken von";
$ewiki_t["de"]["create"] = "erstellen";
$ewiki_t["de"]["AddedPages"] = "HinzugefügteSeiten";


function ewiki_addpage($id, &$data, $version) {

   $o = ewiki_make_title($id, $id, 2);

   #-- output page creation dialog
   if (empty($_REQUEST["new_id"])) {

      $o .= ewiki_t(
         '<form action="'.ewiki_script("",$id).'" method="POST" enctype="multipart/formdata"> '
         .'_{name of the new page} <input type="text" name="new_id" size="26" value="">'
         .'<br />'
         .'<input type="submit" value="_{create}">'
         .'<br /><br />'
         .'<input type="checkbox" name="add_link_from" value="1" checked="checked">'
         .' _{link it from} '
         .'<input type="text" name="link_from" size="20" value="_{AddedPages}">'
         .'</form>'
      );

   }
   else {
      $new_id = trim($_REQUEST["new_id"]);

      #-- add a link to new page
      if ($_REQUEST["add_link_from"] && ($from = $_REQUEST["link_from"])) {
         $row = ewiki_db::GET($from);
         if ($row && $row["version"]) {
            if (($row["flags"] & EWIKI_DB_F_TYPE) == EWIKI_DB_F_TEXT) {
               $row["version"]++;
               $row["content"] .= "\n* [$new_id]";
               ewiki_scan_wikiwords($row["content"], $row["refs"], "_STRIP_EMAIL=1");
               $row["refs"] = "\n\n".implode("\n", array_keys($row["refs"]))."\n\n";
            }
            else {
               $row = false;
            }
         }
         else {
            $row = array(
               "id" => $from,
               "version" => 1,
               "flags" => EWIKI_DB_F_TEXT,
               "created" => time(),
               "lastmodified" => time(),
               "hits" => 0,
               "meta"=>"",
               "content" => "\n* [$new_id]",
               "refs" => "\n\n$new_id\n\n",
            );
         }
         if ($row) {
            ewiki_db::WRITE($row);
         }
      }

      #-- call edit <form>
      $o = ewiki_page($new_id);
   }

   return($o);
}

?>