<?php

# This plugin adds a fancy (CSS) control link box to the top of each
# page, including and next (right) to the PageTitle. The old control
# line is disabled to achieve this.
# ... as seen on a system, which appeared to be some CVS frontend.


define("EWIKI_CONTROL_LINE", 0);
define("EWIKI_PRINT_TITLE", 1);


$ewiki_plugins["view_final"][] = "ewiki_print_control_line_fancy2";


function ewiki_print_control_line_fancy2(&$html, $id,$data,$action) {
   global $ewiki_plugins, $ewiki_t;

   #-- extract <h2> with info/ link
   list($h2, $html) = explode("\n", $html, 2);

   #-- produce control links
   $cl = "";
   if (!empty($data["forced_version"])) {
      $cl = '<a href="'.ewiki_script("edit", $id,
            array("version"=>$data["forced_version"], "edit"=>"old")).
            '">'.ewiki_t("OLDVERCOMEBACK")."</a>";
   }
   else {
      foreach ($ewiki_config["action_links"]["view"] as $action => $title) if (!empty($ewiki_plugins["action"][$action])) {
         if (EWIKI_PROTECTED_MODE && (!ewiki_auth($uu, $uu, $action) || EWIKI_PROTECTED_MODE_HIDING && empty($ewiki_ring))) { continue; }
         $cl .= '<a href="'.ewiki_script($action,$id).'">'.$title.'</a> ';
      }
   }

   if ($data["lastmodified"] >= UNIX_MILLENNIUM) { 
      $cl .= '<br /><small>' . strftime(ewiki_t("LASTCHANGED"), @$data["lastmodified"]) . '</small>';
   }

   #-- change <h2>
   $h2 = str_replace("<h2>", '<h2 style="float:left; margin:5pt;">', $h2);

   #-- output
   $html = '<div class="controlbox" style="border:2px #333377 solid; background-color:#555599;">'.
           "$h2\n".
           '<div style="text-align:right;">'.$cl.'</div>'.
           "</div>\n".
           $html;

}


?>