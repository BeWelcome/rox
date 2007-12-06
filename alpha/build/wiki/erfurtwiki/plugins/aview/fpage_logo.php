<?php
/*
    Appends an image to the output string.
    
    Created by: Jeffrey Engleman
    
*/
//$ewiki_t["en"]["LOGOPATH"] = "path/to/logo.gif";
//$ewiki_t["en"]["LOGOALT"] = "Alt Logo Name";

$ewiki_plugins["page_final"][] = "ewiki_page_final_logo";

function ewiki_page_final_logo(&$o, &$id, &$data, &$action){
  $o.="<div id=\"bottomlogo\">\n<img src=\"".ewiki_t("LOGOPATH")."\" alt=\"".ewiki_t("LOGOALT")."\"/>\n</div>\n";
}

?>