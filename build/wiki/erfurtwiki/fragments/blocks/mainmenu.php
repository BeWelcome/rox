<?php

/*
   Gets the first list of links from the page "MainMenu" and outputs
   it. (verything else on that page gets stripped)
*/


if (true) {

   $mm = ewiki_db::GET("MainMenu");

   if ($mm = $mm["content"]) {
      $mm = preg_replace("/^([^*]+[^\n]+)?\n/m", "", $mm);
      $mm = ewiki_format($mm);
      echo '<div class="MainMenu">' .  "\n" . $mm . "\n</div>\n";
   }

}


?>