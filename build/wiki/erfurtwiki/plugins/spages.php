<?php

/*
   The StaticPages plugin allows you to put some .html or .php files
   into dedicated directories, which then will get available with their
   basename as ewiki pages. The files can be in wiki format (.txt or no
   extension), they can also be in .html format and they may even contain
   php code (.php). Some binary files may also be thrown into there, but
   you should use PathInfo or a ModRewrite setup then.

   Of course it is not possible to provide anything else, than viewing
   those pages (editing is not possible), but it is of course up to you
   to add php code to achieve some interactivity.
   The idea for this plugin was 'borought' from http://geeklog.org/.

   In your static page .php files you cannot do everything you could
   normally do, there are some restrictions because of the way these static
   pages are processed. You need to use $GLOBALS to access variables other
   than the $ewiki_ ones. To return headers() you must append them to the
   $headers[] or $ewiki_headers[] array.

   If you define("EWIKI_SPAGES_DIR") then this directory will be read
   initially, but you could also just edit the following list/array of 
   directories, or call ewiki_init_spages() yourself.
*/


#-- specify which dirs to search for page files
ewiki_init_spages(
   array(
      "spages",
      # "/usr/local/share/wikipages",
      # "C:/Documents/StaticPages/",
   )
);
if (defined("EWIKI_SPAGES_DIR")) {
   ewiki_init_spages(EWIKI_SPAGES_DIR);
}
define("EWIKI_SPAGES_BIN", 1);


#-- plugin glue
# - will be added automatically by _init_spages()


#-- return page
function ewiki_spage($id, &$data, $action) {

   global $ewiki_spages, $ewiki_plugins, $ewiki_t;

   $r = "";

   #-- filename from $id
   $fn = $ewiki_spages[strtolower($id)];

   #-- php file
   if (strpos($fn, ".php") || strpos($fn, ".htm")) {

      #-- start new ob level
      ob_start();
      ob_implicit_flush(0);

      #-- prepare environment
      global $ewiki_id, $ewiki_title, $ewiki_author, $ewiki_ring,
             $ewiki_t, $ewiki_config, $ewiki_action, $_EWIKI,
             $ewiki_auth_user, $ewiki_headers, $headers;
      $ewiki_headers = array();
      $headers = &$ewiki_headers;

      #-- execute script
      include($fn);

      #-- close ob
      $r = ob_get_contents();
      ob_end_clean();

      #-- add headers
      if ($ewiki_headers) {
         headers(implode("\n", $ewiki_headers));
      }
      $clean_html = true;
   }

   #-- plain binary file
   elseif (EWIKI_SPAGES_BIN && !headers_sent() && preg_match('#\.(png|gif|jpe?g|zip|tar)#', $fn)) {
      $ct = "application/octet-stream";
      if (function_exists("mime_content_type")) {
         $ct = mime_content_type($fn);
      }
      header("Content-Type: $ct");
      header("ETag: ewiki:spages:".md5($r).":0");
      header("Last-Modified: " . gmstrftime($ewiki_t["C"]["DATE"], filemtime($fn)));
      passthru($r);
   }

   #-- wiki file
   else {
      $f = gzopen($fn, "rb");
      $r = gzread($f, 256<<10);
      gzclose($f);

      #-- render as text/plain, text/x-wiki
      if ($r) {
         $r = $ewiki_plugins["render"][0]($r);
      }
   }

   #-- strip <html> and <head> parts (if any)
   if ($clean_html) {
      $r = preg_replace('#^.+<body[^>]*>(.+)</body>.+$#is', '$1', $r);
   }

   #-- return body (means successfully handled)
   return($r);
}



#-- init
function ewiki_init_spages($dirs, $idprep="") {

   global $ewiki_spages, $ewiki_plugins;

   if (!is_array($dirs)) {
      $dirs = array($dirs);
   }

   #-- go through list of directories
   foreach ($dirs as $dir) {

      if (empty($dir)) {
         continue;
      }

      #-- read in one directory
      $dh = opendir($dir);
      while ($fn = readdir($dh)) {

         #-- skip over . and ..
         if ($fn[0] == ".") { continue; }

         #-- be recursive
         if ($fn && is_dir("$dir/$fn")) {
            if ($fn != trim($fn, ".")) {
               $fnadd = trim($fn, ".") . ".";
            }
            else {
               $fnadd = "$fn/";
            }

            ewiki_init_spages(array("$dir/$fn"), "$idprep$fnadd");

            continue;
         }

         #-- strip filename extensions
         $id = str_replace(
                  array(".html", ".htm", ".php", ".txt", ".wiki", ".src"),
                  "",
                  basename($fn)
         );

         #-- register spage file and as page plugin (one for every spage)
         $ewiki_spages[strtolower("$idprep$id")] = "$dir/$fn";
         $ewiki_plugins["page"]["$idprep$id"] = "ewiki_spage";

      }
      closedir($dh);
   }

}



?>