<?php
/*
   Allows to embed a RSS feed into a page. It retrieves and decodes the
   external URL and caches results in the database for later reuse.

   <?plugin Syndicate url="http://example.com/rss.php" limit=10 ?>
   
   @depends: http, http_cache, xml, feed, feedparse
*/


$ewiki_plugins["mpi"]["syndicate"] = "ewiki_mpi_syndicate";

function ewiki_mpi_syndicate($action, &$args, &$iii, &$s) {
   global $ewiki_id;

   #-- params
   ($url = $args["url"]) or ($args = $args["_"]);
   ($cut = $args["limit"]) or ($cut = $args["cut"]) or ($cut = 10);

   #-- fetch
   list($channel, $item) = ewiki_feed_get($url);

   #-- insert as html into current page
   if ($channel) {
      $o = "<b><a href=\"".htmlentities($channel[link])."\">".htmlentities($channel[title]) . "</a></b><br />\n";
      $item = array_slice($item, 0, $cut);
      foreach ($item as $dat) {
         $dat = array_map("htmlentities", $dat);
         $o .= "<a href=\"$dat[link]\">$dat[title]</a> $dat[description] <br />\n";
      }
      return($o);
   }
}


?>