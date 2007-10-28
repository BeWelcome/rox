<?php

/*
   check for broken browsers
   (UTF8 problem)
*/



$ewiki_plugins["edit_hook"][] = "warn_utf8";


function warn_utf8($id, &$data, &$hidden_postdata) {

   if (empty($_REQUEST["warn_utf8"])) {
      $hidden_postdata["warn_utf8"] = "&#255;";
#echo "CharSet:C-EMPTY\n";
   }
   elseif ($_REQUEST["warn_utf8"] == "\377") {
      #-- perfect
      #  ISO-Latin1
#echo "CharSet:ISO-Latin1\n";
   }
   elseif ($_REQUEST["warn_utf8"] == "\303\277") {
      die("Your browser sent UTF8 where only ISO-8859-1 was allowed!");
   }
   else {
      die("Your browser sent an inacceptable charset where only ISO-8859-1 was allowed (completely broken)!");
   }
}


?>