<?php

/*
  allows to diff freely chooseable page versions
*/


#-- glue
$ewiki_plugins["action"]["verdiff"] = "ewiki_action_verdiff";
$ewiki_config["action_links"]["view"]["verdiff"] = "verdiff";


#-- impl
function ewiki_action_verdiff($id, $data, $action) {

   global $ewiki_plugins, $ewiki_diff_versions;

   if (($v0 = (int)$_REQUEST["from"]) && ($v1 = (int)$_REQUEST["to"])) {

      $ewiki_diff_versions = array($v0, $v1);

      return($ewiki_plugins["action"]["diff"]($id, $data, $action));

   }
   else {

      $o = ewiki_make_title($id, "$id version differences");

      $o .= '<form action="' . ewiki_script($action, $id) . '" method="GET">';
      $o .= '<input type="submit" value="diff">';
      $o .= '<input type="hidden" name="id" value="'.$action."/".htmlentities($id).'">';

      $o .= "\n".'<table border="1" class="diff"><tr>'
         .  "<th>from</th> <th>to</th> <th>version</th> <th>mtime</th> "
         .  "<th>size</th> <th>author</th></tr>\n";

      for ($n=$data["version"]; $n>=1; $n--) {

         $data = ewiki_db::GET($id, $n);
         if (EWIKI_DB_F_TEXT == ($data["flags"] & EWIKI_DB_F_TYPE)) {

            $o .= "<tr>"
               .  '<td><input type="radio" name="from" value="'.$n.'"></td>'
               .  '<td><input type="radio" name="to" value="'.$n.'"></td>'
               .  "<td>#$n</td>"
               .  "<td>".strftime("%Y-%m-%d %H:%M",$data["last_modified"])."</td>"
               .  "<td>".strlen($data["content"])."</td>"
               .  "<td>".$data["author"]."</td>"
               .  "</tr>\n";

         }
      }

      $o .= "</table>\n";
      $o .= "</form>\n";

   }

   return($o);
}


?>