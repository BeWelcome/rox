<?php

/*
   If you load this plugin, pages with names ending in "...Template"
   would be shown as <select> list, whenever a new page was to be
   created.
*/


$ewiki_plugins["edit_form_final"][] = "ewiki_aedit_templates";
$ewiki_plugins["edit_hook"][] = "ewiki_edit_load_template";


function ewiki_aedit_templates(&$html, $id, &$data, $action)
{
   if (!$data["content"]) {

      #-- search template pages
      $list = array();
      $result = ewiki_db::SEARCH("id","Template");
      while ($row = $result->get(0, 0x21, EWIKI_DB_F_TEXT)) {
         if (preg_match('/Template$/', $row["id"])) {
            $list[] = $row["id"];
         }
      }

      #-- add list
      if ($list) {
         $o = '<form action="'.ewiki_script("", $id).'" method="POST" entype="multipart/form-data">'
            . '<input type="hidden" name="id" value="'.$action.'/'.$id.'">'
            . '<input type="submit" value="load"> '
            . '<select name="load_template">';
         foreach ($list as $i) {
            $o .= '<option value="'.$i.'">'.$i.'</option>';
         }
         $o .= '</select></form>';

         #-- output
         $html = $o . $html;
      }
   }
}


function ewiki_edit_load_template($id, &$data, $action)
{
   if ($id = $_REQUEST["load_template"]) {
      $d2 = ewiki_db::GET($id);
      $data["content"] = $d2["content"];
   }
}


?>