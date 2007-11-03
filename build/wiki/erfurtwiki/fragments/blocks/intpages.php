<?php

/*
   Just lists all the internal/dynamic page plugins, as seen
   in the default example-1 script.
*/


if (true) {

   foreach ($GLOBALS["ewiki_plugins"]["page"] as $pid=>$uu) {

      echo "· " . ewiki_script("", $pid) . "<br>\n";

   }
}


?>