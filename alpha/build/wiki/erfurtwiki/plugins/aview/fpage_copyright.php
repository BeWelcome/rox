<?php
/*
    Generates a copyright notice.
    
    Created by: Jeffrey Engleman
    
*/

$ewiki_t["en"]["ALLRIGHTSRESERVED"] = "all rights reserved.";

$ewiki_plugins["page_final"][] = "ewiki_page_final_copyright";

function ewiki_page_final_copyright(&$o, &$id, &$data, &$action){
    if(isset($data["lastmodified"])){
       $o.="<div id=\"copyright\">\nCopyright &copy; ".strftime("%B %d, %Y",$data["lastmodified"])." ".ewiki_t("ALLRIGHTSRESERVED")."\n</div>";
    }else {
       $o.="<div id=\"copyright\">\nCopyright &copy; ".strftime("2004")." ".ewiki_t("ALLRIGHTSRESERVED")."\n</div>";
    }
}

?>