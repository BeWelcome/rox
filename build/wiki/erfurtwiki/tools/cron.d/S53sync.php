<?php
/*
   This script simply calls the WikiSync backend for a given sync URL.
   It is probably rarely useful, and you should rather use the standalone
   frontend/tool for this.
*/

// define("SYNC_URL", "wikisync://example.net/ewiki/tools/t_sync.php");


#-- go
if (defined("SYNC_URL")) {

   $proto = "sync";
   $url = SYNC_URL;
   if (!function_exists("xmlrpc")) include("plugins/lib/xmlrpc.php");
   if (!function_exists("phprpc")) include("plugins/lib/phprpc.php");
   if (!function_exists("ewiki_sync_local")) include("plugins/lib/sync.php");

   #-- check connection
   if ($rlist = ewiki_sync_remote("::LIST")) {
      $locall = ewiki_sync_local("::LIST");
      echo "[$cron]: ".count($llocal)." pages here, ".count($rlist)." remotely\n";
      

      #-- 1
      echo "[$cron]: downloading from $url\n";
       ewiki_sync_start(
          "download",
          $rlist, $locall,
          "ewiki_sync_remote", "ewiki_sync_local"
       );

      #-- 2
      echo "[$cron]: uploading from $url\n";
       ewiki_sync_start(
          "upload",
          $llocal, $rlist,
          "ewiki_sync_local", "ewiki_sync_remote"
       );

      echo "[$cron]: done\n";
   }
   else {
      echo "[$cron]: No connection to $url\n";
   }

}


?>