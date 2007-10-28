<?php

/*
   Enable this plugin to get rid of the "md5md5md5md5md5" in uploaded
   images` storage/filenames. It can automatically discard frivolous
   names like "test0.gif" and "DSC00001.jpg" and provides the md5-name
   as fallback anyhow.

   This plugin must be included() BEFORE the binary_store plugin (if
   that one was enabled)!
*/



$ewiki_plugins["binary_store"][] = "ewiki_imgupload_better_fn";


function ewiki_imgupload_better_fn(&$fn, &$id, &$meta, &$ext) {

   $parent = $_REQUEST[EWIKI_UP_PARENTID];
   $name = $meta["Content-Location"];
   $bad_names = '/^(DSC.?0\d+|test|bild.?\d+|pic.?\d+|image.?\d+|img.?\d+)/i';

   #-- normalize desired name (discard path/ and old .extension)
   $name = substr($name, strrpos($name, "/\\"));
   $name = substr($name, 0, strrpos($name, "."));
   $name = preg_replace('/[^-_+.\w\d]+/', "_", $name);

   #-- filter names
   if (preg_match($bad_names, $name)) {
      $name = "";
   }
   elseif (strlen($name) < 5) {
      $name = "";
   }

   #-- check if wish name is free
   if ($name) {
      $name = $name . ".$ext";
      $found = ewiki_db::FIND(array($name));
      if ($found[$name]) {
         $name = "";  // no, is used
      }
   }

   #-- else use page name as base
   if (!$name && $parent) {
      for ($n=1; $n++; $n<=99) {
         $name = "$parent.$n.$ext";
         $found = ewiki_db::FIND(array($name));
         if ($found[$name]) {
            $name = "";   // name is already occoupied
         }
         else {
            break;    // done
         }
      }
   }

   #-- assign new internal:// name
   if ($name) {
      $id = EWIKI_IDF_INTERNAL . $name;
   }
   else {
      // (it else gets a md5md5md5md5-sum as name)
   }
}


?>