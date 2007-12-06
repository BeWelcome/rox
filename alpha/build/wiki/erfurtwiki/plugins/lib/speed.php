<?php

/*
   *** STILL BROKEN ! ***

   This plugin implements some speed-ups for the HTTP protocol (used by
   most proxies and all browsers maintaining their own cache). The
   rendering of the current page will then be aborted, if this plugin
   detects it is unnecessary, because a client told so.

   It can compare against "ETags" and "Last-Modified" values, and aborts
   further ewiki processing early, if one of these conditions matches, and
   also terminates ewiki for HEAD method requests (because no output is
   required then).

   This will only take effect, if you disable EWIKI_NOCACHE (set it to 0).
   (Should we output the "*: no-cache" headers conditionally? - how?)
*/


#-- plugin glue
$ewiki_plugins["handler"][] = "ewiki_speed_abort";


#-- implementation
function ewiki_speed_abort($id, &$data, $action) {

   $o = "";  // unused here
   $inverse = 0;
   $yes = 0;
   $precond = 0;

   #-- ETag comparisions
   if (($if=$_SERVER["HTTP_IF_MATCH"]) || ($if=$_SERVER["HTTP_IF_NONE_MATCH"]) && ($inverse=1) ) {

      ($data["version"])
      and ($etag = ewiki_etag($data))
      or ($etag = "never:matching:".time());

      #-- walk through comparison values
      foreach (explode(",", $if) as $match) {
         $match = trim(trim($match), '"');
         if (($match == "*") || ($match == $etag)) {
            $yes = 1;
         }
         $precond = 1;
      }

   }

   #-- check against modification time
   if (($if=$_SERVER["HTTP_IF_MODIFIED_SINCE"]) || ($if=$_SERVER["HTTP_IF_UNMODIFIED_SINCE"]) && ($inverse=1)) {

      ($modif = $data["lastmodified"])
      or ($modif = UNIX_MILLENNIUM);

      $if = strtotime(trim($if));

      if ($modif > $if) {
         $yes = 1;
      }
   }

   #-- invert result
   if ($inverse) {
     $yes = $yes ? 0 : 1;
   }

   #-- abort ewiki rendering, if matched or senseful to do so
   if ($yes || ($_SERVER["REQUEST_METHOD"] == "HEAD")) {
      /*      
         #ewiki_http_headers($o, $id, $data, $action);
         (It was probably bad to send the ETag and Content-Version fields
         for this http answer?)
      */
      header("Status: 304 Not Modified");
      die(304);
   }
   elseif ($precond) {
      if (!$inverse || ($_SERVER["REQUEST_METHOD"]!="GET")) {
         header("Status: 412 Precondition Failed");
         die(412);
      }
   }

}


?>