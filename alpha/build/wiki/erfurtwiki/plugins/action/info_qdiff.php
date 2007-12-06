<?php

/*
  provides the 'quickdiff' through multiple (all) page versions,
  available from the info/ page; depends upon 'stupid diff' module
*/

$ewiki_plugins["action"]["qdiff"] = "ewiki_action_info_qdiff";
$ewiki_config["action_links"]["summary"]["qdiff"] = "quick diff (history overview)";


function ewiki_action_info_qdiff($id, &$data, $action) {

   $CLK = "%c";
   $o = ewiki_make_title($id, "history of '$id'", 2);

   #-- walk through versions
   $prev = (array)$data;
   $ver = $data["version"] + 1;
   while ((--$ver) >= 2) {
   
      #-- get
      if ($d = ewiki_db::GET($id, $ver-1)) {
         $curr = $prev;
         $prev = $d;
         $d = NULL;
      }
      else {
         continue;
      }
      
      #-- info header
      $o .= '<table border="1">' . "\n" . '<tr class="qdiff-header"><td>'
         .  '<b><a href="' . ewiki_script("", $id, "version=$ver") . "\">version $ver</a></b>"
         . '</td><td>' . ewiki_author_html($curr["author"])
         . '</td><td>' . strftime($CLK, $curr["lastmodified"])
         . "</td></tr>\n";

      #-- diff part
      $diff = ewiki_stupid_diff($curr["content"], $prev["content"], $show_unchanged=0, $magic_notes=1);
      $o .= '<td colspan="3">' . $diff;
      $o .= "\n</td></tr>\n</table>\n<br />\n";

   }

   return($o);   
}


?>