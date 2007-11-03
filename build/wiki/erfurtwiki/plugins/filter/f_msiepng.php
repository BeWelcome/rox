<?php

/*
   This plugin enables the RedirectX workaround for M$IE 5.5+ browsers, to
   make the alpha channel of .png graphics work. You must first set the
   name of the 1-pixel-transparent gif graphic!
*/


define("FIX_MSIE_NULL_GIF", "/img/null.gif");


$ewiki_plugins["view_final"][] = "fix_msie_png";


function fix_msie_png(&$html) {
   $ua = $_SERVER["HTTP_USER_AGENT"];
   if (strstr($ua, "MSIE") && !strstr($ua, "Opera")
       && (strstr($ua, "5.5") || strstr($ua, "6.0"))
      )
   {
      $html = preg_replace(
         '/(<img[^>]*?)\ssrc="([^">]+?png[^>"]*)"([^>]*?>)/i',
         '$1 src="'.FIX_MSIE_NULL_GIF.'" style="filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\'$2\', sizingMethod=\'scale\')"$3',
         $html
      );
   }
}


?>