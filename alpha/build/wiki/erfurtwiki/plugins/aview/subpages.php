<?php

//General subpages display plugin, lists all pages of the form
// current_pagename.*

//Original code by AndyFundinger

$ewiki_plugins["view_append"][] = "ewiki_view_append_subpages";
$ewiki_t["en"]["SUBPAGES"]= "Subpages";

include_once("plugins/lib/subpages.php");

function ewiki_view_append_subpages($id, $data, $action, $title="SUBPAGES", $class="subpages") {

   $pages=ewiki_subpage_list($id);

   if(0==count($pages)){return("");}

   $o = '<div class="'.$class.'"><small>'.ewiki_t($title).":</small><br />";
   $o .= ewiki_list_pages($pages)."</div>\n";
   return($o);
}

?>