<?php

# This plugin adds a fancy (CSS) control link box to the top of each
# page, including and next (right) to the PageTitle. The old control
# line is disabled to achieve this.
# ... as seen on a system, which appeared to be some CVS frontend.

$ewiki_plugins["view_final"][] = "ewiki_print_control_line_fancy2";


function ewiki_print_control_line_fancy2(&$html, $id,$data,$action) {
   global $ewiki_plugins, $ewiki_t, $ewiki_config;

   #-- produce control links
   $cl = "";
   if (!empty($data["forced_version"])) {
      $cl = '<a href="'.ewiki_script("edit", $id,
            array("version"=>$data["forced_version"], "edit"=>"old")).
            '">'.ewiki_t("OLDVERCOMEBACK")."</a>";
   }
   else {
      foreach ($ewiki_config["action_links"]["view"] as $action => $title) if (!empty($ewiki_plugins["action"][$action])) {
         if (EWIKI_PROTECTED_MODE && EWIKI_PROTECTED_MODE_HIDING && !ewiki_auth($id, $data, $action)) {
             continue; 
         }
           $cl .= $ins[1] . '<a href="' .
              ( strpos($action, "://")
                 ? $action   # an injected "action" URL
                 : ewiki_script($action, $id, $version?array("version"=>$version):NULL)
              ) . '">[' . ewiki_t($title) . ']</a> ' . $ins[2];
      }
   }

   if ($data["lastmodified"] >= UNIX_MILLENNIUM) { 
      $cl .= '<br />' . strftime(ewiki_t("LASTCHANGED"), @$data["lastmodified"]);
   }

   #-- output
   $html = '<div class="controlbox float_right small">'.
           "$h2\n".
           '<div style="text-align:right">'.$cl.'</div>'.
           "</div>\n".
           $html;

}


?>