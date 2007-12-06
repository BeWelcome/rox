<?php

# prints out the list of known InterWiki:ShortCuts
# (using a <dl>)


$ewiki_plugins["page"]["InterWikiMap"] = "ewiki_page_interwikimap";


function ewiki_page_interwikimap($id, $data, $action) {

   global $ewiki_config;

   $o = ewiki_make_title($id, $id, 1);

   $o .= '<dl id="InterWikiMap">'."\n";
   foreach ($ewiki_config["interwiki"] as $shortcut=>$url) {
      $o .= "<dt>$shortcut:</dt>\n".
           "   <dd><a href=\"$url\">$url</a></dd>\n";
   }
   $o .= "</dl>";
   
   return($o);
}

?>