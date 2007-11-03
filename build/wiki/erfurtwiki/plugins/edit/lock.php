<?php
/*
   Places an more intrusive warning message in front of the edit screen,
   if someone else activated the edit box recently (as determined by lock
   file). An unlock button will be present to override it (spiders may
   activate the locking feature).
   This is a poor replacement for the 'patchsaving' extension, and you
   should normally rather use plugins/edit/warn instead of the lock
   extension (both use same lock files). Needs EWIKI_TMP set correctly.
   
   @feature: edit-lock
   @title: concurrent edit locking
   @desc: like the edit-warn extensions, this could be used where 'patchsaving' didn't work
*/

$ewiki_plugins["edit_hook"][] = "ewiki_edit_lock";

function ewiki_edit_lock($id, &$data, $action) {

   $keep = 500;  // in seconds
   $o = "";

   #-- lock dir
   if (!file_exists($dir = EWIKI_TMP."/edit.d/")) {
      mkdir($dir);
   }
   
   #-- check file
   $lockfile = $dir . ewiki_lowercase($id) . ".lock";
   $time = 0;
   if (file_exists($lockfile)) {
      $time = filemtime($lockfile);
   }

   #-- force
   if ($_REQUEST["edit_unlock"]) {
      unlink($lockfile);
      $time = -1;
   }

   #-- automatic unlock on save
   elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
      @unlink($lockfile);
   }

   #-- checking
   else {
      if ($time + $keep > time()) {
         $o = ewiki_t("<p class=\"system-message\"><b>_{Warning}</b>:"
            . " _{This page is currently being edited by someone else},"
            . " _{and therefore locked currently}."
            . " "
            . '<form action="'.$_SERVER[REQUEST_URI].'" method="POST">'
            . '<input type="id" name="'."$action/$id".'">'
            . '<input type="submit" name="edit_unlock" value="_{unlock}">'
            . '</form>'
            . "</p>\n");
      }
      elseif ($time) {
         // unlink($lockfile);
         touch($lockfile);
      }
      else {
         touch($lockfile);
      }
   }

   return($o);
}

?>