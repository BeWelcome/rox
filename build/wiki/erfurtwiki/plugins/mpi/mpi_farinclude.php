<?php
/*
   A plugin call like <?plugin FarInclude http://example.org/doc.htm ?>
   rapes the text/html body from the given URL and inserts this into
   the current page. You could also specify (Perl-) regular expressions
   to break the wanted content out with (case-insensitive):
    <?plugin  FarInclude  url=...  start="<b><i>"  end="<\/b>"  ?>
   Where quotation marks must be escaped with backslashes, if you need
   them, and the borders won't be included of course.

   This can be used in conjuction with WikiFeatures:SnippetPublishing
   and ewikis htm/ action -> "http://erfurtwiki.sf.net/htm/SandBox".
*/


$ewiki_plugins["mpi"]["farinclude"] = "ewiki_mpi_farinclude";
function ewiki_mpi_farinclude($action, &$args, &$iii, &$s) {

   global $ewiki_config;

   #-- get params
   ($url = $args["url"])
   or ($url = $args["href"])
   or ($url = $args["src"])
   or ($url = $args[0]);
   if (!$url || (strpos($url, "http") !== 0)) {
      return;
   }

   #-- load page
   ini_set("user_agent", "mpi_FarInclude/1.2 $ewiki_config[ua]");
   if (function_exists("ewiki_http")) {
      $html = ewiki_http("GET", $url);
   }
   else {
      $html = @implode("", @file($url));
   }
   if (!$html) {
      return;
   }

   #-- strip out stuff
   if ($l = strpos(strtolower($html), "<body")) {
      $html = substr($html, strpos($html, ">", $l) + 1);
   }
   if ($l = strpos(strtolower($html), "</body")) {
      $html = substr($html, 0, $l);
   }

   #-- reduce by regex
   if (($a = $args["start"]) and ($z = $args["end"])) {
      if (preg_match("\007$a(.+?)$z\007ims", $html, $uu)) {
         $html = $uu[1];
      }
   }

   #-- that's it
   $html = "\n<!-- the following was raped from \"$url\" -->\n$html\n<!-- end of FarInclude'd stuff -->\n";
   return($html);
}


?>