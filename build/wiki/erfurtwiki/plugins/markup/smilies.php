<?php

/*
   You can embed smilies into pages, if you load this plugin. Only the
   location and URL to the images must be configured, the directory is
   read in at initialization.
   Images with a textual name then can be referenced as   :word:
   from within any WikiPage, and ordinary smilies can be written as is
   and get replaced by their graphical counterpart (if there is one).

   A prepared "_smilies.tar.gz" package is available from
   http://erfurtwiki.sourceforge.net/downloads/contrib-add-ons/
   Just untar it in the ewiki main directory, due to the 'special'
   characters this however only works on Unix filesystems.
*/


define("SMILIES_DIR", "./img/smilies/");
define("SMILIES_BASE_HREF", "/img/smilies/");
$ewiki_config["smilies"] = array(
   ":)" => "cap.gif",
);


$ewiki_plugins["format_final"][] = "smilies_format_final";

function smilies_format_final(&$html) {

   global $ewiki_config;

   #-- read in directories
   static $imgs, $regex;
   if (!isset($imgs)) {
      $imgs = array();
      $regex = array();
      foreach (smilies_dir(SMILIES_DIR) as $fn) {
         #-- check for file name extensions
         if (($r = strrpos($fn, ".")) >= 2) {
            $id = substr($fn, 0, $r);
            if ($r = strrpos($fn, "/")) {
               $id = substr($fn, $r+1);
            }
            $id0 = $id[0];

            #-- word images
            if (($id0>="a") && ($id0<="z") || ($id0>="0") && ($id0<="9")) {
               $id = ":$id:";
            }
            else {
               $id = htmlentities($id);
            }

            #-- decoding, encoding
            if ((strpos($id, "%")!==false) && ($uu = urldecode($id))) {
               $id = $uu;
            }
            $imgs[$id] = $fn;
            $regex[] = preg_quote($id);
         }
      }

      #-- append default images and aliases
      foreach ($ewiki_config["smilies"] as $id=>$fn) {
         $imgs[$id] = $fn;
         $regex[] = preg_quote($id);
      }

      $regex = implode("|", $regex);
   }
#print_r($imgs);
#print_r($regex);

   #-- use regex to insert <img> tags
   if ($imgs) {
      $html = preg_replace(
         '/(?!<[^>]*)('.$regex.')/e',
         '
            "<img src=\"" .
            SMILIES_BASE_HREF . urlencode(stripslashes($imgs["$1"])) .
            "\" alt=\"" . htmlentities("$1") . "\" />"
         ',
         $html
      );
   }
}


function smilies_dir($dir, $prep="") {
   $r = array();
   if ($dh = opendir($dir)) {
      while ($fn = readdir($dh)) {
         if (is_dir("$dir/$fn")) {
            if ($fn[0] != ".") {
               $r = array_merge($r, smilies_dir("$dir/$fn", "$fn/"));
            }
         }
         else {
            $r[] = "$prep$fn";
         }
      }
      closedir($dh);
   }
   return($r);
}

?>