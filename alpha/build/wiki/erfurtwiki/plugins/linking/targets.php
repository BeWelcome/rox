<?php

/*
   Using this plugin, you can utilize link target: settings from within
   wiki. You can either write [target:_parent:WikiWord] or of course
   also [[target=_blank:http://example.com/...]]. Between target and
   the window/frame name, both the colon and the equal sign are allowed.
*/

$ewiki_plugins["interxhtml"][] = "ewiki_interxhtml_target";


function ewiki_interxhtml_target($prefix, &$page, &$s) {
   global $ewiki_links;

   if (strtok($prefix, ":=") == "target") {
      $href_i = strtolower("$prefix:$page");
      if ($frame = strtok(":=")) {
         // ok
      }
      elseif ($frame = strtok($page, ":")) {
         $page = strtok("\000");
      }

      #-- set extra attribute
      if ($frame) {
         $s["xhtml"]["target"] = $frame;

         #-- shorten title
         //$s["title"] = substr($s["title"], strlen($prefix)+1+strlen($frame)+1);
      }
      $ewiki_links[$href_i] = 1;
      
   }
}


?>