<?php

/*
 this _link_regex_callback() plugin scans for icons in a
 given directory and prepends it before all links generated
 by ewiki_format()
*/


$ewiki_plugins["link_final"][] = "ewiki_link_icons";

define("EWIKI_LINK_ICONS_DIR", "/icons/");		# absolute to www root
define("EWIKI_LINK_ICONS_LOOKUP_DIR", "./icons");	# to access the files
define("EWIKI_LINK_ICONS_LOOKUP_SIZE", 1);		# use getimagesize()
define("EWIKI_LINK_ICONS_DEFAULT_SIZE", 'width="14" height="14"');  # fallback

$ewiki_link_icons = array(
   "wikipage" => "",
   "notfound" => "bomb.png",
   "email" => "letter.gif",
   "binary" => "disk.jpeg",
   "http" => "www",
   "mailto" => "letter.gif",
   "ftp" => "disk.jpeg",
);



function ewiki_link_icons(&$html, $type, $href, $title) {

   global $ewiki_link_icons;

   ksort($type);
   foreach (array_reverse($type) as $probe) {

      if ($probe == "image") {
         return;
      }
      $probe = strtolower($probe);

      $test = array(
         $ewiki_link_icons[$probe],
         $probe,
         $probe.".png",
         $probe.".gif",
         $probe.".jpeg",
      );

      foreach ($test as $f) {

         if (strlen($f) && file_exists($fn2 = EWIKI_LINK_ICONS_LOOKUP_DIR . "/" . $f)) {

            if (EWIKI_LINK_ICONS_LOOKUP_SIZE && ($uu = @getimagesize($fn2))) {
               $img_sizes = $uu[4];
            }
            else {
               $img_sizes = EWIKI_LINK_ICONS_DEFAULT_SIZE;
            }

            $img = '<img src="' . EWIKI_LINK_ICONS_DIR . $f . '" ' .
                    $img_sizes . ' alt="\'" border="0" />';

            $html = strtok($html, ">") . ">" . $img . strtok("\000");

            return;
         }

      }

   }

}


?>