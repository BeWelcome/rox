<?php
/*
   Puts a warning message above the edit box, if someone else activated
   the edit screen recently (spiders often interfer with this). This is
   a poor replacement for the 'patchsaving' extension (see ../feature/).
   Needs EWIKI_TMP correctly set.
   
   @feature: edit-warn
   @title: concurrent edit warning
   @desc: if you cannot use 'patchsaving' you should at least warn people if pages are edited concurrently
*/

$ewiki_plugins["edit_form_final"][] = "ewiki_edit_warn";

function ewiki_edit_warn(&$o, $id, &$data, $action) {

   $keep = 420;  // in seconds

   if (!file_exists($dir = EWIKI_TMP."/edit.d/")) {
      mkdir($dir);
   }

   $lockfile = $dir . ewiki_lowercase($id) . ".lock";
   $time = 0;
   if (file_exists($lockfile)) {
      $time = filemtime($lockfile);
   }
   
   if ($_SERVER["REQUEST_METHOD"] == "POST") {
      @unlink($lockfile);
   }
   elseif ($time + $keep > time()) {
      $o = ewiki_t("<p class=\"system-message\"><b>_{Warning}</b>:"
         . " _{This page is currently being edited by someone else}."
         . " _{If you start editing now, your changes may get lost}."
         . "</p>\n")
         . $o;
   }
   elseif ($time) {
      // unlink($lockfile);
      touch($lockfile);
   }
   else {
      touch($lockfile);
   }

}

?>