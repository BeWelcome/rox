<?php

/*
   This block prints out a short paragraph of the pages that were
   linked from the "News" page. This is much like the page_wikinews,
   but gets the list of to-be-printed pages from another one instead of
   using the list of newest pages.

   It respects following $ewiki_config[] entries:
    ["newsblock_links"] - use which pages` links ("News")
    ["newsblock_num"] - how many new articles to be shown
    ["newsblock_len"] - string length of the excerpts
   (or just customize the defaults below)
*/


if (true)   #-- this is not a function here
{

   global $ewiki_plugins, $ewiki_config;

   #-- conf
   ($n_get = $ewiki_config["newsblock_links"]) || ($n_get = "News");
   ($n_num = $ewiki_config["newsblock_num"]) || ($n_num = 10);
   ($n_len = $ewiki_config["newsblock_len"]) || ($n_len = 196);

   #-- fetch all page entries from DB, for sorting on creation time
   $links = ewiki_db::GET($n_get);
   $links = explode("\n", trim($links["refs"]));

   #-- cut
   $links = array_slice($links, 0, $n_num);

   #-- gen output
   $o = "";
   foreach ($links as $uu=>$id) {

      if ($row = ewiki_db::GET($id)) {

         $text = substr($row["content"], 0, $n_len);
         $text = trim(strip_tags(strtr($text, "\r\n\t", "   ")));
         $text = str_replace("[internal://", "[  internal://", $text);
         $text .= "\n";
#         $text .= " [...[read more | $id]]\n";

         $o .= "__[$id]__\n"
             . "$text\n"
             . "%%%\n";
      }
   }

   #-- pass thru renderer
   if ($o) {
      $o = ewiki_format($o);
      echo('<div class="block-news">' . $o . '</div>');
   }
}

?>