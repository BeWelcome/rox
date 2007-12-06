<?php

/*
   Provides WikiFeatures:InterMapWalking, which will automatically
   redirect users to foreign Wikis, if an InterMap page name is
   detected.
   WARNING: This is already a core feature, don't load this plugin!
*/


$ewiki_plugins["handler"][] = "ewiki_intermap_walking";


function ewiki_intermap_walking($id, &$data, $action) {
   if (empty($data["version"]) && ($href = ewiki_interwiki($id, $uu))) {
      header("Location: $href");
      return("<a href=\"$href\">$href</a>");
   }
}


?>