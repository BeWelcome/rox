<?php
/*
   Inserts an <iframe> for a given URL (as url=, src=, href= or simply
   the first unnamed parameter, width= and height= must be given in
   conjunction and an alternative text may be included).
   
   <?plugin InlineFrame http://example.org/
      examplary inlines a non-existent page
   ?>
*/

$ewiki_plugins["mpi"]["inlineframe"] = "ewiki_mpi_inlineframe";

function ewiki_mpi_inlineframe($action, &$args, &$iii, &$s) {

   #-- get args
   ($url = $args["url"]) or ($url = $args["href"])
   or ($url = $args["src"]) or ($url = $args[0]);
   $width = htmlentities($args["width"]);
   $height = htmlentities($args["height"]);
   if ($l = strpos($args["_"], "\n")) {
      $alt = htmlentities(substr($args["_"], $l));
   }
   
   #-- return <iframe> tag / bare link
   $inj = ($height&&$width) ? " width=\"$width\" height=\"$height\"" :  "";
   $url = htmlentities($url);
   return
     "<iframe src=\"$url\" $inj>"
     ."<a href=\"$url\">$url</a>$alt"
     ."</iframe>";
}


?>