<?php
/*
   Loads the page named "UsageHints" and randomly selects a
   paragraph to output.
*/

if (true) {
   if ($data = ewiki_db::GET("UsageHints")) {
   
      $hints = preg_split("/\n+(---+\s*)?\n+/", trim($data["content"]));
      $n = rand(0, count($hints)-1);
      $text = $hints[$n];
      
      if ($text) {
         echo ewiki_format($text);
      }
   }
}

?>